<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Perfil;
use App\Models\Asignacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateAuditFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:update-existing-data {--dry-run : Solo mostrar qué se actualizaría sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza los campos de auditoría en registros existentes que no los tienen';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 Ejecutando en modo dry-run - No se realizarán cambios reales');
        }

        $this->info('🚀 Iniciando actualización de campos de auditoría...');

        // Actualizar tabla users
        $this->updateUsersTable($dryRun);
        
        // Actualizar tabla perfiles
        $this->updatePerfilesTable($dryRun);
        
        // Actualizar tabla asignaciones
        $this->updateAsignacionesTable($dryRun);

        $this->info('✅ Proceso completado exitosamente');
    }

    private function updateUsersTable($dryRun)
    {
        $this->info('📝 Procesando tabla users...');
        
        $usersToUpdate = DB::table('users')
            ->where('creado_por_timestamp', null)
            ->orWhere('modificado_por_timestamp', null)
            ->count();
            
        if ($usersToUpdate > 0) {
            $this->warn("   ⚠️  Encontrados {$usersToUpdate} usuarios sin campos de auditoría completos");
            
            if (!$dryRun) {
                DB::table('users')
                    ->where('creado_por_timestamp', null)
                    ->orWhere('modificado_por_timestamp', null)
                    ->update([
                        'creado_por_timestamp' => DB::raw('COALESCE(creado_por_timestamp, created_at)'),
                        'modificado_por_timestamp' => DB::raw('COALESCE(modificado_por_timestamp, updated_at)')
                    ]);
                    
                $this->info("   ✅ Actualizados {$usersToUpdate} usuarios");
            } else {
                $this->info("   🔍 Se actualizarían {$usersToUpdate} usuarios");
            }
        } else {
            $this->info('   ✅ Todos los usuarios ya tienen campos de auditoría completos');
        }
    }

    private function updatePerfilesTable($dryRun)
    {
        $this->info('📝 Procesando tabla perfiles...');
        
        $perfilesToUpdate = DB::table('perfiles')
            ->where('creado_por_timestamp', null)
            ->orWhere('modificado_por_timestamp', null)
            ->count();
            
        if ($perfilesToUpdate > 0) {
            $this->warn("   ⚠️  Encontrados {$perfilesToUpdate} perfiles sin campos de auditoría completos");
            
            if (!$dryRun) {
                DB::table('perfiles')
                    ->where('creado_por_timestamp', null)
                    ->orWhere('modificado_por_timestamp', null)
                    ->update([
                        'creado_por_timestamp' => DB::raw('COALESCE(creado_por_timestamp, created_at)'),
                        'modificado_por_timestamp' => DB::raw('COALESCE(modificado_por_timestamp, updated_at)')
                    ]);
                    
                $this->info("   ✅ Actualizados {$perfilesToUpdate} perfiles");
            } else {
                $this->info("   🔍 Se actualizarían {$perfilesToUpdate} perfiles");
            }
        } else {
            $this->info('   ✅ Todos los perfiles ya tienen campos de auditoría completos');
        }
    }

    private function updateAsignacionesTable($dryRun)
    {
        $this->info('📝 Procesando tabla asignaciones...');
        
        $asignacionesToUpdate = DB::table('asignaciones')
            ->where('creado_por_timestamp', null)
            ->orWhere('modificado_por_timestamp', null)
            ->count();
            
        if ($asignacionesToUpdate > 0) {
            $this->warn("   ⚠️  Encontradas {$asignacionesToUpdate} asignaciones sin campos de auditoría completos");
            
            if (!$dryRun) {
                DB::table('asignaciones')
                    ->where('creado_por_timestamp', null)
                    ->orWhere('modificado_por_timestamp', null)
                    ->update([
                        'creado_por_timestamp' => DB::raw('COALESCE(creado_por_timestamp, created_at)'),
                        'modificado_por_timestamp' => DB::raw('COALESCE(modificado_por_timestamp, updated_at)')
                    ]);
                    
                $this->info("   ✅ Actualizadas {$asignacionesToUpdate} asignaciones");
            } else {
                $this->info("   🔍 Se actualizarían {$asignacionesToUpdate} asignaciones");
            }
        } else {
            $this->info('   ✅ Todas las asignaciones ya tienen campos de auditoría completos');
        }
    }
}
