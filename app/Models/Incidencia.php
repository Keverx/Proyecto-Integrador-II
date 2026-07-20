<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    protected $table = 'incidencias';
    protected $primaryKey = 'id_incidencia';

    protected $fillable = [
        'id_usuario',
        'id_tacho',
        'tipo_problema',
        'descripcion',
        'estado',
        'respuesta_admin',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function tacho()
    {
        return $this->belongsTo(Bin::class, 'id_tacho', 'id_tacho');
    }
}
