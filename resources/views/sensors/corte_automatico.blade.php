@extends('layouts.app')

@section('title', '📆 Corte Automático Último Día')

@section('content')
<div class="container mt-4">
  <h1 class="mb-4">Corte Automático - Último día con datos</h1>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @isset($device)
    <p><strong>Dispositivo:</strong> {{ $device->esp32_id }} {{ $device->nombre ? '('.$device->nombre.')' : '' }}</p>
  @endisset

  @isset($fechaSeleccionada)
    <p><strong>Fecha del corte:</strong> {{ \Carbon\Carbon::parse($fechaSeleccionada)->format('d M Y') }}</p>
  @endisset

  @isset($datos)
    @if($datos->isEmpty())
      <div class="alert alert-warning">
        No hay datos registrados para este día.
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead class="table-dark">
            <tr>
              <th>Hora</th>
              <th>Sensor</th>
              <th>Tipo</th>
              <th>Valor</th>
            </tr>
          </thead>
          <tbody>
            @foreach($datos as $dato)
              @php
                $sensor = $sensors->firstWhere('_id', $dato->sensor_id);
              @endphp
              <tr>
                <td>{{ \Carbon\Carbon::parse($dato->timestamp)->timezone('America/Cancun')->format('H:i:s') }}</td>
                <td>{{ $sensor?->sensor_uid ?? 'N/A' }}</td>
                <td>{{ ucfirst($sensor?->tipo ?? 'Desconocido') }}</td>
                <td>{{ $dato->valor ?? $dato->value }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Paginación --}}
      <div class="d-flex justify-content-center">
        {{ $datos->links() }}
      </div>

      {{-- Botón de exportar --}}
      <div class="mt-3">
        <a href="{{ route('export.device_date', [$device->esp32_id, $fechaSeleccionada]) }}" class="btn btn-success">
          <i class="bi bi-download"></i> Exportar corte a CSV
        </a>
      </div>
    @endif
  @endisset

  <div class="mt-4">
    <a href="{{ route('sensors.index') }}" class="btn btn-secondary">
      <i class="bi bi-arrow-left"></i> Volver a Sensores
    </a>
  </div>
</div>
@endsection
