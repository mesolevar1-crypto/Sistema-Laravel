<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Modelo Proveedor
 *
 * Tabla: suppliers (id_proveedor, id_persona, frecuencia_entrega, timestamps)
 * Relación: pertenece a Persona (tabla people, id_persona).
 *
 * Ajusta el nombre de $table si tu tabla real tiene otro nombre
 * (por ejemplo 'proveedor' si aún no la renombraste).
 */
class Proveedor extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'id_proveedor';

    protected $fillable = [
        'id_persona',
        'frecuencia_entrega',
    ];

    // ============================================================
    // RELACIÓN CON PERSONA
    // ============================================================
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    // ============================================================
    // OBTENER TODOS LOS PROVEEDORES (con datos de persona)
    // ============================================================
    public static function obtenerTodos(): array
    {
        return self::with('persona')
            ->orderByDesc('id_persona')
            ->get()
            ->map(fn ($p) => self::aplanar($p))
            ->all();
    }

    // ============================================================
    // OBTENER PROVEEDOR POR ID
    // ============================================================
    public static function obtenerPorId($idProveedor): ?array
    {
        $proveedor = self::with('persona')->find($idProveedor);

        return $proveedor ? self::aplanar($proveedor) : null;
    }

    // ============================================================
    // PROVEEDORES ACTIVOS (para selects en otros formularios)
    // ============================================================
    public static function obtenerActivos(): array
    {
        return self::with('persona')
            ->whereHas('persona', fn ($q) => $q->where('estado', true))
            ->get()
            ->map(fn ($p) => [
                'id_proveedor' => $p->id_proveedor,
                'nombre'       => $p->persona->nombre ?? '',
            ])
            ->all();
    }

    // ============================================================
    // Aplanar proveedor + persona a un array
    // ============================================================
    protected static function aplanar(self $proveedor): array
    {
        return [
            'id_proveedor'        => $proveedor->id_proveedor,
            'id_persona'          => $proveedor->id_persona,
            'frecuencia_entrega'  => $proveedor->frecuencia_entrega,
            'estado'              => ($proveedor->persona->estado ?? false) ? 'activo' : 'inactivo',
            'nombre'              => $proveedor->persona->nombre ?? '',
            'telefono'            => $proveedor->persona->telefono ?? '',
            'correo'              => $proveedor->persona->correo ?? '',
        ];
    }

    // ============================================================
    // REGISTRAR PROVEEDOR
    // ============================================================
    public static function registrar(array $datos)
    {
        try {
            return DB::transaction(function () use ($datos) {
                $persona = Persona::create([
                    'nombre'   => $datos['nombre'],
                    'telefono' => $datos['telefono'],
                    'correo'   => $datos['correo'],
                    'estado'   => true,
                ]);

                self::create([
                    'id_persona'         => $persona->id_persona,
                    'frecuencia_entrega' => $datos['frecuencia_entrega'],
                ]);

                return true;
            });
        } catch (Throwable $e) {
            return "Error al registrar: " . $e->getMessage();
        }
    }

    // ============================================================
    // EDITAR PROVEEDOR
    // ============================================================
    public static function editar($idProveedor, $nombre, $telefono, $correo, $frecuenciaEntrega)
    {
        try {
            $proveedor = self::find($idProveedor);

            if (!$proveedor) {
                return "Proveedor no encontrado.";
            }

            $proveedor->persona()->update([
                'nombre'   => $nombre,
                'telefono' => $telefono,
                'correo'   => $correo,
            ]);

            $proveedor->update([
                'frecuencia_entrega' => $frecuenciaEntrega,
            ]);

            return true;
        } catch (Throwable $e) {
            return "Error al actualizar: " . $e->getMessage();
        }
    }

    // ============================================================
    // ACTIVAR / DESACTIVAR (estado vive en persona)
    // ============================================================
    public static function toggleEstado($idProveedor)
    {
        try {
            $proveedor = self::find($idProveedor);

            if (!$proveedor || !$proveedor->persona) {
                return "Proveedor no encontrado.";
            }

            $persona = $proveedor->persona;
            $persona->estado = !$persona->estado;
            $persona->save();

            return true;
        } catch (Throwable $e) {
            return "Error al cambiar el estado: " . $e->getMessage();
        }
    }

    // ============================================================
    // VERIFICAR SI TIENE COMPRAS
    // NOTA: asume que aún existe una tabla 'compra' (legado) con
    // columna 'id_proveedor'. Ajusta el nombre si ya migraste
    // compras a un modelo Eloquent propio (p. ej. tabla 'purchases').
    // ============================================================
    public static function tieneCompras($idProveedor): bool
    {
        try {
            return DB::table('compra')->where('id_proveedor', $idProveedor)->count() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    // ============================================================
    // ELIMINAR PROVEEDOR (y su persona asociada, si no tiene compras)
    // ============================================================
    public static function eliminar($idProveedor)
    {
        try {
            $proveedor = self::find($idProveedor);

            if (!$proveedor) {
                return "Proveedor no encontrado.";
            }

            if (self::tieneCompras($idProveedor)) {
                return "No se puede eliminar: este proveedor tiene compras registradas.";
            }

            return DB::transaction(function () use ($proveedor) {
                $idPersona = $proveedor->id_persona;

                $proveedor->delete();

                Persona::where('id_persona', $idPersona)->delete();

                return true;
            });
        } catch (Throwable $e) {
            return "Error al eliminar: " . $e->getMessage();
        }
    }
}