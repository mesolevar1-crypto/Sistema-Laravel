@extends('layouts.dashboard')

@php
    $titulo = 'Mi Reporte de Ventas';
@endphp

@section('content')

<script src="{{ asset('js/chart.umd.js') }}"
        onerror="this.onerror=null;this.src='https:cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js';"></script>

<style>
    /* ============================================================
       TOKENS DE DISEÑO — mismo verde VentaNet, jerarquía más clara
    ============================================================ */
    :root {
        --vn-verde:        #00875F;
        --vn-verde-oscuro: #01614B;
        --vn-verde-claro:  #DDF5EC;
        --vn-verde-medio:  #61D0A7;
        --vn-texto:        #171717;
        --vn-texto-suave:  #5F6673;
        --vn-texto-tenue:  #9CA3AF;
        --vn-borde:        #E5E7EB;
        --vn-fondo-suave:  #F8FAF9;
        --vn-amarillo:     #FFB51B;
        --vn-rojo:         #E53935;
    }

    .rv-eyebrow {
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--vn-verde);
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 4px;
    }
    .rv-eyebrow::before {
        content: '';
        width: 16px;
        height: 2px;
        background: var(--vn-verde-medio);
        border-radius: 2px;
        display: inline-block;
    }

    .btn-primario {
        background: var(--vn-verde); color:#fff;border-radius:10px;border:none;font-weight:600;
        font-family:'Outfit',sans-serif;cursor:pointer;
        transition:background .18s,transform .15s;
        box-shadow:0 4px 14px rgba(0,135,95,.24);
        display:inline-flex;align-items:center;gap:6px;padding:10px 20px;
        text-decoration:none; font-size:.85rem;
    }
    .btn-primario:hover { background: var(--vn-verde-oscuro);transform:translateY(-2px); }
    .btn-primario:disabled { background:#B9BFC6;cursor:not-allowed;box-shadow:none;transform:none; }

    .btn-secundario {
        padding:10px 16px;border-radius:10px;border:1.5px solid var(--vn-borde);background:#fff;
        color:var(--vn-texto-suave);font-size:.85rem;font-weight:600;text-decoration:none;
        display:inline-flex;align-items:center;gap:5px;cursor:pointer;
        font-family:'Outfit',sans-serif;transition:background .15s,border-color .15s,color .15s;
    }
    .btn-secundario:hover { background: var(--vn-fondo-suave);border-color: var(--vn-verde);color: var(--vn-verde); }

    .campo-input {
        background:#fff;border:1.5px solid var(--vn-borde);border-radius:10px;
        color:var(--vn-texto);font-family:'Outfit',sans-serif;font-size:.9rem;
        outline:none;padding:10px 12px;width:100%;
        transition:border-color .2s,box-shadow .2s;
    }
    .campo-input:focus { border-color: var(--vn-verde-medio);box-shadow:0 0 0 4px rgba(97,208,167,.16); }

    .panel {
        background:#fff;border:1px solid var(--vn-borde);border-radius:16px;overflow:hidden;
        box-shadow: 0 1px 2px rgba(16,24,32,.03);
    }
    .panel-head {
        background: var(--vn-fondo-suave);
        border-bottom:1px solid var(--vn-borde);
        padding:15px 22px;
    }

    /* ── KPIs ── */
    .kpi {
        background:#fff;border:1px solid var(--vn-borde);border-radius:16px;padding:20px;
        transition: transform .18s, box-shadow .18s;
        position: relative;
        overflow: hidden;
    }
    .kpi:hover { transform: translateY(-3px); box-shadow: 0 10px 26px rgba(0,135,95,.10); }
    .kpi-icono {
        width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;
        margin-bottom:12px;
    }
    .kpi-label {
        font-size:.68rem;font-weight:800;color:var(--vn-texto-suave);text-transform:uppercase;letter-spacing:.06em;
    }
    .kpi-valor { font-size:1.9rem;font-weight:800;color:var(--vn-texto);line-height:1;margin-top:8px; }
    .kpi-sub { font-size:.72rem;color:var(--vn-texto-tenue);margin-top:5px; }

    /* ── Toggle de agrupación (Día / Semana / Mes) ── */
    .toggle-grupo {
        display:inline-flex;background: var(--vn-fondo-suave);border:1px solid var(--vn-borde);
        border-radius:11px;padding:4px;gap:2px;
    }
    .toggle-grupo a {
        padding:7px 16px;border-radius:8px;font-size:.8rem;font-weight:700;
        color: var(--vn-texto-suave); text-decoration:none;transition:all .15s;
    }
    .toggle-grupo a:hover { color: var(--vn-verde); }
    .toggle-grupo a.activo {
        background: var(--vn-verde); color:#fff; box-shadow:0 2px 8px rgba(0,135,95,.28);
    }

    /* ===== Exportar a PDF (misma pestaña, sin abrir ventanas nuevas) ===== */
    .encabezado-impresion { display:none; }

    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        body * { visibility: hidden; }
        #reporteImprimir, #reporteImprimir * { visibility: visible; }
        #reporteImprimir {
            position: absolute; left: 0; top: 0; width: 100%; padding: 10px;
        }
        .no-imprimir { display: none !important; }
        .kpi, .panel { break-inside: avoid; box-shadow: none !important; }
        thead { break-after: avoid; }
        .encabezado-impresion { display: block !important; margin-bottom: 16px; }

        .overflow-x-auto { overflow: visible !important; }
        table { width: 100% !important; font-size: 10px; }
        th, td { padding: 4px 6px !important; }

        /* El canvas del gráfico necesita medidas fijas al imprimir,
           de lo contrario el navegador lo corta al recalcular el layout */
        .chart-box, [style*="height:320px"] { height: 300px !important; }
        canvas { max-width: 100% !important; }

        @page { margin: 12mm 10mm; }
    }
