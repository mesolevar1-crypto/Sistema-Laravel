{{--
    ============================================================
    Sidebar del Administrador
    Orden: Inicio > Usuarios > Clientes > Proveedores > Compras
           > Inventario > Productos > Ventas > Reportes
    ============================================================
--}}

@php
    $usuario    = auth()->user();
    $rolDisplay = ucfirst(optional($usuario->rol)->nombre ?? '');
@endphp

<style>
    .nav-item {
        color: rgba(255,255,255,.80);
        transition: background .18s, color .18s;
        position: relative; overflow: hidden;
        text-decoration: none;
        display: flex; align-items: center; gap: 12px;
        padding: 11px 16px; border-radius: 10px;
        font-size: .92rem; font-weight: 500;
    }
    .nav-item:hover  { background: rgba(255,255,255,.12); color: #fff; }
    .nav-item.activo { background: #00875F; color: #fff; font-weight: 700; }
    .nav-item.activo::before {
        content: '';
        position: absolute; left: 0; top: 0; bottom: 0;
        width: 4px; background: #fff;
        border-radius: 0 4px 4px 0;
    }
    .nav-item i { width: 20px; text-align: center; font-size: 1rem; }

    .header-top {
        background: #fff; border-bottom: 1px solid #E5E7EB;
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 32px; height: 64px;
        position: sticky; top: 0; z-index: 30;
    }
    .header-titulo {
        font-family: 'DM Serif Display', serif;
        font-size: 1.4rem; color: #171717; font-weight: 700;
    }

    .user-menu-wrap { position: relative; }
    .user-trigger {
        display: flex; align-items: center; gap: 10px; cursor: pointer;
        padding: 7px 14px; border-radius: 10px;
        border: 1px solid #E5E7EB; background: #fff;
        transition: background .18s, border-color .18s; user-select: none;
    }
    .user-trigger:hover { background: #F8F8F8; border-color: #61D0A7; }
    .user-avatar {
        width: 36px; height: 36px; background: #DDF5EC; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .85rem; font-weight: 700; color: #01614B; flex-shrink: 0;
    }
    .user-info-nombre { font-size: .88rem; font-weight: 700; color: #171717; line-height: 1.2; }
    .user-info-rol    { font-size: .72rem; color: #5F6673; text-transform: uppercase; letter-spacing: .04em; }
    .user-chevron     { font-size: .7rem; color: #5F6673; margin-left: 4px; transition: transform .2s; }

    .user-dropdown {
        position: absolute; top: calc(100% + 8px); right: 0;
        background: #fff; border: 1px solid #E5E7EB; border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,.10);
        min-width: 180px; overflow: hidden; display: none; z-index: 100;
    }
    .user-dropdown.abierto { display: block; }
    .dropdown-item {
        display: flex; align-items: center; gap: 10px;
        padding: 11px 16px; font-size: .88rem; color: #171717;
        font-weight: 500; text-decoration: none;
        transition: background .15s; cursor: pointer;
        border: none; background: none; width: 100%; text-align: left;
    }
    .dropdown-item:hover { background: #F8F8F8; }
    .dropdown-item.rojo  { color: #E53935; }
    .dropdown-item.rojo:hover { background: #fde8e8; }
    .dropdown-divider { height: 1px; background: #E5E7EB; margin: 2px 0; }
</style>

<!-- ════════════════════════════════════
     SIDEBAR
════════════════════════════════════ -->
<aside style="width:240px;min-width:240px;background:#01614B;"
       class="flex flex-col shadow-xl relative z-20">

    <div style="border-bottom:1px solid rgba(255,255,255,.15);"
         class="flex flex-col items-center justify-center py-7 px-4">
        <div class="bg-white rounded-full p-2 shadow mb-2">
            <img src="{{ asset('img/icon.png') }}" width="38" height="38" alt="VentaNet">
        </div>
        <span class="font-serif-ventanet text-white text-2xl leading-none tracking-tight">VentaNet</span>
        <span class="text-xs mt-1" style="color:rgba(255,255,255,.55);">Sistema de Gestión</span>
    </div>

    <nav class="flex-1 px-3 py-5 flex flex-col gap-0.5 overflow-y-auto">

        <a href="{{ route('inicio.index') }}"
           class="nav-item {{ request()->routeIs('inicio.*') ? 'activo' : '' }}">
            <i class="fas fa-home"></i><span>Inicio</span>
        </a>

        <a href="{{ route('admin.usuarios') }}"
           class="nav-item {{ request()->routeIs('admin.usuarios*') ? 'activo' : '' }}">
            <i class="fas fa-users"></i><span>Usuarios</span>
        </a>

        <a href="{{ route('clientes.index') }}"
           class="nav-item {{ request()->routeIs('clientes.*') ? 'activo' : '' }}">
            <i class="fas fa-user-tie"></i><span>Clientes</span>
        </a>

        <a href="{{ route('proveedores.index') }}"
           class="nav-item {{ request()->routeIs('proveedores.*') ? 'activo' : '' }}">
            <i class="fas fa-truck"></i><span>Proveedores</span>
        </a>

        <a href="{{ route('compras.index') }}"
           class="nav-item {{ request()->routeIs('compras.*') ? 'activo' : '' }}">
            <i class="fas fa-shopping-bag"></i><span>Compras</span>
        </a>

        <a href="{{ route('inventario.index') }}"
           class="nav-item {{ request()->routeIs('inventario.*') ? 'activo' : '' }}">
            <i class="fas fa-warehouse"></i><span>Inventario</span>
        </a>

        <a href="{{ route('productos.index') }}"
           class="nav-item {{ request()->routeIs('productos.*') ? 'activo' : '' }}">
            <i class="fas fa-box-open"></i><span>Productos</span>
        </a>

        <a href="{{ route('ventas.index') }}"
           class="nav-item {{ request()->routeIs('ventas.*') && !request()->routeIs('inventario.*') && !request()->routeIs('reportes.*') ? 'activo' : '' }}">
            <i class="fas fa-cash-register"></i><span>Ventas</span>
        </a>

        <a href="{{ route('reportes.index') }}"
           class="nav-item {{ request()->routeIs('reportes.*') ? 'activo' : '' }}">
            <i class="fas fa-chart-line"></i><span>Reportes</span>
        </a>

    </nav>

</aside>

<!-- ════════════════════════════════════
     CONTENIDO PRINCIPAL
════════════════════════════════════ -->
<main class="flex-1 flex flex-col overflow-hidden" style="min-width:0;">

    <header class="header-top">
        <span class="header-titulo">{{ $titulo ?? 'Inicio' }}</span>

        <div class="user-menu-wrap">
            <div class="user-trigger" id="userTrigger" onclick="toggleUserMenu()">
                <div class="user-avatar">
                    {{ Str::upper(Str::substr($usuario->persona->nombre ?? '', 0, 2)) }}
                </div>
                <div>
                    <p class="user-info-nombre">{{ $usuario->persona->nombre ?? '' }}</p>
                    <p class="user-info-rol">{{ $rolDisplay }}</p>
                </div>
                <i class="fas fa-chevron-down user-chevron" id="userChevron"></i>
            </div>

            <div class="user-dropdown" id="userDropdown">
                <a href="#" class="dropdown-item">
                    <i class="fas fa-user" style="color:#00875F;width:16px;text-align:center;"></i>
                    Mi perfil
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="dropdown-item rojo">
                        <i class="fas fa-sign-out-alt" style="width:16px;text-align:center;"></i>
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </header>

    <section class="flex-1 overflow-y-auto p-8" style="background:#F8F8F8;">

<script>
    function toggleUserMenu() {
        var d = document.getElementById('userDropdown');
        var c = document.getElementById('userChevron');
        var open = d.classList.toggle('abierto');
        c.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
    }
    document.addEventListener('click', function(e) {
        var t = document.getElementById('userTrigger');
        var d = document.getElementById('userDropdown');
        if (t && d && !t.contains(e.target) && !d.contains(e.target)) {
            d.classList.remove('abierto');
            var c = document.getElementById('userChevron');
            if (c) c.style.transform = 'rotate(0deg)';
        }
    });
</script>