<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Modelo Inicio — KPIs y datos del panel principal.
 *
 * Tablas usadas (ya migradas a inglés):
 *   sales          (antes 'venta')          PK: id_venta
 *   sales_details  (antes 'detalle_venta')  PK: id_detalle
 *   products       (antes 'producto')       PK: id_producto
 *   inventories    (antes 'inventario')     PK: id_inventario
 *   customers      (antes 'cliente')        PK: id_cliente
 *   users          (antes 'usuario')        PK: id_usuario
 *
 * Todos los métodos relacionados con VENTAS aceptan un parámetro
 * opcional $idUsuario:
 *   - null (o no se pasa)  -> comportamiento igual que antes (Administrador: ve TODO)
 *   - un id_usuario        -> filtra solo las ventas de ESE vendedor
 *
 * Los métodos de INVENTARIO/PRODUCTOS (stockBajo, totalProductos) son
 * globales del negocio y no dependen del vendedor, así que no cambian.
 *
 * totalUsuarios() y gananciasSemana() (margen/costo) se dejan tal cual,
 * son de uso exclusivo del Administrador y no se deben llamar desde
 * la vista del vendedor.
 *
 * Stock bajo se calcula comparando i.stock_actual contra
 * i.stock_minimo (columna real de la tabla inventories).
 *
 * NOTA: se usan métodos estáticos a propósito para poder llamarlos
 * directamente desde la vista Blade (App\Models\Inicio::ventasDia())
 * sin necesidad de pasar por un controlador.
 */
