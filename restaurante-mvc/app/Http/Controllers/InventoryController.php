<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Get the branch ID for the current manager, or null for admin
     */
    private function getManagerBranchId(): ?int
    {
        $user = auth()->user();
        if ($user && $user->isManager() && $user->branch_id) {
            return $user->branch_id;
        }
        return null;
    }

    public function index(Request $request)
    {
        $managerBranchId = $this->getManagerBranchId();

        // Managers can only see their branch
        $branchesQuery = Branch::active()->with(['products' => function ($q) {
            $q->with('category')->orderBy('name');
        }])->orderBy('name');

        if ($managerBranchId) {
            $branchesQuery->where('id', $managerBranchId);
        }

        $branches = $branchesQuery->get();

        $selectedBranch = null;
        if ($request->filled('branch_id')) {
            // Only allow viewing their own branch for managers
            $branchId = $managerBranchId ? $managerBranchId : $request->branch_id;
            $selectedBranch = Branch::with(['products.category'])->find($branchId);
        } elseif ($managerBranchId) {
            $selectedBranch = $branches->first();
        }

        return view('inventory.index', compact('branches', 'selectedBranch'));
    }

    public function movements(Request $request)
    {
        $managerBranchId = $this->getManagerBranchId();

        $query = InventoryMovement::with(['product', 'branch', 'user'])
            ->latest();

        // Managers can only see their branch's movements
        if ($managerBranchId) {
            $query->where('branch_id', $managerBranchId);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->paginate(20)->withQueryString();

        // Managers only see their branch
        if ($managerBranchId) {
            $branches = Branch::active()->where('id', $managerBranchId)->get();
        } else {
            $branches = Branch::active()->orderBy('name')->get();
        }

        $products = Product::active()->orderBy('name')->get();

        return view('inventory.movements', compact('movements', 'branches', 'products'));
    }

    public function adjust(Request $request)
    {
        $user = auth()->user();
        $managerBranchId = $this->getManagerBranchId();

        // Managers can only adjust inventory in their branch
        if ($managerBranchId) {
            $branches = Branch::active()->where('id', $managerBranchId)->get();
        } else {
            $branches = Branch::active()->orderBy('name')->get();
        }

        $products = Product::active()->orderBy('name')->get();

        if ($request->isMethod('post')) {
            // Employees can only register 'out' movements (sales)
            $allowedTypes = $user->isEmployee() ? ['out'] : ['in', 'out', 'adjust', 'transfer'];

            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'branch_id'  => 'required|exists:branches,id',
                'type'       => 'required|in:' . implode(',', $allowedTypes),
                'quantity'   => 'required|integer|min:1',
                'reason'     => 'nullable|string|max:255',
                'reference'  => 'nullable|string|max:100',
                'destination_branch_id' => 'required_if:type,transfer|different:branch_id|exists:branches,id',
            ]);

            // Managers can only adjust their own branch
            if ($managerBranchId && $validated['branch_id'] != $managerBranchId) {
                abort(403, 'Solo puedes ajustar inventario de tu sucursal.');
            }

            // Employees can only adjust their branch
            if ($user->isEmployee() && $user->branch_id && $validated['branch_id'] != $user->branch_id) {
                abort(403, 'Solo puedes registrar ventas en tu sucursal.');
            }

            // Validate product exists in branch for non-'in' movements
            if ($validated['type'] !== 'in') {
                $exists = DB::table('branch_product')
                    ->where('branch_id', $validated['branch_id'])
                    ->where('product_id', $validated['product_id'])
                    ->exists();

                if (!$exists) {
                    return back()->with('error', 'El producto no existe en esta sucursal.');
                }
            }

            // For transfers, validate product exists in destination
            if ($validated['type'] === 'transfer') {
                $destExists = DB::table('branch_product')
                    ->where('branch_id', $validated['destination_branch_id'])
                    ->where('product_id', $validated['product_id'])
                    ->exists();

                if (!$destExists) {
                    // Create the product in destination branch
                    DB::table('branch_product')->insert([
                        'branch_id' => $validated['destination_branch_id'],
                        'product_id' => $validated['product_id'],
                        'stock' => 0,
                        'is_available' => true,
                    ]);
                }
            }

            DB::transaction(function () use ($validated) {
                // Handle transfer - two stock changes
                if ($validated['type'] === 'transfer') {
                    // Origin branch - out
                    $this->applyStockChange(
                        $validated['product_id'],
                        $validated['branch_id'],
                        InventoryMovement::TYPE_OUT,
                        $validated['quantity'],
                        $validated['reason'],
                        $validated['reference']
                    );

                    // Destination branch - in
                    $this->applyStockChange(
                        $validated['product_id'],
                        $validated['destination_branch_id'],
                        InventoryMovement::TYPE_IN,
                        $validated['quantity'],
                        $validated['reason'],
                        $validated['reference']
                    );
                } else {
                    // Regular movement
                    $this->applyStockChange(
                        $validated['product_id'],
                        $validated['branch_id'],
                        $validated['type'],
                        $validated['quantity'],
                        $validated['reason'],
                        $validated['reference']
                    );
                }
            });

            return redirect()->route('inventory.movements')->with('success', 'Movimiento registrado exitosamente.');
        }

        return view('inventory.adjust', compact('branches', 'products'));
    }

    /**
     * Apply stock change and create inventory movement
     */
    private function applyStockChange(
        int $productId,
        int $branchId,
        string $type,
        int $quantity,
        ?string $reason = null,
        ?string $reference = null
    ): void {
        $pivot = DB::table('branch_product')
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->first();

        $previousStock = $pivot ? $pivot->stock : 0;

        $newStock = match($type) {
            InventoryMovement::TYPE_IN     => $previousStock + $quantity,
            InventoryMovement::TYPE_OUT    => max(0, $previousStock - $quantity),
            InventoryMovement::TYPE_ADJUST => max(0, $quantity),
            InventoryMovement::TYPE_TRANSFER => max(0, $previousStock - $quantity),
            default                       => $previousStock,
        };

        DB::table('branch_product')->updateOrInsert(
            ['branch_id' => $branchId, 'product_id' => $productId],
            ['stock' => $newStock, 'is_available' => true]
        );

        InventoryMovement::create([
            'product_id'     => $productId,
            'branch_id'      => $branchId,
            'type'           => $type,
            'quantity'       => $quantity,
            'previous_stock' => $previousStock,
            'new_stock'      => $newStock,
            'reason'         => $reason,
            'reference'      => $reference,
            'user_id'        => auth()->id(),
        ]);
    }

    public function lowStock()
    {
        $managerBranchId = $this->getManagerBranchId();

        // Get products with low stock across branches
        $productsQuery = Product::with(['category', 'branches'])
            ->whereHas('branches', function ($q) {
                $q->whereRaw('branch_product.stock <= products.minimum_stock');
            })
            ->orderBy('name');

        // Managers only see their branch's low stock
        if ($managerBranchId) {
            $productsQuery->whereHas('branches', function ($q) use ($managerBranchId) {
                $q->where('branch_id', $managerBranchId);
            });
        }

        $products = $productsQuery->get();

        return view('inventory.low-stock', compact('products'));
    }

    public function report(Request $request)
    {
        $managerBranchId = $this->getManagerBranchId();

        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to', now()->toDateString());

        $query = InventoryMovement::with(['product', 'branch'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        // Managers can only see their branch's report
        if ($managerBranchId) {
            $query->where('branch_id', $managerBranchId);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Use database grouping for better performance
        $summary = $query->clone()
            ->select('type', \DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $movements = $query->latest()->paginate(20)->withQueryString();

        // Managers only see their branch
        if ($managerBranchId) {
            $branches = Branch::active()->where('id', $managerBranchId)->get();
        } else {
            $branches = Branch::active()->orderBy('name')->get();
        }

        return view('inventory.report', compact('movements', 'summary', 'branches', 'dateFrom', 'dateTo'));
    }
}
