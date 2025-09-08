<?php

namespace Database\Seeders;

use App\Models\Congregacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CongregacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear registros de congregaciones con ID específicos
        $congregaciones = [
            [
                'id' => 1,
                'nombre' => 'Congregación Central',
                'direccion' => 'Av. Principal 123, Centro',
                'telefono' => '+56 9 1234 5678',
                'persona_contacto' => 'Juan Pérez',
                'estado' => 1
            ],
            [
                'id' => 2,
                'nombre' => 'Congregación Norte',
                'direccion' => 'Calle Norte 456, Sector Norte',
                'telefono' => '+56 9 8765 4321',
                'persona_contacto' => 'María González',
                'estado' => 1
            ],
            [
                'id' => 3,
                'nombre' => 'Congregación Sur',
                'direccion' => 'Av. Sur 789, Zona Sur',
                'telefono' => '+56 9 5555 1234',
                'persona_contacto' => 'Carlos Rodríguez',
                'estado' => 1
            ]
        ];

        foreach ($congregaciones as $congregacionData) {
            // Verificar si ya existe el registro
            if (!Congregacion::where('id', $congregacionData['id'])->exists()) {
                Congregacion::create($congregacionData);
            }
        }

        $this->command->info('Tabla congregaciones poblada con registros iniciales');
    }
}
