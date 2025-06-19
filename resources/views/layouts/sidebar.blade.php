<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white border-end shadow-sm pt-4">
  <div class="position-sticky px-3">
    <ul class="nav flex-column">
      <li class="nav-item mb-3">
        <a href="{{ route('dashboard') }}" class="nav-link fw-semibold {{ request()->routeIs('dashboard') ? 'text-success' : 'text-dark' }}">
          <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
      </li>
      <li class="nav-item mb-3">
        <a href="{{ route('sensors.index') }}" class="nav-link fw-semibold {{ request()->routeIs('sensors.index') ? 'text-success' : 'text-dark' }}">
          <i class="bi bi-graph-up me-2"></i> Sensores
        </a>
      </li>
      <li class="nav-item mb-3">
        <a href="{{ route('devices.index') }}" class="nav-link fw-semibold {{ request()->routeIs('devices.index') ? 'text-success' : 'text-dark' }}">
          <i class="bi bi-cpu me-2"></i> Dispositivos
        </a>
      </li>
    </ul>
  </div>
</nav>
