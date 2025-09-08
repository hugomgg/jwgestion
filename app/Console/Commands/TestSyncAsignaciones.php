<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Asignacion;

class TestSyncAsignaciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:sync-asignaciones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba la sincronización de asignaciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Prueba de Sincronización de Asignaciones ===');
        
        // Obtener primer usuario
        $user = User::first();
        if (!$user) {
            $this->error('No hay usuarios para probar');
            return;
        }
        
        $this->info("Usuario de prueba: {$user->name} ({$user->email})");
        
        // Mostrar asignaciones activas disponibles
        $asignacionesActivas = Asignacion::where('estado', 1)->get();
        $this->info("Asignaciones activas disponibles: {$asignacionesActivas->count()}");
        foreach ($asignacionesActivas as $asignacion) {
            $this->line("- ID: {$asignacion->id}, Nombre: {$asignacion->nombre}");
        }
        
        // Mostrar asignaciones actuales del usuario
        $this->newLine();
        $this->info('Asignaciones actuales del usuario:');
        $asignacionesActuales = $user->asignaciones;
        if ($asignacionesActuales->count() > 0) {
            foreach ($asignacionesActuales as $asignacion) {
                $this->line("- {$asignacion->nombre}");
            }
        } else {
            $this->line('Sin asignaciones');
        }
        
        // Simular sync - agregar asignaciones específicas
        $this->newLine();
        $this->info('Simulando sync con asignaciones [1, 3]...');
        $user->asignaciones()->sync([1, 3]);
        
        // Verificar resultado
        $user->refresh();
        $user->load('asignaciones');
        $this->info('Asignaciones después del sync:');
        foreach ($user->asignaciones as $asignacion) {
            $this->line("- {$asignacion->nombre}");
        }
        
        // Simular sync vacío
        $this->newLine();
        $this->info('Simulando sync vacío []...');
        $user->asignaciones()->sync([]);
        
        // Verificar resultado
        $user->refresh();
        $user->load('asignaciones');
        $this->info('Asignaciones después del sync vacío:');
        if ($user->asignaciones->count() > 0) {
            foreach ($user->asignaciones as $asignacion) {
                $this->line("- {$asignacion->nombre}");
            }
        } else {
            $this->line('Sin asignaciones (correcto)');
        }
        
        $this->newLine();
        $this->info('=== Prueba completada ===');
    }
}
