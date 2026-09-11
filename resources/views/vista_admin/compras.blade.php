
@extends('layouts.dashboard')
@php
    $titulo = 'Panel de compras - Administrador';
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
.btn-primario:hover {
    background:#01614B;
    transform:translateY(-2px);
}
.btn-peligro {
    background:#E53935;
    color:#fff;
    border-radius:10px;
    border:none;
    font-weight:600;
    font-family:'Outfit',sans-serif;
    cursor:pointer;
    transition:background .18s;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    text-decoration:none;
}
.btn-peligro:hover {
    background:#c62828;
}
.campo-input {
    width:100%;
    background:#fff;
    border:1.5px solid #E5E7EB;
    border-radius:10px;
    color:#171717;
    font-family:'Outfit',sans-serif;
    font-size:.9rem;
    outline:none;
    padding:9px 12px;
    transition: border-color .2s, box-shadow .2s;
}
.campo-input:focus {
    border-color:#61D0A7;
    box-shadow:0 0 0 4px rgba(97,208,167,.15);
}
@keyframes modalRise {
    from { opacity:0; transform:translateY(18px) scale(.98); }
    to { opacity:1; transform:translateY(0) scale(1); }
}
.modal-anim {
    animation: modalRise .28s cubic-bezier(.22,1,.36,1) forwards;
}
.kpi-card {
    background:#fff;
    border:1px solid #E5E7EB;
    border-radius:14px;
    padding:18px;
    transition: transform .18s, box-shadow .18s;
}
.kpi-card:hover {
    transform:translateY(-3px);
    box-shadow:0 8px 24px rgba(0,135,95,.10);
}
.item-row {
    background:#F8F8F8;
    border:1.5px solid #E5E7EB;
    border-radius:12px;
    padding:16px;
    display:flex;
    flex-direction:column;
    gap:14px;
}
.item-row-seccion {
    font-size:.62rem;
    color:#9CA3AF;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.04em;
    margin-bottom:6px;
}
.item-row-fila1 {
    display:grid;
    grid-template-columns: 2fr 1fr;
    gap:12px;
    margin-bottom:14px;
}
.item-row-fila2 {
    display:grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap:12px;
    margin-bottom:14px;
}
.item-row-fila3 {
    display:grid;
    grid-template-columns: 1fr 1fr 30px;
    gap:12px;
    align-items:end;
}
.item-row-label {
    display:block;
    font-size:.78rem;
    font-weight:700;
    color:#171717;
    margin-bottom:4px;
}
.item-row-ayuda {
    font-size:.68rem;
    color:#9CA3AF;
    margin-top:4px;
    line-height:1.3;
}
.paginacion {
    padding:14px 20px;
    border-top:1px solid #E5E7EB;
    display:flex;
    justify-content:center;
    gap:6px;
}
.pag {
    width:36px;
    height:36px;
    border:1px solid #E5E7EB;
    border-radius:9px;
    background:#fff;
    color:#5F6673;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
}
.pag:hover, .pag.activa {
    background:#00875F;
    color:#fff;
}
.pag.deshabilitado {
    opacity:.4;
    pointer-events:none;
}
@media (max-width:900px) {
    .item-row-fila1, .item-row-fila2, .item-row-fila3 {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="max-w-7xl mx-auto font-sans-ventanet">
    <!-- ENCABEZADO -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-7 gap-4">
        <div>
            <h2 class="text-3xl font-bold font-serif-ventanet" style="color:#01614B;">
                Gestionar Compras
            </h2>
            <p class="text-sm mt-1" style="color:#5F6673;">
                Registra las compras realizadas a tus proveedores y actualiza el inventario.
            </p>
        </div>
        <button type="button" onclick="abrirModalNuevaCompra()" class="btn-primario px-5 py-2.5">
            <i class="fas fa-shopping-bag"></i>
            Nueva Compra
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
                customClass: {
                    popup: 'rounded-[20px]',
                    confirmButton: 'rounded-lg px-6 py-2.5'
                }
            });
        });
        </script>
    @endif

    <!-- KPIs -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
        <div class="kpi-card">
            <div class="flex items-center gap-3 mb-3">
                <div style="width:40px;height:40px;background:#DDF5EC;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-shopping-bag" style="color:#00875F;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;">
                    Total compras
                </span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:#171717;line-height:1;">
                {{ (int) ($resumen['total_compras'] ?? 0) }}
            </p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:3px;">registradas</p>
        </div>
        <div class="kpi-card">
            <div class="flex items-center gap-3 mb-3">
                <div style="width:40px;height:40px;background:#DDF5EC;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-dollar-sign" style="color:#00875F;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;">
                    Gasto total
                </span>
            </div>
            <p style="font-size:1.4rem;font-weight:800;color:#171717;line-height:1;">
                {{ number_format($resumen['gasto_total'] ?? 0, 0, ',', '.') }}
            </p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:3px;">acumulado</p>
        </div>
        <div class="kpi-card">
            <div class="flex items-center gap-3 mb-3">
                <div style="width:40px;height:40px;background:#fffbeb;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-calendar-day" style="color:#FFB51B;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;">
                    Compras hoy
                </span>
            </div>
            <p style="font-size:1.8rem;font-weight:800;color:#171717;line-height:1;">
                {{ (int) ($resumen['compras_hoy'] ?? 0) }}
            </p>
            <p style="font-size:.72rem;color:#9CA3AF;margin-top:3px;">del día</p>
        </div>
        <div class="kpi-card">
            <div class="flex items-center gap-3 mb-3">
                <div style="width:40px;height:40px;background:#fffbeb;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-coins" style="color:#FFB51B;"></i>
                </div>
                <span style="font-size:.72rem;font-weight:700;color:#5F6673;text-transform:uppercase;">
                    Gasto hoy
                </span>
            </div>
            <p style="font-size:1.4rem;font-weight:800;color:#171717;line-height:1;">
                {{ number_format($resumen['gasto_hoy'] ?? 0, 0, ',', '.') }}
            </p>
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
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide" style="color:#fff;">Proveedor</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide" style="color:#fff;">Registrado por</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-right" style="color:#fff;">Total</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-center" style="color:#fff;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($compras as $c)
                        <tr
                            style="border-bottom:1px solid #E5E7EB; transition:background .15s;"
                            onmouseover="this.style.background='#F8F8F8'"
                            onmouseout="this.style.background=''"
                        >
                            <td class="px-5 py-3.5 text-sm font-medium" style="color:#171717;">
                                {{ !empty($c['fecha']) ? \Illuminate\Support\Carbon::parse($c['fecha'])->format('d/m/Y') : '---' }}
                            </td>
                            <td class="px-5 py-3.5 text-sm font-bold" style="color:#171717;">
                                {{ $c['proveedor'] ?? 'Sin proveedor' }}
                            </td>
                            <td class="px-5 py-3.5 text-sm" style="color:#5F6673;">
                                {{ $c['comprador'] ?? '---' }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span style="font-size:1rem;font-weight:800;color:#00875F;">
                                    {{ number_format($c['total'] ?? 0, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div style="display:flex;align-items:center;justify-content:center;gap:8px;">
                                    <button
                                        type="button"
                                        onclick="verDetalle({{ (int) $c['id_compra'] }})"
                                        title="Ver detalle"
                                        style="width:32px;height:32px;border-radius:8px;border:1px solid #E5E7EB;background:#fff;color:#00875F;cursor:pointer;display:flex;align-items:center;justify-content:center;"
                                    >
                                        <i class="fas fa-eye" style="font-size:.75rem;"></i>
                                    </button>
                                    <button
                                        type="button"
                                        onclick="abrirModalEliminar({{ (int) $c['id_compra'] }})"
                                        title="Eliminar"
                                        style="width:32px;height:32px;border-radius:8px;border:1px solid #fde8e8;background:#fff;color:#E53935;cursor:pointer;display:flex;align-items:center;justify-content:center;"
                                    >
                                        <i class="fas fa-trash" style="font-size:.75rem;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:48px;text-align:center;">
                                <div style="width:56px;height:56px;background:#F8F8F8;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;border:1px solid #E5E7EB;">
                                    <i class="fas fa-shopping-bag" style="color:#9CA3AF;font-size:1.3rem;"></i>
                                </div>
                                <p style="color:#5F6673;font-weight:600;">
                                    No hay compras registradas.
                                </p>
                                <p style="color:#9CA3AF;font-size:.85rem;margin-top:4px;">
                                    Registra tu primera compra usando el botón superior.
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
                <a class="pag {{ $pagina <= 1 ? 'deshabilitado' : '' }}"
                   href="{{ route('admin.compras', ['pagina' => max(1, $pagina - 1)]) }}">«</a>

                @for ($i = 1; $i <= $paginas; $i++)
                    <a class="pag {{ $i == $pagina ? 'activa' : '' }}"
                       href="{{ route('admin.compras', ['pagina' => $i]) }}">
                        {{ $i }}
                    </a>
                @endfor

                <a class="pag {{ $pagina >= $paginas ? 'deshabilitado' : '' }}"
                   href="{{ route('admin.compras', ['pagina' => min($paginas, $pagina + 1)]) }}">»</a>
            </nav>
        @endif
    </div>
</div>

<!-- ============================================================
     MODAL NUEVA COMPRA
============================================================ -->
<div id="modalCrear" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.50);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl w-full shadow-2xl modal-anim overflow-hidden" style="max-width:1080px;max-height:92vh;display:flex;flex-direction:column;">

        <!-- CABECERA -->
        <div style="background:#F8F8F8;border-bottom:1px solid #E5E7EB;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;background:#00875F;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-shopping-bag" style="color:#fff;font-size:.9rem;"></i>
                </div>
                <div>
                    <h3 class="font-serif-ventanet" style="font-size:1.1rem;color:#171717;">
                        Nueva Compra
                    </h3>
                    <p style="font-size:.75rem;color:#5F6673;margin-top:2px;">
                        Selecciona un proveedor activo, luego el producto, y escribe el precio y la cantidad, y selecciona la unidad que negociaste.
                    </p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalCrear()" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:1.2rem;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- FORMULARIO -->
        <form
            id="formCompra"
            action="{{ route('admin.compras.store') }}"
            method="POST"
            style="padding:24px;display:flex;flex-direction:column;gap:18px;overflow-y:auto;flex:1;"
        >
            @csrf

            <!-- PROVEEDOR -->
            <div>
                <label style="display:block;font-size:.83rem;font-weight:700;color:#171717;margin-bottom:5px;">
                    Proveedor *
                </label>
                @if (empty($proveedores))
                    <div style="background:#fffbeb;border:1px solid #FFB51B;border-radius:10px;padding:12px 14px;color:#92400E;font-size:.85rem;">
                        <i class="fas fa-exclamation-triangle" style="color:#FFB51B;margin-right:6px;"></i>
                        No hay proveedores activos disponibles. Registra o activa un proveedor primero.
                    </div>
                @else
                    <div style="position:relative;">
                        <select
                            id="selectProveedor"
                            name="id_proveedor"
                            required
                            class="campo-input"
                            style="appearance:none;cursor:pointer;padding-right:36px;"
                            onchange="filtrarProductosPorProveedor()"
                        >
                            <option value="">Selecciona un proveedor</option>
                            @foreach ($proveedores as $pv)
                                <option value="{{ (int) $pv['id_proveedor'] }}">
                                    {{ $pv['nombre'] ?? 'Proveedor' }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#00875F;font-size:.75rem;pointer-events:none;"></i>
                    </div>
                @endif
            </div>

            <!-- PRODUCTOS -->
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <div>
                        <label style="font-size:.83rem;font-weight:700;color:#171717;">
                            Productos *
                        </label>
                        <p style="font-size:.72rem;color:#9CA3AF;margin-top:2px;">
                            El precio, la unidad de compra, el contenido y su unidad los defines tú según lo que negociaste.
                        </p>
                    </div>
                    <button
                        type="button"
                        onclick="agregarFila()"
                        style="background:#DDF5EC;color:#00875F;border:1px solid #61D0A7;border-radius:8px;padding:6px 12px;font-size:.8rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:5px;"
                    >
                        <i class="fas fa-plus" style="font-size:.7rem;"></i>
                        Agregar producto
                    </button>
                </div>
                <div id="filasProductos" style="display:flex;flex-direction:column;gap:12px;"></div>
            </div>

            <!-- TOTAL -->
            <div style="display:flex;align-items:center;justify-content:space-between;background:#DDF5EC;border:1px solid #61D0A7;border-radius:12px;padding:13px 18px;">
                <span style="font-size:.82rem;font-weight:700;color:#01614B;text-transform:uppercase;letter-spacing:.04em;">
                    Total de la compra
                </span>
                <span id="totalCompra" style="font-size:1.5rem;font-weight:800;color:#00875F;">
                    $0
                </span>
            </div>

            <!-- BOTONES -->
            <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:12px;border-top:1px solid #E5E7EB;">
                <button type="button" onclick="cerrarModalCrear()" style="padding:9px 20px;border-radius:9px;border:1px solid #E5E7EB;background:#fff;color:#5F6673;font-weight:600;cursor:pointer;">
                    Cancelar
                </button>
                <button type="submit" class="btn-primario" style="padding:9px 24px;">
                    <i class="fas fa-check"></i>
                    Confirmar Compra
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
     MODAL DETALLE
============================================================ -->
<div id="modalDetalle" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.50);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl w-full shadow-2xl modal-anim overflow-hidden" style="max-width:760px;">
        <div style="background:#F8F8F8;border-bottom:1px solid #E5E7EB;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;background:#00875F;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-receipt" style="color:#fff;font-size:.9rem;"></i>
                </div>
                <div>
                    <h3 class="font-serif-ventanet" style="font-size:1.1rem;color:#171717;">
                        Detalle de Compra
                    </h3>
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
     MODAL ELIMINAR
============================================================ -->
<div id="modalEliminar" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.55);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl w-full shadow-2xl modal-anim overflow-hidden" style="max-width:420px;">
        <div style="padding:36px 32px;text-align:center;">
            <div style="width:68px;height:68px;background:#fde8e8;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="fas fa-exclamation-triangle" style="color:#E53935;font-size:1.7rem;"></i>
            </div>
            <h3 class="font-serif-ventanet" style="font-size:1.3rem;color:#171717;margin-bottom:8px;">
                Eliminar Compra
            </h3>
            <p style="color:#5F6673;font-size:.9rem;">
                ¿Estás seguro de que deseas eliminar esta compra?<br>
                El inventario que se agregó se revertirá. Esta acción no se puede deshacer.
            </p>
        </div>
        <div style="background:#F8F8F8;border-top:1px solid #E5E7EB;padding:16px 24px;display:flex;justify-content:center;gap:10px;">
            <button type="button" onclick="cerrarModal('modalEliminar')" style="padding:9px 20px;border-radius:9px;border:1px solid #E5E7EB;background:#fff;color:#5F6673;font-weight:600;cursor:pointer;">
                Cancelar
            </button>
            <button type="button" onclick="confirmarEliminar()" class="btn-peligro" style="padding:9px 24px;">
                Sí, eliminar
            </button>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar (Laravel requiere DELETE, no GET) -->
<form id="formEliminar" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

<script>
// ============================================================
// DATOS DESDE PHP (vía Blade)
// ============================================================
var productos = @json($productos);
var unidades = @json($unidades);

var baseCompras = "{{ url('/admin/compras') }}";

var filaCount = 0;

// ============================================================
// GENERAR OPCIONES DE UNIDAD (catálogo `units`)
// ============================================================
function generarOpcionesUnidad(placeholder) {
    var texto = placeholder || 'Unidad';
    var html = '<option value="">' + escapeHtml(texto) + '</option>';
    unidades.forEach(function (u) {
        html += '<option value="' + u.id_unidad + '">' +
            escapeHtml(u.nombre || 'Unidad') + '</option>';
    });
    return html;
}

// ============================================================
// MODALES
// ============================================================
function abrirModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.remove('hidden');
}

function cerrarModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

function abrirModalNuevaCompra() {
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
    var proveedor = document.getElementById('selectProveedor');
    if (proveedor) proveedor.value = '';
    var total = document.getElementById('totalCompra');
    if (total) total.textContent = '$0';
    filaCount = 0;
}

// ============================================================
// ELIMINAR
// ============================================================
var compraEliminar = null;

function abrirModalEliminar(id) {
    compraEliminar = id;
    abrirModal('modalEliminar');
}

function confirmarEliminar() {
    if (!compraEliminar) return;

    var form = document.getElementById('formEliminar');
    form.action = baseCompras + '/' + compraEliminar;
    form.submit();
}

// ============================================================
// FILTRAR PRODUCTOS POR PROVEEDOR
// ============================================================
function filtrarProductosPorProveedor() {
    var selectProveedor = document.getElementById('selectProveedor');
    var idProveedor = parseInt(selectProveedor.value) || 0;

    document.querySelectorAll('.select-producto').forEach(function (select) {
        var idx = select.dataset.idx;
        reconstruirOpcionesProducto(select, idProveedor, idx);
    });

    recalcularTotal();
}

// ============================================================
// RECONSTRUIR OPCIONES DE PRODUCTO
// ============================================================
function reconstruirOpcionesProducto(selectEl, idProveedor, idx) {
    if (!selectEl) return;

    var valorActual = selectEl.value;
    selectEl.innerHTML = '<option value="">Seleccionar producto</option>';

    productos.forEach(function (p) {
        var option = document.createElement('option');
        option.value = p.id_producto;
        option.dataset.categoria = p.categoria || '';

        var texto = p.producto || 'Producto';
        if (p.categoria) texto += ' — ' + p.categoria;
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
    var proveedor = document.getElementById('selectProveedor');
    var idProveedor = proveedor ? parseInt(proveedor.value) || 0 : 0;

    if (idProveedor <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Selecciona un proveedor',
            text: 'Primero debes seleccionar un proveedor activo.',
            confirmButtonColor: '#00875F'
        });
        return;
    }

    var container = document.getElementById('filasProductos');
    var idx = filaCount++;
    var div = document.createElement('div');
    div.className = 'item-row';
    div.id = 'fila-' + idx;

    div.innerHTML =
        // ---------- SECCIÓN 1: QUÉ COMPRAS ----------
        '<div>' +
            '<p class="item-row-seccion">Qué compras</p>' +
            '<div class="item-row-fila1">' +

                '<div>' +
                    '<span class="item-row-label">Producto</span>' +
                    '<select class="campo-input select-producto" name="id_producto[]" data-idx="' + idx + '" required ' +
                        'onchange="onProductoChange(' + idx + ')">' +
                        '<option value="">Seleccionar producto</option>' +
                    '</select>' +
                    '<p class="item-row-ayuda">El producto que estás comprando al proveedor.</p>' +
                '</div>' +

                '<div>' +
                    '<span class="item-row-label">Cantidad</span>' +
                    '<input type="number" name="cantidad[]" id="cant-' + idx + '" min="1" step="1" value="1" required ' +
                        'oninput="calcularFila(' + idx + ')" class="campo-input" style="text-align:center;">' +
                    '<p class="item-row-ayuda">Cuántas unidades de compra vas a llevar.</p>' +
                '</div>' +

            '</div>' +
        '</div>' +

        // ---------- SECCIÓN 2: CÓMO LO EMPAQUETA EL PROVEEDOR ----------
        '<div>' +
            '<p class="item-row-seccion">Cómo lo empaqueta el proveedor</p>' +
            '<div class="item-row-fila2">' +

                '<div>' +
                    '<span class="item-row-label">Unidad de compra</span>' +
                    '<select class="campo-input" name="id_unidad[]" id="unidad-' + idx + '" required>' +
                        generarOpcionesUnidad('Unidad') +
                    '</select>' +
                    '<p class="item-row-ayuda">La presentación en que te vende: caja, bulto, paquete.</p>' +
                '</div>' +

                '<div>' +
                    '<span class="item-row-label">Contenido</span>' +
                    '<input type="number" name="cantidad_por_unidad[]" id="cpu-' + idx + '" min="1" step="1" value="1" required ' +
                        'oninput="calcularFila(' + idx + ')" class="campo-input" style="text-align:center;">' +
                    '<p class="item-row-ayuda">Cuántas piezas trae cada presentación.</p>' +
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

        // ---------- SECCIÓN 3: PRECIO ----------
        '<div>' +
            '<p class="item-row-seccion">Precio</p>' +
            '<div class="item-row-fila3">' +

                '<div>' +
                    '<span class="item-row-label">Precio compra</span>' +
                    '<input type="number" name="precio_compra[]" id="precio-' + idx + '" min="0.01" step="0.01" required ' +
                        'oninput="calcularFila(' + idx + ')" class="campo-input" placeholder="0">' +
                    '<p class="item-row-ayuda">Lo que pagas por cada unidad de compra.</p>' +
                '</div>' +

                '<div>' +
                    '<span class="item-row-label">Subtotal</span>' +
                    '<div style="position:relative;">' +
                        '<span style="position:absolute;left:8px;top:50%;transform:translateY(-50%);color:#9CA3AF;font-size:.8rem;">$</span>' +
                        '<input type="text" id="subvisual-' + idx + '" value="0" readonly class="campo-input" ' +
                            'style="padding-left:22px;text-align:right;background:#fff;font-weight:700;color:#00875F;cursor:not-allowed;">' +
                    '</div>' +
                    '<p class="item-row-ayuda">Cantidad × precio de compra.</p>' +
                '</div>' +

                '<button type="button" onclick="quitarFila(' + idx + ')" title="Quitar producto" ' +
                    'style="width:26px;height:26px;border-radius:6px;background:#fde8e8;border:none;color:#E53935;cursor:pointer;display:flex;align-items:center;justify-content:center;margin-bottom:4px;">' +
                    '<i class="fas fa-times" style="font-size:.65rem;"></i>' +
                '</button>' +

            '</div>' +
        '</div>';

    container.appendChild(div);

    var select = div.querySelector('.select-producto');
    reconstruirOpcionesProducto(select, idProveedor, idx);
}

// ============================================================
// QUITAR FILA
// ============================================================
function quitarFila(idx) {
    var el = document.getElementById('fila-' + idx);
    if (el) {
        el.remove();
        recalcularTotal();
    }
}

// ============================================================
// CAMBIO DE PRODUCTO
// ============================================================
function onProductoChange(idx) {
    calcularFila(idx);
}

// ============================================================
// CALCULAR SUBTOTAL DE LA FILA
// ============================================================
function calcularFila(idx) {
    var cantidadInput = document.getElementById('cant-' + idx);
    var precioInput = document.getElementById('precio-' + idx);
    var subtotalVisual = document.getElementById('subvisual-' + idx);

    var cantidadTexto = cantidadInput ? cantidadInput.value.trim() : '';
    var cantidad = /^\d+$/.test(cantidadTexto) ? Number(cantidadTexto) : 0;

    if (cantidadInput && cantidadInput.value !== '' && !Number.isInteger(parseFloat(cantidadInput.value))) {
        cantidadInput.value = cantidad;
    }

    var precioCompra = precioInput ? (parseFloat(precioInput.value) || 0) : 0;
    var subtotal = cantidad * precioCompra;

    if (subtotalVisual) {
        subtotalVisual.value = subtotal.toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    recalcularTotal();
}

// ============================================================
// RECALCULAR TOTAL
// ============================================================
function recalcularTotal() {
    var total = 0;

    document.querySelectorAll('#filasProductos .item-row').forEach(function (fila) {
        var idx = fila.id.replace('fila-', '');
        var cantidadInput = document.getElementById('cant-' + idx);
        var precioInput = document.getElementById('precio-' + idx);

        var cantidadTexto = cantidadInput ? cantidadInput.value.trim() : '';
        var cantidad = /^\d+$/.test(cantidadTexto) ? Number(cantidadTexto) : 0;
        var precioCompra = precioInput ? (parseFloat(precioInput.value) || 0) : 0;

        total += cantidad * precioCompra;
    });

    var totalElemento = document.getElementById('totalCompra');
    if (totalElemento) {
        totalElemento.textContent =
            '$' + total.toLocaleString('es-CO', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
    }
}

// ============================================================
// VER DETALLE
// ============================================================
function verDetalle(id) {
    document.getElementById('detalleEncabezado').textContent = 'Compra #' + id;
    document.getElementById('detalleCuerpo').innerHTML =
        '<div style="text-align:center;padding:32px 0;">' +
            '<i class="fas fa-spinner fa-spin" style="color:#00875F;font-size:1.5rem;"></i>' +
        '</div>';

    abrirModal('modalDetalle');

    fetch(baseCompras + '/' + encodeURIComponent(id) + '/detalle')
        .then(function (response) {
            if (!response.ok) throw new Error('Error HTTP');
            return response.json();
        })
        .then(function (data) {
            if (!Array.isArray(data) || data.length === 0) {
                document.getElementById('detalleCuerpo').innerHTML =
                    '<p style="text-align:center;color:#9CA3AF;padding:24px 0;">' +
                    'Sin productos registrados en esta compra.</p>';
                return;
            }

            var html =
                '<table style="width:100%;border-collapse:collapse;">' +
                    '<thead>' +
                        '<tr style="background:#F8F8F8;">' +
                            '<th style="padding:8px 10px;text-align:left;font-size:.75rem;color:#5F6673;text-transform:uppercase;">Producto</th>' +
                            '<th style="padding:8px 10px;text-align:left;font-size:.75rem;color:#5F6673;text-transform:uppercase;">Unidad compra</th>' +
                            '<th style="padding:8px 10px;text-align:left;font-size:.75rem;color:#5F6673;text-transform:uppercase;">Contenido</th>' +
                            '<th style="padding:8px 10px;text-align:center;font-size:.75rem;color:#5F6673;text-transform:uppercase;">Cant.</th>' +
                            '<th style="padding:8px 10px;text-align:right;font-size:.75rem;color:#5F6673;text-transform:uppercase;">P. Compra</th>' +
                            '<th style="padding:8px 10px;text-align:right;font-size:.75rem;color:#5F6673;text-transform:uppercase;">Subtotal</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody>';

            var totalD = 0;

            data.forEach(function (d) {
                totalD += parseFloat(d.subtotal) || 0;

                var contenidoTexto = '---';
                if (d.cantidad_por_unidad !== undefined && d.cantidad_por_unidad !== null) {
                    contenidoTexto = d.cantidad_por_unidad;
                    if (d.unidad_contenido) {
                        contenidoTexto += ' ' + escapeHtml(d.unidad_contenido);
                    }
                }

                html +=
                    '<tr style="border-bottom:1px solid #E5E7EB;">' +
                        '<td style="padding:9px 10px;font-weight:700;color:#171717;font-size:.85rem;">' +
                            escapeHtml(d.producto || '') +
                        '</td>' +
                        '<td style="padding:9px 10px;color:#5F6673;font-size:.82rem;">' +
                            escapeHtml(d.unidad_compra || '---') +
                        '</td>' +
                        '<td style="padding:9px 10px;color:#5F6673;font-size:.82rem;">' +
                            contenidoTexto +
                        '</td>' +
                        '<td style="padding:9px 10px;text-align:center;color:#171717;font-size:.85rem;">' +
                            d.cantidad +
                        '</td>' +
                        '<td style="padding:9px 10px;text-align:right;color:#5F6673;font-size:.82rem;">$' +
                            Number(d.precio_unitario).toLocaleString('es-CO') +
                        '</td>' +
                        '<td style="padding:9px 10px;text-align:right;font-weight:700;color:#00875F;font-size:.9rem;">$' +
                            Number(d.subtotal).toLocaleString('es-CO') +
                        '</td>' +
                    '</tr>';
            });

            html +=
                '</tbody></table>' +
                '<div style="display:flex;justify-content:space-between;align-items:center;padding:12px 10px;margin-top:8px;border-top:2px solid #DDF5EC;">' +
                    '<span style="font-weight:700;color:#01614B;font-size:.82rem;text-transform:uppercase;">TOTAL</span>' +
                    '<span style="font-weight:800;color:#00875F;font-size:1.2rem;">$' +
                        totalD.toLocaleString('es-CO') +
                    '</span>' +
                '</div>';

            document.getElementById('detalleCuerpo').innerHTML = html;
        })
        .catch(function (error) {
            console.error(error);
            document.getElementById('detalleCuerpo').innerHTML =
                '<p style="text-align:center;color:#E53935;padding:24px 0;">' +
                'Error al cargar el detalle de la compra.</p>';
        });
}

// ============================================================
// PROTEGER HTML
// ============================================================
function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================================
// CERRAR MODALES AL HACER CLIC AFUERA
// ============================================================
document.addEventListener('click', function (event) {
    if (event.target.id === 'modalCrear') cerrarModalCrear();
    if (event.target.id === 'modalDetalle') cerrarModal('modalDetalle');
    if (event.target.id === 'modalEliminar') cerrarModal('modalEliminar');
});

// ============================================================
// VALIDACIÓN DEL FORMULARIO
// ============================================================
document.getElementById('formCompra')?.addEventListener('submit', function (event) {
    var proveedor = document.getElementById('selectProveedor');
    if (!proveedor || !proveedor.value) {
        event.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Proveedor requerido',
            text: 'Debes seleccionar un proveedor activo.',
            confirmButtonColor: '#00875F'
        });
        return;
    }

    var filas = document.querySelectorAll('#filasProductos .item-row');
    if (filas.length === 0) {
        event.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Sin productos',
            text: 'Debes agregar al menos un producto.',
            confirmButtonColor: '#00875F'
        });
        return;
    }

    var valido = true;
    var mensaje = '';

    filas.forEach(function (fila) {
        var idx = fila.id.replace('fila-', '');
        var select = fila.querySelector('.select-producto');
        var cantidadInput = document.getElementById('cant-' + idx);
        var precioInput = document.getElementById('precio-' + idx);
        var unidadSelect = document.getElementById('unidad-' + idx);
        var cpuInput = document.getElementById('cpu-' + idx);
        var unidadContenidoSelect = document.getElementById('unidadcontenido-' + idx);

        if (!select || !select.value) {
            valido = false;
            mensaje = 'Selecciona un producto en todas las filas.';
            return;
        }

        var cantidadTexto = cantidadInput ? cantidadInput.value.trim() : '';
        if (!/^\d+$/.test(cantidadTexto) || Number(cantidadTexto) <= 0) {
            valido = false;
            mensaje = 'Todas las cantidades deben ser números enteros mayores que cero.';
            return;
        }

        var precioTexto = precioInput ? precioInput.value.trim() : '';
        if (precioTexto === '' || isNaN(precioTexto) || Number(precioTexto) <= 0) {
            valido = false;
            mensaje = 'Debes escribir el precio de compra negociado para cada producto.';
            return;
        }

        var unidadValor = unidadSelect ? unidadSelect.value : '';
        if (unidadValor === '') {
            valido = false;
            mensaje = 'Debes seleccionar la unidad de compra (bulto, caja, etc) para cada producto.';
            return;
        }

        var cpuTexto = cpuInput ? cpuInput.value.trim() : '';
        if (!/^\d+$/.test(cpuTexto) || Number(cpuTexto) <= 0) {
            valido = false;
            mensaje = 'El contenido por unidad debe ser un número entero mayor que cero.';
            return;
        }

        var unidadContenidoValor = unidadContenidoSelect ? unidadContenidoSelect.value : '';
        if (unidadContenidoValor === '') {
            valido = false;
            mensaje = 'Debes seleccionar la unidad de contenido (ej. kilogramo, libra) para cada producto.';
        }
    });

    if (!valido) {
        event.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Revisa la compra',
            text: mensaje,
            confirmButtonColor: '#00875F'
        });
    }
});
</script>

@endsection