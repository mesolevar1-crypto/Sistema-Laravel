<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

/**
 * Controlador de Cliente.
 *
 * index()         -> Administrador: ve TODOS los clientes.
 * vendedorIndex()  -> Vendedor: ve solo LOS SUYOS.
 *
 * store/update/toggleEstado/destroy son compartidos entre ambos
 * paneles. Cuando la petición llega por una ruta "vendedor.*", se
 * valida que el cliente sobre el que se actúa le pertenezca al
 * vendedor autenticado (mismo patrón usado en VentaController).
 */
class ClienteController extends Controller
{
    // ============================================================
    // LISTADO — ADMIN (con paginación simple, igual que el original)
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
    // LISTADO — VENDEDOR (solo SUS propios clientes)
    // ============================================================
    public function vendedorIndex(Request $request)
    {
        $idUsuario = (int) (auth()->id() ?? 0);

        $todos = Cliente::obtenerTodos($idUsuario);

        $porPagina = 5;
        $total     = count($todos);
        $paginas   = max(1, (int) ceil($total / $porPagina));
        $pagina    = max(1, min((int) $request->input('pagina', 1), $paginas));
        $clientes  = array_slice($todos, ($pagina - 1) * $porPagina, $porPagina);

        return view('vista_vendedor.clientes', compact('clientes', 'pagina', 'paginas', 'total'));
    }

    // ============================================================
    // REGISTRAR
    // El cliente queda asociado a quien lo crea (admin o vendedor),
    // así el filtro de "mis clientes" del vendedor funciona.
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

        $idUsuario = (int) (auth()->id() ?? 0);

        $resultado = Cliente::registrar(compact('nombre', 'telefono', 'correo'), $idUsuario ?: null);

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

        if ($this->esRutaVendedor() && !$this->clientePerteneceAlVendedorActual($idCliente)) {
            return $this->regresarConAlerta('error', 'Sin permiso', 'No puedes editar un cliente que no es tuyo.');
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

        if ($this->esRutaVendedor() && (int) ($cliente['id_usuario'] ?? 0) !== (int) (auth()->id() ?? 0)) {
            return $this->regresarConAlerta('error', 'Sin permiso', 'No puedes cambiar el estado de un cliente que no es tuyo.');
        }

        $resultado = Cliente::cambiarEstado($idCliente);

        if ($resultado === true) {
            // $cliente trae el estado ANTES del cambio, así calculamos
            // el mensaje igual que el original.
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

        if ($this->esRutaVendedor() && !$this->clientePerteneceAlVendedorActual($idCliente)) {
            return $this->regresarConAlerta('error', 'Sin permiso', 'No puedes eliminar un cliente que no es tuyo.');
        }

        $resultado = Cliente::eliminarCliente($idCliente);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', 'Cliente eliminado', 'El cliente fue eliminado correctamente.');
        }

        return $this->regresarConAlerta('error', 'No se puede eliminar', $resultado);
    }

    // ============================================================
    // ¿La petición actual llegó por una ruta del panel vendedor?
    // ============================================================
    private function esRutaVendedor(): bool
    {
        $nombreRuta = request()->route()?->getName() ?? '';

        return str_starts_with($nombreRuta, 'vendedor.');
    }

    // ============================================================
    // ¿El cliente $idCliente pertenece al vendedor autenticado?
    // ============================================================
    private function clientePerteneceAlVendedorActual(int $idCliente): bool
    {
        $cliente = Cliente::obtenerPorId($idCliente);

        if (!$cliente) {
            return false;
        }

        return (int) ($cliente['id_usuario'] ?? 0) === (int) (auth()->id() ?? 0);
    }

    // ============================================================
    // Redirige según la ruta desde la que se hizo la acción, con
    // alerta flash (rutas "vendedor.*" -> su panel, resto -> admin).
    // ============================================================
    private function regresarConAlerta(string $icon, string $title, string $text)
    {
        $ruta = $this->esRutaVendedor() ? 'vendedor.clientes' : 'admin.clientes';

        return redirect()->route($ruta)->with('alert', [
            'icon'  => $icon,
            'title' => $title,
            'text'  => $text,
        ]);
    }
}