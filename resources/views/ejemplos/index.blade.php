@extends('layouts.app')

@section('title', 'Catálogo de Sensores')

@push('head')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
<div class="container py-1">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-journal-code"></i> Guía IoT – Sensores</h2>
    @auth
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSensorModal">
        <i class="bi bi-plus-circle"></i> Agregar sensor
      </button>
    @endauth
  </div>

  <div class="accordion" id="accordionSensors">
    @foreach($sensores as $sensor)
      @php
        $processed = str_replace(
          ['{{esp32_id}}', '{{serverURL}}'],
          ['TU_ESP32_ID', 'http://TU_IP/api/sensors/data'],
          $sensor->code_snippet
        );
      @endphp

      <div class="accordion-item">
        <h2 class="accordion-header" id="heading{{ $sensor->_id }}">
          <button class="accordion-button collapsed" type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#collapse{{ $sensor->_id }}"
                  aria-expanded="false"
                  aria-controls="collapse{{ $sensor->_id }}">
            {{ $sensor->name }}
          </button>
        </h2>
        <div id="collapse{{ $sensor->_id }}"
             class="accordion-collapse collapse"
             aria-labelledby="heading{{ $sensor->_id }}"
             data-bs-parent="#accordionSensors">
          <div class="accordion-body">

            <p>{{ $sensor->description }}</p>

            @if($sensor->wiring)
              <h6>Conexión:</h6>
              @if(filter_var($sensor->wiring, FILTER_VALIDATE_URL))
                <img src="{{ $sensor->wiring }}" alt="Esquema" class="img-fluid rounded mb-3">
              @else
                <pre class="wiring-ascii">{{ $sensor->wiring }}</pre>
              @endif
            @endif

            <h6 class="mb-2">Código de ejemplo:</h6>
            <pre><code class="language-cpp">{{ $processed }}</code></pre>
            <button class="btn btn-outline-primary mt-2 copy-btn" data-code="{{ e($processed) }}">
              <i class="bi bi-clipboard"></i> Copiar código
            </button>

            @auth
              <div class="mt-3 d-flex gap-2">
                <button class="btn btn-warning btn-edit-sensor" 
                        data-id="{{ $sensor->_id }}"
                        data-name="{{ $sensor->name }}"
                        data-description="{{ $sensor->description }}"
                        data-wiring="{{ $sensor->wiring }}"
                        data-code_snippet="{{ $sensor->code_snippet }}">
                  <i class="bi bi-pencil-square"></i> Editar
                </button>

                <form action="{{ route('sensores-info.destroy', $sensor->_id) }}" method="POST" class="d-inline delete-form">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Eliminar
                  </button>
                </form>
              </div>
            @endauth

          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>

{{-- Modal para agregar sensor --}}
<div class="modal fade" id="addSensorModal" tabindex="-1" aria-labelledby="addSensorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="{{ route('sensores-info.store') }}" method="POST" id="sensorForm">
      @csrf
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="addSensorModalLabel">Agregar Sensor</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          @if ($errors->any() && session('form') == 'create')
            <div class="alert alert-danger">
              <strong>⚠️ Corrige los siguientes errores:</strong>
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
          </div>

          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-control" required>{{ old('description') }}</textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Wiring (URL o ASCII)</label>
            <input type="text" name="wiring" class="form-control" value="{{ old('wiring') }}">
          </div>

          <div class="mb-3">
            <label class="form-label">Código de ejemplo</label>
            <textarea name="code_snippet" class="form-control font-monospace" rows="6" required>{{ old('code_snippet') }}</textarea>
            <div class="form-text">
              Puedes usar <code>@{{esp32_id}}</code> y <code>@{{serverURL}}</code>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Guardar sensor</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Modal para editar sensor --}}
<div class="modal fade" id="editSensorModal" tabindex="-1" aria-labelledby="editSensorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" id="editSensorForm">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title" id="editSensorModalLabel">Editar Sensor</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" id="edit-name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="description" id="edit-description" class="form-control" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Wiring (URL o ASCII)</label>
            <input type="text" name="wiring" id="edit-wiring" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Código de ejemplo</label>
            <textarea name="code_snippet" id="edit-code_snippet" class="form-control font-monospace" rows="6" required></textarea>
            <div class="form-text">
              Puedes usar <code>@{{esp32_id}}</code> y <code>@{{serverURL}}</code>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning">Guardar cambios</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Toast de éxito --}}
@if(session('success'))
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div class="toast bg-success text-white show">
      <div class="d-flex">
        <div class="toast-body">
          {{ session('success') }}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>
@endif
@endsection

@push('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
  <script>hljs.highlightAll();</script>

  {{-- SweetAlert al copiar --}}
  <script>
    document.querySelectorAll('.copy-btn').forEach(button => {
      button.addEventListener('click', () => {
        const code = button.getAttribute('data-code');
        const textarea = document.createElement('textarea');
        textarea.value = code;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        textarea.remove();

        Swal.fire({
          icon: 'success',
          title: '¡Copiado!',
          text: 'El código fue copiado al portapapeles',
          timer: 2000,
          showConfirmButton: false
        });
      });
    });
  </script>

  {{-- Abrir modal editar y rellenar campos --}}
  <script>
    document.querySelectorAll('.btn-edit-sensor').forEach(button => {
      button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const description = button.getAttribute('data-description');
        const wiring = button.getAttribute('data-wiring');
        const code_snippet = button.getAttribute('data-code_snippet');

        // Set form action (PUT URL)
        const form = document.getElementById('editSensorForm');
        form.action = `/sensores-info/${id}`;

        // Fill inputs
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-description').value = description;
        document.getElementById('edit-wiring').value = wiring;
        document.getElementById('edit-code_snippet').value = code_snippet;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editSensorModal'));
        modal.show();
      });
    });
  </script>

  {{-- Confirmación SweetAlert para eliminar --}}
  <script>
    document.querySelectorAll('.delete-form').forEach(form => {
      form.addEventListener('submit', e => {
        e.preventDefault();
        Swal.fire({
          title: '¿Estás seguro?',
          text: "¡No podrás revertir esto!",
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

  {{-- Reabrir modal si hubo errores --}}
  @if ($errors->any())
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        @if(session('form') == 'edit')
          new bootstrap.Modal(document.getElementById('editSensorModal')).show();
        @else
          new bootstrap.Modal(document.getElementById('addSensorModal')).show();
        @endif
      });
    </script>
  @endif

  {{-- Limpiar formularios al cerrar modal --}}
  <script>
    document.getElementById('addSensorModal').addEventListener('hidden.bs.modal', () => {
      document.getElementById('sensorForm').reset();
    });
    document.getElementById('editSensorModal').addEventListener('hidden.bs.modal', () => {
      document.getElementById('editSensorForm').reset();
    });
  </script>
@endpush
