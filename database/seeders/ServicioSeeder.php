<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Servicio;
use Carbon\Carbon;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $servicios = [
            [
                'id' => 1,
                'nombre' => 'Precursor Regular',
                'descripcion' => 'Hermano dedicado que presta servicio de tiempo completo en la predicación',
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1,
                'creado_por_timestamp' => $now,
                'modificado_por_timestamp' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'nombre' => 'Precursor Auxiliar Indefinido',
                'descripcion' => 'Hermano que presta servicio de precursor auxiliar por tiempo indefinido',
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1,
                'creado_por_timestamp' => $now,
                'modificado_por_timestamp' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'nombre' => 'Precursor Auxiliar',
                'descripcion' => 'Hermano que presta servicio de precursor auxiliar por un período determinado',
                'estado' => 1,
                'creador_id' => 1,
                'modificador_id' => 1,
                'creado_por_timestamp' => $now,
                'modificado_por_timestamp' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($servicios as $servicio) {
            Servicio::create($servicio);
        }

        $this->command->info('Tabla servicios poblada con registros iniciales: 1=Precursor Regular, 2=Precursor Auxiliar Indefinido, 3=Precursor Auxiliar');
    }
}
