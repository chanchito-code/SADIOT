@extends('layouts.app')
@section('title','Dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

<div class="container mt-2">
  @foreach($devices as $device)
    <div class="card mb-3">
      <div 
        class="card-header bg-uqroo-green text-white d-flex justify-content-between align-items-center"
        data-bs-toggle="collapse"
        data-bs-target="#collapse-device-{{ $device->esp32_id }}"
        style="cursor: pointer;">
        <span>
          {{ $device->nombre }} ({{ $device->esp32_id }})
        </span>
        <i class="bi bi-chevron-down transition-transform" id="icon-device-{{ $device->esp32_id }}"></i>
      </div>

      <div id="collapse-device-{{ $device->esp32_id }}" class="collapse show">
        <div class="card-body">
          <div class="row">
            @foreach($device->sensors as $sensor)
              <div class="col-md-6 mb-4">
                <div class="card h-100">
                  <div class="card-header bg-success text-white">
                    {{ ucfirst($sensor->tipo) }} <small>({{ $sensor->sensor_uid }})</small>
                  </div>
                  <div class="card-body">
                    <div class="mb-2">
                      <label for="chart-type-{{ $device->_id }}-{{ $sensor->sensor_uid }}" class="form-label small">
                        Tipo de gráfica:
                      </label>
                      <select class="form-select form-select-sm chart-selector" data-sensor="{{ $device->_id }}-{{ $sensor->sensor_uid }}">
                        <option value="line">Línea</option>
                        <option value="bar">Barras</option>
                        <option value="pie">Pastel</option>
                      </select>
                    </div>
                    <div class="chart-container" style="height:300px;">
                      <canvas id="chart-{{ $device->_id }}-{{ $sensor->sensor_uid }}"></canvas>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  @endforeach
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const charts = {};

  const colorPalette = [
    '#036c39', '#04874a', '#70b376', '#a1c9a7', '#d0e2cc',
    '#f7d04b', '#ffc107', '#ff5722', '#f44336', '#e91e63',
    '#9c27b0', '#3f51b5', '#2196f3', '#00bcd4', '#009688',
    '#4caf50', '#8bc34a', '#cddc39', '#ff9800', '#795548'
  ];

  @foreach($devices as $device)
    @foreach($device->sensors as $sensor)
      (function() {
        const sensorId = '{{ $device->_id }}-{{ $sensor->sensor_uid }}';
        const ctx = document.getElementById('chart-' + sensorId)?.getContext('2d');
        if (!ctx) return;

        @php
          $valores = $sensor->data->pluck('valor')->toArray();
          $etiquetas = $sensor->data->pluck('timestamp')->map(fn($t) => \Carbon\Carbon::parse($t)->format('H:i:s'))->values()->toArray();
        @endphp

        const rawValores = @json($valores);
        const rawEtiquetas = @json($etiquetas);

        function getColors(count) {
          let result = [];
          for (let i = 0; i < count; i++) {
            result.push(colorPalette[i % colorPalette.length]);
          }
          return result;
        }

        function renderChart(tipo) {
          if (charts[sensorId]) {
            charts[sensorId].destroy();
          }

          let valores = [...rawValores];
          let etiquetas = [...rawEtiquetas];

          if (tipo === 'pie') {
            const agrupado = {};
            valores.forEach(v => {
              agrupado[v] = (agrupado[v] || 0) + 1;
            });
            etiquetas = Object.keys(agrupado);
            valores = Object.values(agrupado);
          }

          if (tipo === 'bar') {
            valores = valores.slice(-10);
            etiquetas = etiquetas.slice(-10);
          }

          let backgroundColors = getColors(valores.length);

          let config = {
            type: tipo,
            data: {
              labels: etiquetas,
              datasets: [{
                label: tipo === 'pie' ? 'Distribución de valores' : '{{ ucfirst($sensor->tipo) }}',
                data: valores,
                borderColor: tipo === 'line' ? '#036c39' : backgroundColors,
                backgroundColor: tipo === 'pie' || tipo === 'bar' ? backgroundColors : 'transparent',
                fill: false,
                tension: 0.3,
                pointRadius: tipo === 'line' ? 3 : 0
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  display: tipo !== 'line',
                  position: 'bottom'
                }
              },
              scales: (tipo === 'pie') ? {} : {
                y: {
                  beginAtZero: true,
                  ticks: {
                    stepSize: 1
                  }
                },
                x: {
                  ticks: {
                    autoSkip: true,
                    maxRotation: 45,
                    minRotation: 0
                  }
                }
              }
            }
          };

          charts[sensorId] = new Chart(ctx, config);
        }

        renderChart('line');

        const selector = document.querySelector(`select[data-sensor="${sensorId}"]`);
        if (selector) {
          selector.addEventListener('change', function() {
            renderChart(this.value);
          });
        }
      })();
    @endforeach
  @endforeach
});
</script>
@endpush

