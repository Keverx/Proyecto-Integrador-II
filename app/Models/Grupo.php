<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $table = 'grupos';
    protected $primaryKey = 'id_grupo';
    public $timestamps = false;

    public function tachos()
    {
        return $this->belongsToMany(Bin::class, 'grupo_tachos', 'id_grupo', 'id_tacho');
    }

    public function transacciones()
    {
        return $this->hasMany(PointTransaction::class, 'id_grupo', 'id_grupo');
    }

    public function getPuntosGrupalesAttribute()
    {
        $ingresos = $this->transacciones()->where('tipo_movimiento', 'INGRESO')->sum('monto');
        $egresos = $this->transacciones()->whereIn('tipo_movimiento', ['EGRESO', 'PENALIZACION'])->sum('monto');
        return $ingresos - $egresos;
    }
}
