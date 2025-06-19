@extends('layouts.app')

@section('title', 'Inicio de Sesión')

@section('content')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
<div class="container mt-5" style="max-width: 450px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white text-center">
            <h4 class="mb-0">🔐 Iniciar Sesión</h4>
            <small>Accede al panel universitario</small>
        </div>
        <div class="card-body">
            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="login" class="form-label">Correo</label>
                    <input type="text" placeholder="Ej. usuario@gmail.com" name="login" id="login" class="form-control" value="{{ old('login') }}" required autofocus>
                    @error('login')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    @error('password')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Recuérdame</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
        <div class="card-footer text-center">
            <small>¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></small>
        </div>
    </div>
</div>
@endsection
