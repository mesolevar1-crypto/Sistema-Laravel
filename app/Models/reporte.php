<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Modelo Reporte
 *
 * Tablas usadas (ya migradas a inglés):
 *   sales             (antes 'venta')            PK: id_venta
 *   sales_details     (antes 'detalle_venta')    PK: id_detalle
 *   invoices          (antes 'factura')          PK: id_factura
 *   purchases         (antes 'compra')           PK: id_compra
 *   purchases_details (antes 'detalle_compra')   PK: id_detalle
 *   products          (antes 'producto')         PK: id_producto
 *   categories        (antes 'categoria')        PK: id_categoria
 *   inventories       (antes 'inventario')       PK: id_inventario
 *   customers         (antes 'cliente')          PK: id_cliente
 *   suppliers         (antes 'proveedor')        PK: id_proveedor
 *   people            (antes 'persona')          PK: id_persona
 *   users             (antes 'usuario')          PK: id_usuario
 *
 * No existe tabla de precios de producto: la ganancia por línea de
 * venta se calcula igual que en Venta::obtenerTodas():
 *     sales_details.subtotal - (sales_details.costo_unitario * sales_details.cantidad)
 * Esto garantiza que los reportes muestren EXACTAMENTE los mismos
 * números que el módulo de Ventas.
 *
 * Las consultas con subconsultas correlacionadas o GROUP BY dinámico
 * (reporteVentas, gananciasPorPeriodo) se escriben con DB::select()
 * en SQL crudo, igual que el modelo legacy con PDO, porque es la
 * forma más clara y confiable de expresarlas.
 */
class Reporte extends Model
{
    // Modelo solo de consultas de solo-lectura, no representa una tabla propia.
    protected $table = 'sales';

    // ============================================================
    // KPIs PARA EL INDEX
    // ============================================================
    public static function ventasHoy(): array
    {
        $fila = DB::table('sales')
            ->whereRaw('fecha = CURDATE()')
            ->where('estado', true)
            ->selectRaw('COALESCE(SUM(total), 0) as valor, COUNT(*) as cantidad')
            ->first();

        return $fila ? (array) $fila : ['valor' => 0, 'cantidad' => 0];
    }

    public static function ventasMes(): array
    {
        $fila = DB::table('sales')
            ->whereRaw('YEAR(fecha) = YEAR(CURDATE())')
            ->whereRaw('MONTH(fecha) = MONTH(CURDATE())')
            ->where('estado', true)
            ->selectRaw('COALESCE(SUM(total), 0) as valor, COUNT(*) as cantidad')
            ->first();

        return $fila ? (array) $fila : ['valor' => 0, 'cantidad' => 0];
    }

    public static function comprasMes(): array
    {
        // 'purchases' no tiene concepto de "anulada": Compra::eliminar()
        // borra la fila físicamente, así que no hace falta filtrar
        // por estado -- lo que existe en la tabla ya está vigente.
        $fila = DB::table('purchases')
            ->whereRaw('YEAR(fecha) = YEAR(CURDATE())')
            ->whereRaw('MONTH(fecha) = MONTH(CURDATE())')
            ->selectRaw('COALESCE(SUM(total), 0) as valor, COUNT(*) as cantidad')
            ->first();

        return $fila ? (array) $fila : ['valor' => 0, 'cantidad' => 0];
    }

    // Misma fórmula por línea que usa Venta::obtenerTodas():
    //   ganancia_linea = subtotal - (costo_unitario * cantidad)
    public static function gananciasMes(): array
    {
        $fila = DB::table('sales_details as dv')
            ->join('sales as v', 'dv.id_venta', '=', 'v.id_venta')
            ->whereRaw('YEAR(v.fecha) = YEAR(CURDATE())')
            ->whereRaw('MONTH(v.fecha) = MONTH(CURDATE())')
            ->where('v.estado', true)
            ->selectRaw('COALESCE(SUM(dv.subtotal - (dv.costo_unitario * dv.cantidad)), 0) as valor')
            ->first();

        return $fila ? (array) $fila : ['valor' => 0];
    }

    public static function contarStockBajo(): int
    {
        return (int) DB::table('inventories')
            ->where('stock_actual', '>', 0)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->count();
    }

    public static function contarAgotados(): int
    {
        return (int) DB::table('inventories')->where('stock_actual', 0)->count();
    }

    // ============================================================
    // REPORTE DE COMPRAS CON FILTROS
    // ============================================================
    public static function reporteCompras(string $desde, string $hasta, int $idProv = 0): array
    {
        $sql = "SELECT
                    c.id_compra,
                    c.fecha,
                    c.total,
                    pe_prov.nombre AS proveedor,
                    pe_usr.nombre  AS comprador
                FROM purchases c
                LEFT JOIN suppliers pr      ON c.id_proveedor = pr.id_proveedor
                LEFT JOIN people    pe_prov ON pr.id_persona  = pe_prov.id_persona
                LEFT JOIN users     u       ON c.id_usuario   = u.id_usuario
                LEFT JOIN people    pe_usr  ON u.id_persona   = pe_usr.id_persona
                WHERE c.fecha BETWEEN ? AND ?";

        $bindings = [$desde, $hasta];

        if ($idProv > 0) {
            $sql .= " AND c.id_proveedor = ?";
            $bindings[] = $idProv;
        }

        $sql .= " ORDER BY c.fecha DESC";

        return array_map(fn ($fila) => (array) $fila, DB::select($sql, $bindings));
    }

