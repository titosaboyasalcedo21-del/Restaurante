@extends('layouts.app')

@section('title', 'Stock Bajo')

@section('module', 'Inventario')

@section('content')
<!-- Page Header -->
<div class="page-header mb-4">
    <h3 class="page-title">
        <i class="bi bi-exclamation-triangle"></i>
        Stock Bajo
    </h3>
</div>

<!-- Low Stock Products -->
<div class="dark-card">
    <div class="dark-card-header">
        <h4 class="mb-0" style="font-size: 0.9rem; font-weight: 600;">
            <i class="bi bi-box-seam me-2"></i>Productos con Stock Bajo
        </h4>
    </div>
    <div class="table-responsive">
        <table class="table-dark-custom">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Sucursal</th>
                    <th>Stock Actual</th>
                    <th>Stock Mínimo</th>
                    <th>Estado</th>
                    @can('update', \App\Models\InventoryMovement::class)
                    <th class="text-end">Acción</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                @php
                    $branchStock = $product->branches->first();
                    $stock = $branchStock ? $branchStock->pivot->stock : 0;
                    $minStock = $product->minimum_stock ?? 0;
                    $branchName = $branchStock ? $branchStock->name : '—';
                @endphp
                <tr>
                    <td>
                        <div style="color: var(--text-primary); font-weight: 500;">{{ $product->name }}</div>
                        <span class="sku-code">{{ $product->sku }}</span>
                    </td>
                    <td>
                        @if($product->category)
                            <span class="category-badge">{{ $product->category->name }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $branchName }}</td>
                    <td>
                        <span class="{{ $stock == 0 ? 'text-danger' : 'text-warning' }}" style="font-weight: 600;">
                            {{ $stock }} {{ $product->unit }}
                        </span>
                    </td>
                    <td class="text-muted">{{ $minStock }} {{ $product->unit }}</td>
                    <td>
                        @if($stock == 0)
                        <span class="low-stock-badge critical">SIN STOCK</span>
                        @else
                        <span class="low-stock-badge warning">BAJO</span>
                        @endif
                    </td>
                    @can('update', \App\Models\InventoryMovement::class)
                    <td class="text-end">
                        <a href="{{ route('inventory.adjust', ['type' => 'in', 'product_id' => $product->id]) }}" class="btn-red-sm">
                            <i class="bi bi-plus-lg"></i> Reponer
                        </a>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->isAdmin() ? '7' : '6' }}">
                        <div class="empty-state">
                            <i class="bi bi-check-circle"></i>
                            <p>No hay productos con stock bajo</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
