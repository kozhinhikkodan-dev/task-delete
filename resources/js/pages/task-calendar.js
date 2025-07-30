/*
Template Name: Task Calendar
Author: Custom
File: task-calendar.js
*/
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import { Modal } from 'bootstrap';

class TaskCalendar {
    constructor() {
        this.calendar = document.getElementById('task-calendar');
        this.calendarObj = null;
        this.taskDetailsModal = new Modal(document.getElementById('task-details-modal'));
        this.taskFormModal = new Modal(document.getElementById('task-form-modal'));
        this.currentFilters = {};
        this.currentTaskId = null;
        this.isEditMode = false;
        this.init();
    }

    init() {
        this.setupCalendar();
        this.setupFilters();
        this.setupEventListeners();
        this.setupTaskFormModal();
    }

    setupCalendar() {
        const self = this;
        this.calendarObj = new Calendar(this.calendar, {
            plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            height: 'auto',
            editable: true,
            droppable: true,
            selectable: true,
            selectMirror: true,
            eventDisplay: 'block',
            dayMaxEvents: true,
            
            // Event sources
            events: function(fetchInfo, successCallback, failureCallback) {
                self.fetchTasks(fetchInfo, successCallback, failureCallback);
            },
            
            // Event click handler
            eventClick: function(info) {
                self.showTaskDetails(info.event);
            },
            
            // Event drop handler (drag and drop)
            eventDrop: function(info) {
                if (typeof isStaff !== 'undefined' && isStaff === true) {
                    // Prevent drag and drop for staff
                    info.revert();
                    return;
                }
                self.updateTaskDate(info.event, info.event.start);
            },
            
            // Event resize handler
            eventResize: function(info) {
                self.updateTaskDate(info.event, info.event.start);
            },
            
            // Date click handler
            dateClick: function(info) {
                // Open task form modal with pre-filled date
                self.openTaskFormModal(info.dateStr);
            },
            
            // Loading handler
            loading: function(isLoading) {
                if (isLoading) {
                    document.body.style.cursor = 'wait';
                } else {
                    document.body.style.cursor = 'default';
                }
            }
        });

        this.calendarObj.render();
    }

    setupTaskFormModal() {
        const self = this;

        // Handle form submission
        document.getElementById('task-form').addEventListener('submit', function (e) {
            e.preventDefault();
            self.submitTaskForm();
        });

        // Handle modal close
        document.getElementById('task-form-modal').addEventListener('hidden.bs.modal', function () {
            self.resetTaskForm();
            self.forceModalCleanup();
        });

        // Handle quick edit button in task details modal
        if(isStaff) {
            const modalQuickEditBtn = document.getElementById('modal-quick-edit-btn');
            if(modalQuickEditBtn) { modalQuickEditBtn.style.display = 'none'};
            const modalEditLink = document.getElementById('modal-edit-link');
            if(modalEditLink) { modalEditLink.style.display = 'none' };
        }

        const quickEditBtn = document.getElementById('modal-quick-edit-btn');
        if (quickEditBtn && !isStaff) {
            quickEditBtn.addEventListener('click', function () {
                self.openTaskFormModalForEdit();
            });
        }

        // Handle modal backdrop clicks
        document.getElementById('task-form-modal').addEventListener('click', function (e) {
            if (e.target === this) {
                self.taskFormModal.hide();
                self.forceModalCleanup();
            }
        });
        
        // Add a global method to force close stuck modals
        window.forceCloseTaskModal = function() {
            self.taskFormModal.hide();
            self.forceModalCleanup();
        };
    }

    openTaskFormModal(date = null) {
        // Ensure any previous modal state is cleaned up
        this.forceModalCleanup();
        
        this.isEditMode = false;
        this.currentTaskId = null;

        // Update modal title and submit button
        document.getElementById('taskFormModalLabel').textContent = 'Add New Task';
        document.getElementById('modal-submit-text').textContent = 'Create Task';

        // Clear form
        this.resetTaskForm();

        // Set date if provided
        if (date) {
            document.getElementById('modal-task_date').value = date;
            this.loadStaffWorkload(date);
        } else {
            // Set to today's date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('modal-task_date').value = today;
            this.loadStaffWorkload(today);
        }

        // Show modal
        this.taskFormModal.show();
    }

