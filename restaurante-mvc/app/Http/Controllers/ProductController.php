<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Category::active()->ordered()->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->ordered()->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData();
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeImage($request->file('image'));
        }

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Producto creado exitosamente.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'branches']);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->ordered()->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validatedData($product->id);
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $this->storeImage($request->file('image'));
        }

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Producto eliminado exitosamente.');
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        $status = $product->is_active ? 'activado' : 'desactivado';
        return redirect()->back()->with('success', "Producto {$status} exitosamente.");
    }

    /**
     * Validate product data
     */
    private function validatedData(?int $productId = null): array
    {
        $rules = [
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'sku'           => ['required', 'string', Rule::unique('products')->withoutTrashed()],
            'price'         => 'required|numeric|min:0',
            'cost'          => 'nullable|numeric|min:0',
            'category_id'   => 'required|exists:categories,id',
            'image'         => 'nullable|image|max:2048',
            'is_active'     => 'boolean',
            'minimum_stock' => 'integer|min:0',
            'unit'          => 'required|string|max:50',
        ];

        if ($productId) {
            $rules['sku'] = ['required', 'string', Rule::unique('products')->withoutTrashed()->ignore($productId)];
        }

        return request()->validate($rules);
    }

    /**
     * Store image with UUID and original extension
     */
    private function storeImage($file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        return $file->storeAs('products', $filename, 'public');
    }
}
