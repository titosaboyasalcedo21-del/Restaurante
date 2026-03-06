@extends('layouts.app')

@section('title', 'Inventario')

@section('module', 'Inventario')

@section('content')
<!-- Page Header -->
<div class="page-header mb-4">
    <h3 class="page-title">
        <i class="bi bi-clipboard-data"></i>
        Inventario
    </h3>
</div>

@php
$canAdjust = auth()->user()->isManager() || auth()->user()->isAdmin();
$canIn = auth()->user()->isManager() || auth()->user()->isAdmin();
$canTransfer = auth()->user()->isManager() || auth()->user()->isAdmin();
@endphp

<!-- Movement Type Cards -->
<div class="movement-grid">
    <!-- Entrada - Admin & Manager -->
    <a href="{{ $canIn ? route('inventory.adjust', ['type' => 'in']) : '#' }}"
       class="movement-card {{ !$canIn ? 'blocked' : '' }}"
       @if(!$canIn) onclick="return false;" @endif>
        <div class="movement-icon">
            <i class="bi bi-box-arrow-in-down"></i>
        </div>
        <div class="movement-label">Entrada</div>
        <div class="movement-desc">Stock recibido</div>
        @if(!$canIn)
        <small>🔒 Sin permiso</small>
        @endif
    </a>

    <!-- Salida - All users -->
    <a href="{{ route('inventory.adjust', ['type' => 'out']) }}" class="movement-card out">
        <div class="movement-icon">
            <i class="bi bi-box-arrow-up"></i>
        </div>
        <div class="movement-label">Salida</div>
        <div class="movement-desc">Consumo / Venta</div>
    </a>

    <!-- Ajuste - Admin & Manager -->
    <a href="{{ $canAdjust ? route('inventory.adjust', ['type' => 'adjust']) : '#' }}"
       class="movement-card {{ !$canAdjust ? 'blocked' : '' }}"
       @if(!$canAdjust) onclick="return false;" @endif>
        <div class="movement-icon">
            <i class="bi bi-sliders"></i>
        </div>
        <div class="movement-label">Ajuste</div>
        <div class="movement-desc">Corrección de inventario</div>
        @if(!$canAdjust)
        <small>🔒 Sin permiso</small>
        @endif
    </a>

    <!-- Transferencia - Admin & Manager -->
    <a href="{{ $canTransfer ? route('inventory.adjust', ['type' => 'transfer']) : '#' }}"
       class="movement-card {{ !$canTransfer ? 'blocked' : '' }}"
       @if(!$canTransfer) onclick="return false;" @endif>
        <div class="movement-icon">
            <i class="bi bi-arrow-left-right"></i>
        </div>
        <div class="movement-label">Transferencia</div>
        <div class="movement-desc">Entre sucursales</div>
        @if(!$canTransfer)
        <small>🔒 Sin permiso</small>
        @endif
    </a>
</div>

