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

        // Sembrar Tipos de Residuos (requeridos para el dashboard y el reciclaje)
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

        // Sembrar un Tacho (Bin) de prueba para escaneos e IoT
        \App\Models\Bin::firstOrCreate(
            ['id_tacho' => 1],
            [
                'codigo_qr' => 'TACHO-TEST-01',
                'pin_actual' => '1234',
                'estado_operativo' => 'ACTIVO'
            ]
        );

        // Sembrar algunos premios de prueba para la tienda/catálogo
        \App\Models\Reward::firstOrCreate(
            ['nombre_premio' => 'Descuento en Cafetería'],
            [
                'descripcion' => 'Obtén un 20% de descuento en consumos mínimos de S/. 15.',
                'costo_puntos' => 50,
                'tipo_premio' => 'INDIVIDUAL',
                'stock_disponible' => 100
            ]
        );
        \App\Models\Reward::firstOrCreate(
            ['nombre_premio' => 'Cuaderno Ecológico'],
            [
                'descripcion' => 'Cuaderno de tapa dura hecho 100% de material reciclado.',
                'costo_puntos' => 120,
                'tipo_premio' => 'INDIVIDUAL',
                'stock_disponible' => 50
            ]
        );
    }
}
