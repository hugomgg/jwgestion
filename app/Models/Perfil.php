<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    use Auditable;
    
    protected $table = 'perfiles';
    
    protected $fillable = [
        'nombre',
        'privilegio',
        'descripcion',
        'estado',
        'creador_id',
        'modificador_id',
        'creado_por_timestamp',
        'modificado_por_timestamp',
    ];

    protected $attributes = [
        'privilegio' => 'Anciano', // Por defecto privilegio Anciano
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

    public function users()
    {
        return $this->hasMany(User::class, 'perfil');
    }
}
