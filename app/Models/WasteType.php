<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteType extends Model
{
    protected $table = 'tipos_residuo';
    protected $primaryKey = 'id_tipo_residuo';
    public $timestamps = false;

    protected $fillable = [
        'nombre_residuo',
        'puntos_otorgados',
        'descripcion'
    ];
}
