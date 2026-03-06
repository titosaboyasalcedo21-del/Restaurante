@extends('layouts.app')

@section('title', 'Editar Orden ' . $purchaseOrder->order_number)

@section('module', 'Órdenes de Compra')

@section('content')
<div class="page-header mb-4">
    <h3 class="page-title"><i class="bi bi-cart"></i> Editar Orden: {{ $purchaseOrder->order_number }}</h3>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">Productos de la Orden</div>
            <div class="card-body">
                <form action="{{ route('purchase-orders.add-item', $purchaseOrder) }}" method="POST" class="row g-3 mb-4">
                    @csrf
                    <div class="col-md-5">
                        <select name="product_id" class="form-select" required>
                            <option value="">Seleccionar producto</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="quantity" class="form-control" placeholder="Cantidad" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="unit_cost" class="form-control" placeholder="Costo" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Agregar</button>
                    </div>
                </form>

                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Costo</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrder->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>S/ {{ number_format($item->unit_cost, 2) }}</td>
                            <td>S/ {{ number_format($item->total, 2) }}</td>
                            <td>
                                <form action="{{ route('purchase-orders.remove-item', [$purchaseOrder, $item]) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">No hay productos agregados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Detalles de la Orden</div>
            <div class="card-body">
                <form method="POST" action="{{ route('purchase-orders.update', $purchaseOrder) }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">Proveedor</label>
                        <select name="supplier_id" class="form-select" required>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $supplier->id == $purchaseOrder->supplier_id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sucursal</label>
                        <select name="branch_id" class="form-select" required>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $branch->id == $purchaseOrder->branch_id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="order_date" class="form-control" value="{{ $purchaseOrder->order_date->toDateString() }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea name="notes" class="form-control" rows="2">{{ $purchaseOrder->notes }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
