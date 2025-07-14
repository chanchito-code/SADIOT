@extends('layouts.guest')

@section('title', 'Registro - Paso 1')

@section('content')
<link rel="stylesheet" href="{{ asset('css/formularios.css') }}">
<div class="container mt-5">
  <div class="card mx-auto" style="max-width:400px">
    <div class="card-header bg-primary text-white text-center">
      <h4>🎓 Registro - Paso 1</h4>
      <small>Ingresa tu correo para recibir un código</small>
    </div>
    <div class="card-body">
      @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
      @endif

      <form action="{{ route('register.sendCode') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label class="form-label">Correo Electrónico</label>
          <input type="email" name="email" class="form-control" required autofocus>
          @error('email')
            <div class="text-danger small">{{ $message }}</div>
          @enderror
        </div>
        <button class="btn btn-success w-100">Enviar Código</button>
      </form>
    </div>
  </div>
</div>
@endsection
