<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUsuarioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Models\Reporte;

/*
|--------------------------------------------------------------------------
| WELCOME
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.procesar');

/*
|--------------------------------------------------------------------------
| REGISTRO
|--------------------------------------------------------------------------
*/

Route::get('/registro', function () {
    return view('autenticacion.registre');
})->name('registro');

Route::post('/registro', [UsuarioController::class, 'registrar'])->name('registro.guardar');

/*
|--------------------------------------------------------------------------
| DASHBOARD ADMINISTRADOR
|--------------------------------------------------------------------------
*/

// Panel de Inicio (KPIs): va directo a la vista, sin controlador.
// La vista llama internamente a App\Models\Inicio.
Route::get('/admin', fn () => view('vista_admin.inicio'))
    ->middleware(['auth'])
    ->name('inicio.index');

Route::get('/admin/usuarios', [AdminUsuarioController::class, 'index'])->name('admin.usuarios');

Route::post('/admin/usuarios', [AdminUsuarioController::class, 'crear'])->name('admin.usuarios.crear');
Route::post('/admin/usuarios/{id}/toggle', [AdminUsuarioController::class, 'toggleEstado'])->name('admin.usuarios.toggle');
Route::post('/admin/usuarios/{id}/editar', [AdminUsuarioController::class, 'editar'])->name('admin.usuarios.editar');
Route::delete('/admin/usuarios/{id}', [AdminUsuarioController::class, 'eliminar'])->name('admin.usuarios.eliminar');

