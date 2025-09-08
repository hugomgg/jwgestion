<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ParteSeccion;
use App\Models\SeccionReunion;
use App\Models\Asignacion;

class ParteSeccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener algunas secciones y asignaciones existentes
        $secciones = SeccionReunion::all();
        $asignaciones = Asignacion::all();

        if ($secciones->count() > 0 && $asignaciones->count() > 0) {
            // Datos de ejemplo para partes de sección
            $partesSeccion = [
                [
                    'nombre' => 'Canción de apertura',
                    'abreviacion' => 'CAPERT',
                    'orden' => 1,
                    'tiempo' => 3,
                    'seccion_id' => $secciones->first()->id,
                    'asignacion_id' => $asignaciones->first()->id,
                    'estado' => 1,
                ],
                [
                    'nombre' => 'Oración de apertura',
                    'abreviacion' => 'ORAPERT',
                    'orden' => 2,
                    'tiempo' => 2,
                    'seccion_id' => $secciones->first()->id,
                    'asignacion_id' => $asignaciones->first()->id,
                    'estado' => 1,
                ],
                [
                    'nombre' => 'Comentarios introductorios',
                    'abreviacion' => 'COMINT',
                    'orden' => 3,
                    'tiempo' => 3,
                    'seccion_id' => $secciones->first()->id,
                    'asignacion_id' => $asignaciones->first()->id,
                    'estado' => 1,
                ],
                [
                    'nombre' => 'Lectura de la Biblia',
                    'abreviacion' => 'LECBIB',
                    'orden' => 4,
                    'tiempo' => 4,
                    'seccion_id' => $secciones->first()->id,
                    'asignacion_id' => $asignaciones->skip(1)->first()->id ?? $asignaciones->first()->id,
                    'estado' => 1,
                ],
                [
                    'nombre' => 'Primera conversación',
                    'abreviacion' => 'CONV1',
                    'orden' => 5,
                    'tiempo' => 6,
                    'seccion_id' => $secciones->skip(1)->first()->id ?? $secciones->first()->id,
                    'asignacion_id' => $asignaciones->skip(2)->first()->id ?? $asignaciones->first()->id,
                    'estado' => 1,
                ],
            ];

            foreach ($partesSeccion as $parte) {
                ParteSeccion::create($parte);
            }
        }
    }
}
