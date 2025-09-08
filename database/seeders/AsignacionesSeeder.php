<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Asignacion;

class AsignacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Asignacion::create([
            'nombre' => 'Desarrollo de Sistema Web',
            'descripcion' => 'Proyecto de desarrollo completo de una aplicación web moderna con Laravel y Vue.js',
            'estado' => 1
        ]);

        Asignacion::create([
            'nombre' => 'Base de Datos MySQL',
            'descripcion' => 'Diseño e implementación de base de datos relacional para sistema empresarial',
            'estado' => 1
        ]);

        Asignacion::create([
            'nombre' => 'Análisis de Sistemas',
            'descripcion' => 'Análisis y documentación de requerimientos para nuevo software corporativo',
            'estado' => 0
        ]);

        Asignacion::create([
            'nombre' => 'Proyecto Final',
            'descripcion' => 'Desarrollo de aplicación móvil con React Native y backend en Node.js',
            'estado' => 1
        ]);

        Asignacion::create([
            'nombre' => 'Investigación IA',
            'descripcion' => 'Proyecto de investigación sobre implementación de inteligencia artificial en procesos empresariales',
            'estado' => 0
        ]);
    }
}
