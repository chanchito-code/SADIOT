<ul class="list-group list-group-flush">
  @forelse ($datos as $dato)
    <li class="list-group-item d-flex justify-content-between">
      <span><i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($dato->timestamp)->format('H:i:s d M') }}</span>
      <span class="fw-bold">{{ $dato->valor }}</span>
    </li>
  @empty
    <li class="list-group-item text-muted">Sin lecturas</li>
  @endforelse
</ul>

<div class="mt-2 px-3">
  <nav aria-label="Paginación de sensor {{ $sensor->sensor_uid }}">
    {{ $datos->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
  </nav>
</div>
