<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'users';
    protected $primaryKey = 'id_usuario';
    public $timestamps = true;

    protected $fillable = [
        'id_persona',
        'id_rol',
        'contraseña',
        'estado',
    ];

    protected $hidden = [
        'contraseña',
    ];

    /**
     * Un usuario pertenece a una persona
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    /**
     * Un usuario pertenece a un rol
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    /**
     * Laravel busca por defecto el campo "password" para
     * verificar la contraseña en el sistema de autenticación.
     * Como tu columna se llama "contraseña", se lo indicamos
     * explícitamente aquí.
     */
    public function getAuthPassword()
    {
        return $this->contraseña;
    }
}