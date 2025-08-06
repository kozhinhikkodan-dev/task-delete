<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
        info("Task Type: " . $taskType->name);
        info("Count: " . $count);
        // Calculate task dates distributed between service start and renewal dates
        $taskDates = $this->calculateTaskDates($customer, $count, $assignedUserId);

        $tasks = [];
        $taskCounter = 1;

        info("Creating " . count($taskDates) . " tasks for count field: " . $countField);
        info("Task Dates: " . implode(', ', $taskDates));

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
            $tasks[] = $task->toArray();
            $taskCounter++;
        }

        return $tasks;
    }

    /**
     * Calculate task dates distributed between service start and renewal dates
     * Now considers existing task load to avoid overloading days
     *
     * @param Customer $customer
     * @param float $count
     * @param int|null $assignedUserId
     * @return array
     */
    private function calculateTaskDates(Customer $customer, float $count, ?int $assignedUserId): array
    {
        $serviceStartDate = $customer->service_start_date ? Carbon::parse($customer->service_start_date) : Carbon::now();
        $serviceRenewDate = $customer->service_renew_date ? Carbon::parse($customer->service_renew_date) : $serviceStartDate->copy()->addMonths(12);

        // If service renewal date is before or equal to start date, default to 12 months
        if ($serviceRenewDate->lte($serviceStartDate)) {
            $serviceRenewDate = $serviceStartDate->copy()->addMonths(12);
        }

        $taskCount = (int) $count;
        if ($taskCount <= 0) {
            return [];
        }

        // Get available work days
        $availableDays = $this->getAvailableWorkDays($assignedUserId);

        // Generate all available work days in the date range
        $workDays = $this->generateWorkDaysInRange($serviceStartDate, $serviceRenewDate, $availableDays);

        if (empty($workDays)) {
            // If no work days available, return the start date repeated
            return array_fill(0, $taskCount, $serviceStartDate->format('Y-m-d'));
        }

        // Get existing task load for the assigned user
        $existingTaskLoad = $this->getExistingTaskLoad($assignedUserId, $serviceStartDate, $serviceRenewDate);

        // Distribute tasks considering existing load
        return $this->distributeTasksWithLoadConsideration($workDays, $taskCount, $existingTaskLoad);
    }

    /**
     * Get existing task load for a user in the given date range
     *
     * @param int|null $assignedUserId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getExistingTaskLoad(?int $assignedUserId, Carbon $startDate, Carbon $endDate): array
    {
        if (!$assignedUserId) {
            return [];
        }

        $existingTasks = Task::where('assigned_to', $assignedUserId)
            ->whereBetween('task_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw('task_date, COUNT(*) as task_count')
            ->groupBy('task_date')
            ->get()
            ->keyBy('task_date')
            ->map(function ($item) {
                return $item->task_count;
            })
            ->toArray();

        return $existingTasks;
    }

    /**
     * Distribute tasks considering existing task load and maximum tasks per day
     *
     * @param array $workDays
     * @param int $taskCount
     * @param array $existingTaskLoad
     * @return array
     */
    private function distributeTasksWithLoadConsideration(array $workDays, int $taskCount, array $existingTaskLoad = []): array
    {
        $maxTasksPerDay = 4; // Configurable maximum tasks per day
        $dates = [];

        // Create a map of available capacity for each work day
        $dayCapacity = [];
        foreach ($workDays as $workDay) {
            $existingCount = $existingTaskLoad[$workDay] ?? 0;
            $availableCapacity = max(0, $maxTasksPerDay - $existingCount);
            $dayCapacity[$workDay] = $availableCapacity;
        }

        // Sort work days by available capacity (descending) to prioritize less loaded days
        arsort($dayCapacity);
        $sortedWorkDays = array_keys($dayCapacity);

        $remainingTasks = $taskCount;
        $currentDayIndex = 0;

        while ($remainingTasks > 0 && $currentDayIndex < count($sortedWorkDays)) {
            $currentDay = $sortedWorkDays[$currentDayIndex];
            $availableCapacity = $dayCapacity[$currentDay];

            if ($availableCapacity > 0) {
                // Add as many tasks as possible to this day
                $tasksToAdd = min($remainingTasks, $availableCapacity);

                for ($i = 0; $i < $tasksToAdd; $i++) {
                    $dates[] = $currentDay;
                }

                $remainingTasks -= $tasksToAdd;
                $dayCapacity[$currentDay] -= $tasksToAdd;
            }

            $currentDayIndex++;
        }

        // If we still have remaining tasks and exhausted all work days, 
        // distribute them across the least loaded days
        if ($remainingTasks > 0) {
            $dates = array_merge($dates, $this->distributeRemainingTasks($sortedWorkDays, $remainingTasks, $maxTasksPerDay, $existingTaskLoad));
        }

        return $dates;
    }

    /**
     * Distribute remaining tasks when all days are at capacity
     *
     * @param array $workDays
     * @param int $remainingTasks
     * @param int $maxTasksPerDay
     * @param array $existingTaskLoad
     * @return array
     */
    private function distributeRemainingTasks(array $workDays, int $remainingTasks, int $maxTasksPerDay, array $existingTaskLoad = []): array
    {
        $dates = [];
        $workDayCount = count($workDays);

        if ($workDayCount === 0) {
            return $dates;
        }

        // Distribute remaining tasks evenly across all work days, considering current load
        $tasksPerDay = (int) floor($remainingTasks / $workDayCount);
        $extraTasks = $remainingTasks % $workDayCount;

        foreach ($workDays as $index => $workDay) {
            $existingCount = $existingTaskLoad[$workDay] ?? 0;
            $totalCurrentLoad = $existingCount;

            // Only add tasks if we haven't exceeded the limit
            $tasksForThisDay = $tasksPerDay;

            if ($extraTasks > 0 && $totalCurrentLoad < $maxTasksPerDay) {
                $tasksForThisDay++;
                $extraTasks--;
            }

            // Ensure we don't exceed max tasks per day
            $availableSlots = max(0, $maxTasksPerDay - $totalCurrentLoad);
            $tasksForThisDay = min($tasksForThisDay, $availableSlots);

            for ($i = 0; $i < $tasksForThisDay; $i++) {
                $dates[] = $workDay;
            }
        }

        return $dates;
    }

    /**
     * Get available work days based on assigned user or default work days
     *
     * @param int|null $assignedUserId
     * @return array
     */
    private function getAvailableWorkDays(?int $assignedUserId): array
    {
        if ($assignedUserId) {
            $staff = User::find($assignedUserId);
            if ($staff && !empty($staff->available_days)) {
                return $staff->available_days;
            }
        }

        // Default work days (Monday to Friday)
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }

    /**
     * Generate all available work days in the given date range
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $availableDays
     * @return array
     */
    private function generateWorkDaysInRange(Carbon $startDate, Carbon $endDate, array $availableDays): array
    {
        $workDays = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dayName = $currentDate->format('l'); // Full day name (Monday, Tuesday, etc.)

            if (in_array($dayName, $availableDays)) {
                $workDays[] = $currentDate->format('Y-m-d');
            }

            $currentDate->addDay();
        }

        return $workDays;
    }

    /**
     * Distribute tasks evenly across available work days
     *
     * @param array $workDays
     * @param int $taskCount
     * @return array
     */
    private function distributeTasksEvenly(array $workDays, int $taskCount): array
    {
        $workDayCount = count($workDays);

        if ($workDayCount === 0) {
            return [];
        }

        if ($taskCount <= $workDayCount) {
            // If we have more work days than tasks, distribute evenly
            return $this->distributeSparseTasks($workDays, $taskCount);
        } else {
            // If we have more tasks than work days, some days will have multiple tasks
            return $this->distributeDenseTasks($workDays, $taskCount);
        }
    }

    /**
     * Distribute tasks when there are more work days than tasks
     *
     * @param array $workDays
     * @param int $taskCount
     * @return array
     */
    private function distributeSparseTasks(array $workDays, int $taskCount): array
    {
        $workDayCount = count($workDays);
        $step = $workDayCount / $taskCount;
        $dates = [];

        for ($i = 0; $i < $taskCount; $i++) {
            $index = (int) round($i * $step);
            // Ensure index is within bounds
            $index = min($index, $workDayCount - 1);
            $dates[] = $workDays[$index];
        }

        return $dates;
    }

    /**
     * Distribute tasks when there are more tasks than work days
     *
     * @param array $workDays
     * @param int $taskCount
     * @return array
     */
    private function distributeDenseTasks(array $workDays, int $taskCount): array
    {
        $workDayCount = count($workDays);
        $tasksPerDay = (int) floor($taskCount / $workDayCount);
        $remainingTasks = $taskCount % $workDayCount;

        $dates = [];

        foreach ($workDays as $index => $workDay) {
            $tasksForThisDay = $tasksPerDay;

            // Distribute remaining tasks to the first few days
            if ($remainingTasks > 0) {
                $tasksForThisDay++;
                $remainingTasks--;
            }

            // Add the work day multiple times based on tasks needed
            for ($i = 0; $i < $tasksForThisDay; $i++) {
                $dates[] = $workDay;
            }
        }

        return $dates;
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
            if (!$taskType)
                continue;

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
                    if (!$taskType)
                        continue;

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
        if ($taskCount === 0)
            return;

        $newDates = $this->calculateTaskDates($customer, $taskCount, $tasks->first()->assigned_to);

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