</style>

<div class="max-w-7xl mx-auto font-sans-ventanet" id="reporteImprimir">

    <!-- Encabezado solo visible al imprimir/exportar -->
    <div class="encabezado-impresion">
        <h2 style="font-size:1.4rem;font-weight:800;color:#01614B;">VentaNet — Mi Reporte de Ventas</h2>
        <p style="font-size:.8rem;color:#5F6673;">
            Período: {{ date('d/m/Y', strtotime($desde)) }} al {{ date('d/m/Y', strtotime($hasta)) }}
            · Vendedor: {{ $nombreVendedor }}
            · Generado: {{ date('d/m/Y H:i') }}
        </p>
    </div>

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-7 gap-4 no-imprimir">
        <div style="display:flex;align-items:center;gap:14px;">
            <a href="{{ route('vendedor.inicio') }}"
               style="padding:8px 14px;border-radius:9px;border:1px solid #E5E7EB;background:#fff;color:#00875F;font-size:.82rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
               <i class="fas fa-arrow-left" style="font-size:.7rem;"></i> Volver
            </a>
            <div>
                <h2 class="text-3xl font-bold font-serif-ventanet" style="color:#01614B;">Mi Reporte de Ventas</h2>
                <p class="text-sm mt-1" style="color:var(--vn-texto-suave);">Consulta tus ventas realizadas y tus ganancias por periodo</p>
            </div>
        </div>
        <button type="button" class="btn-primario" onclick="exportarPDF()" {{ empty($ventas) ? 'disabled title="No hay datos para exportar"' : '' }}>
            <i class="fas fa-file-pdf"></i> Exportar a PDF
        </button>
    </div>

    @if (empty($ventas))
    <!-- Sin resultados -->
    <div class="panel" style="padding:56px;text-align:center;">
        <div style="width:60px;height:60px;background:var(--vn-fondo-suave);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;border:1px solid var(--vn-borde);">
            <i class="fas fa-receipt" style="color:var(--vn-texto-tenue);font-size:1.4rem;"></i>
        </div>
        <p style="color:var(--vn-texto-suave);font-weight:600;">No tienes ventas en el período seleccionado.</p>
        <p style="color:var(--vn-texto-tenue);font-size:.82rem;margin-top:4px;">Prueba con un rango de fechas diferente o limpia los filtros.</p>
    </div>

    @else

    <!-- KPIs -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="kpi">
            <div class="kpi-icono" style="background:var(--vn-verde-claro);">
                <i class="fas fa-receipt" style="color:var(--vn-verde);font-size:.95rem;"></i>
            </div>
            <span class="kpi-label">Cantidad</span>
            <p class="kpi-valor">{{ $totalRegistros }}</p>
            <p class="kpi-sub">ventas registradas</p>
        </div>

        <div class="kpi">
            <div class="kpi-icono" style="background:var(--vn-verde-claro);">
                <i class="fas fa-dollar-sign" style="color:var(--vn-verde);font-size:.95rem;"></i>
            </div>
            <span class="kpi-label">Total vendido</span>
            <p class="kpi-valor" style="font-size:1.5rem;">${{ number_format($totalVendido, 0, ',', '.') }}</p>
            <p class="kpi-sub">en el período</p>
        </div>

        @php $colorGan = $totalGanancia >= 0 ? 'var(--vn-verde)' : 'var(--vn-rojo)'; @endphp
        <div class="kpi" style="border-color:{{ $totalGanancia < 0 ? '#fde8e8' : 'var(--vn-borde)' }};">
            <div class="kpi-icono" style="background:{{ $totalGanancia >= 0 ? 'var(--vn-verde-claro)' : '#fde8e8' }};">
                <i class="fas fa-chart-line" style="color:{{ $colorGan }};font-size:.95rem;"></i>
            </div>
            <span class="kpi-label">Ganancia total</span>
            <p class="kpi-valor" style="font-size:1.5rem;color:{{ $colorGan }};">
                ${{ number_format(abs($totalGanancia), 0, ',', '.') }}
            </p>
            <p class="kpi-sub">{{ $totalGanancia >= 0 ? 'ganancia neta' : 'pérdida' }}</p>
        </div>

        <div class="kpi">
            <div class="kpi-icono" style="background:var(--vn-verde-claro);">
                <i class="fas fa-percent" style="color:var(--vn-verde);font-size:.95rem;"></i>
            </div>
            <span class="kpi-label">Margen general</span>
            <p class="kpi-valor" style="color:{{ $margenGeneral >= 0 ? 'var(--vn-verde)' : 'var(--vn-rojo)' }};">{{ $margenGeneral }}%</p>
            <p class="kpi-sub">ganancia / total vendido</p>
        </div>

    </div>

    @endif

    <!-- Filtros (solo fechas: el vendedor no puede elegir a otro usuario) -->
    <form method="GET" action="{{ route('vendedor.reporte') }}" class="panel mb-6 no-imprimir">
        <div class="panel-head">
            <h3 style="font-size:.85rem;font-weight:700;color:var(--vn-texto);display:flex;align-items:center;gap:8px;">
                <i class="fas fa-filter" style="color:var(--vn-verde);"></i> Filtros
            </h3>
        </div>
        <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr auto auto;gap:12px;align-items:end;">
            <div>
                <label style="display:block;font-size:.78rem;font-weight:700;color:var(--vn-texto-suave);margin-bottom:5px;">Fecha inicial</label>
                <input type="date" name="desde" value="{{ $desde }}" class="campo-input">
            </div>
            <div>
                <label style="display:block;font-size:.78rem;font-weight:700;color:var(--vn-texto-suave);margin-bottom:5px;">Fecha final</label>
                <input type="date" name="hasta" value="{{ $hasta }}" class="campo-input">
            </div>
            <input type="hidden" name="agrupacion" value="{{ $agrupacion }}">
            <button type="submit" class="btn-primario">
                <i class="fas fa-search"></i> Buscar
            </button>
            <a href="{{ route('vendedor.reporte') }}" class="btn-secundario">
               <i class="fas fa-times"></i> Limpiar
            </a>
        </div>
        <div style="padding:0 20px 16px;font-size:.75rem;color:var(--vn-texto-tenue);">
            Mostrando tus ventas del <strong style="color:var(--vn-texto);">{{ date('d/m/Y', strtotime($desde)) }}</strong>
            al <strong style="color:var(--vn-texto);">{{ date('d/m/Y', strtotime($hasta)) }}</strong>
        </div>
    </form>

    @if (!empty($ventas))

    <!-- Gráfico de ganancias por periodo -->
    <div class="panel mb-6">
        <div class="panel-head" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h3 style="font-size:.9rem;font-weight:700;color:var(--vn-texto);display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-chart-column" style="color:var(--vn-verde);"></i>
                    Mis ganancias {{ $etiquetaAgrupacion }}
                </h3>
                <p style="font-size:.75rem;color:var(--vn-texto-tenue);margin-top:2px;">Comparativo de total vendido vs. ganancia neta</p>
            </div>
            <div class="toggle-grupo no-imprimir">
                @php $qs = ['desde' => $desde, 'hasta' => $hasta]; @endphp
                @foreach (['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes'] as $val => $label)
                    <a href="{{ route('vendedor.reporte', array_merge($qs, ['agrupacion' => $val])) }}" class="{{ $agrupacion === $val ? 'activo' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div style="padding:24px;">
            @if (empty($gananciasPeriodo))
                <p style="text-align:center;color:var(--vn-texto-tenue);font-size:.85rem;padding:20px 0;">
                    No hay datos suficientes para graficar este periodo.
                </p>
            @else
                <div style="position:relative;height:320px;">
                    <canvas id="graficoGanancias"></canvas>
                </div>
            @endif
        </div>
    </div>

    @endif

