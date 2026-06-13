<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->insert([
            ['nombre' => 'SOPORTE'],
            ['nombre' => 'ADMIN_GENERAL']
        ]);
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('nombre', ['SOPORTE', 'ADMIN_GENERAL'])->delete();
    }
};
