<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
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
     * Determine if the user can view any categories.
     * Admin: Yes
     * Manager: Yes (read-only)
     * Employee: Yes (read-only)
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view
    }

    /**
     * Determine if the user can view the category.
     * Admin: Yes
     * Manager: Yes (read-only)
     * Employee: Yes (read-only)
     */
    public function view(User $user, Category $category): bool
    {
        return true; // All authenticated users can view
    }

    /**
     * Determine if the user can create categories.
     * Admin: Yes
     * Manager: No (read-only)
     * Employee: No
     */
    public function create(User $user): bool
    {
        return false; // Only admin can create
    }

    /**
     * Determine if the user can update the category.
     * Admin: Yes
     * Manager: No (read-only)
     * Employee: No
     */
    public function update(User $user, Category $category): bool
    {
        return false; // Only admin can update
    }

    /**
     * Determine if the user can delete the category.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function delete(User $user, Category $category): bool
    {
        return false; // Only admin can delete
    }

    /**
     * Determine if the user can restore the category.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function restore(User $user, Category $category): bool
    {
        return false; // Only admin can restore
    }

    /**
     * Determine if the user can permanently delete the category.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function forceDelete(User $user, Category $category): bool
    {
        return false; // Only admin can force delete
    }

    /**
     * Determine if user can toggle category status.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function toggleStatus(User $user, Category $category): bool
    {
        return false; // Only admin can toggle
    }
}
