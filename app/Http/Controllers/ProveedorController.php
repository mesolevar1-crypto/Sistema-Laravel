<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

/**
 * Controlador de Proveedor.
 *
 * Equivalente al antiguo controllers/ProveedorController.php, pero
 * dividido en acciones REST (index, store, update, toggleEstado,
 * destroy) en vez de un switch por $_GET['accion'].
 */
class ProveedorController extends Controller
{
    // ============================================================
    // LISTADO (con paginación simple, igual que el original)
    // ============================================================
    public function index(Request $request)
    {
        $todos = Proveedor::obtenerTodos();

        $porPagina = 5;
        $total     = count($todos);
        $paginas   = max(1, (int) ceil($total / $porPagina));
        $pagina    = max(1, min((int) $request->input('pagina', 1), $paginas));
        $proveedores = array_slice($todos, ($pagina - 1) * $porPagina, $porPagina);

        return view('vista_admin.proveedores', compact('proveedores', 'pagina', 'paginas', 'total'));
    }

    // ============================================================
    // REGISTRAR
    // ============================================================
    public function store(Request $request)
    {
        $nombre             = trim($request->input('nombre', ''));
        $telefono           = trim($request->input('telefono', ''));
        $correo             = trim($request->input('correo', ''));
        $frecuenciaEntrega  = trim($request->input('frecuencia_entrega', ''));

        if ($nombre === '') {
            return $this->regresarConAlerta('warning', 'Campo requerido', 'El nombre del proveedor es obligatorio.');
        }

        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return $this->regresarConAlerta('warning', 'Correo inválido', 'El formato del correo no es válido.');
        }

        $resultado = Proveedor::registrar([
            'nombre'             => $nombre,
            'telefono'           => $telefono,
            'correo'             => $correo,
            'frecuencia_entrega' => $frecuenciaEntrega,
        ]);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', '¡Proveedor registrado!', 'Proveedor agregado correctamente.');
        }

        return $this->regresarConAlerta('error', 'Error', $resultado);
    }

    // ============================================================
    // EDITAR
    // ============================================================
    public function update(Request $request, $id)
    {
        $idProveedor        = (int) $id;
        $nombre              = trim($request->input('nombre', ''));
        $telefono            = trim($request->input('telefono', ''));
        $correo              = trim($request->input('correo', ''));
        $frecuenciaEntrega   = trim($request->input('frecuencia_entrega', ''));

        if ($idProveedor <= 0 || $nombre === '') {
            return $this->regresarConAlerta('warning', 'Datos inválidos', 'El nombre es obligatorio.');
        }

        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return $this->regresarConAlerta('warning', 'Correo inválido', 'El formato del correo no es válido.');
        }

        $resultado = Proveedor::editar($idProveedor, $nombre, $telefono, $correo, $frecuenciaEntrega);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', '¡Actualizado!', 'Proveedor actualizado correctamente.');
        }

        return $this->regresarConAlerta('error', 'Error', $resultado);
    }

    // ============================================================
    // ACTIVAR / DESACTIVAR
    // ============================================================
    public function toggleEstado($id)
    {
        $idProveedor = (int) $id;

        if ($idProveedor <= 0) {
            return $this->regresarConAlerta('error', 'Error', 'Proveedor no válido.');
        }

        $proveedor = Proveedor::obtenerPorId($idProveedor);

        if (!$proveedor) {
            return $this->regresarConAlerta('error', 'Error', 'Proveedor no encontrado.');
        }

        $resultado = Proveedor::toggleEstado($idProveedor);

        if ($resultado === true) {
            $quedoActivo = $proveedor['estado'] !== 'activo';

            return $this->regresarConAlerta(
                'success',
                '¡Estado actualizado!',
                'El proveedor fue ' . ($quedoActivo ? 'activado' : 'desactivado') . ' correctamente.'
            );
        }

        return $this->regresarConAlerta('error', 'Error', $resultado);
    }

    // ============================================================
    // ELIMINAR
    // ============================================================
    public function destroy($id)
    {
        $idProveedor = (int) $id;

        if ($idProveedor <= 0) {
            return $this->regresarConAlerta('error', 'Error', 'Proveedor no válido.');
        }

        $resultado = Proveedor::eliminar($idProveedor);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', 'Eliminado', 'Proveedor eliminado correctamente.');
        }

        return $this->regresarConAlerta('error', 'Error', $resultado);
    }

    // ============================================================
    // Redirige al panel de proveedores con alerta flash
    // ============================================================
    private function regresarConAlerta(string $icon, string $title, string $text)
    {
        return redirect()->route('admin.proveedores')->with('alert', [
            'icon'  => $icon,
            'title' => $title,
            'text'  => $text,
        ]);
    }
}