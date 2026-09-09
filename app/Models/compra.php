<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * Modelo Compra
 *
 * Tablas usadas (ya migradas a inglés):
 *   purchases          (antes 'compra')          PK: id_compra
 *   purchases_details  (antes 'detalle_compra')  PK: id_detalle
 *   products           (antes 'producto')        PK: id_producto
 *   categories         (antes 'categoria')       PK: id_categoria
 *   inventories        (antes 'inventario')      PK: id_inventario
 *   units              (antes 'unidades_medida') PK: id_unidad
 *   suppliers          (antes 'proveedor')       PK: id_proveedor
 *   people             (antes 'persona')         PK: id_persona
 *   users              (antes 'usuario')         PK: id_usuario
 *
 * Las columnas internas se mantienen en español, igual que en
 * Cliente.php y Proveedor.php, para no romper el resto del código.
 *
 * Las reglas de negocio (precio/unidad/cantidad los define el
 * usuario, nunca un catálogo de precios; subtotal/total se
 * calculan aquí; el inventario se revierte solo si alcanza) se
 * mantienen exactamente igual que en el modelo legacy.
 */
class Compra extends Model
{
    protected $table = 'purchases';
    protected $primaryKey = 'id_compra';
    public $timestamps = false;

    protected $fillable = [
        'id_proveedor',
        'id_usuario',
        'fecha',
        'total',
        'estado',
    ];

