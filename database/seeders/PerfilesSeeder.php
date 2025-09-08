<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Perfil;

class PerfilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Perfil::create([
            'id' => 1,
            'nombre' => 'Administrador',
            'descripcion' => 'Usuario administrador con acceso completo al sistema',
            'estado' => 1
        ]);

        Perfil::create([
            'id' => 2,
            'nombre' => 'Estudiante',
            'descripcion' => 'Usuario estudiante con acceso limitado',
            'estado' => 1
        ]);
    }
}
