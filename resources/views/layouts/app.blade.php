<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'ESP32 Dashboard')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet" />
</head>
<body class="bg-light d-flex flex-column" style="min-height:100vh;">

  @auth
    @include('layouts.navbar')

    <div class="d-flex flex-grow-1" style="overflow-y: auto;">
      @include('layouts.sidebar')

      <div id="mainContent" class="flex-grow-1">
        <main class="p-4">
          @yield('content')
        </main>
      </div>
    </div>
  @endauth

  @guest
    <div class="container py-5 flex-grow-1">
      @yield('content')
    </div>
  @endguest

  {{-- Aquí solo mostramos el footer si la vista define esta sección --}}
  @hasSection('footer')
    @yield('footer')
  @endif

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')

</body>
</html>
