<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartePrograma extends Model
{
    protected $table = 'partes_programa';

    protected $fillable = [
        'programa_id',
        'parte_id',
        'orden',
        'tiempo',
        'tema',
        'encargado_id',
        'encargado_reemplazado_id',
        'ayudante_id',
        'ayudante_reemplazado_id',
        'leccion',
        'estado',
        'sala_id',
        'creador_id',
        'modificador_id'
    ];

    protected $casts = [
        'estado' => 'boolean',
        'tiempo' => 'integer'
    ];

    // Relaciones

    /**
     * Relación con el programa
     */
    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class);
    }

    /**
     * Relación con la parte de sección
     */
    public function parte(): BelongsTo
    {
        return $this->belongsTo(ParteSeccion::class, 'parte_id');
    }


    /**
     * Relación con el usuario encargado
     */
    public function encargado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encargado_id');
    }

    /**
     * Relación con el usuario encargado reemplazado
     */
    public function encargadoReemplazado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encargado_reemplazado_id');
    }

    /**
     * Relación con el usuario ayudante
     */
    public function ayudante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ayudante_id');
    }

    /**
     * Relación con el usuario ayudante reemplazado
     */
    public function ayudanteReemplazado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ayudante_reemplazado_id');
    }

    /**
     * Relación con el usuario creador
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    /**
     * Relación con el usuario modificador
     */
    public function modificador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificador_id');
    }

    /**
     * Relación con la sala
     */
    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class, 'sala_id');
    }
}
