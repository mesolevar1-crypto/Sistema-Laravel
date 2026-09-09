@extends('layouts.dashboard')

@section('content')

<div class="max-w-7xl mx-auto">

    <div class="mb-6">
        <h2 class="text-3xl font-bold font-serif-ventanet" style="color:#01614B">
            {{ $titulo ?? 'Módulo' }}
        </h2>
        <p class="text-sm mt-1" style="color:#5F6673">
            {{ $subtitulo ?? 'Esta sección está en construcción.' }}
        </p>
    </div>

    <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#E5E7EB">
        <div style="padding:64px 30px;text-align:center">

            <div style="
                width:70px;height:70px;margin:0 auto 20px;border-radius:50%;
                background:#DDF5EC;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-tools" style="color:#00875F;font-size:28px"></i>
            </div>

            <h3 style="margin:0 0 8px;font-size:20px;font-weight:700;color:#171717">
                Próximamente
            </h3>

            <p style="margin:0;color:#5F6673">
                Este módulo todavía no ha sido implementado.
            </p>

        </div>
    </div>

</div>

@endsection