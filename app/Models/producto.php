<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Modelo Producto
 *
 * Tablas usadas (ya migradas a inglés):
 *   products     (antes 'producto')    PK: id_producto
 *   categories   (antes 'categoria')   PK: id_categoria
 *   inventories  (antes 'inventario')  PK: id_inventario
 *
 * Incluye también la gestión de categorías (categories), igual que
 * en el modelo legacy Producto.php, que manejaba productos y
 * categorías en un mismo archivo.
 *
 * 'products' ahora tiene la columna id_usuario (FK a users), que
 * guarda quién registró cada producto. Con esto:
 *
 * obtenerTodos() acepta un parámetro opcional $idUsuario:
 *   - null (o no se pasa) -> Administrador: ve TODOS los productos
 *   - un id_usuario        -> filtra solo los productos registrados
 *                             por ESE vendedor
 */
class Producto extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id_producto';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_categoria',
        'imagen',
        'estado',
        'id_usuario',
    ];

    // ============================================================
    // PRODUCTOS
    // ============================================================

    // ------------------------------------------------------------
    // OBTENER TODOS LOS PRODUCTOS (con categoría y stock)
    //
    // $idUsuario = null -> admin: todos los productos.
    // $idUsuario = <id> -> vendedor: solo los productos con
    //                      p.id_usuario = <id>.
    // ------------------------------------------------------------
    public static function obtenerTodos(?int $idUsuario = null): array
    {
        return DB::table('products as p')
            ->leftJoin('categories as c', 'p.id_categoria', '=', 'c.id_categoria')
            ->leftJoin('inventories as i', 'p.id_producto', '=', 'i.id_producto')
            ->when($idUsuario, fn ($query) => $query->where('p.id_usuario', $idUsuario))
            ->orderByDesc('p.id_producto')
            ->select([
                'p.id_producto',
                'p.nombre',
                'p.descripcion',
                'p.id_categoria',
                'p.imagen',
                'p.estado',
                'p.id_usuario',
                'c.tipo as categoria',
                DB::raw('COALESCE(i.stock_actual, 0) as stock_actual'),
                DB::raw('COALESCE(i.stock_minimo, 0) as stock_minimo'),
            ])
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    // ------------------------------------------------------------
    // OBTENER PRODUCTO POR ID
    // ------------------------------------------------------------
    public static function obtenerPorId(int $idProducto): ?array
    {
        $fila = DB::table('products as p')
            ->leftJoin('categories as c', 'p.id_categoria', '=', 'c.id_categoria')
            ->where('p.id_producto', $idProducto)
            ->select(['p.*', 'c.tipo as categoria'])
            ->first();

        return $fila ? (array) $fila : null;
    }

    // ------------------------------------------------------------
    // VERIFICAR NOMBRE DUPLICADO
    // ------------------------------------------------------------
    public static function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        $query = DB::table('products')->where('nombre', trim($nombre));

        if ($excluirId !== null) {
            $query->where('id_producto', '!=', $excluirId);
        }

        return $query->exists();
    }

    // ------------------------------------------------------------
    // REGISTRAR PRODUCTO
    // Retorna el id_producto (int) creado, o un string con el error.
    // Guarda id_usuario para saber quién lo registró.
    // ------------------------------------------------------------
    public static function registrar(array $datos)
    {
        try {
            $idProducto = DB::table('products')->insertGetId([
                'nombre'       => trim($datos['nombre'] ?? ''),
                'descripcion'  => !empty($datos['descripcion']) ? trim($datos['descripcion']) : null,
                'id_categoria' => !empty($datos['id_categoria']) ? (int) $datos['id_categoria'] : null,
                'imagen'       => !empty($datos['imagen']) ? trim($datos['imagen']) : null,
                'id_usuario'   => !empty($datos['id_usuario']) ? (int) $datos['id_usuario'] : null,
                'estado'       => true,
            ], 'id_producto');

            return (int) $idProducto;

        } catch (Throwable $e) {
            return 'Error al registrar producto: ' . $e->getMessage();
        }
    }

    // ------------------------------------------------------------
    // EDITAR PRODUCTO
    // (no se reasigna dueño al editar, igual que en Cliente/Venta)
    // ------------------------------------------------------------
    public static function editar(int $idProducto, array $datos)
    {
        try {
            $actualizar = [
                'nombre'       => trim($datos['nombre'] ?? ''),
                'descripcion'  => !empty($datos['descripcion']) ? trim($datos['descripcion']) : null,
                'id_categoria' => !empty($datos['id_categoria']) ? (int) $datos['id_categoria'] : null,
            ];

            if (!empty($datos['imagen'])) {
                $actualizar['imagen'] = trim($datos['imagen']);
            }

            DB::table('products')
                ->where('id_producto', $idProducto)
                ->update($actualizar);

            return true;

        } catch (Throwable $e) {
            return 'Error al editar producto: ' . $e->getMessage();
        }
    }

    // ------------------------------------------------------------
    // ACTIVAR / DESACTIVAR (toggle sobre el estado actual)
    // ------------------------------------------------------------
    public static function toggleEstado(int $idProducto)
    {
        try {
            $producto = DB::table('products')->where('id_producto', $idProducto)->first();

            if (!$producto) {
                return 'Producto no encontrado.';
            }

            DB::table('products')
                ->where('id_producto', $idProducto)
                ->update(['estado' => !$producto->estado]);

            return true;

        } catch (Throwable $e) {
            return 'Error al cambiar estado: ' . $e->getMessage();
        }
    }

    // ------------------------------------------------------------
    // ELIMINAR PRODUCTO
    // ------------------------------------------------------------
    public static function eliminar(int $idProducto)
    {
        try {
            $producto = self::obtenerPorId($idProducto);

            if (!$producto) {
                return 'El producto no existe.';
            }

            DB::table('products')->where('id_producto', $idProducto)->delete();

            return true;

        } catch (Throwable $e) {
            return 'No se puede eliminar el producto porque tiene información relacionada, como precios o registros de compras. Puedes desactivarlo en lugar de eliminarlo.';
        }
    }

    // ============================================================
    // CATEGORÍAS (catálogo compartido, sin dueño)
    // ============================================================

    // ------------------------------------------------------------
    // OBTENER TODAS LAS CATEGORÍAS
    // ------------------------------------------------------------
    public static function obtenerCategorias(): array
    {
        return DB::table('categories')
            ->orderBy('tipo')
            ->select(['id_categoria', 'tipo'])
            ->get()
            ->map(fn ($fila) => (array) $fila)
            ->all();
    }

    // ------------------------------------------------------------
    // VERIFICAR TIPO DE CATEGORÍA DUPLICADO
    // ------------------------------------------------------------
    public static function existeCategoriaTipo(string $tipo, ?int $excluirId = null): bool
    {
        $query = DB::table('categories')->where('tipo', trim($tipo));

        if ($excluirId !== null) {
            $query->where('id_categoria', '!=', $excluirId);
        }

        return $query->exists();
    }

    // ------------------------------------------------------------
    // REGISTRAR CATEGORÍA
    // ------------------------------------------------------------
    public static function registrarCategoria(array $datos)
    {
        try {
            DB::table('categories')->insert([
                'tipo' => trim($datos['tipo'] ?? ''),
            ]);

            return true;

        } catch (Throwable $e) {
            return 'Error al registrar categoría: ' . $e->getMessage();
        }
    }

    // ------------------------------------------------------------
    // EDITAR CATEGORÍA
    // ------------------------------------------------------------
    public static function editarCategoria(int $idCategoria, array $datos)
    {
        try {
            DB::table('categories')
                ->where('id_categoria', $idCategoria)
                ->update(['tipo' => trim($datos['tipo'] ?? '')]);

            return true;

        } catch (Throwable $e) {
            return 'Error al editar categoría: ' . $e->getMessage();
        }
    }

    // ------------------------------------------------------------
    // ELIMINAR CATEGORÍA
    // ------------------------------------------------------------
    public static function eliminarCategoria(int $idCategoria)
    {
        try {
            DB::table('categories')->where('id_categoria', $idCategoria)->delete();

            return true;

        } catch (Throwable $e) {
            return 'No se puede eliminar la categoría porque tiene productos asociados.';
        }
    }
}