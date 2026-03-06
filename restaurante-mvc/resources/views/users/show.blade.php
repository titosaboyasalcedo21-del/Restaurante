@extends('layouts.app')

@section('title', 'Ver Usuario')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuarios</a></li>
    <li class="breadcrumb-item active">Ver</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-person me-2 text-primary"></i>Detalles del Usuario</h4>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar-circle mx-auto mb-3">
                        <i class="bi bi-person-fill fs-1"></i>
                    </div>
                    <h5>{{ $user->name }}</h5>
                    <p class="text-muted mb-0">{{ $user->email }}</p>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold">Rol:</div>
                    <div class="col-md-8">
                        @switch($user->role)
                            @case('admin')
                                <span class="badge bg-danger">Administrador</span>
                                @break
                            @case('manager')
                                <span class="badge bg-primary">Gerente</span>
                                @break
                            @case('employee')
                                <span class="badge bg-secondary">Empleado</span>
                                @break
                        @endswitch
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold">Sucursal:</div>
                    <div class="col-md-8">{{ $user->branch->name ?? '—' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 fw-semibold">Creado:</div>
                    <div class="col-md-8">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                </div>

                <hr>

                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i>Editar
                    </a>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline"
                          onsubmit="return confirm('¿Eliminar este usuario?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>Eliminar
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}
</style>
@endsection
