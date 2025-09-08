<?php

namespace Database\Seeders;

use App\Models\Perfil;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdatePerfilesPrivilegioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Actualizar perfiles existentes con privilegios apropiados
        $perfilesUpdates = [
            'Administrador' => 'Anciano',
            'Estudiante' => 'Publicador',
            'Supervisor' => 'Siervo Ministerial'
        ];

        foreach ($perfilesUpdates as $nombre => $privilegio) {
            DB::table('perfiles')
                ->where('nombre', $nombre)
                ->update(['privilegio' => $privilegio]);
        }

        // Si no existe un perfil de Administrador, crearlo
        if (!Perfil::where('nombre', 'Administrador')->exists()) {
            Perfil::create([
                'nombre' => 'Administrador',
                'privilegio' => 'Anciano',
                'descripcion' => 'Perfil con acceso completo al sistema',
                'estado' => 1
            ]);
        }

        // Si no existe un perfil de Estudiante, crearlo
        if (!Perfil::where('nombre', 'Estudiante')->exists()) {
            Perfil::create([
                'nombre' => 'Estudiante',
                'privilegio' => 'Publicador',
                'descripcion' => 'Perfil con acceso de solo lectura',
                'estado' => 1
            ]);
        }

        // Crear perfiles adicionales si no existen
        $perfilesAdicionales = [
            [
                'nombre' => 'Siervo Ministerial',
                'privilegio' => 'Siervo Ministerial',
                'descripcion' => 'Perfil para siervos ministeriales',
                'estado' => 1
            ],
            [
                'nombre' => 'Precursor',
                'privilegio' => 'Precursor Regular',
                'descripcion' => 'Perfil para precursores regulares',
                'estado' => 1
            ],
            [
                'nombre' => 'Betelita',
                'privilegio' => 'Miembro de Betel',
                'descripcion' => 'Perfil para miembros de Betel',
                'estado' => 1
            ]
        ];

        foreach ($perfilesAdicionales as $perfilData) {
            if (!Perfil::where('nombre', $perfilData['nombre'])->exists()) {
                Perfil::create($perfilData);
            }
        }

        $this->command->info('Perfiles actualizados con privilegios correctos.');
    }
}
