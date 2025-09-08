<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CancionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('canciones')->insert([
            [
                'id' => 1,
                'nombre' => 'Canción 1',
                'descripcion' => 'Primera canción del catálogo',
                'estado' => true,
                'creador' => 1,
                'modificador' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'Canción 2',
                'descripcion' => 'Segunda canción del catálogo',
                'estado' => true,
                'creador' => 1,
                'modificador' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
