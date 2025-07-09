@extends('layouts.app')

@section('title', 'Registro de Usuario')

@section('content')
<link rel="stylesheet" href="{{ asset('css/registro.css') }}">

<div class="container mt-0" >
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white text-center">
            <h4>🎓 Registro de Usuario</h4>
            <small>Acceso al sistema educativo universitario</small>
        </div>
        <div class="card-body">
            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="username" class="form-label">Nombre de Usuario</label>
                    <input type="text" placeholder="Ej. Juan Pérez" id="username" name="username" class="form-control" required>
                    @error('username')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" placeholder="Ej. correo@example.com" id="email" name="email" class="form-control" required>
                    @error('email')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" placeholder="Mínimo 8 caracteres" id="password" name="password" class="form-control" required>
                    @error('password')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100" id="register-btn">Registrar</button>
                 <!-- Botón Cancelar -->
                    <a href="{{ url()->previous() }}"
                    class="btn btn-outline-secondary w-100 mt-2"
                    role="button">
                    Cancelar
                    </a>
            </form>
        </div>
    </div>
</div>
@endsection
