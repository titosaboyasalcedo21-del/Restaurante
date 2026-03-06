@extends('layouts.app')

@section('title', 'Nueva Orden de Compra')

@section('module', 'Órdenes de Compra')

@section('content')
<div class="page-header mb-4">
    <h3 class="page-title"><i class="bi bi-cart"></i> Nueva Orden de Compra</h3>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('purchase-orders.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Proveedor *</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">Seleccionar proveedor</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sucursal *</label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">Seleccionar sucursal</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fecha de Orden *</label>
                    <input type="date" name="order_date" class="form-control" required value="{{ now()->toDateString() }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fecha Esperada</label>
                    <input type="date" name="expected_date" class="form-control">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Notas</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Crear Orden</button>
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
