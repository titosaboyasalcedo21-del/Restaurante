<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::with('parent')
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer|min:0',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Categoría creada',
            'category' => $category
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json($category->load('parent', 'children', 'products'));
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Categoría actualizada',
            'category' => $category->fresh()
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->count() > 0) {
            return response()->json(['error' => 'La categoría tiene productos asociados'], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Categoría eliminada']);
    }
}
