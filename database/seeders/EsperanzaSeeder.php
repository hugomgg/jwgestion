<?php

namespace Database\Seeders;

use App\Models\Esperanza;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EsperanzaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear registros de esperanza con ID específicos
        $esperanzas = [
            [
                'id' => 1,
                'nombre' => 'Ungido',
                'descripcion' => 'Persona llamada por Jehová para reinar con Cristo en el cielo',
                'estado' => 1
            ],
            [
                'id' => 2,
                'nombre' => 'Otra Oveja',
                'descripcion' => 'Persona con la esperanza de vivir para siempre en la Tierra',
                'estado' => 1
            ]
        ];

        foreach ($esperanzas as $esperanzaData) {
            // Verificar si ya existe el registro
            if (!Esperanza::where('id', $esperanzaData['id'])->exists()) {
                Esperanza::create($esperanzaData);
            }
        }

        $this->command->info('Tabla esperanzas poblada con registros iniciales: 1=Ungido, 2=Otra Oveja');
    }
}
