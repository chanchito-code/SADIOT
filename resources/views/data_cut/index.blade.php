@extends('layouts.app')

@section('title', '📆 Corte de Datos por Día')

@section('content')
<div class="container mt-4">

  <h1 class="mb-4">Corte de Datos por Día</h1>

  {{-- Formulario --}}
  <form action="{{ route('data_cut.show') }}" method="POST" class="row gy-3 align-items-end">
    @csrf

    <div class="col-md-4">
      <label for="esp32_id" class="form-label">Selecciona dispositivo</label>
      <select name="esp32_id" id="esp32_id"
              class="form-select @error('esp32_id') is-invalid @enderror">
        <option value="">— Elige una placa —</option>
        @foreach($devices as $dev)
          <option value="{{ $dev->esp32_id }}"
            @if(isset($selectedDevice) && $selectedDevice == $dev->esp32_id) selected @endif>
            {{ $dev->esp32_id }} @if($dev->nombre) ({{ $dev->nombre }}) @endif
          </option>
        @endforeach
      </select>
      @error('esp32_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
      <label for="date" class="form-label">Selecciona fecha</label>
      <input type="date" name="date" id="date"
            min="{{ now()->subDays(15)->toDateString() }}"
            max="{{ now()->toDateString() }}"
            class="form-control @error('date') is-invalid @enderror"
            value="{{ $selectedDate ?? '' }}">
      @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-search"></i> Ver datos
      </button>
    </div>
  </form>

  @isset($datos)
    <hr class="my-4">

    @if($datos->isEmpty())
      <div class="alert alert-warning">
        No hay lecturas para {{ $selectedDate }} en {{ $selectedDevice }}.
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-striped">
          <thead class="table-dark">
            <tr>
              <th>Hora</th>
              <th>Sensor UID</th>
              <th>Tipo</th>
              <th>Valor</th>
            </tr>
          </thead>
          <tbody>
            @foreach($datos as $d)
              <tr>
                <td>{{ \Carbon\Carbon::parse($d->timestamp)
                          ->timezone('America/Cancun')
                          ->format('H:i:s') }}</td>
                <td>{{ optional($d->sensor)->sensor_uid ?? 'N/A' }}</td>
                <td>{{ ucfirst(optional($d->sensor)->tipo ?? '—') }}</td>
                <td>{{ $d->valor }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
        <div class="mt-3">
            <a href="{{ route('data_cut.export', [
                'esp32_id' => $selectedDevice,
                'date'     => $selectedDate,
                ]) }}"
            class="btn btn-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Exportar a Excel
            </a>
        </div>
    @endif
  @endisset

</div>
@endsection
