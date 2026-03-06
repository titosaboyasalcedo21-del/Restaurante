<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Give full access to admins
     */
    public function before(User $user): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    /**
     * Determine if user can view any users.
     * Admin: Yes (all users)
     * Manager: Yes (only employees of their branch)
     * Employee: No
     */
    public function viewAny(User $user): bool
    {
        return false; // Handled by viewEmployees
    }

    /**
     * Determine if user can view a specific user.
     * Admin: Yes
     * Manager: Yes (employees of their branch)
     * Employee: No
     */
    public function view(User $user, User $targetUser): bool
    {
        return false;
    }

    /**
     * Determine if user can view employees of a branch.
     * Admin: Yes
     * Manager: Yes (employees of their branch)
     * Employee: No
     */
    public function viewEmployees(User $user, $branch = null): bool
    {
        // Admin can view all (handled by before())
        // Manager can view employees of their branch
        if ($user->isManager()) {
            if ($branch) {
                return $user->branch_id === $branch->id;
            }
            return true;
        }
        return false;
    }

    /**
     * Determine if user can create users.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function create(User $user): bool
    {
        return false; // Only admin
    }

    /**
     * Determine if user can update users.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function update(User $user, User $targetUser): bool
    {
        return false;
    }

    /**
     * Determine if user can assign roles.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function assignRole(User $user, User $targetUser): bool
    {
        return false; // Only admin
    }

    /**
     * Determine if user can assign manager to branch.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function assignBranch(User $user, User $targetUser): bool
    {
        return false; // Only admin
    }

    /**
     * Determine if user can delete users.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function delete(User $user, User $targetUser): bool
    {
        return false; // Only admin
    }

    /**
     * Determine if user can reset passwords.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function resetPassword(User $user, User $targetUser): bool
    {
        return false; // Only admin
    }
}
