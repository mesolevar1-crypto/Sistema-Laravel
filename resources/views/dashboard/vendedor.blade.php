@extends('layouts.dashboard')

@php
    $titulo = 'Panel de inicio - Vendedor';

    use App\Models\Inicio;

    // Datos del vendedor autenticado
    $nombreUsuario = e(optional(auth()->user()->persona)->nombre ?? 'Vendedor');
    $idUsuario     = auth()->user()->id_usuario;

    // ── Valores reales de las tarjetas (todos filtrados por $idUsuario) ──
    $ventasDiaRaw = Inicio::ventasDia($idUsuario);
    $ventasMesRaw = Inicio::ventasMes($idUsuario);
    $ingresosRaw  = Inicio::totalIngresos($idUsuario);
    $ticketProm   = Inicio::ticketPromedio($idUsuario);

    // Informativos del negocio en general (no son "de él")
    $totalClientes  = Inicio::totalClientes();
    $totalProductos = Inicio::totalProductos();

    $cantVentasHoy = Inicio::contarVentasHoy($idUsuario);
    $cantVentasMes = Inicio::contarVentasMes($idUsuario);

    $ventas7Dias = Inicio::ventasUltimos7Dias($idUsuario);
    $masVendidos = Inicio::productosMasVendidos(5, $idUsuario);

    if (!function_exists('formatoMoneda')) {
        function formatoMoneda($valor)
        {
            return '$' . number_format((float) $valor, 0, ',', '.');
        }
    }

    $ventasDia = formatoMoneda($ventasDiaRaw);
    $ventasMes = formatoMoneda($ventasMesRaw);
    $ingresos  = formatoMoneda($ingresosRaw);
    $ticket    = formatoMoneda($ticketProm);

    $etiquetasDias = array_map(function ($fecha) {
        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $ts   = strtotime($fecha);
        return $dias[(int) date('w', $ts)] . ' ' . date('d', $ts);
    }, array_keys($ventas7Dias));

    $valoresDias = array_values($ventas7Dias);
@endphp

@section('content')