    openTaskFormModalForEdit() {
        if (!this.currentTaskId) return;

        const event = this.calendarObj.getEventById(this.currentTaskId);
        if (!event) return;

        // Ensure any previous modal state is cleaned up
        this.forceModalCleanup();
        
        this.isEditMode = true;

        // Update modal title and submit button
        document.getElementById('taskFormModalLabel').textContent = 'Edit Task';
        document.getElementById('modal-submit-text').textContent = 'Update Task';

        // Populate form with current task data
        const props = event.extendedProps;
        const taskDate = event.start.toISOString().split('T')[0];
        document.getElementById('modal-customer_id').value = props.customer_id || '';
        document.getElementById('modal-task_date').value = taskDate;
        document.getElementById('modal-task_type_id').value = props.task_type_id || '';
        document.getElementById('modal-assigned_to').value = props.assigned_to || '';
        document.getElementById('modal-status').value = props.status || 'pending';
        document.getElementById('modal-note_content').value = props.note_content || '';

        // Load staff workload for the task date
        this.loadStaffWorkload(taskDate);

        // Hide task details modal and show form modal
        this.taskDetailsModal.hide();
        this.taskFormModal.show();
    }

    submitTaskForm() {
        const form = document.getElementById('task-form');
        const formData = new FormData(form);

        console.log('Form element:', form);
        console.log('Form validity:', form.checkValidity());

        // Add method field for updates
        if (this.isEditMode) {
            formData.append('_method', 'PUT');
        }

        // Convert FormData to regular object
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        console.log('Form data:', data);
        console.log('Is edit mode:', this.isEditMode);
        console.log('Current task ID:', this.currentTaskId);

        // Check if required fields are present
        const requiredFields = ['customer_id', 'task_type_id', 'task_date', 'status'];
        const missingFields = requiredFields.filter(field => !data[field]);
        if (missingFields.length > 0) {
            console.warn('Missing required fields:', missingFields);
        }

        // Determine URL and method
        const url = this.isEditMode
            ? `/api/tasks/${this.currentTaskId}` 
            : '/api/tasks';

        const method = this.isEditMode ? 'PUT' : 'POST';

        console.log('Request URL:', url);
        console.log('Request method:', method);

        // Clear previous errors
        this.clearFormErrors();

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i> Saving...';
        submitBtn.disabled = true;

        // Get CSRF token
        const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
        if (!csrfTokenElement) {
            console.error('CSRF token meta tag not found');
            this.showNotification('CSRF token not found. Please refresh the page.', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            return;
        }

        const csrfToken = csrfTokenElement.getAttribute('content');
        console.log('CSRF token:', csrfToken ? 'Found' : 'Missing');

        // Submit form
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);

                if (data.success) {
                    // Show success message
                    this.showNotification(data.message || 'Task saved successfully!', 'success');

                    // Close modal properly
                    this.taskFormModal.hide();

                    // Ensure modal cleanup
                    setTimeout(() => {
                        // Remove any lingering modal backdrops
                        document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                            backdrop.remove();
                        });

                        // Ensure body classes are cleaned up
                        document.body.classList.remove('modal-open');
                        document.body.style.paddingRight = '';
                        document.body.style.overflow = '';
                    }, 100);

                    // Reset form
                    this.resetTaskForm();

