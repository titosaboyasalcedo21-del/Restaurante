@extends('layouts.app')

@section('title', 'Sucursales')

@section('module', 'Sucursales')

@section('content')
<!-- Page Header -->
<div class="page-header mb-4">
    <h3 class="page-title">
        <i class="bi bi-shop"></i>
        Sucursales
    </h3>
    @can('create', App\Models\Branch::class)
    <a href="{{ route('branches.create') }}" class="btn-primary-custom">
        <i class="bi bi-plus-lg"></i>
        Nueva Sucursal
    </a>
    @endcan
</div>

<!-- Branches Grid -->
<div class="branch-grid">
    @forelse($branches as $branch)
    @php
        $totalProducts = $branch->products->count();
        $lowStockCount = $branch->products->filter(fn($p) => $p->pivot->stock <= ($p->minimum_stock ?? 0))->count();
    @endphp
    <div class="branch-card {{ !$branch->is_active ? 'inactive' : '' }}">
        <div class="branch-header">
            <div class="branch-icon">🏪</div>
            <div class="branch-title">
                <div class="branch-name">{{ $branch->name }}</div>
                <div class="branch-location">
                    <i class="bi bi-geo-alt me-1"></i>
                    {{ $branch->address ?? 'Sin dirección' }} • {{ $branch->city ?? 'Sin ciudad' }}
                </div>
            </div>
            <span class="branch-status {{ $branch->is_active ? 'active' : 'inactive' }}">
                {{ $branch->is_active ? 'Activa' : 'Inactiva' }}
            </span>
        </div>

        <div class="branch-metrics">
            <div class="metric-item">
                <div class="metric-value">{{ $totalProducts }}</div>
                <div class="metric-label">Productos</div>
            </div>
            <div class="metric-item {{ $lowStockCount > 3 ? 'warning' : '' }}">
                <div class="metric-value">{{ $lowStockCount }}</div>
                <div class="metric-label">Stock Bajo</div>
            </div>
        </div>

        <div class="branch-actions">
            <a href="{{ route('branches.show', $branch) }}" class="btn-dark-sm">
                <i class="bi bi-eye"></i> Ver detalle
            </a>
            <a href="{{ route('branches.products', $branch) }}" class="btn-red-sm">
                <i class="bi bi-box-seam"></i> Inventario
            </a>
            @can('update', $branch)
            <a href="{{ route('branches.edit', $branch) }}" class="btn-outline-custom">
                <i class="bi bi-pencil"></i> Editar
            </a>
            @endcan
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="dark-card">
            <div class="empty-state">
                <i class="bi bi-shop"></i>
                <p>No hay sucursales registradas</p>
                @can('create', App\Models\Branch::class)
                <a href="{{ route('branches.create') }}" class="btn-primary-custom mt-3">
                    <i class="bi bi-plus-lg"></i>
                    Crear Primera Sucursal
                </a>
                @endcan
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
