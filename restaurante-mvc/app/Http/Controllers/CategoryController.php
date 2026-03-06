<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('parent', 'children')
            ->roots()
            ->ordered();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->paginate(15)->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::roots()->active()->ordered()->get();
        return view('categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:categories,id',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'products']);
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::roots()
            ->active()
            ->ordered()
            ->where('id', '!=', $category->id)
            ->get();

        return view('categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:categories,id',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->withTrashed()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar la categoría porque tiene productos asignados.');
        }

        if ($category->children()->withTrashed()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar la categoría porque tiene subcategorías asignadas.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Categoría eliminada exitosamente.');
    }

    /**
     * Toggle category status (admin only)
     */
    public function toggleStatus(Category $category)
    {
        $this->authorize('toggleStatus', $category);

        $category->update(['is_active' => !$category->is_active]);
        $status = $category->is_active ? 'activada' : 'desactivada';
        return redirect()->back()->with('success', "Categoría {$status} exitosamente.");
    }
}
