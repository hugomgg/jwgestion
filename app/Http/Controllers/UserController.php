<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ParteSeccion;
use App\Models\Perfil;
use App\Models\Congregacion;
use App\Models\Sexo;
use App\Models\Servicio;
use App\Models\Nombramiento;
use App\Models\Esperanza;
use App\Models\Asignacion;
use App\Models\Grupo;
use App\Models\EstadoEspiritual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $currentUser = auth()->user();

        // Construir condiciones WHERE de forma segura con parámetros enlazados
        $whereConditions = [];
        $queryParams = [];

        // Si es coordinador, subcoordinador, secretario, subsecretario, organizador o suborganizador,
        // solo mostrar usuarios de su congregación
        if ($currentUser->isCoordinator() || $currentUser->isSubcoordinator() || $currentUser->isSecretary() || $currentUser->isSubsecretary() || $currentUser->isOrganizer() || $currentUser->isSuborganizer()) {
            $whereConditions[] = "u.congregacion = ?";
            $queryParams[] = $currentUser->congregacion;
        }

        // Administradores y supervisores ven todos los usuarios pero filtrados por perfil
        if ($currentUser->isAdmin() || $currentUser->isSupervisor()) {
            $whereConditions[] = "u.perfil IN (1, 2, 3)";
        }

        // Construir cláusula WHERE unificada (evita múltiples WHERE y es SQL válido)
        $whereClause = !empty($whereConditions)
            ? "WHERE " . implode(" AND ", $whereConditions)
            : "";

        // Consulta base con JOIN explícito para obtener el nombre del perfil, congregación, nombramiento, servicio, grupo, estado espiritual y datos de auditoría
        $users = DB::select("WITH
                                asignaciones_usuarios AS (
                                    SELECT user_id,GROUP_CONCAT(a.abreviacion ORDER BY a.id ASC SEPARATOR ',') AS asignaciones
                                    FROM asignaciones_users au
                                    INNER JOIN asignaciones a ON au.asignacion_id = a.id AND a.estado = 1
                                    GROUP BY user_id
                                )
                                SELECT
                                    u.id,
                                    u.name,
                                    u.email,
                                    u.estado,
                                    p.nombre as nombre_perfil,
                                    p.privilegio as privilegio_perfil,
                                    p.id as perfil_id,
                                    c.nombre as nombre_congregacion,
                                    c.id as congregacion_id,
                                    g.nombre as nombre_grupo,
                                    g.id as grupo_id,
                                    n.nombre as nombre_nombramiento,
                                    n.id as nombramiento_id,
                                    s.nombre as nombre_servicio,
                                    s.id as servicio_id,
                                    ee.nombre as nombre_estado_espiritual,
                                    ee.id as estado_espiritual_id,
                                    u.creado_por_timestamp,
                                    u.modificado_por_timestamp,
                                    creador.name as creado_por_nombre,
                                    modificador.name as modificado_por_nombre,
                                    asignaciones
                                FROM users u
                                INNER JOIN perfiles p ON u.perfil=p.id
                                INNER JOIN congregaciones c ON u.congregacion = c.id
                                INNER JOIN grupos g ON u.grupo = g.id
                                INNER JOIN estado_espiritual ee ON u.estado_espiritual = ee.id
                                LEFT JOIN nombramiento n ON u.nombramiento = n.id
                                LEFT JOIN servicios s ON u.servicio = s.id
                                LEFT JOIN users as creador ON u.creador_id = creador.id
                                LEFT JOIN users as modificador ON u.modificador_id = modificador.id
                                LEFT JOIN asignaciones_usuarios aus ON u.id=aus.user_id
                                $whereClause
                            ", $queryParams);


        // Filtrar perfiles para el filtro del listado
        if ($currentUser->isSecretary() || $currentUser->isSubsecretary() || $currentUser->isOrganizer() || $currentUser->isSuborganizer()) {
            // Secretarios, subsecretarios, organizadores y suborganizadores no pueden ver perfiles 1, 2 en el filtro
            $perfiles = Perfil::whereNotIn('id', [1, 2])->get();
        } elseif ($currentUser->isCoordinator() || $currentUser->isSubcoordinator()) {
            // Coordinadores y subcoordinadores no pueden ver perfiles 1, 2
            $perfiles = Perfil::whereNotIn('id', [1, 2])->get();
        } else {
            // Administradores y supervisores ven todos los perfiles en el filtro
            $perfiles = Perfil::all();
        }

        // Filtrar perfiles para el modal CREAR (solo perfiles activos)
        // Coordinadores, secretarios o organizadores no pueden crear usuarios con perfiles 1, 2
        if ($currentUser->isSecretary() || $currentUser->isCoordinator() || $currentUser->isOrganizer()) {
            $perfilesModal = Perfil::where('estado', 1)->whereNotIn('id', [1, 2])->get();
        } elseif ($currentUser->isAdmin()) {
            // Administradores y supervisores ven todos los perfiles activos
            $perfilesModal = Perfil::where('estado', 1)->get();
        } else {
            // Subsecretarios y suborganizadores tienen acceso de solo lectura, no necesitan perfiles para modales
            $perfilesModal = Perfil::whereNotIn('id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10])->get();
        }

        // Filtrar perfiles para el modal EDITAR (perfiles activos e inactivos)
        //Administradores, Secretarios y organizadores ven todos los perfiles
        if ($currentUser->isAdmin()) {
            $perfilesModalEdit = Perfil::all();
        } elseif ($currentUser->isSecretary() || $currentUser->isCoordinator() || $currentUser->isOrganizer()) {
            $perfilesModalEdit = Perfil::whereNotIn('id', [1, 2])->get();
        } elseif ($currentUser->isSubsecretary() || $currentUser->isSuborganizer() || $currentUser->isSubcoordinator()) {
            // Subsecretarios y suborganizadores tienen acceso de solo lectura, no necesitan perfiles para modales
            $perfilesModalEdit = Perfil::whereNotIn('id', [1, 2, 3, 4, 5, 6])->get();
        } else {
            // Supervisor u otros perfiles: ven todos los perfiles (igual que Admin)
            $perfilesModalEdit = Perfil::all();
        }
        $sexos = Sexo::where('estado', 1)->get();
        $servicios = Servicio::where('estado', 1)->get();
        $nombramientos = Nombramiento::where('estado', 1)->get();
        $esperanzas = Esperanza::where('estado', 1)->get();
        $asignaciones = Asignacion::where('estado', 1)->orderBy('nombre')->get();
        $estadosEspirituales = EstadoEspiritual::where('estado', 1)->get();

        // Si es coordinador, subcoordinador, secretario u organizador, solo mostrar su congregación
        if ($currentUser->isCoordinator() || $currentUser->isSubcoordinator() || $currentUser->isSecretary() || $currentUser->isOrganizer()) {
            $congregaciones = Congregacion::where('id', $currentUser->congregacion)->where('estado', 1)->get();
        } else {
            $congregaciones = Congregacion::where('estado', 1)->get();
        }

        // Para el filtro de la vista principal, coordinadores, subcoordinadores, secretarios, subsecretarios, organizadores y suborganizadores solo ven grupos asignados a usuarios de su congregación
        if ($currentUser->isCoordinator() || $currentUser->isSubcoordinator() || $currentUser->isSecretary() || $currentUser->isSubsecretary() || $currentUser->isOrganizer() || $currentUser->isSuborganizer()) {
            // Obtener solo los grupos que están asignados a usuarios de la congregación del coordinador/subcoordinador/secretario/subsecretario/organizador/suborganizador
            $gruposParaFiltro = Grupo::whereIn('id', function ($query) use ($currentUser) {
                $query->select('grupo')
                    ->from('users')
                    ->where('congregacion', $currentUser->congregacion)
                    ->distinct();
            })->where('estado', 1)->get();
        } else {
            $gruposParaFiltro = Grupo::where('estado', 1)->get();
        }

        // Para modales crear/editar, mostrar grupos según el perfil del usuario
        if ($currentUser->isAdmin() || $currentUser->isSupervisor()) {
            // Administradores y supervisores ven todos los grupos
            $grupos = Grupo::where('estado', 1)->get();
        } else {
            // Otros usuarios solo ven grupos de su congregación
            $grupos = Grupo::where('congregacion_id', $currentUser->congregacion)
                ->where('estado', 1)
                ->get();
        }

        return view('users.index', compact('users', 'perfiles', 'perfilesModal', 'perfilesModalEdit', 'congregaciones', 'sexos', 'servicios', 'nombramientos', 'esperanzas', 'grupos', 'gruposParaFiltro', 'asignaciones', 'estadosEspirituales'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $currentUser = auth()->user();

        // Filtrar perfiles según el tipo de usuario
        //Secretario, coordinador y organizador no pueden crear usuarios con perfiles 1, 2
        if ($currentUser->isSecretary() || $currentUser->isCoordinator() || $currentUser->isOrganizer()) {
            // Secretarios no pueden ver perfiles 1, 2, 3, 4, 7, 8
            $perfiles = Perfil::whereNotIn('id', [1, 2])->get();
        } elseif ($currentUser->Admin()) {
            // Administradores ven todos los perfiles
            $perfiles = Perfil::all();
        }

        // Obtener datos para dropdowns en formulario de creación
        $congregaciones = Congregacion::where('estado', 1)->get();
        $sexos = Sexo::where('estado', 1)->get();
        $servicios = Servicio::where('estado', 1)->get();
        $nombramientos = Nombramiento::where('estado', 1)->get();
        $esperanzas = Esperanza::where('estado', 1)->get();
        $asignaciones = Asignacion::where('estado', 1)->get();
        $estadosEspirituales = EstadoEspiritual::where('estado', 1)->get();

        // Para el filtro de la vista principal, coordinadores, subcoordinadores y secretarios solo ven grupos asignados a usuarios de su congregación
        if ($currentUser->isCoordinator() || $currentUser->isSubcoordinator() || $currentUser->isSecretary()) {
            // Obtener solo los grupos que están asignados a usuarios de la congregación del coordinador/subcoordinador/secretario
            $gruposParaFiltro = Grupo::whereIn('id', function ($query) use ($currentUser) {
                $query->select('grupo')
                    ->from('users')
                    ->where('congregacion', $currentUser->congregacion)
                    ->distinct();
            })->where('estado', 1)->get();
        } else {
            $gruposParaFiltro = Grupo::where('estado', 1)->get();
        }

        // Para modales crear/editar, mostrar grupos según el perfil del usuario
        if ($currentUser->isAdmin() || $currentUser->isSupervisor()) {
            // Administradores y supervisores ven todos los grupos
            $grupos = Grupo::where('estado', 1)->get();
        } else {
            // Otros usuarios solo ven grupos de su congregación
            $grupos = Grupo::where('congregacion_id', $currentUser->congregacion)
                ->where('estado', 1)
                ->get();
        }

        return view('users.create', compact('perfiles', 'congregaciones', 'sexos', 'servicios', 'nombramientos', 'esperanzas', 'grupos', 'gruposParaFiltro', 'asignaciones', 'estadosEspirituales'));
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        try {
            $currentUser = auth()->user();
            $user = User::with(['perfil', 'congregacion', 'sexo', 'servicio', 'nombramiento', 'esperanza', 'grupo', 'estadoEspiritual', 'asignaciones', 'creador', 'modificador'])->findOrFail($id);

            // Si es coordinador, subcoordinador, secretario, subsecretario, organizador o suborganizador, verificar que el usuario pertenezca a la misma congregación
            if (($currentUser->isCoordinator() || $currentUser->isSubcoordinator() || $currentUser->isSecretary() || $currentUser->isSubsecretary() || $currentUser->isOrganizer() || $currentUser->isSuborganizer()) && $user->congregacion != $currentUser->congregacion) {
                return redirect()->route('users.index')->with('error', 'No tiene permisos para ver este usuario.');
            }

            return view('users.show', compact('user'));

        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
        }
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();

        // Si es coordinador, secretario y organizador forzar que la congregación sea la suya y validar perfil
        if ($currentUser->isCoordinator() || $currentUser->isSecretary() || $currentUser->isOrganizer()) {
            $request->merge(['congregacion' => $currentUser->congregacion]);

            // Coordinadores no pueden crear usuarios con perfiles 1, 2
            if (in_array($request->perfil, [1, 2])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para asignar este perfil.'
                ], 403);
            }
        } elseif (!$currentUser->isAdmin()) {
            //Solo administradores pueden crear usuarios
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para crear usuarios.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nombre_completo' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
            'password' => ['nullable', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).+$/'],
            'perfil' => 'required|integer|exists:perfiles,id',
            'estado' => 'required|integer|in:0,1',
            'congregacion' => 'required|integer|exists:congregaciones,id',
            'fecha_nacimiento' => 'nullable|date',
            'fecha_bautismo' => 'nullable|date',
            'telefono' => 'nullable|string|max:20',
            'persona_contacto' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:20',
            'sexo' => 'required|integer|exists:sexo,id',
            'servicio' => 'nullable|integer|exists:servicios,id',
            'nombramiento' => 'nullable|integer|exists:nombramiento,id',
            'esperanza' => 'required|integer|exists:esperanzas,id',
            'grupo' => 'required|integer|exists:grupos,id',
            'estado_espiritual' => 'required|integer|exists:estado_espiritual,id',
            'observacion' => 'nullable|string|max:1000',
            'asignaciones' => 'nullable|array',
            'asignaciones.*' => 'integer|exists:asignaciones,id'
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'nombre_completo.max' => 'El nombre completo no puede exceder 255 caracteres.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe contener al menos una letra y un número.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'perfil.required' => 'El perfil es obligatorio.',
            'perfil.exists' => 'El perfil seleccionado no es válido.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser Activo o Inactivo.',
            'congregacion.required' => 'La congregación es obligatoria.',
            'congregacion.exists' => 'La congregación seleccionada no es válida.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'fecha_bautismo.date' => 'La fecha de bautismo debe ser una fecha válida.',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
            'persona_contacto.max' => 'La persona de contacto no puede exceder 255 caracteres.',
            'telefono_contacto.max' => 'El teléfono de contacto no puede exceder 20 caracteres.',
            'sexo.required' => 'El sexo es obligatorio.',
            'sexo.exists' => 'El sexo seleccionado no es válido.',
            'servicio.exists' => 'El servicio seleccionado no es válido.',
            'nombramiento.exists' => 'El nombramiento seleccionado no es válido.',
            'esperanza.required' => 'La esperanza es obligatoria.',
            'esperanza.exists' => 'La esperanza seleccionada no es válida.',
            'grupo.required' => 'El grupo es obligatorio.',
            'grupo.exists' => 'El grupo seleccionado no es válido.',
            'estado_espiritual.required' => 'El estado espiritual es obligatorio.',
            'estado_espiritual.exists' => 'El estado espiritual seleccionado no es válido.',
            'observacion.max' => 'La observación no puede exceder 1000 caracteres.',
            'asignaciones.array' => 'Las asignaciones deben ser un array válido.',
            'asignaciones.*.integer' => 'Cada asignación debe ser un número válido.',
            'asignaciones.*.exists' => 'Una o más asignaciones seleccionadas no son válidas.'
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userData = [
                'name' => $request->name,
                'nombre_completo' => $request->nombre_completo,
                'email' => $request->email,
                'perfil' => $request->perfil,
                'estado' => $request->estado,
                'congregacion' => $request->congregacion,
                'grupo' => $request->grupo,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'fecha_bautismo' => $request->fecha_bautismo,
                'telefono' => $request->telefono,
                'persona_contacto' => $request->persona_contacto,
                'telefono_contacto' => $request->telefono_contacto,
                'sexo' => $request->sexo,
                'servicio' => $request->servicio,
                'nombramiento' => $request->nombramiento,
                'esperanza' => $request->esperanza,
                'estado_espiritual' => $request->estado_espiritual,
                'observacion' => $request->observacion,
            ];

            // Agregar contraseña: si se proporciona usar la del request, sino usar una temporal
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            } else {
                // Establecer una contraseña temporal basada en el email
                $tempPassword = 'temp' . substr(md5($request->email), 0, 8);
                $userData['password'] = Hash::make($tempPassword);
            }

            $user = User::create($userData);

            // Sincronizar asignaciones (elimina todas las existentes y agrega la nueva selección)
            $asignaciones = $request->has('asignaciones') && is_array($request->asignaciones)
                ? $request->asignaciones
                : [];
            $user->asignaciones()->sync($asignaciones);

            // Recargar el usuario con todas sus relaciones para devolver los datos completos
            $userWithRelations = DB::table('users')
                ->join('perfiles', 'users.perfil', '=', 'perfiles.id')
                ->join('congregaciones', 'users.congregacion', '=', 'congregaciones.id')
                ->join('grupos', 'users.grupo', '=', 'grupos.id')
                ->leftJoin('nombramiento', 'users.nombramiento', '=', 'nombramiento.id')
                ->join('estado_espiritual', 'users.estado_espiritual', '=', 'estado_espiritual.id')
                ->leftJoin('servicios', 'users.servicio', '=', 'servicios.id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.estado',
                    'perfiles.privilegio as privilegio_perfil',
                    'congregaciones.nombre as nombre_congregacion',
                    'grupos.nombre as nombre_grupo',
                    'nombramiento.nombre as nombre_nombramiento',
                    'servicios.nombre as nombre_servicio',
                    'estado_espiritual.nombre as nombre_estado_espiritual'
                )
                ->where('users.id', $user->id)
                ->first();

            // Cargar asignaciones
            $userModel = User::with('asignaciones')->find($user->id);
            $asignacionesData = $userModel ? $userModel->asignaciones : collect();

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente.',
                'user' => $userWithRelations,
                'asignaciones' => $asignacionesData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        try {
            $currentUser = auth()->user();
            $user = User::with(['creador', 'modificador', 'asignaciones', 'grupo'])->findOrFail($id);

            // Roles con visión restringida a su propia congregación:
            // Coordinador, Subcoordinador, Secretario, Subsecretario, Organizador, Suborganizador
            $esRolPorCongregacion = $currentUser->isCoordinator()
                || $currentUser->isSubcoordinator()
                || $currentUser->isSecretary()
                || $currentUser->isSubsecretary()
                || $currentUser->isOrganizer()
                || $currentUser->isSuborganizer();

            if ($esRolPorCongregacion && $user->congregacion != $currentUser->congregacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para ver este usuario.'
                ], 403);
            }


            // Formatear la información de auditoría
            $userData = $user->toArray();
            $userData['creado_por_nombre'] = $user->creador ? $user->creador->name : null;
            $userData['modificado_por_nombre'] = $user->modificador ? $user->modificador->name : null;
            $userData['creado_por_timestamp'] = $user->creado_por_timestamp ?
                $user->creado_por_timestamp->format('d/m/Y H:i:s') : null;
            $userData['modificado_por_timestamp'] = $user->modificado_por_timestamp ?
                $user->modificado_por_timestamp->format('d/m/Y H:i:s') : null;

            // Preservar el ID del grupo (se pierde con toArray() al cargar la relación)
            $userData['grupo_id'] = $user->getAttributes()['grupo'];

            // Preservar el ID del estado espiritual
            $userData['estado_espiritual'] = $user->getAttributes()['estado_espiritual'];

            // Agregar IDs de asignaciones
            $userData['asignaciones'] = $user->asignaciones->pluck('id')->toArray();

            // Formatear fechas para inputs tipo date (YYYY-MM-DD)
            if ($user->fecha_nacimiento) {
                $userData['fecha_nacimiento'] = date('Y-m-d', strtotime($user->fecha_nacimiento));
            }
            if ($user->fecha_bautismo) {
                $userData['fecha_bautismo'] = date('Y-m-d', strtotime($user->fecha_bautismo));
            }

            // Agregar opciones de congregaciones y grupos según el perfil
            if ($currentUser->isCoordinator()) {
                $userData['congregaciones_disponibles'] = Congregacion::where('id', $currentUser->congregacion)->where('estado', 1)->get();
                // Filtrar grupos solo de la congregación del usuario autenticado
                $userData['grupos_disponibles'] = Grupo::where('congregacion_id', $currentUser->congregacion)
                    ->where('estado', 1)
                    ->get();
            } elseif ($currentUser->isAdmin() || $currentUser->isSupervisor()) {
                // Administradores y supervisores ven todas las congregaciones y todos los grupos
                $userData['congregaciones_disponibles'] = Congregacion::where('estado', 1)->get();
                $userData['grupos_disponibles'] = Grupo::where('estado', 1)->get();
            }

            return response()->json([
                'success' => true,
                'user' => $userData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $currentUser = auth()->user();
            $user = User::findOrFail($id);

            // Supervisores son solo lectura: no pueden modificar usuarios
            if ($currentUser->isSupervisor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los supervisores no tienen permisos para modificar usuarios.'
                ], 403);
            }

            // Si es coordinador,secretario o organizador verificar que el usuario pertenezca a la misma congregación
            if (($currentUser->isCoordinator() || $currentUser->isSecretary() || $currentUser->isOrganizer()) && $user->congregacion != $currentUser->congregacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para actualizar este usuario.'
                ], 403);
            }

            // Si es coordinador, secretario o organizador forzar que la congregación sea la suya y validar perfil
            if ($currentUser->isCoordinator() || $currentUser->isSecretary() || $currentUser->isOrganizer()) {
                $request->merge(['congregacion' => $currentUser->congregacion]);

                // Coordinadores no pueden actualizar usuarios con perfiles 1, 2
                if (in_array($request->perfil, [1, 2])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tiene permisos para asignar este perfil.'
                    ], 403);
                }
            }


            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'nombre_completo' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
                'password' => ['nullable', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).+$/'],
                'perfil' => 'required|integer|exists:perfiles,id',
                'estado' => 'required|integer|in:0,1',
                'congregacion' => 'required|integer|exists:congregaciones,id',
                'fecha_nacimiento' => 'nullable|date',
                'fecha_bautismo' => 'nullable|date',
                'telefono' => 'nullable|string|max:20',
                'persona_contacto' => 'nullable|string|max:255',
                'telefono_contacto' => 'nullable|string|max:20',
                'sexo' => 'required|integer|exists:sexo,id',
                'servicio' => 'nullable|integer|exists:servicios,id',
                'nombramiento' => 'nullable|integer|exists:nombramiento,id',
                'esperanza' => 'required|integer|exists:esperanzas,id',
                'grupo' => 'required|integer|exists:grupos,id',
                'estado_espiritual' => 'required|integer|exists:estado_espiritual,id',
                'observacion' => 'nullable|string|max:1000',
                'asignaciones' => 'nullable|array',
                'asignaciones.*' => 'integer|exists:asignaciones,id'
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'name.max' => 'El nombre no puede exceder 255 caracteres.',
                'nombre_completo.max' => 'El nombre completo no puede exceder 255 caracteres.',
                'email.email' => 'El email debe tener un formato válido.',
                'email.unique' => 'Este email ya está registrado.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.regex' => 'La contraseña debe contener al menos una letra y un número.',
                'password.confirmed' => 'La confirmación de contraseña no coincide.',
                'perfil.required' => 'El perfil es obligatorio.',
                'perfil.exists' => 'El perfil seleccionado no es válido.',
                'estado.required' => 'El estado es obligatorio.',
                'estado.in' => 'El estado debe ser Activo o Inactivo.',
                'congregacion.required' => 'La congregación es obligatoria.',
                'congregacion.exists' => 'La congregación seleccionada no es válida.',
                'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida.',
                'fecha_bautismo.date' => 'La fecha de bautismo debe ser una fecha válida.',
                'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
                'persona_contacto.max' => 'La persona de contacto no puede exceder 255 caracteres.',
                'telefono_contacto.max' => 'El teléfono de contacto no puede exceder 20 caracteres.',
                'sexo.required' => 'El sexo es obligatorio.',
                'sexo.exists' => 'El sexo seleccionado no es válido.',
                'servicio.exists' => 'El servicio seleccionado no es válido.',
                'nombramiento.exists' => 'El nombramiento seleccionado no es válido.',
                'esperanza.required' => 'La esperanza es obligatoria.',
                'esperanza.exists' => 'La esperanza seleccionada no es válida.',
                'grupo.required' => 'El grupo es obligatorio.',
                'grupo.exists' => 'El grupo seleccionado no es válido.',
                'estado_espiritual.required' => 'El estado espiritual es obligatorio.',
                'estado_espiritual.exists' => 'El estado espiritual seleccionado no es válido.',
                'observacion.max' => 'La observación no puede exceder 1000 caracteres.',
                'asignaciones.array' => 'Las asignaciones deben ser un array válido.',
                'asignaciones.*.integer' => 'Cada asignación debe ser un número válido.',
                'asignaciones.*.exists' => 'Una o más asignaciones seleccionadas no son válidas.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [
                'name' => $request->name,
                'nombre_completo' => $request->nombre_completo,
                'email' => $request->email,
                'perfil' => $request->perfil,
                'estado' => $request->estado,
                'congregacion' => $request->congregacion,
                'grupo' => $request->grupo,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'fecha_bautismo' => $request->fecha_bautismo,
                'telefono' => $request->telefono,
                'persona_contacto' => $request->persona_contacto,
                'telefono_contacto' => $request->telefono_contacto,
                'sexo' => $request->sexo,
                'servicio' => $request->servicio,
                'nombramiento' => $request->nombramiento,
                'esperanza' => $request->esperanza,
                'estado_espiritual' => $request->estado_espiritual,
                'observacion' => $request->observacion,
            ];

            // Solo actualizar contraseña si se proporciona
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Sincronizar asignaciones (elimina todas las existentes y agrega la nueva selección)
            $asignaciones = $request->has('asignaciones') && is_array($request->asignaciones)
                ? $request->asignaciones
                : [];
            $user->asignaciones()->sync($asignaciones);

            // Recargar el usuario con todas sus relaciones para devolver los datos completos
            $userWithRelations = DB::table('users')
                ->join('perfiles', 'users.perfil', '=', 'perfiles.id')
                ->join('congregaciones', 'users.congregacion', '=', 'congregaciones.id')
                ->join('grupos', 'users.grupo', '=', 'grupos.id')
                ->leftJoin('nombramiento', 'users.nombramiento', '=', 'nombramiento.id')
                ->join('estado_espiritual', 'users.estado_espiritual', '=', 'estado_espiritual.id')
                ->leftJoin('servicios', 'users.servicio', '=', 'servicios.id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.estado',
                    'perfiles.privilegio as privilegio_perfil',
                    'congregaciones.nombre as nombre_congregacion',
                    'grupos.nombre as nombre_grupo',
                    'nombramiento.nombre as nombre_nombramiento',
                    'servicios.nombre as nombre_servicio',
                    'estado_espiritual.nombre as nombre_estado_espiritual'
                )
                ->where('users.id', $user->id)
                ->first();

            // Cargar asignaciones
            $userModel = User::with('asignaciones')->find($user->id);
            $asignacionesData = $userModel ? $userModel->asignaciones : collect();

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente.',
                'user' => $userWithRelations,
                'asignaciones' => $asignacionesData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        try {
            $currentUser = auth()->user();
            $user = User::findOrFail($id);

            // Supervisores son solo lectura: no pueden eliminar usuarios
            if ($currentUser->isSupervisor()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los supervisores no tienen permisos para eliminar usuarios.'
                ], 403);
            }

            // Si es coordinador, verificar que el usuario pertenezca a la misma congregación
            if ($currentUser->isCoordinator() && $user->congregacion != $currentUser->congregacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para eliminar este usuario.'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
                'password' => ['nullable', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).+$/'],
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'name.max' => 'El nombre no puede exceder 255 caracteres.',
                'email.email' => 'El email debe tener un formato válido.',
                'email.max' => 'El email no puede exceder 255 caracteres.',
                'email.unique' => 'Ya existe un usuario con este email.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.regex' => 'La contraseña debe contener al menos una letra y un número.',
                'password.confirmed' => 'La confirmación de contraseña no coincide.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            // Solo actualizar contraseña si se proporciona
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Datos actualizados exitosamente.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar los datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar usuarios a PDF con filtros aplicados.
     */
    public function exportPdf(Request $request)
    {
        try {
            $currentUser = auth()->user();

            // Verificar que el usuario sea administrador (perfil=1)
            if (!$currentUser->isAdmin()) {
                abort(403, 'No tiene permisos para exportar PDF.');
            }

            // Consulta base igual que en el método index
            $query = DB::table('users')
                ->join('perfiles', 'users.perfil', '=', 'perfiles.id')
                ->join('congregaciones', 'users.congregacion', '=', 'congregaciones.id')
                ->join('grupos', 'users.grupo', '=', 'grupos.id')
                ->leftJoin('nombramiento', 'users.nombramiento', '=', 'nombramiento.id')
                ->join('estado_espiritual', 'users.estado_espiritual', '=', 'estado_espiritual.id')
                ->leftJoin('servicios', 'users.servicio', '=', 'servicios.id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.estado',
                    'perfiles.nombre as nombre_perfil',
                    'perfiles.privilegio as privilegio_perfil',
                    'congregaciones.nombre as nombre_congregacion',
                    'grupos.nombre as nombre_grupo',
                    'nombramiento.nombre as nombre_nombramiento',
                    'servicios.nombre as nombre_servicio',
                    'estado_espiritual.nombre as nombre_estado_espiritual'
                );

            // Aplicar filtros si existen
            if ($request->filled('congregacion')) {
                $query->where('congregaciones.nombre', $request->congregacion);
            }

            if ($request->filled('grupo')) {
                $query->where('grupos.nombre', $request->grupo);
            }

            if ($request->filled('nombramiento')) {
                $query->where('nombramiento.nombre', $request->nombramiento);
            }

            if ($request->filled('servicio')) {
                $query->where('servicios.nombre', $request->servicio);
            }

            if ($request->filled('estadoEspiritual')) {
                $query->where('estado_espiritual.nombre', $request->estadoEspiritual);
            }

            if ($request->filled('perfil')) {
                $query->where('perfiles.privilegio', $request->perfil);
            }

            if ($request->filled('estado')) {
                $query->where('users.estado', $request->estado);
            }

            // Ordenar por nombre
            $users = $query->orderBy('users.name')->get();

            // Para usuarios con asignaciones, cargar las asignaciones
            $users = $users->map(function ($user) use ($request) {
                $userModel = User::with('asignaciones')->find($user->id);
                $user->asignaciones = $userModel ? $userModel->asignaciones : collect();

                // Filtrar por asignación si se especifica
                if ($request->filled('asignacion')) {
                    $hasAsignacion = $user->asignaciones->contains('abreviacion', $request->asignacion);
                    return $hasAsignacion ? $user : null;
                }

                return $user;
            })->filter();

            // Preparar datos para la vista PDF
            $data = [
                'users' => $users,
                'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                'generado_por' => $currentUser->name,
                'total_usuarios' => $users->count(),
                'filtros_aplicados' => $this->getFiltrosAplicados($request)
            ];

            // Generar PDF
            $pdf = PDF::loadView('users.pdf', $data);
            $pdf->setPaper('A4', 'landscape');

            // Nombre del archivo
            $filename = 'usuarios_' . now()->format('Y-m-d_H-i-s') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Error generando PDF: ' . $e->getMessage());
            return redirect()->route('users.index')->with('error', 'Error al generar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Obtener descripción de filtros aplicados para mostrar en el PDF.
     */
    private function getFiltrosAplicados(Request $request)
    {
        $filtros = [];

        if ($request->filled('congregacion')) {
            $filtros[] = "Congregación: {$request->congregacion}";
        }

        if ($request->filled('grupo')) {
            $filtros[] = "Grupo: {$request->grupo}";
        }

        if ($request->filled('nombramiento')) {
            $filtros[] = "Nombramiento: {$request->nombramiento}";
        }

        if ($request->filled('servicio')) {
            $filtros[] = "Servicio: {$request->servicio}";
        }

        if ($request->filled('estadoEspiritual')) {
            $filtros[] = "Estado Espiritual: {$request->estadoEspiritual}";
        }

        if ($request->filled('perfil')) {
            $filtros[] = "Perfil: {$request->perfil}";
        }

        if ($request->filled('estado')) {
            $estado = $request->estado == '1' ? 'Activo' : 'Inactivo';
            $filtros[] = "Estado: {$estado}";
        }

        if ($request->filled('asignacion')) {
            $filtros[] = "Asignación: {$request->asignacion}";
        }

        return empty($filtros) ? ['Sin filtros aplicados'] : $filtros;
    }

    /**
     * Obtener historial de participaciones de un usuario en la segunda sección.
     */
    public function getHistorialSegundaSeccion($encargadoId, $parteId)
    {
        //Historial de participaciones del usuario en partes de la segunda sección como estudiante o ayudante
        try {
            // Verificar que el usuario existe
            $usuario = User::findOrFail($encargadoId);
            $partesSeccion = ParteSeccion::findOrFail($parteId);
            // Obtener todas las participaciones del usuario en partes de la segunda sección
            $historial = DB::table('partes_programa as pp')
                ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                //->join('secciones_reunion as sr', 'ps.seccion_id', '=', 'sr.id')
                ->join('programas as prog', 'pp.programa_id', '=', 'prog.id')
                ->join('salas as s', 'pp.sala_id', '=', 's.id')
                ->leftJoin('users as encargado', 'pp.encargado_id', '=', 'encargado.id')
                ->leftJoin('users as ayudante', 'pp.ayudante_id', '=', 'ayudante.id')
                ->where('ps.asignacion_id', $partesSeccion->asignacion_id) // Segunda sección
                ->where(function ($query) use ($encargadoId) {
                    $query->where('pp.encargado_id', $encargadoId)
                        ->orWhere('pp.ayudante_id', $encargadoId);
                })
                ->select(
                    'prog.fecha',
                    'pp.programa_id',
                    's.abreviacion as sala_abreviacion',
                    'ps.abreviacion as parte_abreviacion',
                    DB::raw("CASE WHEN encargado_reemplazado_id IS NOT NULL AND encargado_id={$encargadoId} THEN concat('R-', encargado.name) ELSE encargado.name END as encargado_nombre"),
                    DB::raw("CASE WHEN ayudante_reemplazado_id IS NOT NULL AND ayudante_id={$encargadoId} THEN concat('R-', ayudante.name) ELSE ayudante.name END as ayudante_nombre")
                )
                ->orderBy('prog.fecha', 'desc')
                ->get();

            // Formatear las fechas y nombres con padding
            $historial = $historial->map(function ($item) {
                $item->fecha_formateada = date('d/m/Y', strtotime($item->fecha));

                // Aplicar str_pad con puntos para los nombres (25 caracteres)
                $item->encargado_nombre_formateado = mb_str_pad(substr($item->encargado_nombre, 0, 25), 25, '.', STR_PAD_RIGHT);
                $item->ayudante_nombre_formateado = $item->ayudante_nombre;

                return $item;
            });

            return response()->json([
                'success' => true,
                'historial' => $historial,
                'usuario_nombre' => $usuario->name
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial: ' . $e->getMessage()
            ], 500);
        }
    }
}
