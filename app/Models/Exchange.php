<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exchange extends Model
{
    protected $table = 'canjes';
    protected $primaryKey = 'id_canje';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_premio',
        'cuenta_origen',
        'id_grupo_afectado',
        'puntos_gastados',
        'fecha_canje'
    ];

    protected $casts = [
        'fecha_canje' => 'datetime'
    ];

    public function reward()
    {
        return $this->belongsTo(Reward::class, 'id_premio', 'id_premio');
    }
}
