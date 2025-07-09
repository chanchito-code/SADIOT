@extends('layouts.app')

@section('title', 'Editar Perfil')

@push('head')
<link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
@endpush

@section('content')
<div class="container mt-3">
    <div class="card shadow border-0">
        <div class="card-header bg-success text-white">
            <h4><i class="bi bi-person-circle"></i> Editar Perfil</h4>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('perfil.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="username" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="username" name="username"
                        value="{{ old('username', $user->username) }}" required>
                    @error('username')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="current_password" class="form-label">Contraseña Actual <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                    @error('current_password')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Nueva Contraseña <small class="text-muted">(opcional)</small></label>
                    <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                    @error('password')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <button type="submit" class="btn-uqroo-primary">
                        <i class="bi bi-save"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
