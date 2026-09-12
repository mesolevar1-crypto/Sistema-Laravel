@extends('layouts.dashboard')

@php
    $titulo = 'Gestionar Productos - Vendedor';

    // ============================================================
    // PALETA AUTOMÁTICA DE COLORES
    // La categoría NO tiene color en la BD. Se le asigna un color de
    // esta paleta según su id_categoria, siempre el mismo mientras
    // el id no cambie -- nunca se guarda en BD.
    // ============================================================
    $paleta = [
        ['bg' => 'from-emerald-400 to-green-500', 'accent' => '#10b981'],
        ['bg' => 'from-sky-400 to-blue-500',       'accent' => '#0ea5e9'],
        ['bg' => 'from-violet-400 to-purple-500',  'accent' => '#8b5cf6'],
        ['bg' => 'from-amber-400 to-orange-500',   'accent' => '#f59e0b'],
        ['bg' => 'from-rose-400 to-pink-500',      'accent' => '#f43f5e'],
        ['bg' => 'from-teal-400 to-cyan-500',      'accent' => '#14b8a6'],
        ['bg' => 'from-lime-400 to-green-400',     'accent' => '#84cc16'],
        ['bg' => 'from-indigo-400 to-blue-600',    'accent' => '#6366f1'],
    ];

    function colorPorCategoria($id_categoria, $paleta)
    {
        if (empty($id_categoria)) {
            return $paleta[0];
        }

        $indice = ((int) $id_categoria - 1) % count($paleta);

        return $paleta[$indice];
    }

    function hexClaro($hex, $porcentaje = 0.85)
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) !== 6) {
            $hex = '10b981';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = round($r + ($porcentaje * (255 - $r)));
        $g = round($g + ($porcentaje * (255 - $g)));
        $b = round($b + ($porcentaje * (255 - $b)));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
@endphp

@section('content')

<style>
.product-card {
    background: #fff;
    border: 1px solid #dfe7df;
    border-top: 4px solid #dfe7df;
    border-radius: 18px;
    overflow: hidden;
    transition: .25s ease;
}
.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 25px rgba(0,0,0,.08);
}
.product-image { height: 150px; }
.product-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}
.product-action {
    height: 42px;
    width: 100%;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: .2s;
}
.action-edit { color: #15803d; border: 1px solid #bbdfb8; background: #fff; }
.action-edit:hover { background: #edf9eb; }
.action-status { color: #d97706; border: 1px solid #f5b942; background: #fff; }
.action-status:hover { background: #fff8e6; }
.action-delete { color: #ef4444; border: 1px solid #fecaca; background: #fff; }
.action-delete:hover { background: #fff1f1; }
.estado-activo { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.estado-inactivo { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
.product-card.inactivo { background: #fef2f2; }
.product-card.inactivo:hover { box-shadow: 0 12px 25px rgba(239,68,68,.15); }
.product-card.inactivo .product-image { opacity: 0.6; }
.product-card.inactivo h3 { color: #991b1b; }
.ventanet-input, .ventanet-select {
    background: #fff;
    border: 1px solid #dfe7df;
    border-radius: 10px;
    outline: none;
}
.ventanet-input:focus, .ventanet-select:focus {
    border-color: #61d0a7;
    box-shadow: 0 0 0 3px rgba(97,208,167,.15);
}
.ventanet-btn-primary { background: #00875f; color: #fff; border-radius: 10px; font-weight: 600; transition: .2s; }
.ventanet-btn-primary:hover { background: #01614b; }
.ventanet-btn-secondary { background: #fff; color: #00875f; border: 1px solid #00875f; border-radius: 10px; font-weight: 600; transition: .2s; }
.ventanet-btn-secondary:hover { background: #edf9f5; }
.ventanet-btn-danger { background: #e53935; color: #fff; border-radius: 10px; font-weight: 600; }
.modal-overlay { overflow-y: auto; padding: 20px; }
.modal-container { width: 100%; max-height: 92vh; overflow-y: auto; }
.paginacion-btn {
    width: 38px; height: 38px; border-radius: 10px; border: 1px solid #c9e4c5;
    background: #fff; color: #4a8c44; font-weight: 600;
    display: flex; align-items: center; justify-content: center; transition: .2s;
}
.paginacion-btn:hover { background: #eff9ed; }
.paginacion-btn.activo { background: #00875f; color: #fff; border-color: #00875f; }
.paginacion-flecha { font-size: 1rem; }
.paginacion-btn:disabled { opacity: .4; cursor: not-allowed; background: #fff; color: #9CA3AF; border-color: #e5e7eb; }
.paginacion-btn:disabled:hover { background: #fff; }
.categoria-item {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff;
}
.categoria-swatch { width: 28px; height: 28px; border-radius: 8px; border: 1px solid rgba(0,0,0,.1); flex-shrink: 0; }
</style>

<div class="font-sans-ventanet text-[#1C2E1A] max-w-7xl mx-auto">

    <!-- ENCABEZADO -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-bold font-serif-ventanet text-[#01614B]">
                Mis Productos
            </h2>
            <p class="text-sm mt-1 text-[#5F6673]">
                Administra los productos que tú has registrado
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button type="button" onclick="openModal('modalCategorias')" class="ventanet-btn-secondary px-5 py-2.5 flex items-center gap-2 justify-center">
                <i class="fas fa-tags"></i>
                Agregar Categorías
            </button>
            <button type="button" onclick="openModal('modalCrear')" class="ventanet-btn-primary px-5 py-2.5 flex items-center gap-2 justify-center">
                <i class="fas fa-plus-circle"></i>
                Nuevo Producto
            </button>
        </div>
    </div>

    <!-- ALERTAS -->
    @if (session('alert'))
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: @json(session('alert')['icon'] ?? 'info'),
                title: @json(session('alert')['title'] ?? 'Aviso'),
                text: @json(session('alert')['text'] ?? ''),
                confirmButtonColor: '#00875F'
            });
        });
        </script>
    @endif

    <!-- BUSCADOR -->
    <div class="relative mb-6">
        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-[#96B092]"></i>
        <input type="text" id="buscadorProductos" placeholder="Buscar producto por nombre o categoría..." class="ventanet-input w-full pl-11 pr-4 py-3">
        <span id="contadorResultados" class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-[#96B092]"></span>
    </div>

    <!-- GRID -->
    <div id="gridProductos" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">

        @forelse ($productos as $p)
            @php
                $color = colorPorCategoria($p['id_categoria'] ?? null, $paleta);
                $colorBadgeBg = hexClaro($color['accent'], 0.85);
                $estado = strtolower(trim($p['estado'] ?? 'activo'));
                $estadoActivo = in_array($estado, ['activo', '1', 'habilitado', '1.0']);
                $colorBorde = $estadoActivo ? $color['accent'] : '#ef4444';
            @endphp

            <div
                class="product-card producto-item {{ $estadoActivo ? '' : 'inactivo' }}"
                data-nombre="{{ strtolower($p['nombre']) }}"
                data-categoria="{{ strtolower($p['categoria'] ?? '') }}"
                style="border-color: {{ $colorBorde }};"
            >
                <div class="p-4">

                    <div class="relative">
                        @if ($estadoActivo)
                            <span class="absolute top-2 left-2 z-10 px-3 py-1 rounded-full text-xs font-bold estado-activo">
                                <i class="fas fa-check-circle mr-1"></i> Activo
                            </span>
                        @else
                            <span class="absolute top-2 left-2 z-10 px-3 py-1 rounded-full text-xs font-bold estado-inactivo">
                                <i class="fas fa-ban mr-1"></i> Inactivo
                            </span>
                        @endif

                        @if (!empty($p['imagen']))
                            <div class="product-image rounded-xl overflow-hidden bg-[#f0f9ee]">
                                <img src="{{ asset($p['imagen']) }}" alt="{{ $p['nombre'] }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="product-image rounded-xl bg-gradient-to-br {{ $color['bg'] }} flex items-center justify-center">
                                <i class="fas fa-box-open text-4xl text-white/80"></i>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between gap-2 mt-4">
                        <h3 class="text-base font-bold text-[#1C2E1A] truncate" title="{{ $p['nombre'] }}">
                            {{ $p['nombre'] }}
                        </h3>

                        @if (!empty($p['categoria']))
                            <span
                                class="shrink-0 px-2.5 py-1 rounded-full text-[.68rem] font-bold border"
                                style="background: {{ $colorBadgeBg }}; color: {{ $color['accent'] }}; border-color: {{ $color['accent'] }}55;"
                            >
                                {{ $p['categoria'] }}
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-[#6B7280] mt-3 min-h-[36px] line-clamp-2">
                        {{ !empty($p['descripcion']) ? $p['descripcion'] : 'Sin descripción' }}
                    </p>

                    <div class="border-t border-[#C9E4C5] mt-4 pt-4">
                        <div class="product-actions">

                            <button type="button" class="product-action action-edit" onclick='openEditModal(@json($p))' title="Editar producto">
                                <i class="fas fa-pen"></i>
                            </button>

                            <button
                                type="button"
                                class="product-action action-status"
                                onclick="cambiarEstado({{ (int) $p['id_producto'] }})"
                                title="{{ $estadoActivo ? 'Desactivar producto' : 'Activar producto' }}"
                            >
                                <i class="fas fa-power-off"></i>
                            </button>

                            <button
                                type="button"
                                class="product-action action-delete"
                                onclick="openDeleteModal({{ (int) $p['id_producto'] }}, {{ Js::from($p['nombre']) }})"
                                title="Eliminar producto"
                            >
                                <i class="fas fa-trash"></i>
                            </button>

                        </div>
                    </div>

                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-[20px] border-2 border-dashed border-[#C9E4C5] p-12 text-center">
                    <div class="w-20 h-20 mx-auto bg-[#e4f5e2] rounded-full flex items-center justify-center text-[#96B092] text-3xl mb-5">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3 class="text-2xl font-serif-ventanet text-[#1C2E1A] mb-2">
                        Aún no has registrado productos
                    </h3>
                    <p class="text-[#4E6B4A] font-medium mb-6">
                        Comienza agregando tu primer producto al catálogo
                    </p>
                    <button type="button" onclick="openModal('modalCrear')" class="ventanet-btn-primary px-6 py-3 inline-flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i>
                        Agregar Producto
                    </button>
                </div>
            </div>
        @endforelse

    </div>

    <!-- PAGINACION -->
    <div id="paginacion" class="flex justify-center items-center gap-2 mt-8"></div>
</div>


<!-- MODAL CREAR PRODUCTO -->
<div id="modalCrear" class="fixed inset-0 bg-[#1C2E1A]/50 backdrop-blur-sm hidden z-[9999] modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-[20px] shadow-2xl modal-container max-w-lg">
        <div class="px-7 py-5 border-b flex justify-between items-center bg-[#F8F8F8]">
            <h3 class="text-xl font-serif-ventanet text-[#171717]">Agregar Producto</h3>
            <button type="button" onclick="closeModal('modalCrear')" class="text-[#9CA3AF] hover:text-[#00875F]">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form action="{{ route('vendedor.productos.store') }}" method="POST" enctype="multipart/form-data" class="p-7 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-bold mb-1.5">Nombre del Producto *</label>
                <input type="text" name="nombre" required class="ventanet-input w-full px-4 py-2.5" placeholder="Ej. Manzana Roja">
            </div>

            <div>
                <label class="block text-sm font-bold mb-1.5">Descripción</label>
                <textarea name="descripcion" rows="3" class="ventanet-input w-full px-4 py-2.5 resize-none" placeholder="Descripción del producto..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold mb-1.5 flex items-center justify-between">
                    <span>Categoría *</span>
                    <button type="button" onclick="closeModal('modalCrear'); openModal('modalCategorias');" class="text-xs text-[#00875F] font-semibold hover:underline">
                        <i class="fas fa-plus"></i> Gestionar categorías
                    </button>
                </label>
                <select name="id_categoria" required class="ventanet-select w-full px-4 py-2.5">
                    <option value="" selected disabled>Seleccione una categoría</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ (int) $cat['id_categoria'] }}">{{ $cat['tipo'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold mb-1.5">Imagen del Producto</label>
                <div class="border-2 border-dashed border-[#C9E4C5] rounded-xl p-4 text-center cursor-pointer" onclick="document.getElementById('imagenCrear').click()">
                    <img id="previewCrear" class="hidden mx-auto mb-2 max-h-32 rounded-lg object-cover">
                    <div id="placeholderCrear">
                        <i class="fas fa-cloud-upload-alt text-3xl text-[#96B092] mb-2"></i>
                        <p class="text-sm text-[#4E6B4A]">Haz clic para subir una imagen</p>
                        <p class="text-xs text-[#96B092] mt-1">JPG, PNG, GIF, WEBP — máx. 2MB</p>
                    </div>
                    <input type="file" id="imagenCrear" name="imagen" accept="image/*" class="hidden" onchange="previewImagen(this,'previewCrear','placeholderCrear')">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-5 border-t">
                <button type="button" onclick="closeModal('modalCrear')" class="px-6 py-2 rounded-xl border">Cancelar</button>
                <button type="submit" class="ventanet-btn-primary px-8 py-2">Guardar Producto</button>
            </div>
        </form>
    </div>
</div>


<!-- MODAL EDITAR PRODUCTO -->
<div id="modalEditar" class="fixed inset-0 bg-[#1C2E1A]/50 backdrop-blur-sm hidden z-[9999] modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-[20px] shadow-2xl modal-container max-w-lg">
        <div class="px-7 py-5 border-b flex justify-between items-center bg-[#F8F8F8]">
            <h3 class="text-xl font-serif-ventanet">Editar Producto</h3>
            <button type="button" onclick="closeModal('modalEditar')">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formEditarProducto" action="{{ route('vendedor.productos.update', ['id' => 'ID_PLACEHOLDER']) }}" method="POST" enctype="multipart/form-data" class="p-7 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-bold mb-1.5">Nombre del Producto *</label>
                <input type="text" name="nombre" id="edit_nombre" required class="ventanet-input w-full px-4 py-2.5">
            </div>

            <div>
                <label class="block text-sm font-bold mb-1.5">Descripción</label>
                <textarea name="descripcion" id="edit_descripcion" rows="3" class="ventanet-input w-full px-4 py-2.5 resize-none"></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold mb-1.5">Categoría *</label>
                <select name="id_categoria" id="edit_id_categoria" required class="ventanet-select w-full px-4 py-2.5">
                    @foreach ($categorias as $cat)
                        <option value="{{ (int) $cat['id_categoria'] }}">{{ $cat['tipo'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold mb-1.5">Imagen del Producto</label>
                <div class="border-2 border-dashed border-[#C9E4C5] rounded-xl p-4 text-center cursor-pointer" onclick="document.getElementById('imagenEditar').click()">
                    <img id="previewEditar" class="hidden mx-auto mb-2 max-h-32 rounded-lg object-cover">
                    <div id="placeholderEditar">
                        <i class="fas fa-cloud-upload-alt text-3xl text-[#96B092] mb-2"></i>
                        <p class="text-sm text-[#4E6B4A]">Haz clic para cambiar la imagen</p>
                    </div>
                    <input type="file" id="imagenEditar" name="imagen" accept="image/*" class="hidden" onchange="previewImagen(this,'previewEditar','placeholderEditar')">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-5 border-t">
                <button type="button" onclick="closeModal('modalEditar')" class="px-6 py-2 rounded-xl border">Cancelar</button>
                <button type="submit" class="ventanet-btn-primary px-8 py-2">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>


<!-- MODAL ELIMINAR PRODUCTO -->
<div id="modalEliminar" class="fixed inset-0 bg-[#1C2E1A]/60 backdrop-blur-sm hidden z-[10000] modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-[20px] w-full max-w-md shadow-2xl">
        <div class="p-8 text-center">
            <div class="w-20 h-20 mx-auto bg-red-50 rounded-full flex items-center justify-center mb-5">
                <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
            </div>
            <h3 class="text-2xl font-serif-ventanet mb-2">Eliminar Producto</h3>
            <p class="text-[#4E6B4A]">¿Estás seguro de eliminar:</p>
            <p id="delete_nombre" class="font-bold text-lg"></p>
        </div>
        <div class="px-6 py-5 bg-[#F8F8F8] border-t flex justify-center gap-3">
            <button type="button" onclick="closeModal('modalEliminar')" class="px-5 py-2.5 rounded-xl border">Cancelar</button>
            <button type="button" onclick="confirmarEliminarProducto()" class="ventanet-btn-danger px-6 py-2.5 flex items-center">Sí, eliminar</button>
        </div>
    </div>
</div>


<!-- MODAL GESTIONAR CATEGORÍAS -->
<div id="modalCategorias" class="fixed inset-0 bg-[#1C2E1A]/50 backdrop-blur-sm hidden z-[9999] modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-[20px] shadow-2xl modal-container max-w-lg">
        <div class="px-7 py-5 border-b flex justify-between items-center bg-[#F8F8F8]">
            <h3 class="text-xl font-serif-ventanet text-[#171717]">Gestionar Categorías</h3>
            <button type="button" onclick="closeModal('modalCategorias')" class="text-[#9CA3AF] hover:text-[#00875F]">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-7 space-y-4">
            <button type="button" onclick="closeModal('modalCategorias'); openModal('modalCrearCategoria');" class="ventanet-btn-primary w-full py-2.5 flex items-center justify-center gap-2">
                <i class="fas fa-plus-circle"></i>
                Nueva Categoría
            </button>

            <div class="space-y-2.5 max-h-[50vh] overflow-y-auto pr-1">
                @forelse ($categorias as $cat)
                    @php $colorCat = colorPorCategoria($cat['id_categoria'], $paleta); @endphp
                    <div class="categoria-item">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="categoria-swatch" style="background: {{ $colorCat['accent'] }};"></div>
                            <span class="font-semibold truncate">{{ $cat['tipo'] }}</span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" class="product-action action-edit" style="height:36px; width:36px;" title="Editar categoría" onclick='openEditCategoriaModal(@json($cat))'>
                                <i class="fas fa-pen"></i>
                            </button>
                            <button type="button" class="product-action action-delete" style="height:36px; width:36px;" title="Eliminar categoría" onclick="openDeleteCategoriaModal({{ (int) $cat['id_categoria'] }}, {{ Js::from($cat['tipo']) }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-sm text-[#6B7280] py-6">Aún no hay categorías registradas.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>


<!-- MODAL CREAR CATEGORÍA -->
<div id="modalCrearCategoria" class="fixed inset-0 bg-[#1C2E1A]/50 backdrop-blur-sm hidden z-[10000] modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-[20px] shadow-2xl modal-container max-w-sm">
        <div class="px-7 py-5 border-b flex justify-between items-center bg-[#F8F8F8]">
            <h3 class="text-xl font-serif-ventanet text-[#171717]">Nueva Categoría</h3>
            <button type="button" onclick="closeModal('modalCrearCategoria'); openModal('modalCategorias');" class="text-[#9CA3AF] hover:text-[#00875F]">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form action="{{ route('vendedor.categorias.store') }}" method="POST" class="p-7 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-bold mb-1.5">Nombre de la Categoría *</label>
                <input type="text" name="tipo" required maxlength="50" class="ventanet-input w-full px-4 py-2.5" placeholder="Ej. Verdura, Grano, Fruta...">
                <p class="text-xs text-[#96B092] mt-1.5">El color de la categoría se asigna automáticamente.</p>
            </div>

            <div class="flex justify-end gap-3 pt-5 border-t">
                <button type="button" onclick="closeModal('modalCrearCategoria'); openModal('modalCategorias');" class="px-6 py-2 rounded-xl border">Cancelar</button>
                <button type="submit" class="ventanet-btn-primary px-8 py-2">Guardar</button>
            </div>
        </form>
    </div>
</div>


<!-- MODAL EDITAR CATEGORÍA -->
<div id="modalEditarCategoria" class="fixed inset-0 bg-[#1C2E1A]/50 backdrop-blur-sm hidden z-[10000] modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-[20px] shadow-2xl modal-container max-w-sm">
        <div class="px-7 py-5 border-b flex justify-between items-center bg-[#F8F8F8]">
            <h3 class="text-xl font-serif-ventanet text-[#171717]">Editar Categoría</h3>
            <button type="button" onclick="closeModal('modalEditarCategoria'); openModal('modalCategorias');" class="text-[#9CA3AF] hover:text-[#00875F]">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formEditarCategoria" action="{{ route('vendedor.categorias.update', ['id' => 'ID_PLACEHOLDER']) }}" method="POST" class="p-7 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-bold mb-1.5">Nombre de la Categoría *</label>
                <input type="text" name="tipo" id="edit_tipo_categoria" required maxlength="50" class="ventanet-input w-full px-4 py-2.5">
            </div>

            <div class="flex justify-end gap-3 pt-5 border-t">
                <button type="button" onclick="closeModal('modalEditarCategoria'); openModal('modalCategorias');" class="px-6 py-2 rounded-xl border">Cancelar</button>
                <button type="submit" class="ventanet-btn-primary px-8 py-2">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>


<!-- MODAL ELIMINAR CATEGORÍA -->
<div id="modalEliminarCategoria" class="fixed inset-0 bg-[#1C2E1A]/60 backdrop-blur-sm hidden z-[10001] modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-[20px] w-full max-w-md shadow-2xl">
        <div class="p-8 text-center">
            <div class="w-20 h-20 mx-auto bg-red-50 rounded-full flex items-center justify-center mb-5">
                <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
            </div>
            <h3 class="text-2xl font-serif-ventanet mb-2">Eliminar Categoría</h3>
            <p class="text-[#4E6B4A]">¿Estás seguro de eliminar la categoría:</p>
            <p id="delete_categoria_nombre" class="font-bold text-lg"></p>
            <p class="text-xs text-[#9CA3AF] mt-2">No podrás eliminarla si tiene productos asociados.</p>
        </div>
        <div class="px-6 py-5 bg-[#F8F8F8] border-t flex justify-center gap-3">
            <button type="button" onclick="closeModal('modalEliminarCategoria'); openModal('modalCategorias');" class="px-5 py-2.5 rounded-xl border">Cancelar</button>
            <button type="button" onclick="confirmarEliminarCategoria()" class="ventanet-btn-danger px-6 py-2.5 flex items-center">Sí, eliminar</button>
        </div>
    </div>
</div>

<!-- Formularios ocultos para toggle/eliminar (Laravel requiere POST/DELETE, no GET) -->
<form id="formToggleProducto" action="" method="POST" style="display:none">
    @csrf
</form>

<form id="formEliminarProducto" action="" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

<form id="formEliminarCategoria" action="" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>


<script>
// URLs base con placeholder de ID, generadas por Laravel
var urlToggleProductoBase   = "{{ route('vendedor.productos.toggle', ['id' => 'ID_PLACEHOLDER']) }}";
var urlEliminarProductoBase = "{{ route('vendedor.productos.destroy', ['id' => 'ID_PLACEHOLDER']) }}";
var urlEditarProductoBase   = "{{ route('vendedor.productos.update', ['id' => 'ID_PLACEHOLDER']) }}";
var urlEditarCategoriaBase  = "{{ route('vendedor.categorias.update', ['id' => 'ID_PLACEHOLDER']) }}";
var urlEliminarCategoriaBase = "{{ route('vendedor.categorias.destroy', ['id' => 'ID_PLACEHOLDER']) }}";

function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

/* ============================================================
   EDITAR PRODUCTO
   ============================================================ */
function openEditModal(p) {
    document.getElementById('formEditarProducto').action = urlEditarProductoBase.replace('ID_PLACEHOLDER', p.id_producto || '');

    document.getElementById('edit_nombre').value = p.nombre || '';
    document.getElementById('edit_descripcion').value = p.descripcion || '';
    document.getElementById('edit_id_categoria').value = p.id_categoria || '';

    const preview = document.getElementById('previewEditar');
    const placeholder = document.getElementById('placeholderEditar');

    if (p.imagen) {
        preview.src = '/' + p.imagen;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
    } else {
        preview.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }

    document.getElementById('imagenEditar').value = '';

    openModal('modalEditar');
}

/* ============================================================
   CAMBIAR ESTADO PRODUCTO
   ============================================================ */
function cambiarEstado(id) {
    Swal.fire({
        icon: 'question',
        title: 'Cambiar estado',
        text: '¿Quieres cambiar el estado de este producto?',
        showCancelButton: true,
        confirmButtonColor: '#00875F',
        cancelButtonColor: '#9CA3AF',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        var form = document.getElementById('formToggleProducto');
        form.action = urlToggleProductoBase.replace('ID_PLACEHOLDER', id);
        form.submit();
    });
}

/* ============================================================
   PREVISUALIZAR IMAGEN
   ============================================================ */
function previewImagen(input, previewId, placeholderId) {
    const preview = document.getElementById(previewId);
    const placeholder = document.getElementById(placeholderId);

    if (!input.files || !input.files.length) return;

    const file = input.files[0];

    if (!file.type.startsWith('image/')) {
        Swal.fire({ icon: 'warning', title: 'Archivo inválido', text: 'Selecciona una imagen válida.', confirmButtonColor: '#00875F' });
        input.value = '';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        Swal.fire({ icon: 'warning', title: 'Imagen demasiado grande', text: 'La imagen no puede superar los 2MB.', confirmButtonColor: '#00875F' });
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(file);
}

/* ============================================================
   ELIMINAR PRODUCTO
   ============================================================ */
var productoEliminar = null;

function openDeleteModal(id, nombre) {
    productoEliminar = id;
    document.getElementById('delete_nombre').textContent = nombre;
    openModal('modalEliminar');
}

function confirmarEliminarProducto() {
    if (!productoEliminar) return;

    var form = document.getElementById('formEliminarProducto');
    form.action = urlEliminarProductoBase.replace('ID_PLACEHOLDER', productoEliminar);
    form.submit();
}

/* ============================================================
   EDITAR CATEGORÍA
   ============================================================ */
function openEditCategoriaModal(cat) {
    document.getElementById('formEditarCategoria').action = urlEditarCategoriaBase.replace('ID_PLACEHOLDER', cat.id_categoria || '');
    document.getElementById('edit_tipo_categoria').value = cat.tipo || '';

    closeModal('modalCategorias');
    openModal('modalEditarCategoria');
}

/* ============================================================
   ELIMINAR CATEGORÍA
   ============================================================ */
var categoriaEliminar = null;

function openDeleteCategoriaModal(id, tipo) {
    categoriaEliminar = id;
    document.getElementById('delete_categoria_nombre').textContent = tipo;

    closeModal('modalCategorias');
    openModal('modalEliminarCategoria');
}

function confirmarEliminarCategoria() {
    if (!categoriaEliminar) return;

    var form = document.getElementById('formEliminarCategoria');
    form.action = urlEliminarCategoriaBase.replace('ID_PLACEHOLDER', categoriaEliminar);
    form.submit();
}

/* ============================================================
   CERRAR MODALES
   ============================================================ */
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(function (modal) {
            modal.classList.add('hidden');
        });
        document.body.classList.remove('overflow-hidden');
    }
});

/* ============================================================
   BUSCADOR + PAGINACION -- 8 PRODUCTOS POR PAGINA
   ============================================================ */
const buscador = document.getElementById('buscadorProductos');
const paginacion = document.getElementById('paginacion');
const contador = document.getElementById('contadorResultados');
const tarjetas = Array.from(document.querySelectorAll('.producto-item'));

let paginaActual = 1;
const productosPorPagina = 8;
let productosFiltrados = [...tarjetas];

function actualizarProductos() {
    const inicio = (paginaActual - 1) * productosPorPagina;
    const fin = inicio + productosPorPagina;

    tarjetas.forEach(card => card.style.display = 'none');
    productosFiltrados.slice(inicio, fin).forEach(card => card.style.display = '');

    crearPaginacion();

    if (contador) {
        contador.textContent = productosFiltrados.length
            ? productosFiltrados.length + ' producto' + (productosFiltrados.length !== 1 ? 's' : '')
            : 'Sin resultados';
    }
}

function crearPaginacion() {
    paginacion.innerHTML = '';

    const totalPaginas = Math.ceil(productosFiltrados.length / productosPorPagina);
    if (totalPaginas <= 1) return;

    const btnAnterior = document.createElement('button');
    btnAnterior.type = 'button';
    btnAnterior.innerHTML = '&laquo;';
    btnAnterior.className = 'paginacion-btn paginacion-flecha';
    btnAnterior.disabled = paginaActual === 1;
    btnAnterior.addEventListener('click', function () {
        if (paginaActual === 1) return;
        paginaActual--;
        actualizarProductos();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    paginacion.appendChild(btnAnterior);

    for (let i = 1; i <= totalPaginas; i++) {
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.textContent = i;
        boton.className = 'paginacion-btn' + (i === paginaActual ? ' activo' : '');
        boton.addEventListener('click', function () {
            paginaActual = i;
            actualizarProductos();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        paginacion.appendChild(boton);
    }

    const btnSiguiente = document.createElement('button');
    btnSiguiente.type = 'button';
    btnSiguiente.innerHTML = '&raquo;';
    btnSiguiente.className = 'paginacion-btn paginacion-flecha';
    btnSiguiente.disabled = paginaActual === totalPaginas;
    btnSiguiente.addEventListener('click', function () {
        if (paginaActual === totalPaginas) return;
        paginaActual++;
        actualizarProductos();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    paginacion.appendChild(btnSiguiente);
}

if (buscador) {
    buscador.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();

        productosFiltrados = tarjetas.filter(card => {
            const nombre = card.dataset.nombre || '';
            const categoria = card.dataset.categoria || '';
            return nombre.includes(q) || categoria.includes(q);
        });

        paginaActual = 1;
        actualizarProductos();
    });
}

actualizarProductos();
</script>

@endsection