@extends('layouts.app')
@section('title','Mis Dispositivos')

@section('content')
<div class="container mt-4">
  <h2>📦 Dispositivos ESP32</h2>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <a href="{{ route('devices.create') }}" class="btn btn-uqroo-primary mb-3">+ Nuevo Dispositivo</a>

  <table class="table table-striped">
    <thead>
      <tr>
        <th>ESP32 ID</th>
        <th>Nombre</th>
        <th>Registrado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @foreach($devices as $d)
        <tr>
          <td>{{ $d->esp32_id }}</td>
          <td>{{ $d->nombre }}</td>
          <td>{{ $d->created_at->format('d/M/Y H:i') }}</td>
          <td>
            <form action="{{ route('devices.destroy',$d) }}" method="POST" onsubmit="return confirm('Eliminar dispositivo?')">
              @csrf @method('DELETE')
              <button class="btn btn-outline-danger btn-sm">Eliminar</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $devices->links() }}
</div>
@endsection