<style>
    .kpi {
        background:#fff; border:1px solid #E5E7EB; border-radius:14px;
        padding:20px; transition:transform .18s,box-shadow .18s;
    }
    .kpi:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,135,95,.12); border-color:#61D0A7; }

    .panel-grande { background:#ffffff; border:1px solid #E5E7EB; border-radius:16px; overflow:hidden; }
    .panel-header { padding:18px 24px; border-bottom:1px solid #E5E7EB; display:flex; align-items:center; justify-content:space-between; }
    .panel-header h3 { font-size:1rem; font-weight:700; color:#171717; }
    .panel-body { padding:24px; display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:180px; gap:10px; text-align:center; }
    .panel-body .panel-vacio-icono { width:52px; height:52px; border-radius:50%; background:#F3F4F6; display:flex; align-items:center; justify-content:center; font-size:1.4rem; color:#9CA3AF; }
    .panel-body p { font-size:0.9rem; color:#9CA3AF; font-style:italic; }
    .panel-body span { font-size:0.75rem; color:#C4C9D4; }

    .badge-ok { display:inline-flex; align-items:center; gap:6px; background:#DDF5EC; border:1px solid #61D0A7; color:#01614B; border-radius:20px; padding:4px 12px; font-size:0.75rem; font-weight:600; }

    .seccion-titulo { font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:#9CA3AF; margin-bottom:12px; margin-top:8px; padding-left:2px; }

    .lista-vendidos { width:100%; display:flex; flex-direction:column; gap:4px; }
    .item-vendido { display:flex; align-items:center; justify-content:space-between; padding:10px 4px; border-bottom:1px solid #F3F4F6; }
    .item-vendido:last-child { border-bottom:none; }
    .item-vendido .rank { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:6px; background:#F3F4F6; color:#5F6673; font-size:0.7rem; font-weight:800; margin-right:10px; }
    .item-vendido .nombre { font-size:0.85rem; font-weight:600; color:#171717; text-align:left; flex:1; }
    .item-vendido .cantidad { font-size:0.8rem; font-weight:700; color:#00875F; background:#DDF5EC; border-radius:20px; padding:2px 10px; }
</style>

<div class="max-w-7xl mx-auto font-sans-ventanet">

    <div class="mb-8">
        <h2 class="text-4xl font-bold font-serif-ventanet text-[#1C2E1A] leading-tight">
            Bienvenido, {{ $nombreUsuario }} 👋
        </h2>
        <p class="text-[#5F6673] mt-1 text-sm">
            Aquí tienes el resumen de tu actividad, actualizado en tiempo real.
        </p>
    </div>

    <p class="seccion-titulo">Mi resumen</p>
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">

        <div class="kpi">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#DDF5EC;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-cash-register" style="color:#00875F;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Mis ventas hoy</span>
            </div>
            <p style="font-size:1.5rem;font-weight:800;color:#00875F;line-height:1;">{{ $ventasDia }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">{{ $cantVentasHoy }} venta(s) hoy</p>
        </div>

        <div class="kpi">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#DDF5EC;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-calendar-alt" style="color:#00875F;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Mis ventas del mes</span>
            </div>
            <p style="font-size:1.5rem;font-weight:800;color:#00875F;line-height:1;">{{ $ventasMes }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">{{ $cantVentasMes }} venta(s) este mes</p>
        </div>

        <div class="kpi">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#fffbeb;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-dollar-sign" style="color:#FFB51B;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Mis ingresos</span>
            </div>
            <p style="font-size:1.5rem;font-weight:800;color:#FFB51B;line-height:1;">{{ $ingresos }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">acumulado total</p>
        </div>

        <div class="kpi">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#EBF5FF;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-receipt" style="color:#1F3552;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Ticket promedio</span>
            </div>
            <p style="font-size:1.5rem;font-weight:800;color:#171717;line-height:1;">{{ $ticket }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">valor promedio por venta</p>
        </div>

        <div class="kpi">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#EBF5FF;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-users" style="color:#1F3552;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Clientes registrados</span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:#171717;line-height:1;">{{ $totalClientes }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">cliente(s) en el sistema</p>
        </div>

        <div class="kpi">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#EBF5FF;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-box-open" style="color:#1F3552;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Total productos</span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:#171717;line-height:1;">{{ $totalProductos }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">producto(s) activos</p>
        </div>

    </div>

    <p class="seccion-titulo">Mi análisis de ventas</p>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

        <div class="panel-grande lg:col-span-2">
            <div class="panel-header">
                <h3><i class="fas fa-chart-bar mr-2" style="color:#00875F;"></i>Mis ventas — últimos 7 días</h3>
                <span class="badge-ok"><i class="fas fa-check-circle text-[10px]"></i> En vivo</span>
            </div>
            <div class="panel-body" style="min-height:260px; align-items:stretch;">
                <canvas id="graficaVentas7Dias" height="90"></canvas>
            </div>
        </div>

        <div class="panel-grande">
            <div class="panel-header">
                <h3><i class="fas fa-fire mr-2" style="color:#E53935;"></i>Mis más vendidos</h3>
                <span class="badge-ok"><i class="fas fa-check-circle text-[10px]"></i> En vivo</span>
            </div>
            <div class="panel-body">
                @if (empty($masVendidos))
                    <div class="panel-vacio-icono"><i class="fas fa-box-open"></i></div>
                    <p>Aún no tienes ventas registradas</p>
                    <span>Aparecerán aquí en cuanto registres ventas</span>
                @else
                    <div class="lista-vendidos">
                        @foreach ($masVendidos as $i => $p)
                            <div class="item-vendido">
                                <span class="rank">{{ $i + 1 }}</span>
                                <span class="nombre">{{ $p['nombre'] }}</span>
                                <span class="cantidad">{{ (int) $p['cantidad_vendida'] }} und</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
    const ctxVentas = document.getElementById('graficaVentas7Dias');

    new Chart(ctxVentas, {
        type: 'bar',
        data: {
            labels: {!! json_encode($etiquetasDias, JSON_UNESCAPED_UNICODE) !!},
            datasets: [{
                label: 'Ventas',
                data: {!! json_encode($valoresDias) !!},
                backgroundColor: '#00875F',
                borderRadius: 6,
                maxBarThickness: 42
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return '$' + Number(context.raw).toLocaleString('es-CO');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return '$' + Number(value).toLocaleString('es-CO');
                        }
                    }
                }
            }
        }
    });
</script>

@endsection