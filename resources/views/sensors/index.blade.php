@extends('layouts.app')

@section('title', '📊 Mis Dispositivos')

@section('content')

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="container-fluid mt-4 px-3 px-md-0">
  @if ($devices->isEmpty())
    <div class="alert alert-warning text-center">
      ⚠️ Aún no haz hecho pruebas con sensores.
    </div>
  @else
    @foreach ($devices as $device)
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center"
             data-bs-toggle="collapse"
             data-bs-target="#collapse-device-{{ $device->esp32_id }}"
             style="cursor: pointer;">
          <div>
            <strong>ESP32: {{ $device->esp32_id }}</strong>
            @if($device->nombre)
              <span class="badge bg-light text-dark ms-2">
                {{ $device->nombre }}
              </span>
            @endif
          </div>
          <div class="d-flex align-items-center">
            <small class="me-2">
              Registrado: {{ $device->created_at->format('d M Y, H:i') }}
            </small>
            <i class="bi bi-chevron-down transition-transform" id="icon-device-{{ $device->esp32_id }}"></i>
          </div>
        </div>

        <div id="collapse-device-{{ $device->esp32_id }}" class="collapse show">
          <div class="card-body">
            @if($device->sensors->isEmpty())
              <p class="text-muted">Sin sensores asociados.</p>
            @else
              <div class="row">
                @foreach ($device->sensors as $sensor)
                  <div class="col-md-6 mb-4">
                    <div class="card h-100">
                      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span>
                          {{ ucfirst($sensor->tipo) }}
                          <small>({{ $sensor->sensor_uid }})</small>
                        </span>

                        <a href="{{ route('export.sensor', $sensor->sensor_uid) }}"
                           class="btn btn-sm btn-light"
                           title="Exportar datos de este sensor"
                           target="_blank"
                           rel="noopener noreferrer"
                           onclick="event.stopPropagation();">
                          <i class="bi bi-file-earmark-excel"></i>
                        </a>
                      </div>

                      @php
                        $pageName = 'page_' . $device->esp32_id . '_' . $sensor->sensor_uid;
                        $datos = $sensor->data()
                           ->orderBy('timestamp','desc')
                           ->paginate(5, ['*'], $pageName);
                      @endphp

                      <div id="sensor-data-{{ $sensor->sensor_uid }}" class="sensor-data-container">
                        @include('partials.sensor_data_pagination', compact('sensor','datos'))
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  @endif
</div>

@endsection

@push('styles')
<style>
  .transition-transform { transition: transform .2s ease-in-out; }
  .rotate-180 { transform: rotate(180deg); }
  .pagination .page-link {
    padding: .25rem .5rem;
    font-size: .8rem;
  }
  .pagination {
    margin-bottom: 0;
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    @foreach ($devices as $device)
      const collapseEl = document.getElementById('collapse-device-{{ $device->esp32_id }}');
      const iconEl     = document.getElementById('icon-device-{{ $device->esp32_id }}');

      collapseEl.addEventListener('show.bs.collapse', () =>
        iconEl.classList.remove('rotate-180'));
      collapseEl.addEventListener('hide.bs.collapse', () =>
        iconEl.classList.add('rotate-180'));
    @endforeach

    // AJAX paginación individual por sensor
    document.querySelectorAll('.sensor-data-container').forEach(container => {
      container.addEventListener('click', function(event) {
        const target = event.target;
        if (target.tagName === 'A' && target.closest('nav')) {
          event.preventDefault();
          fetch(target.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => container.innerHTML = html)
            .catch(console.error);
        }
      });
    });
  });
</script>
@endpush
