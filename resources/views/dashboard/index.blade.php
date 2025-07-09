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
      <!-- Icono de Bootstrap Icons -->
      <i class="bi bi-chevron-down transition-transform"
        id="icon-device-{{ $device->esp32_id }}"
      ></i>
    </div>

    <div id="collapse-device-{{ $device->esp32_id }}"
      class="collapse show">
      <div class="card-body">
        <div class="row">
          @foreach($device->sensors as $sensor)
            <div class="col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-header bg-success text-white">
                  {{ ucfirst($sensor->tipo) }}
                </div>
                <div class="card-body">
                  <div class="chart-container">
                    <canvas id="chart-{{ $sensor->sensor_uid }}"></canvas>
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
document.addEventListener('DOMContentLoaded', ()=> {
  @foreach($devices as $device)
  @foreach($device->sensors as $sensor)
    (function(){
      const ctx = document.getElementById('chart-{{ $sensor->sensor_uid }}').getContext('2d');
      const data = {!! $sensor->data->pluck('valor')->toJson() !!};
      const labels = {!! $sensor->data->pluck('timestamp')
          ->map(fn($t)=>\Carbon\Carbon::parse($t)->format('H:i:s'))->toJson() !!};

      new Chart(ctx, {
        type: 'line',
        data: { labels, datasets: [{
          label: '{{ ucfirst($sensor->tipo) }}',
          data,
          fill: false,
          borderColor: '#036c39',
          tension: 0.3,
          pointRadius: 2
        }]},
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: { y: { beginAtZero: true } }
        }
      });
    })();
  @endforeach
@endforeach

});
</script>
@endpush

