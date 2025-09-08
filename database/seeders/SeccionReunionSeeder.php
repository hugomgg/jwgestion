<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SeccionReunion;
use Carbon\Carbon;

class SeccionReunionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $secciones = [
            [
                'nombre' => 'Tesoros de la Biblia',
                'abreviacion' => 'TB',
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1,
                'creado_por_timestamp' => Carbon::now(),
                'modificado_por_timestamp' => Carbon::now(),
            ],
            [
                'nombre' => 'Seamos Mejores Maestros',
                'abreviacion' => 'SMM',
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1,
                'creado_por_timestamp' => Carbon::now(),
                'modificado_por_timestamp' => Carbon::now(),
            ],
            [
                'nombre' => 'Nuestra Vida Cristiana',
                'abreviacion' => 'NVC',
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1,
                'creado_por_timestamp' => Carbon::now(),
                'modificado_por_timestamp' => Carbon::now(),
            ],
            [
                'nombre' => 'Cánticos',
                'abreviacion' => 'CAN',
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1,
                'creado_por_timestamp' => Carbon::now(),
                'modificado_por_timestamp' => Carbon::now(),
            ],
            [
                'nombre' => 'Oración',
                'abreviacion' => 'ORA',
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1,
                'creado_por_timestamp' => Carbon::now(),
                'modificado_por_timestamp' => Carbon::now(),
            ],
            [
                'nombre' => 'Palabras de Introducción',
                'abreviacion' => 'PI',
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1,
                'creado_por_timestamp' => Carbon::now(),
                'modificado_por_timestamp' => Carbon::now(),
            ],
        ];

        foreach ($secciones as $seccion) {
            SeccionReunion::create($seccion);
        }
    }
}
