@extends('layouts.dashboard')

@php
    $titulo = 'Panel de usuarios - Administrador';
@endphp

@section('content')

<style>
.btn-primario,.btn-cancelar,.btn-eliminar{border-radius:10px;padding:10px 20px;font-weight:600;cursor:pointer}
.btn-primario{background:#00875F;color:white;border:0}
.btn-primario:hover{background:#01614B}
.campo-input{width:100%;padding:10px 14px;background:white;border:1.5px solid #E5E7EB;border-radius:10px;font-size:.95rem;outline:none;box-sizing:border-box}
.campo-input:focus{border-color:#61D0A7}
.btn-accion{width:34px!important;height:34px!important;padding:0!important;border-radius:8px!important;border:1px solid!important;background:white!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;cursor:pointer}
.paginacion{padding:14px 20px;border-top:1px solid #E5E7EB;display:flex;justify-content:center;align-items:center;gap:6px}
.modal-fondo{position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;padding:20px;z-index:9999}
.modal-fondo.oculto{display:none}
.modal-caja{background:white;width:100%;max-width:650px;border-radius:18px;box-shadow:0 20px 60px rgba(0,0,0,.20);overflow:hidden}
.modal-caja.pequeno{max-width:430px}
.modal-header{padding:18px 24px;border-bottom:1px solid #E5E7EB;display:flex;align-items:center;justify-content:space-between}
.modal-header h3{margin:0;color:#01614B;font-size:20px;font-weight:700}
.btn-cerrar{background:none;border:0;cursor:pointer;font-size:20px;color:#5F6673}
.modal-body{padding:24px}
.modal-footer{padding:16px 24px;border-top:1px solid #E5E7EB;display:flex;justify-content:flex-end;gap:10px}
.btn-cancelar{background:white;border:1px solid #D1D5DB}
.btn-eliminar{background:#E53935;color:white;border:0}
</style>

<div class="max-w-7xl mx-auto">

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h2 class="text-3xl font-bold font-serif-ventanet" style="color:#01614B">
                Gestionar Usuarios
            </h2>
            <p class="text-sm mt-1" style="color:#5F6673">
                Gestiona los accesos y permisos de forma centralizada
            </p>
        </div>

        <button type="button" onclick="abrirModal('modalCrear')" class="btn-primario">
            <i class="fas fa-user-plus"></i> Nuevo Usuario
        </button>
    </div>

    @if (session('alert'))
        <script>
        document.addEventListener('DOMContentLoaded', () => Swal.fire({
            icon: @json(session('alert')['icon'] ?? 'info'),
            title: @json(session('alert')['title'] ?? 'Aviso'),
            text: @json(session('alert')['text'] ?? ''),
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#00875F'
        }));
        </script>
    @endif

    <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#E5E7EB">
        <div class="overflow-x-auto">

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr style="background:#01614B">
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white">Nombre</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white">Correo</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white">Teléfono</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white text-center">Rol</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white text-center">Estado</th>
                        <th class="px-5 py-3 text-xs font-bold uppercase text-white text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>

                @forelse ($usuarios as $usuario)

                    @php
                        $activo = (int) $usuario->estado === 1;
                        $fila = $activo ? '#FFFFFF' : '#FDECEC';
                        $hover = $activo ? '#F8F8F8' : '#FDE0E0';
                        $nombreRol = optional($usuario->rol)->nombre ?? '—';
                        $admin = strtolower($nombreRol) === 'administrador';
                        $datosEditar = json_encode([
                            'id_usuario' => $usuario->id_usuario,
                            'nombre' => optional($usuario->persona)->nombre,
                            'telefono' => optional($usuario->persona)->telefono,
                            'correo' => optional($usuario->persona)->correo,
                            'id_rol' => $usuario->id_rol,
                        ]);
                    @endphp

                    <tr
                        style="border-bottom:1px solid #E5E7EB;background:{{ $fila }}"
                        onmouseover="this.style.background='{{ $hover }}'"
                        onmouseout="this.style.background='{{ $fila }}'"
                    >

                        <td class="px-5 py-3.5 font-bold text-sm">
                            {{ optional($usuario->persona)->nombre }}
                        </td>

                        <td class="px-5 py-3.5 text-sm" style="color:#5F6673">
                            {{ optional($usuario->persona)->correo }}
                        </td>

                        <td class="px-5 py-3.5 text-sm" style="color:#5F6673">
                            {{ optional($usuario->persona)->telefono ?: 'No registrado' }}
                        </td>

                        <td class="px-5 py-3.5 text-center">
                            <span style="
                                {{ $admin
                                    ? 'background:#EBF5FF;color:#1F3552;border:1px solid #BFDBFE;'
                                    : 'background:#FFFBEB;color:#92400E;border:1px solid #FFB51B;' }}
                                padding:3px 12px;border-radius:999px;font-size:.75rem;font-weight:700">
                                {{ $nombreRol }}
                            </span>
                        </td>

                        <td class="px-5 py-3.5 text-center">
                            <span style="
                                background:{{ $activo ? '#DDF5EC' : '#FDE8E8' }};
                                color:{{ $activo ? '#00875F' : '#E53935' }};
                                border:1px solid {{ $activo ? '#61D0A7' : '#E53935' }};
                                padding:3px 12px;border-radius:999px;font-size:.75rem;font-weight:700">
                                {{ $activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>

                        <td class="px-5 py-3.5">
                            <div style="display:flex;justify-content:center;align-items:center;gap:8px">

                                <button
                                    type="button"
                                    class="btn-accion"
                                    title="Editar"
                                    onclick='abrirModalEditar({{ $datosEditar }})'
                                    style="color:#00875F;border-color:#61D0A7!important"
                                >
                                    <i class="fas fa-pen"></i>
                                </button>

                                <button
                                    type="button"
                                    class="btn-accion"
                                    title="{{ $activo ? 'Desactivar usuario' : 'Activar usuario' }}"
                                    onclick="cambiarEstadoUsuario({{ $usuario->id_usuario }})"
                                    style="
                                        color:{{ $activo ? '#FFB51B' : '#00875F' }};
                                        border-color:{{ $activo ? '#FFB51B' : '#61D0A7' }}!important"
                                >
                                    <i class="fas {{ $activo ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>

                                <button
                                    type="button"
                                    class="btn-accion"
                                    title="Eliminar"
                                    onclick="abrirModalEliminar({{ $usuario->id_usuario }}, @json(optional($usuario->persona)->nombre))"
                                    style="color:#E53935;border-color:#E53935!important"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>

                            </div>
                        </td>

                    </tr>

                @empty
                    <tr>
                        <td colspan="6" style="padding:48px;text-align:center;color:#5F6673;font-weight:600">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                @endforelse

                </tbody>
            </table>

        </div>

        @if ($usuarios->hasPages())
            <div class="paginacion">
                {{ $usuarios->links() }}
            </div>
        @endif

    </div>
</div>


<!-- MODAL CREAR -->

<div id="modalCrear" class="modal-fondo oculto">
    <div class="modal-caja">

        <div class="modal-header">
            <h3>Agregar Usuario</h3>
            <button type="button" class="btn-cerrar" onclick="cerrarModal('modalCrear')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('admin.usuarios.crear') }}" method="POST">
            @csrf

            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label>Nombre completo *</label>
                        <input type="text" name="nombre" required class="campo-input" placeholder="Ej. Catalina Santana">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="campo-input" placeholder="Ej. 3144345676">
                    </div>

                    <div>
                        <label>Correo *</label>
                        <input type="email" name="correo" required class="campo-input" placeholder="Ej. cata@gmail.com">
                    </div>

                    <div>
                        <label>Contraseña *</label>
                        <input type="password" name="password" required class="campo-input" placeholder="••••••••">
                    </div>

                    <div>
                        <label>Confirmar contraseña *</label>
                        <input type="password" name="confirmar_password" required class="campo-input" placeholder="••••••••">
                    </div>

                    <div>
                        <label>Rol *</label>
                        <select name="rol" required class="campo-input">
                            @foreach ($roles as $r)
                                <option value="{{ $r->id_rol }}">
                                    {{ $r->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancelar" onclick="cerrarModal('modalCrear')">
                    Cancelar
                </button>
                <button type="submit" class="btn-primario">
                    Guardar Usuario
                </button>
            </div>

        </form>
    </div>
</div>


<!-- MODAL EDITAR -->

<div id="modalEditar" class="modal-fondo oculto">
    <div class="modal-caja">

        <div class="modal-header">
            <h3>Editar Usuario</h3>
            <button type="button" class="btn-cerrar" onclick="cerrarModal('modalEditar')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formEditar" action="" method="POST">
            @csrf

            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label>Nombre completo *</label>
                        <input type="text" name="nombre" id="editNombre" required class="campo-input">
                    </div>

                    <div>
                        <label>Teléfono</label>
                        <input type="text" name="telefono" id="editTelefono" class="campo-input">
                    </div>

                    <div>
                        <label>Correo</label>
                        <input type="email" id="editCorreo" readonly class="campo-input">
                    </div>

                    <div>
                        <label>Nueva contraseña</label>
                        <input type="password" name="password" id="editPassword" class="campo-input">
                    </div>

                    <div>
                        <label>Rol *</label>
                        <select name="rol" id="editRol" required class="campo-input">
                            @foreach ($roles as $r)
                                <option value="{{ $r->id_rol }}">
                                    {{ $r->nombre }}
                                </option>
                            @endforeach
                        </select>
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
                width:65px;height:65px;margin:0 auto 18px;border-radius:50%;
                background:#FDE8E8;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-exclamation-triangle" style="color:#E53935;font-size:28px"></i>
            </div>

            <h3 style="margin:0 0 10px;font-size:21px;font-weight:700;color:#171717">
                Eliminar Usuario
            </h3>

            <p style="margin:0;color:#5F6673;line-height:1.5">
                ¿Estás seguro de que deseas eliminar a:
            </p>

            <p id="elimNombre" style="margin:8px 0 0;font-weight:700;color:#171717"></p>

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


<!-- Formularios ocultos para toggle y eliminar (Laravel requiere POST/DELETE, no GET) -->

<form id="formToggle" action="" method="POST" style="display:none">
    @csrf
</form>

<form id="formEliminar" action="" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>


<script>

const modal = id => document.getElementById(id);

function abrirModal(id){
    modal(id)?.classList.remove('oculto');
}

function cerrarModal(id){
    modal(id)?.classList.add('oculto');
}

function abrirModalEditar(u){

    const form = document.getElementById('formEditar');
    form.action = "{{ url('/admin/usuarios') }}/" + u.id_usuario + "/editar";

    document.getElementById('editNombre').value = u.nombre || '';
    document.getElementById('editTelefono').value = u.telefono || '';
    document.getElementById('editCorreo').value = u.correo || '';
    document.getElementById('editRol').value = u.id_rol || '';
    document.getElementById('editPassword').value = '';

    abrirModal('modalEditar');
}

let usuarioEliminar = null;

function abrirModalEliminar(id, nombre){

    usuarioEliminar = id;
    document.getElementById('elimNombre').textContent = nombre;
    abrirModal('modalEliminar');
}

function confirmarEliminar(){

    if(!usuarioEliminar) return;

    const form = document.getElementById('formEliminar');
    form.action = "{{ url('/admin/usuarios') }}/" + usuarioEliminar;
    form.submit();
}

function cambiarEstadoUsuario(id){

    if(!id || id <= 0) return;

    const form = document.getElementById('formToggle');
    form.action = "{{ url('/admin/usuarios') }}/" + id + "/toggle";
    form.submit();
}

document.querySelectorAll('.modal-fondo').forEach(m =>
    m.addEventListener('click', e => {
        if (e.target === m) m.classList.add('oculto');
    })
);

document.addEventListener('keydown', e => {

    if (e.key === 'Escape')
        document.querySelectorAll('.modal-fondo')
            .forEach(m => m.classList.add('oculto'));

});

</script>

@endsection