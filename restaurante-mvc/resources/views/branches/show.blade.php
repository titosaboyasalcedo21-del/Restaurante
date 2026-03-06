@extends('layouts.app')
@section('title', $branch->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('branches.index') }}">Sucursales</a></li>
    <li class="breadcrumb-item active">{{ $branch->name }}</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-geo-alt me-2 text-info"></i>{{ $branch->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('branches.products', $branch) }}" class="btn btn-success btn-sm"><i class="bi bi-box-seam me-1"></i>Productos</a>
        <a href="{{ route('branches.edit', $branch) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil me-1"></i>Editar</a>
        <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header fw-semibold">Información</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted">Código</dt>
                    <dd class="col-sm-7 font-monospace">{{ $branch->code }}</dd>
                    <dt class="col-sm-5 text-muted">Dirección</dt>
                    <dd class="col-sm-7">{{ $branch->full_address ?: '—' }}</dd>
                    <dt class="col-sm-5 text-muted">Teléfono</dt>
                    <dd class="col-sm-7">{{ $branch->phone ?? '—' }}</dd>
                    <dt class="col-sm-5 text-muted">Email</dt>
                    <dd class="col-sm-7">{{ $branch->email ?? '—' }}</dd>
                    <dt class="col-sm-5 text-muted">Gerente</dt>
                    <dd class="col-sm-7">{{ $branch->manager_name ?? '—' }}</dd>
                    <dt class="col-sm-5 text-muted">Estado</dt>
                    <dd class="col-sm-7">
                        <span class="badge {{ $branch->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $branch->is_active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </dd>
                    @if($branch->latitude)
                    <dt class="col-sm-5 text-muted">Coordenadas</dt>
                    <dd class="col-sm-7 small">{{ $branch->latitude }}, {{ $branch->longitude }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        @php $lowStock = $branch->low_stock_products; @endphp
        @if($lowStock->isNotEmpty())
        <div class="card mt-3 border-warning">
            <div class="card-header bg-warning text-dark fw-semibold">
                <i class="bi bi-exclamation-triangle me-1"></i>Stock Bajo ({{ $lowStock->count() }})
            </div>
            <ul class="list-group list-group-flush">
                @foreach($lowStock as $p)
                <li class="list-group-item d-flex justify-content-between">
                    <span>{{ $p->name }}</span>
                    <span class="badge bg-danger">{{ $p->pivot->stock }} / {{ $p->minimum_stock }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header fw-semibold">Productos Asignados ({{ $branch->products->count() }})</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Producto</th><th>Categoría</th><th>Stock</th><th>Disponible</th></tr>
                    </thead>
                    <tbody>
                        @forelse($branch->products as $product)
                        <tr>
                            <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td>
                            <td>{{ $product->category->name ?? '—' }}</td>
                            <td>
                                <span class="{{ $product->pivot->stock <= $product->minimum_stock ? 'text-danger fw-bold' : '' }}">
                                    {{ $product->pivot->stock }}
                                </span>
                            </td>
                            <td><span class="badge {{ $product->pivot->is_available ? 'bg-success' : 'bg-secondary' }}">{{ $product->pivot->is_available ? 'Sí' : 'No' }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Sin productos asignados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
