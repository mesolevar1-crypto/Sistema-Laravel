<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Modelo Venta — alineado a la estructura REAL de la BD ya migrada.
 *
 * Tablas usadas (ya migradas a inglés):
 *   sales           (antes 'venta')          PK: id_venta
 *   sales_details   (antes 'detalle_venta')  PK: id_detalle
 *   invoices        (antes 'factura')        PK: id_factura
 *   customers       (antes 'cliente')        PK: id_cliente
 *   people          (antes 'persona')        PK: id_persona
 *   users           (antes 'usuario')        PK: id_usuario
 *   products        (antes 'producto')       PK: id_producto
 *   purchases       (antes 'compra')         PK: id_compra
 *   purchases_details (antes 'detalle_compra') PK: id_detalle
 *   units           (antes 'unidades_medida') PK: id_unidad
 *   inventories     (antes 'inventario')     PK: id_inventario
 *
 * No existe tabla de precios de producto: el precio de venta se
 * digita línea por línea en el formulario. El costo se obtiene
 * automáticamente de la ÚLTIMA compra registrada del producto.
 *
 * Todas las cantidades se normalizan a una unidad de "contenido"
 * (ej. kilogramos) para poder comparar costo de compra vs precio
 * de venta aunque se compre en Bulto y se venda en Libra.
 *
 * obtenerTodas() y obtenerResumen() aceptan un parámetro opcional
 * $idUsuario:
 *   - null (o no se pasa) -> Administrador: ve TODAS las ventas
 *   - un id_usuario        -> filtra solo las ventas de ESE vendedor
 */
