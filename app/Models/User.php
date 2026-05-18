<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'email',
        'password_hash',
        'codigo_verificacion',
        'token_recuperacion',
        'estado_cuenta',
        'id_rol',
    ];

    protected $hidden = [
        'password_hash',
        'codigo_verificacion',
    ];


    public function getAuthPassword()
    {
        return $this->password_hash;
    }


    public function transacciones()
    {
        return $this->hasMany(PointTransaction::class, 'id_usuario', 'id_usuario');
    }


    public function getPuntosAttribute()
    {
        $ingresos = $this->transacciones()->where('tipo_movimiento', 'INGRESO')->sum('monto');
        $egresos = $this->transacciones()->whereIn('tipo_movimiento', ['EGRESO', 'PENALIZACION'])->sum('monto');
        return $ingresos - $egresos;
    }


    public function role()
    {
        return $this->belongsTo(Role::class, 'id_rol', 'id_rol');
    }


    public function hasRole(string $role)
    {
        return $this->role && strtoupper($this->role->nombre) === strtoupper($role);
    }
}