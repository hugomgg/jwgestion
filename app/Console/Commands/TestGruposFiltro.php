<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Grupo;
use Illuminate\Support\Facades\DB;

class TestGruposFiltro extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:grupos-filtro {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el filtro de grupos para coordinadores';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $currentUser = User::find($userId);
        
        if (!$currentUser) {
            $this->error("Usuario con ID {$userId} no encontrado");
            return;
        }
        
        $this->info("=== PRUEBA DE FILTRO DE GRUPOS ===");
        $this->info("Usuario: {$currentUser->name}");
        $this->info("Perfil: {$currentUser->perfil} (" . ($currentUser->isCoordinator() ? 'Coordinador' : 'Otro') . ")");
        $this->info("Congregación: {$currentUser->congregacion}");
        $this->newLine();
        
        // Mostrar todos los grupos disponibles
        $todosLosGrupos = Grupo::where('estado', 1)->get();
        $this->info("=== TODOS LOS GRUPOS ACTIVOS ===");
        foreach ($todosLosGrupos as $grupo) {
            $this->line("ID: {$grupo->id} - {$grupo->nombre} (Congregación: {$grupo->congregacion_id})");
        }
        $this->newLine();
        
        // Mostrar usuarios de la congregación del coordinador y sus grupos
        if ($currentUser->isCoordinator()) {
            $usuariosCongregacion = DB::table('users')
                ->join('grupos', 'users.grupo', '=', 'grupos.id')
                ->where('users.congregacion', $currentUser->congregacion)
                ->select('users.name', 'users.grupo', 'grupos.nombre as grupo_nombre')
                ->get();
                
            $this->info("=== USUARIOS DE SU CONGREGACIÓN Y SUS GRUPOS ===");
            foreach ($usuariosCongregacion as $usuario) {
                $this->line("Usuario: {$usuario->name} - Grupo: {$usuario->grupo_nombre} (ID: {$usuario->grupo})");
            }
            $this->newLine();
        }
        
        // Aplicar la lógica del filtro
        if ($currentUser->isCoordinator()) {
            // Obtener solo los grupos que están asignados a usuarios de la congregación del coordinador
            $gruposParaFiltro = Grupo::whereIn('id', function($query) use ($currentUser) {
                $query->select('grupo')
                      ->from('users')
                      ->where('congregacion', $currentUser->congregacion)
                      ->distinct();
            })->where('estado', 1)->get();
        } else {
            $gruposParaFiltro = Grupo::where('estado', 1)->get();
        }
        
        $this->info("=== GRUPOS PARA FILTRO (RESULTADO ESPERADO) ===");
        if ($gruposParaFiltro->isEmpty()) {
            $this->warn("No hay grupos asignados a usuarios de la congregación del coordinador");
        } else {
            foreach ($gruposParaFiltro as $grupo) {
                $this->line("ID: {$grupo->id} - {$grupo->nombre}");
            }
        }
        
        $this->newLine();
        $this->info("Prueba completada exitosamente");
    }
}