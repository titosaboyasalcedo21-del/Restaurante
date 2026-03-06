@extends('layouts.app')

@section('title', 'Editar Proveedor')

@section('module', 'Proveedores')

@section('content')
<div class="page-header mb-4">
    <h3 class="page-title"><i class="bi bi-truck"></i> Editar Proveedor</h3>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
            @csrf @method('PATCH')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $supplier->name) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Código</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $supplier->code) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre de Contacto</label>
                    <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name', $supplier->contact_name) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">RUC</label>
                    <input type="text" name="ruc" class="form-control" value="{{ old('ruc', $supplier->ruc) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $supplier->city) }}">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Dirección</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address) }}</textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Notas</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $supplier->notes) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ $supplier->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Activo</label>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
