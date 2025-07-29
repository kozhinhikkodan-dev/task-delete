@extends('layouts.vertical', ['title' => 'Task Calendar'])

@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <div class="d-flex align-items-center">
                    @include('common.components.view-toggle', ['currentView' => 'calendar'])
                    @unless(auth()->user()->hasRole('Staff'))
                        <a href="{{ route('tasks.create') }}?redirect_to=calendar" class="btn btn-primary me-2">
                            <i class="bx bx-plus me-1"></i> Add New Task
                        </a>
                    @endunless
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#task-form-modal">
                        <i class="bx bx-plus-circle me-1"></i> Quick Add
                    </button>
                </div>
            </div>
            <h4 class="page-title">Task Calendar</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-3">
                        <!-- Filters -->
                        <div class="calendar-filters">
                            <h5 class="card-title">Filters</h5>
                            <form id="calendar-filters">
                                <div class="mb-1">
                                    <label class="form-label">Customer</label>
                                    <select class="form-select" id="filter-customer" name="customer_id">
                                        <option value="">All Customers</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="mb-1">
                                    <label class="form-label">Task Type</label>
                                    <select class="form-select" id="filter-task-type" name="task_type_id">
                                        <option value="">All Task Types</option>
                                        @foreach($taskTypes as $taskType)
                                            <option value="{{ $taskType->id }}">{{ $taskType->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @cannot(\App\Policies\TaskPolicy::PERMISSION_SHOW_ASSIGNED_TASKS_ONLY, \App\Models\Task::class)
                                    <div class="mb-1">
                                        <label class="form-label">Assigned To</label>
                                        <select class="form-select" id="filter-assigned-to" name="assigned_to">
                                            <option value="">All Users</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endcannot
                                
                                <div class="mb-2">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="filter-status" name="status">
                                        <option value="">All Statuses</option>
                                        @foreach($statusOptions as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-primary" id="apply-filters">Apply Filters</button>
                                    <button type="button" class="btn btn-secondary" id="clear-filters">Clear Filters</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Legend -->
                        <div class="mt-4 status-legend">
                            <h6 class="card-title">Status Legend</h6>
                            <div class="mb-2">
                                <span class="status-indicator status-pending me-2"></span> Pending
                            </div>
                            <div class="mb-2">
                                <span class="status-indicator status-in-progress me-2"></span> In Progress
                            </div>
                            <div class="mb-2">
                                <span class="status-indicator status-completed me-2"></span> Completed
                            </div>
                            <div class="mb-2">
                                <span class="status-indicator status-cancelled me-2"></span> Cancelled
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-9">
                        <div class="mt-4 mt-lg-0 task-calendar">
                            <div id="task-calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Details Modal -->
        <div class="modal fade task-details-modal" id="task-details-modal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Task Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Customer:</label>
                                    <p id="modal-customer" class="mb-0"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Task Type:</label>
                                    <p id="modal-task-type" class="mb-0"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Assigned To:</label>
                                    <p id="modal-assigned-to" class="mb-0"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status:</label>
                                    <p id="modal-status" class="mb-0"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Estimated Cost:</label>
                                    <p id="modal-estimated-cost" class="mb-0"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Estimated Duration:</label>
                                    <p id="modal-estimated-duration" class="mb-0"></p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Notes:</label>
                            <p id="modal-notes" class="mb-0"></p>
                        </div>
                        
                        {{-- Status Update Section --}}
                        @if(auth()->user()->can('Update task') || auth()->user()->can('Update task status'))
                        <div class="border-top pt-3" id="modal-status-update-section">
                            <h6 class="mb-3">Quick Status Update</h6>
                            <form id="modal-status-form">
                                @csrf
                                <div class="row align-items-end">
                                    <div class="col-md-6">
                                        <label for="modal-status-select" class="form-label">Update Status:</label>
                                        <select id="modal-status-select" class="form-select">
                                            @foreach(\App\Models\Task::getStatusOptions() as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-success" id="modal-update-status-btn">
                                            <i class="bx bx-check me-1"></i> Update Status
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        @unless(auth()->user()->hasRole('Staff'))
                            <a href="#" id="modal-edit-link" class="btn btn-primary me-2">Edit Task</a>
                            <button type="button" class="btn btn-success" id="modal-quick-edit-btn">
                                <i class="bx bx-edit me-1"></i> Quick Edit
                            </button>
                        @endunless
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Add/Update Modal -->
        <div class="modal fade" id="task-form-modal" tabindex="-1" aria-labelledby="taskFormModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="task-form" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="taskFormModalLabel">Add New Task</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="modal-customer_id" class="form-label">Select Client <span class="text-danger">*</span></label>
                                        <select name="customer_id" id="modal-customer_id" class="form-select" required>
                                            <option value="">Select Customer</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="modal-task_date" class="form-label">Task Date <span class="text-danger">*</span></label>
                                        <input type="date" name="task_date" id="modal-task_date" class="form-control" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="modal-task_type_id" class="form-label">Select Task Type <span class="text-danger">*</span></label>
                                        <select name="task_type_id" id="modal-task_type_id" class="form-select" required>
                                            <option value="">Select Task Type</option>
                                            @foreach($taskTypes as $taskType)
                                                <option value="{{ $taskType->id }}">{{ $taskType->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="modal-assigned_to" class="form-label">
                                            Assigned To 
                                            <span class="text-muted">(Optional - Auto-assign to available staff)</span>
                                        </label>
                                        <select name="assigned_to" id="modal-assigned_to" class="form-select">
                                            <option value="">Auto-assign to available staff</option>
                                            @foreach($users->where('status', 'active') as $user)
                                                <option value="{{ $user->id }}">
                                                    {{ $user->name }}
                                                    @if($user->hasRole('Staff'))
                                                        (Staff - {{ $user->min_task_per_day ?? 0 }}-{{ $user->max_task_per_day ?? 0 }} tasks/day)
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Leave empty for automatic assignment based on staff availability and workload
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="modal-status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" id="modal-status" class="form-select" required>
                                            @foreach(\App\Models\Task::getStatusOptions() as $value => $label)
                                                <option value="{{ $value }}" {{ $value === 'pending' ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="modal-note_content" class="form-label">Note / Content</label>
                                        <textarea name="note_content" id="modal-note_content" class="form-control" rows="4" placeholder="Enter task details, requirements, or notes..."></textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> <span id="modal-submit-text">Create Task</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script-bottom')
<style>
    #task-form-modal .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    #task-form-modal .form-select:focus,
    #task-form-modal .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    #task-form-modal .required::after {
        content: " *";
        color: #dc3545;
    }
    
    .task-calendar .fc-event {
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .task-calendar .fc-event:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .status-legend .status-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }
    
    .status-legend .status-pending {
        background-color: #ffc107;
    }
    
    .status-legend .status-in-progress {
        background-color: #0dcaf0;
    }
    
    .status-legend .status-completed {
        background-color: #198754;
    }
    
    .status-legend .status-cancelled {
        background-color: #dc3545;
    }
</style>
@if (auth()->user()->hasRole('Staff'))
    <script>
        const isStaff = true;
    </script>
@else
    <script>
        const isStaff = false;
    </script>
@endif
@vite(['resources/js/pages/task-calendar.js'])
@endsection 