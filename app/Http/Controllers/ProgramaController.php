<?php

namespace App\Http\Controllers;

use App\Models\Programa;
use App\Models\User;
use App\Models\Cancion;
use App\Models\ParteSeccion;
use App\Models\SeccionReunion;
use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Maatwebsite\Excel\Facades\Excel;

class ProgramaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentUser = auth()->user();
        //No permite ver programas si $currentUser->perfil no es 3 (Coordinador), 7 (Organizador), 6 (Subsecretario) o 8 (SubOrganizador)
        if (!($currentUser->isCoordinator() || $currentUser->isOrganizer() || $currentUser->isSubsecretary() || $currentUser->isSuborganizer())) {
            return redirect()->route('home')
                ->with('error', 'No tienes permiso para acceder a esta sección2.');
        }
        // Consulta base con JOIN para obtener los nombres relacionados
        $query = DB::table('programas')
            ->leftJoin('users as orador_inicial', 'programas.orador_inicial', '=', 'orador_inicial.id')
            ->leftJoin('users as presidencia', 'programas.presidencia', '=', 'presidencia.id')
            ->leftJoin('users as orador_final', 'programas.orador_final', '=', 'orador_final.id')
            ->leftJoin('canciones as cancion_pre', 'programas.cancion_pre', '=', 'cancion_pre.id')
            ->leftJoin('canciones as cancion_en', 'programas.cancion_en', '=', 'cancion_en.id')
            ->leftJoin('canciones as cancion_post', 'programas.cancion_post', '=', 'cancion_post.id')
            ->leftJoin('users as creador', 'programas.creador', '=', 'creador.id')
            ->leftJoin('users as modificador', 'programas.modificador', '=', 'modificador.id')
             ->where('creador.congregacion', $currentUser->congregacion)
            ->select(
                'programas.id',
                'programas.fecha',
                'programas.estado',
                'orador_inicial.name as nombre_orador_inicial',
                'presidencia.name as nombre_presidencia',
                'orador_final.name as nombre_orador_final',
                'cancion_pre.nombre as nombre_cancion_pre',
                'cancion_pre.numero as numero_cancion_pre',
                'cancion_en.nombre as nombre_cancion_en',
                'cancion_en.numero as numero_cancion_en',
                'cancion_post.nombre as nombre_cancion_post',
                'cancion_post.numero as numero_cancion_post',
                'creador.name as creado_por_nombre',
                'modificador.name as modificado_por_nombre',
                'programas.created_at',
                'programas.updated_at'
            );

        $programas = $query->orderBy('programas.fecha', 'desc')->get();

        // Obtener datos para los selects
        $usuarios = User::where('estado', true)
            ->orderBy('name')
            ->get();

        // Para coordinadores (perfil=3) y organizadores (perfil=7), obtener usuarios especiales para presidencia y orador inicial
        $usuariosPresidencia = [];
        $usuariosOradorInicial = [];

        // Usuarios para Presidencia (asignacion_id=1)
        $usuariosPresidencia = DB::table('users as u')
            ->join('asignaciones_users as au', 'u.id', '=', 'au.user_id')
            ->leftJoin(DB::raw('(
                SELECT presidencia as user_id, MAX(fecha) as ultima_fecha
                FROM programas
                WHERE presidencia IS NOT NULL
                GROUP BY presidencia
            ) as ultima_presidencia'), 'u.id', '=', 'ultima_presidencia.user_id')
            ->where('au.asignacion_id', 1)
            ->where('u.congregacion', $currentUser->congregacion)
            ->where('u.estado', 1)
            ->select(
                'u.id',
                'u.name',
                'ultima_presidencia.ultima_fecha'
            )
            ->orderByRaw('ultima_presidencia.ultima_fecha IS NULL DESC, ultima_presidencia.ultima_fecha ASC, u.name ASC')
            ->get();

        // Usuarios para Orador Inicial (asignacion_id=23)
        $usuariosOradorInicial = DB::table('users as u')
            ->join('asignaciones_users as au', 'u.id', '=', 'au.user_id')
            ->leftJoin(DB::raw('(
                SELECT user_id, MAX(fecha) as ultima_fecha
                FROM (
                    SELECT orador_inicial as user_id, fecha
                    FROM programas
                    WHERE orador_inicial IS NOT NULL
                    UNION ALL
                    SELECT orador_final as user_id, fecha
                    FROM programas
                    WHERE orador_final IS NOT NULL
                ) as oradores
                GROUP BY user_id
            ) as ultima_oracion'), 'u.id', '=', 'ultima_oracion.user_id')
            ->where('au.asignacion_id', 23)
            ->where('u.congregacion', $currentUser->congregacion)
            ->where('u.estado', 1)
            ->select(
                'u.id',
                'u.name',
                'ultima_oracion.ultima_fecha'
            )
            ->orderByRaw('ultima_oracion.ultima_fecha IS NULL DESC, ultima_oracion.ultima_fecha ASC, u.name ASC')
            ->get();

        $canciones = Cancion::where('estado', true)
            ->orderBy('numero')
            ->get();

        return view('programas.index', compact('programas', 'usuarios', 'canciones', 'usuariosPresidencia', 'usuariosOradorInicial', 'currentUser'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $programa = Programa::with(['oradorInicial', 'presidenciaUsuario', 'oradorFinal'])->findOrFail($id);
            $currentUser = auth()->user();
            //No permite ver programas si $currentUser->perfil no es 3 (Coordinador) o 7 (Organizador)
            if (!($currentUser->isCoordinator() || $currentUser->isOrganizer())) {
                return redirect()->route('home')
                    ->with('error', 'No tienes permiso para acceder a esta sección.');
            }
            // Obtener datos para los selects del formulario principal
            $usuarios = User::where('estado', true)
                ->orderBy('name')
                ->get();

            $canciones = Cancion::where('estado', true)
                ->orderBy('numero')
                ->get();

            // Obtener salas activas
            $salas = Sala::activas()
                ->orderBy('id')
                ->get();

            // Para coordinadores (perfil=3) y organizadores (perfil=7), obtener usuarios especiales para presidencia y orador inicial
            $usuariosPresidencia = [];
            $usuariosOradorInicial = [];

            // Usuarios para Presidencia (asignacion_id=1)
            $usuariosPresidencia = DB::table('users as u')
                ->join('asignaciones_users as au', 'u.id', '=', 'au.user_id')
                ->leftJoin(DB::raw('(
                    SELECT presidencia as user_id, MAX(fecha) as ultima_fecha
                    FROM programas
                    WHERE presidencia IS NOT NULL
                    GROUP BY presidencia
                ) as ultima_presidencia'), 'u.id', '=', 'ultima_presidencia.user_id')
                ->where('au.asignacion_id', 1)
                ->where('u.congregacion', $currentUser->congregacion)
                ->where('u.estado', 1)
                ->select(
                    'u.id',
                    'u.name',
                    'ultima_presidencia.ultima_fecha'
                )
                ->orderByRaw('ultima_presidencia.ultima_fecha IS NULL DESC, ultima_presidencia.ultima_fecha ASC, u.name ASC')
                ->get();

            // Usuarios para Orador Inicial (asignacion_id=23)
            $usuariosOradorInicial = DB::table('users as u')
                ->join('asignaciones_users as au', 'u.id', '=', 'au.user_id')
                ->leftJoin(DB::raw('(
                    SELECT user_id, MAX(fecha) as ultima_fecha
                    FROM (
                        SELECT orador_inicial as user_id, fecha
                        FROM programas
                        WHERE orador_inicial IS NOT NULL
                        UNION ALL
                        SELECT orador_final as user_id, fecha
                        FROM programas
                        WHERE orador_final IS NOT NULL
                    ) as oradores
                    GROUP BY user_id
                ) as ultima_oracion'), 'u.id', '=', 'ultima_oracion.user_id')
                ->where('au.asignacion_id', 23)
                ->where('u.congregacion', $currentUser->congregacion)
                ->where('u.estado', 1)
                ->select(
                    'u.id',
                    'u.name',
                    'ultima_oracion.ultima_fecha'
                )
                ->orderByRaw('ultima_oracion.ultima_fecha IS NULL DESC, ultima_oracion.ultima_fecha ASC, u.name ASC')
                ->get();

            // Obtener la sección de reunión con id=1 para el título
            $seccionReunion = SeccionReunion::find(1);

            // Obtener programa anterior (fecha anterior más cercana)
            $programaAnterior = DB::table('programas')
                ->join('users as creador', 'programas.creador', '=', 'creador.id')
                ->where('creador.congregacion', $currentUser->congregacion)
                ->where('programas.fecha', '<', $programa->fecha)
                ->orderBy('programas.fecha', 'desc')
                ->select('programas.id')
                ->first();

            // Obtener programa posterior (fecha posterior más cercana)
            $programaPosterior = DB::table('programas')
                ->join('users as creador', 'programas.creador', '=', 'creador.id')
                ->where('creador.congregacion', $currentUser->congregacion)
                ->where('programas.fecha', '>', $programa->fecha)
                ->orderBy('programas.fecha', 'asc')
                ->select('programas.id')
                ->first();

            return view('programas.edit', compact('programa', 'usuarios', 'canciones', 'seccionReunion', 'usuariosPresidencia', 'usuariosOradorInicial', 'salas', 'currentUser', 'programaAnterior', 'programaPosterior'));
        } catch (\Exception $e) {
            return redirect()->route('programas.index')
                ->with('error', 'Programa no encontrado.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $programa = Programa::with(['oradorInicial', 'presidenciaUsuario', 'oradorFinal', 'cancionPre', 'cancionEn', 'cancionPost'])->findOrFail($id);
            $currentUser = auth()->user();

            // Obtener la sección de reunión con id=1 para el título
            $seccionReunion = SeccionReunion::find(1);

            // Obtener programa anterior (fecha anterior más cercana)
            $programaAnterior = DB::table('programas')
                ->join('users as creador', 'programas.creador', '=', 'creador.id')
                ->where('creador.congregacion', $currentUser->congregacion)
                ->where('programas.fecha', '<', $programa->fecha)
                ->orderBy('programas.fecha', 'desc')
                ->select('programas.id')
                ->first();

            // Obtener programa posterior (fecha posterior más cercana)
            $programaPosterior = DB::table('programas')
                ->join('users as creador', 'programas.creador', '=', 'creador.id')
                ->where('creador.congregacion', $currentUser->congregacion)
                ->where('programas.fecha', '>', $programa->fecha)
                ->orderBy('programas.fecha', 'asc')
                ->select('programas.id')
                ->first();

            return view('programas.show', compact('programa', 'seccionReunion', 'currentUser', 'programaAnterior', 'programaPosterior'));
        } catch (\Exception $e) {
            return redirect()->route('programas.index')
                ->with('error', 'Programa no encontrado.');
        }
    }

    /**
     * Obtener usuarios con asignación de oración para orador inicial
     */
    public function getUsuariosOradorInicial()
    {
        try {
            $currentUser = auth()->user();

            // Obtener usuarios con asignación_id=23 (oración) de la misma congregación
            $usuarios = DB::table('users as u')
                ->join('asignaciones_users as au', 'u.id', '=', 'au.user_id')
                ->leftJoin(DB::raw('(
                    SELECT user_id, MAX(fecha) as ultima_fecha
                    FROM (
                        SELECT orador_inicial as user_id, fecha
                        FROM programas
                        WHERE orador_inicial IS NOT NULL
                        UNION ALL
                        SELECT orador_final as user_id, fecha
                        FROM programas
                        WHERE orador_final IS NOT NULL
                    ) as oradores
                    GROUP BY user_id
                ) as ultima_oracion'), 'u.id', '=', 'ultima_oracion.user_id')
                ->where('au.asignacion_id', 23)
                ->where('u.congregacion', $currentUser->congregacion)
                ->where('u.estado', 1)
                ->select(
                    'u.id',
                    'u.name',
                    'ultima_oracion.ultima_fecha'
                )
                ->orderByRaw('ultima_oracion.ultima_fecha IS NULL DESC, ultima_oracion.ultima_fecha ASC, u.name ASC')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $usuarios
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios para orador inicial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de participaciones como orador de un usuario específico
     */
    public function getHistorialOrador($usuarioId)
    {
        try {
            // Obtener todas las participaciones del usuario como orador inicial u orador final
            $participaciones = DB::table('programas')
                ->join('users', function($join) use ($usuarioId) {
                    $join->where(function($query) use ($usuarioId) {
                        $query->on('programas.orador_inicial', '=', 'users.id')
                              ->where('users.id', $usuarioId);
                    })->orWhere(function($query) use ($usuarioId) {
                        $query->on('programas.orador_final', '=', 'users.id')
                              ->where('users.id', $usuarioId);
                    });
                })
                ->where(function($query) use ($usuarioId) {
                    $query->where('programas.orador_inicial', $usuarioId)
                          ->orWhere('programas.orador_final', $usuarioId);
                })
                ->select(
                    'programas.id as programa_id',
                    'programas.fecha',
                    'users.name as nombre_usuario',
                    DB::raw('CASE
                        WHEN programas.orador_inicial = ' . $usuarioId . ' THEN "inicial"
                        WHEN programas.orador_final = ' . $usuarioId . ' THEN "final"
                        ELSE "desconocido"
                    END as tipo')
                )
                ->orderBy('programas.fecha', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $participaciones
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial del orador: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuarios con asignación de presidencia
     */
    public function getUsuariosPresidencia()
    {
        try {
            $currentUser = auth()->user();

            // Obtener usuarios con asignación_id=1 (presidencia) de la misma congregación
            $usuarios = DB::table('users as u')
                ->join('asignaciones_users as au', 'u.id', '=', 'au.user_id')
                ->leftJoin(DB::raw('(
                    SELECT presidencia as user_id, MAX(fecha) as ultima_fecha
                    FROM programas
                    WHERE presidencia IS NOT NULL
                    GROUP BY presidencia
                ) as ultima_presidencia'), 'u.id', '=', 'ultima_presidencia.user_id')
                ->where('au.asignacion_id', 1)
                ->where('u.congregacion', $currentUser->congregacion)
                ->where('u.estado', 1)
                ->select(
                    'u.id',
                    'u.name',
                    'ultima_presidencia.ultima_fecha'
                )
                ->orderByRaw('ultima_presidencia.ultima_fecha IS NULL DESC, ultima_presidencia.ultima_fecha ASC, u.name ASC')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $usuarios
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios para presidencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de participaciones como presidente de un usuario específico
     */
    public function getHistorialPresidencia($usuarioId)
    {
        try {
            // Obtener todas las participaciones del usuario como presidente
            $participaciones = DB::table('programas')
                ->join('users', 'programas.presidencia', '=', 'users.id')
                ->where('programas.presidencia', $usuarioId)
                ->select(
                    'programas.id as programa_id',
                    'programas.fecha',
                    'users.name as nombre_usuario'
                )
                ->orderBy('programas.fecha', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $participaciones
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial de presidencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todas las canciones disponibles
     */
    public function getCancionesDisponibles()
    {
        try {
            // Obtener todas las canciones activas ordenadas por número
            $canciones = DB::table('canciones')
                ->where('estado', 1)
                ->select('id', 'numero', 'nombre')
                ->orderBy('numero')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $canciones
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener canciones disponibles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener partes de programa para la segunda sección (sala principal)
     */
    public function getPartesSMM($programaId)
    {
        try {
            $partes = DB::table('partes_programa as pp')
                ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                ->leftJoin('users as u', 'pp.encargado_id', '=', 'u.id')
                ->leftJoin('users as u_ayudante', 'pp.ayudante_id', '=', 'u_ayudante.id')
                ->leftJoin('salas as s', 'pp.sala_id', '=', 's.id')
                ->where('pp.programa_id', $programaId)
                ->where('ps.seccion_id', 2)
                ->select(
                    'pp.id',
                    'ps.nombre as parte_nombre',
                    'ps.abreviacion as parte_abreviacion',
                    'pp.tiempo',
                    'pp.encargado_id',
                    'pp.ayudante_id',
                    'u.name as encargado_nombre',
                    'u_ayudante.name as ayudante_nombre',
                    's.nombre as sala_nombre',
                    's.abreviacion as sala_abreviacion',
                    'pp.leccion',
                    'pp.orden',
                    'pp.sala_id',
                    DB::raw('(row_number() OVER (PARTITION BY pp.sala_id ORDER BY pp.sala_id, pp.orden))+3 as numero')
                )
                ->get();

            // Agregar información sobre posición (primero/último) para cada parte
            $partesCollection = collect($partes);
            $partesCollection = $partesCollection
                ->groupBy('sala_id') // agrupamos por sala_id
                ->flatMap(function ($grupo) {
                    // $grupo es la subcolección de una sala_id
                    $total = $grupo->count();

                    return $grupo->values()->map(function ($parte, $index) use ($total) {
                        $parte->es_primero = $index === 0;
                        $parte->es_ultimo  = $index === ($total - 1);
                        return $parte;
                    });
                });

            return response()->json([
                'success' => true,
                'data' => $partesCollection->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las partes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todas las partes de sección activas para la segunda sección
     */
    public function getPartesSegundaSeccionDisponibles($programaId, Request $request)
    {
        try {
            $includeSelected = $request->get('include_selected');

            // Obtener TODAS las partes de la segunda sección activas
            $query = DB::table('partes_seccion')
                ->where('seccion_id', 2)
                ->where(function($q) use ($includeSelected) {
                    $q->where('estado', 1);
                    // Si se especifica include_selected, incluir también esa parte específica aunque no esté activa
                    if ($includeSelected) {
                        $q->orWhere('id', $includeSelected);
                    }
                })
                ->orderBy('orden')
                ->select('id', 'nombre', 'abreviacion', 'tiempo', 'tipo');

            $partesDisponibles = $query->get();

            return response()->json([
                'success' => true,
                'data' => $partesDisponibles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las partes disponibles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();
        //No permite crear programas si $currentUser->perfil no es 3 (Coordinador) o 7 (Organizador)
        if (!($currentUser->isCoordinator() || $currentUser->isOrganizer())) {
            return redirect()->route('home')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'orador_inicial' => 'nullable|exists:users,id',
            'presidencia' => 'nullable|exists:users,id',
            'cancion_pre' => 'nullable|exists:canciones,id',
            'cancion_en' => 'nullable|exists:canciones,id',
            'cancion_post' => 'nullable|exists:canciones,id',
            'orador_final' => 'nullable|exists:users,id',
            'estado' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $programa = new Programa();
            $programa->fecha = $request->fecha;
            $programa->orador_inicial = $request->orador_inicial;
            $programa->presidencia = $request->presidencia;
            $programa->cancion_pre = $request->cancion_pre;
            $programa->cancion_en = $request->cancion_en;
            $programa->cancion_post = $request->cancion_post;
            $programa->orador_final = $request->orador_final;
            $programa->estado = $request->estado;
            $programa->creador = Auth::id();
            $programa->modificador = Auth::id();
            $programa->save();

            return response()->json([
                'success' => true,
                'message' => 'Programa creado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el programa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $currentUser = auth()->user();
        //No permite actualizar programas si $currentUser->perfil no es 3 (Coordinador) o 7 (Organizador)
        if (!($currentUser->isCoordinator() || $currentUser->isOrganizer())) {
            return redirect()->route('home')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'orador_inicial' => 'nullable|exists:users,id',
            'presidencia' => 'nullable|exists:users,id',
            'cancion_pre' => 'nullable|exists:canciones,id',
            'cancion_en' => 'nullable|exists:canciones,id',
            'cancion_post' => 'nullable|exists:canciones,id',
            'orador_final' => 'nullable|exists:users,id',
            'estado' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $programa = Programa::findOrFail($id);
            $programa->fecha = $request->fecha;
            $programa->orador_inicial = $request->orador_inicial;
            $programa->presidencia = $request->presidencia;
            $programa->cancion_pre = $request->cancion_pre;
            $programa->cancion_en = $request->cancion_en;
            $programa->cancion_post = $request->cancion_post;
            $programa->orador_final = $request->orador_final;
            $programa->estado = $request->estado;
            $programa->modificador = Auth::id();
            $programa->save();

            return response()->json([
                'success' => true,
                'message' => 'Programa actualizado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el programa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $currentUser = auth()->user();
        //No permite eliminar programas si $currentUser->perfil no es 3 (Coordinador) o 7 (Organizador)
        if (!($currentUser->isCoordinator() || $currentUser->isOrganizer())) {
            return redirect()->route('home')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        try {
            $programa = Programa::findOrFail($id);
            $programa->delete();

            return response()->json([
                'success' => true,
                'message' => 'Programa eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el programa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener años disponibles de programas
     */
    public function getAniosDisponibles()
    {
        try {
            // Cambiar strftime (SQLite) por YEAR (MySQL)
            $anios = DB::table('programas')
                ->selectRaw('DISTINCT YEAR(fecha) as anio')
                ->whereNotNull('fecha')
                ->orderBy('anio', 'desc')
                ->pluck('anio')
                ->toArray();

            return response()->json([
                'success' => true,
                'anios' => $anios
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener años disponibles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener meses disponibles para un año específico
     */
    public function getMesesDisponibles($anio)
    {
        try {
            // Obtener todas las fechas del año especificado y extraer los meses únicos
            $fechas = DB::table('programas')
                ->select('fecha')
                ->whereNotNull('fecha')
                ->where('fecha', 'like', $anio . '-%')
                ->get();

            $mesesUnicos = [];
            foreach ($fechas as $fecha) {
                $mes = date('m', strtotime($fecha->fecha));
                if (!in_array($mes, $mesesUnicos)) {
                    $mesesUnicos[] = $mes;
                }
            }

            sort($mesesUnicos);

            $mesesNombres = [
                '01' => 'Enero',
                '02' => 'Febrero',
                '03' => 'Marzo',
                '04' => 'Abril',
                '05' => 'Mayo',
                '06' => 'Junio',
                '07' => 'Julio',
                '08' => 'Agosto',
                '09' => 'Septiembre',
                '10' => 'Octubre',
                '11' => 'Noviembre',
                '12' => 'Diciembre'
            ];

            $meses = array_map(function ($mes) use ($mesesNombres) {
                return [
                    'mes' => $mes,
                    'nombre' => $mesesNombres[$mes] ?? $mes,
                    'numero_mes' => $mes
                ];
            }, $mesesUnicos);

            return response()->json([
                'success' => true,
                'meses' => $meses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener meses disponibles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar programas a PDF (para coordinadores - perfil 3 y organizadores - perfil 7)
     */
    public function exportPdf(Request $request)
    {
        try {
            $currentUser = Auth::user();

            // Obtener parámetros de filtro
            $anio = $request->get('anio');
            $meses = $request->get('mes'); // Ahora puede ser un array

            // Si meses es un string, convertirlo a array para consistencia
            if (is_string($meses)) {
                $meses = [$meses];
            }

            // Consulta base de programas
            $query = DB::table('programas as p')
                ->Join('users as creador', 'p.creador', '=', 'creador.id')
                ->leftjoin('users as presidente', 'p.presidencia', '=', 'presidente.id')
                ->leftJoin('users as orador_inicial', 'p.orador_inicial', '=', 'orador_inicial.id')
                ->leftJoin('users as orador_final', 'p.orador_final', '=', 'orador_final.id')
                ->leftJoin('congregaciones as c', 'presidente.congregacion', '=', 'c.id')
                ->where('creador.congregacion', $currentUser->congregacion);

            // Aplicar filtros de fecha si se proporcionan
            if ($anio) {
                // Cambiar whereYear (SQLite) por whereRaw con YEAR (MySQL)
                $query->whereRaw('YEAR(p.fecha) = ?', [$anio]);

                // Si hay meses específicos seleccionados, filtrar por ellos
                if ($meses && is_array($meses) && !empty($meses)) {
                    $query->where(function($q) use ($meses) {
                        foreach ($meses as $mes) {
                            // Cambiar whereMonth (SQLite) por whereRaw con MONTH (MySQL)
                            $q->orWhereRaw('MONTH(p.fecha) = ?', [$mes]);
                        }
                    });
                }
            }

            $programas = $query->select(
                    'p.id',
                    'p.fecha',
                    'presidente.name as nombre_presidencia',
                    'orador_inicial.name as nombre_orador_inicial',
                    'orador_final.name as nombre_orador_final',
                    'c.nombre as congregacion_nombre',
                    'p.cancion_pre',
                    'p.cancion_en',
                    'p.cancion_post'
                )
                ->orderBy('p.fecha', 'asc') // Orden ascendente para mostrar programas cronológicamente
                ->get();

            // Para cada programa, obtener sus partes con temas
            foreach ($programas as &$programa) {
                $programa->partes = DB::table('partes_programa as pp')
                    ->leftJoin('users as encargado', 'pp.encargado_id', '=', 'encargado.id')
                    ->leftJoin('users as ayudante', 'pp.ayudante_id', '=', 'ayudante.id')
                    ->leftJoin('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                    ->where('pp.programa_id', $programa->id)
                    ->select(
                        'pp.tema',
                        'pp.tiempo',
                        'pp.sala_id',
                        'ps.nombre as parte_nombre',
                        'encargado.name as encargado_nombre',
                        'ayudante.name as ayudante_nombre',
                        'pp.parte_id',
                        'pp.orden',
                        'ps.seccion_id'
                    )
                    ->orderBy('ps.seccion_id', 'asc')
                    ->orderBy('pp.sala_id', 'asc')
                    ->orderBy('pp.orden', 'asc')
                    ->get();
            }

            // Verificar si hay programas para exportar
            if ($programas->isEmpty()) {
                return redirect()->route('programas.index')
                    ->with('error', 'No hay programas disponibles para el período seleccionado.');
            }

            // Obtener nombre de la congregación
            $congregacionNombre = $programas->first()->congregacion_nombre ?? 'Sin nombre';

            // Preparar nombre del archivo
            $fileName = 'programaMensual';
            if ($anio && $meses && is_array($meses) && !empty($meses)) {
                // Si hay múltiples meses, usar el primer mes para el nombre del archivo
                $primerMes = min($meses); // Usar el mes más pequeño
                $fileName .= '_' . $anio . '_' . str_pad($primerMes, 2, '0', STR_PAD_LEFT);

                // Si hay más de un mes, agregar indicador
                if (count($meses) > 1) {
                    $fileName .= '_multiple';
                }
            } else {
                $fileName .= '_' . date('Y-m-d');
            }

            // Crear PDF usando la vista blade
            $pdf = PDF::loadView('programas.pdf', compact('programas', 'congregacionNombre'));
            $pdf->setPaper('letter', 'portrait'); // Cambiar a carta (letter) como se solicitó

            return $pdf->download($fileName . '.pdf');

        } catch (\Throwable $e) {
            \Log::error('❌ Error en exportación PDF:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('programas.index')
                ->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Exportar programas a XLS con el mismo formato que el PDF
     */
    public function exportProgramaXls(Request $request)
    {
        try {
            $currentUser = Auth::user();

            // Obtener parámetros de filtro
            $anio = $request->get('anio');
            $meses = $request->get('mes'); // Ahora puede ser un array

            // Si meses es un string, convertirlo a array para consistencia
            if (is_string($meses)) {
                $meses = [$meses];
            }

            // Consulta base de programas
            $query = DB::table('programas as p')
                ->Join('users as creador', 'p.creador', '=', 'creador.id')
                ->leftjoin('users as presidente', 'p.presidencia', '=', 'presidente.id')
                ->leftJoin('users as orador_inicial', 'p.orador_inicial', '=', 'orador_inicial.id')
                ->leftJoin('users as orador_final', 'p.orador_final', '=', 'orador_final.id')
                ->leftJoin('congregaciones as c', 'presidente.congregacion', '=', 'c.id')
                ->where('creador.congregacion', $currentUser->congregacion);

            // Aplicar filtros de fecha si se proporcionan
            if ($anio) {
                $query->whereRaw('YEAR(p.fecha) = ?', [$anio]);

                if ($meses && is_array($meses) && !empty($meses)) {
                    $query->where(function($q) use ($meses) {
                        foreach ($meses as $mes) {
                            $q->orWhereRaw('MONTH(p.fecha) = ?', [$mes]);
                        }
                    });
                }
            }

            $programas = $query->select(
                    'p.id',
                    'p.fecha',
                    'presidente.name as nombre_presidencia',
                    'orador_inicial.name as nombre_orador_inicial',
                    'orador_final.name as nombre_orador_final',
                    'c.nombre as congregacion_nombre',
                    'p.cancion_pre',
                    'p.cancion_en',
                    'p.cancion_post'
                )
                ->orderBy('p.fecha', 'asc')
                ->get();

            // Para cada programa, obtener sus partes con temas
            foreach ($programas as &$programa) {
                $programa->partes = DB::table('partes_programa as pp')
                    ->leftJoin('users as encargado', 'pp.encargado_id', '=', 'encargado.id')
                    ->leftJoin('users as ayudante', 'pp.ayudante_id', '=', 'ayudante.id')
                    ->leftJoin('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                    ->where('pp.programa_id', $programa->id)
                    ->select(
                        'pp.tema',
                        'pp.tiempo',
                        'pp.sala_id',
                        'ps.nombre as parte_nombre',
                        'encargado.name as encargado_nombre',
                        'ayudante.name as ayudante_nombre',
                        'pp.parte_id',
                        'pp.orden',
                        'ps.seccion_id'
                    )
                    ->orderBy('ps.seccion_id', 'asc')
                    ->orderBy('pp.sala_id', 'asc')
                    ->orderBy('pp.orden', 'asc')
                    ->get();
            }

            // Verificar si hay programas para exportar
            if ($programas->isEmpty()) {
                return redirect()->route('programas.index')
                    ->with('error', 'No hay programas disponibles para el período seleccionado.');
            }

            // Obtener nombre de la congregación
            $congregacionNombre = $programas->first()->congregacion_nombre ?? 'Sin nombre';

            // Preparar nombre del archivo
            $fileName = 'programaMensual';
            if ($anio && $meses && is_array($meses) && !empty($meses)) {
                $primerMes = min($meses);
                $fileName .= '_' . $anio . '_' . str_pad($primerMes, 2, '0', STR_PAD_LEFT);

                if (count($meses) > 1) {
                    $fileName .= '_multiple';
                }
            } else {
                $fileName .= '_' . date('Y-m-d');
            }

            // Crear el archivo Excel
            return Excel::download(new \App\Exports\ProgramaExport($programas, $congregacionNombre), $fileName . '.xlsx');

        } catch (\Throwable $e) {
            \Log::error('❌ Error en exportación Programa XLS:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('programas.index')
                ->with('error', 'Error al generar el archivo Excel: ' . $e->getMessage());
        }
    }

    /**
     * Exportar programas a XLS (para coordinadores - perfil 3 y organizadores - perfil 7)
     */
    public function exportXls(Request $request)
    {
        try {
            $currentUser = Auth::user();

            // Obtener parámetros de filtro
            $anio = $request->get('anio');
            $meses = $request->get('mes'); // Ahora puede ser un array

            // Si meses es un string, convertirlo a array para consistencia
            if (is_string($meses)) {
                $meses = [$meses];
            }else {
                $mesesSQL = implode(',', $meses);
            }

            // Consulta para obtener los programas con sus partes
            $query = DB::table('programas as p')
                ->Join('users as creador', 'p.creador', '=', 'creador.id')
                ->leftJoin('users as presidente', 'p.presidencia', '=', 'presidente.id')
                ->leftJoin('congregaciones as c', 'presidente.congregacion', '=', 'c.id')
                ->leftJoinSub("SELECT usuario,count(usuario) AS contador 
                    FROM
                        programas p
                        INNER JOIN 
                        (
                        SELECT presidencia AS usuario,id AS programa_id FROM programas
                        UNION 
                        SELECT encargado_id AS usuario,programa_id FROM partes_programa
                        UNION 
                        SELECT ayudante_id AS usuario,programa_id FROM partes_programa
                        ) usuarios ON usuarios.programa_id=p.id
                    WHERE YEAR(p.fecha) = {$anio} AND MONTH(p.fecha) IN ($mesesSQL) AND usuario IS NOT null
                    GROUP BY usuario ", 'posts_count1', function ($join) {
                        $join->on('presidente.id', '=', 'posts_count1.usuario');
                    })
                ->where('creador.congregacion', $currentUser->congregacion)
                ->whereNotNull('p.fecha');

            // Aplicar filtros de fecha si se proporcionan
            if ($anio) {
                // Cambiar whereYear (SQLite) por whereRaw con YEAR (MySQL)
                $query->whereRaw('YEAR(p.fecha) = ?', [$anio]);

                // Si hay meses específicos seleccionados, filtrar por ellos
                if ($meses && is_array($meses) && !empty($meses)) {
                    $query->where(function($q) use ($meses) {
                        foreach ($meses as $mes) {
                            // Cambiar whereMonth (SQLite) por whereRaw con MONTH (MySQL)
                            $q->orWhereRaw('MONTH(p.fecha) = ?', [$mes]);
                        }
                    });
                }
            }

            $programas = $query->select(
                    'p.id',
                    'p.fecha',
                    'p.presidencia',
                    'c.nombre as congregacion_nombre',
                    'presidente.name as presidente_nombre',
                    'posts_count1.contador'
                )
                ->orderBy('p.fecha', 'asc')
                ->get();

            // Agregar las partes a cada programa
            foreach ($programas as &$programa) {
                $programa->partes = DB::table('partes_programa as pp')
                    ->leftJoin('users as encargado', 'pp.encargado_id', '=', 'encargado.id')
                    ->leftJoin('users as ayudante', 'pp.ayudante_id', '=', 'ayudante.id')
                    ->leftJoin('users as encargadoreemplazado', 'pp.encargado_reemplazado_id', '=', 'encargadoreemplazado.id')
                    ->leftJoin('users as ayudantereemplazado', 'pp.ayudante_reemplazado_id', '=', 'ayudantereemplazado.id')
                    ->leftJoin('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                    ->leftJoinSub("SELECT usuario,count(usuario) AS contador 
                        FROM
                            programas p
                            INNER JOIN 
                            (
                            SELECT presidencia AS usuario,id AS programa_id FROM programas
                            UNION 
                            SELECT encargado_id AS usuario,programa_id FROM partes_programa
                            UNION 
                            SELECT ayudante_id AS usuario,programa_id FROM partes_programa
                            ) usuarios ON usuarios.programa_id=p.id
                        WHERE YEAR(p.fecha) = {$anio} AND MONTH(p.fecha) IN ($mesesSQL) AND usuario IS NOT null
                        GROUP BY usuario ", 'posts_count2', function ($join) {
                            $join->on('encargado.id', '=', 'posts_count2.usuario');
                        })
                    ->leftJoinSub("SELECT usuario,count(usuario) AS contador 
                        FROM
                            programas p
                            INNER JOIN 
                            (
                            SELECT presidencia AS usuario,id AS programa_id FROM programas
                            UNION 
                            SELECT encargado_id AS usuario,programa_id FROM partes_programa
                            UNION 
                            SELECT ayudante_id AS usuario,programa_id FROM partes_programa
                            ) usuarios ON usuarios.programa_id=p.id
                        WHERE YEAR(p.fecha) = {$anio} AND MONTH(p.fecha) IN ($mesesSQL) AND usuario IS NOT null
                        GROUP BY usuario ", 'posts_count3', function ($join) {
                            $join->on('ayudante.id', '=', 'posts_count3.usuario');
                        })
                    ->where('pp.programa_id', $programa->id)
                    ->select(
                        'pp.tema',
                        'pp.tiempo',
                        'ps.abreviacion as parte_abreviacion',
                        'encargado.name as nombre_encargado',
                        'ayudante.name as nombre_ayudante',
                        'encargadoreemplazado.name as nombre_encargado_reemplazado',
                        'ayudantereemplazado.name as nombre_ayudante_reemplazado',
                        'pp.sala_id',
                        'ps.seccion_id',
                        DB::raw('CASE WHEN pp.parte_id = 3 THEN 99 ELSE pp.orden END as orden'),
                        'posts_count2.contador as contador_encargado',
                        'posts_count3.contador as contador_ayudante')
                    ->orderBy('ps.seccion_id', 'asc')
                    ->orderBy('pp.sala_id', 'asc')
                    ->orderBy('pp.orden', 'asc')
                    ->get();
            }

            // Verificar si hay programas para exportar
            if ($programas->isEmpty()) {
                return redirect()->route('programas.index')
                    ->with('error', 'No hay programas disponibles para el período seleccionado.');
            }

            // Preparar nombre del archivo
            $fileName = 'programaResumido';
            if ($anio && $meses && is_array($meses) && !empty($meses)) {
                // Si hay múltiples meses, usar el primer mes para el nombre del archivo
                $primerMes = min($meses); // Usar el mes más pequeño
                $fileName .= '_' . $anio . '_' . str_pad($primerMes, 2, '0', STR_PAD_LEFT);

                // Si hay más de un mes, agregar indicador
                if (count($meses) > 1) {
                    $fileName .= '_multiple';
                }
            } else {
                $fileName .= '_' . date('Y-m-d');
            }

            // Crear Excel usando Laravel Excel
            return Excel::download(new class($programas) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                private $programas;
                private $rowStyles = [];

                public function __construct($programas)
                {
                    $this->programas = $programas;
                }

                public function collection()
                {
                    $data = collect();
                    $rowIndex = 2; // Empezar desde la fila 2 (después del header)

                    foreach ($this->programas as $programaIndex => $programa) {
                        // Determinar el color de fondo para este programa
                        $backgroundColor = $this->getProgramBackgroundColor($programaIndex);

                        //Agregar fila para el presidente
                        $data->push([
                            //Fecha en formato dd-mm-AAAA
                            'Fecha' => \Carbon\Carbon::parse($programa->fecha)->locale('es')->translatedFormat('d-m-Y'),
                            'Parte' => 'PD',
                            'Nombre' => $programa->presidente_nombre ? $programa->presidente_nombre: 'N/A',
                            'Participaciones' => '('.$programa->contador.')',
                            'Rol' => 'Presidente',
                            'Sala' => '1',
                            'Tiempo' => '',
                        ]);

                        // Aplicar estilo a la fila del presidente
                        $this->rowStyles[$rowIndex] = [
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $backgroundColor]
                            ]
                        ];
                        $rowIndex++;

                        foreach ($programa->partes as $parte) {
                            // Agregar fila para el encargado (Estudiante)
                            $data->push([
                                //Fecha en formato dd-mm-AAAA
                                'Fecha' => \Carbon\Carbon::parse($programa->fecha)->locale('es')->translatedFormat('d-m-Y'),
                                'Parte' => $parte->parte_abreviacion ?: 'N/A',
                                'Nombre' => $parte->nombre_encargado ? $parte->nombre_encargado : 'N/A',
                                'Participaciones' => '('.$parte->contador_encargado.')',
                                'Rol' => $parte->seccion_id == 2 ? 'Estudiante' : 'Encargado',
                                'Sala' => $parte->sala_id ?: '',
                                'Reemplazado' => $parte->nombre_encargado_reemplazado ?: '',
                            ]);

                            // Aplicar estilo a la fila del estudiante
                            $this->rowStyles[$rowIndex] = [
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => $backgroundColor]
                                ]
                            ];
                            $rowIndex++;

                            // Si hay ayudante, agregar fila adicional para el ayudante
                            if ($parte->nombre_ayudante) {
                                $data->push([
                                    //Fecha en formato dd-mm-AAAA
                                    'Fecha' => \Carbon\Carbon::parse($programa->fecha)->locale('es')->translatedFormat('d-m-Y'),
                                    'Parte' => $parte->parte_abreviacion ?: 'N/A',
                                    'Nombre' => $parte->nombre_ayudante ?: 'N/A',
                                    'Participaciones' => '('.$parte->contador_ayudante.')',
                                    'Rol' => 'Ayudante',
                                    'Sala' => $parte->sala_id ?: '',
                                    'Reemplazado' => $parte->nombre_ayudante_reemplazado ?: '',
                                ]);

                                // Aplicar estilo a la fila del ayudante
                                $this->rowStyles[$rowIndex] = [
                                    'fill' => [
                                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                        'startColor' => ['rgb' => $backgroundColor]
                                    ]
                                ];
                                $rowIndex++;
                            }
                        }
                    }

                    return $data;
                }

                public function headings(): array
                {
                    return [
                        'Fecha',
                        'Parte',
                        'Nombre',
                        'Participaciones',
                        'Rol',
                        'Sala',
                        'Reemplazado',
                    ];
                }

                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    // Aplicar estilos a las filas de datos
                    foreach ($this->rowStyles as $row => $style) {
                        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($style);
                    }
                    //Columna G texto tachado
                    $sheet->getStyle('G2:G' . $sheet->getHighestRow())->applyFromArray([
                        'font' => [
                            'strikethrough' => true,
                        ],
                    ]);
                    // Estilo para el header
                    $sheet->getStyle('A1:G1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'FFFFFF']
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '4CAF50']
                        ]
                    ]);

                    // Autoajustar columnas
                    foreach (range('A', 'G') as $column) {
                        $sheet->getColumnDimension($column)->setAutoSize(true);
                    }
                }

                private function getProgramBackgroundColor($programIndex)
                {
                    // Colores alternados para diferentes programas
                    $colors = [
                        'E8F5E8', // Verde muy claro
                        'F3E5F5', // Púrpura muy claro
                        'E3F2FD', // Azul muy claro
                        'FFF3E0', // Naranja muy claro
                        'FCE4EC', // Rosa muy claro
                        'E8F5E8', // Verde muy claro (repetir patrón)
                        'F3E5F5', // Púrpura muy claro
                        'E3F2FD', // Azul muy claro
                        'FFF3E0', // Naranja muy claro
                        'FCE4EC', // Rosa muy claro
                    ];

                    return $colors[$programIndex % count($colors)];
                }
            }, $fileName . '.xlsx');

        } catch (\Exception $e) {
             \Log::error('❌ Error en exportación XLS:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);
            return redirect()->route('programas.index')
                ->with('error', 'Error al exportar XLS: ' . $e->getMessage());
        }
    }

    /**
     * Exportar asignaciones de programas a XLS (para coordinadores - perfil 3 y organizadores - perfil 7)
     */
    public function exportAsignaciones(Request $request)
    {
        try {
            $currentUser = Auth::user();

            // Obtener parámetros de filtro
            $anio = $request->get('anio');
            $meses = $request->get('mes'); // Ahora puede ser un array

            // Si meses es un string, convertirlo a array para consistencia
            if (is_string($meses)) {
                $meses = [$meses];
            }

            // Consulta para obtener las partes de programa de la sección "Seamos Mejores Maestros" (seccion_id = 2)
            $query = DB::table('partes_programa as pp')
                ->leftJoin('programas as p', 'pp.programa_id', '=', 'p.id')
                ->leftJoin('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                ->leftJoin('users as encargado', 'pp.encargado_id', '=', 'encargado.id')
                ->leftJoin('users as ayudante', 'pp.ayudante_id', '=', 'ayudante.id')
                ->leftJoin('users as creador', 'p.creador', '=', 'creador.id')
                ->leftJoin('congregaciones as c', 'creador.congregacion', '=', 'c.id')
                ->whereIn('ps.seccion_id', [1,2]) // Filtrar "Tesoros de la Biblia y Seamos Mejores Maestros"
                ->where('creador.congregacion', $currentUser->congregacion)
                ->whereNotIn('ps.id', [1, 2]) // Excluir TB, BPE (dejando solo la Lectura de la Biblia)
                ->whereNotNull('p.fecha')
                ->whereNotNull('pp.encargado_id'); // Solo partes con encargado asignado

            // Aplicar filtros de fecha si se proporcionan
            if ($anio) {
                // Cambiar whereYear (SQLite) por whereRaw con YEAR (MySQL)
                $query->whereRaw('YEAR(p.fecha) = ?', [$anio]);

                // Si hay meses específicos seleccionados, filtrar por ellos
                if ($meses && is_array($meses) && !empty($meses)) {
                    $query->where(function($q) use ($meses) {
                        foreach ($meses as $mes) {
                            // Cambiar whereMonth (SQLite) por whereRaw con MONTH (MySQL)
                            $q->orWhereRaw('MONTH(p.fecha) = ?', [$mes]);
                        }
                    });
                }
            }

            $asignaciones = $query->select(
                    'p.fecha',
                    'pp.leccion',
                    'ps.nombre as parte_nombre',
                    'ps.tipo as parte_tipo',
                    'encargado.name as nombre_encargado',
                    'ayudante.name as nombre_ayudante',
                    'pp.sala_id',
                    'pp.orden',
                    'pp.parte_id'
                )
                ->orderBy('p.fecha', 'asc')
                ->orderBy('pp.sala_id', 'asc')
                ->orderBy('pp.orden', 'asc')
                ->get();

            $asignaciones = $asignaciones->groupBy('fecha')->map(function ($asignacionesPorFecha) {
                $numeroIntervencion = 4; // Empezar desde 4

                return $asignacionesPorFecha->map(function ($asignacion) use (&$numeroIntervencion) {
                    if ($asignacion->parte_id == 3) {
                        // Si es parte_id=3 (Lectura de la Biblia), mostrar 3
                        $asignacion->numero_intervencion = 3;
                    } else {
                        // Para los demás, asignar números consecutivos empezando desde 4
                        $asignacion->numero_intervencion = $numeroIntervencion;
                        $numeroIntervencion++;
                    }
                    return $asignacion;
                });
            })->flatten();

            // Si no hay asignaciones, crear datos de ejemplo para mostrar el formato
            if ($asignaciones->isEmpty()) {
                $asignaciones = collect([
                    (object)[
                        'fecha' => '2025-10-15',
                        'leccion' => 'th lección 10',
                        'parte_nombre' => 'Lectura de la Biblia',
                        'parte_tipo' => 1,
                        'parte_id' => 3,
                        'nombre_encargado' => 'CAMILO ARRIAGADA',
                        'nombre_ayudante' => null,
                        'numero_intervencion' => 3
                    ],
                    (object)[
                        'fecha' => '2025-10-15',
                        'leccion' => 'lmd lección 2 punto 4',
                        'parte_nombre' => 'Empiece conversaciones',
                        'parte_tipo' => 2,
                        'parte_id' => 12,
                        'nombre_encargado' => 'SILVIA GUTIERREZ RAMOS',
                        'nombre_ayudante' => 'ALBA GIL',
                        'numero_intervencion' => 4
                    ],
                    (object)[
                        'fecha' => '2025-10-15',
                        'leccion' => 'lmd lección 2 punto 3',
                        'parte_nombre' => 'Empiece conversaciones',
                        'parte_tipo' => 2,
                        'parte_id' => 12,
                        'nombre_encargado' => 'IGNACIA BRAVO',
                        'nombre_ayudante' => 'CLAUDIA MATURANA',
                        'numero_intervencion' => 5
                    ],
                    (object)[
                        'fecha' => '2025-10-15',
                        'leccion' => 'lmd lección 9 punto 4',
                        'parte_nombre' => 'Haga revisitas',
                        'parte_tipo' => 3,
                        'parte_id' => 13,
                        'nombre_encargado' => 'GILDA HERMOSILLA',
                        'nombre_ayudante' => 'PRISCILA ZUÑIGA',
                        'numero_intervencion' => 6
                    ]
                ]);
            }

            // Preparar nombre del archivo
            $fileName = 'programaEscuelaAsignaciones';
            if ($anio && $meses && is_array($meses) && !empty($meses)) {
                // Si hay múltiples meses, usar el primer mes para el nombre del archivo
                $primerMes = min($meses); // Usar el mes más pequeño
                $fileName .= '_' . $anio . '_' . str_pad($primerMes, 2, '0', STR_PAD_LEFT);

                // Si hay más de un mes, agregar indicador
                if (count($meses) > 1) {
                    $fileName .= '_multiple';
                }
            } else {
                $fileName .= '_' . date('Y-m-d');
            }

            // Agrupar las asignaciones en grupos de 4 para el PDF
            $asignacionesAgrupadas = $asignaciones->chunk(4);

            // Generar PDF usando dompdf
            $pdf = PDF::loadView('programas.asignaciones-pdf', compact('asignacionesAgrupadas'));
            $pdf->setPaper('letter', 'portrait');

            return $pdf->download($fileName . '.pdf');

        } catch (\Exception $e) {
            return redirect()->route('programas.index')
                ->with('error', 'Error al exportar asignaciones: ' . $e->getMessage());
        }
    }

    /**
     * Obtener información de una asignación específica por partes_programa.id
     */
    public function getAsignacionPorId($parteProgramaId)
    {
        try {
            $currentUser = Auth::user();

            // Consulta para obtener la parte de programa específica
            $asignacion = DB::table('partes_programa as pp')
                ->join('programas as p', 'pp.programa_id', '=', 'p.id')
                ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                ->leftJoin('users as encargado', 'pp.encargado_id', '=', 'encargado.id')
                ->leftJoin('users as ayudante', 'pp.ayudante_id', '=', 'ayudante.id')
                ->leftJoin('users as creador', 'p.creador', '=', 'creador.id')
                ->where('pp.id', $parteProgramaId)
                ->where('creador.congregacion', $currentUser->congregacion)
                ->select(
                    'pp.id',
                    'p.fecha',
                    'pp.leccion',
                    'pp.sala_id',
                    'pp.orden',
                    'pp.parte_id',
                    'ps.nombre as parte_nombre',
                    'ps.abreviacion as parte_abreviacion',
                    'ps.tipo as parte_tipo',
                    'ps.seccion_id',
                    'encargado.id as encargado_id',
                    'encargado.name as nombre_encargado',
                    'ayudante.id as ayudante_id',
                    'ayudante.name as nombre_ayudante'
                )
                ->first();

            if (!$asignacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la asignación o no tiene permisos para verla.'
                ], 404);
            }

            // Calcular el número de intervención
            // Obtener todas las asignaciones del mismo programa para calcular el orden correcto
            $asignacionesDelPrograma = DB::table('partes_programa as pp')
                ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                ->where('pp.programa_id', DB::table('partes_programa')
                    ->where('id', $parteProgramaId)
                    ->value('programa_id'))
                ->whereIn('ps.seccion_id', [2]) // Tesoros de la Biblia y Seamos Mejores Maestros
                ->orderBy('pp.orden', 'asc')
                ->select('pp.id', 'pp.parte_id', 'pp.orden')
                ->get();

            $numeroIntervencion = 4;
            foreach ($asignacionesDelPrograma as $item) {
                if ($item->parte_id == 3) {
                    if ($item->id == $parteProgramaId) {
                        $numeroIntervencion = 3;
                        break;
                    }
                } else {
                    if ($item->id == $parteProgramaId) {
                        break;
                    }
                    $numeroIntervencion++;
                }
            }

            $asignacion->numero_intervencion = $numeroIntervencion;
            $asignacion->fecha_formateada = \Carbon\Carbon::parse($asignacion->fecha)->locale('es')->translatedFormat('d \d\e F \d\e Y');

            return response()->json([
                'success' => true,
                'data' => $asignacion
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la asignación: ' . $e->getMessage()
            ], 500);
        }
    }
}
