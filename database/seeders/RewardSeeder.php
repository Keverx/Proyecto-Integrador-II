<?php
 
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use App\Models\Reward;
 
class RewardSeeder extends Seeder
{ 
    public function run()
    {
        Reward::firstOrCreate(
            ['nombre_premio' => 'Descuento en Cafetería'],
            [
                'descripcion' => 'Obtén un 20% de descuento en consumos mínimos de S/. 15.',
                'costo_puntos' => 50,
                'tipo_premio' => 'INDIVIDUAL',
                'stock_disponible' => 100,
                'categoria' => 'comida'
            ]
        );

        Reward::firstOrCreate(
            ['nombre_premio' => 'Cuaderno Ecológico'],
            [
                'descripcion' => 'Cuaderno de tapa dura hecho 100% de material reciclado.',
                'costo_puntos' => 120,
                'tipo_premio' => 'INDIVIDUAL',
                'stock_disponible' => 50,
                'categoria' => 'merchandising'
            ]
        );

        Reward::firstOrCreate(
            ['nombre_premio' => 'Cupón 10% de descuento en Cineplanet'],
            [
                'descripcion' => 'Válido para una entrada 2D de lunes a jueves.',
                'costo_puntos' => 500,
                'tipo_premio' => 'INDIVIDUAL',
                'stock_disponible' => 50,
                'categoria' => 'entretenimiento'
            ]
        );

        Reward::firstOrCreate(
            ['nombre_premio' => 'Café Americano Gratis'],
            [
                'descripcion' => 'Reclama un café americano tamaño regular en Starbucks.',
                'costo_puntos' => 800,
                'tipo_premio' => 'INDIVIDUAL',
                'stock_disponible' => 30,
                'categoria' => 'comida'
            ]
        );

        Reward::firstOrCreate(
            ['nombre_premio' => 'Medalla de Bronce Familiar'],
            [
                'descripcion' => 'Desbloquea el logro de Bronce para toda la familia al llegar a 5000 puntos históricos.',
                'costo_puntos' => 5000,
                'tipo_premio' => 'GRUPAL',
                'stock_disponible' => 9999,
                'categoria' => 'logros'
            ]
        );

        Reward::firstOrCreate(
            ['nombre_premio' => 'Vale de S/100 en Supermercados'],
            [
                'descripcion' => 'Vale de compras válido en Plaza Vea, Metro y Tottus. ¡Meta de 20,000 puntos grupales!',
                'costo_puntos' => 20000,
                'tipo_premio' => 'GRUPAL',
                'stock_disponible' => 5,
                'categoria' => 'compras'
            ]
        );
    }
}
