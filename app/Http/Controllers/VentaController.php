<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
 *
 * IMPORTANTE (seguridad de rol):
 * Todas las acciones que operan sobre una venta puntual (detalle,
 * anular, reactivar, factura) validan que, si la petición viene de
 * una ruta de vendedor, la venta le pertenezca a ESE vendedor. Así
 * un vendedor no puede ver ni tocar ventas de otros vendedores ni
 * del administrador, aunque cambie el ID en la URL a mano.
 */
class VentaController extends Controller
{
    // ============================================================
    // LISTADO (con KPIs, selects y paginación simple) — ADMIN
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
    // LISTADO — VENDEDOR (solo SUS propias ventas)
    // ============================================================
    public function vendedorIndex(Request $request)
    {
        $idUsuario = (int) (auth()->id() ?? 0);

        $todas     = Venta::obtenerTodas($idUsuario);
        $resumen   = Venta::obtenerResumen($idUsuario);
        $clientes  = Venta::obtenerClientes();
        $productos = Venta::obtenerProductosDisponibles();
        $unidades  = Venta::obtenerUnidades();

        $porPagina = 5;
        $total     = count($todas);
        $paginas   = max(1, (int) ceil($total / $porPagina));
        $pagina    = max(1, min((int) $request->input('pagina', 1), $paginas));
        $ventas    = array_slice($todas, ($pagina - 1) * $porPagina, $porPagina);

        return view('vista_vendedor.ventas', compact(
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
            if ($this->esRutaVendedor() && !$this->ventaPerteneceAlVendedorActual($idVenta)) {
                return response()->json(['error' => 'No tienes permiso para ver esta venta.'], 403);
            }

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

        if ($this->esRutaVendedor() && !$this->ventaPerteneceAlVendedorActual($idVenta)) {
            return $this->regresarConAlerta('error', 'Sin permiso', 'No puedes anular una venta que no es tuya.');
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

        if ($this->esRutaVendedor() && !$this->ventaPerteneceAlVendedorActual($idVenta)) {
            return $this->regresarConAlerta('error', 'Sin permiso', 'No puedes reactivar una venta que no es tuya.');
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

        if ($this->esRutaVendedor() && (int) ($venta['id_usuario'] ?? 0) !== (int) auth()->id()) {
            abort(403, 'No tienes permiso para ver esta factura.');
        }

        $detalle = Venta::obtenerDetalle($idVenta);
        $vista   = $this->esRutaVendedor() ? 'vista_vendedor.factura' : 'vista_admin.factura';

        return view($vista, compact('venta', 'detalle'));
    }

    // ============================================================
    // ¿La petición actual llegó por una ruta del panel vendedor?
    // Se basa en el nombre de la ruta (p. ej. "vendedor.ventas.store"),
    // no en un campo "rol" del usuario, que puede fallar o no existir.
    // ============================================================
    private function esRutaVendedor(): bool
    {
        $nombreRuta = request()->route()?->getName() ?? '';

        return str_starts_with($nombreRuta, 'vendedor.');
    }

    // ============================================================
    // ¿La venta $idVenta pertenece al usuario autenticado?
    // Se usa para bloquear que un vendedor opere sobre ventas de
    // otro vendedor o del administrador.
    // ============================================================
    private function ventaPerteneceAlVendedorActual(int $idVenta): bool
    {
        $venta = Venta::obtenerVentaCompleta($idVenta);

        if (!$venta) {
            return false;
        }

        return (int) ($venta['id_usuario'] ?? 0) === (int) (auth()->id() ?? 0);
    }

    // ============================================================
    // Redirige según la ruta desde la que se hizo la acción, con
    // alerta flash (rutas "vendedor.*" -> su panel, resto -> admin).
    //
    // Antes esto se decidía leyendo Auth::user()->rol, pero si esa
    // columna no existe, se llama distinto, o el valor no calza
    // exactamente con "vendedor", el vendedor terminaba redirigido
    // a la vista de admin (que muestra TODAS las ventas sin
    // filtrar) — ese era el bug de "aparecen ventas del admin".
    // ============================================================
    private function regresarConAlerta(string $icon, string $title, string $text)
    {
        $ruta = $this->esRutaVendedor() ? 'vendedor.ventas' : 'admin.ventas';

        return redirect()->route($ruta)->with('alert', [
            'icon'  => $icon,
            'title' => $title,
            'text'  => $text,
        ]);
    }
}