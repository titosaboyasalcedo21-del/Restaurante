@extends('layouts.app')

@section('title', 'Configuración del Perfil')

@section('module', 'Perfil')

@section('content')
<div class="page-header mb-4">
    <h3 class="page-title">
        <i class="bi bi-person-gear"></i>
        Configuración del Perfil
    </h3>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="dark-card mb-4">
            <div class="dark-card-header">
                <h4 class="mb-0" style="font-size: 0.9rem; font-weight: 600;">
                    <i class="bi bi-person me-2"></i>Información del Perfil
                </h4>
            </div>
            <div class="dark-card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ auth()->user()->name }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ auth()->user()->email }}" required>
                    </div>

                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-check-lg"></i> Guardar Cambios
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="dark-card mb-4">
            <div class="dark-card-header">
                <h4 class="mb-0" style="font-size: 0.9rem; font-weight: 600;">
                    <i class="bi bi-key me-2"></i>Cambiar Contraseña
                </h4>
            </div>
            <div class="dark-card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-key-fill"></i> Actualizar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="dark-card">
            <div class="dark-card-header">
                <h4 class="mb-0" style="font-size: 0.9rem; font-weight: 600;">
                    <i class="bi bi-info-circle me-2"></i>Información de la Cuenta
                </h4>
            </div>
            <div class="dark-card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="text-muted" style="font-size: 0.8rem;">ROL</div>
                        <div style="font-weight: 600;">
                            @switch(auth()->user()->role)
                                @case('admin')
                                    <span class="badge" style="background: var(--role-admin);">Administrador</span>
                                    @break
                                @case('manager')
                                    <span class="badge" style="background: var(--role-manager);">Gerente</span>
                                    @break
                                @case('employee')
                                    <span class="badge" style="background: var(--role-employee);">Empleado</span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted" style="font-size: 0.8rem;">SUCURSAL</div>
                        <div style="font-weight: 600;">
                            {{ auth()->user()->branch->name ?? 'No asignada' }}
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted" style="font-size: 0.8rem;">MIEMBRO DESDE</div>
                        <div style="font-weight: 600;">
                            {{ auth()->user()->created_at->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
