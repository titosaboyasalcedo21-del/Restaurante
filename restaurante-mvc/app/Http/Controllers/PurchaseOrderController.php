<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'branch', 'user']);

        if ($request->filled('search')) {
            $query->where('order_number', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get();
        $branches = Branch::active()->orderBy('name')->get();

        return view('purchase-orders.index', compact('orders', 'suppliers', 'branches'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $branches = Branch::active()->orderBy('name')->get();

        return view('purchase-orders.create', compact('suppliers', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'branch_id' => 'required|exists:branches,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
        ]);

        $validated['order_number'] = PurchaseOrder::generateOrderNumber();
        $validated['user_id'] = auth()->id();
        $validated['status'] = PurchaseOrder::STATUS_DRAFT;

        $order = PurchaseOrder::create($validated);

        return redirect()->route('purchase-orders.edit', $order)->with('success', 'Orden de compra creada. Agregue los productos.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'branch', 'user', 'items.product']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_PENDING])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'No se puede modificar una orden en estado ' . $purchaseOrder->status_label);
        }

        $purchaseOrder->load('items.product');
        $products = Product::active()->with('category')->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();
        $branches = Branch::active()->orderBy('name')->get();

        return view('purchase-orders.edit', compact('purchaseOrder', 'products', 'suppliers', 'branches'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'branch_id' => 'required|exists:branches,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $purchaseOrder->update($validated);

        return redirect()->route('purchase-orders.index')->with('success', 'Orden de compra actualizada.');
    }

    public function addItem(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_PENDING])) {
            return back()->with('error', 'No se puede modificar la orden.');
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check if product already exists in order
        $existingItem = $purchaseOrder->items()->where('product_id', $validated['product_id'])->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $validated['quantity'],
                'unit_cost' => $validated['unit_cost'],
            ]);
            $existingItem->calculateTotal();
        } else {
            $item = $purchaseOrder->items()->create([
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'unit_cost' => $validated['unit_cost'],
                'total' => $validated['quantity'] * $validated['unit_cost'],
                'notes' => $validated['notes'] ?? null,
            ]);
        }

        $purchaseOrder->calculateTotals();

        return back()->with('success', 'Producto agregado a la orden.');
    }

    public function removeItem(PurchaseOrder $purchaseOrder, PurchaseOrderItem $item)
    {
        if (!in_array($purchaseOrder->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_PENDING])) {
            return back()->with('error', 'No se puede modificar la orden.');
        }

        $item->delete();
        $purchaseOrder->calculateTotals();

        return back()->with('success', 'Producto eliminado de la orden.');
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return back()->with('error', 'Solo se pueden aprobar órdenes en borrador.');
        }

        if ($purchaseOrder->items()->count() === 0) {
            return back()->with('error', 'La orden debe tener al menos un producto.');
        }

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_APPROVED]);

        return back()->with('success', 'Orden aprobada.');
    }

    public function receive(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_APPROVED) {
            return back()->with('error', 'Solo se pueden recibir órdenes aprobadas.');
        }

        DB::transaction(function () use ($purchaseOrder) {
            // Update order status
            $purchaseOrder->update([
                'status' => PurchaseOrder::STATUS_RECEIVED,
                'received_date' => now(),
            ]);

            // Add inventory for each item
            foreach ($purchaseOrder->items as $item) {
                // Get or create branch_product pivot
                $exists = DB::table('branch_product')
                    ->where('branch_id', $purchaseOrder->branch_id)
                    ->where('product_id', $item->product_id)
                    ->exists();

                if (!$exists) {
                    DB::table('branch_product')->insert([
                        'branch_id' => $purchaseOrder->branch_id,
                        'product_id' => $item->product_id,
                        'stock' => 0,
                        'is_available' => true,
                    ]);
                }

                // Update stock
                DB::table('branch_product')
                    ->where('branch_id', $purchaseOrder->branch_id)
                    ->where('product_id', $item->product_id)
                    ->increment('stock', $item->quantity);

                // Create inventory movement
                InventoryMovement::create([
                    'product_id' => $item->product_id,
                    'branch_id' => $purchaseOrder->branch_id,
                    'type' => InventoryMovement::TYPE_IN,
                    'quantity' => $item->quantity,
                    'previous_stock' => $item->quantity_received,
                    'new_stock' => $item->quantity_received + $item->quantity,
                    'reason' => 'Recepción de orden de compra: ' . $purchaseOrder->order_number,
                    'reference' => $purchaseOrder->order_number,
                    'user_id' => auth()->id(),
                ]);

                // Update quantity received
                $item->update(['quantity_received' => $item->quantity_received + $item->quantity]);
            }
        });

        return back()->with('success', 'Orden recibida y inventario actualizado.');
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_PENDING])) {
            return back()->with('error', 'No se puede cancelar la orden en este estado.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_CANCELLED,
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        return back()->with('success', 'Orden cancelada.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return back()->with('error', 'Solo se pueden eliminar órdenes en borrador.');
        }

        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')->with('success', 'Orden de compra eliminada.');
    }
}
