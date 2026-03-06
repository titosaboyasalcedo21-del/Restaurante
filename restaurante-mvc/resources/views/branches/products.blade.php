@extends('layouts.app')
@section('title', 'Productos — ' . $branch->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('branches.index') }}">Sucursales</a></li>
    <li class="breadcrumb-item"><a href="{{ route('branches.show', $branch) }}">{{ $branch->name }}</a></li>
    <li class="breadcrumb-item active">Productos</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-box-seam me-2 text-success"></i>Productos — {{ $branch->name }}</h4>
    <a href="{{ route('branches.show', $branch) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Volver</a>
</div>

<!-- Assign product form -->
<div class="card mb-4">
    <div class="card-header fw-semibold">Asignar Producto</div>
    <div class="card-body">
        <form method="POST" action="{{ route('branches.products.assign', $branch) }}" class="row g-2">
            @csrf
            <div class="col-md-4">
                <select name="product_id" class="form-select" required>
                    <option value="">Seleccionar producto...</option>
                    @foreach($allProducts as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="stock" class="form-control" placeholder="Stock inicial" min="0" value="0">
            </div>
            <div class="col-md-2 d-flex align-items-center">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_available" value="1" checked>
                    <label class="form-check-label">Disponible</label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Asignar</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Disponible</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                @forelse($branchProducts as $product)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $product->name }}</div>
                        <small class="text-muted">{{ $product->sku }}</small>
                    </td>
                    <td>{{ $product->category->name ?? '—' }}</td>
                    <td>S/ {{ number_format($product->price, 2) }}</td>
                    <td>
                        <span class="{{ $product->pivot->stock <= $product->minimum_stock ? 'text-danger fw-bold' : 'text-success' }}">
                            {{ $product->pivot->stock }}
                        </span>
                    </td>
                    <td><span class="badge {{ $product->pivot->is_available ? 'bg-success' : 'bg-secondary' }}">{{ $product->pivot->is_available ? 'Sí' : 'No' }}</span></td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('branches.products.remove', [$branch, $product]) }}" class="d-inline"
                              onsubmit="return confirm('¿Quitar este producto de la sucursal?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i>Sin productos asignados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($branchProducts->hasPages())
    <div class="card-footer bg-transparent">{{ $branchProducts->links() }}</div>
    @endif
</div>
@endsection