                    // Refresh calendar
                    this.calendarObj.refetchEvents();
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        console.log('Validation errors:', data.errors);
                        this.showFormErrors(data.errors);
                    } else {
                        this.showNotification(data.message || 'Error saving task.', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                this.showNotification('Error saving task.', 'error');

                // Ensure modal doesn't get stuck on error
                setTimeout(() => {
                    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                        backdrop.remove();
                    });
                    document.body.classList.remove('modal-open');
                    document.body.style.paddingRight = '';
                    document.body.style.overflow = '';
                }, 100);
            })
            .finally(() => {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    }

    resetTaskForm() {
        const form = document.getElementById('task-form');
        form.reset();
        this.clearFormErrors();

        // Reset status to pending
        document.getElementById('modal-status').value = 'pending';

        // Reset staff options to original state
        this.resetStaffOptions();
    }

    clearFormErrors() {
        const errorElements = document.querySelectorAll('#task-form .invalid-feedback');
        errorElements.forEach(el => el.textContent = '');

        const inputElements = document.querySelectorAll('#task-form .form-control, #task-form .form-select');
        inputElements.forEach(el => el.classList.remove('is-invalid'));
    }

    showFormErrors(errors) {
        Object.keys(errors).forEach(field => {
            const input = document.getElementById(`modal-${field}`);
            const errorDiv = input ? input.nextElementSibling : null;

            if (input && errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                input.classList.add('is-invalid');
                errorDiv.textContent = errors[field][0];
            }
        });
    }

    fetchTasks(fetchInfo, successCallback, failureCallback) {
        const params = new URLSearchParams({
            start: fetchInfo.startStr,
            end: fetchInfo.endStr,
            ...this.currentFilters
        });

        fetch(`/api/tasks-calendar?${params}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            successCallback(data);
        })
        .catch(error => {
            console.error('Error fetching tasks:', error);
            failureCallback(error);
        });
    }

    updateTaskDate(event, newDate) {
        const taskId = event.extendedProps.task_id;
        const formattedDate = newDate.toISOString().split('T')[0];
        
        fetch(`/api/tasks/${taskId}/update-date`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                task_date: formattedDate
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('Task date updated successfully!', 'success');
            } else {
                this.showNotification('Error updating task date.', 'error');
                // Revert the event
                event.revert();
            }
        })
        .catch(error => {
            console.error('Error updating task date:', error);
            this.showNotification('Error updating task date.', 'error');
            // Revert the event
            event.revert();
        });
    }

    showTaskDetails(event) {
        const props = event.extendedProps;
        
        // Store current task ID for editing
        this.currentTaskId = props.task_id;

        // Populate modal with task details
        document.getElementById('modal-customer').textContent = props.customer_name;
        document.getElementById('modal-task-type').textContent = props.task_type;
        document.getElementById('modal-assigned-to').textContent = props.assigned_to_name;
        document.getElementById('modal-status').innerHTML = `<span class="badge bg-${this.getStatusColor(props.status)}">${props.status_label}</span>`;
        // document.getElementById('modal-estimated-cost').textContent = props.estimated_cost;
        // document.getElementById('modal-estimated-duration').textContent = props.estimated_duration;
        document.getElementById('modal-notes').textContent = props.note_content || 'No notes available';
        
        if(!isStaff) {
            // Set edit link with calendar redirect
            document.getElementById('modal-edit-link').href = `/tasks/${props.task_id}/edit?redirect_to=calendar`;
        }
        // Set up status update form if it exists
        const statusSelect = document.getElementById('modal-status-select');
        if (statusSelect) {
            statusSelect.value = props.status;
        }
        
        // Show modal
        this.taskDetailsModal.show();
    }

    getStatusColor(status) {
        const colors = {
            'pending': 'warning',
            'in_progress': 'info',
            'completed': 'success',
            'cancelled': 'danger'
        };
        return colors[status] || 'primary';
    }

    setupFilters() {
        const self = this;
        
        // Apply filters button
        document.getElementById('apply-filters').addEventListener('click', function() {
            self.applyFilters();
        });
        
        // Clear filters button
        document.getElementById('clear-filters').addEventListener('click', function() {
            self.clearFilters();
        });
        
        // Filter change handlers
        const filterElements = [
            'filter-customer',
            'filter-task-type',
            'filter-assigned-to',
            'filter-status'
        ];
        
        filterElements.forEach(elementId => {
            document.getElementById(elementId).addEventListener('change', function() {
                self.applyFilters();
            });
        });
    }

    applyFilters() {
        this.currentFilters = {};
        
        const filterElements = {
            'filter-customer': 'customer_id',
            'filter-task-type': 'task_type_id',
            'filter-assigned-to': 'assigned_to',
            'filter-status': 'status',
            'filter-content-status' : 'content_status',
            'filter-publish-status' : 'publish_status'
        };
        
        Object.keys(filterElements).forEach(elementId => {
            const element = document.getElementById(elementId);
            if (element && element.value) {
                this.currentFilters[filterElements[elementId]] = element.value;
            }
        });
        
        // Refresh calendar events
        this.calendarObj.refetchEvents();
    }

    clearFilters() {
        this.currentFilters = {};
        
        // Clear form
        document.getElementById('calendar-filters').reset();
        
        // Refresh calendar events
        this.calendarObj.refetchEvents();
    }

    setupEventListeners() {
        // Handle window resize
        window.addEventListener('resize', () => {
            this.calendarObj.updateSize();
        });
        
        // Handle escape key to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.taskDetailsModal._isShown) {
                    this.taskDetailsModal.hide();
                }
                if (this.taskFormModal._isShown) {
                    this.taskFormModal.hide();
                    this.forceModalCleanup();
                }
            }
        });
        
        // Handle Quick Add button
        const quickAddBtn = document.querySelector('[data-bs-target="#task-form-modal"]');
        if(isStaff) {
            quickAddBtn.style.display = 'none';
        }
        if (quickAddBtn  && !isStaff) {
            quickAddBtn.addEventListener('click', () => {
                this.openTaskFormModal();
            });
        }

        // Handle status update form submission
        const statusForm = document.getElementById('modal-status-form');
        if (statusForm) {
            statusForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateTaskStatus();
            });
        }

        // Load staff workload when date changes in modal
        const taskDateInput = document.getElementById('modal-task_date');
        if (taskDateInput) {
            taskDateInput.addEventListener('change', () => {
                if (taskDateInput.value) {
                    this.loadStaffWorkload(taskDateInput.value);
                }
            });
        }
    }

    updateTaskStatus() {
        const statusSelect = document.getElementById('modal-status-select');
        const submitButton = document.getElementById('modal-update-status-btn');
        
        if (!statusSelect || !this.currentTaskId) {
            return;
        }
        
        const newStatus = statusSelect.value;
        
        // Disable button and show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i> Updating...';
        
        // Make AJAX request to update status
        fetch(`/tasks/${this.currentTaskId}/update-status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update the modal status display
                const statusDisplay = document.getElementById('modal-status');
                const statusLabel = this.getStatusLabel(newStatus);
                statusDisplay.innerHTML = `<span class="badge bg-${this.getStatusColor(newStatus)}">${statusLabel}</span>`;
                
                // Refresh calendar to show updated status
                this.calendarObj.refetchEvents();
                
                // Show success notification
                this.showNotification(data.message, 'success');
            } else {
                throw new Error(data.message || 'Failed to update status');
            }
        })
        .catch(error => {
            console.error('Error updating status:', error);
            this.showNotification('Failed to update task status. Please try again.', 'error');
        })
        .finally(() => {
            // Re-enable button and restore original text
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="bx bx-check me-1"></i> Update Status';
        });
    }
    
    getStatusLabel(status) {
        const labels = {
            'pending': 'Pending',
            'in_progress': 'In Progress',
            'completed': 'Completed',
            'cancelled': 'Cancelled'
        };
        return labels[status] || status;
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Add to body
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    // Public method to refresh calendar
    refresh() {
        this.calendarObj.refetchEvents();
    }

    loadStaffWorkload(date) {
        const assignedToSelect = document.getElementById('modal-assigned_to');
        if (!assignedToSelect) return;

        fetch(`/api/staff-workload?date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateStaffOptions(data.staff_workload);
                }
            })
            .catch(error => console.error('Error loading staff workload:', error));
    }

    updateStaffOptions(staffWorkload) {
        const assignedToSelect = document.getElementById('modal-assigned_to');
        if (!assignedToSelect) return;

        const options = assignedToSelect.querySelectorAll('option');

        options.forEach(option => {
            if (option.value === '') return; // Skip the "Auto-assign" option

            // Store original text if not already stored
            if (!option.getAttribute('data-original-text')) {
                option.setAttribute('data-original-text', option.innerHTML);
            }

            const staffId = parseInt(option.value);
            const staffData = staffWorkload.find(s => s.id === staffId);

            if (staffData) {
                const capacityBadge = this.getCapacityBadge(staffData.capacity_percentage);
                const availabilityText = staffData.can_assign_more ? 'Available' : 'At Capacity';

                option.innerHTML = `${staffData.name} (${staffData.current_tasks}/${staffData.max_tasks} tasks) ${capacityBadge}`;
                option.disabled = !staffData.can_assign_more;
                option.title = `${availabilityText} - ${staffData.current_tasks} current tasks, max ${staffData.max_tasks}`;
            }
        });
    }

    getCapacityBadge(percentage) {
        if (percentage >= 100) return '🔴';
        if (percentage >= 80) return '🟠';
        if (percentage >= 60) return '🟡';
        return '🟢';
    }

    resetStaffOptions() {
        const assignedToSelect = document.getElementById('modal-assigned_to');
        if (!assignedToSelect) return;

        const options = assignedToSelect.querySelectorAll('option');

        options.forEach(option => {
            if (option.value === '') return; // Skip the "Auto-assign" option

            // Reset to original text without workload info
            const originalText = option.getAttribute('data-original-text');
            if (originalText) {
                option.innerHTML = originalText;
            }
            option.disabled = false;
            option.title = '';
        });
    }

    forceModalCleanup() {
        // Remove any lingering modal backdrops
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
            backdrop.remove();
        });

        // Clean up body classes and styles
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
        document.body.style.overflow = '';

        // Reset modal state
        const modalElement = document.getElementById('task-form-modal');
        if (modalElement) {
            modalElement.classList.remove('show');
            modalElement.style.display = 'none';
            modalElement.setAttribute('aria-hidden', 'true');
            modalElement.removeAttribute('aria-modal');
        }
    }
}

// Initialize calendar when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.taskCalendar = new TaskCalendar();
}); 