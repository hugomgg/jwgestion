<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Asignacion;
use Illuminate\Support\Facades\DB;

class AsignacionesUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan usuarios y asignaciones
        $users = User::all();
        $asignaciones = Asignacion::all();

        if ($users->count() == 0 || $asignaciones->count() == 0) {
            $this->command->info('No hay usuarios o asignaciones disponibles para crear relaciones.');
            return;
        }

        // Crear algunas relaciones de ejemplo
        $relaciones = [
            ['user_id' => 1, 'asignacion_id' => 1],
            ['user_id' => 1, 'asignacion_id' => 2],
            ['user_id' => 2, 'asignacion_id' => 1],
            ['user_id' => 2, 'asignacion_id' => 3],
        ];

        foreach ($relaciones as $relacion) {
            // Verificar que el usuario y la asignación existan
            $userExists = $users->contains('id', $relacion['user_id']);
            $asignacionExists = $asignaciones->contains('id', $relacion['asignacion_id']);

            if ($userExists && $asignacionExists) {
                // Verificar que la relación no exista ya
                $exists = DB::table('asignaciones_users')
                    ->where('user_id', $relacion['user_id'])
                    ->where('asignacion_id', $relacion['asignacion_id'])
                    ->exists();

                if (!$exists) {
                    DB::table('asignaciones_users')->insert([
                        'user_id' => $relacion['user_id'],
                        'asignacion_id' => $relacion['asignacion_id'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Relaciones usuario-asignación creadas exitosamente.');
    }
}
