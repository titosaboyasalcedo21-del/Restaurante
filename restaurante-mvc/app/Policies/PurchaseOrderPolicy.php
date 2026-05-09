<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    /**
     * Admin bypass.
     */
    public function before(User $user): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    /**
     * Determine if user can view orders.
     */
    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Managers can only view orders of their branch
        if ($user->isManager()) {
            return $user->branch_id === $purchaseOrder->branch_id;
        }
        return false;
    }

    /**
     * Determine if user can update orders.
     */
    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->isManager()) {
            return $user->branch_id === $purchaseOrder->branch_id && 
                   in_array($purchaseOrder->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_PENDING]);
        }
        return false;
    }

    /**
     * Determine if user can delete orders.
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->isManager()) {
            return $user->branch_id === $purchaseOrder->branch_id && 
                   $purchaseOrder->status === PurchaseOrder::STATUS_DRAFT;
        }
        return false;
    }

    /**
     * Determine if user can approve/receive orders.
     */
    public function manage(User $user, PurchaseOrder $purchaseOrder): bool
    {
        if ($user->isManager()) {
            return $user->branch_id === $purchaseOrder->branch_id;
        }
        return false;
    }
}
