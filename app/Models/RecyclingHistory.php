<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecyclingHistory extends Model
{
    protected $table = 'historial_reciclaje';
    protected $primaryKey = 'id_transaccion';
    public $timestamps = false; // Tu SQL solo tiene fecha_hora

    protected $fillable = [
        'id_usuario',
        'id_tacho',
        'id_tipo_residuo',
        'fecha_hora'
    ];
}
