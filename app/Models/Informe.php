<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Informe extends Model
{
    use HasFactory;

    protected $table = 'informes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'anio',
        'mes',
        'user_id',
        'grupo_id',
        'congregacion_id',
        'servicio_id',
        'participa',
        'cantidad_estudios',
        'horas',
        'comentario',
        'nota',
        'estado',
        'creador_id',
        'modificador_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'participa' => 'boolean',
        'estado' => 'integer',
        'anio' => 'integer',
        'mes' => 'integer',
        'cantidad_estudios' => 'integer',
        'horas' => 'integer',
    ];

    /**
     * Boot del modelo para manejar auditoría básica.
     */
    protected static function boot()
    {
        parent::boot();

        // Evento al crear un registro
        static::creating(function ($model) {
            $userId = auth()->id() ?? 1; // Si no hay usuario autenticado, usar ID 1 por defecto
            
            $model->creador_id = $userId;
            $model->modificador_id = $userId;
        });

        // Evento al actualizar un registro
        static::updating(function ($model) {
            $userId = auth()->id() ?? 1; // Si no hay usuario autenticado, usar ID 1 por defecto
            
            $model->modificador_id = $userId;
        });
    }

    /**
     * Relación con el modelo User (usuario que reporta).
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias de la relación usuario para compatibilidad.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con el modelo Grupo.
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    /**
     * Relación con el modelo Congregacion.
     */
    public function congregacion()
    {
        return $this->belongsTo(Congregacion::class, 'congregacion_id');
    }

    /**
     * Relación con el modelo Servicio.
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * Relación con el usuario que creó el registro.
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    /**
     * Relación con el usuario que modificó por última vez el registro.
     */
    public function modificador()
    {
        return $this->belongsTo(User::class, 'modificador_id');
    }

    /**
     * Scope para filtrar por año.
     */
    public function scopeByAnio($query, $anio)
    {
        return $query->where('anio', $anio);
    }

    /**
     * Scope para filtrar por mes.
     */
    public function scopeByMes($query, $mes)
    {
        return $query->where('mes', $mes);
    }

    /**
     * Scope para filtrar por usuario.
     */
    public function scopeByUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por congregación.
     */
    public function scopeByCongregacion($query, $congregacionId)
    {
        return $query->where('congregacion_id', $congregacionId);
    }

    /**
     * Scope para filtrar por grupo.
     */
    public function scopeByGrupo($query, $grupoId)
    {
        return $query->where('grupo_id', $grupoId);
    }

    /**
     * Scope para filtrar solo registros activos.
     */
    public function scopeActive($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Obtener el nombre del mes en español.
     */
    public function getNombreMesAttribute()
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero', 
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        return $meses[$this->mes] ?? '';
    }

    /**
     * Obtener el estado en formato legible.
     */
    public function getEstadoTextoAttribute()
    {
        return $this->estado == 1 ? 'Habilitado' : 'Deshabilitado';
    }

    /**
     * Obtener la participación en formato legible.
     */
    public function getParticipaTextoAttribute()
    {
        return $this->participa ? 'Sí' : 'No';
    }
}