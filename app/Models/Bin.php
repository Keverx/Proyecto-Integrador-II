<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bin extends Model
{
    // Si tu tabla en MySQL se llama "tachos", ponlo aquí:
    protected $table = 'tachos'; 

    // Si tu llave primaria no se llama "id", sino "id_tacho", ponlo aquí:
    protected $primaryKey = 'id_tacho';
    
    // IMPORTANTE: Tu tabla no tiene created_at ni updated_at, apagamos esto para evitar errores
    public $timestamps = false; 

    protected $fillable = [
        'codigo_qr',
        'pin_actual',
        'expiracion_pin',
        'estado_operativo',
        'ultima_conexion',
        'fecha_registro'
    ];
}
