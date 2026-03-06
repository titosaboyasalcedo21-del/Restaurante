@extends('layouts.app')

@section('title', 'Órdenes de Compra')

@section('module', 'Órdenes de Compra')

@section('content')
<div class="page-header mb-4">
    <h3 class="page-title"><i class="bi bi-cart"></i> Órdenes de Compra</h3>
    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nueva Orden
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Número de orden..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprobado</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Recibido</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Sucursal</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->order_date->format('d/m/Y') }}</td>
                        <td>{{ $order->supplier->name }}</td>
                        <td>{{ $order->branch->name }}</td>
                        <td>S/ {{ number_format($order->total, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ match($order->status) {
                                'draft' => 'secondary',
                                'pending' => 'warning',
                                'approved' => 'info',
                                'received' => 'success',
                                'cancelled' => 'danger'
                            } }}">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('purchase-orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(in_array($order->status, ['draft', 'pending']))
                            <a href="{{ route('purchase-orders.edit', $order) }}" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            <a href="{{ route('pdf.purchase-order', $order) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                                <i class="bi bi-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">No hay órdenes de compra.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    </div>
</div>
@endsection
