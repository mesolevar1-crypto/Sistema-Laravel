<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUsuarioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;

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

// TODO: reemplazar por controladores y vistas reales cuando existan.
// Usan una vista placeholder propia (dashboard.proximamente) para no
// depender de $usuarios/$roles, que solo existen en dashboard.admin.
Route::get('/inventario', fn () => view('dashboard.proximamente', [
    'titulo'    => 'Gestionar Inventario',
    'subtitulo' => 'Controla el stock de tu negocio',
]))->name('inventario.index');

Route::get('/productos', fn () => view('dashboard.proximamente', [
    'titulo'    => 'Gestionar Productos',
    'subtitulo' => 'Administra tu catálogo de productos',
]))->name('productos.index');

Route::get('/ventas', fn () => view('dashboard.proximamente', [
    'titulo'    => 'Gestionar Ventas',
    'subtitulo' => 'Consulta y registra tus ventas',
]))->name('ventas.index');

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