<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BranchApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $branches = Branch::withCount('products')
            ->when($request->filled('city'), fn($q) => $q->where('city', $request->city))
            ->get();

        return response()->json($branches);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:branches,code',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'manager_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $branch = Branch::create($validated);

        return response()->json([
            'message' => 'Sucursal creada',
            'branch' => $branch
        ], 201);
    }

    public function show(Branch $branch): JsonResponse
    {
        return response()->json($branch->load('products'));
    }

    public function update(Request $request, Branch $branch): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:branches,code,' . $branch->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'manager_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'sometimes|boolean',
        ]);

        $branch->update($validated);

        return response()->json([
            'message' => 'Sucursal actualizada',
            'branch' => $branch->fresh()
        ]);
    }

    public function destroy(Branch $branch): JsonResponse
    {
        if ($branch->inventoryMovements()->count() > 0) {
            return response()->json(['error' => 'La sucursal tiene movimientos de inventario'], 422);
        }

        $branch->delete();

        return response()->json(['message' => 'Sucursal eliminada']);
    }

    public function inventory(Branch $branch): JsonResponse
    {
        $products = $branch->products()->with('category')->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category->name,
                'stock' => $product->pivot->stock,
                'is_available' => $product->pivot->is_available,
            ];
        });

        return response()->json($products);
    }
}
