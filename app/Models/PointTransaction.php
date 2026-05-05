<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    protected $table = 'transacciones_puntos';
    protected $primaryKey = 'id_movimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_grupo',
        'tipo_movimiento',
        'monto',
        'motivo',
        'referencia_id',
        'fecha_movimiento'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
