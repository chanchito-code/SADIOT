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

      <!-- Submenú para Ejemplos -->
      <li class="nav-item mb-3">
        <a class="nav-link fw-semibold d-flex justify-content-between align-items-center
            {{ request()->is('ejemplos*') ? 'text-success' : 'text-dark' }}"
            data-bs-toggle="collapse" href="#submenuEjemplos" role="button" aria-expanded="{{ request()->is('ejemplos*') ? 'true' : 'false' }}" aria-controls="submenuEjemplos">
          <span><i class="bi bi-file-earmark-code me-2"></i> Ejemplos</span>
          <i class="bi bi-caret-down-fill"></i>
        </a>
        <div class="collapse {{ request()->is('ejemplos*') ? 'show' : '' }}" id="submenuEjemplos">
          <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
            <li>
              <a href="{{ route('ejemplos.index') }}"
                 class="nav-link ps-4 {{ request()->routeIs('ejemplos.index') ? 'text-success' : 'text-dark' }}">
                Índice general
              </a>
            </li>
            <!-- Si en el futuro agregas otras rutas para ejemplos, puedes descomentar y modificar -->
            {{-- 
            <li>
              <a href="{{ route('ejemplos.guias') }}"
                 class="nav-link ps-4 {{ request()->routeIs('ejemplos.guias') ? 'text-success' : 'text-dark' }}">
                Guía IoT
              </a>
            </li>
            --}}
          </ul>
        </div>
      </li>
    </ul>
  </div>
</nav>
