<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    protected $table = 'grupos';
    protected $primaryKey = 'id_grupo';
    public $timestamps = false;

    protected $fillable = [
        'nombre_grupo',
        'codigo_invitacion'
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_grupos', 'id_grupo', 'id_usuario')
                    ->withPivot('rol_en_grupo', 'fecha_union');
    }

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