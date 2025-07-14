@extends('layouts.guest')

@section('title', 'Registro - Paso 3')

@section('content')
<link rel="stylesheet" href="{{ asset('css/formularios.css') }}">
<div class="container mt-5">
  <div class="card mx-auto" style="max-width:400px">
    <div class="card-header bg-primary text-white text-center">
      <h4>🎓 Registro - Paso 3</h4>
      <small>Completa tu perfil</small>
    </div>
    <div class="card-body">
      <form action="{{ route('register.profile.post') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label class="form-label">Nombre de Usuario</label>
          <input type="text" name="username" class="form-control" required>
          @error('username')
            <div class="text-danger small">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Fecha de Nacimiento</label>
          <input type="date" name="birthdate" class="form-control" required>
          @error('birthdate')
            <div class="text-danger small">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <input type="password" name="password" class="form-control" required>
          @error('password')
            <div class="text-danger small">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Confirmar Contraseña</label>
          <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button class="btn btn-success w-100">Finalizar Registro</button>
      </form>
    </div>
  </div>
</div>
@endsection