class Inicio
{
    // =========================================================
    // VENTAS DEL DÍA (solo ventas activas, estado = 1)
    // =========================================================
    public static function ventasDia($idUsuario = null)
    {
        try {
            $sql = "SELECT COALESCE(SUM(total), 0) AS total
                    FROM sales
                    WHERE estado = 1 AND DATE(fecha) = CURDATE()"
                 . ($idUsuario ? " AND id_usuario = ?" : "");

            $bindings = $idUsuario ? [$idUsuario] : [];
            $fila = DB::selectOne($sql, $bindings);

            return (float) ($fila->total ?? 0);

        } catch (Throwable $e) {
            Log::error("Error ventasDia: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================
    // VENTAS DEL MES ACTUAL
    // =========================================================
    public static function ventasMes($idUsuario = null)
    {
        try {
            $sql = "SELECT COALESCE(SUM(total), 0) AS total
                    FROM sales
                    WHERE estado = 1
                      AND MONTH(fecha) = MONTH(CURDATE())
                      AND YEAR(fecha)  = YEAR(CURDATE())"
                 . ($idUsuario ? " AND id_usuario = ?" : "");

            $bindings = $idUsuario ? [$idUsuario] : [];
            $fila = DB::selectOne($sql, $bindings);

            return (float) ($fila->total ?? 0);

        } catch (Throwable $e) {
            Log::error("Error ventasMes: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================
    // GANANCIAS DE LOS ÚLTIMOS 7 DÍAS (incluye hoy)
    // Ganancia = subtotal - (costo_unitario * cantidad) por línea
    // SOLO ADMINISTRADOR: expone el costo de los productos.
    // No pasarle id_usuario ni usar desde la vista del vendedor.
    // =========================================================
    public static function gananciasSemana()
    {
        try {
            $sql = "SELECT COALESCE(SUM(dv.subtotal - (dv.costo_unitario * dv.cantidad)), 0) AS ganancia
                    FROM sales_details dv
                    INNER JOIN sales v ON v.id_venta = dv.id_venta
                    WHERE v.estado = 1
                      AND v.fecha >= (CURDATE() - INTERVAL 6 DAY)";

            $fila = DB::selectOne($sql);

            return (float) ($fila->ganancia ?? 0);

        } catch (Throwable $e) {
            Log::error("Error gananciasSemana: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================
    // INGRESOS TOTALES ACUMULADOS (histórico completo)
    // Para el vendedor: sus ingresos de siempre.
    // Para el admin (sin id_usuario): ingresos de todo el negocio.
    // =========================================================
    public static function totalIngresos($idUsuario = null)
    {
        try {
            $sql = "SELECT COALESCE(SUM(total), 0) AS total
                    FROM sales
                    WHERE estado = 1"
                 . ($idUsuario ? " AND id_usuario = ?" : "");

            $bindings = $idUsuario ? [$idUsuario] : [];
            $fila = DB::selectOne($sql, $bindings);

            return (float) ($fila->total ?? 0);

        } catch (Throwable $e) {
            Log::error("Error totalIngresos: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================
    // TICKET PROMEDIO (valor promedio por venta)
    // =========================================================
    public static function ticketPromedio($idUsuario = null)
    {
        try {
            $sql = "SELECT COALESCE(AVG(total), 0) AS promedio
                    FROM sales
                    WHERE estado = 1"
                 . ($idUsuario ? " AND id_usuario = ?" : "");

            $bindings = $idUsuario ? [$idUsuario] : [];
            $fila = DB::selectOne($sql, $bindings);

            return (float) ($fila->promedio ?? 0);

        } catch (Throwable $e) {
            Log::error("Error ticketPromedio: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================
    // PRODUCTOS CON STOCK BAJO (global del negocio, no cambia)
    // =========================================================
    public static function stockBajo()
    {
        try {
            $sql = "SELECT COUNT(*) AS total
                    FROM inventories i
                    INNER JOIN products p ON p.id_producto = i.id_producto
                    WHERE p.estado = 1
                      AND i.stock_actual <= i.stock_minimo";

            $fila = DB::selectOne($sql);

            return (int) ($fila->total ?? 0);

        } catch (Throwable $e) {
            Log::error("Error stockBajo: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================
    // TOTAL DE CLIENTES REGISTRADOS
    // =========================================================
    public static function totalClientes()
    {
        try {
            $sql = "SELECT COUNT(*) AS total FROM customers";
            $fila = DB::selectOne($sql);

            return (int) ($fila->total ?? 0);

        } catch (Throwable $e) {
            Log::error("Error totalClientes: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================
    // TOTAL DE PRODUCTOS ACTIVOS (global del negocio, no cambia)
    // =========================================================
    public static function totalProductos()
    {
        try {
            $sql = "SELECT COUNT(*) AS total FROM products WHERE estado = 1";
            $fila = DB::selectOne($sql);

            return (int) ($fila->total ?? 0);

        } catch (Throwable $e) {
            Log::error("Error totalProductos: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================
    // CANTIDAD DE VENTAS DE HOY (para el subtítulo de la tarjeta)
    // =========================================================
    public static function contarVentasHoy($idUsuario = null)
    {
        try {
            $sql = "SELECT COUNT(*) AS total
                    FROM sales
                    WHERE estado = 1 AND DATE(fecha) = CURDATE()"
                 . ($idUsuario ? " AND id_usuario = ?" : "");

            $bindings = $idUsuario ? [$idUsuario] : [];
            $fila = DB::selectOne($sql, $bindings);

            return (int) ($fila->total ?? 0);

        } catch (Throwable $e) {
            Log::error("Error contarVentasHoy: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================
    // CANTIDAD DE VENTAS DEL MES (para el subtítulo de la tarjeta)
    // =========================================================
    public static function contarVentasMes($idUsuario = null)
    {
        try {
            $sql = "SELECT COUNT(*) AS total
                    FROM sales
                    WHERE estado = 1
                      AND MONTH(fecha) = MONTH(CURDATE())
                      AND YEAR(fecha)  = YEAR(CURDATE())"
                 . ($idUsuario ? " AND id_usuario = ?" : "");

            $bindings = $idUsuario ? [$idUsuario] : [];
            $fila = DB::selectOne($sql, $bindings);

            return (int) ($fila->total ?? 0);

        } catch (Throwable $e) {
            Log::error("Error contarVentasMes: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================
    // TOTAL DE USUARIOS REGISTRADOS (activos)
    // SOLO ADMINISTRADOR. No usar desde la vista del vendedor.
    // =========================================================
    public static function totalUsuarios()
    {
        try {
            $sql = "SELECT COUNT(*) AS total FROM users WHERE estado = 1";
            $fila = DB::selectOne($sql);

            return (int) ($fila->total ?? 0);

        } catch (Throwable $e) {
            Log::error("Error totalUsuarios: " . $e->getMessage());
            return 0;
        }
    }

    // =========================================================
    // VENTAS DE LOS ÚLTIMOS 7 DÍAS (para la gráfica)
    // Devuelve un arreglo asociativo ['2026-08-17' => 125000.0, ...]
    // con los 7 días completos, incluso los que no tuvieron ventas.
    // =========================================================
    public static function ventasUltimos7Dias($idUsuario = null)
    {
        try {
            $sql = "SELECT DATE(fecha) AS dia, COALESCE(SUM(total), 0) AS total
                    FROM sales
                    WHERE estado = 1
                      AND fecha >= (CURDATE() - INTERVAL 6 DAY)"
                 . ($idUsuario ? " AND id_usuario = ?" : "") . "
                    GROUP BY DATE(fecha)
                    ORDER BY dia ASC";

            $bindings = $idUsuario ? [$idUsuario] : [];
            $filas = DB::select($sql, $bindings);

            // Rellenar los 7 días aunque algún día no tenga ventas
            $resultado = [];
            for ($i = 6; $i >= 0; $i--) {
                $fecha = date('Y-m-d', strtotime("-$i day"));
                $resultado[$fecha] = 0.0;
            }
            foreach ($filas as $f) {
                $resultado[$f->dia] = (float) $f->total;
            }

            return $resultado;

        } catch (Throwable $e) {
            Log::error("Error ventasUltimos7Dias: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================
    // TOP PRODUCTOS MÁS VENDIDOS (por cantidad, ventas activas)
    // =========================================================
    public static function productosMasVendidos($limite = 5, $idUsuario = null)
    {
        try {
            $sql = "SELECT p.nombre, SUM(dv.cantidad) AS cantidad_vendida
                    FROM sales_details dv
                    INNER JOIN sales v    ON v.id_venta = dv.id_venta
                    INNER JOIN products p ON p.id_producto = dv.id_producto
                    WHERE v.estado = 1"
                 . ($idUsuario ? " AND v.id_usuario = ?" : "") . "
                    GROUP BY dv.id_producto, p.nombre
                    ORDER BY cantidad_vendida DESC
                    LIMIT " . (int) $limite;

            $bindings = $idUsuario ? [$idUsuario] : [];
            $filas = DB::select($sql, $bindings);

            // Convertir de stdClass a array asociativo, igual que fetchAll(PDO::FETCH_ASSOC)
            return array_map(fn ($f) => (array) $f, $filas);

        } catch (Throwable $e) {
            Log::error("Error productosMasVendidos: " . $e->getMessage());
            return [];
        }
    }
}