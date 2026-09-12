<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Modelo Cliente
 *
 * Tabla: customers (id_cliente, id_persona, id_usuario, fecha_registro,
 * estado, timestamps). id_usuario es FK a users y guarda quién
 * registró cada cliente.
 *
 * Relación: pertenece a Persona (tabla people, id_persona).
 *
 * obtenerTodos() acepta un parámetro opcional $idUsuario:
 *   - null (o no se pasa) -> Administrador: ve TODOS los clientes
 *   - un id_usuario        -> filtra solo los clientes registrados
 *                             por ESE vendedor
 */
class Cliente extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'id_persona',
        'fecha_registro',
        'estado',
        'id_usuario',
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
    //
    // $idUsuario = null -> admin: todos los clientes.
    // $idUsuario = <id> -> vendedor: solo customers.id_usuario = <id>
    // ============================================================
    public static function obtenerTodos(?int $idUsuario = null): array
    {
        return self::with('persona')
            ->when($idUsuario, fn ($query) => $query->where('id_usuario', $idUsuario))
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
            'id_usuario'     => $cliente->id_usuario,
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
    // Guarda id_usuario para saber quién lo registró.
    // ============================================================
    public static function registrar(array $datos, ?int $idUsuario = null)
    {
        try {
            return DB::transaction(function () use ($datos, $idUsuario) {
                $persona = Persona::create([
                    'nombre'   => $datos['nombre'],
                    'telefono' => $datos['telefono'],
                    'correo'   => $datos['correo'],
                ]);

                self::create([
                    'id_persona'     => $persona->id_persona,
                    'fecha_registro' => now(),
                    'estado'         => true,
                    'id_usuario'     => $idUsuario,
                ]);

                return true;
            });
        } catch (Throwable $e) {
            return "Error al registrar el cliente: " . $e->getMessage();
        }
    }

    // ============================================================
    // EDITAR CLIENTE
    // (no se reasigna dueño al editar)
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
    // ============================================================
    public static function tieneVentas($idCliente): bool
    {
        try {
            return DB::table('sales')->where('id_cliente', $idCliente)->count() > 0;
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