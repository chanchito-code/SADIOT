@extends('layouts.guest')

@section('title', 'Registro - Paso 2')

@section('content')
<link rel="stylesheet" href="{{ asset('css/formularios.css') }}">
<div class="container mt-5">
  <div class="card mx-auto" style="max-width:400px">
    <div class="card-header bg-primary text-white text-center">
      <h4>🎓 Registro - Paso 2</h4>
      <small>Ingresa el código que recibiste en tu correo</small>
    </div>
    <div class="card-body">
      <form action="{{ route('register.verifyCode.post') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label class="form-label">Código de Verificación</label>
          <input type="text" name="code" class="form-control" required>
          @error('code')
            <div class="text-danger small">{{ $message }}</div>
          @enderror
        </div>
        <button class="btn btn-success w-100">Verificar Código</button>
      </form>
    </div>
  </div>
</div>
@endsection
