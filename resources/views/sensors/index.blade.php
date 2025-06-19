@extends('layouts.app')

@section('title', '📊 Mis Dispositivos')

@section('content')
<h1>Botones Accion Corte </h1>
<div class="container-fluid mt-4 px-3 px-md-0">
  @if ($devices->isEmpty())
    <div class="alert alert-warning text-center">
      ⚠️ Aún no tienes dispositivos registrados.
    </div>
  @else
    @foreach ($devices as $device)
      <div class="card shadow-sm mb-4">
        <div
          class="card-header bg-success text-white d-flex justify-content-between align-items-center"
          data-bs-toggle="collapse"
          data-bs-target="#collapse-device-{{ $device->esp32_id }}"
          style="cursor: pointer;"
        >
          <div>
            <strong>ESP32: {{ $device->esp32_id }}</strong>
            @if($device->nombre)
              <span class="badge bg-light text-dark ms-2">{{ $device->nombre }}</span>
            @endif
          </div>
          <div class="d-flex align-items-center">
            <small class="me-2">Registrado: {{ $device->created_at->format('d M Y, H:i') }}</small>

            <!-- Botón exportar todos los sensores del dispositivo -->
            <a href="{{ route('export.device', $device->esp32_id) }}"
              class="btn btn-sm btn-light me-2"
              onclick="event.stopPropagation();"
              title="Exportar todos los sensores de este dispositivo">
              <i class="bi bi-file-earmark-excel"></i>
            </a>

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
                      <div
                        class="card-header bg-primary text-white d-flex justify-content-between align-items-center"
                        style="cursor: default;">
                        <span>
                          {{ ucfirst($sensor->tipo) }} <small>({{ $sensor->sensor_uid }})</small>
                        </span>

                        <!-- Botón exportar sensor individual -->
                        <a href="{{ route('export.sensor', $sensor->sensor_uid) }}"
                          class="btn btn-sm btn-outline-light"
                          onclick="event.stopPropagation();"
                          title="Exportar datos del sensor a Excel">
                          <i class="bi bi-file-earmark-spreadsheet"></i>
                        </a>
                      </div>

                      {{-- Contenido AJAX para datos y paginación --}}
                      <div id="sensor-data-{{ $sensor->sensor_uid }}" class="sensor-data-container">
                        <ul class="list-group list-group-flush">
                          @forelse ($sensor->data()->orderBy('timestamp', 'desc')->paginate(5) as $dato)
                            <li class="list-group-item d-flex justify-content-between">
                              <span><i class="bi bi-clock"></i>
                                {{ \Carbon\Carbon::parse($dato->timestamp)->format('H:i:s d M') }}
                              </span>
                              <span class="fw-bold">{{ $dato->valor }}</span>
                            </li>
                          @empty
                            <li class="list-group-item text-muted">Sin lecturas</li>
                          @endforelse
                        </ul>

                        <div class="mt-2 px-3">
                          <nav aria-label="Paginación de sensor {{ $sensor->sensor_uid }}">
                            {{ $sensor->data()
                                ->orderBy('timestamp', 'desc')
                                ->paginate(5)
                                ->onEachSide(1)
                                ->links('pagination::bootstrap-5') }}
                          </nav>
                        </div>
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
    // Toggle para dispositivos (sidebar)
    @foreach ($devices as $device)
      const collapseDev{{ $device->esp32_id }} = document.getElementById('collapse-device-{{ $device->esp32_id }}');
      const iconDev{{ $device->esp32_id }} = document.getElementById('icon-device-{{ $device->esp32_id }}');

      collapseDev{{ $device->esp32_id }}.addEventListener('show.bs.collapse', () =>
        iconDev{{ $device->esp32_id }}.classList.remove('rotate-180'));
      collapseDev{{ $device->esp32_id }}.addEventListener('hide.bs.collapse', () =>
        iconDev{{ $device->esp32_id }}.classList.add('rotate-180'));
    @endforeach

    // Interceptar clicks en paginación de datos de sensores y cargar con AJAX
    document.querySelectorAll('.sensor-data-container').forEach(container => {
      container.addEventListener('click', function(event) {
        const target = event.target;

        if(target.tagName === 'A' && target.closest('nav')) {
          event.preventDefault();
          const url = target.getAttribute('href');
          if(!url) return;

          // Extraemos sensor UID del id del contenedor
          const sensorUid = container.id.replace('sensor-data-', '');

          fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          })
          .then(response => response.text())
          .then(html => {
            // Reemplazamos el contenido con la respuesta parcial
            container.outerHTML = html;

            // Reasignar eventos de paginación al nuevo contenido
            const newContainer = document.getElementById('sensor-data-' + sensorUid);
            if (newContainer) {
              newContainer.addEventListener('click', arguments.callee);
            }
          })
          .catch(err => console.error('Error al cargar paginación AJAX:', err));
        }
      });
    });
  });
</script>
@endpush
