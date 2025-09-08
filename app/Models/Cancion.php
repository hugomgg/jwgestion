<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cancion extends Model
{
    protected $table = 'canciones';

    protected $fillable = [
        'numero',
        'nombre',
        'descripcion',
        'estado',
        'creador',
        'modificador'
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

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
