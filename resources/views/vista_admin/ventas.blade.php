@extends('layouts.dashboard')
@php
    $titulo = 'Panel de ventas - Administrador';
@endphp
@section('content')

<style>
.btn-primario {
    background:#00875F;
    color:#fff;
    border-radius:10px;
    border:none;
    font-weight:600;
    font-family:'Outfit',sans-serif;
    cursor:pointer;
    transition: background .18s, transform .15s, box-shadow .18s;
    box-shadow:0 4px 12px rgba(0,135,95,.22);
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
}
.btn-primario:hover { background:#01614B; transform:translateY(-2px); }
.btn-peligro {
    background:#E53935; color:#fff; border-radius:10px; border:none; font-weight:600;
    font-family:'Outfit',sans-serif; cursor:pointer; transition:background .18s;
    display:inline-flex; align-items:center; justify-content:center; gap:6px; text-decoration:none;
}
.btn-peligro:hover { background:#c62828; }
.btn-exito {
    background:#00875F; color:#fff; border-radius:10px; border:none; font-weight:600;
    font-family:'Outfit',sans-serif; cursor:pointer; transition:background .18s;
    display:inline-flex; align-items:center; justify-content:center; gap:6px; text-decoration:none;
}
.btn-exito:hover { background:#01614B; }
.campo-input {
    width:100%; background:#fff; border:1.5px solid #E5E7EB; border-radius:10px;
    color:#171717; font-family:'Outfit',sans-serif; font-size:.9rem; outline:none;
    padding:9px 12px; transition: border-color .2s, box-shadow .2s;
}
.campo-input:focus { border-color:#61D0A7; box-shadow:0 0 0 4px rgba(97,208,167,.15); }
@keyframes modalRise {
    from { opacity:0; transform:translateY(18px) scale(.98); }
    to { opacity:1; transform:translateY(0) scale(1); }
}
.modal-anim { animation: modalRise .28s cubic-bezier(.22,1,.36,1) forwards; }
.kpi-card {
    background:#fff; border:1px solid #E5E7EB; border-radius:14px; padding:18px;
    transition: transform .18s, box-shadow .18s;
}
.kpi-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,135,95,.10); }
.item-row {
    background:#F8F8F8; border:1.5px solid #E5E7EB; border-radius:12px; padding:16px;
    display:flex; flex-direction:column; gap:14px;
}
.item-row-seccion {
    font-size:.62rem; color:#9CA3AF; font-weight:700; text-transform:uppercase;
    letter-spacing:.04em; margin-bottom:6px;
}
.item-row-fila1 { display:grid; grid-template-columns: 2fr 1fr; gap:12px; margin-bottom:14px; }
.item-row-fila2 { display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px; margin-bottom:14px; }
.item-row-fila3 { display:grid; grid-template-columns: 1fr 1fr 1fr 30px; gap:12px; align-items:end; }
.item-row-label { display:block; font-size:.78rem; font-weight:700; color:#171717; margin-bottom:4px; }
.item-row-ayuda { font-size:.68rem; color:#9CA3AF; margin-top:4px; line-height:1.3; }
.paginacion { padding:14px 20px; border-top:1px solid #E5E7EB; display:flex; justify-content:center; gap:6px; }
.pag {
    width:36px; height:36px; border:1px solid #E5E7EB; border-radius:9px; background:#fff;
    color:#5F6673; display:flex; align-items:center; justify-content:center; text-decoration:none;
}
.pag:hover, .pag.activa { background:#00875F; color:#fff; }
.pag.deshabilitado { opacity:.4; pointer-events:none; }
.estado-badge { padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; }
.estado-activa  { background:#DDF5EC; color:#00875F; }
.estado-anulada { background:#fde8e8; color:#E53935; }
.btn-accion-cuadro {
    width:32px; height:32px; border-radius:8px; border:1px solid #E5E7EB; background:#fff;
    cursor:pointer; display:flex; align-items:center; justify-content:center; text-decoration:none;
    transition: background .15s, transform .15s;
}
.btn-accion-cuadro:hover { transform:translateY(-2px); }
@media (max-width:900px) {
    .item-row-fila1, .item-row-fila2, .item-row-fila3 { grid-template-columns: 1fr; }
}
</style>

<div class="max-w-7xl mx-auto font-sans-ventanet">
    <!-- ENCABEZADO -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-7 gap-4">
        <div>
            <h2 class="text-3xl font-bold font-serif-ventanet" style="color:#01614B;">
                Gestionar Ventas
            </h2>
            <p class="text-sm mt-1" style="color:#5F6673;">
                Registra y administra las ventas realizadas a tus clientes.
            </p>
        </div>
        <button type="button" onclick="abrirModalNuevaVenta()" class="btn-primario px-5 py-2.5">
            <i class="fas fa-cash-register"></i>
            Nueva Venta
        </button>
    </div>

    <!-- ALERTAS -->
    @if (session('alert'))
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: @json(session('alert')['icon'] ?? 'info'),
                title: @json(session('alert')['title'] ?? 'Aviso'),
                text: @json(session('alert')['text'] ?? ''),
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#00875F',
                customClass: { popup: 'rounded-[20px]', confirmButton: 'rounded-lg px-6 py-2.5' }
            });
        });
        </script>
    @endif

    <!-- KPIs -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
        <div class="kpi-card">
            <div class="flex items-center gap-3 mb-3">
                <div style="width:40px;height:40px;background:#DDF5EC;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-receipt" style="color:#00875F;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;">Total ventas</span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:#171717;line-height:1;">{{ (int) ($resumen['total_ventas'] ?? 0) }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:3px;">histórico</p>
        </div>
        <div class="kpi-card">
            <div class="flex items-center gap-3 mb-3">
                <div style="width:40px;height:40px;background:#DDF5EC;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-dollar-sign" style="color:#00875F;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;">Ingresos total</span>
            </div>
            <p style="font-size:1.4rem;font-weight:800;color:#171717;line-height:1;">{{ number_format($resumen['ingresos_total'] ?? 0, 0, ',', '.') }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:3px;">acumulado</p>
        </div>
        <div class="kpi-card">
            <div class="flex items-center gap-3 mb-3">
                <div style="width:40px;height:40px;background:#fffbeb;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-calendar-day" style="color:#FFB51B;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;">Ventas hoy</span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:#171717;line-height:1;">{{ (int) ($resumen['ventas_hoy'] ?? 0) }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:3px;">del día</p>
        </div>
        <div class="kpi-card">
            <div class="flex items-center gap-3 mb-3">
                <div style="width:40px;height:40px;background:#fffbeb;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-coins" style="color:#FFB51B;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;">Ingresos hoy</span>
            </div>
            <p style="font-size:1.4rem;font-weight:800;color:#171717;line-height:1;">{{ number_format($resumen['ingresos_hoy'] ?? 0, 0, ',', '.') }}</p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:3px;">del día</p>
        </div>
    </div>

    <!-- TABLA -->
    <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#E5E7EB;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr style="background:#01614B;">
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide" style="color:#fff;">Fecha</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide" style="color:#fff;">Cliente</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide" style="color:#fff;">Registrado por</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-right" style="color:#fff;">Total</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-right" style="color:#fff;">Ganancia</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-right" style="color:#fff;">Margen</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-center" style="color:#fff;">Estado</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-center" style="color:#fff;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ventas as $v)
                        @php
                            $gan    = (float) ($v['ganancia'] ?? 0);
                            $total  = (float) ($v['total'] ?? 0);
                            $margen = $total > 0 ? round(($gan / $total) * 100, 1) : 0;
                            $cg     = $gan >= 0 ? '#00875F' : '#E53935';
                            $activa = (bool) ($v['estado'] ?? false);
                        @endphp
                        <tr
                            style="border-bottom:1px solid #E5E7EB; transition:background .15s;"
                            onmouseover="this.style.background='#F8F8F8'"
                            onmouseout="this.style.background=''"
                        >
                            <td class="px-5 py-3.5 text-sm font-medium" style="color:#171717;">
                                {{ !empty($v['fecha']) ? \Illuminate\Support\Carbon::parse($v['fecha'])->format('d/m/Y') : '---' }}
                            </td>
                            <td class="px-5 py-3.5 text-sm font-bold" style="color:#171717;">
                                {{ $v['cliente'] ?? 'Sin cliente' }}
                            </td>
                            <td class="px-5 py-3.5 text-sm" style="color:#5F6673;">
                                {{ $v['vendedor'] ?? '---' }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span style="font-size:1rem;font-weight:800;color:#00875F;">
                                    {{ number_format($total, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span style="font-size:.9rem;font-weight:800;color:{{ $cg }};">
                                    ${{ number_format(abs($gan), 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span style="font-size:.9rem;font-weight:800;color:{{ $cg }};">
                                    {{ $margen }}%
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="estado-badge {{ $activa ? 'estado-activa' : 'estado-anulada' }}">
                                    {{ $activa ? 'Activa' : 'Anulada' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div style="display:flex;align-items:center;justify-content:center;gap:8px;">

                                    <!-- VER DETALLE -->
                                    <button type="button" onclick="verDetalle({{ (int) $v['id_venta'] }})"
                                        title="Ver detalle" class="btn-accion-cuadro" style="color:#00875F;">
                                        <i class="fas fa-eye" style="font-size:.75rem;"></i>
                                    </button>

                                    <!-- VER FACTURA -->
                                    <a href="{{ route('admin.ventas.factura', $v['id_venta']) }}" title="Ver factura"
                                       class="btn-accion-cuadro" style="color:#5F6673;">
                                        <i class="fas fa-file-invoice" style="font-size:.75rem;"></i>
                                    </a>

                                    <!-- ANULAR / REACTIVAR según el estado actual -->
                                    @if ($activa)
                                        <button type="button" onclick="abrirModalEliminar({{ (int) $v['id_venta'] }})"
                                            title="Anular venta" class="btn-accion-cuadro" style="border-color:#fde8e8;color:#E53935;">
                                            <i class="fas fa-ban" style="font-size:.75rem;"></i>
                                        </button>
                                    @else
                                        <button type="button" onclick="abrirModalReactivar({{ (int) $v['id_venta'] }})"
                                            title="Reactivar venta" class="btn-accion-cuadro" style="border-color:#DDF5EC;color:#00875F;">
                                            <i class="fas fa-rotate-left" style="font-size:.75rem;"></i>
                                        </button>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:48px;text-align:center;">
                                <div style="width:56px;height:56px;background:#F8F8F8;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;border:1px solid #E5E7EB;">
                                    <i class="fas fa-receipt" style="color:#9CA3AF;font-size:1.3rem;"></i>
                                </div>
                                <p style="color:#5F6673;font-weight:600;">No hay ventas registradas.</p>
                                <p style="color:#9CA3AF;font-size:.85rem;margin-top:4px;">
                                    Registra tu primera venta usando el botón superior.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        @if ($total > 5)
            <nav class="paginacion">
                <a class="pag {{ $pagina <= 1 ? 'deshabilitado' : '' }}" href="{{ route('admin.ventas', ['pagina' => max(1, $pagina - 1)]) }}">«</a>
                @for ($i = 1; $i <= $paginas; $i++)
                    <a class="pag {{ $i == $pagina ? 'activa' : '' }}" href="{{ route('admin.ventas', ['pagina' => $i]) }}">{{ $i }}</a>
                @endfor
                <a class="pag {{ $pagina >= $paginas ? 'deshabilitado' : '' }}" href="{{ route('admin.ventas', ['pagina' => min($paginas, $pagina + 1)]) }}">»</a>
            </nav>
        @endif
    </div>
</div>

<!-- ============================================================
     MODAL NUEVA VENTA
============================================================ -->
<div id="modalCrear" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.50);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl w-full shadow-2xl modal-anim overflow-hidden" style="max-width:1120px;max-height:92vh;display:flex;flex-direction:column;">

        <div style="background:#F8F8F8;border-bottom:1px solid #E5E7EB;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;background:#00875F;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-cash-register" style="color:#fff;font-size:.9rem;"></i>
                </div>
                <div>
                    <h3 class="font-serif-ventanet" style="font-size:1.1rem;color:#171717;">Nueva Venta</h3>
                    <p style="font-size:.75rem;color:#5F6673;margin-top:2px;">
                        Selecciona un cliente activo, luego el producto, y escribe el precio, la cantidad, el descuento (si aplica) y la unidad de venta.
                    </p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalCrear()" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:1.2rem;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formVenta" action="{{ route('admin.ventas.store') }}" method="POST"
              style="padding:24px;display:flex;flex-direction:column;gap:18px;overflow-y:auto;flex:1;">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:700;color:#171717;margin-bottom:5px;">Cliente *</label>
                    @if (empty($clientes))
                        <div style="background:#fffbeb;border:1px solid #FFB51B;border-radius:10px;padding:12px 14px;color:#92400E;font-size:.85rem;">
                            <i class="fas fa-exclamation-triangle" style="color:#FFB51B;margin-right:6px;"></i>
                            No hay clientes activos disponibles. Registra o activa un cliente primero.
                        </div>
                    @else
                        <div style="position:relative;">
                            <select id="selectCliente" name="id_cliente" required class="campo-input" style="appearance:none;cursor:pointer;padding-right:36px;">
                                <option value="">Selecciona un cliente</option>
                                @foreach ($clientes as $cl)
                                    <option value="{{ (int) $cl['id_cliente'] }}">{{ $cl['nombre'] ?? 'Cliente' }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#00875F;font-size:.75rem;pointer-events:none;"></i>
                        </div>
                    @endif
                </div>

                <div>
                    <label style="display:block;font-size:.83rem;font-weight:700;color:#171717;margin-bottom:5px;">Método de pago</label>
                    <div style="position:relative;">
                        <select name="metodo_pago" class="campo-input" style="appearance:none;cursor:pointer;padding-right:36px;">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                        <i class="fas fa-chevron-down" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#00875F;font-size:.75rem;pointer-events:none;"></i>
                    </div>
                </div>
            </div>

            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <div>
                        <label style="font-size:.83rem;font-weight:700;color:#171717;">Productos *</label>
                        <p style="font-size:.72rem;color:#9CA3AF;margin-top:2px;">
                            El precio de venta, el descuento, la unidad, el contenido y su unidad los defines tú según lo que se acordó con el cliente.
                        </p>
                    </div>
                    <button type="button" onclick="agregarFila()"
                        style="background:#DDF5EC;color:#00875F;border:1px solid #61D0A7;border-radius:8px;padding:6px 12px;font-size:.8rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:5px;">
                        <i class="fas fa-plus" style="font-size:.7rem;"></i> Agregar producto
                    </button>
                </div>
                <div id="filasProductos" style="display:flex;flex-direction:column;gap:12px;"></div>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;background:#DDF5EC;border:1px solid #61D0A7;border-radius:12px;padding:13px 18px;">
                <span style="font-size:.82rem;font-weight:700;color:#01614B;text-transform:uppercase;letter-spacing:.04em;">Total de la venta</span>
                <span id="totalVenta" style="font-size:1.5rem;font-weight:800;color:#00875F;">$0</span>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:12px;border-top:1px solid #E5E7EB;">
                <button type="button" onclick="cerrarModalCrear()" style="padding:9px 20px;border-radius:9px;border:1px solid #E5E7EB;background:#fff;color:#5F6673;font-weight:600;cursor:pointer;">
                    Cancelar
                </button>
                <button type="submit" class="btn-primario" style="padding:9px 24px;">
                    <i class="fas fa-check"></i> Confirmar Venta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
     MODAL DETALLE
============================================================ -->
<div id="modalDetalle" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.50);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl w-full shadow-2xl modal-anim overflow-hidden" style="max-width:820px;">
        <div style="background:#F8F8F8;border-bottom:1px solid #E5E7EB;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;background:#00875F;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-receipt" style="color:#fff;font-size:.9rem;"></i>
                </div>
                <div>
                    <h3 class="font-serif-ventanet" style="font-size:1.1rem;color:#171717;">Detalle de Venta</h3>
                    <p id="detalleEncabezado" style="font-size:.78rem;color:#5F6673;"></p>
                </div>
            </div>
            <button type="button" onclick="cerrarModal('modalDetalle')" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:1.2rem;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="detalleCuerpo" style="padding:24px;max-height:500px;overflow-y:auto;">
            <div style="text-align:center;padding:32px 0;">
                <i class="fas fa-spinner fa-spin" style="color:#00875F;font-size:1.5rem;"></i>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL ANULAR
============================================================ -->
<div id="modalEliminar" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.55);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl w-full shadow-2xl modal-anim overflow-hidden" style="max-width:420px;">
        <div style="padding:36px 32px;text-align:center;">
            <div style="width:68px;height:68px;background:#fde8e8;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fas fa-exclamation-triangle" style="color:#E53935;font-size:1.7rem;"></i>
            </div>
            <h3 class="font-serif-ventanet" style="font-size:1.3rem;color:#171717;margin-bottom:8px;">Anular Venta</h3>
            <p style="color:#5F6673;font-size:.9rem;">
                El registro histórico se conserva y el inventario vendido será devuelto al stock.
            </p>
        </div>
        <div style="background:#F8F8F8;border-top:1px solid #E5E7EB;padding:16px 24px;display:flex;justify-content:center;gap:10px;">
            <button type="button" onclick="cerrarModal('modalEliminar')" style="padding:9px 20px;border-radius:9px;border:1px solid #E5E7EB;background:#fff;color:#5F6673;font-weight:600;cursor:pointer;">
                Cancelar
            </button>
            <button type="button" onclick="confirmarAnular()" class="btn-peligro" style="padding:9px 24px;">
                Sí, anular
            </button>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL REACTIVAR
============================================================ -->
<div id="modalReactivar" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.55);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl w-full shadow-2xl modal-anim overflow-hidden" style="max-width:420px;">
        <div style="padding:36px 32px;text-align:center;">
            <div style="width:68px;height:68px;background:#DDF5EC;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fas fa-rotate-left" style="color:#00875F;font-size:1.5rem;"></i>
            </div>
            <h3 class="font-serif-ventanet" style="font-size:1.3rem;color:#171717;margin-bottom:8px;">Reactivar Venta</h3>
            <p style="color:#5F6673;font-size:.9rem;">
                La venta volverá a estar activa y el inventario se descontará de nuevo. Si no hay stock suficiente, no se podrá reactivar.
            </p>
        </div>
        <div style="background:#F8F8F8;border-top:1px solid #E5E7EB;padding:16px 24px;display:flex;justify-content:center;gap:10px;">
            <button type="button" onclick="cerrarModal('modalReactivar')" style="padding:9px 20px;border-radius:9px;border:1px solid #E5E7EB;background:#fff;color:#5F6673;font-weight:600;cursor:pointer;">
                Cancelar
            </button>
            <button type="button" onclick="confirmarReactivar()" class="btn-exito" style="padding:9px 24px;">
                Sí, reactivar
            </button>
        </div>
    </div>
</div>

<!-- Formularios ocultos para anular y reactivar (Laravel requiere POST, no GET) -->
<form id="formAnular" method="POST" style="display:none">
    @csrf
</form>
<form id="formReactivar" method="POST" style="display:none">
    @csrf
</form>

<script>
// ============================================================
// DATOS DESDE PHP (vía Blade)
// ============================================================
var productos = @json($productos);
var unidades = @json($unidades);

var baseVentas = "{{ url('/admin/ventas') }}";

var filaCount = 0;

function generarOpcionesUnidad(placeholder) {
    var texto = placeholder || 'Unidad';
    var html = '<option value="">' + escapeHtml(texto) + '</option>';
    unidades.forEach(function (u) {
        html += '<option value="' + u.id_unidad + '">' + escapeHtml(u.nombre || 'Unidad') + '</option>';
    });
    return html;
}

function abrirModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.remove('hidden');
}

function cerrarModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

function abrirModalNuevaVenta() {
    abrirModal('modalCrear');
    var filas = document.getElementById('filasProductos');
    if (filas && filas.children.length === 0) {
        agregarFila();
    }
}

function cerrarModalCrear() {
    cerrarModal('modalCrear');
    var filas = document.getElementById('filasProductos');
    if (filas) filas.innerHTML = '';
    var cliente = document.getElementById('selectCliente');
    if (cliente) cliente.value = '';
    var total = document.getElementById('totalVenta');
    if (total) total.textContent = '$0';
    filaCount = 0;
}

// ============================================================
// ANULAR / REACTIVAR
// ============================================================
var ventaAnular = null;
var ventaReactivar = null;

function abrirModalEliminar(id) {
    ventaAnular = id;
    abrirModal('modalEliminar');
}

function confirmarAnular() {
    if (!ventaAnular) return;
    var form = document.getElementById('formAnular');
    form.action = baseVentas + '/' + ventaAnular + '/anular';
    form.submit();
}

function abrirModalReactivar(id) {
    ventaReactivar = id;
    abrirModal('modalReactivar');
}

function confirmarReactivar() {
    if (!ventaReactivar) return;
    var form = document.getElementById('formReactivar');
    form.action = baseVentas + '/' + ventaReactivar + '/reactivar';
    form.submit();
}

// ============================================================
// RECONSTRUIR OPCIONES DE PRODUCTO (con stock disponible)
// ============================================================
function reconstruirOpcionesProducto(selectEl, idx) {
    if (!selectEl) return;

    var valorActual = selectEl.value;
    selectEl.innerHTML = '<option value="">Seleccionar producto</option>';

    productos.forEach(function (p) {
        var stock = parseInt(p.stock) || 0;
        var option = document.createElement('option');
        option.value = p.id_producto;
        option.dataset.stock = stock;

        var texto = p.nombre || 'Producto';
        if (stock <= 0) {
            texto += ' — SIN STOCK';
            option.disabled = true;
        } else {
            texto += ' [' + stock + ' disp.]';
        }
        option.textContent = texto;

        selectEl.appendChild(option);
    });

    if (valorActual) selectEl.value = valorActual;
    onProductoChange(idx);
}

// ============================================================
// AGREGAR FILA
// ============================================================
function agregarFila() {
    var container = document.getElementById('filasProductos');
    var idx = filaCount++;
    var div = document.createElement('div');
    div.className = 'item-row';
    div.id = 'fila-' + idx;

    div.innerHTML =
        '<div>' +
            '<p class="item-row-seccion">Qué vendes</p>' +
            '<div class="item-row-fila1">' +
                '<div>' +
                    '<span class="item-row-label">Producto</span>' +
                    '<select class="campo-input select-producto" name="id_producto[]" data-idx="' + idx + '" required ' +
                        'onchange="onProductoChange(' + idx + ')">' +
                        '<option value="">Seleccionar producto</option>' +
                    '</select>' +
                    '<p class="item-row-ayuda">El producto que le estás vendiendo al cliente.</p>' +
                '</div>' +
                '<div>' +
                    '<span class="item-row-label">Cantidad</span>' +
                    '<input type="number" name="cantidad[]" id="cant-' + idx + '" min="1" step="1" value="1" required ' +
                        'oninput="calcularFila(' + idx + ')" class="campo-input" style="text-align:center;">' +
                    '<p class="item-row-ayuda">Cuántas unidades de venta le estás entregando.</p>' +
                '</div>' +
            '</div>' +
        '</div>' +

        '<div>' +
            '<p class="item-row-seccion">Cómo se lo vendes al cliente</p>' +
            '<div class="item-row-fila2">' +
                '<div>' +
                    '<span class="item-row-label">Unidad de venta</span>' +
                    '<select class="campo-input" name="id_unidad[]" id="unidad-' + idx + '" required>' +
                        generarOpcionesUnidad('Unidad') +
                    '</select>' +
                    '<p class="item-row-ayuda">La presentación en que se lo llevas: pieza, paquete, caja.</p>' +
                '</div>' +
                '<div>' +
                    '<span class="item-row-label">Contenido</span>' +
                    '<input type="number" name="cantidad_por_unidad[]" id="cpu-' + idx + '" min="1" step="1" value="1" required ' +
                        'oninput="calcularFila(' + idx + ')" class="campo-input" style="text-align:center;">' +
                    '<p class="item-row-ayuda">Cuántas piezas trae cada presentación vendida.</p>' +
                '</div>' +
                '<div>' +
                    '<span class="item-row-label">Unid. contenido</span>' +
                    '<select class="campo-input" name="id_unidad_contenido[]" id="unidadcontenido-' + idx + '" required>' +
                        generarOpcionesUnidad('Contenido') +
                    '</select>' +
                    '<p class="item-row-ayuda">En qué se mide ese contenido interno.</p>' +
                '</div>' +
            '</div>' +
        '</div>' +

        '<div>' +
            '<p class="item-row-seccion">Precio y descuento</p>' +
            '<div class="item-row-fila3">' +
                '<div>' +
                    '<span class="item-row-label">Precio venta</span>' +
                    '<input type="number" name="precio_venta[]" id="precio-' + idx + '" min="0.01" step="0.01" required ' +
                        'oninput="calcularFila(' + idx + ')" class="campo-input" placeholder="0">' +
                    '<p class="item-row-ayuda">Lo que le cobras al cliente por cada unidad de venta.</p>' +
                '</div>' +
                '<div>' +
                    '<span class="item-row-label">Desc. %</span>' +
                    '<input type="number" name="descuento_porcentaje[]" id="desc-' + idx + '" min="0" max="100" step="0.01" value="0" ' +
                        'oninput="calcularFila(' + idx + ')" class="campo-input" style="text-align:center;">' +
                    '<p class="item-row-ayuda">Descuento sobre esta línea, si aplica.</p>' +
                '</div>' +
                '<div>' +
                    '<span class="item-row-label">Subtotal</span>' +
                    '<div style="position:relative;">' +
                        '<span style="position:absolute;left:8px;top:50%;transform:translateY(-50%);color:#9CA3AF;font-size:.8rem;">$</span>' +
                        '<input type="text" id="subvisual-' + idx + '" value="0" readonly class="campo-input" ' +
                            'style="padding-left:22px;text-align:right;background:#fff;font-weight:700;color:#00875F;cursor:not-allowed;">' +
                    '</div>' +
                    '<p class="item-row-ayuda">Cantidad × precio, menos el descuento.</p>' +
                '</div>' +
                '<button type="button" onclick="quitarFila(' + idx + ')" title="Quitar producto" ' +
                    'style="width:26px;height:26px;border-radius:6px;background:#fde8e8;border:none;color:#E53935;cursor:pointer;display:flex;align-items:center;justify-content:center;margin-bottom:4px;">' +
                    '<i class="fas fa-times" style="font-size:.65rem;"></i>' +
                '</button>' +
            '</div>' +
        '</div>';

    container.appendChild(div);

    var select = div.querySelector('.select-producto');
    reconstruirOpcionesProducto(select, idx);
}

function quitarFila(idx) {
    var el = document.getElementById('fila-' + idx);
    if (el) {
        el.remove();
        recalcularTotal();
    }
}

function onProductoChange(idx) {
    calcularFila(idx);
}

function calcularFila(idx) {
    var cantidadInput = document.getElementById('cant-' + idx);
    var precioInput = document.getElementById('precio-' + idx);
    var descuentoInput = document.getElementById('desc-' + idx);
    var subtotalVisual = document.getElementById('subvisual-' + idx);

    var cantidadTexto = cantidadInput ? cantidadInput.value.trim() : '';
    var cantidad = /^\d+$/.test(cantidadTexto) ? Number(cantidadTexto) : 0;

    if (cantidadInput && cantidadInput.value !== '' && !Number.isInteger(parseFloat(cantidadInput.value))) {
        cantidadInput.value = cantidad;
    }

    var precioVenta = precioInput ? (parseFloat(precioInput.value) || 0) : 0;
    var descuentoPct = descuentoInput ? (parseFloat(descuentoInput.value) || 0) : 0;
    descuentoPct = Math.min(Math.max(descuentoPct, 0), 100);

    var bruto = cantidad * precioVenta;
    var subtotal = bruto - (bruto * descuentoPct / 100);

    if (subtotalVisual) {
        subtotalVisual.value = subtotal.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    recalcularTotal();
}

function recalcularTotal() {
    var total = 0;

    document.querySelectorAll('#filasProductos .item-row').forEach(function (fila) {
        var idx = fila.id.replace('fila-', '');
        var cantidadInput = document.getElementById('cant-' + idx);
        var precioInput = document.getElementById('precio-' + idx);
        var descuentoInput = document.getElementById('desc-' + idx);

        var cantidadTexto = cantidadInput ? cantidadInput.value.trim() : '';
        var cantidad = /^\d+$/.test(cantidadTexto) ? Number(cantidadTexto) : 0;
        var precioVenta = precioInput ? (parseFloat(precioInput.value) || 0) : 0;
        var descuentoPct = descuentoInput ? (parseFloat(descuentoInput.value) || 0) : 0;
        descuentoPct = Math.min(Math.max(descuentoPct, 0), 100);

        var bruto = cantidad * precioVenta;
        total += bruto - (bruto * descuentoPct / 100);
    });

    var totalElemento = document.getElementById('totalVenta');
    if (totalElemento) {
        totalElemento.textContent = '$' + total.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }
}

// ============================================================
// VER DETALLE
// ============================================================
function verDetalle(id) {
    document.getElementById('detalleEncabezado').textContent = 'Venta #' + id;
    document.getElementById('detalleCuerpo').innerHTML =
        '<div style="text-align:center;padding:32px 0;"><i class="fas fa-spinner fa-spin" style="color:#00875F;font-size:1.5rem;"></i></div>';

    abrirModal('modalDetalle');

    fetch(baseVentas + '/' + encodeURIComponent(id) + '/detalle')
        .then(function (response) {
            if (!response.ok) throw new Error('Error HTTP');
            return response.json();
        })
        .then(function (data) {
            if (!Array.isArray(data) || data.length === 0) {
                document.getElementById('detalleCuerpo').innerHTML =
                    '<p style="text-align:center;color:#9CA3AF;padding:24px 0;">Sin productos registrados en esta venta.</p>';
                return;
            }

            var html =
                '<table style="width:100%;border-collapse:collapse;">' +
                    '<thead>' +
                        '<tr style="background:#F8F8F8;">' +
                            '<th style="padding:8px 10px;text-align:left;font-size:.75rem;color:#5F6673;text-transform:uppercase;">Producto</th>' +
                            '<th style="padding:8px 10px;text-align:center;font-size:.75rem;color:#5F6673;text-transform:uppercase;">Cant.</th>' +
                            '<th style="padding:8px 10px;text-align:right;font-size:.75rem;color:#5F6673;text-transform:uppercase;">P. Venta</th>' +
                            '<th style="padding:8px 10px;text-align:right;font-size:.75rem;color:#5F6673;text-transform:uppercase;">Desc.</th>' +
                            '<th style="padding:8px 10px;text-align:right;font-size:.75rem;color:#5F6673;text-transform:uppercase;">Subtotal</th>' +
                            '<th style="padding:8px 10px;text-align:right;font-size:.75rem;color:#5F6673;text-transform:uppercase;">Ganancia</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody>';

            var totalV = 0, totalG = 0;

            data.forEach(function (d) {
                totalV += parseFloat(d.subtotal) || 0;
                var gan = parseFloat(d.ganancia_linea) || 0;
                totalG += gan;
                var cg = gan >= 0 ? '#00875F' : '#E53935';
                var descPct = parseFloat(d.descuento_porcentaje) || 0;

                html +=
                    '<tr style="border-bottom:1px solid #E5E7EB;">' +
                        '<td style="padding:9px 10px;font-weight:700;color:#171717;font-size:.85rem;">' + escapeHtml(d.producto || '') + '</td>' +
                        '<td style="padding:9px 10px;text-align:center;color:#171717;font-size:.85rem;">' + d.cantidad + '</td>' +
                        '<td style="padding:9px 10px;text-align:right;color:#5F6673;font-size:.82rem;">$' + Number(d.precio_venta).toLocaleString('es-CO') + '</td>' +
                        '<td style="padding:9px 10px;text-align:right;color:#5F6673;font-size:.82rem;">' + (descPct > 0 ? descPct + '%' : '—') + '</td>' +
                        '<td style="padding:9px 10px;text-align:right;font-weight:700;color:#00875F;font-size:.9rem;">$' + Number(d.subtotal).toLocaleString('es-CO') + '</td>' +
                        '<td style="padding:9px 10px;text-align:right;font-weight:700;color:' + cg + ';font-size:.85rem;">$' + Number(Math.abs(gan)).toLocaleString('es-CO') + '</td>' +
                    '</tr>';
            });

            var margen = totalV > 0 ? (totalG / totalV * 100).toFixed(1) : 0;

            html +=
                '</tbody></table>' +
                '<div style="margin-top:14px;padding:14px 10px;background:#DDF5EC;border-radius:10px;border:1px solid #61D0A7;">' +
                    '<div style="display:flex;justify-content:space-between;margin-bottom:6px;">' +
                        '<span style="font-size:.78rem;font-weight:700;color:#01614B;">Total venta</span>' +
                        '<span style="font-weight:800;color:#00875F;">$' + totalV.toLocaleString('es-CO') + '</span>' +
                    '</div>' +
                    '<div style="display:flex;justify-content:space-between;margin-bottom:6px;">' +
                        '<span style="font-size:.78rem;font-weight:700;color:#01614B;">Ganancia total</span>' +
                        '<span style="font-weight:800;color:#00875F;">$' + Math.abs(totalG).toLocaleString('es-CO') + '</span>' +
                    '</div>' +
                    '<div style="display:flex;justify-content:space-between;">' +
                        '<span style="font-size:.78rem;font-weight:700;color:#01614B;">Margen</span>' +
                        '<span style="font-weight:800;color:#00875F;">' + margen + '%</span>' +
                    '</div>' +
                '</div>';

            document.getElementById('detalleCuerpo').innerHTML = html;
        })
        .catch(function (error) {
            console.error(error);
            document.getElementById('detalleCuerpo').innerHTML =
                '<p style="text-align:center;color:#E53935;padding:24px 0;">Error al cargar el detalle de la venta.</p>';
        });
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('click', function (event) {
    if (event.target.id === 'modalCrear') cerrarModalCrear();
    if (event.target.id === 'modalDetalle') cerrarModal('modalDetalle');
    if (event.target.id === 'modalEliminar') cerrarModal('modalEliminar');
    if (event.target.id === 'modalReactivar') cerrarModal('modalReactivar');
});

document.getElementById('formVenta')?.addEventListener('submit', function (event) {
    var cliente = document.getElementById('selectCliente');
    if (!cliente || !cliente.value) {
        event.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Cliente requerido', text: 'Debes seleccionar un cliente activo.', confirmButtonColor: '#00875F' });
        return;
    }

    var filas = document.querySelectorAll('#filasProductos .item-row');
    if (filas.length === 0) {
        event.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Sin productos', text: 'Debes agregar al menos un producto.', confirmButtonColor: '#00875F' });
        return;
    }

    var valido = true;
    var mensaje = '';

    filas.forEach(function (fila) {
        var idx = fila.id.replace('fila-', '');
        var select = fila.querySelector('.select-producto');
        var cantidadInput = document.getElementById('cant-' + idx);
        var precioInput = document.getElementById('precio-' + idx);
        var descuentoInput = document.getElementById('desc-' + idx);
        var unidadSelect = document.getElementById('unidad-' + idx);
        var cpuInput = document.getElementById('cpu-' + idx);
        var unidadContenidoSelect = document.getElementById('unidadcontenido-' + idx);

        if (!select || !select.value) {
            valido = false; mensaje = 'Selecciona un producto en todas las filas.'; return;
        }

        var cantidadTexto = cantidadInput ? cantidadInput.value.trim() : '';
        if (!/^\d+$/.test(cantidadTexto) || Number(cantidadTexto) <= 0) {
            valido = false; mensaje = 'Todas las cantidades deben ser números enteros mayores que cero.'; return;
        }

        var precioTexto = precioInput ? precioInput.value.trim() : '';
        if (precioTexto === '' || isNaN(precioTexto) || Number(precioTexto) <= 0) {
            valido = false; mensaje = 'Debes escribir el precio de venta para cada producto.'; return;
        }

        var descuentoTexto = descuentoInput ? descuentoInput.value.trim() : '0';
        if (descuentoTexto !== '' && (isNaN(descuentoTexto) || Number(descuentoTexto) < 0 || Number(descuentoTexto) > 100)) {
            valido = false; mensaje = 'El descuento debe ser un número entre 0 y 100.'; return;
        }

        var unidadValor = unidadSelect ? unidadSelect.value : '';
        if (unidadValor === '') {
            valido = false; mensaje = 'Debes seleccionar la unidad de venta para cada producto.'; return;
        }

        var cpuTexto = cpuInput ? cpuInput.value.trim() : '';
        if (!/^\d+$/.test(cpuTexto) || Number(cpuTexto) <= 0) {
            valido = false; mensaje = 'El contenido por unidad debe ser un número entero mayor que cero.'; return;
        }

        var unidadContenidoValor = unidadContenidoSelect ? unidadContenidoSelect.value : '';
        if (unidadContenidoValor === '') {
            valido = false; mensaje = 'Debes seleccionar la unidad de contenido (ej. kilogramo, libra) para cada producto.';
        }
    });

    if (!valido) {
        event.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Revisa la venta', text: mensaje, confirmButtonColor: '#00875F' });
    }
});
</script>

@endsection