    public static function listaProveedores(): array
    {
        return DB::table('suppliers as pr')
            ->join('people as pe', 'pr.id_persona', '=', 'pe.id_persona')
            ->where('pe.estado', true)
            ->orderBy('pe.nombre')
            ->select(['pr.id_proveedor', 'pe.nombre'])
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    // ============================================================
    // REPORTE DE VENTAS CON GANANCIAS
    // ============================================================
    public static function reporteVentas(string $desde, string $hasta, int $idUsuario = 0): array
    {
        $sql = "SELECT
                    v.id_venta,
                    v.fecha,
                    v.total,
                    v.metodo_pago,
                    f.numero_factura,
                    pc.nombre AS cliente,
                    pu.nombre AS vendedor,
                    COALESCE((
                        SELECT SUM(dv2.subtotal - (dv2.costo_unitario * dv2.cantidad))
                        FROM sales_details dv2
                        WHERE dv2.id_venta = v.id_venta
                    ), 0) AS ganancia
                FROM sales v
                LEFT JOIN customers c  ON v.id_cliente = c.id_cliente
                LEFT JOIN people    pc ON c.id_persona = pc.id_persona
                LEFT JOIN users     u  ON v.id_usuario = u.id_usuario
                LEFT JOIN people    pu ON u.id_persona = pu.id_persona
                LEFT JOIN invoices  f  ON f.id_venta   = v.id_venta
                WHERE v.fecha BETWEEN ? AND ?
                  AND v.estado = 1";

        $bindings = [$desde, $hasta];

        if ($idUsuario > 0) {
            $sql .= " AND v.id_usuario = ?";
            $bindings[] = $idUsuario;
        }

        $sql .= " ORDER BY v.fecha DESC";

        return array_map(fn ($fila) => (array) $fila, DB::select($sql, $bindings));
    }

    // ============================================================
    // GANANCIAS AGRUPADAS (día / semana / mes) PARA EL GRÁFICO
    // ============================================================
    public static function gananciasPorPeriodo(string $desde, string $hasta, string $agrupacion = 'dia', int $idUsuario = 0): array
    {
        switch ($agrupacion) {
            case 'semana':
                $grupo    = 'YEARWEEK(v.fecha, 3)';
                $etiqueta = "CONCAT('Sem. ', WEEK(v.fecha, 3), ' · ', YEAR(v.fecha))";
                break;
            case 'mes':
                $grupo    = "DATE_FORMAT(v.fecha, '%Y-%m')";
                $etiqueta = "DATE_FORMAT(v.fecha, '%M %Y')";
                break;
            default: // dia
                $grupo    = 'v.fecha';
                $etiqueta = "DATE_FORMAT(v.fecha, '%d/%m')";
        }

        $sql = "SELECT
                    $grupo AS periodo_key,
                    MIN($etiqueta) AS periodo_label,
                    MIN(v.fecha)   AS fecha_orden,
                    COUNT(*)       AS cantidad,
                    COALESCE(SUM(v.total), 0) AS total_vendido,
                    COALESCE(SUM((
                        SELECT SUM(dv2.subtotal - (dv2.costo_unitario * dv2.cantidad))
                        FROM sales_details dv2
                        WHERE dv2.id_venta = v.id_venta
                    )), 0) AS ganancia
                FROM sales v
                WHERE v.fecha BETWEEN ? AND ?
                  AND v.estado = 1";

        $bindings = [$desde, $hasta];

        if ($idUsuario > 0) {
            $sql .= " AND v.id_usuario = ?";
            $bindings[] = $idUsuario;
        }

        $sql .= " GROUP BY $grupo ORDER BY fecha_orden ASC";

        return array_map(fn ($fila) => (array) $fila, DB::select($sql, $bindings));
    }

    public static function listaUsuarios(): array
    {
        return DB::table('users as u')
            ->join('people as p', 'u.id_persona', '=', 'p.id_persona')
            ->orderBy('p.nombre')
            ->select(['u.id_usuario', 'p.nombre'])
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    // ============================================================
    // REPORTE DE INVENTARIO CON FILTROS
    // ============================================================
    public static function reporteInventario(string $buscar = '', int $idCat = 0, string $estado = ''): array
    {
        $query = DB::table('products as p')
            ->leftJoin('categories as c', 'p.id_categoria', '=', 'c.id_categoria')
            ->leftJoin('inventories as i', 'p.id_producto', '=', 'i.id_producto')
            ->select([
                'p.id_producto',
                'p.nombre as producto',
                'c.tipo as categoria',
                DB::raw('COALESCE(i.stock_actual, 0) as stock_actual'),
                DB::raw('COALESCE(i.stock_minimo, 0) as stock_minimo'),
                'i.fecha_actualizacion',
            ]);

        if ($buscar !== '') {
            $query->where('p.nombre', 'like', '%' . $buscar . '%');
        }

        if ($idCat > 0) {
            $query->where('p.id_categoria', $idCat);
        }

        if ($estado === 'agotado') {
            $query->whereRaw('COALESCE(i.stock_actual, 0) = 0');
        } elseif ($estado === 'bajo') {
            $query->whereRaw('COALESCE(i.stock_actual, 0) > 0')
                  ->whereRaw('COALESCE(i.stock_actual, 0) <= COALESCE(i.stock_minimo, 0)');
        } elseif ($estado === 'disponible') {
            $query->whereRaw('COALESCE(i.stock_actual, 0) > COALESCE(i.stock_minimo, 0)');
        }

        $query->orderByRaw('COALESCE(i.stock_actual, 0) ASC')
              ->orderBy('p.nombre', 'asc');

        return $query->get()->map(fn ($fila) => (array) $fila)->all();
    }

    public static function listaCategorias(): array
    {
        return DB::table('categories')
            ->orderBy('tipo')
            ->select(['id_categoria', 'tipo'])
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }
}