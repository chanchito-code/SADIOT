@extends('layouts.guest')

@section('title', "Corte Diario - Dispositivo {{ $deviceId }}")

@push('styles')
<link href="{{ asset('css/corte.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container mt-4">
    <a href="{{ route('sensors.index') }}" class="btn btn-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Regresar a Sensores
    </a>

    <h1>Corte Diario - Dispositivo {{ $deviceId }}</h1>

    {{-- Formulario para seleccionar fecha --}}
    <form action="{{ route('sensor.datos_por_dia.general') }}" method="GET" class="mb-4 d-flex align-items-center gap-2">
        <input type="hidden" name="device" value="{{ $deviceId }}">

        <label for="fecha" class="form-label mb-0">Selecciona una fecha:</label>
        <select name="fecha" id="fecha" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">-- Todas las fechas --</option>
            @foreach ($fechas as $fecha)
                <option value="{{ $fecha->format('Y-m-d') }}"
                    {{ (isset($fechaSeleccionada) && $fechaSeleccionada == $fecha->format('Y-m-d')) ? 'selected' : '' }}>
                    {{ $fecha->format('d M Y') }}
                </option>
            @endforeach
        </select>
    </form>

    @if ($datos->isEmpty())
        <div class="alert alert-info">No hay datos para la fecha seleccionada.</div>
    @else
        <table class="table table-striped table-bordered table-sm">
            <thead>
                <tr>
                    <th>Sensor UID</th>
                    <th>Tipo</th>
                    <th>Fecha y Hora</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datos as $dato)
                    @php
                        $sensor = $sensors->firstWhere('_id', $dato->sensor_id);
                    @endphp
                    <tr>
                        <td>{{ $sensor->sensor_uid ?? 'Desconocido' }}</td>
                        <td>{{ ucfirst($sensor->tipo ?? 'N/A') }}</td>
                        <td>{{ \Carbon\Carbon::parse($dato->timestamp)->format('d M Y H:i:s') }}</td>
                        <td>{{ $dato->valor ?? $dato->value ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $datos->withQueryString()->links() }}
    @endif
</div>
@endsection
