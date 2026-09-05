{{--
    ============================================================
    Layout maestro del dashboard.
    Decide qué sidebar mostrar según el rol del usuario y arma
    la página completa: header + sidebar + contenido + footer.
    ============================================================
--}}

@include('layouts.header')

@php
    $nombreRol = strtolower(optional(auth()->user()->rol)->nombre ?? '');
@endphp

@if ($nombreRol === 'administrador')
    @include('layouts.sidebar')
@else
    @include('layouts.sidebar_vendedor')
@endif

@yield('content')

@include('layouts.footer')