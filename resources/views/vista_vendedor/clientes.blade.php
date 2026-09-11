@extends('layouts.dashboard')

@php
    $titulo = 'Gestionar Clientes - Vendedor';
@endphp

@section('content')

<style>
.btn-primario,.btn-eliminar,.btn-cancelar,.btn-accion,.pag{
    border-radius:10px;padding:10px 20px;font-weight:600;cursor:pointer
}
.btn-primario{background:#00875F;color:#fff;border:0}
.btn-primario:hover{background:#01614B}
.btn-eliminar{background:#E53935;color:#fff;border:0}
.btn-accion{width:34px;height:34px;padding:0;background:#fff;display:inline-flex;align-items:center;justify-content:center}
.campo-input{width:100%;padding:10px 14px;background:#fff;border:1.5px solid #E5E7EB;border-radius:10px;box-sizing:border-box}
.campo-input:focus{border-color:#61D0A7;outline:none}
.paginacion{padding:14px 20px;border-top:1px solid #E5E7EB;display:flex;justify-content:center;gap:6px}
.pag{width:36px;height:36px;padding:0;border:1px solid #E5E7EB;background:#fff;color:#5F6673;display:flex;align-items:center;justify-content:center;text-decoration:none}
.pag.activa{background:#00875F;color:#fff;border-color:#00875F}
.pag.deshabilitado{opacity:.4;pointer-events:none}
.modal-fondo{position:fixed;inset:0;background:#0007;backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;padding:20px;z-index:9999}
.modal-fondo.oculto{display:none}
.modal-caja{background:#fff;width:100%;max-width:600px;border-radius:18px;overflow:hidden}
.modal-caja.pequeno{max-width:430px}
.modal-header{padding:18px 24px;border-bottom:1px solid #E5E7EB;display:flex;justify-content:space-between}
.modal-header h3{margin:0;color:#01614B}
.btn-cerrar{background:none;border:0;cursor:pointer;font-size:20px}
.modal-body{padding:24px}
.modal-footer{padding:16px 24px;border-top:1px solid #E5E7EB;display:flex;justify-content:flex-end;gap:10px}
.btn-cancelar{background:#fff;border:1px solid #D1D5DB}
</style>

<div class="max-w-7xl mx-auto">

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h2 class="text-3xl font-bold font-serif-ventanet" style="color:#01614B">
                Mis Clientes
            </h2>
            <p class="text-sm mt-1" style="color:#5F6673">
                Administra los clientes que tú has registrado.
            </p>
        </div>

        <button type="button" class="btn-primario" onclick="abrirModal('modalCrear')">
            <i class="fas fa-user-plus"></i> Nuevo Cliente
        </button>
    </div>

    @if (session('alert'))
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: @json(session('alert')['icon'] ?? 'info'),
                title: @json(session('alert')['title'] ?? 'Aviso'),
                text: @json(session('alert')['text'] ?? ''),
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#00875F'
            });
        });
        </script>
    @endif

    <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#E5E7EB">
        <div class="overflow-x-auto">

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr style="background:#01614B">
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white">Nombre Completo</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white">Correo</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white">Teléfono</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white text-center">Estado</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white text-center">Fecha registro</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($clientes as $c)
                        @php
                            $id     = (int) ($c['id_cliente'] ?? 0);
                            $nombre = $c['nombre'] ?? '';
                            $activo = ($c['estado'] ?? '') === 'activo';
                            $fila   = $activo ? '#fff' : '#FDECEC';
                            $hover  = $activo ? '#F8F8F8' : '#FDE0E0';
                        @endphp

                        <tr
                            style="border-bottom:1px solid #E5E7EB;background:{{ $fila }}"
                            onmouseover="this.style.background='{{ $hover }}'"
                            onmouseout="this.style.background='{{ $fila }}'"
                        >
                            <td class="px-5 py-3.5 font-bold text-sm">
                                {{ $nombre }}
                            </td>

                            <td class="px-5 py-3.5 text-sm" style="color:#5F6673">
                                @if (!empty($c['correo']))
                                    {{ $c['correo'] }}
                                @else
                                    <span style="color:#9CA3AF;font-style:italic">Sin correo</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-sm" style="color:#5F6673">
                                @if (!empty($c['telefono']))
                                    {{ $c['telefono'] }}
                                @else
                                    <span style="color:#9CA3AF;font-style:italic">Sin teléfono</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-center">
                                @if ($activo)
                                    <span style="background:#DDF5EC;color:#00875F;border:1px solid #61D0A7;padding:3px 12px;border-radius:999px;font-size:.75rem;font-weight:700">
                                        Activo
                                    </span>
                                @else
                                    <span style="background:#FDE8E8;color:#E53935;border:1px solid #E53935;padding:3px 12px;border-radius:999px;font-size:.75rem;font-weight:700">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-center text-sm" style="color:#5F6673">
                                {{ !empty($c['fecha_registro']) ? \Carbon\Carbon::parse($c['fecha_registro'])->format('d/m/Y') : '—' }}
                            </td>

                            <td class="px-5 py-3.5">
                                <div style="display:flex;justify-content:center;gap:8px">

                                    <button
                                        type="button"
                                        class="btn-accion"
                                        title="Editar"
                                        onclick='abrirModalEditar(@json($c))'
                                        style="color:#00875F;border:1px solid #61D0A7"
                                    >
                                        <i class="fas fa-pen"></i>
                                    </button>

                                    <button
                                        type="button"
                                        class="btn-accion"
                                        title="{{ $activo ? 'Desactivar' : 'Activar' }}"
                                        onclick="cambiarEstadoCliente({{ $id }})"
                                        style="color:{{ $activo ? '#FFB51B' : '#00875F' }};border:1px solid {{ $activo ? '#FFB51B' : '#61D0A7' }}"
                                    >
                                        <i class="fas {{ $activo ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>

                                    <button
                                        type="button"
                                        class="btn-accion"
                                        title="Eliminar"
                                        onclick="abrirModalEliminar({{ $id }}, {{ Js::from($nombre) }})"
                                        style="color:#E53935;border:1px solid #E53935"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:48px;text-align:center;color:#5F6673;font-weight:600">
                                Aún no has registrado clientes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        @if ($total > 5)
            <nav class="paginacion">
                <a class="pag {{ $pagina <= 1 ? 'deshabilitado' : '' }}" href="?pagina={{ max(1, $pagina - 1) }}">«</a>
                @for ($i = 1; $i <= $paginas; $i++)
                    <a class="pag {{ $i === $pagina ? 'activa' : '' }}" href="?pagina={{ $i }}">{{ $i }}</a>
                @endfor
                <a class="pag {{ $pagina >= $paginas ? 'deshabilitado' : '' }}" href="?pagina={{ min($paginas, $pagina + 1) }}">»</a>
            </nav>
        @endif
    </div>
</div>


<!-- MODAL CREAR -->
<div id="modalCrear" class="modal-fondo oculto">
    <div class="modal-caja">

        <div class="modal-header">
            <h3>Agregar Cliente</h3>
            <button type="button" class="btn-cerrar" onclick="cerrarModal('modalCrear')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('vendedor.clientes.store') }}" method="POST">
            @csrf

            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label>Nombre *</label>
                        <input type="text" name="nombre" required class="campo-input" placeholder="Ej. Marisol Lopez">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="campo-input" placeholder="Ej. 300 7081694">
                    </div>

                    <div class="md:col-span-2">
                        <label>Correo</label>
                        <input type="email" name="correo" class="campo-input" placeholder="Ej. marisol@example.com">
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalCrear')">
                    Cancelar
                </button>

                <button type="submit" class="btn-primario">
                    Guardar Cliente
                </button>
            </div>

        </form>
    </div>
</div>


<!-- MODAL EDITAR -->
<div id="modalEditar" class="modal-fondo oculto">
    <div class="modal-caja">

        <div class="modal-header">
            <h3>Editar Cliente</h3>
            <button type="button" class="btn-cerrar" onclick="cerrarModal('modalEditar')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formEditar" action="{{ route('vendedor.clientes.update', ['id' => 'ID_PLACEHOLDER']) }}" method="POST">
            @csrf

            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label>Nombre *</label>
                        <input type="text" name="nombre" id="editNombre" required class="campo-input">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="text" name="telefono" id="editTelefono" class="campo-input">
                    </div>

                    <div class="md:col-span-2">
                        <label>Correo</label>
                        <input type="email" name="correo" id="editCorreo" class="campo-input">
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalEditar')">
                    Cancelar
                </button>

                <button type="submit" class="btn-primario">
                    Guardar Cambios
                </button>
            </div>

        </form>
    </div>
</div>


<!-- MODAL ELIMINAR -->
<div id="modalEliminar" class="modal-fondo oculto">
    <div class="modal-caja pequeno">

        <div style="padding:35px 30px 20px;text-align:center">

            <div style="width:65px;height:65px;margin:auto auto 18px;border-radius:50%;background:#FDE8E8;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-exclamation-triangle" style="color:#E53935;font-size:28px"></i>
            </div>

            <h3 style="margin:0 0 10px;font-size:21px;color:#171717">
                Eliminar Cliente
            </h3>

            <p style="margin:0;color:#5F6673">
                ¿Estás seguro de que deseas eliminar a:
            </p>

            <p id="elimNombre" style="margin:8px 0 0;font-weight:700"></p>

        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancelar" onclick="cerrarModal('modalEliminar')">
                Cancelar
            </button>

            <button type="button" class="btn-eliminar" onclick="confirmarEliminar()">
                Sí, eliminar
            </button>
        </div>
    </div>
</div>

<!-- Formularios ocultos para toggle/eliminar (Laravel requiere POST/DELETE, no GET) -->
<form id="formToggle" action="" method="POST" style="display:none">
    @csrf
</form>

<form id="formEliminar" action="" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>


<script>
// URLs base con placeholder de ID, generadas por Laravel
var urlToggleBase   = "{{ route('vendedor.clientes.toggle', ['id' => 'ID_PLACEHOLDER']) }}";
var urlEliminarBase = "{{ route('vendedor.clientes.destroy', ['id' => 'ID_PLACEHOLDER']) }}";
var urlEditarBase   = "{{ route('vendedor.clientes.update', ['id' => 'ID_PLACEHOLDER']) }}";

function abrirModal(id){
    document.getElementById(id)?.classList.remove('oculto');
}

function cerrarModal(id){
    document.getElementById(id)?.classList.add('oculto');
}

function abrirModalEditar(c){
    document.getElementById('formEditar').action = urlEditarBase.replace('ID_PLACEHOLDER', c.id_cliente || '');
    document.getElementById('editNombre').value = c.nombre || '';
    document.getElementById('editTelefono').value = c.telefono || '';
    document.getElementById('editCorreo').value = c.correo || '';

    abrirModal('modalEditar');
}

var clienteEliminar = null;

function abrirModalEliminar(id, nombre){
    clienteEliminar = id;
    document.getElementById('elimNombre').textContent = nombre;

    abrirModal('modalEliminar');
}

function confirmarEliminar(){
    if (!clienteEliminar) return;

    var form = document.getElementById('formEliminar');
    form.action = urlEliminarBase.replace('ID_PLACEHOLDER', clienteEliminar);
    form.submit();
}

function cambiarEstadoCliente(id){
    if (!id) return;

    var form = document.getElementById('formToggle');
    form.action = urlToggleBase.replace('ID_PLACEHOLDER', id);
    form.submit();
}

document.querySelectorAll('.modal-fondo').forEach(function (modal) {
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.add('oculto');
    });
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-fondo').forEach(function (m) {
            m.classList.add('oculto');
        });
    }
});
</script>

@endsection