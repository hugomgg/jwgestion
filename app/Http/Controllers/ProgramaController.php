<?php

namespace App\Http\Controllers;

use App\Models\Programa;
use App\Models\User;
use App\Models\Cancion;
use App\Models\ParteSeccion;
use App\Models\SeccionReunion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProgramaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentUser = auth()->user();
        
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
            
        // Para coordinadores (perfil=3), obtener usuarios especiales para presidencia y orador inicial
        $usuariosPresidencia = [];
        $usuariosOradorInicial = [];
        if ($currentUser->perfil == 3) {
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
        }
            
        $canciones = Cancion::where('estado', true)
            ->orderBy('numero')
            ->get();

        return view('programas.index', compact('programas', 'usuarios', 'canciones', 'usuariosPresidencia', 'usuariosOradorInicial'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $programa = Programa::with(['oradorInicial', 'presidenciaUsuario', 'oradorFinal'])->findOrFail($id);
            $currentUser = auth()->user();
            
            // Obtener datos para los selects del formulario principal
            $usuarios = User::where('estado', true)
                ->orderBy('name')
                ->get();
                
            $canciones = Cancion::where('estado', true)
                ->orderBy('numero')
                ->get();

            // Para coordinadores (perfil=3), obtener usuarios especiales para presidencia y orador inicial
            $usuariosPresidencia = [];
            $usuariosOradorInicial = [];
            if ($currentUser->perfil == 3) {
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
            }

            // Obtener la sección de reunión con id=1 para el título
            $seccionReunion = SeccionReunion::find(1);

            return view('programas.edit', compact('programa', 'usuarios', 'canciones', 'seccionReunion', 'usuariosPresidencia', 'usuariosOradorInicial'));
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
    public function getPartesSegundaSeccion($programaId)
    {
        try {
            $partes = DB::table('partes_programa as pp')
                ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                ->leftJoin('users as u', 'pp.encargado_id', '=', 'u.id')
                ->leftJoin('users as u_ayudante', 'pp.ayudante_id', '=', 'u_ayudante.id')
                ->leftJoin('salas as s', 'pp.sala_id', '=', 's.id')
                ->where('pp.programa_id', $programaId)
                ->where('ps.seccion_id', 2)
                ->where('pp.sala_id', 1)
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
                    'pp.leccion',
                    'pp.orden'
                )
                ->orderBy('pp.orden', 'asc')
                ->get();

            // Agregar información sobre posición (primero/último) para cada parte
            $partesCollection = collect($partes);
            $partesCollection = $partesCollection->map(function ($parte, $index) use ($partesCollection) {
                $parte->es_primero = $index === 0;
                $parte->es_ultimo = $index === ($partesCollection->count() - 1);
                return $parte;
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
}
