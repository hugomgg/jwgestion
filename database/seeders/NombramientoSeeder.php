<?php

namespace Database\Seeders;

use App\Models\Nombramiento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NombramientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear registros de nombramiento con ID específicos
        $nombramientos = [
            [
                'id' => 1,
                'nombre' => 'Anciano',
                'descripcion' => 'Responsable del rebaño y las actividades congregacionales',
                'estado' => 1
            ],
            [
                'id' => 2,
                'nombre' => 'Siervo Ministerial',
                'descripcion' => 'Asiste a los ancianos en las responsabilidades congregacionales',
                'estado' => 1
            ],
            [
                'id' => 3,
                'nombre' => 'Publicador',
                'descripcion' => 'Miembro bautizado que participa en la obra de predicación',
                'estado' => 1
            ]
        ];

        foreach ($nombramientos as $nombramientoData) {
            // Verificar si ya existe el registro
            if (!Nombramiento::where('id', $nombramientoData['id'])->exists()) {
                Nombramiento::create($nombramientoData);
            }
        }

        $this->command->info('Tabla nombramiento poblada con registros iniciales: 1=Anciano, 2=Siervo Ministerial, 3=Publicador');
    }
}
