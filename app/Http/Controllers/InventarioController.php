<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador de Inventario.
 *
 * Equivalente al antiguo controllers/InventarioController.php.
 * Solo tiene una acción real (ajuste manual de stock); el listado
 * vive en index() porque la vista legacy no tenía controlador propio,
 * hacía las consultas directamente.
 */
class InventarioController extends Controller
{
    // ============================================================
    // LISTADO (con búsqueda, filtro por estado y paginación)
    // ============================================================
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $estado = trim((string) $request->input('estado', ''));

        $todos   = Inventario::obtenerTodos($buscar, $estado);
        $resumen = Inventario::obtenerResumen();

        $porPagina = 5;
        $total     = count($todos);
        $paginas   = max(1, (int) ceil($total / $porPagina));
        $pagina    = max(1, min((int) $request->input('pagina', 1), $paginas));
        $inventario = array_slice($todos, ($pagina - 1) * $porPagina, $porPagina);

        return view('vista_admin.inventario', compact(
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
    // Redirige según el rol del usuario autenticado, con alerta
    // flash (admin -> su panel, vendedor -> el suyo).
    // ============================================================
    private function regresarConAlerta(string $icon, string $title, string $text)
    {
        $rol = strtolower(trim(optional(Auth::user())->rol ?? ''));

        $ruta = $rol === 'vendedor' ? 'vendedor.inventario' : 'admin.inventario';

        return redirect()->route($ruta)->with('alert', [
            'icon'  => $icon,
            'title' => $title,
            'text'  => $text,
        ]);
    }
}