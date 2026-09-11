{{--
    ============================================================
    Vista: Inicio del Administrador
    Acceso: Solo Administrador
    Función: Panel principal con tarjetas de resumen conectadas
             a datos reales de la base de datos (bdventas).

    IMPORTANTE: esta vista llama directamente al modelo
    App\Models\Inicio (sin controlador), tal como se pidió.
    Se asume que el guard de acceso (solo Administrador) ya lo
    resuelve el middleware de la ruta, por ejemplo:

        Route::get('/admin', fn () => view('vista_admin.inicio'))
            ->middleware(['auth', 'role:Administrador'])
            ->name('inicio.index');

    Si no tienes ese middleware todavía, puedes dejar el chequeo
    manual que se muestra comentado más abajo.
    ============================================================
--}}

@php
    use App\Models\Inicio;

    /*
    // ── Chequeo manual opcional (si no usas middleware de rol) ──
    if (!auth()->check() || auth()->user()->rol !== 'Administrador') {
        abort(403, 'No tienes permiso para ver esta página.');
    }
    */

    // Nombre del usuario autenticado para el saludo
    $nombreUsuario = e(optional(auth()->user()->persona)->nombre ?? 'Administrador');

    // ── Valores reales de las tarjetas ───────────────────────────
    $ventasDiaRaw       = Inicio::ventasDia();
    $ventasMesRaw       = Inicio::ventasMes();
    $gananciasSemanaRaw = Inicio::gananciasSemana();
    $stockBajo          = Inicio::stockBajo();
    $totalProductos     = Inicio::totalProductos();
    $totalUsuarios      = Inicio::totalUsuarios();

    $cantVentasHoy = Inicio::contarVentasHoy();
    $cantVentasMes = Inicio::contarVentasMes();

    $ventas7Dias = Inicio::ventasUltimos7Dias(); // ['2026-08-17' => 125000.0, ...]
    $masVendidos = Inicio::productosMasVendidos(5);

    // ── Formateador de moneda (pesos, sin decimales) ─────────────
    if (!function_exists('formatoMoneda')) {
        function formatoMoneda($valor)
        {
            return '$' . number_format((float) $valor, 0, ',', '.');
        }
    }

    $ventasDia       = formatoMoneda($ventasDiaRaw);
    $ventasMes       = formatoMoneda($ventasMesRaw);
    $gananciasSemana = formatoMoneda($gananciasSemanaRaw);

    // Datos listos para la gráfica (JS)
    $etiquetasDias = array_map(function ($fecha) {
        // Ej: "Vie 21"
        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $ts   = strtotime($fecha);
        return $dias[(int) date('w', $ts)] . ' ' . date('d', $ts);
    }, array_keys($ventas7Dias));

    $valoresDias = array_values($ventas7Dias);
@endphp

@php
    $titulo = 'Panel de Inicio - Administrador';
    // ... el resto de tu código igual
@endphp

@include('layouts.header')
@include('layouts.sidebar')

