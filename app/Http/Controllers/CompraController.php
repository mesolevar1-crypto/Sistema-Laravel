<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Controlador de Compra.
 *
 * Equivalente al antiguo controllers/CompraController.php, pero
 * dividido en acciones REST (index, store, detalle, destroy) en
 * vez de un switch por $_GET['accion'].
 *
 * El controlador NUNCA confía en precios, unidades ni subtotales
 * enviados desde el navegador -- todo eso lo recalcula y verifica
 * el modelo Compra contra la base de datos.
 */
class CompraController extends Controller
{
    // ============================================================
    // LISTADO (con KPIs, selects y paginación simple)
    // ============================================================
    public function index(Request $request)
    {
        $todas       = Compra::obtenerTodas();
        $resumen     = Compra::obtenerResumen();
        $proveedores = Compra::obtenerProveedores();
        $productos   = Compra::obtenerProductos();
        $unidades    = Compra::obtenerUnidades();

        $porPagina = 5;
        $total     = count($todas);
        $paginas   = max(1, (int) ceil($total / $porPagina));
        $pagina    = max(1, min((int) $request->input('pagina', 1), $paginas));
        $compras   = array_slice($todas, ($pagina - 1) * $porPagina, $porPagina);

        return view('vista_admin.compras', compact(
            'compras', 'resumen', 'proveedores', 'productos', 'unidades',
            'pagina', 'paginas', 'total'
        ));
    }

    // ============================================================
    // REGISTRAR COMPRA
    // ============================================================
    public function store(Request $request)
    {
        // Usuario desde la sesión autenticada (nunca desde el formulario).
        // Asume que el modelo User usa 'id_usuario' como primaryKey; si tu
        // modelo User usa el 'id' por defecto de Laravel, cambia esta línea
        // por: $idUsuario = (int) optional(auth()->user())->id_usuario;
        $idUsuario = (int) (auth()->id() ?? 0);

        if ($idUsuario <= 0) {
            return $this->regresarConAlerta('error', 'Sesión inválida', 'No se pudo identificar el usuario actual.');
        }

        $idProveedor = (int) $request->input('id_proveedor', 0);

        if ($idProveedor <= 0) {
            return $this->regresarConAlerta('warning', 'Proveedor requerido', 'Debes seleccionar un proveedor activo.');
        }

        $idsProducto         = $request->input('id_producto', []);
        $cantidades          = $request->input('cantidad', []);
        $preciosCompra       = $request->input('precio_compra', []);
        $idsUnidad           = $request->input('id_unidad', []);
        $cantidadesPorUnidad = $request->input('cantidad_por_unidad', []);
        $idsUnidadContenido  = $request->input('id_unidad_contenido', []);

        if (
            !is_array($idsProducto) ||
            !is_array($cantidades) ||
            count($idsProducto) === 0 ||
            count($cantidades) === 0
        ) {
            return $this->regresarConAlerta('warning', 'Compra vacía', 'Debes agregar al menos un producto a la compra.');
        }

        // --------------------------------------------------------
        // CONSTRUIR ITEMS
        // cantidad y cantidad_por_unidad se castean a ENTERO -- las
        // columnas son INT en la base de datos.
        // --------------------------------------------------------
        $items = [];
        $cantidadFilas = max(count($idsProducto), count($cantidades));

        for ($i = 0; $i < $cantidadFilas; $i++) {

            $idProducto          = isset($idsProducto[$i]) ? (int) $idsProducto[$i] : 0;
            $cantidadCruda       = isset($cantidades[$i]) ? trim((string) $cantidades[$i]) : '';
            $precioCruda         = isset($preciosCompra[$i]) ? trim((string) $preciosCompra[$i]) : '';
            $idUnidad            = isset($idsUnidad[$i]) ? (int) $idsUnidad[$i] : 0;
            $cantidadUnidadCruda = isset($cantidadesPorUnidad[$i]) ? trim((string) $cantidadesPorUnidad[$i]) : '';
            $idUnidadContenido   = isset($idsUnidadContenido[$i]) ? (int) $idsUnidadContenido[$i] : 0;

            // Ignorar filas completamente vacías
            if (
                $idProducto <= 0 && $cantidadCruda === '' && $precioCruda === '' &&
                $idUnidad <= 0 && $cantidadUnidadCruda === '' && $idUnidadContenido <= 0
            ) {
                continue;
            }

            if ($idProducto <= 0) {
                return $this->regresarConAlerta('warning', 'Producto inválido', 'Hay una fila de la compra sin producto seleccionado.');
            }

            if (!ctype_digit($cantidadCruda) || (int) $cantidadCruda <= 0) {
                return $this->regresarConAlerta('warning', 'Cantidad inválida', 'Todas las cantidades deben ser números enteros mayores que cero.');
            }

            if (!is_numeric($precioCruda) || (float) $precioCruda <= 0) {
                return $this->regresarConAlerta('warning', 'Precio inválido', 'Debes escribir el precio de compra negociado con el proveedor para cada producto.');
            }

            if ($idUnidad <= 0) {
                return $this->regresarConAlerta('warning', 'Unidad de compra requerida', 'Debes seleccionar en qué unidad compraste cada producto (bulto, caja, etc).');
            }

            if (!ctype_digit($cantidadUnidadCruda) || (int) $cantidadUnidadCruda <= 0) {
                return $this->regresarConAlerta('warning', 'Presentación inválida', 'Debes indicar cuánto contenido trae cada presentación comprada.');
            }

            if ($idUnidadContenido <= 0) {
                return $this->regresarConAlerta('warning', 'Unidad de contenido requerida', 'Debes seleccionar en qué unidad se mide el contenido de cada producto (ej. kilogramo, libra).');
            }

            $items[] = [
                'id_producto'         => $idProducto,
                'cantidad'            => (int) $cantidadCruda,
                'precio_compra'       => (float) $precioCruda,
                'id_unidad'           => $idUnidad,
                'cantidad_por_unidad' => (int) $cantidadUnidadCruda,
                'id_unidad_contenido' => $idUnidadContenido,
            ];
        }

        if (empty($items)) {
            return $this->regresarConAlerta('warning', 'Compra vacía', 'Debes seleccionar al menos un producto y especificar su cantidad.');
        }

        $resultado = Compra::registrar($idUsuario, $idProveedor, $items);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', 'Compra registrada', 'La compra se registró correctamente y el inventario fue actualizado.');
        }

