<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EstadoEspiritual extends Model
{
    protected $table = 'estado_espiritual';
    
    protected $fillable = [
        'nombre',
        'estado',
        'creador',
        'modificador',
    ];

    protected $attributes = [
        'estado' => 1, // Por defecto estado Activo
        'creador' => 1, // Por defecto usuario con ID 1
        'modificador' => 1 // Por defecto usuario con ID 1
    ];

    /**
     * Boot del modelo para eventos personalizados
     */
    public static function boot()
    {
        parent::boot();

        // Evento al crear un registro
        static::creating(function ($model) {
            $userId = Auth::id() ?? 1;
            $model->creador = $userId;
            $model->modificador = $userId;
        });

        // Evento al actualizar un registro
        static::updating(function ($model) {
            $userId = Auth::id() ?? 1;
            $model->modificador = $userId;
        });
    }

    /**
     * Relación con el usuario creador.
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'creador');
    }

    /**
     * Relación con el usuario modificador.
     */
    public function modificador()
    {
        return $this->belongsTo(User::class, 'modificador');
    }

    /**
     * Verificar si el estado espiritual está activo.
     */
    public function isActive()
    {
        return $this->estado == 1;
    }

    /**
     * Verificar si el estado espiritual está inactivo.
     */
    public function isInactive()
    {
        return $this->estado == 0;
    }

    /**
     * Scope para obtener solo estados espirituales activos.
     */
    public function scopeActive($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para obtener solo estados espirituales inactivos.
     */
    public function scopeInactive($query)
    {
        return $query->where('estado', 0);
    }

    /**
     * Obtener información de auditoría formateada
     */
    public function getAuditInfo()
    {
        return [
            'creado_por' => [
                'usuario_id' => $this->creador,
                'usuario_nombre' => $this->creador->name ?? 'Usuario no encontrado',
                'fecha' => $this->created_at?->format('d/m/Y H:i:s'),
            ],
            'modificado_por' => [
                'usuario_id' => $this->modificador,
                'usuario_nombre' => $this->modificador->name ?? 'Usuario no encontrado',
                'fecha' => $this->updated_at?->format('d/m/Y H:i:s'),
            ]
        ];
    }
}
