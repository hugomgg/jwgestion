<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Servicio extends Model
{
    use HasFactory, Auditable;

    protected $table = 'servicios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'creador_id',
        'modificador_id',
        'creado_por_timestamp',
        'modificado_por_timestamp'
    ];

    protected $attributes = [
        'estado' => 1,
        'creador_id' => 1,
        'modificador_id' => 1
    ];

    protected $casts = [
        'estado' => 'integer',
        'creado_por_timestamp' => 'datetime',
        'modificado_por_timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con el usuario que creó el registro
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    /**
     * Relación con el usuario que modificó el registro
     */
    public function modificador()
    {
        return $this->belongsTo(User::class, 'modificador_id');
    }

    /**
     * Scope para servicios activos
     */
    public function scopeActive($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para servicios inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where('estado', 0);
    }

    /**
     * Accessor para obtener el texto del estado
     */
    public function getEstadoTextoAttribute()
    {
        return $this->estado == 1 ? 'Activo' : 'Inactivo';
    }
}
