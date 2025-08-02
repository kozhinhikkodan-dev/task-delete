<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Task;
use App\Models\TaskType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerTaskCreationService
{
    /**
     * Task type mapping for customer count fields
     */
    private const TASK_TYPE_MAPPING = [
        'total_posters' => 'GRAPHIC_DESIGN', // Maps to Graphic Design task type
        'total_video_edits' => 'VIDEO_EDIT', // Maps to Video Editing task type
        'total_blog_posts' => 'CONTENT_WRITE', // Maps to Content Writing task type
        'total_anchoring_video' => 'VIDEO_EDIT', // Maps to Video Editing task type
    ];

    /**
     * Assignment field mapping
     */
    private const ASSIGNMENT_MAPPING = [
        'total_posters' => 'posters_assigned',
        'total_video_edits' => 'video_edits_assigned',
        'total_blog_posts' => 'blog_posts_assigned',
        'total_anchoring_video' => 'anchoring_video_assigned',
    ];

    /**
     * Create tasks for a customer based on their count fields and assignments
     *
     * @param Customer $customer
     * @param array $assignmentData
     * @return array
     */
    public function createTasksForCustomer(Customer $customer, array $assignmentData): array
    {
        $createdTasks = [];
        $errors = [];

        try {
            DB::transaction(function () use ($customer, $assignmentData, &$createdTasks, &$errors) {
                foreach (self::TASK_TYPE_MAPPING as $countField => $taskTypeCode) {
                    $count = $customer->{$countField} ?? 0;
                    $assignmentField = self::ASSIGNMENT_MAPPING[$countField];
                    $assignedUserId = $assignmentData[$assignmentField] ?? null;

                    if ($count > 0) {
                        $tasks = $this->createTasksForType(
                            $customer,
                            $taskTypeCode,
                            $count,
                            $assignedUserId,
                            $countField
                        );

                        $createdTasks = array_merge($createdTasks, $tasks);
                    }
                }
            });

            return [
                'success' => true,
                'tasks_created' => count($createdTasks),
                'tasks' => $createdTasks,
                'message' => 'Successfully created ' . count($createdTasks) . ' task(s) for customer.',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating tasks: ' . $e->getMessage(),
                'tasks_created' => 0,
                'tasks' => [],
            ];
        }
    }

    /**
     * Create tasks for a specific task type
     *
     * @param Customer $customer
     * @param string $taskTypeCode
     * @param float $count
     * @param int|null $assignedUserId
     * @param string $countField
     * @return array
     */
    private function createTasksForType(
        Customer $customer,
        string $taskTypeCode,
        float $count,
        ?int $assignedUserId,
        string $countField
    ): array {
        $taskType = TaskType::where('code', $taskTypeCode)->where('status', 'active')->first();
        
        if (!$taskType) {
            throw new \Exception("Task type '{$taskTypeCode}' not found or inactive");
        }

        // Calculate task dates distributed between service start and renewal dates
        $taskDates = $this->calculateTaskDates($customer, $count);
        
        $tasks = [];
        $taskCounter = 1;

        foreach ($taskDates as $taskDate) {
            $taskData = [
                'customer_id' => $customer->id,
                'task_type_id' => $taskType->id,
                'assigned_to' => $assignedUserId,
                'task_date' => $taskDate,
                // 'note_content' => $this->generateTaskNote($countField, $taskCounter, $count),
                'note_content' => null,
                'status' => 'pending',
                'estimated_cost' => $taskType->base_rate,
                'estimated_duration_minutes' => $taskType->estimated_time_minutes,
                'is_auto' => 1
            ];

            $task = Task::create($taskData);
            $tasks[] = $task;
            $taskCounter++;
        }

        return $tasks;
    }

    /**
     * Calculate task dates distributed between service start and renewal dates
     *
     * @param Customer $customer
     * @param float $count
     * @return array
     */
    private function calculateTaskDates(Customer $customer, float $count): array
    {
        $serviceStartDate = $customer->service_start_date ? Carbon::parse($customer->service_start_date) : Carbon::now();
        $serviceRenewDate = $customer->service_renew_date ? Carbon::parse($customer->service_renew_date)->subDay() : $serviceStartDate->copy()->addMonths(12);

        // If service renewal date is before or equal to start date, default to 12 months
        if ($serviceRenewDate->lte($serviceStartDate)) {
            $serviceRenewDate = $serviceStartDate->copy()->addMonths(12);
        }

        $totalDays = $serviceStartDate->diffInDays($serviceRenewDate);
        $taskCount = (int) $count;
        $dates = [];

        if ($taskCount <= 1) {
            // Single task: place it at the start date
            $dates[] = $serviceStartDate->format('Y-m-d');
        } else {
            // Multiple tasks: distribute evenly across the period
            $interval = max(1, floor($totalDays / ($taskCount - 1)));
            
            for ($i = 0; $i < $taskCount; $i++) {
                $taskDate = $serviceStartDate->copy()->addDays($i * $interval);
                
                // Ensure the last task doesn't exceed the renewal date
                if ($i === $taskCount - 1) {
                    $taskDate = $serviceRenewDate->copy();
                }
                
                // Skip weekends (optional - move to next Monday)
                if ($taskDate->isWeekend()) {
                    $taskDate = $taskDate->next(Carbon::MONDAY);
                }
                
                $dates[] = $taskDate->format('Y-m-d');
            }
        }

        return array_unique($dates);
    }

    /**
     * Generate task note content
     *
     * @param string $countField
     * @param int $taskNumber
     * @param float $totalCount
     * @return string
     */
    private function generateTaskNote(string $countField, int $taskNumber, float $totalCount): string
    {
        $taskTypeNames = [
            'total_posters' => 'Poster',
            'total_video_edits' => 'Video Edit',
            'total_blog_posts' => 'Blog Post',
            'total_anchoring_video' => 'Anchoring Video',
        ];

        $taskName = $taskTypeNames[$countField] ?? 'Task';
        
        return "Auto-generated task: {$taskName} {$taskNumber} of {$totalCount} for customer service period.";
    }

    /**
     * Create additional tasks for a customer when counts are increased
     *
     * @param Customer $customer
     * @param array $assignmentData
     * @param array $originalCounts
     * @return array
     */
    public function createAdditionalTasksForCustomer(Customer $customer, array $assignmentData, array $originalCounts): array
    {
        $createdTasks = [];

        try {
            DB::transaction(function () use ($customer, $assignmentData, $originalCounts, &$createdTasks) {
                foreach (self::TASK_TYPE_MAPPING as $countField => $taskTypeCode) {
                    $currentCount = $customer->{$countField} ?? 0;
                    $originalCount = $originalCounts[$countField] ?? 0;
                    $additionalCount = $currentCount - $originalCount;

                    if ($additionalCount > 0) {
                        $assignmentField = self::ASSIGNMENT_MAPPING[$countField];
                        $assignedUserId = $assignmentData[$assignmentField] ?? null;

                        $tasks = $this->createTasksForType(
                            $customer,
                            $taskTypeCode,
                            $additionalCount,
                            $assignedUserId,
                            $countField
                        );

                        $createdTasks = array_merge($createdTasks, $tasks);
                    }
                }
            });

            return [
                'success' => true,
                'tasks_created' => count($createdTasks),
                'tasks' => $createdTasks,
                'message' => 'Successfully created ' . count($createdTasks) . ' additional task(s) for customer.',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating additional tasks: ' . $e->getMessage(),
                'tasks_created' => 0,
                'tasks' => [],
            ];
        }
    }

    /**
     * Get existing task assignments for a customer
     *
     * @param Customer $customer
     * @return array
     */
    public function getExistingAssignments(Customer $customer): array
    {
        $assignments = [];
        
        foreach (self::TASK_TYPE_MAPPING as $countField => $taskTypeCode) {
            $taskType = TaskType::where('code', $taskTypeCode)->first();
            if (!$taskType) continue;

            // Get the most recent assignment for this task type
            $task = Task::where('customer_id', $customer->id)
                ->where('task_type_id', $taskType->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->orderBy('created_at', 'desc')
                ->first();

            $assignmentField = self::ASSIGNMENT_MAPPING[$countField];
            $assignments[$assignmentField] = $task ? $task->assigned_to : null;
        }

        return $assignments;
    }

    /**
     * Handle comprehensive customer update scenarios
     *
     * @param Customer $customer
     * @param array $assignmentData
     * @param array $originalCounts
     * @param array $originalDates
     * @return array
     */
    public function handleCustomerUpdate(
        Customer $customer, 
        array $assignmentData, 
        array $originalCounts, 
        array $originalDates = []
    ): array {
        $results = [];
        
        try {
            DB::transaction(function () use ($customer, $assignmentData, $originalCounts, $originalDates, &$results) {
                foreach (self::TASK_TYPE_MAPPING as $countField => $taskTypeCode) {
                    $currentCount = $customer->{$countField} ?? 0;
                    $originalCount = $originalCounts[$countField] ?? 0;
                    $assignmentField = self::ASSIGNMENT_MAPPING[$countField];
                    $assignedUserId = $assignmentData[$assignmentField] ?? null;

                    $taskType = TaskType::where('code', $taskTypeCode)->where('status', 'active')->first();
                    if (!$taskType) continue;

                    // Get existing tasks for this customer and task type
                    $existingTasks = Task::where('customer_id', $customer->id)
                        ->where('task_type_id', $taskType->id)
                        ->whereIn('status', ['pending', 'in_progress'])
                        ->orderBy('task_date', 'asc')
                        ->get();

                    $result = $this->processTaskTypeUpdate(
                        $customer,
                        $taskType,
                        $existingTasks,
                        $currentCount,
                        $originalCount,
                        $assignedUserId,
                        $countField,
                        $originalDates
                    );

                    if (!empty($result['actions'])) {
                        $results[] = $result;
                    }
                }
            });

            return [
                'success' => true,
                'message' => $this->buildUpdateMessage($results),
                'details' => $results,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating customer tasks: ' . $e->getMessage(),
                'details' => [],
            ];
        }
    }

    /**
     * Process updates for a specific task type
     */
    private function processTaskTypeUpdate(
        Customer $customer,
        TaskType $taskType,
        $existingTasks,
        int $currentCount,
        int $originalCount,
        ?int $assignedUserId,
        string $countField,
        array $originalDates
    ): array {
        $actions = [];
        $existingCount = $existingTasks->count();

        // Handle count changes
        if ($currentCount > $existingCount) {
            // Create additional tasks
            $additionalCount = $currentCount - $existingCount;
            $newTasks = $this->createTasksForType(
                $customer,
                $taskType->code,
                $additionalCount,
                $assignedUserId,
                $countField
            );
            $actions[] = "Created {$additionalCount} new tasks";
        } elseif ($currentCount < $existingCount) {
            // Cancel excess tasks (only pending ones)
            $excessTasks = $existingTasks->where('status', 'pending')
                ->sortByDesc('task_date')
                ->take($existingCount - $currentCount);
            
            foreach ($excessTasks as $task) {
                $task->update(['status' => 'cancelled']);
            }
            $actions[] = "Cancelled " . $excessTasks->count() . " excess tasks";
        }

        // Handle assignment changes for remaining tasks
        if ($assignedUserId) {
            $tasksToReassign = $existingTasks->where('status', 'pending')
                ->where('assigned_to', '!=', $assignedUserId);
            
            if ($tasksToReassign->count() > 0) {
                foreach ($tasksToReassign as $task) {
                    $task->update(['assigned_to' => $assignedUserId]);
                }
                $actions[] = "Reassigned " . $tasksToReassign->count() . " tasks to new user";
            }
        }

        // Handle service date changes
        if (!empty($originalDates) && $this->serviceDatesChanged($customer, $originalDates)) {
            $pendingTasks = $existingTasks->where('status', 'pending');
            if ($pendingTasks->count() > 0) {
                $this->redistributeTaskDates($customer, $pendingTasks);
                $actions[] = "Redistributed task dates due to service period changes";
            }
        }

        return [
            'task_type' => $taskType->name,
            'count_field' => $countField,
            'actions' => $actions,
        ];
    }

    /**
     * Check if service dates changed
     */
    private function serviceDatesChanged(Customer $customer, array $originalDates): bool
    {
        return $customer->service_start_date != $originalDates['service_start_date'] ||
               $customer->service_renew_date != $originalDates['service_renew_date'];
    }

    /**
     * Redistribute task dates for existing tasks
     */
    private function redistributeTaskDates(Customer $customer, $tasks)
    {
        $taskCount = $tasks->count();
        if ($taskCount === 0) return;

        $newDates = $this->calculateTaskDates($customer, $taskCount);
        
        foreach ($tasks->values() as $index => $task) {
            if (isset($newDates[$index])) {
                $task->update(['task_date' => $newDates[$index]]);
            }
        }
    }

    /**
     * Build update message from results
     */
    private function buildUpdateMessage(array $results): string
    {
        if (empty($results)) {
            return '';
        }

        $messages = [];
        foreach ($results as $result) {
            if (!empty($result['actions'])) {
                $taskType = $result['task_type'];
                $actionList = implode(', ', $result['actions']);
                $messages[] = "{$taskType}: {$actionList}";
            }
        }

        return empty($messages) ? '' : 'Task updates: ' . implode('. ', $messages) . '.';
    }
} 