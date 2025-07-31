@extends('layouts.vertical', ['title' => 'Task ' . (isset($task) ? 'Edit' : 'Add')])

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form method="POST" action="{{ isset($task) ? route('tasks.update', $task->id) : route('tasks.store') }}">
                    @csrf
                    @if (isset($task))
                        @method('PUT')
                    @endif

                    {{-- Hidden field to track where user came from --}}
                    @if (request()->has('date') || str_contains(request()->header('referer', ''), 'tasks-calendar'))
                        <input type="hidden" name="redirect_to" value="calendar">
                    @endif

                    <div class="card-body">
                        {{-- Assignment Error Alert --}}
                        @if (session('assignment_error'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Assignment Issue:</strong> {{ session('assignment_error') }}

                                @if (session('suggested_dates'))
                                    <hr class="my-2">
                                    <p class="mb-2"><strong>Suggested available dates:</strong></p>
                                    <div class="row">
                                        @foreach (session('suggested_dates') as $suggestedDate)
                                            <div class="col-md-6 col-lg-4 mb-2">
                                                <button type="button"
                                                    class="btn btn-outline-primary btn-sm w-100 suggested-date-btn"
                                                    data-date="{{ $suggestedDate['date'] }}">
                                                    {{ $suggestedDate['formatted_date'] }}
                                                    ({{ $suggestedDate['day_of_week'] }})
                                                    <br><small>{{ $suggestedDate['available_staff_count'] }} staff
                                                        available</small>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_id" class="form-label">Select Client <span
                                            class="text-danger">*</span></label>
                                    <select name="customer_id" id="customer_id"
                                        class="form-select @error('customer_id') is-invalid @enderror" required>
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ old('customer_id', $task->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="task_date" class="form-label">Task Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="task_date" id="task_date"
                                        class="form-control @error('task_date') is-invalid @enderror"
                                        value="{{ old('task_date', isset($task) ? $task->task_date->format('Y-m-d') : $taskDate ?? '') }}"
                                        required>
                                    @error('task_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="task_type_id" class="form-label">Select Task Type <span
                                            class="text-danger">*</span></label>
                                    <select name="task_type_id" id="task_type_id"
                                        class="form-select @error('task_type_id') is-invalid @enderror" required>
                                        <option value="">Select Task Type</option>
                                        @foreach ($taskTypes as $taskType)
                                            <option value="{{ $taskType->id }}"
                                                {{ old('task_type_id', $task->task_type_id ?? '') == $taskType->id ? 'selected' : '' }}>
                                                {{ $taskType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('task_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="assigned_to" class="form-label">
                                        Assigned To
                                        <span class="text-muted">(Optional - Auto-assign to available staff)</span>
                                    </label>
                                    <select name="assigned_to" id="assigned_to"
                                        class="form-select @error('assigned_to') is-invalid @enderror">
                                        <option value="">Auto-assign to available staff</option>
                                        @foreach ($users->where('status', 'active') as $user)
                                            <option value="{{ $user->id }}"
                                                {{ old('assigned_to', $task->assigned_to ?? '') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                                @if ($user->hasRole('Staff'))
                                                    (Staff -
                                                    {{ $user->min_task_per_day ?? 0 }}-{{ $user->max_task_per_day ?? 0 }}
                                                    tasks/day)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Leave empty for automatic assignment based on staff availability and workload
                                    </div>
                                    @error('assigned_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span
                                            class="text-danger">*</span></label>
                                    <select name="status" id="status"
                                        class="form-select @error('status') is-invalid @enderror" required>
                                        @foreach (\App\Models\Task::getStatusOptions() as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ old('status', $task->status ?? 'pending') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6" id="create-form-publish-status"
                                style="@if (!in_array($task?->taskType?->name ?? null, App\Models\TaskType::PUBLISHABLE_TASK_TYPES)) display:none @endif">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Publish Status</label>
                                    <select name="publish_status" id="publish_status"
                                        class="form-select @error('publish_status') is-invalid @enderror">
                                        <option value="">Select Publish Status</option>
                                        @foreach (['1' => 'Published', '0' => 'Unpublished'] as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ old('publish_status', $task->publish_status ?? '') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('publish_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Hidden fields - these will be set automatically or handled in the backend --}}
                        <input type="hidden" name="estimated_cost"
                            value="{{ old('estimated_cost', $task->estimated_cost ?? '') }}">
                        <input type="hidden" name="estimated_duration_minutes"
                            value="{{ old('estimated_duration_minutes', $task->estimated_duration_minutes ?? '') }}">
                        @if (isset($task))
                            <input type="hidden" name="started_at"
                                value="{{ old('started_at', $task->started_at ? $task->started_at->format('Y-m-d\TH:i') : '') }}">
                            <input type="hidden" name="completed_at"
                                value="{{ old('completed_at', $task->completed_at ? $task->completed_at->format('Y-m-d\TH:i') : '') }}">
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="note_content" class="form-label">Note / Content</label>
                                    <textarea name="note_content" id="note_content" class="form-control @error('note_content') is-invalid @enderror"
                                        rows="5" placeholder="Enter task details, requirements, or notes...">{{ old('note_content', $task->note_content ?? '') }}</textarea>
                                    @error('note_content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Hidden completion notes field --}}
                        @if (isset($task))
                            <input type="hidden" name="completion_notes"
                                value="{{ old('completion_notes', $task->completion_notes ?? '') }}">
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <div class="text-end">
                                    @php
                                        $cancelRoute = 'tasks.index';
                                        // Determine cancel route based on where user came from
                                        if (request()->has('redirect_to') && request()->redirect_to === 'calendar') {
                                            $cancelRoute = 'tasks.calendar';
                                        } elseif (
                                            request()->has('date') ||
                                            str_contains(request()->header('referer', ''), 'tasks-calendar')
                                        ) {
                                            $cancelRoute = 'tasks.calendar';
                                        }
                                    @endphp
                                    <a href="{{ route($cancelRoute) }}" class="btn btn-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i> {{ isset($task) ? 'Update Task' : 'Create Task' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle suggested date buttons
        const suggestedDateButtons = document.querySelectorAll('.suggested-date-btn');
        const taskDateInput = document.getElementById('task_date');
        const assignedToSelect = document.getElementById('assigned_to');
        
        suggestedDateButtons.forEach(button => {
            button.addEventListener('click', function() {
                const selectedDate = this.getAttribute('data-date');
                if (taskDateInput) {
                    taskDateInput.value = selectedDate;
                    
                    // Remove the alert after selecting a date
                    const alert = this.closest('.alert');
                    if (alert) {
                        alert.remove();
                    }
                    
                    // Scroll to the task date field
                    taskDateInput.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    
                    // Highlight the field briefly
                    taskDateInput.style.borderColor = '#0d6efd';
                    taskDateInput.style.boxShadow = '0 0 0 0.2rem rgba(13,110,253,0.25)';
                    
                    setTimeout(() => {
                        taskDateInput.style.borderColor = '';
                        taskDateInput.style.boxShadow = '';
                    }, 2000);
                    
                    // Load staff workload for the selected date
                    loadStaffWorkload(selectedDate);
                }
            });
        });
        
        // Load staff workload when date changes
        if (taskDateInput) {
            taskDateInput.addEventListener('change', function() {
                if (this.value) {
                    loadStaffWorkload(this.value);
                }
            });
        }
        
        function loadStaffWorkload(date) {
            // Only show workload for social media managers
            if (!assignedToSelect || assignedToSelect.hasAttribute('required')) {
                return;
            }
            
            fetch(`{{ route('api.staff.workload') }}?date=${date}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStaffOptions(data.staff_workload);
                    }
                })
                .catch(error => console.error('Error loading staff workload:', error));
        }
        
        function updateStaffOptions(staffWorkload) {
            const options = assignedToSelect.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === '') return; // Skip the "Auto-assign" option
                
                const staffId = parseInt(option.value);
                const staffData = staffWorkload.find(s => s.id === staffId);
                
                if (staffData) {
                    const capacityBadge = getCapacityBadge(staffData.capacity_percentage);
                    const availabilityText = staffData.can_assign_more ? 'Available' : 'At Capacity';
                    
                    option.innerHTML = `${staffData.name} (${staffData.current_tasks}/${staffData.max_tasks} tasks) ${capacityBadge}`;
                    option.disabled = !staffData.can_assign_more;
                    option.title = `${availabilityText} - ${staffData.current_tasks} current tasks, max ${staffData.max_tasks}`;
                }
            });
        }
        
        function getCapacityBadge(percentage) {
            if (percentage >= 100) return '🔴';
            if (percentage >= 80) return '🟠';
            if (percentage >= 60) return '🟡';
            return '🟢';
        }
        
        // Initial load if date is already set
        if (taskDateInput && taskDateInput.value) {
            loadStaffWorkload(taskDateInput.value);
        }
    });
</script>
@endpush


@section('script')
<script>
    document.getElementById('task_type_id').addEventListener('change', function() {
            console.log('Task type ID changed:', this.value);
            const PUBLISHABLE_TASK_TYPES = [
                'Graphic Design',
                'Video Editing'
            ];
            const selectedValue = this.value;
            const selectedText = this.options[this.selectedIndex].text;
            if (PUBLISHABLE_TASK_TYPES.includes(selectedText)) {
                $('#create-form-publish-status').slideDown();
            } else {
                $('#create-form-publish-status').slideUp();
            }
        });
</script>
@endsection