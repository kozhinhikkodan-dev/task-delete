@extends('layouts.vertical', ['title' => 'Task Details'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="card-title mb-0">Task Information</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="text-md-end">
                                    @can('update', $task)
                                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-primary me-2">
                                            <i class="bx bx-edit me-1"></i> Edit Task
                                        </a>
                                    @endcan
                                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to List
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Task ID:</label>
                                    <p class="mb-0">#{{ $task->id }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status:</label>
                                    <p class="mb-0">
                                        @php
                                            $badgeClass = match($task->status) {
                                                'pending' => 'bg-warning-subtle text-warning',
                                                'in_progress' => 'bg-info-subtle text-info',
                                                'completed' => 'bg-success-subtle text-success',
                                                'cancelled' => 'bg-danger-subtle text-danger',
                                                default => 'bg-secondary-subtle text-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} py-1 px-2">{{ $task->status_label }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Customer:</label>
                                    <p class="mb-0">{{ $task->customer->name }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Task Type:</label>
                                    <p class="mb-0">{{ $task->taskType->name }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Assigned To:</label>
                                    <p class="mb-0">{{ $task->assignedUser->name }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Task Date:</label>
                                    <p class="mb-0">{{ $task->task_date->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden fields - these are now hidden from the overview page
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Estimated Cost:</label>
                                    <p class="mb-0">{{ $task->formatted_estimated_cost }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Estimated Duration:</label>
                                    <p class="mb-0">{{ $task->formatted_estimated_duration }}</p>
                                </div>
                            </div>
                        </div>

                        @if($task->started_at)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Started At:</label>
                                        <p class="mb-0">{{ $task->started_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                                @if($task->completed_at)
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Completed At:</label>
                                            <p class="mb-0">{{ $task->completed_at->format('M d, Y h:i A') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                        --}}

                        @if($task->note_content)
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Note / Content:</label>
                                        <div class="bg-light p-3 rounded">
                                            <p class="mb-0">{{ $task->note_content }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Hidden completion notes field
                        @if($task->completion_notes)
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Completion Notes:</label>
                                        <div class="bg-light p-3 rounded">
                                            <p class="mb-0">{{ $task->completion_notes }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        --}}

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Created:</label>
                                    <p class="mb-0">{{ $task->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Last Updated:</label>
                                    <p class="mb-0">{{ $task->updated_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        @if($task->status !== 'completed' && $task->status !== 'cancelled')
                            <div class="row">
                                <div class="col-12">
                                    <div class="border-top pt-3">
                                        <h6 class="mb-3">Quick Status Update</h6>
                                        <form method="POST" action="{{ route('tasks.update-status', $task->id) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <div class="row align-items-end">
                                                <div class="col-md-4">
                                                    <select name="status" class="form-select">
                                                        @foreach(\App\Models\Task::getStatusOptions() as $value => $label)
                                                            <option value="{{ $value }}" {{ $task->status == $value ? 'selected' : '' }}>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="bx bx-check me-1"></i> Update Status
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                </div>
            </div>
        </div>
    </div>
@endsection 