<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    public const PERMISSION_VIEW_LIST = 'View tasks list';
    public const PERMISSION_VIEW = 'View task';
    public const PERMISSION_CREATE = 'Create task';
    public const PERMISSION_UPDATE = 'Update task';
    public const PERMISSION_UPDATE_STATUS = 'Update task status';
    public const PERMISSION_DELETE = 'Delete task';
    public const PERMISSION_RESTORE = 'Restore task';
    public const PERMISSION_FORCE_DELETE = 'Force delete task';
    public const PERMISSION_SHOW_ASSIGNED_TASKS_ONLY = 'Show assigned tasks only';

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(self::PERMISSION_VIEW_LIST);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->can(self::PERMISSION_VIEW);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(self::PERMISSION_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Block edit operations for Staff users
        if ($user->hasRole('Staff')) {
            return false;
        }
        
        return $user->can(self::PERMISSION_UPDATE);
    }

    /**
     * Determine whether the user can update the task status.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        return $user->can(self::PERMISSION_UPDATE_STATUS) || $user->can(self::PERMISSION_UPDATE);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Block delete operations for Staff users
        if ($user->hasRole('Staff')) {
            return false;
        }
        
        return $user->can(self::PERMISSION_DELETE);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->can(self::PERMISSION_RESTORE);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->can(self::PERMISSION_FORCE_DELETE);
    }

     public function showAssignedTasksOnly(User $user, Task $task): bool
    {
        return $user->can(self::PERMISSION_SHOW_ASSIGNED_TASKS_ONLY);
    }
} 