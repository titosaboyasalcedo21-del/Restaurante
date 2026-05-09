<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with stats scoped to the authenticated user's role.
     *
     * - Admin   → sees all branches, all movements, global stats.
     * - Manager → sees only their assigned branch.
     * - Employee → redirected to inventory index.
     */
    public function index(): View|RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Employees have no dashboard; send them to inventory
        if ($user->isEmployee()) {
            return redirect()->route('inventory.index');
        }

        $isAdmin = $user->isAdmin();

        // ── Stats cards ──────────────────────────────────────────────────────
        $stats = [
            // Active products (admin sees all; manager sees only active)
            'products' => Product::when(!$isAdmin, fn($q) => $q->active())->count(),

            // Branch count (admin sees total; manager always manages 1)
            'branches' => $isAdmin ? Branch::count() : 1,

            // Products with stock at or below their minimum_stock threshold
            'lowStock' => Product::whereHas('branches', function ($q) use ($isAdmin, $user) {
                $q->whereRaw('branch_product.stock <= products.minimum_stock');
                if (!$isAdmin) {
                    $q->where('branches.id', $user->branch_id);
                }
            })->count(),

            // Movements registered today, scoped to branch for managers
            'movementsToday' => InventoryMovement::whereDate('created_at', today())
                ->when(!$isAdmin, fn($q) => $q->where('branch_id', $user->branch_id))
                ->count(),
        ];

        // ── Recent movements (last 8) ─────────────────────────────────────────
        $recentMovements = InventoryMovement::with(['product', 'branch', 'user'])
            ->when(!$isAdmin, fn($q) => $q->where('branch_id', $user->branch_id))
            ->latest()
            ->limit(8)
            ->get();

        // ── Low-stock items panel (top 5 lowest) ──────────────────────────────
        $lowStockItems = Product::with(['category', 'branches'])
            ->whereHas('branches', function ($q) use ($isAdmin, $user) {
                $q->whereRaw('branch_product.stock <= products.minimum_stock');
                if (!$isAdmin) {
                    $q->where('branches.id', $user->branch_id);
                }
            })
            ->get()
            ->map(function ($product) use ($isAdmin, $user) {
                // For managers, only show their branch stock. For admins, show total.
                $product->total_stock = $isAdmin 
                    ? $product->branches->sum('pivot.stock')
                    : $product->branches->where('id', $user->branch_id)->first()?->pivot->stock ?? 0;
                return $product;
            })
            ->sortBy('total_stock')
            ->take(5);

        return view('dashboard', compact('stats', 'recentMovements', 'lowStockItems'));
    }
}
