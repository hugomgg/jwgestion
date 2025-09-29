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

        // Los datos iniciales se insertarán mediante el seeder EstadoEspiritualSeeder
        // después de que se creen los usuarios
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estado_espiritual');
    }
};
