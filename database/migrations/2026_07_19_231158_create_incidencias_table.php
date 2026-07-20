<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id('id_incidencia');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_tacho')->nullable();
            $table->string('tipo_problema', 100);
            $table->text('descripcion');
            $table->enum('estado', ['PENDIENTE', 'EN_PROCESO', 'RESUELTO'])->default('PENDIENTE');
            $table->text('respuesta_admin')->nullable();
            $table->timestamps();

            // Claves foráneas (ajustadas al nombre de tus tablas actuales)
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('cascade');
            $table->foreign('id_tacho')->references('id_tacho')->on('tachos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