/*
|--------------------------------------------------------------------------
| CLIENTES (Administrador) — con controlador
| Todo bajo /admin/clientes (plural), consistente con /admin/usuarios.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/clientes', [ClienteController::class, 'index'])->name('admin.clientes');
    Route::post('/admin/clientes', [ClienteController::class, 'store'])->name('admin.clientes.store');
    Route::post('/admin/clientes/{id}/editar', [ClienteController::class, 'update'])->name('admin.clientes.update');
    Route::post('/admin/clientes/{id}/toggle', [ClienteController::class, 'toggleEstado'])->name('admin.clientes.toggle');
    Route::delete('/admin/clientes/{id}', [ClienteController::class, 'destroy'])->name('admin.clientes.destroy');
});

/*
|--------------------------------------------------------------------------
| PROVEEDORES (Administrador) — con controlador
| Todo bajo /admin/proveedores, consistente con /admin/clientes.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/proveedores', [ProveedorController::class, 'index'])->name('admin.proveedores');
    Route::post('/admin/proveedores', [ProveedorController::class, 'store'])->name('admin.proveedores.store');
    Route::post('/admin/proveedores/{id}/editar', [ProveedorController::class, 'update'])->name('admin.proveedores.update');
    Route::post('/admin/proveedores/{id}/toggle', [ProveedorController::class, 'toggleEstado'])->name('admin.proveedores.toggle');
    Route::delete('/admin/proveedores/{id}', [ProveedorController::class, 'destroy'])->name('admin.proveedores.destroy');
});

// Alias legado: si algo en el proyecto aún enlaza a /proveedores (nombre
// antiguo 'proveedores.index'), lo mandamos a la ruta real del controlador
// en vez de renderizar una vista placeholder rota.
Route::get('/proveedores', fn () => redirect()->route('admin.proveedores'))->name('proveedores.index');

/*
|--------------------------------------------------------------------------
| COMPRAS (Administrador) — con controlador
| Todo bajo /admin/compras, consistente con clientes y proveedores.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/compras', [CompraController::class, 'index'])->name('admin.compras');
    Route::post('/admin/compras', [CompraController::class, 'store'])->name('admin.compras.store');
    Route::get('/admin/compras/{id}/detalle', [CompraController::class, 'detalle'])->name('admin.compras.detalle');
    Route::delete('/admin/compras/{id}', [CompraController::class, 'destroy'])->name('admin.compras.destroy');
});

// Alias legado: si algo en el proyecto aún enlaza a /compras (nombre
// antiguo 'compras.index'), lo mandamos a la ruta real del controlador.
Route::get('/compras', fn () => redirect()->route('admin.compras'))->name('compras.index');

/*
|--------------------------------------------------------------------------
| INVENTARIO (Administrador) — con controlador
| Todo bajo /admin/inventario, consistente con los demás módulos.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/inventario', [InventarioController::class, 'index'])->name('admin.inventario');
    Route::post('/admin/inventario/{id}/actualizar', [InventarioController::class, 'actualizar'])->name('admin.inventario.actualizar');
});

// Alias legado: si algo en el proyecto aún enlaza a /inventario (nombre
// antiguo 'inventario.index'), lo mandamos a la ruta real del controlador.
Route::get('/inventario', fn () => redirect()->route('admin.inventario'))->name('inventario.index');

/*
|--------------------------------------------------------------------------
| PRODUCTOS Y CATEGORÍAS (Administrador) — con controlador
| Todo bajo /admin/productos y /admin/categorias.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/productos', [ProductoController::class, 'index'])->name('admin.productos');
    Route::post('/admin/productos', [ProductoController::class, 'store'])->name('admin.productos.store');
    Route::post('/admin/productos/{id}/editar', [ProductoController::class, 'update'])->name('admin.productos.update');
    Route::post('/admin/productos/{id}/toggle', [ProductoController::class, 'toggleEstado'])->name('admin.productos.toggle');
    Route::delete('/admin/productos/{id}', [ProductoController::class, 'destroy'])->name('admin.productos.destroy');

    Route::post('/admin/categorias', [ProductoController::class, 'storeCategoria'])->name('admin.categorias.store');
    Route::post('/admin/categorias/{id}/editar', [ProductoController::class, 'updateCategoria'])->name('admin.categorias.update');
    Route::delete('/admin/categorias/{id}', [ProductoController::class, 'destroyCategoria'])->name('admin.categorias.destroy');
});

// Alias legado: si algo en el proyecto aún enlaza a /productos (nombre
// antiguo 'productos.index'), lo mandamos a la ruta real del controlador.
Route::get('/productos', fn () => redirect()->route('admin.productos'))->name('productos.index');

/*
|--------------------------------------------------------------------------
| VENTAS (Administrador) — con controlador
| Todo bajo /admin/ventas, consistente con los demás módulos.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/ventas', [VentaController::class, 'index'])->name('admin.ventas');
    Route::post('/admin/ventas', [VentaController::class, 'store'])->name('admin.ventas.store');
    Route::get('/admin/ventas/{id}/detalle', [VentaController::class, 'detalle'])->name('admin.ventas.detalle');
    Route::post('/admin/ventas/{id}/anular', [VentaController::class, 'anular'])->name('admin.ventas.anular');
    Route::post('/admin/ventas/{id}/reactivar', [VentaController::class, 'reactivar'])->name('admin.ventas.reactivar');
    Route::get('/admin/ventas/{id}/factura', [VentaController::class, 'factura'])->name('admin.ventas.factura');
});

// Alias legado: si algo en el proyecto aún enlaza a /ventas (nombre
// antiguo 'ventas.index'), lo mandamos a la ruta real del controlador.
Route::get('/ventas', fn () => redirect()->route('admin.ventas'))->name('ventas.index');

/*
|--------------------------------------------------------------------------
| REPORTES (Administrador) — SIN controlador, van directo a la vista.
| Cada ruta llama al modelo Reporte directamente, igual que el
| index.php / ventas.php / compras.php / inventario.php originales
| (que tampoco tenían un controlador propio).
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // ── Índice: KPIs generales ──
    Route::get('/admin/reportes', function () {
        $ventasHoy    = Reporte::ventasHoy();
        $ventasMes    = Reporte::ventasMes();
        $comprasMes   = Reporte::comprasMes();
        $gananciasMes = Reporte::gananciasMes();
        $stockBajo    = Reporte::contarStockBajo();
        $agotados     = Reporte::contarAgotados();

        return view('vista_admin.reporte', compact(
            'ventasHoy', 'ventasMes', 'comprasMes', 'gananciasMes', 'stockBajo', 'agotados'
        ));
    })->name('admin.reportes');

    // ── Reporte de ventas ──
    Route::get('/admin/reportes/ventas', function (Request $request) {
        $desde     = $request->input('desde', now()->startOfMonth()->toDateString());
        $hasta     = $request->input('hasta', now()->toDateString());
        $idUsuario = (int) $request->input('id_usuario', 0);

        if ($desde > $hasta) {
            $desde = $hasta;
        }

        $agrupacion = $request->input('agrupacion', 'dia');
        if (!in_array($agrupacion, ['dia', 'semana', 'mes'], true)) {
            $agrupacion = 'dia';
        }

        $ventas           = Reporte::reporteVentas($desde, $hasta, $idUsuario);
        $usuarios         = Reporte::listaUsuarios();
        $gananciasPeriodo = Reporte::gananciasPorPeriodo($desde, $hasta, $agrupacion, $idUsuario);

        $totalRegistros = count($ventas);
        $totalVendido   = array_sum(array_column($ventas, 'total'));
        $totalGanancia  = array_sum(array_column($ventas, 'ganancia'));
        $margenGeneral  = $totalVendido > 0 ? round(($totalGanancia / $totalVendido) * 100, 1) : 0;

        $chartLabels   = array_map(fn ($r) => $r['periodo_label'], $gananciasPeriodo);
        $chartVendido  = array_map(fn ($r) => round((float) $r['total_vendido'], 2), $gananciasPeriodo);
        $chartGanancia = array_map(fn ($r) => round((float) $r['ganancia'], 2), $gananciasPeriodo);

        $etiquetaAgrupacion = [
            'dia'    => 'por día',
            'semana' => 'por semana',
            'mes'    => 'por mes',
        ][$agrupacion];

        $nombreArchivoPDF = 'Reporte_Ventas_' . date('Y-m-d', strtotime($desde)) . '_a_' . date('Y-m-d', strtotime($hasta));

        return view('vista_admin.reporte_ventas', compact(
            'desde', 'hasta', 'idUsuario', 'agrupacion',
            'ventas', 'usuarios', 'gananciasPeriodo',
            'totalRegistros', 'totalVendido', 'totalGanancia', 'margenGeneral',
            'chartLabels', 'chartVendido', 'chartGanancia',
            'etiquetaAgrupacion', 'nombreArchivoPDF'
        ));
    })->name('admin.reportes.ventas');

    // ── Reporte de compras ──
    Route::get('/admin/reportes/compras', function (Request $request) {
        $desde  = $request->input('desde', now()->startOfMonth()->toDateString());
        $hasta  = $request->input('hasta', now()->toDateString());
        $idProv = (int) $request->input('id_proveedor', 0);

        if ($desde > $hasta) {
            $desde = $hasta;
        }

        $agrupacion = $request->input('agrupacion', 'dia');
        if (!in_array($agrupacion, ['dia', 'semana', 'mes'], true)) {
            $agrupacion = 'dia';
        }

        $compras     = Reporte::reporteCompras($desde, $hasta, $idProv);
        $proveedores = Reporte::listaProveedores();

        $totalRegistros    = count($compras);
        $totalComprado     = array_sum(array_column($compras, 'total'));
        $promedioCompra    = $totalRegistros > 0 ? round($totalComprado / $totalRegistros) : 0;
        $proveedoresUnicos = count(array_unique(array_filter(array_column($compras, 'proveedor'))));

        // Agrupar compras por periodo para el gráfico (día/semana/mes).
        $meses = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
            '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
            '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
        ];

        $grupos = [];
        foreach ($compras as $c) {
            $ts = strtotime($c['fecha']);
            switch ($agrupacion) {
                case 'semana':
                    $key   = date('o', $ts) . '-' . date('W', $ts);
                    $label = 'Sem. ' . date('W', $ts) . ' · ' . date('Y', $ts);
                    break;
                case 'mes':
                    $key   = date('Y-m', $ts);
                    $label = $meses[date('m', $ts)] . ' ' . date('Y', $ts);
                    break;
                default:
                    $key   = date('Y-m-d', $ts);
                    $label = date('d/m', $ts);
            }
            if (!isset($grupos[$key])) {
                $grupos[$key] = ['label' => $label, 'orden' => $ts, 'total' => 0.0, 'cantidad' => 0];
            }
            $grupos[$key]['total']    += (float) $c['total'];
            $grupos[$key]['cantidad'] += 1;
        }
        uasort($grupos, fn ($a, $b) => $a['orden'] <=> $b['orden']);
        $comprasPeriodo = array_values($grupos);

        $chartLabels = array_map(fn ($r) => $r['label'], $comprasPeriodo);
        $chartTotal  = array_map(fn ($r) => round($r['total'], 2), $comprasPeriodo);
        $chartCant   = array_map(fn ($r) => $r['cantidad'], $comprasPeriodo);

        $etiquetaAgrupacion = [
            'dia'    => 'por día',
            'semana' => 'por semana',
            'mes'    => 'por mes',
        ][$agrupacion];

        $nombreArchivoPDF = 'Reporte_Compras_' . date('Y-m-d', strtotime($desde)) . '_a_' . date('Y-m-d', strtotime($hasta));

        return view('vista_admin.reporte_compras', compact(
            'desde', 'hasta', 'idProv', 'agrupacion',
            'compras', 'proveedores',
            'totalRegistros', 'totalComprado', 'promedioCompra', 'proveedoresUnicos',
            'comprasPeriodo', 'chartLabels', 'chartTotal', 'chartCant',
            'etiquetaAgrupacion', 'nombreArchivoPDF'
        ));
    })->name('admin.reportes.compras');

    // ── Reporte de inventario ──
    Route::get('/admin/reportes/inventario', function (Request $request) {
        $buscar = trim((string) $request->input('buscar', ''));
        $idCat  = (int) $request->input('id_categoria', 0);
        $estado = trim((string) $request->input('estado', ''));

        $inventario = Reporte::reporteInventario($buscar, $idCat, $estado);
        $categorias = Reporte::listaCategorias();

        $totalProductos = count($inventario);
        $totalUnidades  = 0;
        $totalBajo      = 0;
        $totalAgotado   = 0;

        foreach ($inventario as $fila) {
            $stockActual = (int) $fila['stock_actual'];
            $stockMinimo = (int) $fila['stock_minimo'];
            $totalUnidades += $stockActual;

            if ($stockActual === 0) {
                $totalAgotado++;
            } elseif ($stockActual <= $stockMinimo) {
                $totalBajo++;
            }
        }

        $nombreArchivoPDF = 'Reporte_Inventario_' . date('Y-m-d');

        return view('vista_admin.reporte_inventario', compact(
            'buscar', 'idCat', 'estado', 'inventario', 'categorias',
            'totalProductos', 'totalUnidades', 'totalBajo', 'totalAgotado',
            'nombreArchivoPDF'
        ));
    })->name('admin.reportes.inventario');
});

// Alias legado: si algo en el proyecto aún enlaza a /reportes (nombre
// antiguo 'reportes.index'), lo mandamos a la ruta real.
Route::get('/reportes', fn () => redirect()->route('admin.reportes'))->name('reportes.index');



/*
|--------------------------------------------------------------------------
| DASHBOARD VENDEDOR
|--------------------------------------------------------------------------
*/

