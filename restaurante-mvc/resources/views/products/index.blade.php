@extends('layouts.app')

@section('title', 'Productos')

@section('module', 'Productos')

@section('content')
<!-- Page Header -->
<div class="page-header mb-4">
    <h3 class="page-title">
        <i class="bi bi-box-seam"></i>
        Productos
    </h3>
    @can('create', App\Models\Product::class)
    <a href="{{ route('products.create') }}" class="btn-primary-custom">
        <i class="bi bi-plus-lg"></i>
        Nuevo Producto
    </a>
    @endcan
</div>

<!-- Filters -->
<div class="filter-card">
    <form method="GET" action="{{ route('products.index') }}" class="row g-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control-dark w-100" placeholder="Buscar por nombre o SKU..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="category_id" class="form-select-dark w-100">
                <option value="">Todas las categorías</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        @if(auth()->user()->isAdmin())
        <div class="col-md-2">
            <select name="status" class="form-select-dark w-100">
                <option value="">Todos los estados</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
            </select>
        </div>
        @endif
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn-outline-custom flex-fill">
                <i class="bi bi-search"></i>
                Filtrar
            </button>
            <a href="{{ route('products.index') }}" class="btn-outline-custom">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="dark-card">
    <div class="table-responsive">
        <table class="table-dark-custom">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    @can('viewCost', App\Models\Product::class)
                    <th>Costo</th>
                    <th>Margen</th>
                    @endcan
                    <th>Precio</th>
                    <th>Stock</th>
                    @if(auth()->user()->isAdmin())
                    <th>Estado</th>
                    @endif
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                @php
                    $totalStock = $product->branches->sum('pivot.stock');
                    $stockClass = $totalStock == 0 ? 'out-of-stock' : ($totalStock < $product->minimum_stock ? 'low-stock' : 'in-stock');
                    $stockLabel = $totalStock == 0 ? 'Agotado' : $totalStock . ' ' . $product->unit;
                @endphp
                <tr>
                    <td>
                        <img src="{{ $product->image ? $product->image_url : asset('images/no-image.svg') }}"
                             alt="{{ $product->name }}"
                             class="product-thumb">
                    </td>
                    <td>
                        <div class="fw-semibold" style="color: var(--text-primary);">{{ $product->name }}</div>
                        <span class="sku-code">{{ $product->sku }}</span>
                    </td>
                    <td>
                        @if($product->category)
                            <span class="category-badge">{{ $product->category->name }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    @can('viewCost', $product)
                    <td class="text-muted">S/ {{ number_format($product->cost, 2) }}</td>
                    <td>
                        @if($product->profit_margin !== null)
                            <span class="badge-margin {{ $product->profit_margin >= 60 ? 'badge-green' : 'badge-orange' }}">
                                {{ $product->profit_margin }}%
                            </span>
                        @else
                            —
                        @endif
                    </td>
                    @endcan
                    <td class="price">S/ {{ number_format($product->price, 2) }}</td>
                    <td>
                        <span class="stock-badge {{ $stockClass }}">
                            {{ $stockLabel }}
                        </span>
                    </td>
                    @if(auth()->user()->isAdmin())
                    <td>
                        <span class="status-dot {{ $product->is_active ? 'active' : 'inactive' }}"></span>
                        <span class="ms-2" style="color: var(--text-secondary); font-size: 0.8rem;">
                            {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    @endif
                    <td class="text-end">
                        <a href="{{ route('products.show', $product) }}" class="btn-dark-sm" title="Ver">
                            <i class="bi bi-eye"></i>
                        </a>
                        @can('update', $product)
                        <a href="{{ route('products.edit', $product) }}" class="btn-red-sm" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endcan
                        @can('delete', $product)
                        <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este producto?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger-sm" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->isAdmin() ? '9' : (auth()->user()->isManager() ? '7' : '6') }}" class="text-center py-4">
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>No se encontraron productos</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="pagination-custom">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
