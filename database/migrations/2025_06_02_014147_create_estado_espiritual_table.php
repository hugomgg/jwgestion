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
        Schema::create('estado_espiritual', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->boolean('estado')->default(1);
            $table->unsignedBigInteger('creador')->default(1);
            $table->unsignedBigInteger('modificador')->default(1);
            $table->timestamps();

            // Foreign keys
            $table->foreign('creador')->references('id')->on('users');
            $table->foreign('modificador')->references('id')->on('users');
        });

        // Insertar registros iniciales
        \Illuminate\Support\Facades\DB::table('estado_espiritual')->insert([
            [
                'id' => 1,
                'nombre' => 'Activo',
                'estado' => 1,
                'creador' => 1,
                'modificador' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'Inactivo',
                'estado' => 1,
                'creador' => 1,
                'modificador' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nombre' => 'Expulsado',
                'estado' => 1,
                'creador' => 1,
                'modificador' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nombre' => 'Desasociado',
                'estado' => 1,
                'creador' => 1,
                'modificador' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estado_espiritual');
    }
};
