@extends('layouts.vertical', ['title' => 'Dashboard'])

@section('content')
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-size-18">Dashboard Overview</h4>
                <div class="page-title-right">
                    <small class="text-muted">{{ now()->format('M d, Y') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-lg">
                                <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                    <iconify-icon icon="iconamoon:profile-circle-duotone" class="fs-24"></iconify-icon>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ number_format($metrics['total_customers']) }}</h3>
                            <p class="text-muted mb-2">Total Customers</p>
                            <small class="text-success fw-medium">
                                <i class="bx bx-up-arrow-alt"></i> {{ number_format($metrics['active_customers']) }} Active
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-lg">
                                <div class="avatar-title bg-info-subtle text-info rounded-circle">
                                    <iconify-icon icon="iconamoon:category-duotone" class="fs-24"></iconify-icon>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ number_format($metrics['total_tasks']) }}</h3>
                            <p class="text-muted mb-2">Total Tasks</p>
                            <small class="text-warning fw-medium">
                                <i class="bx bx-time"></i> {{ number_format($metrics['pending_tasks']) }} Pending
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-lg">
                                <div class="avatar-title bg-success-subtle text-success rounded-circle">
                                    <iconify-icon icon="iconamoon:certificate-badge-duotone" class="fs-24"></iconify-icon>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ number_format($metrics['completed_tasks']) }}</h3>
                            <p class="text-muted mb-2">Completed Tasks</p>
                            <small class="text-success fw-medium">
                                <i class="bx bx-check-circle"></i> {{ $metrics['completion_rate'] }}% Rate
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-lg">
                                <div class="avatar-title bg-warning-subtle text-warning rounded-circle">
                                    <iconify-icon icon="iconamoon:coin-duotone" class="fs-24"></iconify-icon>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">${{ number_format($metrics['total_revenue'], 2) }}</h3>
                            <p class="text-muted mb-2">Total Revenue</p>
                            <small class="text-info fw-medium">
                                <i class="bx bx-trending-up"></i> ${{ number_format($metrics['monthly_revenue'], 2) }} This Month
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Task Status Distribution</h5>
                            <p class="text-muted mb-0 mt-1">Current task breakdown</p>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-warning-subtle rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted fw-medium">Pending</span>
                                    <span class="fw-bold">{{ $metrics['pending_tasks'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $metrics['total_tasks'] > 0 ? ($metrics['pending_tasks'] / $metrics['total_tasks']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-info-subtle rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted fw-medium">In Progress</span>
                                    <span class="fw-bold">{{ $metrics['in_progress_tasks'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: {{ $metrics['total_tasks'] > 0 ? ($metrics['in_progress_tasks'] / $metrics['total_tasks']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-success-subtle rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted fw-medium">Completed</span>
                                    <span class="fw-bold">{{ $metrics['completed_tasks'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $metrics['total_tasks'] > 0 ? ($metrics['completed_tasks'] / $metrics['total_tasks']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-danger-subtle rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted fw-medium">Cancelled</span>
                                    <span class="fw-bold">{{ $metrics['cancelled_tasks'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $metrics['total_tasks'] > 0 ? ($metrics['cancelled_tasks'] / $metrics['total_tasks']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Quick Stats</h5>
                            <p class="text-muted mb-0 mt-1">Important metrics at a glance</p>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-primary-subtle rounded">
                                <div class="avatar-sm mx-auto mb-2">
                                    <div class="avatar-title bg-primary text-white rounded-circle">
                                        <i class="bx bx-calendar fs-18"></i>
                                    </div>
                                </div>
                                <h5 class="mb-1">{{ $metrics['due_today'] }}</h5>
                                <p class="text-muted mb-0 small">Due Today</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-danger-subtle rounded">
                                <div class="avatar-sm mx-auto mb-2">
                                    <div class="avatar-title bg-danger text-white rounded-circle">
                                        <i class="bx bx-time fs-18"></i>
                                    </div>
                                </div>
                                <h5 class="mb-1">{{ $metrics['overdue_tasks'] }}</h5>
                                <p class="text-muted mb-0 small">Overdue</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-warning-subtle rounded">
                                <div class="avatar-sm mx-auto mb-2">
                                    <div class="avatar-title bg-warning text-white rounded-circle">
                                        <i class="bx bx-dollar fs-18"></i>
                                    </div>
                                </div>
                                <h6 class="mb-1">${{ number_format($metrics['pending_revenue'], 0) }}</h6>
                                <p class="text-muted mb-0 small">Pending Revenue</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-info-subtle rounded">
                                <div class="avatar-sm mx-auto mb-2">
                                    <div class="avatar-title bg-info text-white rounded-circle">
                                        <i class="bx bx-stopwatch fs-18"></i>
                                    </div>
                                </div>
                                <h6 class="mb-1">{{ $metrics['avg_task_duration'] ? number_format($metrics['avg_task_duration']) . 'm' : 'N/A' }}</h6>
                                <p class="text-muted mb-0 small">Avg Duration</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Top Performers</h5>
                            <p class="text-muted mb-0 mt-1">Most productive team members</p>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="vstack gap-3">
                        @forelse($metrics['top_performers'] as $index => $performer)
                        <div class="d-flex align-items-center p-3 {{ $index === 0 ? 'bg-success-subtle' : 'bg-light' }} rounded">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-{{ $index === 0 ? 'success' : 'primary' }} text-white rounded-circle">
                                        @if($index === 0)
                                            <i class="bx bx-trophy fs-16"></i>
                                        @else
                                            {{ substr($performer->name, 0, 1) }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $performer->name }}</h6>
                                <p class="text-muted mb-0 small">{{ $performer->assigned_tasks_count }} tasks completed</p>
                            </div>
                            @if($index === 0)
                            <div class="flex-shrink-0">
                                <span class="badge bg-success-subtle text-success">Top</span>
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <i class="bx bx-user-circle fs-48 text-muted"></i>
                            <p class="text-muted mb-0 mt-2">No performance data available</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom pb-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Recent Tasks</h5>
                            <p class="text-muted mb-0 mt-1">Latest task activities</p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-nowrap align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Task</th>
                                    <th class="border-0">Customer</th>
                                    <th class="border-0">Assigned To</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Cost</th>
                                    <th class="border-0">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($metrics['recent_tasks'] as $task)
                                <tr>
                                    <td class="py-3">
                                        <div>
                                            <h6 class="mb-1">{{ $task->taskType->name ?? 'N/A' }}</h6>
                                            @if($task->note_content)
                                            <p class="text-muted mb-0 small">{{ Str::limit($task->note_content, 50) }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-2">
                                                <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                    {{ substr($task->customer->name ?? 'N', 0, 1) }}
                                                </div>
                                            </div>
                                            <span>{{ $task->customer->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3">{{ $task->assignedUser->name ?? 'Unassigned' }}</td>
                                    <td class="py-3">
                                        <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'info' : ($task->status == 'pending' ? 'warning' : 'danger')) }}-subtle text-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'info' : ($task->status == 'pending' ? 'warning' : 'danger')) }}">
                                            {{ $task->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-3 fw-medium">${{ number_format($task->estimated_cost, 2) }}</td>
                                    <td class="py-3 text-muted">{{ $task->task_date->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bx bx-task fs-48 text-muted"></i>
                                        <p class="text-muted mb-0 mt-2">No recent tasks</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom pb-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Recent Customers</h5>
                            <p class="text-muted mb-0 mt-1">Latest customer registrations</p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="vstack gap-3">
                        @forelse($metrics['recent_customers'] as $customer)
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-primary text-white rounded-circle">
                                        {{ substr($customer->name, 0, 1) }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $customer->name }}</h6>
                                <p class="text-muted mb-1 small">{{ $customer->email }}</p>
                                <small class="text-muted">{{ $customer->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="badge bg-{{ $customer->status == 'active' ? 'success' : 'secondary' }}-subtle text-{{ $customer->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($customer->status) }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <i class="bx bx-user-plus fs-48 text-muted"></i>
                            <p class="text-muted mb-0 mt-2">No recent customers</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Types Performance -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom pb-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Task Types Performance</h5>
                            <p class="text-muted mb-0 mt-1">Detailed performance metrics by task type</p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('task-types.index') }}" class="btn btn-sm btn-outline-primary">Manage Types</a>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-nowrap align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Task Type</th>
                                    <th class="border-0">Base Rate</th>
                                    <th class="border-0">Est. Time</th>
                                    <th class="border-0">Priority</th>
                                    <th class="border-0">Completed</th>
                                    <th class="border-0">Revenue</th>
                                    <th class="border-0">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($metrics['task_types_performance'] as $taskType)
                                <tr>
                                    <td class="py-3">
                                        <div>
                                            <h6 class="mb-1">{{ $taskType->name }}</h6>
                                            @if($taskType->description)
                                            <p class="text-muted mb-0 small">{{ Str::limit($taskType->description, 50) }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 fw-medium text-success">${{ number_format($taskType->base_rate, 2) }}</td>
                                    <td class="py-3">
                                        <span class="badge bg-info-subtle text-info">{{ $taskType->formatted_estimated_time }}</span>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-{{ $taskType->priority == 'high' ? 'danger' : ($taskType->priority == 'medium' ? 'warning' : 'info') }}-subtle text-{{ $taskType->priority == 'high' ? 'danger' : ($taskType->priority == 'medium' ? 'warning' : 'info') }}">
                                            {{ $taskType->priority_label }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <span class="fw-medium me-2">{{ $taskType->tasks_count }}</span>
                                            <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                                <div class="progress-bar bg-success" style="width: {{ $taskType->tasks_count > 0 ? min(($taskType->tasks_count / 20) * 100, 100) : 0 }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 fw-medium text-primary">${{ number_format($taskType->tasks->sum('estimated_cost'), 2) }}</td>
                                    <td class="py-3">
                                        <span class="badge bg-{{ $taskType->status == 'active' ? 'success' : 'secondary' }}-subtle text-{{ $taskType->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ $taskType->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="bx bx-category fs-48 text-muted"></i>
                                        <p class="text-muted mb-0 mt-2">No task types found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script-bottom')
@vite(['resources/js/pages/widgets.js'])
@endsection