// Panel de Inicio (KPIs del vendedor): usa la vista real que ya existe
// en resources/views/dashboard/vendedor.blade.php
Route::get('/vendedor', fn () => view('dashboard.vendedor'))
    ->middleware(['auth'])
    ->name('vendedor.inicio');

Route::middleware(['auth'])->group(function () {
    Route::get('/vendedor/ventas', [VentaController::class, 'vendedorIndex'])->name('vendedor.ventas');
    Route::post('/vendedor/ventas', [VentaController::class, 'store'])->name('vendedor.ventas.store');
    Route::get('/vendedor/ventas/{id}/detalle', [VentaController::class, 'detalle'])->name('vendedor.ventas.detalle');
    Route::post('/vendedor/ventas/{id}/anular', [VentaController::class, 'anular'])->name('vendedor.ventas.anular');
    Route::post('/vendedor/ventas/{id}/reactivar', [VentaController::class, 'reactivar'])->name('vendedor.ventas.reactivar');
    Route::get('/vendedor/ventas/{id}/factura', [VentaController::class, 'factura'])->name('vendedor.ventas.factura');
});

// CLIENTES (Vendedor) — antes solo existía el listado; se agregan
// crear/editar/activar-desactivar/eliminar, igual que en el panel admin.
Route::middleware(['auth'])->group(function () {
    Route::get('/vendedor/clientes', [ClienteController::class, 'vendedorIndex'])->name('vendedor.clientes');
    Route::post('/vendedor/clientes', [ClienteController::class, 'store'])->name('vendedor.clientes.store');
    Route::post('/vendedor/clientes/{id}/editar', [ClienteController::class, 'update'])->name('vendedor.clientes.update');
    Route::post('/vendedor/clientes/{id}/toggle', [ClienteController::class, 'toggleEstado'])->name('vendedor.clientes.toggle');
    Route::delete('/vendedor/clientes/{id}', [ClienteController::class, 'destroy'])->name('vendedor.clientes.destroy');
});

