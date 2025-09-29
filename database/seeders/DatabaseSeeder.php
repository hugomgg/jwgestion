<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SexoSeeder::class,
            ServicioSeeder::class,
            NombramientoSeeder::class,
            EsperanzaSeeder::class,
            EstadoEspiritualSeeder::class,
            CongregacionesSeeder::class,
            GrupoSeeder::class,
            // PerfilesSeeder::class, // Ya creado en AdminUserSeeder
            AsignacionesSeeder::class,
            AsignacionesUsersSeeder::class,
            SalasSeeder::class,
        ]);
    }
}
