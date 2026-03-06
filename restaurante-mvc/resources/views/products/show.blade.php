@extends('layouts.app')

@section('title', $product->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Productos</a></li>
    <li class="breadcrumb-item active">{{ $product->name }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-box-seam me-2 text-info"></i>{{ $product->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Editar
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body p-4">
                <img src="{{ $product->image ? $product->image_url : asset('images/no-image.svg') }}" alt="{{ $product->name }}"
                     class="img-fluid rounded" style="max-height:200px;object-fit:cover;">
                <h5 class="mt-3 fw-bold">{{ $product->name }}</h5>
                <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header fw-semibold">Información General</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-muted">SKU</small>
                        <div class="fw-semibold font-monospace">{{ $product->sku }}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted">Categoría</small>
                        <div>{{ $product->category->name ?? '—' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted">Precio</small>
                        <div class="fw-bold text-success fs-5">S/ {{ number_format($product->price, 2) }}</div>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted">Costo</small>
                        <div>{{ $product->cost ? 'S/ ' . number_format($product->cost, 2) : '—' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted">Margen</small>
                        <div class="text-primary">{{ $product->profit_margin ? $product->profit_margin . '%' : '—' }}</div>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted">Unidad</small>
                        <div>{{ $product->unit }}</div>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted">Stock mínimo</small>
                        <div>{{ $product->minimum_stock }}</div>
                    </div>
                    @if($product->is_perishable)
                    <div class="col-sm-4">
                        <small class="text-muted">¿Perecible?</small>
                        <div><span class="badge bg-warning">Sí</span></div>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted">Fecha de Vencimiento</small>
                        <div>
                            @if($product->isExpired())
                                <span class="badge bg-danger">Vencido</span>
                            @elseif($product->isExpiringSoon())
                                <span class="badge bg-warning text-dark">Por vencer ({{ $product->days_until_expiry }} días)</span>
                            @else
                                <span class="text-{{ $product->days_until_expiry <= 30 ? 'warning' : 'success' }}">
                                    {{ $product->expiry_date->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($product->shelf_days)
                    <div class="col-sm-4">
                        <small class="text-muted">Días en Estante</small>
                        <div>{{ $product->shelf_days }} días</div>
                    </div>
                    @endif
                    @endif
                    @if($product->description)
                    <div class="col-12">
                        <small class="text-muted">Descripción</small>
                        <div>{{ $product->description }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold">Stock por Sucursal</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Sucursal</th><th>Stock</th><th>Disponible</th></tr>
                    </thead>
                    <tbody>
                        @forelse($product->branches as $branch)
                        <tr>
                            <td>{{ $branch->name }}</td>
                            <td>
                                <span class="{{ $branch->pivot->stock <= $product->minimum_stock ? 'text-danger fw-bold' : '' }}">
                                    {{ $branch->pivot->stock }}
                                </span>
                            </td>
                            <td>
                                @if($branch->pivot->is_available)
                                    <span class="badge bg-success">Sí</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Sin sucursales asignadas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
