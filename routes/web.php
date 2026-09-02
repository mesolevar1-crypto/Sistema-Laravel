<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUsuarioController;

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

Route::get('/admin', [AdminUsuarioController::class, 'index'])->name('inicio.index');
Route::get('/admin/usuarios', [AdminUsuarioController::class, 'index'])->name('admin.usuarios');

Route::post('/admin/usuarios', [AdminUsuarioController::class, 'crear'])->name('admin.usuarios.crear');
Route::post('/admin/usuarios/{id}/toggle', [AdminUsuarioController::class, 'toggleEstado'])->name('admin.usuarios.toggle');
Route::post('/admin/usuarios/{id}/editar', [AdminUsuarioController::class, 'editar'])->name('admin.usuarios.editar');
Route::delete('/admin/usuarios/{id}', [AdminUsuarioController::class, 'eliminar'])->name('admin.usuarios.eliminar');

// TODO: reemplazar por controladores reales cuando existan
Route::get('/clientes', fn () => view('dashboard.admin'))->name('clientes.index');
Route::get('/proveedores', fn () => view('dashboard.admin'))->name('proveedores.index');
Route::get('/compras', fn () => view('dashboard.admin'))->name('compras.index');
Route::get('/inventario', fn () => view('dashboard.admin'))->name('inventario.index');
Route::get('/productos', fn () => view('dashboard.admin'))->name('productos.index');
Route::get('/ventas', fn () => view('dashboard.admin'))->name('ventas.index');
Route::get('/reportes', fn () => view('dashboard.admin'))->name('reportes.index');

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