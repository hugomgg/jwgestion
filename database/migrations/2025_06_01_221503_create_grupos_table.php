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
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->tinyInteger('estado')->default(1);
            $table->unsignedBigInteger('creador')->default(1);
            $table->unsignedBigInteger('modificador')->default(1);
            $table->timestamps();
            
            // Índices
            $table->index('estado');
            $table->index('creador');
            $table->index('modificador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
