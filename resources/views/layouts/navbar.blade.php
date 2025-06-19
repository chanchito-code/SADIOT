<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
  <div class="container-fluid px-4 d-flex align-items-center">
    <button id="sidebarToggle" class="btn btn-outline-success me-3" type="button">
      <i class="bi bi-list"></i>
    </button>
    <a class="navbar-brand fw-bold text-success" href="{{ route('dashboard') }}">SADIoT</a>

    <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item me-3">
          <a href="{{ route('devices.create') }}" class="btn btn-success btn-sm">+ Registrar ESP32</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle fs-4 me-1 text-success"></i> {{ auth()->user()->username ?? 'Usuario' }}
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="{{ route('perfil.edit') }}">Editar Perfil</a></li>
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="dropdown-item text-danger" type="submit">Cerrar Sesión</button>
              </form>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<script>
  document.getElementById('sidebarToggle').addEventListener('click', function () {
    const sidebar = document.getElementById('sidebarMenu');
    const mainContent = document.getElementById('mainContent');

    sidebar.classList.toggle('hidden');
    mainContent.classList.toggle('expanded');
  });
</script>


