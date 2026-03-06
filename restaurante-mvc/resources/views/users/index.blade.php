@extends('layouts.app')

@section('title', 'Usuarios')

@section('module', 'Usuarios')

@section('content')
<!-- Page Header -->
<div class="page-header mb-4">
    <h3 class="page-title">
        <i class="bi bi-people"></i>
        Usuarios
    </h3>
    @can('create', App\Models\User::class)
    <a href="{{ route('users.create') }}" class="btn-primary-custom">
        <i class="bi bi-plus-lg"></i>
        Nuevo Usuario
    </a>
    @endcan
</div>

<!-- Users List -->
@forelse($users as $user)
<div class="user-card">
    <div class="user-card-avatar">
        <div class="user-avatar {{ $user->role }}">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
    </div>
    <div class="user-card-info">
        <div class="user-card-name">{{ $user->name }}</div>
        <div class="user-card-email">{{ $user->email }}</div>
    </div>
    <span class="user-card-badge {{ $user->role }}">
        @switch($user->role)
            @case('admin')
                <i class="bi bi-shield-check"></i>
                @break
            @case('manager')
                <i class="bi bi-person-badge"></i>
                @break
            @case('employee')
                <i class="bi bi-person"></i>
                @break
        @endswitch
        {{ $user->role_label }}
    </span>
    <div class="user-card-branch">
        <i class="bi bi-shop"></i>
        {{ $user->isAdmin() ? '—' : ($user->branch->name ?? 'Sin asignar') }}
    </div>
    <div class="user-card-actions">
        <a href="{{ route('users.show', $user) }}" class="btn-dark-sm" title="Ver">
            <i class="bi bi-eye"></i>
        </a>
        @can('update', $user)
        <a href="{{ route('users.edit', $user) }}" class="btn-red-sm" title="Editar">
            <i class="bi bi-pencil"></i>
        </a>
        @endcan
        @can('delete', $user)
        @if($user->id !== auth()->id())
        <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger-sm" title="Eliminar">
                <i class="bi bi-trash"></i>
            </button>
        </form>
        @endif
        @endcan
        @if(auth()->user()->isAdmin() && $user->id !== auth()->id())
        <form method="POST" action="{{ route('users.reset-password', $user) }}" class="d-inline" onsubmit="return confirm('¿Resetear contraseña de {{ $user->name }}? Se le enviará una contraseña temporal por correo.')">
            @csrf
            <button type="submit" class="btn-key" title="Resetear contraseña">
                <i class="bi bi-key"></i>
            </button>
        </form>
        @endif
    </div>
</div>
@empty
<div class="dark-card">
    <div class="empty-state">
        <i class="bi bi-people"></i>
        <p>No hay usuarios registrados</p>
        @can('create', App\Models\User::class)
        <a href="{{ route('users.create') }}" class="btn-primary-custom mt-3">
            <i class="bi bi-plus-lg"></i>
            Crear Primer Usuario
        </a>
        @endcan
    </div>
</div>
@endforelse

@if($users->hasPages())
<div class="pagination-custom mt-4">
    {{ $users->links() }}
</div>
@endif
@endsection
