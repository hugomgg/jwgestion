<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Agregar nuevos campos después del campo 'modificado_por_timestamp'
            $table->unsignedBigInteger('congregacion')->default(1)->after('modificado_por_timestamp');
            $table->date('fecha_nacimiento')->nullable()->after('congregacion');
            $table->date('fecha_bautismo')->nullable()->after('fecha_nacimiento');
            $table->string('telefono', 20)->nullable()->after('fecha_bautismo');
            $table->string('persona_contacto')->nullable()->after('telefono');
            $table->string('telefono_contacto', 20)->nullable()->after('persona_contacto');
            $table->unsignedBigInteger('sexo')->default(1)->after('telefono_contacto');
            $table->unsignedBigInteger('servicio')->nullable()->after('sexo');
            $table->unsignedBigInteger('nombramiento')->default(3)->after('servicio');
            $table->unsignedBigInteger('esperanza')->default(2)->after('nombramiento');
            $table->text('observacion')->nullable()->after('esperanza');
            
            // Crear índices para optimizar consultas
            $table->index('congregacion', 'idx_users_congregacion');
            $table->index('fecha_nacimiento', 'idx_users_fecha_nacimiento');
            $table->index('fecha_bautismo', 'idx_users_fecha_bautismo');
            $table->index('sexo', 'idx_users_sexo');
            $table->index('servicio', 'idx_users_servicio');
            $table->index('nombramiento', 'idx_users_nombramiento');
            $table->index('esperanza', 'idx_users_esperanza');
            
            // Crear claves foráneas para integridad referencial
            $table->foreign('congregacion', 'fk_users_congregacion')->references('id')->on('congregaciones')->onDelete('restrict');
            $table->foreign('sexo', 'fk_users_sexo')->references('id')->on('sexo')->onDelete('restrict');
            $table->foreign('servicio', 'fk_users_servicio')->references('id')->on('servicios')->onDelete('restrict');
            $table->foreign('nombramiento', 'fk_users_nombramiento')->references('id')->on('nombramiento')->onDelete('restrict');
            $table->foreign('esperanza', 'fk_users_esperanza')->references('id')->on('esperanzas')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar claves foráneas
            $table->dropForeign('fk_users_congregacion');
            $table->dropForeign('fk_users_sexo');
            $table->dropForeign('fk_users_servicio');
            $table->dropForeign('fk_users_nombramiento');
            $table->dropForeign('fk_users_esperanza');
            
            // Eliminar índices
            $table->dropIndex('idx_users_congregacion');
            $table->dropIndex('idx_users_fecha_nacimiento');
            $table->dropIndex('idx_users_fecha_bautismo');
            $table->dropIndex('idx_users_sexo');
            $table->dropIndex('idx_users_servicio');
            $table->dropIndex('idx_users_nombramiento');
            $table->dropIndex('idx_users_esperanza');
            
            // Eliminar columnas
            $table->dropColumn([
                'congregacion',
                'fecha_nacimiento',
                'fecha_bautismo',
                'telefono',
                'persona_contacto',
                'telefono_contacto',
                'sexo',
                'servicio',
                'nombramiento',
                'esperanza',
                'observacion'
            ]);
        });
    }
};