class Venta extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'id_venta';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_usuario',
        'fecha',
        'total',
        'metodo_pago',
        'estado',
    ];

    // ============================================================
    // LISTA DE VENTAS (ganancia calculada al vuelo, no se guarda)
    // ============================================================
    public static function obtenerTodas(?int $idUsuario = null): array
    {
        $query = DB::table('sales as v')
            ->leftJoin('customers as c', 'v.id_cliente', '=', 'c.id_cliente')
            ->leftJoin('people as pc', 'c.id_persona', '=', 'pc.id_persona')
            ->leftJoin('users as u', 'v.id_usuario', '=', 'u.id_usuario')
            ->leftJoin('people as pu', 'u.id_persona', '=', 'pu.id_persona')
            ->leftJoin('invoices as f', 'f.id_venta', '=', 'v.id_venta')
            ->leftJoinSub(
                DB::table('sales_details')
                    ->select('id_venta', DB::raw('SUM(subtotal - (costo_unitario * cantidad)) as ganancia'))
                    ->groupBy('id_venta'),
                'g',
                'g.id_venta',
                '=',
                'v.id_venta'
            )
            ->select([
                'v.id_venta',
                'v.fecha',
                'v.total',
                'v.estado',
                'v.metodo_pago',
                'pc.nombre as cliente',
                'pu.nombre as vendedor',
                'f.numero_factura',
                DB::raw('COALESCE(g.ganancia, 0) as ganancia'),
            ])
            ->orderByDesc('v.fecha')
            ->orderByDesc('v.id_venta');

        if ($idUsuario) {
            $query->where('v.id_usuario', $idUsuario);
        }

        return $query->get()->map(fn ($fila) => (array) $fila)->all();
    }

    // ============================================================
    // KPIs DEL PANEL
    // Histórico completo: no se filtra por estado, así el
    // acumulado no cambia cuando una venta se anula o reactiva.
    // ============================================================
    public static function obtenerResumen(?int $idUsuario = null): array
    {
        $query = DB::table('sales')
            ->selectRaw("
                COUNT(*) as total_ventas,
                COALESCE(SUM(total), 0) as ingresos_total,
                SUM(CASE WHEN DATE(fecha) = CURDATE() THEN 1 ELSE 0 END) as ventas_hoy,
                SUM(CASE WHEN DATE(fecha) = CURDATE() THEN total ELSE 0 END) as ingresos_hoy
            ");

        if ($idUsuario) {
            $query->where('id_usuario', $idUsuario);
        }

        $fila = $query->first();

        return $fila ? (array) $fila : [
            'total_ventas' => 0, 'ingresos_total' => 0, 'ventas_hoy' => 0, 'ingresos_hoy' => 0,
        ];
    }

    // ============================================================
    // CLIENTES ACTIVOS (para el select del modal)
    // ============================================================
    public static function obtenerClientes(): array
    {
        return DB::table('customers as c')
            ->join('people as p', 'c.id_persona', '=', 'p.id_persona')
            ->where('c.estado', true)
            ->orderBy('p.nombre')
            ->select(['c.id_cliente', 'p.nombre'])
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    // ============================================================
    // PRODUCTOS ACTIVOS + STOCK (para el select del modal)
    // No trae precio: el precio se digita al vender.
    // ============================================================
    public static function obtenerProductosDisponibles(): array
    {
        return DB::table('products as p')
            ->leftJoin('inventories as i', 'p.id_producto', '=', 'i.id_producto')
            ->where('p.estado', true)
            ->orderBy('p.nombre')
            ->select([
                'p.id_producto',
                'p.nombre',
                'p.imagen',
                DB::raw('COALESCE(i.stock_actual, 0) as stock'),
            ])
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    // Alias, igual que en el legacy
    public static function obtenerProductos(): array
    {
        return self::obtenerProductosDisponibles();
    }

    // ============================================================
    // UNIDADES DE MEDIDA (para los selects de unidad / contenido)
    // ============================================================
    public static function obtenerUnidades(): array
    {
        return DB::table('units')
            ->orderBy('nombre')
            ->select(['id_unidad', 'nombre'])
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    // ============================================================
    // COSTO DE REFERENCIA DE UN PRODUCTO
    // Toma la ÚLTIMA compra registrada y devuelve el costo
    // por UNIDAD DE CONTENIDO (ej. costo por kg).
    // ============================================================
    private static function obtenerCostoPorContenido(int $idProducto): float
    {
        $fila = DB::table('purchases_details as dc')
            ->join('purchases as c', 'dc.id_compra', '=', 'c.id_compra')
            ->where('dc.id_producto', $idProducto)
            ->orderByDesc('c.fecha')
            ->orderByDesc('dc.id_detalle')
            ->select(['dc.precio_compra', 'dc.cantidad_por_unidad'])
            ->first();

        if (!$fila) {
            return 0;
        }

        $precioCompra  = (float) $fila->precio_compra;
        $cantPorUnidad = (int) ($fila->cantidad_por_unidad ?? 0);

        return $cantPorUnidad > 0 ? round($precioCompra / $cantPorUnidad, 2) : $precioCompra;
    }

    // ============================================================
    // DETALLE DE UNA VENTA (con ganancia por línea)
    // ============================================================
    public static function obtenerDetalle(int $idVenta): array
    {
        return DB::table('sales_details as dv')
            ->join('products as p', 'dv.id_producto', '=', 'p.id_producto')
            ->leftJoin('units as uv', 'dv.id_unidad', '=', 'uv.id_unidad')
            ->leftJoin('units as uc', 'dv.id_unidad_contenido', '=', 'uc.id_unidad')
            ->where('dv.id_venta', $idVenta)
            ->orderBy('dv.id_detalle')
            ->select([
                'dv.id_detalle',
                'dv.id_producto',
                'p.nombre as producto',
                'dv.cantidad',
                'dv.precio_venta',
                'dv.descuento_porcentaje',
                'dv.descuento_valor',
                'dv.subtotal',
                'dv.costo_unitario',
                DB::raw('(dv.subtotal - (dv.costo_unitario * dv.cantidad)) as ganancia_linea'),
                'dv.cantidad_por_unidad',
                'uv.nombre as unidad_venta',
                'uc.nombre as unidad_contenido',
            ])
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    // ============================================================
    // CABECERA COMPLETA DE UNA VENTA (para el comprobante)
    // Incluye id_usuario para poder validar dueño (vendedor)
    // antes de mostrar la factura.
    // ============================================================
    public static function obtenerVentaCompleta(int $idVenta): ?array
    {
        $fila = DB::table('sales as v')
            ->leftJoin('customers as c', 'v.id_cliente', '=', 'c.id_cliente')
            ->leftJoin('people as pc', 'c.id_persona', '=', 'pc.id_persona')
            ->leftJoin('users as u', 'v.id_usuario', '=', 'u.id_usuario')
            ->leftJoin('people as pu', 'u.id_persona', '=', 'pu.id_persona')
            ->leftJoin('invoices as f', 'f.id_venta', '=', 'v.id_venta')
            ->where('v.id_venta', $idVenta)
            ->select([
                'v.id_venta',
                'v.id_usuario',
                'v.fecha',
                'v.total',
                'v.metodo_pago',
                'v.estado',
                'pc.nombre as cliente',
                'pu.nombre as vendedor',
                'f.numero_factura',
                'f.fecha_emision',
                'f.subtotal as factura_subtotal',
                'f.descuento_valor as factura_descuento',
            ])
            ->first();

        return $fila ? (array) $fila : null;
    }

    // ============================================================
    // REGISTRAR VENTA COMPLETA (TRANSACCIÓN)
    //
    // $items[] = [
    //   'id_producto', 'cantidad', 'precio_venta', 'descuento_porcentaje',
    //   'id_unidad', 'cantidad_por_unidad', 'id_unidad_contenido'
    // ]
    // ============================================================
    public static function registrar(int $idUsuario, int $idCliente, string $metodoPago, array $items)
    {
        try {
            DB::beginTransaction();

            $clienteActivo = DB::table('customers')
                ->where('id_cliente', $idCliente)
                ->where('estado', true)
                ->exists();

            if (!$clienteActivo) {
                DB::rollBack();
                return 'El cliente seleccionado no existe o está inactivo.';
            }

            $itemsCalculados = [];
            $total = 0;

            foreach ($items as $item) {

                $idProducto  = (int) $item['id_producto'];
                $cantidad    = (int) $item['cantidad'];
                $precioVenta = (float) $item['precio_venta'];
                $descPct     = (float) ($item['descuento_porcentaje'] ?? 0);
                $idUnidad    = !empty($item['id_unidad']) ? (int) $item['id_unidad'] : null;
                $cantXUnidad = !empty($item['cantidad_por_unidad']) ? (int) $item['cantidad_por_unidad'] : 1;
                $idUndCont   = !empty($item['id_unidad_contenido']) ? (int) $item['id_unidad_contenido'] : $idUnidad;

                if ($cantidad <= 0 || $precioVenta <= 0) {
                    DB::rollBack();
                    return 'Cantidad o precio inválido en uno de los productos.';
                }

                if ($descPct < 0 || $descPct > 100) {
                    DB::rollBack();
                    return 'El descuento debe estar entre 0% y 100%.';
                }

                $prod = DB::table('products as p')
                    ->leftJoin('inventories as i', 'p.id_producto', '=', 'i.id_producto')
                    ->where('p.id_producto', $idProducto)
                    ->where('p.estado', true)
                    ->lockForUpdate()
                    ->select(['p.nombre', DB::raw('COALESCE(i.stock_actual, 0) as stock')])
                    ->first();

                if (!$prod) {
                    DB::rollBack();
                    return 'Uno de los productos ya no existe o está inactivo.';
                }

                $cantidadContenido = $cantidad * $cantXUnidad;

                if ($cantidadContenido > (int) $prod->stock) {
                    DB::rollBack();
                    return "Stock insuficiente para \"{$prod->nombre}\". Disponible: {$prod->stock}.";
                }

                $descuentoValor = round($precioVenta * $cantidad * $descPct / 100, 2);
                $subtotal       = round(($precioVenta * $cantidad) - $descuentoValor, 2);

                $costoPorContenido = self::obtenerCostoPorContenido($idProducto);
                $costoUnitario     = round($costoPorContenido * $cantXUnidad, 2);

                $total += $subtotal;

                $itemsCalculados[] = [
                    'id_producto'          => $idProducto,
                    'cantidad'             => $cantidad,
                    'precio_venta'         => $precioVenta,
                    'descuento_porcentaje' => $descPct,
                    'descuento_valor'      => $descuentoValor,
                    'subtotal'             => $subtotal,
                    'id_unidad'            => $idUnidad,
                    'cantidad_por_unidad'  => $cantXUnidad,
                    'id_unidad_contenido'  => $idUndCont,
                    'costo_unitario'       => $costoUnitario,
                    'cantidad_contenido'   => $cantidadContenido,
                ];
            }

            if (empty($itemsCalculados)) {
                DB::rollBack();
                return 'Debes agregar al menos un producto.';
            }

            $idVenta = DB::table('sales')->insertGetId([
                'id_cliente'  => $idCliente,
                'id_usuario'  => $idUsuario,
                'fecha'       => now(),
                'total'       => $total,
                'metodo_pago' => $metodoPago,
                'estado'      => true,
            ], 'id_venta');

            foreach ($itemsCalculados as $it) {

                DB::table('sales_details')->insert([
                    'id_venta'             => $idVenta,
                    'id_producto'          => $it['id_producto'],
                    'cantidad'             => $it['cantidad'],
                    'precio_venta'         => $it['precio_venta'],
                    'descuento_porcentaje' => $it['descuento_porcentaje'],
                    'descuento_valor'      => $it['descuento_valor'],
                    'subtotal'             => $it['subtotal'],
                    'id_unidad'            => $it['id_unidad'],
                    'cantidad_por_unidad'  => $it['cantidad_por_unidad'],
                    'id_unidad_contenido'  => $it['id_unidad_contenido'],
                    'costo_unitario'       => $it['costo_unitario'],
                ]);

                DB::table('inventories')
                    ->where('id_producto', $it['id_producto'])
                    ->update([
                        'stock_actual'        => DB::raw('stock_actual - ' . (int) $it['cantidad_contenido']),
                        'fecha_actualizacion' => now(),
                    ]);
            }

            $descuentoTotal  = array_sum(array_column($itemsCalculados, 'descuento_valor'));
            $subtotalSinDesc = $total + $descuentoTotal;
            $numeroFactura   = self::generarNumeroFactura();

            DB::table('invoices')->insert([
                'id_venta'        => $idVenta,
                'numero_factura'  => $numeroFactura,
                'fecha_emision'   => now(),
                'subtotal'        => $subtotalSinDesc,
                'descuento_valor' => $descuentoTotal,
                'total'           => $total,
                'estado'          => true,
            ]);

            DB::commit();

            return ['id_venta' => $idVenta, 'numero_factura' => $numeroFactura];

        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return 'Error al registrar la venta: ' . $e->getMessage();
        }
    }

    // ============================================================
    // NÚMERO DE FACTURA CORRELATIVO
    // ============================================================
    private static function generarNumeroFactura(): string
    {
        $total = DB::table('invoices')->count();
        $numero = $total + 1;

        return 'FAC-' . str_pad((string) $numero, 6, '0', STR_PAD_LEFT);
    }

    // ============================================================
    // ANULAR VENTA (no se borra físicamente, se conserva el
    // histórico y se devuelve el stock al inventario)
    // ============================================================
    public static function eliminar(int $idVenta)
    {
        try {
            DB::beginTransaction();

            $existe = DB::table('sales')
                ->where('id_venta', $idVenta)
                ->where('estado', true)
                ->lockForUpdate()
                ->exists();

            if (!$existe) {
                DB::rollBack();
                return 'La venta no existe o ya está anulada.';
            }

            $lineas = DB::table('sales_details')
                ->where('id_venta', $idVenta)
                ->get(['id_producto', 'cantidad', 'cantidad_por_unidad']);

            foreach ($lineas as $l) {
                $cantPorUnidad = (int) ($l->cantidad_por_unidad ?: 1);
                $cantidadContenido = (int) $l->cantidad * $cantPorUnidad;

                DB::table('inventories')
                    ->where('id_producto', $l->id_producto)
                    ->update([
                        'stock_actual'        => DB::raw('stock_actual + ' . $cantidadContenido),
                        'fecha_actualizacion' => now(),
                    ]);
            }

            DB::table('sales')->where('id_venta', $idVenta)->update(['estado' => false]);
            DB::table('invoices')->where('id_venta', $idVenta)->update(['estado' => false]);

            DB::commit();
            return true;

        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return 'Error al anular la venta: ' . $e->getMessage();
        }
    }

    // ============================================================
    // REACTIVAR VENTA (revierte una anulación: vuelve a poner
    // la venta y su factura como activas, y vuelve a descontar
    // el inventario. Si ya no hay stock suficiente, se rechaza.)
    // ============================================================
    public static function reactivar(int $idVenta)
    {
        try {
            DB::beginTransaction();

            $existe = DB::table('sales')
                ->where('id_venta', $idVenta)
                ->where('estado', false)
                ->lockForUpdate()
                ->exists();

            if (!$existe) {
                DB::rollBack();
                return 'La venta no existe o ya está activa.';
            }

            $lineas = DB::table('sales_details as dv')
                ->join('products as p', 'p.id_producto', '=', 'dv.id_producto')
                ->leftJoin('inventories as i', 'i.id_producto', '=', 'dv.id_producto')
                ->where('dv.id_venta', $idVenta)
                ->lockForUpdate()
                ->select([
                    'dv.id_producto', 'dv.cantidad', 'dv.cantidad_por_unidad',
                    'p.nombre', DB::raw('COALESCE(i.stock_actual, 0) as stock'),
                ])
                ->get();

            foreach ($lineas as $l) {
                $cantPorUnidad     = (int) ($l->cantidad_por_unidad ?: 1);
                $cantidadContenido = (int) $l->cantidad * $cantPorUnidad;

                if ($cantidadContenido > (int) $l->stock) {
                    DB::rollBack();
                    return "Stock insuficiente para \"{$l->nombre}\" al reactivar. Disponible: {$l->stock}, se necesitan: {$cantidadContenido}.";
                }
            }

            foreach ($lineas as $l) {
                $cantPorUnidad     = (int) ($l->cantidad_por_unidad ?: 1);
                $cantidadContenido = (int) $l->cantidad * $cantPorUnidad;

                DB::table('inventories')
                    ->where('id_producto', $l->id_producto)
                    ->update([
                        'stock_actual'        => DB::raw('stock_actual - ' . $cantidadContenido),
                        'fecha_actualizacion' => now(),
                    ]);
            }

            DB::table('sales')->where('id_venta', $idVenta)->update(['estado' => true]);
            DB::table('invoices')->where('id_venta', $idVenta)->update(['estado' => true]);

            DB::commit();
            return true;

        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return 'Error al reactivar la venta: ' . $e->getMessage();
        }
    }
}