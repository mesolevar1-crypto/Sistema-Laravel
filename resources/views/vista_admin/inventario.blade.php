@extends('layouts.dashboard')

@section('content')

<style>
    /* ── Botones ── */
    .btn-primario {
        background: #00875F; color: #fff; border-radius: 10px; border: none;
        font-weight: 600; font-family: 'Outfit', sans-serif; cursor: pointer;
        transition: background .18s, transform .15s;
        box-shadow: 0 4px 12px rgba(0,135,95,.22);
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-primario:hover { background: #01614B; transform: translateY(-2px); }

    /* ── Inputs ── */
    .campo-input {
        background: #fff; border: 1.5px solid #E5E7EB; border-radius: 10px;
        color: #171717; font-family: 'Outfit', sans-serif; font-size: .9rem;
        outline: none; padding: 9px 12px;
        transition: border-color .2s, box-shadow .2s;
    }
    .campo-input:focus { border-color: #61D0A7; box-shadow: 0 0 0 4px rgba(97,208,167,.15); }

    /* ── Modal ── */
    @keyframes modalRise {
        from { opacity: 0; transform: translateY(18px) scale(.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .modal-anim { animation: modalRise .28s cubic-bezier(.22,1,.36,1) forwards; }

    /* ── KPI cards ── */
    .kpi-card {
        background: #fff; border: 1px solid #E5E7EB; border-radius: 14px;
        padding: 18px; transition: transform .18s, box-shadow .18s;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,135,95,.10); }

    /* ── Filtros ── */
    .filtro-link {
        padding: 8px 16px; border-radius: 9px; font-size: .85rem; font-weight: 600;
        border: 1px solid #E5E7EB; text-decoration: none; color: #5F6673;
        background: #fff; transition: all .15s; display: inline-block;
    }
    .filtro-link:hover        { background: #DDF5EC; border-color: #61D0A7; color: #01614B; }
    .filtro-link.activo       { background: #00875F; border-color: #00875F; color: #fff; }
    .filtro-link.activo-bajo  { background: #FFB51B; border-color: #FFB51B; color: #fff; }
    .filtro-link.activo-agot  { background: #E53935; border-color: #E53935; color: #fff; }

    /* ── Paginación ── */
    .pag-btn {
        padding: 7px 13px; border-radius: 8px; border: 1px solid #E5E7EB;
        background: #fff; color: #5F6673; font-size: .85rem; font-weight: 600;
        text-decoration: none; transition: background .15s, border-color .15s, color .15s;
    }
    .pag-btn:hover         { background: #DDF5EC; border-color: #61D0A7; color: #01614B; }
    .pag-btn.activa        { background: #00875F; border-color: #00875F; color: #fff; }
    .pag-btn.deshabilitado { opacity: .4; pointer-events: none; }
</style>

<!-- ════════════════════════════════════════════
     CONTENIDO
════════════════════════════════════════════ -->
<div class="max-w-7xl mx-auto font-sans-ventanet">

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-7 gap-4">
        <div>
            <h2 class="text-3xl font-bold font-serif-ventanet" style="color:#01614B;">Inventario</h2>
            <p class="text-sm mt-1" style="color:#5F6673;">
                Controla las existencias y disponibilidad de tus productos
            </p>
        </div>
    </div>

    <!-- Alerta de sesión -->
    @if (session('alert'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon:  @json(session('alert')['icon'] ?? 'info'),
                title: @json(session('alert')['title'] ?? 'Aviso'),
                text:  @json(session('alert')['text'] ?? ''),
                confirmButtonText:  'Entendido',
                confirmButtonColor: '#00875F',
                customClass: { popup: 'rounded-[20px]', confirmButton: 'rounded-lg px-6 py-2.5' }
            });
        });
    </script>
    @endif

    <!-- ── KPIs ── -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">

        <div class="kpi-card">
            <div class="flex items-center gap-3 mb-3">
                <div style="width:40px;height:40px;background:#DDF5EC;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-boxes" style="color:#00875F;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Total productos</span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:#171717;line-height:1;">{{ (int) ($resumen['total_productos'] ?? 0) }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:3px;">en catálogo</p>
        </div>

        <div class="kpi-card">
            <div class="flex items-center gap-3 mb-3">
                <div style="width:40px;height:40px;background:#DDF5EC;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-cubes" style="color:#00875F;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;letter-spacing:.04em;">Unidades disponibles</span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:#171717;line-height:1;">{{ number_format($resumen['total_unidades'] ?? 0) }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:3px;">en inventario</p>
        </div>

        <div class="kpi-card" style="border-color:#FFB51B;">
            <div class="flex items-center gap-3 mb-3">
                <div style="width:40px;height:40px;background:#fffbeb;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-exclamation-triangle" style="color:#FFB51B;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#92400E;text-transform:uppercase;letter-spacing:.04em;">Stock bajo</span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:#FFB51B;line-height:1;">{{ (int) ($resumen['stock_bajo'] ?? 0) }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:3px;">productos con poco stock</p>
        </div>

        <div class="kpi-card" style="border-color:#fde8e8;">
            <div class="flex items-center gap-3 mb-3">
                <div style="width:40px;height:40px;background:#fde8e8;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-times-circle" style="color:#E53935;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#E53935;text-transform:uppercase;letter-spacing:.04em;">Agotados</span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:#E53935;line-height:1;">{{ (int) ($resumen['agotados'] ?? 0) }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:3px;">sin unidades</p>
        </div>

    </div>

    <!-- ── Búsqueda y filtros (GET para que funcione sin JS) ── -->
    <form method="GET" action="{{ route('admin.inventario') }}" class="flex flex-col sm:flex-row gap-3 mb-6">

        <!-- Buscador -->
        <div style="position:relative;flex:1;">
            <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9CA3AF;font-size:.85rem;pointer-events:none;"></i>
            <input type="text" name="buscar" value="{{ $buscar }}"
                   placeholder="Buscar producto..."
                   class="campo-input" style="padding-left:36px;width:100%;">
            @if ($estado)
                <input type="hidden" name="estado" value="{{ $estado }}">
            @endif
        </div>

        <!-- Botón buscar -->
        <button type="submit" class="btn-primario" style="padding:9px 20px;">
            <i class="fas fa-search"></i> Buscar
        </button>

        @if ($buscar)
        <a href="{{ route('admin.inventario', ['estado' => $estado]) }}"
           style="padding:9px 16px;border-radius:10px;border:1px solid #E5E7EB;background:#fff;color:#5F6673;font-size:.85rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
            <i class="fas fa-times" style="font-size:.75rem;"></i> Limpiar
        </a>
        @endif

    </form>

    <!-- Filtros de estado -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;">
        <a href="{{ route('admin.inventario', ['buscar' => $buscar]) }}"
           class="filtro-link {{ $estado === '' ? 'activo' : '' }}">
            Todos
        </a>
        <a href="{{ route('admin.inventario', ['buscar' => $buscar, 'estado' => 'disponible']) }}"
           class="filtro-link {{ $estado === 'disponible' ? 'activo' : '' }}">
            ✓ Disponible
        </a>
        <a href="{{ route('admin.inventario', ['buscar' => $buscar, 'estado' => 'bajo']) }}"
           class="filtro-link {{ $estado === 'bajo' ? 'activo-bajo' : '' }}">
            ⚠ Stock bajo
        </a>
        <a href="{{ route('admin.inventario', ['buscar' => $buscar, 'estado' => 'agotado']) }}"
           class="filtro-link {{ $estado === 'agotado' ? 'activo-agot' : '' }}">
            ✕ Agotado
        </a>
    </div>

    <!-- ── Tabla ── -->
    <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#E5E7EB;">

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr style="background:#01614B;">
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide" style="color:#fff;">Producto</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide" style="color:#fff;">Categoría</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-center" style="color:#fff;">Stock actual</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-center" style="color:#fff;">Stock mínimo</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-center" style="color:#fff;">Estado</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-center" style="color:#fff;">Última actualización</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-center" style="color:#fff;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inventario as $row)
                        @php
                            $sa = (int) $row['stock_actual'];
                            $sm = (int) $row['stock_minimo'];

                            if ($sa === 0) {
                                $estadoFila = 'agotado';
                            } elseif ($sa <= $sm) {
                                $estadoFila = 'bajo';
                            } else {
                                $estadoFila = 'disponible';
                            }

                            $bgFila  = $estadoFila === 'agotado' ? '#FDECEC'
                                     : ($estadoFila === 'bajo'   ? '#fffbeb' : '#fff');
                            $bgHover = $estadoFila === 'agotado' ? '#fde0e0'
                                     : ($estadoFila === 'bajo'   ? '#fef3c7' : '#F8F8F8');
                            $colorSA = $estadoFila === 'agotado' ? '#E53935'
                                     : ($estadoFila === 'bajo'  ? '#FFB51B' : '#00875F');

                            $datosProducto = json_encode([
                                'id_producto'  => $row['id_producto'],
                                'producto'     => $row['producto'],
                                'stock_actual' => $sa,
                                'stock_minimo' => $sm,
                            ]);
                        @endphp
                        <tr style="border-bottom:1px solid #E5E7EB; background:{{ $bgFila }}; transition:background .15s;"
                            onmouseover="this.style.background='{{ $bgHover }}'"
                            onmouseout="this.style.background='{{ $bgFila }}'">

                            <!-- Producto -->
                            <td class="px-5 py-3.5 font-bold text-sm" style="color:#171717;">
                                {{ $row['producto'] }}
                            </td>

                            <!-- Categoría -->
                            <td class="px-5 py-3.5 text-sm" style="color:#5F6673;">
                                @if (!empty($row['categoria']))
                                    <span style="background:#EBF5FF;color:#1F3552;border:1px solid #BFDBFE;padding:2px 10px;border-radius:999px;font-size:.72rem;font-weight:700;">
                                        {{ $row['categoria'] }}
                                    </span>
                                @else
                                    <span style="color:#9CA3AF;font-style:italic;">Sin categoría</span>
                                @endif
                            </td>

                            <!-- Stock actual -->
                            <td class="px-5 py-3.5 text-center">
                                <span style="font-size:1.4rem;font-weight:800;color:{{ $colorSA }};">
                                    {{ $sa }}
                                </span>
                                <span style="font-size:.72rem;color:#9CA3AF;margin-left:2px;">uds.</span>
                            </td>

                            <!-- Stock mínimo -->
                            <td class="px-5 py-3.5 text-center text-sm" style="color:#5F6673;font-weight:600;">
                                {{ $sm }}
                            </td>

                            <!-- Estado -->
                            <td class="px-5 py-3.5 text-center">
                                @if ($estadoFila === 'agotado')
                                    <span style="background:#fde8e8;color:#E53935;border:1px solid #E53935;padding:3px 12px;border-radius:999px;font-size:.75rem;font-weight:700;display:inline-flex;align-items:center;gap:5px;">
                                        <span style="width:7px;height:7px;background:#E53935;border-radius:50%;display:inline-block;"></span>
                                        Agotado
                                    </span>
                                @elseif ($estadoFila === 'bajo')
                                    <span style="background:#fffbeb;color:#92400E;border:1px solid #FFB51B;padding:3px 12px;border-radius:999px;font-size:.75rem;font-weight:700;display:inline-flex;align-items:center;gap:5px;">
                                        <span style="width:7px;height:7px;background:#FFB51B;border-radius:50%;display:inline-block;"></span>
                                        Stock bajo
                                    </span>
                                @else
                                    <span style="background:#DDF5EC;color:#00875F;border:1px solid #61D0A7;padding:3px 12px;border-radius:999px;font-size:.75rem;font-weight:700;display:inline-flex;align-items:center;gap:5px;">
                                        <span style="width:7px;height:7px;background:#00875F;border-radius:50%;display:inline-block;"></span>
                                        Disponible
                                    </span>
                                @endif
                            </td>

                            <!-- Última actualización -->
                            <td class="px-5 py-3.5 text-center text-sm" style="color:#5F6673;">
                                @if (!empty($row['fecha_actualizacion']))
                                    {{ \Illuminate\Support\Carbon::parse($row['fecha_actualizacion'])->format('d/m/Y') }}
                                @else
                                    <span style="color:#9CA3AF;font-style:italic;">Sin registro</span>
                                @endif
                            </td>

                            <!-- Acción: Actualizar stock -->
                            <td class="px-5 py-3.5 text-center">
                                <button type="button"
                                    onclick='abrirModalActualizar({{ $datosProducto }})'
                                    title="Actualizar stock"
                                    style="width:32px;height:32px;border-radius:8px;border:1px solid #E5E7EB;background:#fff;color:#00875F;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;"
                                    onmouseover="this.style.background='#DDF5EC';this.style.borderColor='#61D0A7';"
                                    onmouseout="this.style.background='#fff';this.style.borderColor='#E5E7EB';">
                                    <i class="fas fa-pen" style="font-size:.75rem;"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:48px;text-align:center;">
                                <div style="width:56px;height:56px;background:#F8F8F8;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;border:1px solid #E5E7EB;">
                                    <i class="fas fa-warehouse" style="color:#9CA3AF;font-size:1.3rem;"></i>
                                </div>
                                <p style="color:#5F6673;font-weight:600;">
                                    {{ ($buscar || $estado) ? 'No se encontraron productos con ese criterio.' : 'No hay productos registrados.' }}
                                </p>
                                @if ($buscar || $estado)
                                <a href="{{ route('admin.inventario') }}" style="color:#00875F;font-size:.85rem;font-weight:600;margin-top:8px;display:inline-block;">
                                    Ver todos los productos
                                </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        @if ($paginas > 1)
            <div style="padding:14px 20px;border-top:1px solid #E5E7EB;display:flex;align-items:center;justify-content:center;flex-wrap:wrap;">
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <a href="{{ route('admin.inventario', ['pagina' => max(1, $pagina - 1), 'buscar' => $buscar, 'estado' => $estado]) }}"
                       class="pag-btn {{ $pagina <= 1 ? 'deshabilitado' : '' }}">
                        ← Anterior
                    </a>
                    @for ($pg = 1; $pg <= $paginas; $pg++)
                        <a href="{{ route('admin.inventario', ['pagina' => $pg, 'buscar' => $buscar, 'estado' => $estado]) }}"
                           class="pag-btn {{ $pg === $pagina ? 'activa' : '' }}">
                            {{ $pg }}
                        </a>
                    @endfor
                    <a href="{{ route('admin.inventario', ['pagina' => min($paginas, $pagina + 1), 'buscar' => $buscar, 'estado' => $estado]) }}"
                       class="pag-btn {{ $pagina >= $paginas ? 'deshabilitado' : '' }}">
                        Siguiente →
                    </a>
                </div>
            </div>
        @endif

    </div><!-- /tabla -->

</div><!-- /max-w -->


<!-- ════════════════════════════════════════════
     MODAL: ACTUALIZAR STOCK
════════════════════════════════════════════ -->
<div id="modalActualizar" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,.50);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl modal-anim overflow-hidden" style="width:100%;max-width:420px;">

        <!-- Cabecera -->
        <div style="background:#F8F8F8;border-bottom:1px solid #E5E7EB;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;background:#00875F;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-cubes" style="color:#fff;font-size:.9rem;"></i>
                </div>
                <div>
                    <h3 class="font-serif-ventanet" style="font-size:1.1rem;color:#171717;">Actualizar stock</h3>
                    <p id="modalNombreProducto" style="font-size:.78rem;color:#5F6673;"></p>
                </div>
            </div>
            <button type="button" onclick="cerrarModal()" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:1.2rem;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Formulario -->
        <form id="formActualizar" action="" method="POST"
              style="padding:24px;display:flex;flex-direction:column;gap:16px;">
            @csrf

            <!-- Stock actual -->
            <div>
                <label style="display:block;font-size:.83rem;font-weight:700;color:#171717;margin-bottom:5px;">
                    Stock actual
                    <span id="modalStockActualRef" style="font-size:.75rem;color:#9CA3AF;font-weight:400;margin-left:6px;"></span>
                </label>
                <input type="number" name="stock_actual" id="modalStockActual"
                       min="0" required class="campo-input" style="width:100%;">
                <p style="font-size:.74rem;color:#9CA3AF;margin-top:4px;">
                    Cantidad de unidades disponibles actualmente.
                </p>
            </div>

            <!-- Stock mínimo -->
            <div>
                <label style="display:block;font-size:.83rem;font-weight:700;color:#171717;margin-bottom:5px;">
                    Stock mínimo
                    <span id="modalStockMinimoRef" style="font-size:.75rem;color:#9CA3AF;font-weight:400;margin-left:6px;"></span>
                </label>
                <input type="number" name="stock_minimo" id="modalStockMinimo"
                       min="0" required class="campo-input" style="width:100%;">
                <p style="font-size:.74rem;color:#9CA3AF;margin-top:4px;">
                    Cuando el stock llegue a este valor se mostrará como "Stock bajo".
                </p>
            </div>

            <!-- Botones -->
            <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:12px;border-top:1px solid #E5E7EB;margin-top:4px;">
                <button type="button" onclick="cerrarModal()"
                    style="padding:9px 20px;border-radius:9px;border:1px solid #E5E7EB;background:#fff;color:#5F6673;font-weight:600;cursor:pointer;">
                    Cancelar
                </button>
                <button type="submit" class="btn-primario" style="padding:9px 24px;">
                    <i class="fas fa-check"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>


<script>
    var baseInventario = "{{ url('/admin/inventario') }}";

    function abrirModalActualizar(datos) {
        document.getElementById('formActualizar').action = baseInventario + '/' + datos.id_producto + '/actualizar';
        document.getElementById('modalNombreProducto').textContent = datos.producto;
        document.getElementById('modalStockActual').value       = datos.stock_actual;
        document.getElementById('modalStockMinimo').value       = datos.stock_minimo;
        document.getElementById('modalStockActualRef').textContent  = '(actual: ' + datos.stock_actual + ')';
        document.getElementById('modalStockMinimoRef').textContent  = '(actual: ' + datos.stock_minimo + ')';
        document.getElementById('modalActualizar').classList.remove('hidden');
    }

    function cerrarModal() {
        document.getElementById('modalActualizar').classList.add('hidden');
    }
</script>

@endsection