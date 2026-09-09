<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador de Cliente.
 *
 * Equivalente al antiguo controllers/ClienteController.php, pero
 * dividido en acciones REST (index, store, update, toggleEstado,
 * destroy) en vez de un switch por $_GET['accion'].
 *
 * El "regresar según el rol" del original (admin -> su panel,
 * vendedor -> el suyo) se resuelve leyendo auth()->user()->rol,
 * igual que antes leía $_SESSION['usuario']['rol'].
 */
class ClienteController extends Controller
{
    // ============================================================
    // LISTADO (con paginación simple, igual que el original)
    // ============================================================
    public function index(Request $request)
    {
        $todos = Cliente::obtenerTodos();

        $porPagina = 5;
        $total     = count($todos);
        $paginas   = max(1, (int) ceil($total / $porPagina));
        $pagina    = max(1, min((int) $request->input('pagina', 1), $paginas));
        $clientes  = array_slice($todos, ($pagina - 1) * $porPagina, $porPagina);

        return view('vista_admin.clientes', compact('clientes', 'pagina', 'paginas', 'total'));
    }

    // ============================================================
    // REGISTRAR
    // ============================================================
    public function store(Request $request)
    {
        $nombre   = trim($request->input('nombre', ''));
        $telefono = trim($request->input('telefono', ''));
        $correo   = trim($request->input('correo', ''));

        if ($nombre === '') {
            return $this->regresarConAlerta('warning', 'Campo obligatorio', 'El nombre del cliente es obligatorio.');
        }

        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return $this->regresarConAlerta('warning', 'Correo inválido', 'El formato del correo electrónico no es válido.');
        }

        if ($correo !== '' && Cliente::existeCorreo($correo)) {
            return $this->regresarConAlerta('error', 'Correo duplicado', 'El correo electrónico ya está registrado.');
        }

        $resultado = Cliente::registrar(compact('nombre', 'telefono', 'correo'));

        if ($resultado === true) {
            return $this->regresarConAlerta('success', '¡Cliente registrado!', 'El cliente fue registrado correctamente.');
        }

        return $this->regresarConAlerta('error', 'Error', $resultado);
    }

    // ============================================================
    // EDITAR
    // ============================================================
    public function update(Request $request, $id)
    {
        $idCliente = (int) $id;
        $nombre    = trim($request->input('nombre', ''));
        $telefono  = trim($request->input('telefono', ''));
        $correo    = trim($request->input('correo', ''));

        if ($idCliente <= 0 || $nombre === '') {
            return $this->regresarConAlerta('warning', 'Datos incompletos', 'El nombre del cliente es obligatorio.');
        }

        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return $this->regresarConAlerta('warning', 'Correo inválido', 'El correo electrónico no es válido.');
        }

        $resultado = Cliente::editarCompleto($idCliente, $nombre, $telefono, $correo);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', '¡Actualizado!', 'Cliente actualizado correctamente.');
        }

        return $this->regresarConAlerta('error', 'Error', $resultado);
    }

    // ============================================================
    // ACTIVAR / DESACTIVAR
    // ============================================================
    public function toggleEstado($id)
    {
        $idCliente = (int) $id;

        if ($idCliente <= 0) {
            return $this->regresarConAlerta('error', 'Error', 'Cliente no válido.');
        }

        $cliente = Cliente::obtenerPorId($idCliente);

        if (!$cliente) {
            return $this->regresarConAlerta('error', 'Error', 'Cliente no encontrado.');
        }

        $resultado = Cliente::cambiarEstado($idCliente);

        if ($resultado === true) {
            // $cliente trae el estado ANTES del cambio, así calculamos
            // el mensaje igual que el original (que primero invertía
            // el estado y luego lo usaba para el texto de la alerta).
            $quedoActivo = $cliente['estado'] !== 'activo';

            return $this->regresarConAlerta(
                'success',
                'Estado actualizado',
                'El cliente fue ' . ($quedoActivo ? 'activado.' : 'desactivado.')
            );
        }

        return $this->regresarConAlerta('error', 'Error', $resultado);
    }

    // ============================================================
    // ELIMINAR
    // ============================================================
    public function destroy($id)
    {
        $idCliente = (int) $id;

        if ($idCliente <= 0) {
            return $this->regresarConAlerta('error', 'Error', 'Cliente no válido.');
        }

        $resultado = Cliente::eliminarCliente($idCliente);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', 'Cliente eliminado', 'El cliente fue eliminado correctamente.');
        }

        return $this->regresarConAlerta('error', 'No se puede eliminar', $resultado);
    }

    // ============================================================
    // Redirige según el rol del usuario autenticado, con alerta flash
    // (equivalente a regresarConAlerta() del controlador original)
    // ============================================================
    private function regresarConAlerta(string $icon, string $title, string $text)
    {
        $rol = strtolower(trim(optional(Auth::user())->rol ?? ''));

        $ruta = $rol === 'vendedor' ? 'vendedor.clientes' : 'admin.clientes';

        return redirect()->route($ruta)->with('alert', [
            'icon'  => $icon,
            'title' => $title,
            'text'  => $text,
        ]);
    }
}