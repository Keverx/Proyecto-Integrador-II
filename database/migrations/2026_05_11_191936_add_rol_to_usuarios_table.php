<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Crear tabla de roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre', 50)->unique();
        });

        // 2. Insertar roles básicos por defecto
        DB::table('roles')->insert([
            ['nombre' => 'USER'],
            ['nombre' => 'ADMIN']
        ]);

        // 3. Modificar tabla usuarios para agregar FK
        Schema::table('usuarios', function (Blueprint $table) {
            // Obtenemos el ID del rol 'USER' para ponerlo por defecto
            $defaultRoleId = DB::table('roles')->where('nombre', 'USER')->value('id_rol');

            $table->unsignedBigInteger('id_rol')->default($defaultRoleId)->after('estado_cuenta');
            $table->foreign('id_rol')->references('id_rol')->on('roles')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['id_rol']);
            $table->dropColumn('id_rol');
        });

        Schema::dropIfExists('roles');
    }
};
