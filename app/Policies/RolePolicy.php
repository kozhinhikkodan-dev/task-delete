<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    public const PERMISSION_VIEW_LIST = 'View roles list';
    public const PERMISSION_VIEW = 'View role';
    public const PERMISSION_CREATE = 'Create role';
    public const PERMISSION_UPDATE = 'Update role';
    public const PERMISSION_DELETE = 'Delete role';
    public const PERMISSION_RESTORE = 'Restore role';
    public const PERMISSION_FORCE_DELETE = 'Force delete role';

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
            info('viewAny called for ' . $user->email);
        // return true;
        return $user->can(self::PERMISSION_VIEW_LIST);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
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
    public function update(User $user, Role $role): bool
    {
        return $user->can(self::PERMISSION_UPDATE);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->can(self::PERMISSION_DELETE);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
        return $user->can(self::PERMISSION_RESTORE);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return $user->can(self::PERMISSION_FORCE_DELETE);
    }
}
