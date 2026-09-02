<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'people';
    protected $primaryKey = 'id_persona';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'telefono',
        'correo',
        'estado',
    ];

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_persona', 'id_persona');
    }
}