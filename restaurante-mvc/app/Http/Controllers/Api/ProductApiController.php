<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::with('category')
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('search'), fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->filled('active'), fn($q) => $q->where('is_active', $request->boolean('active')))
            ->paginate($request->get('per_page', 20));

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'minimum_stock' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:50',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'product' => $product->load('category')
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product->load('category', 'branches'));
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|unique:products,sku,' . $product->id,
            'price' => 'sometimes|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'minimum_stock' => 'nullable|integer|min:0',
            'unit' => 'sometimes|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Producto actualizado',
            'product' => $product->fresh()->load('category')
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Producto eliminado']);
    }

    public function barcode(Product $product): JsonResponse
    {
        return response()->json([
            'sku' => $product->sku,
            'barcode' => base64_encode($product->barcode_svg)
        ]);
    }
}
