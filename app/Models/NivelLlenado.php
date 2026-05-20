<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NivelLlenado extends Model
{
    protected $table = 'niveles_llenado';
    protected $primaryKey = 'id_nivel';
    public $timestamps = false;

    protected $fillable = [
        'id_tacho',
        'id_tipo_residuo',
        'porcentaje_llenado',
        'ultima_lectura'
    ];

    public function tipoResiduo()
    {
        return $this->belongsTo(WasteType::class, 'id_tipo_residuo', 'id_tipo_residuo');
    }
}
