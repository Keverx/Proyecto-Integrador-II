<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- Módulo 1: Hardware e IoT ---
        Schema::create('tachos', function (Blueprint $table) {
            $table->id('id_tacho');
            $table->string('codigo_qr', 100)->unique();
            $table->string('pin_actual', 6)->nullable();
            $table->timestamp('expiracion_pin')->nullable();
            $table->enum('estado_operativo', ['ACTIVO', 'MANTENIMIENTO', 'INACTIVO', 'ALERTA'])->default('ACTIVO');
            $table->timestamp('ultima_conexion')->useCurrent()->nullable();
            $table->timestamp('fecha_registro')->useCurrent()->nullable();
        });

        Schema::create('tipos_residuo', function (Blueprint $table) {
            $table->id('id_tipo_residuo');
            $table->string('nombre_residuo', 50);
            $table->integer('puntos_otorgados');
            $table->string('descripcion', 255)->nullable();
        });

        Schema::create('niveles_tacho', function (Blueprint $table) {
            $table->id('id_nivel');
            $table->unsignedBigInteger('id_tacho');
            $table->unsignedBigInteger('id_tipo_residuo');
            $table->decimal('porcentaje_llenado', 5, 2)->default(0.00)->nullable();
            $table->timestamp('ultima_lectura')->useCurrent()->nullable();

            $table->foreign('id_tacho')->references('id_tacho')->on('tachos');
            $table->foreign('id_tipo_residuo')->references('id_tipo_residuo')->on('tipos_residuo');
        });

        Schema::create('eventos_mantenimiento', function (Blueprint $table) {
            $table->id('id_evento');
            $table->unsignedBigInteger('id_tacho');
            $table->enum('tipo_evento', ['VACIADO', 'REINICIO', 'ERROR_SENSOR', 'OFFLINE', 'CALIBRACION']);
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_hora')->useCurrent()->nullable();
            
            $table->foreign('id_tacho')->references('id_tacho')->on('tachos');
        });

        // --- Módulo 2: Autenticación y Social ---
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombre', 100);
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);
            $table->string('codigo_verificacion', 10)->nullable();
            $table->string('token_recuperacion', 100)->nullable();
            $table->timestamp('expiracion_token')->nullable();
            $table->enum('estado_cuenta', ['PENDIENTE', 'VERIFICADO', 'BLOQUEADO'])->default('PENDIENTE')->nullable();
            $table->timestamp('fecha_registro')->useCurrent()->nullable();
        });

        Schema::create('grupos', function (Blueprint $table) {
            $table->id('id_grupo');
            $table->string('nombre_grupo', 100);
            $table->timestamp('fecha_creacion')->useCurrent()->nullable();
        });

        Schema::create('usuario_grupos', function (Blueprint $table) {
            $table->id('id_usuario_grupo');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_grupo');
            $table->enum('rol_en_grupo', ['ADMIN_GRUPO', 'MIEMBRO'])->default('MIEMBRO')->nullable();
            $table->timestamp('fecha_union')->useCurrent()->nullable();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos');
        });

        Schema::create('grupo_tachos', function (Blueprint $table) {
            $table->id('id_grupo_tacho');
            $table->unsignedBigInteger('id_grupo');
            $table->unsignedBigInteger('id_tacho');
            $table->timestamp('fecha_asignacion')->useCurrent()->nullable();

            $table->foreign('id_grupo')->references('id_grupo')->on('grupos');
            $table->foreign('id_tacho')->references('id_tacho')->on('tachos');
        });

        // --- Módulo 3: Core Transaccional ---
        Schema::create('historial_reciclaje', function (Blueprint $table) {
            $table->id('id_transaccion');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_tacho');
            $table->unsignedBigInteger('id_tipo_residuo');
            $table->timestamp('fecha_hora')->useCurrent()->nullable();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            $table->foreign('id_tacho')->references('id_tacho')->on('tachos');
            $table->foreign('id_tipo_residuo')->references('id_tipo_residuo')->on('tipos_residuo');
        });

        Schema::create('transacciones_puntos', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_grupo')->nullable();
            $table->enum('tipo_movimiento', ['INGRESO', 'EGRESO', 'PENALIZACION']);
            $table->integer('monto');
            $table->string('motivo', 100);
            $table->integer('referencia_id')->nullable();
            $table->timestamp('fecha_movimiento')->useCurrent()->nullable();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos');
        });

        // --- Módulo 4: Gamificación y Recompensas ---
        Schema::create('premios_incentivos', function (Blueprint $table) {
            $table->id('id_premio');
            $table->string('nombre_premio', 150);
            $table->text('descripcion')->nullable();
            $table->integer('costo_puntos');
            $table->enum('tipo_premio', ['INDIVIDUAL', 'GRUPAL']);
            $table->integer('stock_disponible')->default(0)->nullable();
        });

        Schema::create('canjes', function (Blueprint $table) {
            $table->id('id_canje');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_premio');
            $table->enum('cuenta_origen', ['PERSONAL', 'GRUPAL']);
            $table->unsignedBigInteger('id_grupo_afectado')->nullable();
            $table->integer('puntos_gastados');
            $table->timestamp('fecha_canje')->useCurrent()->nullable();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            $table->foreign('id_premio')->references('id_premio')->on('premios_incentivos');
            $table->foreign('id_grupo_afectado')->references('id_grupo')->on('grupos');
        });

        // --- Módulo 5: Sistema, Alertas y Analítica ---
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id('id_notificacion');
            $table->unsignedBigInteger('id_usuario');
            $table->string('titulo', 100);
            $table->text('mensaje');
            $table->enum('tipo', ['RECORDATORIO', 'META_ALCANZADA', 'ALERTA_SISTEMA'])->default('RECORDATORIO')->nullable();
            $table->boolean('leida')->default(false)->nullable();
            $table->timestamp('fecha_creacion')->useCurrent()->nullable();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
        });

        Schema::create('metricas_dashboard', function (Blueprint $table) {
            $table->id('id_metrica');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_grupo')->nullable();
            $table->date('fecha_corte');
            $table->integer('racha_dias_activos')->default(0)->nullable();
            $table->integer('proyeccion_puntos')->default(0)->nullable();
            $table->enum('tendencia', ['ALTA', 'ESTABLE', 'BAJA'])->default('ESTABLE')->nullable();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos');
        });
    }

    public function down(): void
    {
        // El orden de borrado debe ser inverso para evitar errores de llaves foráneas
        Schema::dropIfExists('metricas_dashboard');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('canjes');
        Schema::dropIfExists('premios_incentivos');
        Schema::dropIfExists('transacciones_puntos');
        Schema::dropIfExists('historial_reciclaje');
        Schema::dropIfExists('grupo_tachos');
        Schema::dropIfExists('usuario_grupos');
        Schema::dropIfExists('grupos');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('eventos_mantenimiento');
        Schema::dropIfExists('niveles_tacho');
        Schema::dropIfExists('tipos_residuo');
        Schema::dropIfExists('tachos');
    }
};
