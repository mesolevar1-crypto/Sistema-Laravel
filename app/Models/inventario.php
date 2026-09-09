<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Modelo Inventario
 *
 * Tablas usadas (ya migradas a inglés):
 *   inventories  (antes 'inventario')  PK: id_inventario
 *   products     (antes 'producto')    PK: id_producto
 *   categories   (antes 'categoria')   PK: id_categoria
 *
 * Fuente principal de stock: inventories.stock_actual
 *
 * Las columnas internas se mantienen en español, igual que en
 * los demás modelos ya convertidos.
 */
class Inventario extends Model
{
    protected $table = 'inventories';
    protected $primaryKey = 'id_inventario';
    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'stock_actual',
        'stock_minimo',
        'fecha_actualizacion',
    ];

    // ============================================================
    // LISTA TODOS LOS PRODUCTOS CON SU INVENTARIO
    // (LEFT JOIN para incluir productos que aún no tienen registro
    // en inventories). Acepta búsqueda por nombre y filtro por estado.
    //
    // @param string $buscar  Texto a buscar en nombre del producto
    // @param string $estado  'disponible' | 'bajo' | 'agotado' | '' (todos)
    // ============================================================
    public static function obtenerTodos(string $buscar = '', string $estado = ''): array
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
                'i.id_inventario',
            ]);

        if ($buscar !== '') {
            $query->where('p.nombre', 'like', '%' . $buscar . '%');
        }

        if ($estado === 'agotado') {
            $query->whereRaw('COALESCE(i.stock_actual, 0) = 0');
        } elseif ($estado === 'bajo') {
            $query->whereRaw('COALESCE(i.stock_actual, 0) > 0')
                  ->whereRaw('COALESCE(i.stock_actual, 0) <= COALESCE(i.stock_minimo, 0)');
        } elseif ($estado === 'disponible') {
            $query->whereRaw('COALESCE(i.stock_actual, 0) > COALESCE(i.stock_minimo, 0)');
        }

        // Del más reciente al más antiguo; los productos sin registro
        // en inventories (fecha_actualizacion NULL) quedan al final.
        $query->orderByRaw('i.fecha_actualizacion DESC')
              ->orderBy('p.nombre', 'asc');

        return $query->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    // ============================================================
    // ESTADÍSTICAS PARA LAS TARJETAS SUPERIORES
    // ============================================================
    public static function obtenerResumen(): array
    {
        $fila = DB::table('products as p')
            ->leftJoin('inventories as i', 'p.id_producto', '=', 'i.id_producto')
            ->selectRaw("
                COUNT(p.id_producto) as total_productos,
                COALESCE(SUM(COALESCE(i.stock_actual, 0)), 0) as total_unidades,
                SUM(CASE
                    WHEN COALESCE(i.stock_actual,0) > 0
                     AND COALESCE(i.stock_actual,0) <= COALESCE(i.stock_minimo,0)
                    THEN 1 ELSE 0 END) as stock_bajo,
                SUM(CASE
                    WHEN COALESCE(i.stock_actual,0) = 0
                    THEN 1 ELSE 0 END) as agotados
            ")
            ->first();

        return $fila ? (array) $fila : [
            'total_productos' => 0,
            'total_unidades'  => 0,
            'stock_bajo'      => 0,
            'agotados'        => 0,
        ];
    }

    // ============================================================
    // ACTUALIZA stock_actual y stock_minimo.
    // Si el producto no tiene registro en inventories, lo crea.
    // ============================================================
    public static function actualizarStock(int $idProducto, int $stockActual, int $stockMinimo)
    {
        try {
            $existe = DB::table('inventories')
                ->where('id_producto', $idProducto)
                ->first(['id_inventario']);

            if ($existe) {
                DB::table('inventories')
                    ->where('id_producto', $idProducto)
                    ->update([
                        'stock_actual'        => $stockActual,
                        'stock_minimo'        => $stockMinimo,
                        'fecha_actualizacion' => now()->toDateString(),
                    ]);
            } else {
                DB::table('inventories')->insert([
                    'stock_actual'        => $stockActual,
                    'stock_minimo'        => $stockMinimo,
                    'fecha_actualizacion' => now()->toDateString(),
                    'id_producto'         => $idProducto,
                ]);
            }

            return true;

        } catch (Throwable $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}