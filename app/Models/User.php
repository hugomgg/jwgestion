<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nombre_completo',
        'email',
        'password',
        'perfil',
        'estado',
        'creador_id',
        'modificador_id',
        'creado_por_timestamp',
        'modificado_por_timestamp',
        'congregacion',
        'grupo',
        'fecha_nacimiento',
        'fecha_bautismo',
        'telefono',
        'persona_contacto',
        'telefono_contacto',
        'sexo',
        'servicio',
        'nombramiento',
        'esperanza',
        'estado_espiritual',
        'observacion',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'perfil' => 2, // Por defecto perfil Estudiante
        'estado' => 1, // Por defecto estado Activo
        'creador_id' => 1, // Por defecto usuario con ID 1
        'modificador_id' => 1, // Por defecto usuario con ID 1
        'congregacion' => 1, // Por defecto congregación con ID 1
        'grupo' => 1, // Por defecto grupo con ID 1
        'sexo' => 1, // Por defecto sexo con ID 1
        'esperanza' => 2, // Por defecto esperanza con ID 2
        'estado_espiritual' => 1 // Por defecto estado espiritual "Activo"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'creado_por_timestamp' => 'datetime',
            'modificado_por_timestamp' => 'datetime',
            'fecha_nacimiento' => 'date',
            'fecha_bautismo' => 'date',
        ];
    }

    /**
     * Get the perfil that owns the user.
     */
    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil');
    }

    /**
     * Get the profile relationship (alias for perfil)
     */
    public function profile()
    {
        return $this->perfil();
    }

    /**
     * Get the congregacion that owns the user.
     */
    public function congregacion()
    {
        return $this->belongsTo(Congregacion::class, 'congregacion');
    }

    /**
     * Get the sexo that owns the user.
     */
    public function sexo()
    {
        return $this->belongsTo(Sexo::class, 'sexo');
    }

    /**
     * Get the servicio that owns the user.
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio');
    }

    /**
     * Get the nombramiento that owns the user.
     */
    public function nombramiento()
    {
        return $this->belongsTo(Nombramiento::class, 'nombramiento');
    }

    /**
     * Get the esperanza that owns the user.
     */
    public function esperanza()
    {
        return $this->belongsTo(Esperanza::class, 'esperanza');
    }

    /**
     * Get the grupo that owns the user.
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo');
    }

    /**
     * Get the estado espiritual that owns the user.
     */
    public function estadoEspiritual()
    {
        return $this->belongsTo(EstadoEspiritual::class, 'estado_espiritual');
    }

    /**
     * Get the asignaciones that belong to the user.
     */
    public function asignaciones()
    {
        return $this->belongsToMany(Asignacion::class, 'asignaciones_users', 'user_id', 'asignacion_id')
                    ->withTimestamps();
    }

    /**
     * Verificar si el usuario está activo.
     */
    public function isActive()
    {
        return $this->estado == 1;
    }

    /**
     * Verificar si el usuario está inactivo.
     */
    public function isInactive()
    {
        return $this->estado == 0;
    }

    /**
     * Activar el usuario.
     */
    public function activate()
    {
        $this->update(['estado' => 1]);
    }

    /**
     * Desactivar el usuario.
     */
    public function deactivate()
    {
        $this->update(['estado' => 0]);
    }

    /**
     * Scope para obtener solo usuarios activos.
     */
    public function scopeActive($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para obtener solo usuarios inactivos.
     */
    public function scopeInactive($query)
    {
        return $query->where('estado', 0);
    }

    /**
     * Verificar si el usuario es administrador.
     */
    public function isAdmin()
    {
        return $this->perfil == 1;
    }

    /**
     * Verificar si el usuario es supervisor.
     */
    public function isSupervisor()
    {
        return $this->perfil == 2;
    }

    /**
     * Verificar si el usuario es coordinador.
     */
    public function isCoordinator()
    {
        return $this->perfil == 3;
    }

    /**
     * Verificar si el usuario es subcoordinador.
     */
    public function isSubcoordinator()
    {
        return $this->perfil == 4;
    }

    /**
     * Verificar si el usuario es secretario.
     */
    public function isSecretary()
    {
        return $this->perfil == 5;
    }

    /**
     * Verificar si el usuario es subsecretario.
     */
    public function isSubsecretary()
    {
        return $this->perfil == 6;
    }

    /**
     * Verificar si el usuario es organizador.
     */
    public function isOrganizer()
    {
        return $this->perfil == 7;
    }

    /**
     * Verificar si el usuario es suborganizador.
     */
    public function isSuborganizer()
    {
        return $this->perfil == 8;
    }

    /**
     * Verificar si el usuario es estudiante.
     */
    public function isStudent()
    {
        return $this->perfil == 19; // ID 19 es el perfil Estudiante actual
    }

    /**
     * Verificar si el usuario tiene permisos de supervisión (puede ver pero no modificar mucho).
     */
    public function canSupervise()
    {
        return $this->perfil == 2;
    }

    /**
     * Verificar si el usuario tiene permisos de solo lectura.
     */
    public function isReadOnly()
    {
        return in_array($this->perfil, [18, 19]); // Publicador y Estudiante
    }

    /**
     * Verificar si el usuario puede acceder al menú de administración (administradores, supervisores, coordinadores, organizadores, subsecretarios y suborganizadores).
     */
    public function canAccessAdminMenu()
    {
        return $this->perfil == 1 || $this->perfil == 2 || $this->perfil == 3 || $this->perfil == 4 || $this->perfil == 5 || $this->perfil == 6 || $this->perfil == 7 || $this->perfil == 8;
    }

    /**
     * Verificar si el usuario puede acceder al menú de gestión de personas (administradores, supervisores, coordinadores, subcoordinadores, secretarios, subsecretarios, organizadores y suborganizadores).
     */
    public function canAccessPeopleManagementMenu()
    {
        return $this->perfil == 1 || $this->perfil == 2 || $this->perfil == 3 || $this->perfil == 4 || $this->perfil == 5 || $this->perfil == 6 || $this->perfil == 7 || $this->perfil == 8;
    }

    /**
     * Verificar si el usuario puede crear/editar/eliminar (administradores, coordinadores, secretarios y organizadores).
     */
    public function canModify()
    {
        return $this->perfil == 1 || $this->perfil == 3 || $this->perfil == 5 || $this->perfil == 7;
    }

    /**
     * Verificar si el usuario puede gestionar usuarios (coordinadores y secretarios).
     */
    public function canManageUsers()
    {
        return $this->perfil == 3 || $this->perfil == 5;
    }

    /**
     * Verificar si el usuario puede acceder al menú de gestión de personas.
     */
    public function canAccessPeopleMenu()
    {
        return $this->perfil == 1 || $this->perfil == 2 || $this->perfil == 3 || $this->perfil == 4 || $this->perfil == 5 || $this->perfil == 6 || $this->perfil == 7 || $this->perfil == 8;
    }

    /**
     * Verificar si el usuario puede ver usuarios (administradores, supervisores, coordinadores, subcoordinadores, secretarios, subsecretarios, organizadores y suborganizadores).
     */
    public function canViewUsers()
    {
        return $this->perfil == 1 || $this->perfil == 2 || $this->perfil == 3 || $this->perfil == 4 || $this->perfil == 5 || $this->perfil == 6 || $this->perfil == 7 || $this->perfil == 8;
    }

    /**
     * Obtener el nombre del perfil del usuario.
     */
    public function getPerfilNameAttribute()
    {
        // Use relationLoaded to check if relationship is already loaded
        if ($this->relationLoaded('perfil') && $this->getRelation('perfil')) {
            return $this->getRelation('perfil')->nombre;
        }
        
        // If not loaded, load it and return the name
        $perfil = $this->perfil()->first();
        return $perfil ? $perfil->nombre : 'Sin perfil';
    }

    /**
     * Obtener el privilegio del perfil del usuario.
     */
    public function getPerfilPrivilegioAttribute()
    {
        // Use relationLoaded to check if relationship is already loaded
        if ($this->relationLoaded('perfil') && $this->getRelation('perfil')) {
            return $this->getRelation('perfil')->privilegio;
        }
        
        // If not loaded, load it and return the privilegio
        $perfil = $this->perfil()->first();
        return $perfil ? $perfil->privilegio : 'Sin privilegio';
    }

    /**
     * Get the user's role name (alias for perfil privilegio)
     */
    public function getRoleNameAttribute()
    {
        return $this->perfil_privilegio;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
