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
        Grupo::create([
            'id' => 1,
            'nombre' => 'Grupo General',
            'estado' => 1,
            'creador' => 1,
            'modificador' => 1
        ]);

        // Crear algunos grupos adicionales
        Grupo::create([
            'nombre' => 'Ancianos',
            'estado' => 1,
            'creador' => 1,
            'modificador' => 1
        ]);

        Grupo::create([
            'nombre' => 'Siervos Ministeriales',
            'estado' => 1,
            'creador' => 1,
            'modificador' => 1
        ]);

        Grupo::create([
            'nombre' => 'Precursores',
            'estado' => 1,
            'creador' => 1,
            'modificador' => 1
        ]);

        Grupo::create([
            'nombre' => 'Publicadores',
            'estado' => 1,
            'creador' => 1,
            'modificador' => 1
        ]);
    }
}
