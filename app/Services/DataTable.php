<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

abstract class DataTable
{
    protected $request;
    protected $columns = [];
    protected $queryCallback;
    protected $view;

    /**
     * Initialize with the request.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Set the view name.
     *
     * @param string $view
     * @return $this
     */
    public function withView(string $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Set a custom query callback.
     *
     * @param callable $callback
     * @return $this
     */
    public function queryCallback(callable $callback)
    {
        $this->queryCallback = $callback;
        return $this;
    }

    /**
     * Handle the request and return JSON or view response.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function render()
    {
        $this->columns = $this->defineColumns();

        if ($this->request->ajax()) {
            return $this->process();
        }

        if (!$this->view) {
            throw new \InvalidArgumentException('View name must be set using withView() for non-AJAX requests.');
        }

        return view($this->view, ['columns' => $this->columns]);
    }

    /**
     * Process the DataTable request and return JSON response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function process()
    {
        // Validate request parameters
        $this->request->validate([
            'per_page' => 'integer|min:1|max:100',
            'sort_by' => 'nullable|string|in:' . implode(',', array_column($this->columns, 'data')),
            'sort_direction' => 'in:asc,desc',
            'search' => 'nullable|string',
            'page' => 'integer|min:1',
        ]);

        $query = $this->buildQuery();

        // Paginate
        $perPage = $this->request->input('per_page', 1);

        try {
            $data = $query->paginate($perPage);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }

        return response()->json([
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ],
        ]);
    }

    /**
     * Build the query with filters, sorting, and custom logic.
     *
     * @return Builder
     */
    protected function buildQuery()
    {
        $query = $this->getModel()::query();

        // Apply search
        if ($this->request->filled('search')) {
            $query->where(function ($q) {
                foreach ($this->columns as $column) {
                    if ($column['searchable'] ?? true) {
                        $q->orWhere($column['data'], 'like', '%' . $this->request->search . '%');
                    }
                }
            });
        }

        // Apply sorting
        if ($this->request->filled('sort_by') && $this->request->filled('sort_direction')) {
            $query->orderBy($this->request->sort_by, $this->request->sort_direction);
        }

        // Apply custom query modifications
        if ($this->queryCallback) {
            $query = call_user_func($this->queryCallback, $query, $this->request);
        }

        return $query;
    }

    /**
     * Define the model class.
     *
     * @return string
     */
    abstract protected function getModel(): string;

    /**
     * Define the columns for the DataTable.
     *
     * @return array
     */
    abstract protected function defineColumns(): array;
}