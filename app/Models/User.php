<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Importante para que Kotlin pueda recibir el Token

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios'; // Conecta con tu tabla en español
    protected $primaryKey = 'id_usuario'; // Tu llave primaria personalizada
    public $timestamps = false; // Como tu SQL no tiene 'updated_at', desactivamos esto para evitar errores

    protected $fillable = [
        'nombre',
        'email',
        'password_hash', // Usamos tu columna de clave
        'codigo_verificacion',
        'token_recuperacion',
        'estado_cuenta',
        'id_rol',
    ];

    protected $hidden = [
        'password_hash', // Ocultamos la clave cuando Laravel mande datos a Kotlin
        'codigo_verificacion',
    ];

    /**
     * IMPORTANTE: Le decimos a Laravel que tu columna de contraseña
     * no se llama "password", sino "password_hash"
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Relación con la nueva tabla de transacciones de puntos
     */
    public function transacciones()
    {
        return $this->hasMany(PointTransaction::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Calcula automáticamente el saldo sumando INGRESOS y restando EGRESOS/PENALIZACIONES.
     * Esto reemplaza a tu antigua columna eco_puntos_personales.
     * Ahora puedes usar $user->puntos en tu código y Laravel lo calculará por ti.
     */
    public function getPuntosAttribute()
    {
        $ingresos = $this->transacciones()->where('tipo_movimiento', 'INGRESO')->sum('monto');
        $egresos = $this->transacciones()->whereIn('tipo_movimiento', ['EGRESO', 'PENALIZACION'])->sum('monto');
        return $ingresos - $egresos;
    }

    /**
     * Relación con la tabla roles
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'id_rol', 'id_rol');
    }

    /**
     * Verifica si el usuario tiene un rol específico a través de la relación.
     */
    public function hasRole(string $role)
    {
        return $this->role && strtoupper($this->role->nombre) === strtoupper($role);
    }
}