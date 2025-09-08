<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Programa extends Model
{
    protected $table = 'programas';

    protected $fillable = [
        'fecha',
        'orador_inicial',
        'presidencia',
        'cancion_pre',
        'cancion_en',
        'cancion_post',
        'orador_final',
        'estado',
        'creador',
        'modificador'
    ];

    protected $casts = [
        'fecha' => 'date',
        'estado' => 'boolean',
    ];

    /**
     * Relación con el usuario orador inicial
     */
    public function oradorInicial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'orador_inicial');
    }

    /**
     * Relación con el usuario presidencia
     */
    public function presidenciaUsuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'presidencia');
    }

    /**
     * Relación con el usuario orador final
     */
    public function oradorFinal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'orador_final');
    }

    /**
     * Relación con la canción pre
     */
    public function cancionPre(): BelongsTo
    {
        return $this->belongsTo(Cancion::class, 'cancion_pre');
    }

    /**
     * Relación con la canción en
     */
    public function cancionEn(): BelongsTo
    {
        return $this->belongsTo(Cancion::class, 'cancion_en');
    }

    /**
     * Relación con la canción post
     */
    public function cancionPost(): BelongsTo
    {
        return $this->belongsTo(Cancion::class, 'cancion_post');
    }

    /**
     * Relación con el usuario creador
     */
    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creador');
    }

    /**
     * Relación con el usuario modificador
     */
    public function usuarioModificador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificador');
    }
}