    // ============================================================
    // REGISTRAR COMPRA
    //
    // $items = [
    //   [
    //     'id_producto' => int,
    //     'cantidad' => int,
    //     'precio_compra' => float,
    //     'id_unidad' => int,
    //     'cantidad_por_unidad' => int,
    //     'id_unidad_contenido' => int,
    //   ],
    //   ...
    // ]
    //
    // Retorna true si se registró correctamente, o un string con
    // el mensaje de error si algo falló.
    // ============================================================
    public static function registrar(int $idUsuario, int $idProveedor, array $items)
    {
        if ($idUsuario <= 0) {
            return 'Usuario inválido.';
        }

        if ($idProveedor <= 0) {
            return 'Debes seleccionar un proveedor.';
        }

        if (empty($items)) {
            return 'La compra debe tener al menos un producto.';
        }

        try {
            // --------------------------------------------------------
            // VALIDAR PROVEEDOR ACTIVO
            // --------------------------------------------------------
            $proveedorActivo = DB::table('suppliers as pr')
                ->join('people as pe', 'pe.id_persona', '=', 'pr.id_persona')
                ->where('pr.id_proveedor', $idProveedor)
                ->where('pe.estado', true)
                ->exists();

            if (!$proveedorActivo) {
                return 'El proveedor seleccionado no existe o no está activo.';
            }

            // --------------------------------------------------------
            // VALIDAR CADA ITEM Y CALCULAR SUBTOTALES
            // --------------------------------------------------------
            $itemsValidados = [];
            $total = 0.0;

            foreach ($items as $item) {

                $idProducto        = (int) ($item['id_producto'] ?? 0);
                $cantidad          = $item['cantidad'] ?? null;
                $precioCompra      = $item['precio_compra'] ?? null;
                $idUnidad          = (int) ($item['id_unidad'] ?? 0);
                $cantidadPorUnidad = $item['cantidad_por_unidad'] ?? null;
                $idUnidadContenido = (int) ($item['id_unidad_contenido'] ?? 0);

                if ($idProducto <= 0) {
                    return 'Hay un producto inválido en la compra.';
                }

                $productoActivo = DB::table('products')
                    ->where('id_producto', $idProducto)
                    ->where('estado', true)
                    ->exists();

                if (!$productoActivo) {
                    return 'Uno de los productos seleccionados no existe o no está activo.';
                }

                if (!is_int($cantidad) || $cantidad <= 0) {
                    return 'La cantidad debe ser un número entero mayor que cero.';
                }

                if (!is_numeric($precioCompra) || (float) $precioCompra <= 0) {
                    return 'El precio de compra debe ser un valor numérico mayor que cero.';
                }
                $precioCompra = round((float) $precioCompra, 2);

                if ($idUnidad <= 0) {
                    return 'Debes seleccionar la unidad de compra.';
                }
                if (!DB::table('units')->where('id_unidad', $idUnidad)->exists()) {
                    return 'La unidad de compra seleccionada no es válida.';
                }

                if (!is_int($cantidadPorUnidad) || $cantidadPorUnidad <= 0) {
                    return 'La cantidad por unidad debe ser un número entero mayor que cero.';
                }

                if ($idUnidadContenido <= 0) {
                    return 'Debes seleccionar la unidad de contenido de cada producto.';
                }
                if (!DB::table('units')->where('id_unidad', $idUnidadContenido)->exists()) {
                    return 'La unidad de contenido seleccionada no es válida.';
                }

                $subtotal = round($cantidad * $precioCompra, 2);
                $total += $subtotal;

                $itemsValidados[] = [
                    'id_producto'          => $idProducto,
                    'cantidad'             => $cantidad,
                    'precio_compra'        => $precioCompra,
                    'id_unidad'            => $idUnidad,
                    'cantidad_por_unidad'  => $cantidadPorUnidad,
                    'id_unidad_contenido'  => $idUnidadContenido,
                    'subtotal'             => $subtotal,
                ];
            }

            $total = round($total, 2);

            // --------------------------------------------------------
            // TRANSACCIÓN: crear compra + detalles + actualizar inventario
            // --------------------------------------------------------
            DB::beginTransaction();

            $idCompra = DB::table('purchases')->insertGetId([
                'id_proveedor' => $idProveedor,
                'id_usuario'   => $idUsuario,
                'fecha'        => now(),
                'total'        => $total,
                'estado'       => true,
            ], 'id_compra');

            foreach ($itemsValidados as $item) {

                DB::table('purchases_details')->insert([
                    'id_compra'           => $idCompra,
                    'id_producto'         => $item['id_producto'],
                    'cantidad'            => $item['cantidad'],
                    'precio_compra'       => $item['precio_compra'],
                    'cantidad_por_unidad' => $item['cantidad_por_unidad'],
                    'id_unidad'           => $item['id_unidad'],
                    'id_unidad_contenido' => $item['id_unidad_contenido'],
                    'subtotal'            => $item['subtotal'],
                ]);

                $unidadesInventario = $item['cantidad'] * $item['cantidad_por_unidad'];

                $inventario = DB::table('inventories')
                    ->where('id_producto', $item['id_producto'])
                    ->lockForUpdate()
                    ->first();

                if ($inventario) {
                    DB::table('inventories')
                        ->where('id_producto', $item['id_producto'])
                        ->update([
                            'stock_actual'        => DB::raw('stock_actual + ' . (int) $unidadesInventario),
                            'fecha_actualizacion' => now(),
                        ]);
                } else {
                    DB::table('inventories')->insert([
                        'id_producto'         => $item['id_producto'],
                        'stock_actual'        => $unidadesInventario,
                        'stock_minimo'        => 0,
                        'fecha_actualizacion' => now(),
                    ]);
                }
            }

            DB::commit();
            return true;

        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return 'Error al registrar la compra: ' . $e->getMessage();
        }
    }

    // ============================================================
    // ELIMINAR COMPRA
    //
    // Revierte el inventario que la compra había agregado. Si el
    // stock actual no alcanza para revertir (porque ya se vendió
    // parte de ese stock), se cancela la eliminación.
    // ============================================================
    public static function eliminar(int $idCompra)
    {
        if ($idCompra <= 0) {
            return 'Compra inválida.';
        }

        try {
            DB::beginTransaction();

            $detalles = DB::table('purchases_details')
                ->where('id_compra', $idCompra)
                ->get(['id_producto', 'cantidad', 'cantidad_por_unidad']);

            if ($detalles->isEmpty()) {
                DB::rollBack();
                return 'La compra no existe o ya fue eliminada.';
            }

            // Primero se valida TODO antes de tocar nada (todo o nada)
            foreach ($detalles as $d) {
                $unidadesARevertir = $d->cantidad * $d->cantidad_por_unidad;

                $inventario = DB::table('inventories')
                    ->where('id_producto', $d->id_producto)
                    ->lockForUpdate()
                    ->first();

                $stockActual = $inventario ? (float) $inventario->stock_actual : 0;

                if ($stockActual < $unidadesARevertir) {
                    DB::rollBack();
                    return 'No se puede eliminar: parte de este stock ya fue utilizado (vendido) y revertirlo dejaría el inventario en negativo.';
                }
            }

            // Ahora sí se revierte
            foreach ($detalles as $d) {
                $unidadesARevertir = $d->cantidad * $d->cantidad_por_unidad;

                DB::table('inventories')
                    ->where('id_producto', $d->id_producto)
                    ->update([
                        'stock_actual'        => DB::raw('stock_actual - ' . (int) $unidadesARevertir),
                        'fecha_actualizacion' => now(),
                    ]);
            }

            DB::table('purchases_details')->where('id_compra', $idCompra)->delete();
            DB::table('purchases')->where('id_compra', $idCompra)->delete();

            DB::commit();
            return true;

        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return 'Ocurrió un error al eliminar la compra. Inténtalo nuevamente.';
        }
    }

