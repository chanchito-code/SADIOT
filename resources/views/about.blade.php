@extends('layouts.app')

@section('title', 'Inicio')

    {{-- Navbar --}}
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm w-100">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav" aria-controls="navbarNav"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Registro</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Acerca de Nosotros</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>



@section('content')
    {{-- Estilos externos --}}
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">


    {{-- Contenido principal --}}
    <div class="container mt-2 mb-2 main-hero">
        <div class="logo-text-container">
            <div class="text-container">
                <h1 class="mb-4">Bienvenido a SADIoT</h1>
                <p>
                    En SADIoT nos dedicamos a brindar soluciones innovadoras mediante la integración de sensores inteligentes que te permiten monitorear y adquirir información vital para distintos entornos y aplicaciones.
                    Nuestra plataforma está diseñada para facilitar la recolección y visualización de datos de manera eficiente y sencilla, combinando tecnología IoT con una interfaz amigable para el usuario.
                    Ya sea para proyectos educativos, industriales o personales, SADIoT ofrece una experiencia completa para que puedas sacar el máximo provecho de los sensores conectados a tu entorno.
                </p>
            </div>
            <div class="logo-container">
                <img src="{{ asset('storage/images/f_b.png') }}" alt="Logo UQROO" class="uqroo-logo">
            </div>
        </div>
    </div>

    {{-- Sección "Acerca de Nosotros" --}}
    <section id="about" class="container mt-5 pt-5 pb-5 bg-light border rounded shadow-sm">
        <h2 class="mb-5 text-center">Acerca de Nosotros</h2>
        <p class="mb-4 text-center text-muted" style="max-width: 700px; margin-left:auto; margin-right:auto;">
            Somos un equipo apasionado de desarrolladores y especialistas en IoT que trabajamos para crear soluciones tecnológicas que conectan el mundo físico con el digital. Nuestra misión es facilitar el acceso a la información a través de sensores inteligentes y crear herramientas que impulsen la innovación y eficiencia.
        </p>

        <div class="row justify-content-center">
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm">
                    <div class="img-container">
                        <img src="{{ asset('storage/images/user1.png') }}" alt="Desarrollador 1" class="card-img-top centered-img">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">MTRO. Jesús Orifiel Álvarez Ruiz</h5>
                        <h4 class="card-text text-muted">Desarrollador</p>
                    </div>
                </div>
            </div>
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm">
                    <div class="img-container">
                        <img src="{{ asset('storage/images/user2.png') }}" alt="Desarrollador 2" class="card-img-top centered-img">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">Jose Luis Chavez Zetina</h5>
                        <h4 class="card-text text-muted">Desarrollador</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal de Login -->
    <div class="modal fade custom-modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModalLabel">🔐 Iniciar Sesión</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="login" class="form-label">Correo</label>
                            <input type="text" placeholder="Ej. usuario@gmail.com" name="login" id="login" class="form-control" value="{{ old('login') }}" required autofocus>
                            @error('login')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                            @error('password')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Recuérdame</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-uqroo-primary w-100">Entrar</button>
                    </div>
                </form>
                <div class="text-center mb-3">
                    <small>¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></small>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
<footer class="footer-container bg-dark text-light pt-4 pb-2 mt-auto border-top">
  <div class="container-fluid px-5">
    <div class="footer-section footer-border d-flex flex-wrap justify-content-between">
      <div class="footer-block">
        <h5>Universidad Autónoma de Quintana Roo</h5>
        <p>Plataforma desarrollada como parte del proyecto SADIoT.</p>
      </div>
      <div class="footer-block">
        <h5>Contacto</h5>
        <p>Chetumal, Quintana Roo</p>
        <p>Tel. (983) 835-0300</p>
        <p>correo@uqroo.edu.mx</p>
      </div>
      <div class="footer-block">
        <h5>Enlaces útiles</h5>
        <a href="#" class="footer-link">Inicio</a><br />
        <a href="#about" class="footer-link">Acerca de</a><br />
        <a href="{{ route('register') }}" class="footer-link">Registro</a>
      </div>
      <div class="footer-block">
        <h5>Desarrolladores</h5>
        <p>MTRO. Jesús Orifiel Álvarez Ruiz</p>
        <p>Jose Luis Chavez Zetina</p>
      </div>
      <div class="footer-block">
        <h5>Síguenos</h5>
        <div class="footer-social">
          <a href="https://www.facebook.com/UQROOChetumal"><i class="bi bi-facebook"></i></a>
          <a href="https://www.uqroo.mx/"><i class="bi bi-browser-chrome"></i></a>
          <a
            href="https://www.instagram.com/uqroo_mx?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="
            ><i class="bi bi-instagram"></i
          ></a>
        </div>
      </div>
    </div>
    <div class="footer-bottom text-center py-2">
      <small>© {{ date('Y') }} SADIoT - Todos los derechos reservados</small>
    </div>
  </div>
</footer>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Mostrar modal si hay errores
        @if ($errors->has('login') || $errors->has('password'))
            var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
        @endif

        // Scroll suave para anclas internas
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetID = this.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetID);
                if(targetSection){
                    targetSection.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    });
</script>
@endpush
