<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUsuarioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;

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

// TODO: reemplazar por controlador y vista real cuando exista.
Route::get('/reportes', fn () => view('dashboard.proximamente', [
    'titulo'    => 'Reportes',
    'subtitulo' => 'Consulta reportes de tu negocio',
]))->name('reportes.index');

/*
|--------------------------------------------------------------------------
| DASHBOARD VENDEDOR (TEMPORAL: prueba de login "Hola Vendedor")
|--------------------------------------------------------------------------
*/

Route::get('/vendedor', function () {

    $usuario = auth()->user();

    return response("Hola Vendedor, tu nombre es: " . ($usuario->persona->nombre ?? 'sin nombre'));

    // ------------------------------------------------------
    // Versión completa (descomentar cuando la prueba funcione):
    // ------------------------------------------------------
    //
    // return view('dashboard.vendedor');

})->name('vendedor.dashboard');

// TODO: reemplazar por controladores reales cuando existan
Route::get('/vendedor/ventas', fn () => view('dashboard.vendedor'))->name('vendedor.ventas');
Route::get('/vendedor/clientes', fn () => view('dashboard.vendedor'))->name('vendedor.clientes');
Route::get('/vendedor/productos', fn () => view('dashboard.vendedor'))->name('vendedor.productos');
Route::get('/vendedor/inventario', fn () => view('dashboard.vendedor'))->name('vendedor.inventario');
Route::get('/vendedor/reporte', fn () => view('dashboard.vendedor'))->name('vendedor.reporte');

/*
|--------------------------------------------------------------------------
| CERRAR SESIÓN
|--------------------------------------------------------------------------
*/

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');