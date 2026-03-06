<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
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

    /**
     * Check if user can access the branch (admin can access all, manager only their branch)
     */
    private function canAccessBranch(Branch $branch): bool
    {
        $user = auth()->user();

        // Admin can access any branch
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can only access their branch
        if ($user->isManager()) {
            return $user->branch_id === $branch->id;
        }

        return false;
    }

    public function index(Request $request)
    {
        $query = Branch::withCount('products');

        // Managers can only see their branch
        $managerBranchId = $this->getManagerBranchId();
        if ($managerBranchId) {
            $query->where('id', $managerBranchId);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('city', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $branches = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        // Only admin can create
        $this->authorize('create', Branch::class);

        return view('branches.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Branch::class);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'required|string|unique:branches,code',
            'address'      => 'nullable|string',
            'city'         => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'is_active'    => 'boolean',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Branch::create($validated);

        return redirect()->route('branches.index')->with('success', 'Sucursal creada exitosamente.');
    }

    public function show(Branch $branch)
    {
        // Check if user can access this branch
        if (!$this->canAccessBranch($branch)) {
            abort(403, 'No tienes permiso para acceder a esta sucursal.');
        }

        $branch->load(['products.category']);
        return view('branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        // Only admin can edit
        $this->authorize('update', $branch);

        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorize('update', $branch);

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'required|string|unique:branches,code,' . $branch->id,
            'address'      => 'nullable|string',
            'city'         => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'is_active'    => 'boolean',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $branch->update($validated);

        return redirect()->route('branches.index')->with('success', 'Sucursal actualizada exitosamente.');
    }

    public function destroy(Branch $branch)
    {
        $this->authorize('delete', $branch);

        // Check if there are inventory movements
        if ($branch->inventoryMovements()->exists()) {
            return redirect()->back()->with('error',
                'No se puede eliminar la sucursal porque tiene movimientos de inventario asociados.');
        }

        // Detach all products before deleting
        $branch->products()->detach();

        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Sucursal eliminada exitosamente.');
    }

    public function products(Branch $branch, Request $request)
    {
        // Check if user can access this branch
        if (!$this->canAccessBranch($branch)) {
            abort(403, 'No tienes permiso para acceder a esta sucursal.');
        }

        // Check if user can manage products in this branch
        $this->authorize('manageProducts', $branch);

        $branchProducts = $branch->products()->with('category')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('products.name', 'like', '%' . $request->search . '%');
            })
            ->paginate(15)
            ->withQueryString();

        $allProducts = Product::active()->with('category')->orderBy('name')->get();

        return view('branches.products', compact('branch', 'branchProducts', 'allProducts'));
    }

    public function assignProduct(Request $request, Branch $branch)
    {
        // Check if user can manage products in this branch
        $this->authorize('manageProducts', $branch);

        $validated = $request->validate([
            'product_id'   => 'required|exists:products,id',
            'stock'        => 'integer|min:0',
            'is_available' => 'boolean',
        ]);

        // Check for duplicates
        if ($branch->products()->where('product_id', $validated['product_id'])->exists()) {
            return redirect()->back()->with('error',
                'El producto ya está asignado a esta sucursal.');
        }

        DB::transaction(function () use ($validated, $branch, $request) {
            $branch->products()->attach($validated['product_id'], [
                'stock'        => $validated['stock'] ?? 0,
                'is_available' => $request->boolean('is_available', true),
            ]);
        });

        return redirect()->route('branches.products', $branch)->with('success', 'Producto asignado exitosamente.');
    }

    public function removeProduct(Branch $branch, Product $product)
    {
        // Check if user can manage products in this branch
        $this->authorize('manageProducts', $branch);

        // Check if there are inventory movements for this product in this branch
        $hasMovements = \App\Models\InventoryMovement::where('branch_id', $branch->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($hasMovements) {
            return redirect()->back()->with('error',
                'No se puede remover el producto porque existen movimientos de inventario asociados.');
        }

        $branch->products()->detach($product->id);
        return redirect()->route('branches.products', $branch)->with('success', 'Producto removido de la sucursal.');
    }

    /**
     * Toggle product availability in branch (for managers)
     */
    public function toggleProductAvailability(Request $request, Branch $branch, Product $product)
    {
        // Check if user can manage products in this branch
        $this->authorize('manageProducts', $branch);

        $pivot = $branch->products()->where('product_id', $product->id)->first();

        if (!$pivot) {
            return redirect()->back()->with('error', 'El producto no está asignado a esta sucursal.');
        }

        $branch->products()->updateExistingPivot($product->id, [
            'is_available' => !$pivot->pivot->is_available
        ]);

        return redirect()->back()->with('success', 'Disponibilidad actualizada.');
    }

    /**
     * Update product stock in branch (for managers)
     */
    public function updateProductStock(Request $request, Branch $branch, Product $product)
    {
        // Check if user can manage products in this branch
        $this->authorize('manageProducts', $branch);

        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $pivot = $branch->products()->where('product_id', $product->id)->first();

        if (!$pivot) {
            return redirect()->back()->with('error', 'El producto no está asignado a esta sucursal.');
        }

        $branch->products()->updateExistingPivot($product->id, [
            'stock' => $validated['stock']
        ]);

        return redirect()->back()->with('success', 'Stock actualizado.');
    }

    /**
     * Update contact info only (for managers)
     */
    public function updateContactInfo(Request $request, Branch $branch)
    {
        // Check if user can access this branch
        if (!$this->canAccessBranch($branch)) {
            abort(403, 'No tienes permiso para acceder a esta sucursal.');
        }

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $branch->update($validated);

        return redirect()->back()->with('success', 'Información de contacto actualizada.');
    }
}
