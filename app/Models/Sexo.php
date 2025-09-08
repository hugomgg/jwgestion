<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Sexo extends Model
{
    use Auditable;
    
    protected $table = 'sexo';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'creador_id',
        'modificador_id',
        'creado_por_timestamp',
        'modificado_por_timestamp',
    ];

    protected $attributes = [
        'estado' => 1, // Por defecto estado Activo
        'creador_id' => 1, // Por defecto usuario con ID 1
        'modificador_id' => 1 // Por defecto usuario con ID 1
    ];

    protected function casts(): array
    {
        return [
            'creado_por_timestamp' => 'datetime',
            'modificado_por_timestamp' => 'datetime',
        ];
    }

    /**
     * Scope para obtener solo registros activos.
     */
    public function scopeActive($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para obtener solo registros inactivos.
     */
    public function scopeInactive($query)
    {
        return $query->where('estado', 0);
    }
}
