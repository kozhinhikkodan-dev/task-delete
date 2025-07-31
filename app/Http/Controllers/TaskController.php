<?php

namespace App\Http\Controllers;

use App\DataTables\TasksDataTable;
use App\Http\Requests\TaskRequest;
use App\Http\Traits\DataTable;
use App\Models\Task;
use App\Models\Customer;
use App\Models\TaskType;
use App\Models\User;
use App\Services\TaskAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    use DataTable;

    public function __construct()
    {
        $this->authorizeResource(Task::class, 'task', [
            'except' => ['apiStore', 'apiUpdate', 'apiDestroy']
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, TasksDataTable $dataTable)
    {
        return $this->renderDataTable($request, $dataTable, 'tasks.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('status', 'active')->get();
        $taskTypes = TaskType::where('status', 'active')->get();
        $users = User::all();

        // Pre-fill task date if provided from calendar
        $taskDate = $request->get('date');

        return view('tasks.form', compact('customers', 'taskTypes', 'users', 'taskDate'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request)
    {
        $taskData = $request->getData();

        // Use automatic assignment if no assignee is specified
        if (!$request->filled('assigned_to')) {
            $assignmentService = new TaskAssignmentService();
            $assignmentResult = $assignmentService->assignTask($taskData);

            if (!$assignmentResult['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('assignment_error', $assignmentResult['message'])
                    ->with('suggested_dates', $assignmentResult['suggested_dates']);
            }

            $taskData['assigned_to'] = $assignmentResult['assigned_to'];
            $successMessage = 'Task created successfully. ' . $assignmentResult['message'];
        } else {
            $successMessage = 'Task created successfully.';
        }

        DB::transaction(function () use ($taskData) {
            $task = Task::create($taskData);

            // Auto-calculate estimated cost and duration based on task type
            if ($task->taskType) {
                $task->update([
                    'estimated_cost' => $task->taskType->base_rate,
                    'estimated_duration_minutes' => $task->taskType->estimated_time_minutes,
                ]);
            }
        });

        // Determine redirect route based on where user came from
        $redirectRoute = $this->determineRedirectRoute($request);

        return redirect()->route($redirectRoute)->with('success', $successMessage);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $task->load(['customer', 'taskType', 'assignedUser']);
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $customers = Customer::where('status', 'active')->get();
        $taskTypes = TaskType::where('status', 'active')->get();
        $users = User::all();

        return view('tasks.form', compact('task', 'customers', 'taskTypes', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Task $task)
    {
        DB::transaction(function () use ($request, $task) {
            $task->update($request->getData());
        });

        // Determine redirect route based on where user came from
        $redirectRoute = $this->determineRedirectRoute($request);

        return redirect()->route($redirectRoute)->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    /**
     * Store a newly created resource in storage via API.
     */
    public function apiStore(TaskRequest $request)
    {
        $this->authorize('create', Task::class);

        try {
            $taskData = $request->getData();

            // Use automatic assignment if no assignee is specified
            if (!$request->filled('assigned_to')) {
                $assignmentService = new TaskAssignmentService();
                $assignmentResult = $assignmentService->assignTask($taskData);

                if (!$assignmentResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $assignmentResult['message'],
                        'suggested_dates' => $assignmentResult['suggested_dates']
                    ], 422);
                }

                $taskData['assigned_to'] = $assignmentResult['assigned_to'];
                $successMessage = 'Task created successfully. ' . $assignmentResult['message'];
            } else {
                $successMessage = 'Task created successfully.';
            }

            $task = DB::transaction(function () use ($taskData) {
                $task = Task::create($taskData);

                // Auto-calculate estimated cost and duration based on task type
                if ($task->taskType) {
                    $task->update([
                        'estimated_cost' => $task->taskType->base_rate,
                        'estimated_duration_minutes' => $task->taskType->estimated_time_minutes,
                    ]);
                }

                return $task;
            });

            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'task' => $task->load(['customer', 'taskType', 'assignedUser'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage via API.
     */
    public function apiUpdate(TaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        try {
            DB::transaction(function () use ($request, $task) {
                $task->update($request->getData());
            });

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully.',
                'task' => $task->fresh(['customer', 'taskType', 'assignedUser'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage via API.
     */
    public function apiDestroy(Task $task)
    {
        $this->authorize('delete', $task);

        try {
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('updateStatus', $task);

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        DB::transaction(function () use ($request, $task) {
            $data = ['status' => $request->status];

            if ($request->status === 'in_progress' && !$task->started_at) {
                $data['started_at'] = now();
            }

            if ($request->status === 'completed' && !$task->completed_at) {
                $data['completed_at'] = now();
            }

            $task->update($data);
        });

        // Handle both regular and AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully.',
                'task' => $task->fresh(['customer', 'taskType', 'assignedUser'])
            ]);
        }

        return redirect()->back()->with('success', 'Task status updated successfully.');
    }

    /**
     * Display the calendar view for tasks.
     */
    public function calendar()
    {
        $customers = Customer::where('status', 'active')->get();
        $taskTypes = TaskType::where('status', 'active')->get();
        $users = User::all();
        $statusOptions = Task::getStatusOptions();

        return view('tasks.calendar', compact('customers', 'taskTypes', 'users', 'statusOptions'));
    }

    /**
     * Get tasks data for calendar view.
     */
    public function calendarData(Request $request)
    {
        $query = Task::with(['customer', 'taskType', 'assignedUser']);

        // Filter by date range if provided
        if ($request->has('start') && $request->has('end')) {
            $query->whereBetween('task_date', [
                $request->get('start'),
                $request->get('end')
            ]);
        }

        // Filter by customer if provided
        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by task type if provided
        if ($request->has('task_type_id') && $request->task_type_id) {
            $query->where('task_type_id', $request->task_type_id);
        }

        // Filter by assigned user if provided
        if ($request->has('assigned_to') && $request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by content status
        if ($request->has('content_status')) {
            $method = !$request->content_status ? 'whereNull' : 'whereNotNull';
            $query->$method('note_content');
        }

        // Filter by publish status
        if ($request->has('publish_status')) {
            $taskTypeIds = TaskType::select('id')->whereIn('name', TaskType::PUBLISHABLE_TASK_TYPES)->pluck('id');
            $query->whereIn('task_type_id', $taskTypeIds);

            if ($request->publish_status === 0 || $request->publish_status === '0') {
                $query->where(function ($q) {
                    $q->where('publish_status', 0)
                        ->orWhereNull('publish_status');
                });
            } else {
                $query->where('publish_status', $request->publish_status);
            }
        }

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if (auth()->user()->can(\App\Policies\TaskPolicy::PERMISSION_SHOW_ASSIGNED_TASKS_ONLY, Task::class)) {
            $query->where('assigned_to', auth()->id());
        }

        $tasks = $query->get();

        $events = $tasks->map(function ($task) {
            $statusColors = [
                'pending' => [
                    'backgroundColor' => '#ffc107',  // Warning yellow
                    'borderColor' => '#e0a800',
                    'textColor' => '#212529'  // Dark text for better contrast on yellow
                ],
                'in_progress' => [
                    'backgroundColor' => '#0dcaf0',  // Info cyan
                    'borderColor' => '#0aa2c0',
                    'textColor' => '#ffffff'
                ],
                'completed' => [
                    'backgroundColor' => '#198754',  // Success green
                    'borderColor' => '#146c43',
                    'textColor' => '#ffffff'
                ],
                'cancelled' => [
                    'backgroundColor' => '#dc3545',  // Danger red
                    'borderColor' => '#b02a37',
                    'textColor' => '#ffffff'
                ]
            ];

            $colors = $statusColors[$task->status] ?? [
                'backgroundColor' => '#0d6efd',  // Primary blue
                'borderColor' => '#0a58ca',
                'textColor' => '#ffffff'
            ];

            return [
                'id' => $task->id,
                'title' => $task->customer->name . ' - ' . $task->taskType->name,
                'start' => $task->task_date->format('Y-m-d'),
                'backgroundColor' => $colors['backgroundColor'],
                'borderColor' => $colors['borderColor'],
                'textColor' => $colors['textColor'],
                'className' => 'task-' . str_replace('_', '-', $task->status),
                'extendedProps' => [
                    'task_id' => $task->id,
                    'customer_id' => $task->customer_id,
                    'customer_name' => $task->customer->name,
                    'task_type_id' => $task->task_type_id,
                    'task_type' => $task->taskType->name,
                    'assigned_to' => $task->assigned_to,
                    'assigned_to_name' => $task->assignedUser ? $task->assignedUser->name : 'Unassigned',
                    'status' => $task->status,
                    'status_label' => $task->status_label,
                    'estimated_cost' => $task->formatted_estimated_cost,
                    'estimated_duration' => $task->formatted_estimated_duration,
                    'note_content' => $task->note_content,
                    'publish_status' => $task->publish_status
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Update task date via drag and drop.
     */
    public function updateTaskDate(Request $request, Task $task)
    {
        $request->validate([
            'task_date' => 'required|date',
        ]);

        $task->update([
            'task_date' => $request->task_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task date updated successfully.'
        ]);
    }

    /**
     * Get staff workload information for a specific date
     */
    public function getStaffWorkload(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $assignmentService = new TaskAssignmentService();
        $staff = $assignmentService->getStaffWorkloadForDate(
            \Carbon\Carbon::parse($request->date)
        );

        return response()->json([
            'success' => true,
            'staff_workload' => $staff->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'current_tasks' => $user->task_stats['current_tasks'],
                    'min_tasks' => $user->task_stats['min_tasks'],
                    'max_tasks' => $user->task_stats['max_tasks'],
                    'capacity_percentage' => $user->task_stats['capacity_percentage'],
                    'can_assign_more' => $user->task_stats['can_assign_more'],
                ];
            })
        ]);
    }

    /**
     * Determine the redirect route based on where the user came from.
     */
    private function determineRedirectRoute(Request $request): string
    {
        // Check if redirect_to parameter is provided
        if ($request->has('redirect_to') && $request->redirect_to === 'calendar') {
            return 'tasks.calendar';
        }

        // Check HTTP referer header
        $referer = $request->header('referer');
        if ($referer && str_contains($referer, 'tasks-calendar')) {
            return 'tasks.calendar';
        }

        // Check if the task_date parameter suggests calendar creation
        if ($request->has('date') || $request->has('task_date')) {
            $taskDate = $request->get('date') ?? $request->get('task_date');
            $refererContainsCalendar = $referer && str_contains($referer, 'tasks-calendar');

            if ($refererContainsCalendar || $request->has('date')) {
                return 'tasks.calendar';
            }
        }

        // Default to table view
        return 'tasks.index';
    }
}