<!-- ── Estilos locales del Inicio ── -->
<style>
    /* Tarjeta KPI — mismo estilo que la vista de Reportes */
    .kpi {
        background:#fff; border:1px solid #E5E7EB; border-radius:14px;
        padding:20px; transition:transform .18s,box-shadow .18s;
    }
    .kpi:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,135,95,.12); border-color:#61D0A7; }

    /* Sección de panel grande */
    .panel-grande {
        background: #ffffff;
        border: 1px solid #E5E7EB;
        border-radius: 16px;
        overflow: hidden;
    }
    .panel-header {
        padding: 18px 24px;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .panel-header h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #171717;
    }
    .panel-body {
        padding: 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 180px;
        gap: 10px;
        text-align: center;
    }
    .panel-body .panel-vacio-icono {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: #F3F4F6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #9CA3AF;
    }
    .panel-body p {
        font-size: 0.9rem;
        color: #9CA3AF;
        font-style: italic;
    }
    .panel-body span {
        font-size: 0.75rem;
        color: #C4C9D4;
    }

    /* Badge de alerta */
    .badge-aviso {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #fffbeb;
        border: 1px solid #FFB51B;
        color: #92400E;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-ok {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #DDF5EC;
        border: 1px solid #61D0A7;
        color: #01614B;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Sección título */
    .seccion-titulo {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #9CA3AF;
        margin-bottom: 12px;
        margin-top: 8px;
        padding-left: 2px;
    }

    /* Lista de más vendidos */
    .lista-vendidos {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .item-vendido {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 4px;
        border-bottom: 1px solid #F3F4F6;
    }
    .item-vendido:last-child {
        border-bottom: none;
    }
    .item-vendido .rank {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 6px;
        background: #F3F4F6;
        color: #5F6673;
        font-size: 0.7rem;
        font-weight: 800;
        margin-right: 10px;
    }
    .item-vendido .nombre {
        font-size: 0.85rem;
        font-weight: 600;
        color: #171717;
        text-align: left;
        flex: 1;
    }
    .item-vendido .cantidad {
        font-size: 0.8rem;
        font-weight: 700;
        color: #00875F;
        background: #DDF5EC;
        border-radius: 20px;
        padding: 2px 10px;
    }
</style>

<!-- ════════════════════════════════════════════
     CONTENIDO PRINCIPAL — INICIO
════════════════════════════════════════════ -->
<div class="max-w-7xl mx-auto font-sans-ventanet">

    <!-- ── Saludo ── -->
    <div class="mb-8">
        <h2 class="text-4xl font-bold font-serif-ventanet text-[#1C2E1A] leading-tight">
            Bienvenido, {{ $nombreUsuario }} 👋
        </h2>
        <p class="text-[#5F6673] mt-1 text-sm">
            Aquí tienes el resumen general del sistema, actualizado en tiempo real.
        </p>
    </div>

    <!-- ════════════════
         RESUMEN — 6 tarjetas KPI (3 arriba, 3 abajo)
    ════════════════ -->
    <p class="seccion-titulo">Resumen general</p>
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">

        <!-- Ventas del día -->
        <div class="kpi">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#DDF5EC;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-cash-register" style="color:#00875F;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Ventas del día</span>
            </div>
            <p style="font-size:1.5rem;font-weight:800;color:#00875F;line-height:1;">{{ $ventasDia }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">{{ $cantVentasHoy }} venta(s) hoy</p>
        </div>

        <!-- Ventas del mes -->
        <div class="kpi">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#DDF5EC;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-calendar-alt" style="color:#00875F;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Ventas del mes</span>
            </div>
            <p style="font-size:1.5rem;font-weight:800;color:#00875F;line-height:1;">{{ $ventasMes }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">{{ $cantVentasMes }} venta(s) este mes</p>
        </div>

        <!-- Ganancias de la semana -->
        <div class="kpi">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#fffbeb;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-chart-line" style="color:#FFB51B;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Ganancias semana</span>
            </div>
            <p style="font-size:1.5rem;font-weight:800;color:#FFB51B;line-height:1;">{{ $gananciasSemana }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">ganancia neta estimada</p>
        </div>

        <!-- Stock bajo -->
        <div class="kpi" style="border-color:{{ $stockBajo > 0 ? '#FFB51B' : '#E5E7EB' }};">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#fffbeb;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-exclamation-triangle" style="color:#FFB51B;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Stock bajo</span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:{{ $stockBajo > 0 ? '#FFB51B' : '#171717' }};line-height:1;">{{ $stockBajo }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">producto(s) con poco stock</p>
        </div>

        <!-- Total de productos -->
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

        <!-- Total de usuarios registrados -->
        <div class="kpi">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:38px;height:38px;background:#EDE9FE;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-users" style="color:#6D28D9;font-size:.9rem;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Usuarios registrados</span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:#171717;line-height:1;">{{ $totalUsuarios }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:4px;">usuario(s) activos en el sistema</p>
        </div>

    </div>

    <!-- ════════════════
         Gráfica de ventas + Productos más vendidos
    ════════════════ -->
    <p class="seccion-titulo">Análisis de ventas</p>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

        <!-- Ventas últimos 7 días (ocupa 2/3) -->
        <div class="panel-grande lg:col-span-2">
            <div class="panel-header">
                <h3><i class="fas fa-chart-bar mr-2" style="color:#00875F;"></i>Ventas — últimos 7 días</h3>
                <span class="badge-ok">
                    <i class="fas fa-check-circle text-[10px]"></i> En vivo
                </span>
            </div>
            <div class="panel-body" style="min-height:260px; align-items:stretch;">
                <canvas id="graficaVentas7Dias" height="90"></canvas>
            </div>
        </div>

        <!-- Productos más vendidos (ocupa 1/3) -->
        <div class="panel-grande">
            <div class="panel-header">
                <h3><i class="fas fa-fire mr-2" style="color:#E53935;"></i>Más vendidos</h3>
                <span class="badge-ok">
                    <i class="fas fa-check-circle text-[10px]"></i> En vivo
                </span>
            </div>
            <div class="panel-body">
                @if (empty($masVendidos))
                    <div class="panel-vacio-icono">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <p>Sin ventas registradas aún</p>
                    <span>Aparecerán aquí en cuanto se registren ventas</span>
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

@include('layouts.footer')

<!-- ── Chart.js (solo se usa aquí) ── -->
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