{{--
    ============================================================
    Layout: Encabezado HTML global (header.blade.php)
    Incluido en: todas las vistas autenticadas del sistema.
    Función: verifica autenticación y genera el <head> con
             todos los recursos necesarios.
    ============================================================
    NOTA: session_start() y el header("Location: ...") manual ya
    no hacen falta: el middleware 'auth' de Laravel se encarga de
    redirigir al login si no hay sesión (protege la ruta que use
    este layout). $usuario ahora es auth()->user().
--}}

@php
    $usuario = auth()->user();
    $titulo  = $titulo ?? 'Dashboard';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }} | VentaNet</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Fondo general del sistema */
        body {
            font-family: 'Outfit', 'DM Sans', sans-serif;
            background: #F8F8F8;
            color: #171717;
            margin: 0;
            padding: 0;
        }

        /* Tipografías reutilizables */
        .font-serif-ventanet { font-family: 'DM Serif Display', serif; }
        .font-sans-ventanet  { font-family: 'DM Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen">
<!-- Contenedor flex principal: sidebar izquierdo + contenido derecho -->
<div class="flex min-h-screen">