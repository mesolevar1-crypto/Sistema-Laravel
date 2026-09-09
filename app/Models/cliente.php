<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Modelo Cliente
 *
 * Tabla: customers (id_cliente, id_persona, fecha_registro, estado, timestamps)
 * Relación: pertenece a Persona (tabla people, id_persona).
 *
 * Se dejan métodos estáticos con nombres equivalentes al modelo PHP
 * original para poder llamarlos directo desde la vista o desde las
 * rutas (closures) sin necesidad de un controlador dedicado.
 */
class Cliente extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'id_persona',
        'fecha_registro',
        'estado',
    ];

    protected $casts = [
        'fecha_registro' => 'date',
        'estado'         => 'boolean',
    ];

    // ============================================================
    // RELACIÓN CON PERSONA
    // ============================================================
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    // ============================================================
    // OBTENER TODOS LOS CLIENTES (con datos de persona)
    // ============================================================
    public static function obtenerTodos(): array
    {
        return self::with('persona')
            ->orderByDesc('fecha_registro')
            ->get()
            ->map(fn ($c) => self::aplanar($c))
            ->all();
    }

    // ============================================================
    // OBTENER CLIENTE POR ID
    // ============================================================
    public static function obtenerPorId($idCliente): ?array
    {
        $cliente = self::with('persona')->find($idCliente);

        return $cliente ? self::aplanar($cliente) : null;
    }

    // ============================================================
    // Aplanar cliente + persona a un array (como el SELECT original)
    // ============================================================
    protected static function aplanar(self $cliente): array
    {
        return [
            'id_cliente'     => $cliente->id_cliente,
            'id_persona'     => $cliente->id_persona,
            'fecha_registro' => optional($cliente->fecha_registro)->format('Y-m-d'),
            'estado'         => $cliente->estado ? 'activo' : 'inactivo',
            'nombre'         => $cliente->persona->nombre ?? '',
            'telefono'       => $cliente->persona->telefono ?? '',
            'correo'         => $cliente->persona->correo ?? '',
        ];
    }

    // ============================================================
    // VERIFICAR CORREO
    // ============================================================
    public static function existeCorreo($correo): bool
    {
        if (empty($correo)) {
            return false;
        }

        return Persona::where('correo', $correo)->exists();
    }

    // ============================================================
    // REGISTRAR CLIENTE
    // ============================================================
    public static function registrar(array $datos)
    {
        try {
            return DB::transaction(function () use ($datos) {
                $persona = Persona::create([
                    'nombre'   => $datos['nombre'],
                    'telefono' => $datos['telefono'],
                    'correo'   => $datos['correo'],
                ]);

                self::create([
                    'id_persona'     => $persona->id_persona,
                    'fecha_registro' => now(),
                    'estado'         => true,
                ]);

                return true;
            });
        } catch (Throwable $e) {
            return "Error al registrar el cliente: " . $e->getMessage();
        }
    }

    // ============================================================
    // EDITAR CLIENTE
    // ============================================================
    public static function editarCompleto($idCliente, $nombre, $telefono, $correo)
    {
        try {
            $cliente = self::find($idCliente);

            if (!$cliente) {
                return "Cliente no encontrado.";
            }

            $cliente->persona()->update([
                'nombre'   => $nombre,
                'telefono' => $telefono,
                'correo'   => $correo,
            ]);

            return true;
        } catch (Throwable $e) {
            return "Error al actualizar: " . $e->getMessage();
        }
    }

    // ============================================================
    // CAMBIAR ESTADO (activo/inactivo)
    // ============================================================
    public static function cambiarEstado($idCliente)
    {
        try {
            $cliente = self::find($idCliente);

            if (!$cliente) {
                return "Cliente no encontrado.";
            }

            $cliente->estado = !$cliente->estado;
            $cliente->save();

            return true;
        } catch (Throwable $e) {
            return "Error al cambiar el estado: " . $e->getMessage();
        }
    }

    // ============================================================
    // VERIFICAR SI TIENE VENTAS
    // NOTA: asume que aún existe una tabla 'venta' (legado) con
    // columna 'id_cliente'. Ajusta el nombre si ya migraste ventas
    // a un modelo Eloquent propio (p. ej. tabla 'sales').
    // ============================================================
    public static function tieneVentas($idCliente): bool
    {
        try {
            return DB::table('venta')->where('id_cliente', $idCliente)->count() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    // ============================================================
    // ELIMINAR CLIENTE (y su persona asociada, si no tiene ventas)
    // ============================================================
    public static function eliminarCliente($idCliente)
    {
        try {
            $cliente = self::find($idCliente);

            if (!$cliente) {
                return "Cliente no encontrado.";
            }

            if (self::tieneVentas($idCliente)) {
                return "No se puede eliminar este cliente porque tiene ventas registradas.";
            }

            return DB::transaction(function () use ($cliente) {
                $idPersona = $cliente->id_persona;

                $cliente->delete();

                Persona::where('id_persona', $idPersona)->delete();

                return true;
            });
        } catch (Throwable $e) {
            return "Error al eliminar: " . $e->getMessage();
        }
    }
}