<?php

namespace App\Policies;

use App\Models\TaskType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskTypePolicy
{
    public const PERMISSION_VIEW_LIST = 'View task types list';
    public const PERMISSION_VIEW = 'View task type';
    public const PERMISSION_CREATE = 'Create task type';
    public const PERMISSION_UPDATE = 'Update task type';
    public const PERMISSION_DELETE = 'Delete task type';
    public const PERMISSION_RESTORE = 'Restore task type';
    public const PERMISSION_FORCE_DELETE = 'Force delete task type';

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
    public function view(User $user, TaskType $taskType): bool
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
    public function update(User $user, TaskType $taskType): bool
    {
        return $user->can(self::PERMISSION_UPDATE);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TaskType $taskType): bool
    {
        return $user->can(self::PERMISSION_DELETE);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TaskType $taskType): bool
    {
        return $user->can(self::PERMISSION_RESTORE);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TaskType $taskType): bool
    {
        return $user->can(self::PERMISSION_FORCE_DELETE);
    }
}
