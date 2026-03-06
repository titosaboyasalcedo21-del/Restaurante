@extends('layouts.app')

@section('title', 'Movimientos de Inventario')

@section('module', 'Inventario')

@section('content')
<!-- Page Header -->
<div class="page-header mb-4">
    <h3 class="page-title">
        <i class="bi bi-arrow-left-right"></i>
        Movimientos
    </h3>
</div>

<!-- Filters -->
<div class="filter-card">
    <form method="GET" action="{{ route('inventory.movements') }}" class="row g-3">
        <div class="col-md-3">
            <select name="type" class="form-select-dark w-100">
                <option value="">Todos los tipos</option>
                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Entrada</option>
                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Salida</option>
                <option value="adjust" {{ request('type') == 'adjust' ? 'selected' : '' }}>Ajuste</option>
                <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transferencia</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="branch_id" class="form-select-dark w-100">
                <option value="">Todas las sucursales</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control-dark w-100" value="{{ request('date') }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn-outline-custom flex-fill">
                <i class="bi bi-search"></i> Filtrar
            </button>
            <a href="{{ route('inventory.movements') }}" class="btn-outline-custom">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>
</div>

<!-- Movements Table -->
<div class="dark-card">
    <div class="table-responsive">
        <table class="table-dark-custom">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Producto</th>
                    <th>Sucursal</th>
                    <th>Usuario</th>
                    <th>Cantidad</th>
                    <th>Stock Anterior</th>
                    <th>Stock Nuevo</th>
                    <th>Hora</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                <tr>
                    <td>
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
                    </td>
                    <td>
                        <div style="color: var(--text-primary); font-weight: 500;">{{ $movement->product->name ?? 'Producto eliminado' }}</div>
                        @if($movement->product)
                        <span class="sku-code">{{ $movement->product->sku }}</span>
                        @endif
                    </td>
                    <td>{{ $movement->branch->name ?? 'Eliminado' }}</td>
                    <td>{{ $movement->user->name ?? 'Eliminado' }}</td>
                    <td>
                        <span class="qty-delta {{ in_array($movement->type, ['in', 'adjust']) ? 'positive' : 'negative' }}">
                            {{ in_array($movement->type, ['in', 'adjust']) ? '+' : '-' }}{{ $movement->quantity }}
                        </span>
                    </td>
                    <td class="text-muted">{{ $movement->previous_stock ?? '—' }}</td>
                    <td class="text-muted">{{ $movement->new_stock ?? '—' }}</td>
                    <td class="text-muted">{{ $movement->created_at->format('H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>No se encontraron movimientos</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($movements->hasPages())
    <div class="pagination-custom">
        {{ $movements->links() }}
    </div>
    @endif
</div>
@endsection
