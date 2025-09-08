<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Asignacion;

class TestAsignacionesRelationship extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:asignaciones-relationship';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba las relaciones entre usuarios y asignaciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Probando relaciones Usuario-Asignación ===');
        
        // Obtener un usuario con sus asignaciones
        $user = User::with('asignaciones')->first();
        
        if ($user) {
            $this->info("Usuario: {$user->name} ({$user->email})");
            $this->info("Asignaciones del usuario:");
            
            foreach ($user->asignaciones as $asignacion) {
                $this->line("- {$asignacion->nombre}: {$asignacion->descripcion}");
            }
        } else {
            $this->error('No se encontraron usuarios');
        }
        
        $this->newLine();
        
        // Obtener una asignación con sus usuarios
        $asignacion = Asignacion::with('users')->first();
        
        if ($asignacion) {
            $this->info("Asignación: {$asignacion->nombre}");
            $this->info("Usuarios asignados:");
            
            foreach ($asignacion->users as $user) {
                $this->line("- {$user->name} ({$user->email})");
            }
        } else {
            $this->error('No se encontraron asignaciones');
        }
        
        $this->newLine();
        
        // Ejemplo de cómo agregar una nueva relación
        $this->info('=== Ejemplo de uso programático ===');
        
        $user = User::first();
        $asignacion = Asignacion::first();
        
        if ($user && $asignacion) {
            // Verificar si ya existe la relación
            if (!$user->asignaciones->contains($asignacion->id)) {
                // Agregar asignación al usuario
                $user->asignaciones()->attach($asignacion->id);
                $this->info("✓ Asignación '{$asignacion->nombre}' agregada al usuario '{$user->name}'");
            } else {
                $this->info("⚠ La relación ya existe entre '{$user->name}' y '{$asignacion->nombre}'");
            }
            
            // Ejemplo de cómo remover una relación
            // $user->asignaciones()->detach($asignacion->id);
            // $this->info("✓ Asignación removida del usuario");
            
            // Ejemplo de cómo sincronizar asignaciones (reemplaza todas)
            // $user->asignaciones()->sync([1, 2, 3]);
            // $this->info("✓ Asignaciones sincronizadas");
        }
        
        $this->info('=== Prueba completada ===');
    }
}
