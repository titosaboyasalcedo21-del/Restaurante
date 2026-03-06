@extends('layouts.app')

@section('title', 'Orden ' . $purchaseOrder->order_number)

@section('module', 'Órdenes de Compra')

@section('content')
<div class="page-header mb-4">
    <h3 class="page-title">
        <i class="bi bi-cart"></i> Orden: {{ $purchaseOrder->order_number }}
        <span class="badge bg-{{ match($purchaseOrder->status) {
            'draft' => 'secondary',
            'pending' => 'warning',
            'approved' => 'info',
            'received' => 'success',
            'cancelled' => 'danger'
        } }} ms-2">{{ $purchaseOrder->status_label }}</span>
    </h3>
    <div>
        <a href="{{ route('pdf.purchase-order', $purchaseOrder) }}" class="btn btn-danger" target="_blank">
            <i class="bi bi-file-pdf"></i> PDF
        </a>
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Información</div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Número</dt><dd class="col-sm-8">{{ $purchaseOrder->order_number }}</dd>
                    <dt class="col-sm-4">Fecha</dt><dd class="col-sm-8">{{ $purchaseOrder->order_date->format('d/m/Y') }}</dd>
                    <dt class="col-sm-4">Proveedor</dt><dd class="col-sm-8">{{ $purchaseOrder->supplier->name }}</dd>
                    <dt class="col-sm-4">Sucursal</dt><dd class="col-sm-8">{{ $purchaseOrder->branch->name }}</dd>
                    <dt class="col-sm-4">Usuario</dt><dd class="col-sm-8">{{ $purchaseOrder->user->name }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Totales</div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Subtotal</dt><dd class="col-sm-8">S/ {{ number_format($purchaseOrder->subtotal, 2) }}</dd>
                    <dt class="col-sm-4">IGV (18%)</dt><dd class="col-sm-8">S/ {{ number_format($purchaseOrder->tax, 2) }}</dd>
                    <dt class="col-sm-4">Total</dt><dd class="col-sm-8"><strong>S/ {{ number_format($purchaseOrder->total, 2) }}</strong></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Productos</h5>
        @if(in_array($purchaseOrder->status, ['draft', 'pending']))
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="bi bi-plus-lg"></i> Agregar Producto
        </button>
        @endif
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Costo Unit.</th>
                    <th>Total</th>
                    @if(in_array($purchaseOrder->status, ['draft', 'pending']))<th></th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrder->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>S/ {{ number_format($item->unit_cost, 2) }}</td>
                    <td>S/ {{ number_format($item->total, 2) }}</td>
                    @if(in_array($purchaseOrder->status, ['draft', 'pending']))
                    <td>
                        <form action="{{ route('purchase-orders.remove-item', [$purchaseOrder, $item]) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="{{ in_array($purchaseOrder->status, ['draft', 'pending']) ? 5 : 4 }}" class="text-center">Sin productos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(in_array($purchaseOrder->status, ['draft', 'pending']))
<div class="mt-3">
    <form action="{{ route('purchase-orders.approve', $purchaseOrder) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-success">Aprobar Orden</button>
    </form>
</div>
@endif

@if($purchaseOrder->status === 'approved')
<div class="mt-3">
    <form action="{{ route('purchase-orders.receive', $purchaseOrder) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-success" onclick="return confirm('¿Confirmar recepción de mercadería?')">
            <i class="bi bi-check-circle"></i> Recepciones Mercadería
        </button>
    </form>
</div>
@endif
@endsection
