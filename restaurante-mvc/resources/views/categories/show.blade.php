@extends('layouts.app')
@section('title', $category->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Categorías</a></li>
    <li class="breadcrumb-item active">{{ $category->name }}</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-tag me-2 text-info"></i>{{ $category->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil me-1"></i>Editar</a>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header fw-semibold">Información</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted">Nombre</dt>
                    <dd class="col-sm-7">{{ $category->name }}</dd>
                    <dt class="col-sm-5 text-muted">Padre</dt>
                    <dd class="col-sm-7">{{ $category->parent->name ?? '— (raíz)' }}</dd>
                    <dt class="col-sm-5 text-muted">Orden</dt>
                    <dd class="col-sm-7">{{ $category->sort_order }}</dd>
                    <dt class="col-sm-5 text-muted">Estado</dt>
                    <dd class="col-sm-7">
                        <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $category->is_active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </dd>
                    @if($category->description)
                    <dt class="col-sm-5 text-muted">Descripción</dt>
                    <dd class="col-sm-7">{{ $category->description }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        @if($category->children->count())
        <div class="card mt-3">
            <div class="card-header fw-semibold">Subcategorías</div>
            <ul class="list-group list-group-flush">
                @foreach($category->children as $child)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $child->name }}
                    <span class="badge {{ $child->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $child->is_active ? 'Activa' : 'Inactiva' }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header fw-semibold">Productos ({{ $category->products->count() }})</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Nombre</th><th>SKU</th><th>Precio</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        @forelse($category->products as $product)
                        <tr>
                            <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td>
                            <td class="font-monospace text-muted">{{ $product->sku }}</td>
                            <td>S/ {{ number_format($product->price, 2) }}</td>
                            <td><span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $product->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Sin productos en esta categoría.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
