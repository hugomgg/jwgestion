<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class SeccionReunion extends Model
{
    use Auditable;
    
    protected $table = 'secciones_reunion';
    
    protected $fillable = [
        'nombre',
        'abreviacion',
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
     * Verificar si la sección de reunión está activa.
     */
    public function isActive()
    {
        return $this->estado == 1;
    }

    /**
     * Relación con el usuario creador.
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    /**
     * Relación con el usuario modificador.
     */
    public function modificador()
    {
        return $this->belongsTo(User::class, 'modificador_id');
    }

    /**
     * Scope para obtener solo las secciones activas.
     */
    public function scopeActive($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para obtener secciones por estado.
     */
    public function scopeByEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
