{{--
    ============================================================
    Vista: Gestionar Proveedores (Administrador)
    Datos vienen de ProveedorController@index.
    ============================================================
--}}

@include('layouts.header', ['titulo' => 'Panel de proveedores - Administrador'])
@include('layouts.sidebar')

<style>
.btn-primario,.btn-peligro,.btn-cancelar,.btn-accion,.pag{cursor:pointer;font-weight:600}
.btn-primario{background:#00875F;color:#fff;border:0;border-radius:10px;padding:10px 20px}
.btn-primario:hover{background:#01614B}
.btn-peligro{background:#E53935;color:#fff;border:0;border-radius:10px;padding:10px 20px}
.btn-peligro:hover{background:#C62828}
.btn-cancelar{background:#fff;border:1px solid #D1D5DB;border-radius:10px;padding:10px 20px}
.campo-input{width:100%;box-sizing:border-box;padding:10px 14px;border:1.5px solid #E5E7EB;border-radius:10px;outline:none}
.campo-input:focus{border-color:#61D0A7;box-shadow:0 0 0 4px rgba(97,208,167,.15)}
.campo-input[readonly]{background:#F8F8F8;color:#5F6673}
.paginacion{padding:14px 20px;border-top:1px solid #E5E7EB;display:flex;justify-content:center;gap:6px}
.pag{width:36px;height:36px;border:1px solid #E5E7EB;border-radius:9px;background:#fff;color:#5F6673;display:flex;align-items:center;justify-content:center;text-decoration:none}
.pag:hover,.pag.activa{background:#00875F;color:#fff}
.pag.deshabilitado{opacity:.4;pointer-events:none}
.btn-accion{width:34px;height:34px;padding:0;border-radius:8px;background:#fff;display:inline-flex;align-items:center;justify-content:center}
.modal-fondo{position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;padding:20px;z-index:9999}
.modal-fondo.oculto{display:none}
.modal-caja{background:#fff;width:100%;max-width:600px;border-radius:18px;box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden}
.modal-caja.pequeno{max-width:430px}
.modal-header{padding:18px 24px;border-bottom:1px solid #E5E7EB;display:flex;align-items:center;justify-content:space-between}
.modal-header h3{margin:0;color:#01614B;font-size:20px;font-weight:700}
.btn-cerrar{background:none;border:0;cursor:pointer;font-size:20px;color:#5F6673}
.modal-body{padding:24px}
.modal-footer{padding:16px 24px;border-top:1px solid #E5E7EB;display:flex;justify-content:flex-end;gap:10px}
</style>

<div class="max-w-7xl mx-auto">

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h2 class="text-3xl font-bold font-serif-ventanet" style="color:#01614B;">
                Gestionar Proveedores
            </h2>
            <p class="text-sm mt-1" style="color:#5F6673;">
                Administra los proveedores de tu negocio
            </p>
        </div>

        <button type="button" onclick="abrirModal('modalCrear')" class="btn-primario">
            <i class="fas fa-truck"></i> Nuevo Proveedor
        </button>
    </div>

    @if (session('alert'))
        <script>
        document.addEventListener('DOMContentLoaded',()=>Swal.fire({
            icon: @json(session('alert')['icon'] ?? 'info'),
            title: @json(session('alert')['title'] ?? 'Aviso'),
            text: @json(session('alert')['text'] ?? ''),
            confirmButtonText:'Entendido',
            confirmButtonColor:'#00875F'
        }));
        </script>
    @endif

    <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#E5E7EB">

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr style="background:#01614B">
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white">Nombre / Empresa</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white">Correo</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white">Teléfono</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white">Frecuencia Entrega</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white text-center">Estado</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                @foreach ($proveedores as $p)
                    @php
                        $id = (int) ($p['id_proveedor'] ?? 0);
                        $nombre = $p['nombre'] ?? '';
                        $activo = strtolower((string) ($p['estado'] ?? '')) === 'activo';
                        $fila = $activo ? '#fff' : '#FDECEC';
                    @endphp

                    <tr style="border-bottom:1px solid #E5E7EB;background:{{ $fila }}">

                        <td class="px-5 py-3.5 font-bold text-sm">
                            {{ $nombre }}
                        </td>

                        <td class="px-5 py-3.5 text-sm" style="color:#5F6673">
                            @if (!empty($p['correo']))
                                {{ $p['correo'] }}
                            @else
                                <span style="color:#9CA3AF;font-style:italic">Sin correo</span>
                            @endif
                        </td>

                        <td class="px-5 py-3.5 text-sm" style="color:#5F6673">
                            @if (!empty($p['telefono']))
                                {{ $p['telefono'] }}
                            @else
                                <span style="color:#9CA3AF;font-style:italic">Sin teléfono</span>
                            @endif
                        </td>

                        <td class="px-5 py-3.5 text-sm" style="color:#5F6673">
                            @if (!empty($p['frecuencia_entrega']))
                                {{ $p['frecuencia_entrega'] }}
                            @else
                                <span style="color:#9CA3AF;font-style:italic">Sin frecuencia</span>
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

                        <td class="px-5 py-3.5">
                            <div style="display:flex;justify-content:center;gap:8px">

                                <button
                                    type="button"
                                    class="btn-accion"
                                    title="Editar"
                                    onclick='abrirModalEditar(@json($p))'
                                    style="color:#00875F;border:1px solid #61D0A7"
                                >
                                    <i class="fas fa-pen"></i>
                                </button>

                                <button
                                    type="button"
                                    class="btn-accion"
                                    title="{{ $activo ? 'Desactivar' : 'Activar' }}"
                                    onclick="cambiarEstadoProveedor({{ $id }})"
                                    style="color:{{ $activo ? '#FFB51B' : '#00875F' }};border:1px solid {{ $activo ? '#FFB51B' : '#61D0A7' }}"
                                >
                                    <i class="fas {{ $activo ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>

                                <button
                                    type="button"
                                    class="btn-accion"
                                    title="Eliminar"
                                    onclick='abrirModalEliminar({{ $id }}, @json($nombre))'
                                    style="color:#E53935;border:1px solid #E53935"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>

                            </div>
                        </td>
                    </tr>
                @endforeach

                @if (!count($proveedores))
                    <tr>
                        <td colspan="6" style="padding:48px;text-align:center;color:#5F6673;font-weight:600">
                            No hay proveedores registrados.
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>

        @if ($total > 5)
            <nav class="paginacion">

                <a class="pag {{ $pagina <= 1 ? 'deshabilitado' : '' }}"
                   href="{{ route('admin.proveedores', ['pagina' => max(1, $pagina - 1)]) }}">«</a>

                @for ($i = 1; $i <= $paginas; $i++)
                    <a class="pag {{ $i == $pagina ? 'activa' : '' }}"
                       href="{{ route('admin.proveedores', ['pagina' => $i]) }}">
                        {{ $i }}
                    </a>
                @endfor

                <a class="pag {{ $pagina >= $paginas ? 'deshabilitado' : '' }}"
                   href="{{ route('admin.proveedores', ['pagina' => min($paginas, $pagina + 1)]) }}">»</a>

            </nav>
        @endif

    </div>
</div>


<!-- MODAL CREAR -->

<div id="modalCrear" class="modal-fondo oculto">
    <div class="modal-caja">

        <div class="modal-header">
            <h3>Agregar Nuevo Proveedor</h3>
            <button type="button" class="btn-cerrar" onclick="cerrarModal('modalCrear')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('admin.proveedores.store') }}" method="POST">
            @csrf

            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label>Nombre / Empresa *</label>
                        <input type="text" name="nombre" required class="campo-input"
                               placeholder="Ej. Distribuidora Norte">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="campo-input"
                               placeholder="Ej. 3007081694">
                    </div>

                    <div>
                        <label>Correo Electrónico</label>
                        <input type="email" name="correo" class="campo-input"
                               placeholder="proveedor@correo.com">
                    </div>

                    <div>
                        <label>Frecuencia de Entrega</label>
                        <input type="text" name="frecuencia_entrega" class="campo-input"
                               placeholder="Ej. Semanal, Mensual">
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalCrear')">
                    Cancelar
                </button>
                <button type="submit" class="btn-primario">
                    Guardar Proveedor
                </button>
            </div>

        </form>
    </div>
</div>


<!-- MODAL EDITAR -->

<div id="modalEditar" class="modal-fondo oculto">
    <div class="modal-caja">

        <div class="modal-header">
            <h3>Editar Proveedor</h3>
            <button type="button" class="btn-cerrar" onclick="cerrarModal('modalEditar')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formEditar" action="{{ url('/admin/proveedores') }}" method="POST">
            @csrf

            <input type="hidden" name="id_proveedor" id="edit_id_proveedor">

            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label>Nombre / Empresa *</label>
                        <input type="text" name="nombre" id="edit_nombre"
                               required class="campo-input">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="text" name="telefono" id="edit_telefono"
                               class="campo-input">
                    </div>

                    <div>
                        <label>Correo Electrónico</label>
                        <input type="email" name="correo" id="edit_correo"
                               class="campo-input">
                    </div>

                    <div>
                        <label>Frecuencia de Entrega</label>
                        <input type="text" name="frecuencia_entrega"
                               id="edit_frecuencia_entrega"
                               class="campo-input">
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

            <div style="
                width:65px;height:65px;margin:0 auto 18px;
                border-radius:50%;background:#FDE8E8;
                display:flex;align-items:center;justify-content:center">
                <i class="fas fa-exclamation-triangle"
                   style="color:#E53935;font-size:28px"></i>
            </div>

            <h3 style="margin:0 0 10px;font-size:21px;font-weight:700">
                Eliminar Proveedor
            </h3>

            <p style="margin:0;color:#5F6673">
                ¿Estás seguro de que deseas eliminar a:
            </p>

            <p id="elimNombre" style="margin:8px 0 0;font-weight:700"></p>

        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancelar"
                    onclick="cerrarModal('modalEliminar')">
                Cancelar
            </button>

            <button type="button" class="btn-peligro"
                    onclick="confirmarEliminar()">
                Sí, eliminar
            </button>
        </div>

    </div>
</div>

<!-- Formularios ocultos para toggle y eliminar -->
<form id="formToggle" method="POST" style="display:none">
    @csrf
</form>

<form id="formEliminar" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

<script>

const baseProveedores = "{{ url('/admin/proveedores') }}";

function abrirModal(id){
    document.getElementById(id)?.classList.remove('oculto');
}

function cerrarModal(id){
    document.getElementById(id)?.classList.add('oculto');
}

function abrirModalEditar(p){
    document.getElementById('edit_id_proveedor').value = p.id_proveedor || '';
    document.getElementById('edit_nombre').value = p.nombre || '';
    document.getElementById('edit_telefono').value = p.telefono || '';
    document.getElementById('edit_correo').value = p.correo || '';
    document.getElementById('edit_frecuencia_entrega').value = p.frecuencia_entrega || '';

    document.getElementById('formEditar').action = baseProveedores + '/' + p.id_proveedor + '/editar';

    abrirModal('modalEditar');
}

let proveedorEliminar = null;

function abrirModalEliminar(id, nombre){
    proveedorEliminar = id;
    document.getElementById('elimNombre').textContent = nombre;
    abrirModal('modalEliminar');
}

function confirmarEliminar(){
    if(!proveedorEliminar) return;

    const form = document.getElementById('formEliminar');
    form.action = baseProveedores + '/' + proveedorEliminar;
    form.submit();
}

function cambiarEstadoProveedor(id){
    if(!id) return;

    const form = document.getElementById('formToggle');
    form.action = baseProveedores + '/' + id + '/toggle';
    form.submit();
}

document.querySelectorAll('.modal-fondo').forEach(modal=>{
    modal.addEventListener('click',e=>{
        if(e.target===modal) modal.classList.add('oculto');
    });
});

document.addEventListener('keydown',e=>{
    if(e.key==='Escape'){
        document.querySelectorAll('.modal-fondo')
            .forEach(m=>m.classList.add('oculto'));
    }
});
</script>

@include('layouts.footer')