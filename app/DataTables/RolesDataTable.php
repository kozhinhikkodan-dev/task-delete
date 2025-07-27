<?php

namespace App\DataTables;

use App\DataTables\Support\DataTableColumn as Column;
use App\Models\Role;
use Request;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Services\DataTable;
use App\Http\Traits\DataTable as DataTableTrait;

class RolesDataTable extends DataTable
{
    use DataTableTrait;
    public function dataTable($query): DataTableAbstract
    {

        $this->normalizePagination();

        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($role) {
                // $render = renderActions($role);
                // return view('components.common.datatable.actions', compact('role'));
    
                $exclude = ['view'];
                $systemRoles = ['Administrator', 'Staff', 'Social Media Manager'];
                if (in_array($role->name, $systemRoles)) {
                    // $exclude[] = 'edit';
                    $exclude[] = 'delete';
                }

                return renderActions($role, $exclude);
            })
            ->editColumn('created_at', function ($role) {
                // return $role->created_at->diffForHumans();
                // D-M-Y format
                return $role->created_at->format('M d, Y') . ' ' . $role->created_at->format('h:i A');// . ' <br>' . $role->created_at->diffForHumans();
            })
            ->rawColumns(['action', 'created_at']);
    }

    public function query(Role $model)
    {
        $query = $model->newQuery()->select('id', 'name', 'created_at');
        $filters = request()->input('filter');

        if ($searchTerm = request()->input('search', null)) {
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }
        if ($guard = $filters['guard_name'] ?? null) {
            $query->where('guard_name', $guard);
        }
        return $query;
    }
    public function columns(): array
    {
        return [
            Column::make('id')->title('ID')->sortable()->center(),
            Column::make('name')->title('Name')->bold(),
            Column::make('created_at')->title('Created At')->render(),
            Column::make('action')->title('Action')->width('100px'),
        ];
    }
}
