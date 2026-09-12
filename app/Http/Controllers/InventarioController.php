<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador de Inventario.
 *
 * index()        -> Administrador: ve TODO el inventario.
 * vendedorIndex() -> Vendedor: ve solo el inventario de SUS productos.
 *
 * actualizar() es compartido; cuando la petición llega por una ruta
 * "vendedor.*" se valida que el producto sobre el que se actúa le
 * pertenezca al vendedor autenticado (mismo patrón usado en
 * ProductoController).
 */
class InventarioController extends Controller
{
    // ============================================================
    // LISTADO — ADMIN (con búsqueda, filtro por estado y paginación)
    // ============================================================
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $estado = trim((string) $request->input('estado', ''));

        $todos   = Inventario::obtenerTodos($buscar, $estado);
        $resumen = Inventario::obtenerResumen();

        $porPagina  = 5;
        $total      = count($todos);
        $paginas    = max(1, (int) ceil($total / $porPagina));
        $pagina     = max(1, min((int) $request->input('pagina', 1), $paginas));
        $inventario = array_slice($todos, ($pagina - 1) * $porPagina, $porPagina);

        return view('vista_admin.inventario', compact(
            'inventario', 'resumen', 'buscar', 'estado', 'pagina', 'paginas', 'total'
        ));
    }

    // ============================================================
    // LISTADO — VENDEDOR (solo el inventario de SUS productos)
    // ============================================================
    public function vendedorIndex(Request $request)
    {
        $idUsuario = (int) (auth()->id() ?? 0);

        $buscar = trim((string) $request->input('buscar', ''));
        $estado = trim((string) $request->input('estado', ''));

        // Requiere que Inventario::obtenerTodos() acepte un 4to
        // parámetro opcional $idUsuario para filtrar por dueño del
        // producto (mismo patrón que Producto::obtenerTodos($idUsuario)).
        $todos   = Inventario::obtenerTodos($buscar, $estado, $idUsuario);
        $resumen = Inventario::obtenerResumen($idUsuario);

        $porPagina  = 5;
        $total      = count($todos);
        $paginas    = max(1, (int) ceil($total / $porPagina));
        $pagina     = max(1, min((int) $request->input('pagina', 1), $paginas));
        $inventario = array_slice($todos, ($pagina - 1) * $porPagina, $porPagina);

        return view('vista_vendedor.inventario', compact(
            'inventario', 'resumen', 'buscar', 'estado', 'pagina', 'paginas', 'total'
        ));
    }

    // ============================================================
    // ACTUALIZAR STOCK (ajuste manual)
    // ============================================================
    public function actualizar(Request $request, $id)
    {
        $idProducto  = (int) $id;
        $stockActual = $request->input('stock_actual', '');
        $stockMinimo = $request->input('stock_minimo', '');

        if ($idProducto <= 0) {
            return $this->regresarConAlerta('error', 'Error', 'Producto no válido.');
        }

        // ── Verificación de propiedad para el panel vendedor ──
        if ($this->esRutaVendedor()) {
            $producto = Producto::obtenerPorId($idProducto);

            if (!$producto || (int) ($producto['id_usuario'] ?? 0) !== (int) (auth()->id() ?? 0)) {
                return $this->regresarConAlerta('error', 'Sin permiso', 'No puedes actualizar el inventario de un producto que no es tuyo.');
            }
        }

        if ($stockActual === '' || !is_numeric($stockActual)) {
            return $this->regresarConAlerta('warning', 'Valor inválido', 'Ingresa un valor válido para el stock.');
        }

        if ((int) $stockActual < 0) {
            return $this->regresarConAlerta('warning', 'Valor inválido', 'El stock no puede ser negativo.');
        }

        if ($stockMinimo === '' || !is_numeric($stockMinimo)) {
            return $this->regresarConAlerta('warning', 'Valor inválido', 'Ingresa un valor válido para el stock mínimo.');
        }

        if ((int) $stockMinimo < 0) {
            return $this->regresarConAlerta('warning', 'Valor inválido', 'El stock mínimo no puede ser negativo.');
        }

        $resultado = Inventario::actualizarStock($idProducto, (int) $stockActual, (int) $stockMinimo);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', '¡Actualizado!', 'Inventario actualizado correctamente.');
        }

        return $this->regresarConAlerta('error', 'Error', 'No se pudo actualizar. Intenta nuevamente.');
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
    // Redirige según el rol del usuario autenticado, con alerta
    // flash (admin -> su panel, vendedor -> el suyo).
    // ============================================================
    private function regresarConAlerta(string $icon, string $title, string $text)
    {
        $ruta = $this->esRutaVendedor() ? 'vendedor.inventario' : 'admin.inventario';

        return redirect()->route($ruta)->with('alert', [
            'icon'  => $icon,
            'title' => $title,
            'text'  => $text,
        ]);
    }
}