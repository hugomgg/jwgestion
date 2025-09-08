<?php

namespace Database\Seeders;

use App\Models\Sexo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SexoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear registros de sexo con ID específicos
        $sexos = [
            [
                'id' => 1,
                'nombre' => 'Hombre',
                'descripcion' => 'Sexo masculino',
                'estado' => 1
            ],
            [
                'id' => 2,
                'nombre' => 'Mujer',
                'descripcion' => 'Sexo femenino',
                'estado' => 1
            ]
        ];

        foreach ($sexos as $sexoData) {
            // Verificar si ya existe el registro
            if (!Sexo::where('id', $sexoData['id'])->exists()) {
                Sexo::create($sexoData);
            }
        }

        $this->command->info('Tabla sexo poblada con registros iniciales: 1=Hombre, 2=Mujer');
    }
}
