@extends('layouts.app')

@section('title', 'Registrar ESP32')

@section('content')
<div class="container mt-4">
    <h2>➕ Registrar nuevo dispositivo</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Se encontraron errores:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('devices.store') }}" method="POST" class="card shadow p-4">
        @csrf

        <div class="mb-3">
            <label for="esp32_id" class="form-label fw-bold">ID del dispositivo ESP32</label>
            <input 
                type="text" 
                name="esp32_id" 
                id="esp32_id" 
                class="form-control @error('esp32_id') is-invalid @enderror" 
                value="{{ old('esp32_id') }}" 
                placeholder="Ej. ESP32-001" 
                required
            >
            <div class="form-text">Este ID debe coincidir con el que tu ESP32 envía.</div>
            @error('esp32_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="nombre" class="form-label fw-bold">Nombre o ubicación del dispositivo</label>
            <input 
                type="text" 
                name="nombre" 
                id="nombre" 
                class="form-control @error('nombre') is-invalid @enderror" 
                value="{{ old('nombre') }}" 
                placeholder="Ej. Laboratorio 1, Aula B, Tester PIR"
            >
            <div class="form-text">Este nombre es para que lo identifiques fácilmente en la lista.</div>
            @error('nombre')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-uqroo-primary">Registrar dispositivo</button>
        </div>
    </form>
</div>
@endsection
