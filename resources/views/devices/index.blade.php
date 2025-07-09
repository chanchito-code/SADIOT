@extends('layouts.app')
@section('title','Mis Dispositivos')

@section('content')

<style>
  .pulse {
    animation: pulse-animation 1.2s infinite;
  }

  @keyframes pulse-animation {
    0%   { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.5); }
    70%  { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
  }
</style>

<div class="container mt-2">
  <h2>📦 Placas ESP32</h2>

  <a href="{{ route('devices.create') }}" class="btn btn-success btn-lg rounded-3 shadow pulse px-4 py-2 mb-3">
    <i class="bi bi-plus-circle-fill me-1"></i> Nueva placa
  </a>

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
            <form action="{{ route('devices.destroy', $d) }}" method="POST" class="delete-form">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-outline-danger btn-sm delete-btn">Eliminar</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $devices->links() }}
</div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  {{-- Alerta de éxito después de crear --}}
  @if(session('success'))
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: '{{ session('success') }}',
          timer: 2500,
          showConfirmButton: false
        });
      });
    </script>
  @endif

  {{-- Confirmación con SweetAlert al eliminar --}}
  <script>
    document.querySelectorAll('.delete-form').forEach(form => {
      form.addEventListener('submit', function (e) {
        e.preventDefault();

        Swal.fire({
          title: '¿Estás seguro?',
          text: 'Esta acción eliminará el dispositivo.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
  </script>
@endpush
