<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Deshabilitar restricciones de clave foránea temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Crear perfil administrador básico sin campos de auditoría inicialmente
        DB::table('perfiles')->insert([
            'id' => 1,
            'nombre' => 'Administrador',
            'descripcion' => 'Usuario administrador con acceso completo al sistema',
            'privilegio' => 'Anciano',
            'estado' => 1,
            'creador_id' => 1, // Temporal
            'modificador_id' => 1, // Temporal
            'creado_por_timestamp' => now(),
            'modificado_por_timestamp' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear usuario administrador básico
        $userId = DB::table('users')->insertGetId([
            'name' => 'Administrador',
            'email' => 'admin@sistema.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            // 'perfil' => 1, // Ya tiene default
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Actualizar los campos de auditoría del perfil con el ID real del usuario
        DB::table('perfiles')->where('id', 1)->update([
            'creador_id' => $userId,
            'modificador_id' => $userId,
        ]);

        // Crear los demás perfiles
        DB::table('perfiles')->insert([
            [
                'id' => 2,
                'nombre' => 'Supervisor',
                'descripcion' => 'Usuario supervisor con acceso de lectura',
                'privilegio' => 'Siervo Ministerial',
                'estado' => 1,
                'creador_id' => $userId,
                'modificador_id' => $userId,
                'creado_por_timestamp' => now(),
                'modificado_por_timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nombre' => 'Coordinador',
                'descripcion' => 'Usuario coordinador con acceso limitado',
                'privilegio' => 'Precursor Regular',
                'estado' => 1,
                'creador_id' => $userId,
                'modificador_id' => $userId,
                'creado_por_timestamp' => now(),
                'modificado_por_timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nombre' => 'Usuario',
                'descripcion' => 'Usuario básico con acceso limitado',
                'privilegio' => 'Publicador',
                'estado' => 1,
                'creador_id' => $userId,
                'modificador_id' => $userId,
                'creado_por_timestamp' => now(),
                'modificado_por_timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Volver a habilitar restricciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}