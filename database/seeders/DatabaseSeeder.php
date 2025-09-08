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
            PerfilesSeeder::class,
            CongregacionesSeeder::class,
            SexoSeeder::class,
            ServicioSeeder::class,
            NombramientoSeeder::class,
            EsperanzaSeeder::class,
            GrupoSeeder::class,
            UsersSeeder::class,
            AsignacionesSeeder::class,
            AsignacionesUsersSeeder::class,
            SalasSeeder::class,
        ]);
    }
}
