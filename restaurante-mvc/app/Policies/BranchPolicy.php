<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    /**
     * Grant admins full access to every action in this policy.
     * Returning true here short-circuits all other policy methods for admins,
     * so every method below only needs to handle manager/employee logic.
     */
    public function before(User $user): ?bool
    {
        if ($user->isAdmin()) {
            return true; // Admin bypasses all checks below
        }
        return null; // Let the specific method decide for other roles
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
     *
     * Admins bypass this via before() and can update anything.
     * Managers can only update contact info (phone, email) through the
     * dedicated updateContactInfo() policy method — not full update.
     * Employees cannot update branches at all.
     *
     * Returning false here intentionally blocks direct Route::resource update
     * for non-admins. Contact-info updates for managers go through
     * updateContactInfo() which has its own check.
     */
    public function update(User $user, Branch $branch): bool
    {
        return false; // Admins allowed via before(). Non-admins blocked here.
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
     *
     * Only admins can delete branches (allowed via before()).
     * This method returns false so managers and employees are explicitly denied.
     */
    public function delete(User $user, Branch $branch): bool
    {
        return false; // Only admin can delete (admin allowed via before()).
    }

    /**
     * Determine if the user can restore a soft-deleted branch.
     * Only admins can do this (allowed via before()).
     */
    public function restore(User $user, Branch $branch): bool
    {
        return false; // Only admin can restore (admin allowed via before()).
    }

    /**
     * Determine if the user can permanently delete branches.
     * Only admins can do this (allowed via before()).
     */
    public function forceDelete(User $user, Branch $branch): bool
    {
        return false; // Only admin can force delete (admin allowed via before()).
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
