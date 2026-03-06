@extends('layouts.app')
@section('title', 'Editar Sucursal')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('branches.index') }}">Sucursales</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2 text-warning"></i>Editar Sucursal</h4>
    <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('branches.update', $branch) }}">
            @csrf @method('PUT')
            @include('branches._form')
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Actualizar</button>
                <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
