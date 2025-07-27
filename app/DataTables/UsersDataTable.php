<?php

namespace App\DataTables;

use App\DataTables\Support\DataTableColumn as Column;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Request;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Services\DataTable;
use App\Http\Traits\DataTable as DataTableTrait;

class UsersDataTable extends DataTable
{
    use DataTableTrait;
    public function dataTable($query): DataTableAbstract
    {

        $this->normalizePagination();

        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($user) {
                $exclude = ['view'];

            // Get user's roles to check if they're a system user
            $userRoles = $user->roles->pluck('name')->toArray();
                $systemRoles = ['Administrator', 'Supplier', 'Tailor'];

            // If user has any system roles, don't allow deletion
            if (array_intersect($userRoles, $systemRoles)) {
                    $exclude[] = 'delete';
                }

            return renderActions($user, $exclude);
            })
            ->editColumn('created_at', function ($user) {
                return $user->created_at->format('M d, Y') . ' ' . $user->created_at->format('h:i A');
            })
            ->addColumn('roles', function ($user) {
                return $user->roles->pluck('name')->implode(', ');
            })
            ->rawColumns(['action', 'created_at']);
    }

    public function query(User $model)
    {
        $query = $model->newQuery()
            ->with('roles')
            ->select('id', 'name', 'email', 'created_at');

        $filters = request()->input('filter', []);

        // Removing current logged in user
        $query->whereNot('id', Auth::id());

        if ($searchTerm = request()->input('search', null)) {
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        // Filter by role (Spatie) - only if a specific role filter is applied
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        // Order by created_at descending to show newest users first
        $query->orderBy('created_at', 'desc');

        return $query;
    }

    public function columns(): array
    {
        return [
            Column::make('id')->title('ID')->sortable()->center(),
            Column::make('name')->title('Name')->bold(),
            Column::make('email')->title('Email')->bold(),
            Column::make('roles')->title('Roles'),
            Column::make('created_at')->title('Created At')->render(),
            Column::make('action')->title('Action')->width('100px'),
        ];
    }
}
