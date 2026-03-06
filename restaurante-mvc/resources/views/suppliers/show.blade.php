@extends('layouts.app')

@section('title', $supplier->name)

@section('module', 'Proveedores')

@section('content')
<div class="page-header mb-4">
    <h3 class="page-title"><i class="bi bi-truck"></i> {{ $supplier->name }}</h3>
    <div>
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Información del Proveedor</div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Código</dt><dd class="col-sm-8">{{ $supplier->code }}</dd>
                    <dt class="col-sm-4">Nombre</dt><dd class="col-sm-8">{{ $supplier->name }}</dd>
                    <dt class="col-sm-4">Contacto</dt><dd class="col-sm-8">{{ $supplier->contact_name }}</dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $supplier->email }}</dd>
                    <dt class="col-sm-4">Teléfono</dt><dd class="col-sm-8">{{ $supplier->phone }}</dd>
                    <dt class="col-sm-4">RUC</dt><dd class="col-sm-8">{{ $supplier->ruc }}</dd>
                    <dt class="col-sm-4">Dirección</dt><dd class="col-sm-8">{{ $supplier->address }}</dd>
                    <dt class="col-sm-4">Ciudad</dt><dd class="col-sm-8">{{ $supplier->city }}</dd>
                    <dt class="col-sm-4">Estado</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-{{ $supplier->is_active ? 'success' : 'secondary' }}">
                            {{ $supplier->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Productos Asociados</div>
            <div class="card-body">
                @if($supplier->products->count() > 0)
                    <ul class="list-group">
                        @foreach($supplier->products as $product)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $product->name }}
                                <span class="badge bg-primary rounded-pill">{{ $product->sku }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No hay productos asociados.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
