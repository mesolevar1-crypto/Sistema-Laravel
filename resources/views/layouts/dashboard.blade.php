{{--
    ============================================================
    Layout maestro del dashboard.
    Decide qué sidebar mostrar según el rol del usuario y arma
    la página completa: header + sidebar + contenido + footer.

    Al final incluye el ícono flotante de alerta de stock, que
    aparece en TODAS las vistas que usen este layout (admin y vendedor).
    ============================================================
--}}

@include('layouts.header')

@php
    // El "?->" evita un error si por alguna razón no hay usuario autenticado.
    $nombreRol = strtolower(optional(auth()->user()?->rol)->nombre ?? '');
@endphp

@if ($nombreRol === 'administrador')
    @include('layouts.sidebar')
@else
    @include('layouts.sidebar_vendedor')
@endif

@yield('content')

{{-- Alerta de stock: va DESPUÉS del contenido, para que el ícono quede encima de todo --}}
@include('layouts.stock-alerta')

@include('layouts.footer')