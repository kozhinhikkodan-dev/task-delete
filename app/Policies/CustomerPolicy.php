<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    public const PERMISSION_VIEW_LIST = 'View customers list';
    public const PERMISSION_VIEW = 'View customer';
    public const PERMISSION_CREATE = 'Create customer';
    public const PERMISSION_UPDATE = 'Update customer';
    public const PERMISSION_DELETE = 'Delete customer';
    public const PERMISSION_RESTORE = 'Restore customer';
    public const PERMISSION_FORCE_DELETE = 'Force delete customer';

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
    public function view(User $user, Customer $customer): bool
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
    public function update(User $user, Customer $customer): bool
    {
        return $user->can(self::PERMISSION_UPDATE);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        return $user->can(self::PERMISSION_DELETE);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Customer $customer): bool
    {
        return $user->can(self::PERMISSION_RESTORE);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->can(self::PERMISSION_FORCE_DELETE);
    }
}
