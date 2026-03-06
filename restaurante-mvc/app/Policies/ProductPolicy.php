<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
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
     * Determine if the user can view any products.
     * Admin: Yes (all products)
     * Manager: Yes (all products in catalog)
     * Employee: Yes (only active products in their branch)
     */
    public function viewAny(User $user): bool
    {
        return true; // All roles can view products
    }

    /**
     * Determine if the user can view the product.
     * Admin: Yes
     * Manager: Yes
     * Employee: Yes (only active products in their branch - handled in controller)
     */
    public function view(User $user, Product $product): bool
    {
        return true; // All roles can view
    }

    /**
     * Determine if user can view product cost.
     * Admin: Yes
     * Manager: No (cannot see cost)
     * Employee: No (cannot see cost)
     */
    public function viewCost(User $user, ?Product $product = null): bool
    {
        return $user->role === 'admin'; // Only admin can view cost
    }

    /**
     * Determine if user can view profit margin.
     * Admin: Yes
     * Manager: No (cannot see margin)
     * Employee: No (cannot see margin)
     */
    public function viewMargin(User $user, ?Product $product = null): bool
    {
        return $user->role === 'admin'; // Only admin can view margin
    }

    /**
     * Determine if the user can create products.
     * Admin: Yes
     * Manager: No (cannot create products in global catalog)
     * Employee: No
     */
    public function create(User $user): bool
    {
        return false; // Only admin can create
    }

    /**
     * Determine if the user can update products.
     * Admin: Yes (any product)
     * Manager: Only products assigned to their branch (stock, availability)
     * Employee: No
     */
    public function update(User $user, Product $product): bool
    {
        return false; // Handled by manageBranchProduct in controller
    }

    /**
     * Determine if user can manage products in a specific branch.
     * Managers can only manage products in their assigned branch.
     */
    public function manageBranchProduct(User $user, $branch): bool
    {
        // Admin can manage any branch (handled by before())
        // Manager can only manage their own branch
        if ($user->isManager() && $user->branch_id === $branch->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine if user can toggle product availability in their branch.
     * Admin: Yes (any branch)
     * Manager: Yes (only in their branch)
     * Employee: No
     */
    public function toggleAvailability(User $user, Product $product): bool
    {
        return false; // Handled by manageBranchProduct
    }

    /**
     * Determine if the user can delete products.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function delete(User $user, Product $product): bool
    {
        return false; // Only admin can delete
    }

    /**
     * Determine if the user can restore products.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function restore(User $user, Product $product): bool
    {
        return false; // Only admin can restore
    }

    /**
     * Determine if the user can permanently delete products.
     * Admin: Yes
     * Manager: No
     * Employee: No
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return false; // Only admin can force delete
    }

    /**
     * Determine if user can assign products to branches.
     * Admin: Yes (any branch)
     * Manager: Yes (only their branch)
     * Employee: No
     */
    public function assignToBranch(User $user, $branch): bool
    {
        if ($user->isManager() && $user->branch_id === $branch->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine if user can remove products from branches.
     * Admin: Yes (any branch)
     * Manager: Yes (only their branch)
     * Employee: No
     */
    public function removeFromBranch(User $user, $branch): bool
    {
        if ($user->isManager() && $user->branch_id === $branch->id) {
            return true;
        }
        return false;
    }
}
