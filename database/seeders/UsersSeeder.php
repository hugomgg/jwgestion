<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@sistema.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'perfil' => 1, // Administrador
        ]);

        // Crear usuarios de prueba
        User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'perfil' => 2, // Estudiante
        ]);

        User::create([
            'name' => 'María García',
            'email' => 'maria@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => null,
            'perfil' => 2, // Estudiante
        ]);

        User::create([
            'name' => 'Carlos Rodríguez',
            'email' => 'carlos@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'perfil' => 1, // Administrador
        ]);

        User::create([
            'name' => 'Ana López',
            'email' => 'ana@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => null,
            'perfil' => 2, // Estudiante
        ]);

        // Crear usuarios adicionales usando factory
        User::factory(10)->create([
            'perfil' => 2, // Todos los usuarios del factory serán estudiantes por defecto
        ]);
    }
}
