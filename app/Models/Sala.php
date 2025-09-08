<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    protected $fillable = [
        'nombre',
        'estado',
        'creador_id',
        'modificador_id'
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // Constantes para los nombres de las salas
    const PRINCIPAL = 'Principal';
    const AUXILIAR_1 = 'Auxiliar Núm. 1';
    const AUXILIAR_2 = 'Auxiliar Núm. 2';

    // Array con todos los nombres válidos
    public static $nombres = [
        self::PRINCIPAL,
        self::AUXILIAR_1,
        self::AUXILIAR_2,
    ];

    // Relación con el usuario creador
    public function creador()
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    // Relación con el usuario modificador
    public function modificador()
    {
        return $this->belongsTo(User::class, 'modificador_id');
    }

    // Accessor para obtener el nombre de la sala (ahora es directo)
    public function getNombreTextoAttribute()
    {
        return $this->nombre;
    }

    // Scope para salas activas
    public function scopeActivas($query)
    {
        return $query->where('estado', true);
    }

    // Relación con partes del programa
    public function partesPrograma()
    {
        return $this->hasMany(PartePrograma::class, 'sala_id');
    }
}
