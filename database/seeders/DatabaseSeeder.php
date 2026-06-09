<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {

        User::factory()->create([
            'nombre' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // colocamos los Tipos de Residuos
        \App\Models\WasteType::firstOrCreate(
            ['nombre_residuo' => 'plastico'],
            ['puntos_otorgados' => 10, 'descripcion' => 'Botellas, envases y tapitas de plástico']
        );
        \App\Models\WasteType::firstOrCreate(
            ['nombre_residuo' => 'papel'],
            ['puntos_otorgados' => 5, 'descripcion' => 'Hojas, cuadernos, periódicos y cartón']
        );
        \App\Models\WasteType::firstOrCreate(
            ['nombre_residuo' => 'vidrio'],
            ['puntos_otorgados' => 15, 'descripcion' => 'Botellas, frascos y recipientes de vidrio']
        );

        // añadimos un Tacho de prueba para escaneos e IoT
        \App\Models\Bin::firstOrCreate(
            ['id_tacho' => 1],
            [
                'codigo_qr' => 'TACHO-TEST-01',
                'pin_actual' => '1234',
                'estado_operativo' => 'ACTIVO'
            ]
        );
        // llamamos al seeder de premios para la tienda/catálogo
        $this->call(RewardSeeder::class);
    }
}
