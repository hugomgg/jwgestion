<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asignacion;
use App\Models\Perfil;
use App\Models\Congregacion;
use App\Models\Sexo;
use App\Models\Servicio;
use App\Models\Nombramiento;
use App\Models\Esperanza;

class DebugUsersIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:users-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug de datos que se pasan a la vista users.index';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Debug UserController@index ===');
        
        // Simular lo que hace el controlador
        $perfiles = Perfil::all();
        $congregaciones = Congregacion::where('estado', 1)->get();
        $sexos = Sexo::where('estado', 1)->get();
        $servicios = Servicio::where('estado', 1)->get();
        $nombramientos = Nombramiento::where('estado', 1)->get();
        $esperanzas = Esperanza::where('estado', 1)->get();
        $asignaciones = Asignacion::where('estado', 1)->get();
        
        $this->info('Datos que se pasan a la vista:');
        $this->line("- Perfiles: {$perfiles->count()}");
        $this->line("- Congregaciones activas: {$congregaciones->count()}");
        $this->line("- Sexos activos: {$sexos->count()}");
        $this->line("- Servicios activos: {$servicios->count()}");
        $this->line("- Nombramientos activos: {$nombramientos->count()}");
        $this->line("- Esperanzas activas: {$esperanzas->count()}");
        $this->line("- Asignaciones activas: {$asignaciones->count()}");
        
        if ($asignaciones->count() > 0) {
            $this->newLine();
            $this->info('Detalle de asignaciones activas:');
            foreach ($asignaciones as $asignacion) {
                $this->line("- ID: {$asignacion->id}, Nombre: {$asignacion->nombre}, Estado: {$asignacion->estado}");
            }
        } else {
            $this->error('¡No hay asignaciones activas!');
        }
        
        $this->newLine();
        $this->info('=== Debug completado ===');
    }
}
