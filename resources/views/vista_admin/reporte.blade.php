@extends('layouts.dashboard')
@php
    $titulo = 'Panel de reportes- Administrador';
@endphp
@section('content')

<style>
    .kpi {
        background:#fff; border:1px solid #E5E7EB; border-radius:14px;
        padding:20px; transition:transform .18s,box-shadow .18s;
        text-decoration:none; display:block; color:inherit;
    }
    .kpi:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,135,95,.12); border-color:#61D0A7; }
    .acceso {
        background:#fff; border:1px solid #E5E7EB; border-radius:14px;
        padding:24px; display:flex; align-items:center; gap:16px;
        text-decoration:none; color:inherit;
        transition:transform .18s,box-shadow .18s,border-color .18s;
    }
    .acceso:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,135,95,.12); border-color:#61D0A7; }
</style>

<div class="max-w-7xl mx-auto font-sans-ventanet">

    <!-- Título -->
    <div class="mb-7">
        <h2 class="text-3xl font-bold font-serif-ventanet" style="color:#01614B;">Reportes</h2>
        <p class="text-sm mt-1" style="color:#5F6673;">Consulta y analiza la información de tu negocio</p>
    </div>

    <!-- ── KPIs ── -->
    <p style="font-size:.7rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;">Resumen actual</p>
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">

        <!-- Ventas del día -->
        <div class="kpi">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#DDF5EC;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-sun" style="color:#00875F;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Ventas de hoy</span>
            </div>
            <p style="font-size:1.5rem;font-weight:800;color:#00875F;line-height:1;">
                ${{ number_format($ventasHoy['valor'] ?? 0, 0, ',', '.') }}
            </p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">{{ (int) ($ventasHoy['cantidad'] ?? 0) }} venta(s) hoy</p>
        </div>

        <!-- Ventas del mes -->
        <div class="kpi">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#DDF5EC;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-calendar-alt" style="color:#00875F;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Ventas del mes</span>
            </div>
            <p style="font-size:1.5rem;font-weight:800;color:#00875F;line-height:1;">
                ${{ number_format($ventasMes['valor'] ?? 0, 0, ',', '.') }}
            </p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">{{ (int) ($ventasMes['cantidad'] ?? 0) }} venta(s) este mes</p>
        </div>

        <!-- Compras del mes -->
        <div class="kpi">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#fffbeb;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-shopping-bag" style="color:#FFB51B;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Compras del mes</span>
            </div>
            <p style="font-size:1.5rem;font-weight:800;color:#FFB51B;line-height:1;">
                ${{ number_format($comprasMes['valor'] ?? 0, 0, ',', '.') }}
            </p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">{{ (int) ($comprasMes['cantidad'] ?? 0) }} compra(s) este mes</p>
        </div>

        <!-- Ganancias del mes -->
        @php $gan = (float) ($gananciasMes['valor'] ?? 0); @endphp
        <div class="kpi" style="border-color:{{ $gan >= 0 ? '#E5E7EB' : '#fde8e8' }};">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:{{ $gan >= 0 ? '#DDF5EC' : '#fde8e8' }};border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-chart-line" style="color:{{ $gan >= 0 ? '#00875F' : '#E53935' }};font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Ganancias del mes</span>
            </div>
            <p style="font-size:1.5rem;font-weight:800;color:{{ $gan >= 0 ? '#00875F' : '#E53935' }};line-height:1;">
                ${{ number_format(abs($gan), 0, ',', '.') }}
            </p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">{{ $gan >= 0 ? 'ganancia neta estimada' : 'pérdida estimada' }}</p>
        </div>

        <!-- Stock bajo -->
        <div class="kpi" style="border-color:{{ $stockBajo > 0 ? '#FFB51B' : '#E5E7EB' }};">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#fffbeb;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-exclamation-triangle" style="color:#FFB51B;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Stock bajo</span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:{{ $stockBajo > 0 ? '#FFB51B' : '#171717' }};line-height:1;">
                {{ $stockBajo }}
            </p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">producto(s) con poco stock</p>
        </div>

        <!-- Productos agotados -->
        <div class="kpi" style="border-color:{{ $agotados > 0 ? '#fde8e8' : '#E5E7EB' }};">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:{{ $agotados > 0 ? '#fde8e8' : '#F8F8F8' }};border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-times-circle" style="color:{{ $agotados > 0 ? '#E53935' : '#9CA3AF' }};font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Productos agotados</span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:{{ $agotados > 0 ? '#E53935' : '#171717' }};line-height:1;">
                {{ $agotados }}
            </p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">producto(s) sin stock</p>
        </div>

    </div>

    <!-- ── Accesos a reportes detallados ── -->
    <p style="font-size:.7rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;">Reportes detallados</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        <a href="{{ route('admin.reportes.ventas') }}" class="acceso">
            <div style="width:50px;height:50px;background:#DDF5EC;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-receipt" style="color:#00875F;font-size:1.2rem;"></i>
            </div>
            <div>
                <p style="font-weight:700;color:#171717;font-size:.95rem;">Reporte de Ventas</p>
                <p style="font-size:.78rem;color:#5F6673;margin-top:2px;">Consulta ventas por fecha y usuario. Incluye ganancias y márgenes.</p>
                <p style="font-size:.72rem;color:#00875F;font-weight:700;margin-top:6px;">Ver reporte →</p>
            </div>
        </a>

        <a href="{{ route('admin.reportes.compras') }}" class="acceso">
            <div style="width:50px;height:50px;background:#fffbeb;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-shopping-bag" style="color:#FFB51B;font-size:1.2rem;"></i>
            </div>
            <div>
                <p style="font-weight:700;color:#171717;font-size:.95rem;">Reporte de Compras</p>
                <p style="font-size:.78rem;color:#5F6673;margin-top:2px;">Historial de compras a proveedores por rango de fecha.</p>
                <p style="font-size:.72rem;color:#FFB51B;font-weight:700;margin-top:6px;">Ver reporte →</p>
            </div>
        </a>

        <a href="{{ route('admin.reportes.inventario') }}" class="acceso">
            <div style="width:50px;height:50px;background:#EBF5FF;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-warehouse" style="color:#1F3552;font-size:1.2rem;"></i>
            </div>
            <div>
                <p style="font-weight:700;color:#171717;font-size:.95rem;">Reporte de Inventario</p>
                <p style="font-size:.78rem;color:#5F6673;margin-top:2px;">Estado actual del stock con filtros por categoría y estado.</p>
                <p style="font-size:.72rem;color:#1F3552;font-weight:700;margin-top:6px;">Ver reporte →</p>
            </div>
        </a>

    </div>

</div>

@endsection