    // ============================================================
    // OBTENER DETALLE (para el modal de "Ver detalle")
    // ============================================================
    public static function obtenerDetalle(int $idCompra): array
    {
        return DB::table('purchases_details as dc')
            ->join('products as p', 'p.id_producto', '=', 'dc.id_producto')
            ->leftJoin('units as u', 'u.id_unidad', '=', 'dc.id_unidad')
            ->leftJoin('units as uc', 'uc.id_unidad', '=', 'dc.id_unidad_contenido')
            ->where('dc.id_compra', $idCompra)
            ->orderBy('dc.id_detalle')
            ->select([
                'dc.id_detalle',
                'p.nombre as producto',
                'u.nombre as unidad_compra',
                'dc.cantidad',
                'dc.precio_compra as precio_unitario',
                'dc.cantidad_por_unidad',
                'uc.nombre as unidad_contenido',
                'dc.subtotal',
            ])
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    // ============================================================
    // OBTENER TODAS LAS COMPRAS (para la tabla principal)
    // ============================================================
    public static function obtenerTodas(): array
    {
        return DB::table('purchases as c')
            ->join('suppliers as pr', 'pr.id_proveedor', '=', 'c.id_proveedor')
            ->join('people as pe', 'pe.id_persona', '=', 'pr.id_persona')
            ->leftJoin('users as u', 'u.id_usuario', '=', 'c.id_usuario')
            ->leftJoin('people as peu', 'peu.id_persona', '=', 'u.id_persona')
            ->orderByDesc('c.fecha')
            ->orderByDesc('c.id_compra')
            ->select([
                'c.id_compra',
                'c.fecha',
                'c.total',
                'pe.nombre as proveedor',
                'peu.nombre as comprador',
            ])
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    // ============================================================
    // RESUMEN (KPIs del panel)
    // ============================================================
    public static function obtenerResumen(): array
    {
        $fila = DB::table('purchases')
            ->selectRaw("
                COUNT(*) as total_compras,
                COALESCE(SUM(total), 0) as gasto_total,
                SUM(CASE WHEN DATE(fecha) = CURDATE() THEN 1 ELSE 0 END) as compras_hoy,
                COALESCE(SUM(CASE WHEN DATE(fecha) = CURDATE() THEN total ELSE 0 END), 0) as gasto_hoy
            ")
            ->first();

        return $fila ? (array) $fila : [
            'total_compras' => 0,
            'gasto_total'   => 0,
            'compras_hoy'   => 0,
            'gasto_hoy'     => 0,
        ];
    }

    // ============================================================
    // PROVEEDORES ACTIVOS (para el select del formulario)
    // ============================================================
    public static function obtenerProveedores(): array
    {
        return DB::table('suppliers as pr')
            ->join('people as pe', 'pe.id_persona', '=', 'pr.id_persona')
            ->where('pe.estado', true)
            ->orderBy('pe.nombre')
            ->select(['pr.id_proveedor', 'pe.nombre'])
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    // ============================================================
    // UNIDADES DE COMPRA (catálogo, para ambos selects del formulario)
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
    // PRODUCTOS ACTIVOS DEL CATÁLOGO
    // ============================================================
    public static function obtenerProductos(): array
    {
        return DB::table('products as p')
            ->leftJoin('categories as cat', 'cat.id_categoria', '=', 'p.id_categoria')
            ->where('p.estado', true)
            ->orderBy('p.nombre')
            ->select(['p.id_producto', 'p.nombre as producto', 'cat.tipo as categoria'])
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }
}