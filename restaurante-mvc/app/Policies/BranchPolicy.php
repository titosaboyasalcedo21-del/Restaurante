<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
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
     * Determine if the user can view any branches.
     * Admin: Yes (all branches)
     * Manager: Yes (only their branch)
     * Employee: Yes (only their branch - name and address)
     */
    public function viewAny(User $user): bool
    {
        return true; // All roles can view
    }

    /**
     * Determine if the user can view the branch.
     * Admin: Yes (any branch)
     * Manager: Yes (only their branch)
     * Employee: Yes (only their branch)
     */
    public function view(User $user, Branch $branch): bool
    {
        // Admin can view any (handled by before())
        // Manager and Employee can only view their assigned branch
        if ($user->isManager() || $user->isEmployee()) {
            return $user->branch_id === $branch->id;
        }
        return false;
    }

    /**
     * Determine if user can view full branch details (including metrics).
     * Admin: Yes
     * Manager: Yes (only their branch)
     * Employee: No (only name and address)
     */
    public function viewFullDetails(User $user, Branch $branch): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ($user->isManager() && $user->branch_id === $branch->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine if the user can create branches.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function create(User $user): bool
    {
        return false; // Only admin can create
    }

    /**
     * Determine if the user can update branches.
     * Admin: Yes (any branch)
     * Manager: Only contact info (phone, email) of their branch
     * Employee: No
     */
    public function update(User $user, Branch $branch): bool
    {
        return false; // Handled by updateContactInfo
    }

    /**
     * Determine if user can update contact info only.
     * Admin: Yes (any field)
     * Manager: Yes (only phone, email for their branch)
     * Employee: No
     */
    public function updateContactInfo(User $user, Branch $branch): bool
    {
        // Admin can update any (handled by before())
        // Manager can only update their branch's contact info
        if ($user->isManager() && $user->branch_id === $branch->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine if the user can delete branches.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function delete(User $user, Branch $branch): bool
    {
        return false; // Only admin can delete
    }

    /**
     * Determine if the user can restore branches.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function restore(User $user, Branch $branch): bool
    {
        return false; // Only admin can restore
    }

    /**
     * Determine if the user can permanently delete branches.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function forceDelete(User $user, Branch $branch): bool
    {
        return false; // Only admin can force delete
    }

    /**
     * Determine if the user can manage products in a specific branch.
     * Admin: Yes (any branch)
     * Manager: Yes (only their branch)
     * Employee: No
     */
    public function manageProducts(User $user, Branch $branch): bool
    {
        // Admin can manage any (handled by before())
        // Manager can only manage their assigned branch
        if ($user->isManager() && $user->branch_id === $branch->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine if user can view employees of a branch.
     * Admin: Yes
     * Manager: Yes (employees of their branch)
     * Employee: No
     */
    public function viewEmployees(User $user, Branch $branch): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ($user->isManager() && $user->branch_id === $branch->id) {
            return true;
        }
        return false;
    }
}
