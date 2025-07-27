<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TaskAssignmentService
{
    /**
     * Automatically assign a task to the most suitable staff member
     *
     * @param array $taskData
     * @return array
     */
    public function assignTask(array $taskData): array
    {
        $taskDate = Carbon::parse($taskData['task_date']);
        $dayOfWeek = $taskDate->format('l'); // Monday, Tuesday, etc.
        
        // Find available staff members for the given date
        $availableStaff = $this->getAvailableStaff($dayOfWeek);
        
        if ($availableStaff->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No staff members are available on ' . $dayOfWeek . '.',
                'suggested_dates' => $this->getSuggestedDates($taskDate)
            ];
        }
        
        // Check capacity for each staff member
        $staffWithCapacity = $this->filterStaffByCapacity($availableStaff, $taskDate);
        
        if ($staffWithCapacity->isEmpty()) {
            return [
                'success' => false,
                'message' => 'All staff members are at maximum capacity on ' . $taskDate->format('M d, Y') . '.',
                'suggested_dates' => $this->getSuggestedDates($taskDate, $availableStaff)
            ];
        }
        
        // Select the best staff member based on workload distribution
        $selectedStaff = $this->selectBestStaff($staffWithCapacity, $taskDate);
        
        return [
            'success' => true,
            'assigned_to' => $selectedStaff->id,
            'assigned_to_name' => $selectedStaff->name,
            'message' => 'Task automatically assigned to ' . $selectedStaff->name . '.'
        ];
    }
    
    /**
     * Get staff members available on a specific day
     *
     * @param string $dayOfWeek
     * @return Collection
     */
    protected function getAvailableStaff(string $dayOfWeek): Collection
    {
        return User::role('Staff')
            ->where('status', 'active')
            ->whereJsonContains('available_days', $dayOfWeek)
            ->whereNotNull('min_task_per_day')
            ->whereNotNull('max_task_per_day')
            ->get();
    }
    
    /**
     * Filter staff members by their capacity on a specific date
     *
     * @param Collection $staff
     * @param Carbon $taskDate
     * @return Collection
     */
    protected function filterStaffByCapacity(Collection $staff, Carbon $taskDate): Collection
    {
        return $staff->filter(function ($user) use ($taskDate) {
            $currentTaskCount = $this->getCurrentTaskCount($user->id, $taskDate);
            return $currentTaskCount < $user->max_task_per_day;
        });
    }
    
    /**
     * Get current task count for a user on a specific date
     *
     * @param int $userId
     * @param Carbon $date
     * @return int
     */
    protected function getCurrentTaskCount(int $userId, Carbon $date): int
    {
        return Task::where('assigned_to', $userId)
            ->whereDate('task_date', $date)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->count();
    }
    
    /**
     * Select the best staff member based on workload distribution
     *
     * @param Collection $staff
     * @param Carbon $taskDate
     * @return User
     */
    protected function selectBestStaff(Collection $staff, Carbon $taskDate): User
    {
        // Sort by current task count (ascending) and then by distance from min_task_per_day
        return $staff->sortBy(function ($user) use ($taskDate) {
            $currentCount = $this->getCurrentTaskCount($user->id, $taskDate);
            $minTasks = $user->min_task_per_day;
            
            // Priority: users below min_task_per_day first, then by current count
            if ($currentCount < $minTasks) {
                return $currentCount; // Lower count = higher priority
            }
            
            return $minTasks + $currentCount; // Above min, just use current count
        })->first();
    }
    
    /**
     * Get suggested dates when staff are available
     *
     * @param Carbon $originalDate
     * @param Collection|null $staff
     * @return array
     */
    protected function getSuggestedDates(Carbon $originalDate, Collection $staff = null): array
    {
        $suggestedDates = [];
        $currentDate = $originalDate->copy()->addDay();
        $maxDaysToCheck = 14; // Check up to 2 weeks ahead
        
        for ($i = 0; $i < $maxDaysToCheck; $i++) {
            $dayOfWeek = $currentDate->format('l');
            
            if ($staff === null) {
                $dailyStaff = $this->getAvailableStaff($dayOfWeek);
            } else {
                $dailyStaff = $staff->filter(function ($user) use ($dayOfWeek) {
                    return in_array($dayOfWeek, $user->available_days ?? []);
                });
            }
            
            if ($dailyStaff->isNotEmpty()) {
                $staffWithCapacity = $this->filterStaffByCapacity($dailyStaff, $currentDate);
                
                if ($staffWithCapacity->isNotEmpty()) {
                    $suggestedDates[] = [
                        'date' => $currentDate->format('Y-m-d'),
                        'formatted_date' => $currentDate->format('M d, Y'),
                        'day_of_week' => $dayOfWeek,
                        'available_staff_count' => $staffWithCapacity->count()
                    ];
                }
            }
            
            $currentDate->addDay();
            
            // Stop after finding 5 suggested dates
            if (count($suggestedDates) >= 5) {
                break;
            }
        }
        
        return $suggestedDates;
    }
    
    /**
     * Check if a specific staff member can be assigned more tasks on a date
     *
     * @param int $userId
     * @param Carbon $date
     * @return bool
     */
    public function canAssignMoreTasks(int $userId, Carbon $date): bool
    {
        $user = User::find($userId);
        
        if (!$user || !$user->hasRole('Staff') || $user->status !== 'active') {
            return false;
        }
        
        $dayOfWeek = $date->format('l');
        if (!in_array($dayOfWeek, $user->available_days ?? [])) {
            return false;
        }
        
        $currentTaskCount = $this->getCurrentTaskCount($userId, $date);
        return $currentTaskCount < $user->max_task_per_day;
    }
    
    /**
     * Get task assignment statistics for a user on a specific date
     *
     * @param int $userId
     * @param Carbon $date
     * @return array
     */
    public function getTaskAssignmentStats(int $userId, Carbon $date): array
    {
        $user = User::find($userId);
        
        if (!$user) {
            return [];
        }
        
        $currentCount = $this->getCurrentTaskCount($userId, $date);
        
        return [
            'current_tasks' => $currentCount,
            'min_tasks' => $user->min_task_per_day,
            'max_tasks' => $user->max_task_per_day,
            'capacity_percentage' => $user->max_task_per_day > 0 ? 
                round(($currentCount / $user->max_task_per_day) * 100, 1) : 0,
            'can_assign_more' => $this->canAssignMoreTasks($userId, $date)
        ];
    }
    
    /**
     * Get all staff members with their current task loads for a specific date
     *
     * @param Carbon $date
     * @return Collection
     */
    public function getStaffWorkloadForDate(Carbon $date): Collection
    {
        $dayOfWeek = $date->format('l');
        $staff = $this->getAvailableStaff($dayOfWeek);
        
        return $staff->map(function ($user) use ($date) {
            $stats = $this->getTaskAssignmentStats($user->id, $date);
            $user->task_stats = $stats;
            return $user;
        });
    }
} 