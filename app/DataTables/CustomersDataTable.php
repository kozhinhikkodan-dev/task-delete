<?php

namespace App\DataTables;

use App\DataTables\Support\DataTableColumn as Column;
use App\Models\Customer;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Services\DataTable;
use App\Http\Traits\DataTable as DataTableTrait;

class CustomersDataTable extends DataTable
{
    use DataTableTrait;

    public function dataTable($query): DataTableAbstract
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($customer) {
                return '<div class="d-flex gap-2">
                    <a href="'.route('customers.show', $customer->id).'" class="btn btn-soft-info btn-sm" title="View Details">
                        <i class="bx bx-show"></i>
                    </a>
                    <a href="'.route('customers.edit', $customer->id).'" class="btn btn-soft-primary btn-sm" title="Edit">
                        <i class="bx bx-edit"></i>
                    </a>
                    <form method="POST" action="'.route('customers.destroy', $customer->id).'" style="display:inline;">
                        '.csrf_field().'
                        '.method_field('DELETE').'
                        <button type="submit" class="btn btn-soft-danger btn-sm" onclick="return confirm(\'Are you sure?\')" title="Delete">
                            <i class="bx bx-trash"></i>
                        </button>
                    </form>
                </div>';
            })
            ->editColumn('created_at', function ($customer) {
                return $customer->created_at->format('M d, Y') . ' ' . $customer->created_at->format('h:i A');
            })
            ->editColumn('status', function ($customer) {
                $badgeClass = $customer->status === 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                return '<span class="badge ' . $badgeClass . ' py-1 px-2">' . ucfirst($customer->status) . '</span>';
            })
            ->editColumn('service_renew_date', function ($customer) {
                if (!$customer->service_renew_date) {
                    return '<span class="text-muted">Not set</span>';
                }

                $renewDate = $customer->service_renew_date;
                $today = now()->toDateString();
                $thirtyDaysFromNow = now()->addDays(30)->toDateString();

                $badgeClass = 'bg-success-subtle text-success';
                $status = 'Active';

                if ($renewDate < $today) {
                    $badgeClass = 'bg-danger-subtle text-danger';
                    $status = 'Expired';
                } elseif ($renewDate <= $thirtyDaysFromNow) {
                    $badgeClass = 'bg-warning-subtle text-warning';
                    $status = 'Expiring Soon';
                }

                return $renewDate->format('M d, Y') .
                    '<br><span class="badge ' . $badgeClass . ' py-1 px-2" style="font-size: 10px;">' . $status . '</span>';
            })
            ->rawColumns(['action', 'created_at', 'status', 'service_renew_date']);
    }

    public function query(Customer $model)
    {
        $query = $model->newQuery()
            ->select('id', 'name', 'email', 'phone', 'city', 'state', 'country', 'status', 'service_start_date', 'service_renew_date', 'created_at');

        $filters = request()->input('filter', []);

        if ($searchTerm = request()->input('search', null)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                    ->orWhere('city', 'like', '%' . $searchTerm . '%')
                    ->orWhere('state', 'like', '%' . $searchTerm . '%')
                    ->orWhere('country', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter by status if provided
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by city if provided
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        // Filter by state if provided
        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        // Filter by country if provided
        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        // Filter by service start date range
        if (!empty($filters['service_start_from'])) {
            $query->where('service_start_date', '>=', $filters['service_start_from']);
        }

        if (!empty($filters['service_start_to'])) {
            $query->where('service_start_date', '<=', $filters['service_start_to']);
        }

        // Filter by service renewal date range
        if (!empty($filters['service_renew_from'])) {
            $query->where('service_renew_date', '>=', $filters['service_renew_from']);
        }

        if (!empty($filters['service_renew_to'])) {
            $query->where('service_renew_date', '<=', $filters['service_renew_to']);
        }

        // Filter by service status based on renewal dates
        if (!empty($filters['service_status'])) {
            $today = now()->toDateString();
            $thirtyDaysFromNow = now()->addDays(30)->toDateString();

            switch ($filters['service_status']) {
                case 'active':
                    $query->where('service_renew_date', '>', $thirtyDaysFromNow);
                    break;
                case 'expiring_soon':
                    $query->whereBetween('service_renew_date', [$today, $thirtyDaysFromNow]);
                    break;
                case 'expired':
                    $query->where('service_renew_date', '<', $today);
                    break;
            }
        }

        // Order by created_at descending to show newest customers first
        $query->orderBy('created_at', 'desc');

        return $query;
    }

    public function columns(): array
    {
        return [
            Column::make('id')->title('ID')->sortable()->center(),
            Column::make('name')->title('Name')->bold(),
            Column::make('email')->title('Email')->bold(),
            Column::make('phone')->title('Phone'),
            Column::make('city')->title('City'),
            Column::make('state')->title('State'),
            Column::make('country')->title('Country'),
            Column::make('status')->title('Status'),
            Column::make('service_renew_date')->title('Service Renewal')->render(),
            Column::make('created_at')->title('Created At')->render(),
            Column::make('action')->title('Action')->width('120px'),
        ];
    }
} 