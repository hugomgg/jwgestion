<?php

namespace Database\Seeders;

use App\Models\Grupo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear grupo por defecto
        Grupo::updateOrCreate(
            ['id' => 1],
            [
                'nombre' => 'Grupo General',
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1
            ]
        );

        // Crear algunos grupos adicionales
        Grupo::updateOrCreate(
            ['nombre' => 'Ancianos'],
            [
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1
            ]
        );

        Grupo::updateOrCreate(
            ['nombre' => 'Siervos Ministeriales'],
            [
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1
            ]
        );

        Grupo::updateOrCreate(
            ['nombre' => 'Precursores'],
            [
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1
            ]
        );

        Grupo::updateOrCreate(
            ['nombre' => 'Publicadores'],
            [
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1
            ]
        );
    }
}
