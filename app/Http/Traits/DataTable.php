<?php
namespace App\Http\Traits;

use Illuminate\Http\Request;

trait DataTable
{
    protected string|null $ajaxUrl = null;

    public function renderDataTable(Request $request, $dataTable, string $view, string|null $ajaxUrl = null)
    {
        // Fallback to default route name
        $finalAjaxUrl = $ajaxUrl ?? $this->ajaxUrl ?? route($request->route()->getName());

        if ($request->ajax()) {
            $data = $dataTable->ajax()->getData(true);
            $input = $data['input'];

            // Fallback safely
            $currentPage = $input['current_page'] ?? (($input['start'] ?? 0) / ($input['length'] ?? 10) + 1);
            $perPage     = $input['length'] ?? $input['per_page'] ?? 10;

            $columns = collect($dataTable->columns())
                ->map(fn($col) => is_object($col) && method_exists($col, 'toArray') ? $col->toArray() : $col)
                ->values()
                ->all();

            return response()->json([
                'columns' => $columns,
                'data' => $data['data'],
                'ajax_url' => $finalAjaxUrl,
                'meta' => [
                    'draw' => $data['draw'] ?? 1,
                    'recordsTotal' => $data['recordsTotal'] ?? 0,
                    'recordsFiltered' => $data['recordsFiltered'] ?? 0,
                    'current_page' => (int) $currentPage,
                    'per_page' => (int) $perPage,
                ],
            ]);
        }

        return view($view);
    }

   // Make $perPage optional
protected function normalizePagination(?int $perPage = null): void
{
    // Get from request if not passed directly
    $perPage = $perPage ?? (int) request()->get('per_page', 10);
    $page = (int) request()->get('page', 1);
    $start = ($page - 1) * $perPage;

    request()->merge([
        'length' => $perPage,
        'start' => $start,
    ]);
}


}