<!-- Quick Stats -->
<div class="dark-card mb-4">
    <div class="dark-card-header">
        <h4 class="mb-0" style="font-size: 0.9rem; font-weight: 600;">
            <i class="bi bi-graph-up me-2"></i>Resumen de Inventario
        </h4>
    </div>
    <div class="dark-card-body">
        <div class="row">
            <div class="col-md-3 col-6 text-center mb-3 mb-md-0">
                <div class="stat-value" style="font-size: 1.5rem; color: var(--accent-green);">
                    {{ \App\Models\InventoryMovement::where('type', 'in')
                        ->when(!auth()->user()->isAdmin(), fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                        ->whereDate('created_at', today())
                        ->sum('quantity') }}
                </div>
                <div class="stat-label">Entradas Hoy</div>
            </div>
            <div class="col-md-3 col-6 text-center mb-3 mb-md-0">
                <div class="stat-value" style="font-size: 1.5rem; color: var(--accent-red);">
                    {{ \App\Models\InventoryMovement::where('type', 'out')
                        ->when(!auth()->user()->isAdmin(), fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                        ->whereDate('created_at', today())
                        ->sum('quantity') }}
                </div>
                <div class="stat-label">Salidas Hoy</div>
            </div>
            <div class="col-md-3 col-6 text-center">
                <div class="stat-value" style="font-size: 1.5rem; color: var(--accent-yellow);">
                    {{ \App\Models\Product::whereHas('branches', fn($q) =>
                        $q->whereRaw('branch_product.stock <= products.minimum_stock')
                            ->when(!auth()->user()->isAdmin(), fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                    )->count() }}
                </div>
                <div class="stat-label">Stock Bajo</div>
            </div>
            <div class="col-md-3 col-6 text-center">
                <div class="stat-value" style="font-size: 1.5rem; color: var(--accent-blue);">
                    {{ \App\Models\InventoryMovement::whereDate('created_at', today())
                        ->when(!auth()->user()->isAdmin(), fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                        ->count() }}
                </div>
                <div class="stat-label">Movimientos Hoy</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row">
    <div class="col-md-6">
        <div class="dark-card">
            <div class="dark-card-header">
                <h4 class="mb-0" style="font-size: 0.9rem; font-weight: 600;">
                    <i class="bi bi-clock-history me-2"></i>Movimientos Recientes
                </h4>
                <a href="{{ route('inventory.movements') }}" class="btn-outline-custom">Ver todos</a>
            </div>
            <div class="dark-card-body">
                @php
                $recentMovements = \App\Models\InventoryMovement::with(['product', 'branch'])
                    ->when(!auth()->user()->isAdmin(), fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                    ->latest()
                    ->limit(5)
                    ->get();
                @endphp
                @forelse($recentMovements as $movement)
                <div class="movement-item">
                    <div class="movement-item-badge">
                        <span class="badge-movement {{ $movement->type }}">{{ $movement->type_label }}</span>
                    </div>
                    <div class="movement-item-info">
                        <div class="movement-item-product">{{ $movement->product->name ?? 'Eliminado' }}</div>
                        <div class="movement-item-detail">{{ $movement->branch->name ?? 'Eliminado' }}</div>
                    </div>
                    <div class="movement-item-qty">
                        <span class="qty-delta {{ in_array($movement->type, ['in']) ? 'positive' : 'negative' }}">
                            {{ in_array($movement->type, ['in']) ? '+' : '-' }}{{ $movement->quantity }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">Sin movimientos recientes</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="dark-card">
            <div class="dark-card-header">
                <h4 class="mb-0" style="font-size: 0.9rem; font-weight: 600;">
                    <i class="bi bi-exclamation-triangle me-2"></i>Stock Bajo
                </h4>
                <a href="{{ route('inventory.low-stock') }}" class="btn-outline-custom">Ver todos</a>
            </div>
            <div class="dark-card-body">
                @php
                $lowStockProducts = \App\Models\Product::with(['category', 'branches'])
                    ->whereHas('branches', fn($q) =>
                        $q->whereRaw('branch_product.stock <= products.minimum_stock')
                            ->when(!auth()->user()->isAdmin(), fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                    )
                    ->limit(5)
                    ->get();
                @endphp
                @forelse($lowStockProducts as $product)
                @php $totalStock = $product->branches->sum('pivot.stock'); @endphp
                <div class="low-stock-item">
                    <div class="low-stock-info">
                        <div class="low-stock-name">{{ $product->name }}</div>
                        <div class="low-stock-category">{{ $product->category->name ?? 'Sin categoría' }}</div>
                    </div>
                    <span class="low-stock-badge {{ $totalStock == 0 ? 'critical' : 'warning' }}">
                        {{ $totalStock }} {{ $product->unit }}
                    </span>
                </div>
                @empty
                <p class="text-muted text-center py-3">No hay productos con stock bajo</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
