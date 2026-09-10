<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Controlador de Venta.
 *
 * Equivalente al antiguo controllers/VentaController.php, pero
 * dividido en acciones REST (index, store, detalle, anular,
 * reactivar, factura) en vez de un switch por $_GET['accion'].
 *
 * El controlador NUNCA confía en precios, unidades ni subtotales
 * enviados desde el navegador -- todo eso lo recalcula y verifica
 * el modelo Venta contra la base de datos.
 */
class VentaController extends Controller
{
    // ============================================================
    // LISTADO (con KPIs, selects y paginación simple)
    // ============================================================
    public function index(Request $request)
    {
        $todas       = Venta::obtenerTodas();
        $resumen     = Venta::obtenerResumen();
        $clientes    = Venta::obtenerClientes();
        $productos   = Venta::obtenerProductosDisponibles();
        $unidades    = Venta::obtenerUnidades();

        $porPagina = 5;
        $total     = count($todas);
        $paginas   = max(1, (int) ceil($total / $porPagina));
        $pagina    = max(1, min((int) $request->input('pagina', 1), $paginas));
        $ventas    = array_slice($todas, ($pagina - 1) * $porPagina, $porPagina);

        return view('vista_admin.ventas', compact(
            'ventas', 'resumen', 'clientes', 'productos', 'unidades',
            'pagina', 'paginas', 'total'
        ));
    }

    // ============================================================
    // REGISTRAR VENTA
    // ============================================================
    public function store(Request $request)
    {
        // Usuario desde la sesión autenticada (nunca desde el formulario).
        // Asume que el modelo User usa 'id_usuario' como primaryKey; si tu
        // modelo User usa el 'id' por defecto de Laravel, cambia esta línea
        // por: $idUsuario = (int) optional(auth()->user())->id_usuario;
        $idUsuario = (int) (auth()->id() ?? 0);

        if ($idUsuario <= 0) {
            return $this->regresarConAlerta('error', 'Sin sesión', 'Tu sesión expiró, vuelve a iniciar sesión.');
        }

        $idCliente  = (int) $request->input('id_cliente', 0);
        $metodoPago = trim($request->input('metodo_pago', 'efectivo'));

        $idsProducto = $request->input('id_producto', []);
        $cantidades  = $request->input('cantidad', []);
        $precios     = $request->input('precio_venta', []);
        $descPct     = $request->input('descuento_porcentaje', []);
        $idsUnidad   = $request->input('id_unidad', []);
        $cantXUnidad = $request->input('cantidad_por_unidad', []);
        $idsUndCont  = $request->input('id_unidad_contenido', []);

        if ($idCliente <= 0) {
            return $this->regresarConAlerta('warning', 'Cliente requerido', 'Debes seleccionar un cliente.');
        }

        if (empty($idsProducto)) {
            return $this->regresarConAlerta('warning', 'Sin productos', 'Debes agregar al menos un producto.');
        }

        // --------------------------------------------------------
        // ARMAR ITEMS (el controlador solo recolecta, el modelo
        // vuelve a calcular y validar todo)
        // --------------------------------------------------------
        $items = [];

        foreach ($idsProducto as $i => $idProducto) {

            $idProducto = (int) $idProducto;

            if ($idProducto === 0) {
                continue;
            }

            $items[] = [
                'id_producto'          => $idProducto,
                'cantidad'             => (int) ($cantidades[$i] ?? 0),
                'precio_venta'         => (float) ($precios[$i] ?? 0),
                'descuento_porcentaje' => (float) ($descPct[$i] ?? 0),
                'id_unidad'            => !empty($idsUnidad[$i]) ? (int) $idsUnidad[$i] : null,
                'cantidad_por_unidad'  => !empty($cantXUnidad[$i]) ? (int) $cantXUnidad[$i] : 1,
                'id_unidad_contenido'  => !empty($idsUndCont[$i]) ? (int) $idsUndCont[$i] : null,
            ];
        }

        if (empty($items)) {
            return $this->regresarConAlerta('warning', 'Sin productos válidos', 'Debes agregar al menos un producto.');
        }

        $resultado = Venta::registrar($idUsuario, $idCliente, $metodoPago, $items);

        if (is_array($resultado)) {
            return $this->regresarConAlerta(
                'success',
                '¡Venta registrada!',
                'Venta guardada correctamente. Factura: ' . $resultado['numero_factura']
            );
        }

        return $this->regresarConAlerta('error', 'No se pudo registrar', is_string($resultado) ? $resultado : 'No fue posible registrar la venta.');
    }

    // ============================================================
    // DETALLE DE VENTA (JSON, para el modal "Ver detalle")
    // ============================================================
    public function detalle($id): JsonResponse
    {
        $idVenta = (int) $id;

        if ($idVenta <= 0) {
            return response()->json(['error' => 'ID de venta no válido.'], 400);
        }

        try {
            $detalle = Venta::obtenerDetalle($idVenta);
            return response()->json($detalle);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Error al obtener el detalle de la venta.'], 500);
        }
    }

    // ============================================================
    // ANULAR VENTA
    // ============================================================
    public function anular($id)
    {
        $idVenta = (int) $id;

        if ($idVenta <= 0) {
            return $this->regresarConAlerta('error', 'Venta inválida', 'No se recibió una venta válida.');
        }

        $resultado = Venta::eliminar($idVenta);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', 'Venta anulada', 'La venta fue anulada y el inventario fue restaurado.');
        }

        return $this->regresarConAlerta('error', 'No se pudo anular', is_string($resultado) ? $resultado : 'No fue posible anular la venta.');
    }

    // ============================================================
    // REACTIVAR VENTA
    // ============================================================
    public function reactivar($id)
    {
        $idVenta = (int) $id;

        if ($idVenta <= 0) {
            return $this->regresarConAlerta('error', 'Venta inválida', 'No se recibió una venta válida.');
        }

        $resultado = Venta::reactivar($idVenta);

        if ($resultado === true) {
            return $this->regresarConAlerta('success', 'Venta reactivada', 'La venta fue reactivada y el inventario fue descontado nuevamente.');
        }

        return $this->regresarConAlerta('error', 'No se pudo reactivar', is_string($resultado) ? $resultado : 'No fue posible reactivar la venta.');
    }

    // ============================================================
    // COMPROBANTE / FACTURA
    // ============================================================
    public function factura($id)
    {
        $idVenta = (int) $id;

        $venta = $idVenta > 0 ? Venta::obtenerVentaCompleta($idVenta) : null;

        if (!$venta) {
            abort(404, 'Venta no encontrada.');
        }

        $detalle = Venta::obtenerDetalle($idVenta);

        return view('vista_admin.factura', compact('venta', 'detalle'));
    }

    // ============================================================
    // Redirige según el rol del usuario autenticado, con alerta
    // flash (admin -> su panel, vendedor -> el suyo).
    // ============================================================
    private function regresarConAlerta(string $icon, string $title, string $text)
    {
        $rol = strtolower(trim(optional(Auth::user())->rol ?? ''));

        $ruta = $rol === 'vendedor' ? 'vendedor.ventas' : 'admin.ventas';

        return redirect()->route($ruta)->with('alert', [
            'icon'  => $icon,
            'title' => $title,
            'text'  => $text,
        ]);
    }
}