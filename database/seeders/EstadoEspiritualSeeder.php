<?php

namespace Database\Seeders;

use App\Models\EstadoEspiritual;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstadoEspiritualSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EstadoEspiritual::updateOrCreate(
            ['id' => 1],
            [
                'nombre' => 'Activo',
                'estado' => 1,
                'creador' => 1,
                'modificador' => 1
            ]
        );

        EstadoEspiritual::updateOrCreate(
            ['id' => 2],
            [
                'nombre' => 'Inactivo',
                'estado' => 1,
                'creador' => 1,
                'modificador' => 1
            ]
        );

        EstadoEspiritual::updateOrCreate(
            ['id' => 3],
            [
                'nombre' => 'Sacado',
                'estado' => 1,
                'creador' => 1,
                'modificador' => 1
            ]
        );

        EstadoEspiritual::updateOrCreate(
            ['id' => 4],
            [
                'nombre' => 'Deasociada',
                'estado' => 1,
                'creador' => 1,
                'modificador' => 1
            ]
        );
    }
}
