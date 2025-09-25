<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sala;

class SalasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salas = [
            [
                'nombre' => Sala::PRINCIPAL,
                'abreviacion' => 'SP',
                'estado' => true,
                'creador_id' => 1,
                'modificador_id' => 1,
            ],
            [
                'nombre' => Sala::AUXILIAR_1,
                'abreviacion' => 'S1',
                'estado' => true,
                'creador_id' => 1,
                'modificador_id' => 1,
            ],
            [
                'nombre' => Sala::AUXILIAR_2,
                'abreviacion' => 'S2',
                'estado' => true,
                'creador_id' => 1,
                'modificador_id' => 1,
            ],
        ];

        foreach ($salas as $sala) {
            Sala::create($sala);
        }
    }
}
