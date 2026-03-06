<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class InventoryPolicy
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
     * Determine if user can view inventory.
     * Admin: Yes (all branches)
     * Manager: Yes (only their branch)
     * Employee: Yes (only their branch)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if user can view consolidated inventory.
     * Admin: Yes
     * Manager: No (only their branch)
     * Employee: No (only their branch)
     */
    public function viewConsolidated(User $user): bool
    {
        return false; // Only admin can see consolidated
    }

    /**
     * Determine if user can perform 'in' movements.
     * Admin: Yes
     * Manager: Yes (only their branch)
     * Employee: No
     */
    public function createIn(User $user, Branch $branch = null): bool
    {
        return false; // Only admin and manager
    }

    /**
     * Determine if user can perform 'out' movements.
     * Admin: Yes
     * Manager: Yes (only their branch)
     * Employee: Yes (only their branch - for sales)
     */
    public function createOut(User $user, Branch $branch = null): bool
    {
        return true; // All roles can register sales
    }

    /**
     * Determine if user can perform 'adjust' movements.
     * Admin: Yes
     * Manager: Yes (only their branch)
     * Employee: No
     */
    public function createAdjust(User $user, Branch $branch = null): bool
    {
        return false; // Only admin and manager
    }

    /**
     * Determine if user can perform 'transfer' movements.
     * Admin: Yes
     * Manager: Yes (can initiate transfer from their branch)
     * Employee: No
     */
    public function createTransfer(User $user, Branch $branch = null): bool
    {
        return false; // Only admin and manager
    }

    /**
     * Determine if user can view movement history.
     * Admin: Yes (all branches)
     * Manager: Yes (only their branch)
     * Employee: Yes (only their own movements)
     */
    public function viewMovements(User $user): bool
    {
        return true;
    }

    /**
     * Determine if user can view reports.
     * Admin: Yes (global)
     * Manager: Yes (only their branch)
     * Employee: No
     */
    public function viewReports(User $user): bool
    {
        return false; // Only admin and manager
    }

    /**
     * Determine if user can view low stock alerts.
     * Admin: Yes (all branches)
     * Manager: Yes (only their branch)
     * Employee: Yes (only their branch)
     */
    public function viewLowStock(User $user): bool
    {
        return true;
    }

    /**
     * Check if user can perform inventory operations in a specific branch.
     */
    public function canManageBranch(User $user, Branch $branch): bool
    {
        // Admin can manage any (handled by before())
        // Manager can only manage their assigned branch
        if ($user->isManager() && $user->branch_id === $branch->id) {
            return true;
        }
        return false;
    }
}
