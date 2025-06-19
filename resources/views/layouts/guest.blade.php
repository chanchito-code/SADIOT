<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'SADIoT')</title>

    {{-- Aquí cargas Bootstrap o Tailwind, y tus CSS personalizados --}}
    <link href="{{ asset('css/login.css') }}" rel="stylesheet" />
     <link href="{{ asset('css/home.css') }}" rel="stylesheet" />
    
    
    {{-- Si en tu home cargas CSS específicos dentro de la vista, pon esto para que se pueda agregar --}}
    @stack('styles')
</head>
<body>
    {{-- Contenido de cada página --}}
    @yield('content')

    {{-- Scripts comunes --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Scripts específicos por página --}}
    @stack('scripts')
</body>
</html>
