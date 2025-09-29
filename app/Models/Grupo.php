<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupos';

    protected $fillable = [
        'nombre',
        'estado',
        'creador_id',
        'modificador_id'
    ];

    protected $casts = [
        'estado' => 'integer',
        'creador' => 'integer',
        'modificador' => 'integer'
    ];

    /**
     * Relación con usuarios que pertenecen a este grupo
     */
    public function usuarios()
    {
        return $this->hasMany(User::class, 'grupo');
    }

    /**
     * Relación con el usuario creador
     */
    public function usuarioCreador()
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    /**
     * Relación con el usuario modificador
     */
    public function usuarioModificador()
    {
        return $this->belongsTo(User::class, 'modificador_id');
    }

    /**
     * Scope para grupos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para grupos inactivos
     */
    public function scopeInactivos($query)
    {
        return $query->where('estado', 0);
    }
}
