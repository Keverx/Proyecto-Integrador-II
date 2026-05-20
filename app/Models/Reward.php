<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $table = 'premios_incentivos';
    protected $primaryKey = 'id_premio';
    public $timestamps = false;

    protected $fillable = [
        'nombre_premio',
        'descripcion',
        'costo_puntos',
        'tipo_premio',
        'stock_disponible',
        'icon',
        'color'
    ];
}
