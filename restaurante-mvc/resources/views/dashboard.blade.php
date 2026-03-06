@extends('layouts.app')

@section('title', 'Dashboard')

@section('module', 'Dashboard')

@php
$chartData = [
    'movements_in' => \App\Models\InventoryMovement::where('type', 'in')->whereDate('created_at', '>=', now()->subDays(30))->count(),
    'movements_out' => \App\Models\InventoryMovement::where('type', 'out')->whereDate('created_at', '>=', now()->subDays(30))->count(),
    'movements_adjust' => \App\Models\InventoryMovement::where('type', 'adjust')->whereDate('created_at', '>=', now()->subDays(30))->count(),
    'movements_transfer' => \App\Models\InventoryMovement::where('type', 'transfer')->whereDate('created_at', '>=', now()->subDays(30))->count(),
    'daily_labels' => collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('d/m'))->toArray(),
    'daily_data' => collect(range(6, 0))->map(fn($i) => \App\Models\InventoryMovement::whereDate('created_at', now()->subDays($i))->count())->toArray(),
    'branch_labels' => \App\Models\Branch::active()->pluck('name')->toArray(),
    'branch_stock' => \App\Models\Branch::active()->with('products')->get()->map(fn($b) => $b->products->sum('pivot.stock'))->toArray(),
    'category_labels' => \App\Models\Category::active()->pluck('name')->toArray(),
    'category_counts' => \App\Models\Category::active()->withCount('products')->pluck('products_count')->toArray(),
];
@endphp

@section('content')
<!-- Page Header -->
<div class="page-header mb-4">
    <h3 class="page-title">
        <i class="bi bi-grid-1x2"></i>
        Dashboard
    </h3>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card products">
        <div class="stat-label">Productos Activos</div>
        <div class="stat-value">{{ $stats['products'] ?? 0 }}</div>
        <div class="stat-delta positive">
            <i class="bi bi-arrow-up"></i> {{ $stats['products'] ?? 0 }} total
        </div>
    </div>

    <div class="stat-card branches">
        <div class="stat-label">Sucursales</div>
        <div class="stat-value">{{ $stats['branches'] ?? 0 }}</div>
        <div class="stat-delta positive">
            <i class="bi bi-shop"></i> Activas
        </div>
    </div>

    <div class="stat-card low-stock">
        <div class="stat-label">Stock Bajo</div>
        <div class="stat-value">{{ $stats['lowStock'] ?? 0 }}</div>
        <div class="stat-delta {{ ($stats['lowStock'] ?? 0) > 3 ? 'negative' : 'warning' }}">
            <i class="bi bi-exclamation-triangle"></i> Revisar
        </div>
    </div>

    <div class="stat-card movements">
        <div class="stat-label">Movimientos Hoy</div>
        <div class="stat-value">{{ $stats['movementsToday'] ?? 0 }}</div>
        <div class="stat-delta positive">
            <i class="bi bi-arrow-left-right"></i> Hoy
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Movimientos por Tipo (Últimos 30 días)</h5>
            </div>
            <div class="card-body">
                <canvas id="movementsTypeChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar me-2"></i>Movimientos Diarios (Últimos 7 días)</h5>
            </div>
            <div class="card-body">
                <canvas id="dailyMovementsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Stock por Sucursal</h5>
            </div>
            <div class="card-body">
                <canvas id="stockByBranchChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-tags me-2"></i>Productos por Categoría</h5>
            </div>
            <div class="card-body">
                <canvas id="productsByCategoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Movimientos por Tipo</h5></div>
            <div class="card-body"><canvas id="movementsTypeChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Movimientos Diarios</h5></div>
            <div class="card-body"><canvas id="dailyMovementsChart"></canvas></div>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-building me-2"></i>Stock por Sucursal</h5></div>
            <div class="card-body"><canvas id="stockByBranchChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-tags me-2"></i>Productos por Categoría</h5></div>
            <div class="card-body"><canvas id="productsByCategoryChart"></canvas></div>
        </div>
    </div>
</div>

<!-- Two Column Layout -->
<div class="two-column-layout">
    <!-- Recent Movements -->
    <div class="column-card">
        <div class="column-header">
            <h4 class="column-title">
                <i class="bi bi-clock-history me-2"></i>Movimientos Recientes
            </h4>
            <a href="{{ route('inventory.movements') }}" class="btn-outline-custom">
                Ver todos
            </a>
        </div>
        <div class="column-body">
            @forelse($recentMovements as $movement)
            <div class="movement-item">
                <div class="movement-item-badge">
                    <span class="badge-movement {{ $movement->type }}">
                        @switch($movement->type)
                            @case('in')
                                <i class="bi bi-box-arrow-in-down"></i>
                                @break
                            @case('out')
                                <i class="bi bi-box-arrow-up"></i>
                                @break
                            @case('adjust')
                                <i class="bi bi-sliders"></i>
                                @break
                            @case('transfer')
                                <i class="bi bi-arrow-left-right"></i>
                                @break
                        @endswitch
                        {{ $movement->type_label }}
                    </span>
                </div>
                <div class="movement-item-info">
                    <div class="movement-item-product">{{ $movement->product->name ?? 'Producto eliminado' }}</div>
                    <div class="movement-item-detail">
                        {{ $movement->branch->name ?? 'Sucursal eliminada' }} •
                        {{ $movement->user->name ?? 'Usuario eliminado' }}
                    </div>
                </div>
                <div class="movement-item-qty">
                    <span class="qty-delta {{ in_array($movement->type, ['in', 'adjust']) ? 'positive' : 'negative' }}">
                        {{ in_array($movement->type, ['in', 'adjust']) ? '+' : '-' }}{{ $movement->quantity }}
                    </span>
                    <div class="movement-item-time">{{ $movement->created_at->format('H:i') }}</div>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>No hay movimientos recientes</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Low Stock Panel -->
    <div class="column-card">
        <div class="column-header">
            <h4 class="column-title">
                <i class="bi bi-exclamation-triangle me-2"></i>Stock Bajo
            </h4>
            <a href="{{ route('inventory.low-stock') }}" class="btn-outline-custom">
                Ver todos
            </a>
        </div>
        <div class="column-body">
            @forelse($lowStockItems as $product)
            <div class="low-stock-item">
                <div class="low-stock-info">
                    <div class="low-stock-name">{{ $product->name }}</div>
                    <div class="low-stock-category">{{ $product->category->name ?? 'Sin categoría' }}</div>
                </div>
                <span class="low-stock-badge {{ $product->total_stock == 0 ? 'critical' : 'warning' }}">
                    @if($product->total_stock == 0)
                        SIN STOCK
                    @else
                        {{ $product->total_stock }} {{ $product->unit }}
                    @endif
                </span>
            </div>
            @empty
            <div class="empty-state">
                <i class="bi bi-check-circle"></i>
                <p>No hay productos con stock bajo</p>
            </div>
            @endforelse

            @if(auth()->user()->isAdmin() && $lowStockItems->isNotEmpty())
            <div class="mt-3 text-center">
                <a href="{{ route('inventory.adjust') }}" class="btn-primary-custom">
                    <i class="bi bi-plus-lg"></i>
                    Reponer Stock
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($chartData);
    initDashboardCharts(chartData);
});
</script>
@endpush
