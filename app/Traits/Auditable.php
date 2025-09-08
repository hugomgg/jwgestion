<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot del trait para configurar los eventos del modelo
     */
    public static function bootAuditable()
    {
        // Evento al crear un registro
        static::creating(function ($model) {
            $userId = Auth::id() ?? 1; // Si no hay usuario autenticado, usar ID 1 por defecto
            
            $model->creador_id = $userId;
            $model->modificador_id = $userId;
            $model->creado_por_timestamp = now();
            $model->modificado_por_timestamp = now();
        });

        // Evento al actualizar un registro
        static::updating(function ($model) {
            $userId = Auth::id() ?? 1; // Si no hay usuario autenticado, usar ID 1 por defecto
            
            $model->modificador_id = $userId;
            $model->modificado_por_timestamp = now();
        });
    }

    /**
     * Relación con el usuario creador
     */
    public function creador()
    {
        return $this->belongsTo(\App\Models\User::class, 'creador_id');
    }

    /**
     * Relación con el usuario modificador
     */
    public function modificador()
    {
        return $this->belongsTo(\App\Models\User::class, 'modificador_id');
    }

    /**
     * Scope para filtrar por creador
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('creador_id', $userId);
    }

    /**
     * Scope para filtrar por modificador
     */
    public function scopeModifiedBy($query, $userId)
    {
        return $query->where('modificador_id', $userId);
    }

    /**
     * Scope para filtrar por fecha de creación
     */
    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('creado_por_timestamp', [$startDate, $endDate]);
    }

    /**
     * Scope para filtrar por fecha de modificación
     */
    public function scopeModifiedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('modificado_por_timestamp', [$startDate, $endDate]);
    }

    /**
     * Obtener información de auditoría formateada
     */
    public function getAuditInfo()
    {
        return [
            'creado_por' => [
                'usuario_id' => $this->creador_id,
                'usuario_nombre' => $this->creador?->name ?? 'Usuario no encontrado',
                'fecha' => $this->creado_por_timestamp?->format('d/m/Y H:i:s'),
            ],
            'modificado_por' => [
                'usuario_id' => $this->modificador_id,
                'usuario_nombre' => $this->modificador?->name ?? 'Usuario no encontrado',
                'fecha' => $this->modificado_por_timestamp?->format('d/m/Y H:i:s'),
            ]
        ];
    }
}