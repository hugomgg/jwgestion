<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Perfil;
use App\Models\Asignacion;
use App\Models\Sexo;
use App\Models\Nombramiento;
use App\Models\Esperanza;
use App\Models\Servicio;
use App\Models\Congregacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestAuditFunctionality extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba la funcionalidad de auditoría creando y actualizando registros';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando pruebas de funcionalidad de auditoría...');

        // Simular autenticación con el usuario administrador
        $admin = User::where('email', 'admin@sistema.com')->first();
        if (!$admin) {
            $this->error('❌ No se encontró el usuario administrador. Ejecute los seeders primero.');
            return;
        }

        Auth::login($admin);
        $this->info("✅ Autenticado como: {$admin->name} (ID: {$admin->id})");

        // Probar creación de perfil
        $this->testPerfilCreation();

        // Probar actualización de perfil
        $this->testPerfilUpdate();

        // Probar creación de asignación
        $this->testAsignacionCreation();

        // Probar actualización de asignación
        $this->testAsignacionUpdate();

        // Probar creación de sexo
        $this->testSexoCreation();

        // Probar actualización de sexo
        $this->testSexoUpdate();

        // Probar creación de nombramiento
        $this->testNombramientoCreation();

        // Probar actualización de nombramiento
        $this->testNombramientoUpdate();

        // Probar creación de esperanza
        $this->testEsperanzaCreation();

        // Probar actualización de esperanza
        $this->testEsperanzaUpdate();

        // Probar creación de servicio
        $this->testServicioCreation();

        // Probar actualización de servicio
        $this->testServicioUpdate();

        // Probar creación de congregación
        $this->testCongregacionCreation();

        // Probar actualización de congregación
        $this->testCongregacionUpdate();

        // Mostrar información de auditoría
        $this->showAuditInfo();

        $this->info('✅ Todas las pruebas completadas exitosamente');
    }

    private function testPerfilCreation()
    {
        $this->info('📝 Probando creación de perfil...');
        
        // Eliminar perfil de prueba si existe
        Perfil::where('nombre', 'Perfil de Prueba Auditoría')->delete();
        
        $perfil = Perfil::create([
            'nombre' => 'Perfil de Prueba Auditoría',
            'privilegio' => 'Prueba',
            'descripcion' => 'Este es un perfil creado para probar la auditoría',
            'estado' => 1
        ]);

        $this->line("   Perfil creado con ID: {$perfil->id}");
        $this->line("   Privilegio: {$perfil->privilegio}");
        $this->line("   Creado por: {$perfil->creador_id} - {$perfil->creador?->name}");
        $this->line("   Fecha de creación: {$perfil->creado_por_timestamp}");
    }

    private function testPerfilUpdate()
    {
        $this->info('📝 Probando actualización de perfil...');
        
        $perfil = Perfil::where('nombre', 'Perfil de Prueba Auditoría')->first();
        if ($perfil) {
            $perfil->update([
                'descripcion' => 'Descripción actualizada para probar auditoría'
            ]);

            $this->line("   Perfil actualizado con ID: {$perfil->id}");
            $this->line("   Modificado por: {$perfil->modificador_id} - {$perfil->modificador?->name}");
            $this->line("   Fecha de modificación: {$perfil->modificado_por_timestamp}");
        }
    }

    private function testAsignacionCreation()
    {
        $this->info('📝 Probando creación de asignación...');
        
        // Eliminar asignación de prueba si existe
        Asignacion::where('nombre', 'Asignación de Prueba Auditoría')->delete();
        
        $asignacion = Asignacion::create([
            'nombre' => 'Asignación de Prueba Auditoría',
            'descripcion' => 'Esta es una asignación creada para probar la auditoría',
            'estado' => 1
        ]);

        $this->line("   Asignación creada con ID: {$asignacion->id}");
        $this->line("   Creado por: {$asignacion->creador_id} - {$asignacion->creador?->name}");
        $this->line("   Fecha de creación: {$asignacion->creado_por_timestamp}");
    }

    private function testAsignacionUpdate()
    {
        $this->info('📝 Probando actualización de asignación...');
        
        $asignacion = Asignacion::where('nombre', 'Asignación de Prueba Auditoría')->first();
        if ($asignacion) {
            $asignacion->update([
                'descripcion' => 'Descripción actualizada para probar auditoría'
            ]);

            $this->line("   Asignación actualizada con ID: {$asignacion->id}");
            $this->line("   Modificado por: {$asignacion->modificador_id} - {$asignacion->modificador?->name}");
            $this->line("   Fecha de modificación: {$asignacion->modificado_por_timestamp}");
        }
    }

    private function testSexoCreation()
    {
        $this->info('📝 Probando creación de registro de sexo...');
        
        // Eliminar registro de prueba si existe
        Sexo::where('nombre', 'Prueba Sexo Auditoría')->delete();
        
        $sexo = Sexo::create([
            'nombre' => 'Prueba Sexo Auditoría',
            'descripcion' => 'Este es un registro creado para probar la auditoría',
            'estado' => 1
        ]);

        $this->line("   Sexo creado con ID: {$sexo->id}");
        $this->line("   Creado por: {$sexo->creador_id} - {$sexo->creador?->name}");
        $this->line("   Fecha de creación: {$sexo->creado_por_timestamp}");
    }

    private function testSexoUpdate()
    {
        $this->info('📝 Probando actualización de registro de sexo...');
        
        $sexo = Sexo::where('nombre', 'Prueba Sexo Auditoría')->first();
        if ($sexo) {
            $sexo->update([
                'descripcion' => 'Descripción actualizada para probar auditoría'
            ]);

            $this->line("   Sexo actualizado con ID: {$sexo->id}");
            $this->line("   Modificado por: {$sexo->modificador_id} - {$sexo->modificador?->name}");
            $this->line("   Fecha de modificación: {$sexo->modificado_por_timestamp}");
        }
    }

    private function testNombramientoCreation()
    {
        $this->info('📝 Probando creación de nombramiento...');
        
        // Eliminar nombramiento de prueba si existe
        Nombramiento::where('nombre', 'Nombramiento de Prueba Auditoría')->delete();
        
        $nombramiento = Nombramiento::create([
            'nombre' => 'Nombramiento de Prueba Auditoría',
            'descripcion' => 'Este es un nombramiento creado para probar la auditoría',
            'estado' => 1
        ]);

        $this->line("   Nombramiento creado con ID: {$nombramiento->id}");
        $this->line("   Creado por: {$nombramiento->creador_id} - {$nombramiento->creador?->name}");
        $this->line("   Fecha de creación: {$nombramiento->creado_por_timestamp}");
    }

    private function testNombramientoUpdate()
    {
        $this->info('📝 Probando actualización de nombramiento...');
        
        $nombramiento = Nombramiento::where('nombre', 'Nombramiento de Prueba Auditoría')->first();
        if ($nombramiento) {
            $nombramiento->update([
                'descripcion' => 'Descripción actualizada para probar auditoría'
            ]);

            $this->line("   Nombramiento actualizado con ID: {$nombramiento->id}");
            $this->line("   Modificado por: {$nombramiento->modificador_id} - {$nombramiento->modificador?->name}");
            $this->line("   Fecha de modificación: {$nombramiento->modificado_por_timestamp}");
        }
    }

    private function testEsperanzaCreation()
    {
        $this->info('📝 Probando creación de esperanza...');
        
        // Eliminar esperanza de prueba si existe
        Esperanza::where('nombre', 'Esperanza de Prueba Auditoría')->delete();
        
        $esperanza = Esperanza::create([
            'nombre' => 'Esperanza de Prueba Auditoría',
            'descripcion' => 'Esta es una esperanza creada para probar la auditoría',
            'estado' => 1
        ]);

        $this->line("   Esperanza creada con ID: {$esperanza->id}");
        $this->line("   Creado por: {$esperanza->creador_id} - {$esperanza->creador?->name}");
        $this->line("   Fecha de creación: {$esperanza->creado_por_timestamp}");
    }

    private function testEsperanzaUpdate()
    {
        $this->info('📝 Probando actualización de esperanza...');
        
        $esperanza = Esperanza::where('nombre', 'Esperanza de Prueba Auditoría')->first();
        if ($esperanza) {
            $esperanza->update([
                'descripcion' => 'Descripción actualizada para probar auditoría'
            ]);

            $this->line("   Esperanza actualizada con ID: {$esperanza->id}");
            $this->line("   Modificado por: {$esperanza->modificador_id} - {$esperanza->modificador?->name}");
            $this->line("   Fecha de modificación: {$esperanza->modificado_por_timestamp}");
        }
    }

    private function showAuditInfo()
    {
        $this->info('📊 Resumen de información de auditoría:');
        
        // Mostrar estadísticas de perfil
        $perfil = Perfil::where('nombre', 'Perfil de Prueba Auditoría')->first();
        if ($perfil) {
            $auditInfo = $perfil->getAuditInfo();
            $this->line("   📋 Perfil '{$perfil->nombre}':");
            $this->line("      Creado por: {$auditInfo['creado_por']['usuario_nombre']} el {$auditInfo['creado_por']['fecha']}");
            $this->line("      Modificado por: {$auditInfo['modificado_por']['usuario_nombre']} el {$auditInfo['modificado_por']['fecha']}");
        }

        // Mostrar estadísticas de asignación
        $asignacion = Asignacion::where('nombre', 'Asignación de Prueba Auditoría')->first();
        if ($asignacion) {
            $auditInfo = $asignacion->getAuditInfo();
            $this->line("   📋 Asignación '{$asignacion->nombre}':");
            $this->line("      Creado por: {$auditInfo['creado_por']['usuario_nombre']} el {$auditInfo['creado_por']['fecha']}");
            $this->line("      Modificado por: {$auditInfo['modificado_por']['usuario_nombre']} el {$auditInfo['modificado_por']['fecha']}");
        }

        // Mostrar estadísticas de sexo
        $sexo = Sexo::where('nombre', 'Prueba Sexo Auditoría')->first();
        if ($sexo) {
            $auditInfo = $sexo->getAuditInfo();
            $this->line("   📋 Sexo '{$sexo->nombre}':");
            $this->line("      Creado por: {$auditInfo['creado_por']['usuario_nombre']} el {$auditInfo['creado_por']['fecha']}");
            $this->line("      Modificado por: {$auditInfo['modificado_por']['usuario_nombre']} el {$auditInfo['modificado_por']['fecha']}");
        }

        // Mostrar estadísticas de nombramiento
        $nombramiento = Nombramiento::where('nombre', 'Nombramiento de Prueba Auditoría')->first();
        if ($nombramiento) {
            $auditInfo = $nombramiento->getAuditInfo();
            $this->line("   📋 Nombramiento '{$nombramiento->nombre}':");
            $this->line("      Creado por: {$auditInfo['creado_por']['usuario_nombre']} el {$auditInfo['creado_por']['fecha']}");
            $this->line("      Modificado por: {$auditInfo['modificado_por']['usuario_nombre']} el {$auditInfo['modificado_por']['fecha']}");
        }

        // Mostrar estadísticas de esperanza
        $esperanza = Esperanza::where('nombre', 'Esperanza de Prueba Auditoría')->first();
        if ($esperanza) {
            $auditInfo = $esperanza->getAuditInfo();
            $this->line("   📋 Esperanza '{$esperanza->nombre}':");
            $this->line("      Creado por: {$auditInfo['creado_por']['usuario_nombre']} el {$auditInfo['creado_por']['fecha']}");
            $this->line("      Modificado por: {$auditInfo['modificado_por']['usuario_nombre']} el {$auditInfo['modificado_por']['fecha']}");
        }

        // Mostrar estadísticas de servicio
        $servicio = Servicio::where('nombre', 'Servicio de Prueba Auditoría')->first();
        if ($servicio) {
            $auditInfo = $servicio->getAuditInfo();
            $this->line("   📋 Servicio '{$servicio->nombre}':");
            $this->line("      Creado por: {$auditInfo['creado_por']['usuario_nombre']} el {$auditInfo['creado_por']['fecha']}");
            $this->line("      Modificado por: {$auditInfo['modificado_por']['usuario_nombre']} el {$auditInfo['modificado_por']['fecha']}");
        }

        // Mostrar estadísticas de congregación
        $congregacion = Congregacion::where('nombre', 'Congregación de Prueba Auditoría')->first();
        if ($congregacion) {
            $auditInfo = $congregacion->getAuditInfo();
            $this->line("   📋 Congregación '{$congregacion->nombre}':");
            $this->line("      Creado por: {$auditInfo['creado_por']['usuario_nombre']} el {$auditInfo['creado_por']['fecha']}");
            $this->line("      Modificado por: {$auditInfo['modificado_por']['usuario_nombre']} el {$auditInfo['modificado_por']['fecha']}");
        }
    }

    private function testServicioCreation()
    {
        $this->info('📝 Probando creación de servicio...');
        
        // Eliminar servicio de prueba si existe
        Servicio::where('nombre', 'Servicio de Prueba Auditoría')->delete();
        
        $servicio = Servicio::create([
            'nombre' => 'Servicio de Prueba Auditoría',
            'descripcion' => 'Servicio creado para probar funcionalidad de auditoría',
            'estado' => 1,
        ]);

        $this->line("   Servicio creado con ID: {$servicio->id}");
        $this->line("   Nombre: {$servicio->nombre}");
        $this->line("   Creado por: {$servicio->creador_id} - {$servicio->creador->name}");
        $this->line("   Fecha de creación: {$servicio->creado_por_timestamp}");
    }

    private function testServicioUpdate()
    {
        $this->info('📝 Probando actualización de servicio...');
        
        $servicio = Servicio::where('nombre', 'Servicio de Prueba Auditoría')->first();
        
        if ($servicio) {
            $servicio->update([
                'descripcion' => 'Servicio actualizado para probar funcionalidad de auditoría',
                'estado' => 1,
            ]);

            $this->line("   Servicio actualizado con ID: {$servicio->id}");
            $this->line("   Modificado por: {$servicio->modificador_id} - {$servicio->modificador->name}");
            $this->line("   Fecha de modificación: {$servicio->modificado_por_timestamp}");
        }
    }

    private function testCongregacionCreation()
    {
        $this->info('📝 Probando creación de congregación...');
        
        // Eliminar congregación de prueba si existe
        Congregacion::where('nombre', 'Congregación de Prueba Auditoría')->delete();
        
        $congregacion = Congregacion::create([
            'nombre' => 'Congregación de Prueba Auditoría',
            'direccion' => 'Calle de Prueba 123, Ciudad de Prueba',
            'telefono' => '+56912345678',
            'persona_contacto' => 'Juan Pérez - Coordinador',
            'estado' => 1,
        ]);

        $this->line("   Congregación creada con ID: {$congregacion->id}");
        $this->line("   Nombre: {$congregacion->nombre}");
        $this->line("   Creado por: {$congregacion->creador_id} - {$congregacion->creador->name}");
        $this->line("   Fecha de creación: {$congregacion->creado_por_timestamp}");
    }

    private function testCongregacionUpdate()
    {
        $this->info('📝 Probando actualización de congregación...');
        
        $congregacion = Congregacion::where('nombre', 'Congregación de Prueba Auditoría')->first();
        
        if ($congregacion) {
            $congregacion->update([
                'direccion' => 'Avenida Actualizada 456, Nueva Ciudad',
                'telefono' => '+56987654321',
                'persona_contacto' => 'María González - Coordinadora Actualizada',
                'estado' => 1,
            ]);

            $this->line("   Congregación actualizada con ID: {$congregacion->id}");
            $this->line("   Modificado por: {$congregacion->modificador_id} - {$congregacion->modificador->name}");
            $this->line("   Fecha de modificación: {$congregacion->modificado_por_timestamp}");
        }
    }
}