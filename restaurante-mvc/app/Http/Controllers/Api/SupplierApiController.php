<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $suppliers = Supplier::when($request->search, function ($query) use ($request) {
            $query->search($request->search);
        })
        ->when($request->has('active'), function ($query) use ($request) {
            if ($request->active === 'true') {
                $query->active();
            }
        })
        ->paginate($request->per_page ?? 15);

        return response()->json($suppliers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:suppliers,code',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'ruc' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'message' => 'Proveedor creado exitosamente',
            'supplier' => $supplier,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier): JsonResponse
    {
        return response()->json([
            'supplier' => $supplier->load(['products', 'purchaseOrders']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:suppliers,code,' . $supplier->id,
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'ruc' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);

        return response()->json([
            'message' => 'Proveedor actualizado exitosamente',
            'supplier' => $supplier,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return response()->json([
            'message' => 'Proveedor eliminado exitosamente',
        ]);
    }

    /**
     * Toggle supplier status
     */
    public function toggleStatus(Supplier $supplier): JsonResponse
    {
        $supplier->update(['is_active' => !$supplier->is_active]);

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'supplier' => $supplier,
        ]);
    }
}
