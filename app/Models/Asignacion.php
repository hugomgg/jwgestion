<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    use Auditable;
    
    protected $table = 'asignaciones';
    
    protected $fillable = [
        'nombre',
        'abreviacion',
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
     * Get the users that belong to the asignacion.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'asignaciones_users', 'asignacion_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Verificar si la asignación está activa.
     */
    public function isActive()
    {
        return $this->estado == 1;
    }

    /**
     * Verificar si la asignación está inactiva.
     */
    public function isInactive()
    {
        return $this->estado == 0;
    }

    /**
     * Scope para obtener solo asignaciones activas.
     */
    public function scopeActive($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para obtener solo asignaciones inactivas.
     */
    public function scopeInactive($query)
    {
        return $query->where('estado', 0);
    }
}