/*
|--------------------------------------------------------------------------
| PRODUCTOS Y CATEGORÍAS (Vendedor)
| Mismos métodos del controlador que usa el admin; el propio
| ProductoController valida que el producto pertenezca al vendedor
| autenticado cuando la ruta empieza por "vendedor.".
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/vendedor/productos', [ProductoController::class, 'vendedorIndex'])->name('vendedor.productos');
    Route::post('/vendedor/productos', [ProductoController::class, 'store'])->name('vendedor.productos.store');
    Route::post('/vendedor/productos/{id}/editar', [ProductoController::class, 'update'])->name('vendedor.productos.update');
    Route::post('/vendedor/productos/{id}/toggle', [ProductoController::class, 'toggleEstado'])->name('vendedor.productos.toggle');
    Route::delete('/vendedor/productos/{id}', [ProductoController::class, 'destroy'])->name('vendedor.productos.destroy');

    Route::post('/vendedor/categorias', [ProductoController::class, 'storeCategoria'])->name('vendedor.categorias.store');
    Route::post('/vendedor/categorias/{id}/editar', [ProductoController::class, 'updateCategoria'])->name('vendedor.categorias.update');
    Route::delete('/vendedor/categorias/{id}', [ProductoController::class, 'destroyCategoria'])->name('vendedor.categorias.destroy');
});

Route::get('/vendedor/inventario', [InventarioController::class, 'vendedorIndex'])->name('vendedor.inventario');
Route::get('/vendedor/reporte', [ReporteController::class, 'vendedorIndex'])->name('vendedor.reporte');

/*
|--------------------------------------------------------------------------
| CERRAR SESIÓN
|--------------------------------------------------------------------------
*/

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');