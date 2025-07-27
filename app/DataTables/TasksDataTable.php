<?php

namespace App\DataTables;

use App\DataTables\Support\DataTableColumn as Column;
use App\Models\Task;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Services\DataTable;
use App\Http\Traits\DataTable as DataTableTrait;

class TasksDataTable extends DataTable
{
    use DataTableTrait;

    public function dataTable($query): DataTableAbstract
    {
        $this->normalizePagination();

        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($task) {
                return renderActions($task);
            })
            ->editColumn('customer_name', function ($task) {
                return $task->customer->name ?? 'N/A';
            })
            ->editColumn('task_type_name', function ($task) {
                return $task->taskType->name ?? 'N/A';
            })
            ->editColumn('assigned_to_name', function ($task) {
                return $task->assignedUser->name ?? 'N/A';
            })
            ->editColumn('task_date', function ($task) {
                return $task->task_date->format('M d, Y');
            })
            ->editColumn('status', function ($task) {
                $badgeClass = match($task->status) {
                    'pending' => 'bg-warning-subtle text-warning',
                    'in_progress' => 'bg-info-subtle text-info',
                    'completed' => 'bg-success-subtle text-success',
                    'cancelled' => 'bg-danger-subtle text-danger',
                    default => 'bg-secondary-subtle text-secondary'
                };
                return '<span class="badge ' . $badgeClass . ' py-1 px-2">' . $task->status_label . '</span>';
            })
            ->editColumn('estimated_cost', function ($task) {
                return $task->formatted_estimated_cost;
            })
            ->editColumn('estimated_duration_minutes', function ($task) {
                return $task->formatted_estimated_duration;
            })
            ->editColumn('created_at', function ($task) {
                return $task->created_at->format('M d, Y') . ' ' . $task->created_at->format('h:i A');
            })
            ->rawColumns(['action', 'status']);
    }

    public function query(Task $model)
    {
        $query = $model->newQuery()
            ->with(['customer', 'taskType', 'assignedUser'])
            ->select('tasks.*');

        $filters = request()->input('filter');

        if ($searchTerm = request()->input('search', null)) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('note_content', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('customer', function($subQuery) use ($searchTerm) {
                      $subQuery->where('name', 'like', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('taskType', function($subQuery) use ($searchTerm) {
                      $subQuery->where('name', 'like', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('assignedUser', function($subQuery) use ($searchTerm) {
                      $subQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        if ($customer = $filters['customer_id'] ?? null) {
            $query->where('customer_id', $customer);
        }

        if ($taskType = $filters['task_type_id'] ?? null) {
            $query->where('task_type_id', $taskType);
        }

        if ($assignedTo = $filters['assigned_to'] ?? null) {
            $query->where('assigned_to', $assignedTo);
        }

        if ($dateFrom = $filters['date_from'] ?? null) {
            $query->where('task_date', '>=', $dateFrom);
        }

        if ($dateTo = $filters['date_to'] ?? null) {
            $query->where('task_date', '<=', $dateTo);
        }

        return $query->orderBy('id', 'desc');
    }

    public function columns(): array
    {
        return [
            Column::make('id')->title('ID')->center(),
            Column::make('customer_name')->title('Customer'),
            Column::make('task_type_name')->title('Task Type'),
            Column::make('assigned_to_name')->title('Assigned To'),
            Column::make('task_date')->title('Task Date'),
            Column::make('status')->title('Status')->center(),
            Column::make('estimated_cost')->title('Est. Cost')->center(),
            Column::make('estimated_duration_minutes')->title('Est. Duration')->center(),
            Column::make('created_at')->title('Created'),
            Column::make('action')->title('Actions')->width('150px'),
        ];
    }

    public function filename(): string
    {
        return 'tasks_' . date('YmdHis');
    }
} 