        return $this->regresarConAlerta('error', 'No se pudo registrar la compra', (string) $resultado);
    }

    // ============================================================
    // DETALLE DE COMPRA (JSON, para el modal "Ver detalle")
    // ============================================================
    public function detalle($id): JsonResponse
    {
        $idCompra = (int) $id;

        if ($idCompra <= 0) {
            return response()->json(['error' => 'ID de compra no válido.'], 400);
        }

        try {
            $detalle = Compra::obtenerDetalle($idCompra);
            return response()->json($detalle);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Error al obtener el detalle de la compra.'], 500);
        }
    }

    // ============================================================
    // ELIMINAR COMPRA
    // ============================================================
    public function destroy($id)
    {
        $idCompra = (int) $id;

        if ($idCompra <= 0) {
            return $this->regresarConAlerta('error', 'Compra inválida', 'El identificador de la compra no es válido.');
        }

        $resultado = Compra::eliminar($idCompra);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', 'Compra eliminada', 'La compra fue eliminada correctamente y el inventario fue actualizado.');
        }

        return $this->regresarConAlerta('error', 'No se pudo eliminar', (string) $resultado);
    }

    // ============================================================
    // Redirige al panel de compras con alerta flash
    // ============================================================
    private function regresarConAlerta(string $icon, string $title, string $text)
    {
        return redirect()->route('admin.compras')->with('alert', [
            'icon'  => $icon,
            'title' => $title,
            'text'  => $text,
        ]);
    }
}