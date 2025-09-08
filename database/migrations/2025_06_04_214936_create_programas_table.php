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
        Schema::create('programas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha'); // Campo obligatorio
            $table->unsignedBigInteger('orador_inicial')->nullable(); // Opcional, relacionado con users
            $table->unsignedBigInteger('presidencia')->nullable(); // Opcional, relacionado con users
            $table->unsignedBigInteger('cancion_pre')->nullable(); // Opcional, relacionado con canciones
            $table->unsignedBigInteger('cancion_en')->nullable(); // Opcional, relacionado con canciones
            $table->unsignedBigInteger('cancion_post')->nullable(); // Opcional, relacionado con canciones
            $table->unsignedBigInteger('orador_final')->nullable(); // Opcional, relacionado con users
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('creador')->default(1);
            $table->unsignedBigInteger('modificador')->default(1);
            $table->timestamps();

            // Foreign keys para usuarios
            $table->foreign('orador_inicial')->references('id')->on('users')->onDelete('set null');
            $table->foreign('presidencia')->references('id')->on('users')->onDelete('set null');
            $table->foreign('orador_final')->references('id')->on('users')->onDelete('set null');
            
            // Foreign keys para canciones
            $table->foreign('cancion_pre')->references('id')->on('canciones')->onDelete('set null');
            $table->foreign('cancion_en')->references('id')->on('canciones')->onDelete('set null');
            $table->foreign('cancion_post')->references('id')->on('canciones')->onDelete('set null');
            
            // Foreign keys para auditoría
            $table->foreign('creador')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('modificador')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programas');
    }
};
