@extends('layouts.dashboard')

@section('content')

<style>
    :root {
        --verde:        #00875F;
        --verde-oscuro: #01614B;
        --verde-claro:  #DDF5EC;
        --texto:        #171717;
        --texto-suave:  #5F6673;
        --texto-tenue:  #9CA3AF;
        --borde:        #E5E7EB;
        --fondo-suave:  #F8FAF9;
        --amarillo:     #FFB51B;
        --rojo:         #E53935;
    }

    .btn-primario {
        background: var(--verde); color:#fff; border-radius:10px; border:none; font-weight:600;
        font-family:'Outfit',sans-serif; cursor:pointer;
        display:inline-flex; align-items:center; gap:6px; padding:10px 20px;
        text-decoration:none; font-size:.85rem;
        box-shadow:0 4px 14px rgba(0,135,95,.22);
        transition: background .18s, transform .15s;
    }
    .btn-primario:hover { background: var(--verde-oscuro); transform: translateY(-2px); }
    .btn-primario:disabled { background:#B9BFC6; cursor:not-allowed; box-shadow:none; transform:none; }

    .btn-secundario {
        padding:10px 16px; border-radius:10px; border:1.5px solid var(--borde); background:#fff;
        color:var(--texto-suave); font-size:.85rem; font-weight:600; text-decoration:none;
        display:inline-flex; align-items:center; gap:5px; cursor:pointer;
        font-family:'Outfit',sans-serif;
    }
    .btn-secundario:hover { background: var(--fondo-suave); border-color: var(--verde); color: var(--verde); }

    .campo-input {
        background:#fff; border:1.5px solid var(--borde); border-radius:10px;
        color:var(--texto); font-family:'Outfit',sans-serif; font-size:.9rem;
        outline:none; padding:10px 12px; width:100%;
    }
    .campo-input:focus { border-color: var(--verde); box-shadow:0 0 0 4px rgba(0,135,95,.12); }

    .panel { background:#fff; border:1px solid var(--borde); border-radius:16px; overflow:hidden; }
    .panel-head { background: var(--fondo-suave); border-bottom:1px solid var(--borde); padding:14px 20px; }

    .kpi { background:#fff; border:1px solid var(--borde); border-radius:14px; padding:18px; }
    .kpi-icono {
        width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center;
        margin-bottom:10px;
    }
    .kpi-label { font-size:.68rem; font-weight:800; color:var(--texto-suave); text-transform:uppercase; letter-spacing:.05em; }
    .kpi-valor { font-size:1.8rem; font-weight:800; color:var(--texto); line-height:1; margin-top:6px; }
    .kpi-sub   { font-size:.72rem; color:var(--texto-tenue); margin-top:3px; }

    .filtro-link {
        padding:7px 14px; border-radius:9px; border:1.5px solid var(--borde);
        background:#fff; color:var(--texto-suave); font-size:.8rem; font-weight:700;
        text-decoration:none; display:inline-block;
    }
    .filtro-link.activo-todos { background: var(--verde); border-color: var(--verde); color:#fff; }
    .filtro-link.activo-disp  { background: var(--verde); border-color: var(--verde); color:#fff; }
    .filtro-link.activo-bajo  { background: var(--amarillo); border-color: var(--amarillo); color:#fff; }
    .filtro-link.activo-agot  { background: var(--rojo); border-color: var(--rojo); color:#fff; }

    .tabla th {
        background: var(--verde-oscuro); color:#fff;
        font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em;
        padding:12px 16px;
    }
    .tabla td { padding:12px 16px; font-size:.85rem; }
    .tabla tbody tr { border-bottom:1px solid var(--borde); }

    .etiqueta { padding:3px 12px; border-radius:999px; font-size:.74rem; font-weight:700; border:1px solid; }

    .encabezado-impresion { display:none; }

    @media print {
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body * { visibility: hidden; }
        #reporteImprimir, #reporteImprimir * { visibility: visible; }
        #reporteImprimir { position: absolute; left:0; top:0; width:100%; padding:10px; }
        .no-imprimir { display: none !important; }
        .kpi, .panel { break-inside: avoid; box-shadow: none !important; }
        thead { break-after: avoid; }
        .encabezado-impresion { display: block !important; margin-bottom: 16px; }
        @page { margin: 12mm 10mm; }
    }
</style>

<div class="max-w-7xl mx-auto font-sans-ventanet" id="reporteImprimir">

    <!-- Este bloque solo se ve en el PDF exportado -->
    <div class="encabezado-impresion">
        <h2 style="font-size:1.4rem;font-weight:800;color:#01614B;">VentaNet — Reporte de Inventario</h2>
        <p style="font-size:.8rem;color:#5F6673;">
            {{ $buscar ? 'Búsqueda: "' . $buscar . '" · ' : '' }}
            @foreach ($categorias as $cat)
                @if ($cat['id_categoria'] == $idCat)
                    Categoría: {{ $cat['tipo'] }} ·
                @endif
            @endforeach
            {{ $estado ? 'Estado: ' . $estado . ' · ' : '' }}
            Generado: {{ now()->format('d/m/Y H:i') }}
        </p>
    </div>

    <!-- Encabezado de la página -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4 no-imprimir">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="{{ route('admin.reportes') }}"
               style="padding:8px 14px;border-radius:9px;border:1px solid var(--borde);background:#fff;color:var(--verde);font-size:.82rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
               <i class="fas fa-arrow-left" style="font-size:.7rem;"></i> Volver
            </a>
            <div>
                <h2 class="text-3xl font-bold font-serif-ventanet" style="color:var(--verde-oscuro);">Reporte de Inventario</h2>
                <p class="text-sm mt-1" style="color:var(--texto-suave);">Productos y su stock actual</p>
            </div>
        </div>
        <button type="button" class="btn-primario" onclick="exportarPDF()" @if (empty($inventario)) disabled title="No hay datos para exportar" @endif>
            <i class="fas fa-file-pdf"></i> Exportar a PDF
        </button>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="kpi">
            <div class="kpi-icono" style="background:var(--verde-claro);">
                <i class="fas fa-boxes" style="color:var(--verde);"></i>
            </div>
            <span class="kpi-label">Total productos</span>
            <p class="kpi-valor">{{ $totalProductos }}</p>
            <p class="kpi-sub">según filtro actual</p>
        </div>
        <div class="kpi">
            <div class="kpi-icono" style="background:var(--verde-claro);">
                <i class="fas fa-cubes" style="color:var(--verde);"></i>
            </div>
            <span class="kpi-label">Unidades en stock</span>
            <p class="kpi-valor">{{ number_format($totalUnidades) }}</p>
            <p class="kpi-sub">suma de todas las unidades</p>
        </div>
        <div class="kpi">
            <div class="kpi-icono" style="background:#FFF7E3;">
                <i class="fas fa-exclamation-triangle" style="color:var(--amarillo);"></i>
            </div>
            <span class="kpi-label">Stock bajo</span>
            <p class="kpi-valor" style="color:var(--amarillo);">{{ $totalBajo }}</p>
            <p class="kpi-sub">por debajo del mínimo</p>
        </div>
        <div class="kpi">
            <div class="kpi-icono" style="background:#FDECEC;">
                <i class="fas fa-times-circle" style="color:var(--rojo);"></i>
            </div>
            <span class="kpi-label">Agotados</span>
            <p class="kpi-valor" style="color:var(--rojo);">{{ $totalAgotado }}</p>
            <p class="kpi-sub">sin unidades</p>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('admin.reportes.inventario') }}" class="panel mb-6 no-imprimir">
        <div class="panel-head">
            <h3 style="font-size:.85rem;font-weight:700;color:var(--texto);display:flex;align-items:center;gap:8px;">
                <i class="fas fa-filter" style="color:var(--verde);"></i> Filtros
            </h3>
        </div>
        <div style="padding:18px;display:grid;grid-template-columns:1.4fr 1fr auto auto;gap:12px;align-items:end;">
            <div>
                <label style="display:block;font-size:.78rem;font-weight:700;color:var(--texto-suave);margin-bottom:5px;">Buscar producto</label>
                <input type="text" name="buscar" value="{{ $buscar }}"
                       placeholder="Nombre del producto..." class="campo-input">
            </div>
            <div>
                <label style="display:block;font-size:.78rem;font-weight:700;color:var(--texto-suave);margin-bottom:5px;">Categoría</label>
                <select name="id_categoria" class="campo-input">
                    <option value="0">Todas</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat['id_categoria'] }}" {{ $idCat == $cat['id_categoria'] ? 'selected' : '' }}>
                            {{ $cat['tipo'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primario">
                <i class="fas fa-search"></i> Buscar
            </button>
            <a href="{{ route('admin.reportes.inventario') }}" class="btn-secundario">
                <i class="fas fa-times"></i> Limpiar
            </a>
        </div>
        <div style="padding:0 18px 16px;display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('admin.reportes.inventario', ['buscar' => $buscar, 'id_categoria' => $idCat]) }}"
               class="filtro-link {{ $estado === '' ? 'activo-todos' : '' }}">Todos</a>
            <a href="{{ route('admin.reportes.inventario', ['buscar' => $buscar, 'id_categoria' => $idCat, 'estado' => 'disponible']) }}"
               class="filtro-link {{ $estado === 'disponible' ? 'activo-disp' : '' }}">Disponible</a>
            <a href="{{ route('admin.reportes.inventario', ['buscar' => $buscar, 'id_categoria' => $idCat, 'estado' => 'bajo']) }}"
               class="filtro-link {{ $estado === 'bajo' ? 'activo-bajo' : '' }}">Stock bajo</a>
            <a href="{{ route('admin.reportes.inventario', ['buscar' => $buscar, 'id_categoria' => $idCat, 'estado' => 'agotado']) }}"
               class="filtro-link {{ $estado === 'agotado' ? 'activo-agot' : '' }}">Agotado</a>
        </div>
    </form>

    <!-- Tabla de productos -->
    <div class="panel">
        <div class="panel-head" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-size:.88rem;font-weight:700;color:var(--texto);">Productos</h3>
            <span style="font-size:.78rem;color:var(--texto-suave);">{{ $totalProductos }} resultado(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse tabla">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-center">Stock actual</th>
                        <th class="text-center">Stock mínimo</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inventario as $fila)
                        @php
                            $stockActual = (int) $fila['stock_actual'];
                            $stockMinimo = (int) $fila['stock_minimo'];

                            if ($stockActual === 0) {
                                $estadoTexto = 'Agotado';
                                $colorTexto  = 'var(--rojo)';
                                $fondoFila   = '#FDECEC';
                            } elseif ($stockActual <= $stockMinimo) {
                                $estadoTexto = 'Stock bajo';
                                $colorTexto  = '#92400E';
                                $fondoFila   = '#FFFBEB';
                            } else {
                                $estadoTexto = 'Disponible';
                                $colorTexto  = 'var(--verde)';
                                $fondoFila   = '#fff';
                            }
                        @endphp
                        <tr style="background:{{ $fondoFila }};">
                            <td style="font-weight:700;color:var(--texto);">{{ $fila['producto'] }}</td>
                            <td style="color:var(--texto-suave);">{{ $fila['categoria'] ?? 'Sin categoría' }}</td>
                            <td class="text-center" style="font-weight:800;font-size:1.05rem;color:{{ $colorTexto }};">
                                {{ $stockActual }}
                            </td>
                            <td class="text-center" style="color:var(--texto-suave);">{{ $stockMinimo }}</td>
                            <td class="text-center">
                                <span class="etiqueta" style="color:{{ $colorTexto }};border-color:{{ $colorTexto }};">
                                    {{ $estadoTexto }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:40px;text-align:center;color:var(--texto-suave);">
                                No se encontraron productos con ese filtro.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    const tituloOriginal = document.title;

    function exportarPDF() {
        document.title = "{{ $nombreArchivoPDF }}";
        window.print();
    }
    window.onafterprint = function () {
        document.title = tituloOriginal;
    };
</script>

@endsection