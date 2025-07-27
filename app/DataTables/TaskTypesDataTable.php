<?php

namespace App\DataTables;

use App\DataTables\Support\DataTableColumn as Column;
use App\Models\TaskType;
use Request;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Services\DataTable;
use App\Http\Traits\DataTable as DataTableTrait;

class TaskTypesDataTable extends DataTable
{
    use DataTableTrait;

    public function dataTable($query): DataTableAbstract
    {
        $this->normalizePagination();

        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($taskType) {
                $exclude = ['view'];
                return renderActions($taskType, $exclude);
            })
            ->editColumn('created_at', function ($taskType) {
                return $taskType->created_at->format('M d, Y') . ' ' . $taskType->created_at->format('h:i A');
            })

            ->editColumn('status', function ($taskType) {
                $class = $taskType->status === 'active' ? 'badge-success' : 'badge-secondary';
                return '<span class="badge ' . $class . '">' . $taskType->status_label . '</span>';
            })
            ->rawColumns(['action', 'created_at', 'status']);
    }

    public function query(TaskType $model)
    {
        $query = $model->newQuery()->select('id', 'name', 'description', 'status', 'created_at');
        $filters = request()->input('filter');

        if ($searchTerm = request()->input('search', null)) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function columns(): array
    {
        return [
            Column::make('id')->title('ID')->sortable()->center(),
            Column::make('name')->title('Task Type')->bold(),
            Column::make('status')->title('Status')->sortable(),
            Column::make('created_at')->title('Created At')->render(),
            Column::make('action')->title('Action')->width('100px'),
        ];
    }
} 