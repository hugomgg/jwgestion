<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class ParteSeccion extends Model
{
    use Auditable;
    
    protected $table = 'partes_seccion';
    
    // Constantes para los tipos
    const TIPO_SOLO = 1;
    const TIPO_ACOMPANADO = 2;
    const TIPO_HOMBRE_Y_MUJER = 3;
    
    // Array de tipos disponibles
    public static $tipos = [
        self::TIPO_SOLO => 'Solo',
        self::TIPO_ACOMPANADO => 'Acompañado',
        self::TIPO_HOMBRE_Y_MUJER => 'HombreyMujer'
    ];
    
    protected $fillable = [
        'nombre',
        'abreviacion',
        'orden',
        'tiempo',
        'seccion_id',
        'asignacion_id',
        'tipo',
        'estado',
        'creador_id',
        'modificador_id',
        'creado_por_timestamp',
        'modificado_por_timestamp',
    ];

    protected $attributes = [
        'tipo' => 1, // Por defecto tipo Solo
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
     * Verificar si la parte de sección está activa.
     */
    public function isActive()
    {
        return $this->estado == 1;
    }

    /**
     * Obtener el nombre del tipo.
     */
    public function getTipoNombre()
    {
        return self::$tipos[$this->tipo] ?? 'Desconocido';
    }

    /**
     * Verificar si es tipo Solo.
     */
    public function isTipoSolo()
    {
        return $this->tipo == self::TIPO_SOLO;
    }

    /**
     * Verificar si es tipo Acompañado.
     */
    public function isTipoAcompanado()
    {
        return $this->tipo == self::TIPO_ACOMPANADO;
    }

    /**
     * Verificar si es tipo Hombre y Mujer.
     */
    public function isTipoHombreYMujer()
    {
        return $this->tipo == self::TIPO_HOMBRE_Y_MUJER;
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
     * Relación con la sección de reunión.
     */
    public function seccion()
    {
        return $this->belongsTo(SeccionReunion::class, 'seccion_id');
    }

    /**
     * Relación con la asignación.
     */
    public function asignacion()
    {
        return $this->belongsTo(Asignacion::class, 'asignacion_id');
    }

    /**
     * Scope para obtener solo las partes activas.
     */
    public function scopeActive($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para obtener partes por estado.
     */
    public function scopeByEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para obtener partes ordenadas.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('orden');
    }

    /**
     * Scope para obtener partes de una sección específica.
     */
    public function scopeBySeccion($query, $seccionId)
    {
        return $query->where('seccion_id', $seccionId);
    }

    /**
     * Scope para obtener partes por tipo.
     */
    public function scopeByTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para obtener partes de tipo Solo.
     */
    public function scopeTipoSolo($query)
    {
        return $query->where('tipo', self::TIPO_SOLO);
    }

    /**
     * Scope para obtener partes de tipo Acompañado.
     */
    public function scopeTipoAcompanado($query)
    {
        return $query->where('tipo', self::TIPO_ACOMPANADO);
    }

    /**
     * Scope para obtener partes de tipo Hombre y Mujer.
     */
    public function scopeTipoHombreYMujer($query)
    {
        return $query->where('tipo', self::TIPO_HOMBRE_Y_MUJER);
    }
}
