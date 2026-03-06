<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = PurchaseOrder::with(['supplier', 'branch', 'user'])
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->branch_id, function ($query) use ($request) {
                $query->where('branch_id', $request->branch_id);
            })
            ->when($request->supplier_id, function ($query) use ($request) {
                $query->where('supplier_id', $request->supplier_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'branch_id' => 'required|exists:branches,id',
            'order_date' => 'date',
            'expected_date' => 'date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $order = PurchaseOrder::create([
            'order_number' => PurchaseOrder::generateOrderNumber(),
            'supplier_id' => $validated['supplier_id'],
            'branch_id' => $validated['branch_id'],
            'user_id' => $request->user()->id,
            'status' => PurchaseOrder::STATUS_DRAFT,
            'order_date' => $validated['order_date'] ?? now(),
            'expected_date' => $validated['expected_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        $order->calculateTotals();

        return response()->json([
            'message' => 'Orden de compra creada exitosamente',
            'purchase_order' => $order->load(['items', 'supplier', 'branch']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        return response()->json([
            'purchase_order' => $purchaseOrder->load(['supplier', 'branch', 'user', 'items.product']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (!in_array($purchaseOrder->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_PENDING])) {
            return response()->json([
                'message' => 'No se puede modificar una orden en estado ' . $purchaseOrder->status_label,
            ], 422);
        }

        $validated = $request->validate([
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'branch_id' => 'sometimes|exists:branches,id',
            'order_date' => 'sometimes|date',
            'expected_date' => 'sometimes|date',
            'notes' => 'nullable|string',
        ]);

        $purchaseOrder->update($validated);

        return response()->json([
            'message' => 'Orden de compra actualizada exitosamente',
            'purchase_order' => $purchaseOrder,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (!in_array($purchaseOrder->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_CANCELLED])) {
            return response()->json([
                'message' => 'No se puede eliminar una orden en estado ' . $purchaseOrder->status_label,
            ], 422);
        }

        $purchaseOrder->delete();

        return response()->json([
            'message' => 'Orden de compra eliminada exitosamente',
        ]);
    }

    /**
     * Approve a purchase order
     */
    public function approve(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_PENDING) {
            return response()->json([
                'message' => 'Solo se pueden aprobar ordenes pendientes',
            ], 422);
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_APPROVED,
        ]);

        return response()->json([
            'message' => 'Orden de compra aprobada exitosamente',
            'purchase_order' => $purchaseOrder,
        ]);
    }

    /**
     * Receive a purchase order
     */
    public function receive(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_APPROVED) {
            return response()->json([
                'message' => 'Solo se pueden recibir ordenes aprobadas',
            ], 422);
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_RECEIVED,
            'received_date' => now(),
        ]);

        // TODO: Add inventory movement for each item

        return response()->json([
            'message' => 'Orden de compra recibida exitosamente',
            'purchase_order' => $purchaseOrder,
        ]);
    }

    /**
     * Cancel a purchase order
     */
    public function cancel(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (in_array($purchaseOrder->status, [PurchaseOrder::STATUS_RECEIVED, PurchaseOrder::STATUS_CANCELLED])) {
            return response()->json([
                'message' => 'No se puede cancelar una orden ' . $purchaseOrder->status_label,
            ], 422);
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string',
        ]);

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_CANCELLED,
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        return response()->json([
            'message' => 'Orden de compra cancelada exitosamente',
            'purchase_order' => $purchaseOrder,
        ]);
    }
}
