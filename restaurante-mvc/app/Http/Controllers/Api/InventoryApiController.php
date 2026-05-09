<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InventoryApiController extends Controller
{
    public function movements(Request $request): JsonResponse
    {
        $query = InventoryMovement::with('product', 'branch');

        $user = $request->user();
        // If not admin, restrict to their assigned branch
        if ($user->role !== 'admin' && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
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

        $movements = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json($movements);
    }

    public function adjust(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Employees can only register 'out' movements (sales)
        $allowedTypes = $user->role === 'employee' ? ['out'] : ['in', 'out', 'adjust'];

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'type' => 'required|in:' . implode(',', $allowedTypes),
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        // Managers and Employees can only adjust inventory of their assigned branch
        if ($user->role !== 'admin' && $user->branch_id && $validated['branch_id'] != $user->branch_id) {
            return response()->json([
                'message' => 'Solo puedes ajustar inventario de tu sucursal asignada.',
            ], 403);
        }

        $movement = DB::transaction(function () use ($validated, $user) {
            $pivot = DB::table('branch_product')
                ->where('branch_id', $validated['branch_id'])
                ->where('product_id', $validated['product_id'])
                ->lockForUpdate()
                ->first();

            $previousStock = $pivot ? $pivot->stock : 0;

            if (!$pivot) {
                DB::table('branch_product')->insert([
                    'branch_id' => $validated['branch_id'],
                    'product_id' => $validated['product_id'],
                    'stock' => 0,
                    'is_available' => true
                ]);
            }

            $newStock = match($validated['type']) {
                'in' => $previousStock + $validated['quantity'],
                'out' => max(0, $previousStock - $validated['quantity']),
                'adjust' => $validated['quantity'],
                default => $previousStock,
            };

            DB::table('branch_product')
                ->where('branch_id', $validated['branch_id'])
                ->where('product_id', $validated['product_id'])
                ->update(['stock' => $newStock]);

            return InventoryMovement::create([
                'product_id' => $validated['product_id'],
                'branch_id' => $validated['branch_id'],
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $validated['reason'],
                'user_id' => $user->id,
            ]);
        });

        return response()->json([
            'message' => 'Inventario ajustado',
            'movement' => $movement->load('product', 'branch')
        ], 201);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Product::with(['category', 'branches']);

        // Base filter for low stock
        $query->whereHas('branches', function ($q) use ($request, $user) {
            // Apply branch restriction if not admin
            if ($user->role !== 'admin' && $user->branch_id) {
                $q->where('branches.id', $user->branch_id);
            } elseif ($request->filled('branch_id')) {
                $q->where('branches.id', $request->branch_id);
            }
            $q->whereRaw('branch_product.stock <= products.minimum_stock');
        });

        $products = $query->get()->map(function ($product) {
            $branchStock = $product->branches->map(fn($b) => [
                'branch' => $b->name,
                'stock' => $b->pivot->stock,
                'minimum' => $product->minimum_stock,
            ]);
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category->name,
                'minimum_stock' => $product->minimum_stock,
                'total_stock' => $product->total_stock,
                'branch_stock' => $branchStock,
            ];
        });

        return response()->json($products);
    }
}