</div>

<script>
    const tituloOriginal = document.title;
    window.__vnCharts = window.__vnCharts || {};

    function redibujarGraficosVN() {
        Object.values(window.__vnCharts).forEach(function (c) {
            if (c) { c.resize(); c.update('none'); }
        });
    }

    function exportarPDF() {
        document.title = @json($nombreArchivoPDF);
        // Se redibuja el gráfico al tamaño de la hoja antes de abrir el diálogo de impresión,
        // si no, el canvas queda con el tamaño de pantalla y se ve cortado/incompleto.
        redibujarGraficosVN();
        setTimeout(function () { window.print(); }, 80);
    }

    window.onafterprint = function () {
        document.title = tituloOriginal;
        redibujarGraficosVN();
    };

    @if (!empty($gananciasPeriodo))
    // ============================================================
    // Gráfico de ganancias por periodo (Chart.js)
    // ============================================================
    (function () {
        function mostrarErrorEnPagina(mensaje) {
            const contenedor = document.getElementById('graficoGanancias')?.parentElement;
            if (contenedor) {
                contenedor.innerHTML =
                    '<div style="height:100%;display:flex;align-items:center;justify-content:center;' +
                    'text-align:center;color:#E53935;font-family:sans-serif;font-size:.85rem;padding:16px;">' +
                    '⚠️ No se pudo dibujar el gráfico: ' + mensaje +
                    '</div>';
            }
        }

        function iniciarGrafico() {
            try {
                if (typeof Chart === 'undefined') {
                    mostrarErrorEnPagina('la librería Chart.js no cargó (revisa que tu servidor tenga acceso a internet / al CDN cdnjs.cloudflare.com).');
                    return;
                }
                const ctx = document.getElementById('graficoGanancias');
                if (!ctx) {
                    mostrarErrorEnPagina('no se encontró el elemento del gráfico en la página.');
                    return;
                }
                dibujarGrafico(ctx);
            } catch (e) {
                mostrarErrorEnPagina(e.message);
            }
        }

        function dibujarGrafico(ctx) {
        window.__vnCharts.ganancias = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels, JSON_UNESCAPED_UNICODE) !!},
                datasets: [
                    {
                        label: 'Total vendido',
                        data: {!! json_encode($chartVendido) !!},
                        backgroundColor: 'rgba(97, 208, 167, 0.45)',
                        borderColor: '#61D0A7',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        maxBarThickness: 42
                    },
                    {
                        label: 'Ganancia',
                        data: {!! json_encode($chartGanancia) !!},
                        backgroundColor: '#00875F',
                        borderColor: '#01614B',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        maxBarThickness: 42
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { family: 'Outfit', size: 12, weight: '600' },
                            color: '#5F6673'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#01614B',
                        titleFont: { family: 'Outfit', weight: '700' },
                        bodyFont: { family: 'Outfit' },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                let valor = context.parsed.y.toLocaleString('es-CO', { minimumFractionDigits: 0 });
                                return context.dataset.label + ': $' + valor;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Outfit', size: 11 }, color: '#9CA3AF' }
                    },
                    y: {
                        grid: { color: '#F0F2F1' },
                        ticks: {
                            font: { family: 'Outfit', size: 11 },
                            color: '#9CA3AF',
                            callback: function (value) {
                                return '$' + value.toLocaleString('es-CO');
                            }
                        }
                    }
                }
            }
        });
        }

        if (document.readyState === 'complete') {
            iniciarGrafico();
        } else {
            window.addEventListener('load', iniciarGrafico);
        }
    })();
    @endif
</script>

@endsection