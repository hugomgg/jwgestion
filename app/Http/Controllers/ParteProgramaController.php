<?php

namespace App\Http\Controllers;

use App\Models\PartePrograma;
use App\Models\ParteSeccion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParteProgramaController extends Controller
{
    /**
     * Obtener las partes del programa con sección 1
     */
    public function getPartesPorPrograma($programaId)
    {
        try {
            $user = Auth::user();

            $query = DB::table('partes_programa as pp')
                ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                ->leftJoin('users as encargado', 'pp.encargado_id', '=', 'encargado.id')
                ->leftJoin('users as ayudante', 'pp.ayudante_id', '=', 'ayudante.id')
                ->leftJoin('users as encargado_reemplazado', 'pp.encargado_reemplazado_id', '=', 'encargado_reemplazado.id')
                ->where('pp.programa_id', $programaId)
                ->where('ps.seccion_id', 1);

            $partes = $query->select(
                    'pp.id',
                    'pp.tiempo',
                    'pp.tema',
                    'pp.leccion',
                    'pp.estado',
                    'pp.sala_id',
                    'pp.orden',
                    'pp.encargado_id',
                    'pp.ayudante_id',
                    'pp.encargado_reemplazado_id',
                    'ps.nombre as parte_nombre',
                    'ps.abreviacion as parte_abreviacion',
                    'encargado.name as encargado_nombre',
                    'ayudante.name as ayudante_nombre',
                    'encargado_reemplazado.name as encargado_reemplazado_nombre',
                    DB::raw('row_number() OVER (PARTITION BY pp.sala_id ORDER BY pp.orden) as numero')
                )
                ->get();

            // Agregar información sobre posición (primero/último) para cada parte
            $partesCollection = collect($partes);
            $partesCollection = $partesCollection->map(function ($parte, $index) use ($partesCollection) {
                $parte->es_primero = $index === 0;
                $parte->es_ultimo = $index === ($partesCollection->count() - 1);

                // Estructurar los datos del encargado
                $parte->encargado = null;
                if ($parte->encargado_id && $parte->encargado_nombre) {
                    $parte->encargado = (object)[
                        'id' => $parte->encargado_id,
                        'name' => $parte->encargado_nombre
                    ];
                }

                // Estructurar los datos del ayudante
                $parte->ayudante = null;
                if ($parte->ayudante_id && $parte->ayudante_nombre) {
                    $parte->ayudante = (object)[
                        'id' => $parte->ayudante_id,
                        'name' => $parte->ayudante_nombre
                    ];
                }

                // Estructurar los datos del encargado reemplazado
                $parte->encargado_reemplazado = null;
                if ($parte->encargado_reemplazado_id && $parte->encargado_reemplazado_nombre) {
                    $parte->encargado_reemplazado = (object)[
                        'id' => $parte->encargado_reemplazado_id,
                        'name' => $parte->encargado_reemplazado_nombre
                    ];
                }

                return $parte;
            });

            $partes = $partesCollection->toArray();

            return response()->json([
                'success' => true,
                'data' => $partes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las partes del programa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener las partes del programa de la tercera sección (sala_id = 2)
     */
    public function getPartesTerceraSeccion($programaId)
    {
        try {
            $user = Auth::user();

            $query = DB::table('partes_programa as pp')
                ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                ->leftJoin('users as encargado', 'pp.encargado_id', '=', 'encargado.id')
                ->leftJoin('users as ayudante', 'pp.ayudante_id', '=', 'ayudante.id')
                ->leftJoin('salas as s', 'pp.sala_id', '=', 's.id')
                ->where('pp.programa_id', $programaId)
                ->where('pp.sala_id', 2)
                ->where('pp.estado', true)
                ->select(
                    'pp.id',
                    'pp.orden',
                    'pp.tiempo',
                    'pp.tema',
                    'pp.leccion',
                    'ps.abreviacion as parte_abreviacion',
                    's.abreviacion as sala_abreviacion',
                    'encargado.name as encargado_nombre',
                    'ayudante.name as ayudante_nombre'
                )
                ->orderBy('pp.orden', 'asc')
                ->get();

            // Agregar información sobre posición (primero/último) para cada parte
            $partesCollection = collect($query);
            $data = $partesCollection->map(function ($parte, $index) use ($partesCollection) {
                $parte->es_primero = $index === 0;
                $parte->es_ultimo = $index === ($partesCollection->count() - 1);
                return $parte;
            })->toArray();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las partes de la tercera sección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener las partes del programa de la sección Nuestra Vida Cristiana (seccion_id = 3)
     */
    public function getPartesNV($programaId)
    {
        try {
            $user = Auth::user();
            //contar la cantidad de registros de la tabla partes_programa where seccion_id in (1,2)
            $count = DB::table('partes_programa as pp')
                ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                ->where('pp.programa_id', $programaId)
                ->where('pp.sala_id', 1)
                ->whereIn('seccion_id', [2])
                ->count();

            $query = DB::table('partes_programa as pp')
                ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                ->leftJoin('users as encargado', 'pp.encargado_id', '=', 'encargado.id')
                ->leftJoin('users as ayudante', 'pp.ayudante_id', '=', 'ayudante.id')
                ->leftJoin('users as encargado_reemplazado', 'pp.encargado_reemplazado_id', '=', 'encargado_reemplazado.id')
                ->where('pp.programa_id', $programaId)
                ->where('ps.seccion_id', 3)
                ->where('pp.estado', true);

            $partes = $query->select(
                    'pp.id',
                    'pp.tiempo',
                    'pp.tema',
                    'pp.leccion',
                    'pp.estado',
                    'pp.sala_id',
                    'pp.orden',
                    'pp.encargado_id',
                    'pp.ayudante_id',
                    'pp.encargado_reemplazado_id',
                    'pp.ayudante_reemplazado_id',
                    'ps.nombre as parte_nombre',
                    'ps.abreviacion as parte_abreviacion',
                    'encargado.name as encargado_nombre',
                    'ayudante.name as ayudante_nombre',
                    'encargado_reemplazado.name as encargado_reemplazado_nombre',
                    DB::raw("CASE WHEN pp.parte_id=24 THEN '-' ELSE (row_number() OVER (PARTITION BY pp.sala_id ORDER BY pp.sala_id, pp.orden)) + (${count} + 2) END as numero")
                )
                ->get();

            // Agregar información sobre posición (primero/último) para cada parte
            $partesCollection = collect($partes);
            $partesCollection = $partesCollection->map(function ($parte, $index) use ($partesCollection) {
                $parte->es_primero = $index === 0;
                $parte->es_ultimo = $index === ($partesCollection->count() - 1);

                // Estructurar los datos del encargado
                $parte->encargado = null;
                if ($parte->encargado_id && $parte->encargado_nombre) {
                    $parte->encargado = (object)[
                        'id' => $parte->encargado_id,
                        'name' => $parte->encargado_nombre
                    ];
                }

                // Estructurar los datos del ayudante
                $parte->ayudante = null;
                if ($parte->ayudante_id && $parte->ayudante_nombre) {
                    $parte->ayudante = (object)[
                        'id' => $parte->ayudante_id,
                        'name' => $parte->ayudante_nombre
                    ];
                }

                // Estructurar los datos del encargado reemplazado
                $parte->encargado_reemplazado = null;
                if ($parte->encargado_reemplazado_id && $parte->encargado_reemplazado_nombre) {
                    $parte->encargado_reemplazado = (object)[
                        'id' => $parte->encargado_reemplazado_id,
                        'name' => $parte->encargado_reemplazado_nombre
                    ];
                }

                // Estructurar los datos del ayudante reemplazado
                $parte->ayudante_reemplazado = null;
                if ($parte->ayudante_reemplazado_id && $parte->ayudante_reemplazado_nombre) {
                    $parte->ayudante_reemplazado = (object)[
                        'id' => $parte->ayudante_reemplazado_id,
                        'name' => $parte->ayudante_reemplazado_nombre
                    ];
                }

                return $parte;
            });

            $partes = $partesCollection->toArray();

            return response()->json([
                'success' => true,
                'data' => $partes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las partes de Nuestra Vida Cristiana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el historial de participaciones de un usuario
     */
    public function getHistorialUsuario($usuarioId)
    {
        try {
            $historial = DB::table('partes_programa as pp')
                ->join('programas as p', 'pp.programa_id', '=', 'p.id')
                ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                ->where(function($query) use ($usuarioId) {
                    $query->where('pp.encargado_id', $usuarioId)
                          ->orWhere('pp.ayudante_id', $usuarioId);
                })
                ->where('pp.estado', true)
                ->select(
                    'pp.id',
                    'p.fecha',
                    'ps.abreviacion as parte_abreviacion',
                    DB::raw('CASE
                        WHEN pp.encargado_id = ' . $usuarioId . ' THEN "ES"
                        WHEN pp.ayudante_id = ' . $usuarioId . ' THEN "AY"
                        ELSE "N/A"
                    END as tipo_participacion')
                )
                ->orderBy('p.fecha', 'desc')
                ->limit(10) // Limitar a las últimas 10 participaciones
                ->get();

            return response()->json([
                'success' => true,
                'data' => $historial
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial del usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacenar una nueva parte de programa
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();
        //No pernmite ver programas si $currentUser->perfil no es 3
        if ($currentUser->perfil != 3) {
            return redirect()->route('home')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        $validator = Validator::make($request->all(), [
            'programa_id' => 'required|exists:programas,id',
            'parte_id' => 'required|exists:partes_seccion,id',
            'tiempo' => 'required|integer|min:1',
            'tema' => 'nullable|string|max:500',
            'encargado_id' => 'required|exists:users,id',
            'ayudante_id' => 'nullable|exists:users,id',
            'leccion' => 'nullable|string|max:500',
            'encargado_reemplazado_id' => 'nullable|exists:users,id',
            'ayudante_reemplazado_id' => 'nullable|exists:users,id',
            'sala_id' => 'required|integer|in:1,2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que la parte seleccionada existe y es válida
        $parteSeccion = ParteSeccion::find($request->parte_id);
        if (!$parteSeccion) {
            return response()->json([
                'success' => false,
                'message' => 'La parte seleccionada no existe.'
            ], 422);
        }

        if (!in_array($parteSeccion->seccion_id, [1, 2, 3])) {
            return response()->json([
                'success' => false,
                'message' => 'La parte seleccionada no es válida.'
            ], 422);
        }

        try {

            // Obtener el siguiente número de orden para este programa
            $maxOrden = PartePrograma::where('programa_id', $request->programa_id)->max('orden');
            $nuevoOrden = $maxOrden ? $maxOrden + 1 : 1;

            $partePrograma = new PartePrograma();
            $partePrograma->programa_id = $request->programa_id;
            $partePrograma->parte_id = $request->parte_id;
            $partePrograma->orden = $nuevoOrden;
            $partePrograma->tiempo = $request->tiempo;
            $partePrograma->tema = $request->tema;
            $partePrograma->encargado_id = $request->encargado_id;
            $partePrograma->ayudante_id = $request->ayudante_id;
            $partePrograma->leccion = $request->leccion;
            $partePrograma->encargado_reemplazado_id = $request->encargado_reemplazado_id;
            $partePrograma->ayudante_reemplazado_id = $request->ayudante_reemplazado_id;
            $partePrograma->estado = true; // Por defecto activo

            // Asignar sala_id del formulario para todos los usuarios
            $partePrograma->sala_id = $request->sala_id;

            $partePrograma->creador_id = Auth::id();
            $partePrograma->modificador_id = Auth::id();
            $partePrograma->save();

            return response()->json([
                'success' => true,
                'message' => 'Parte del programa creada exitosamente.',
                'data' => $partePrograma
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la parte del programa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una parte específica del programa
     */
    public function show($id)
    {
        try {
            $parte = DB::table('partes_programa as pp')
                ->leftJoin('users as encargado', 'pp.encargado_id', '=', 'encargado.id')
                ->leftJoin('users as ayudante', 'pp.ayudante_id', '=', 'ayudante.id')
                ->leftJoin('users as encargado_reemplazado', 'pp.encargado_reemplazado_id', '=', 'encargado_reemplazado.id')
                ->leftJoin('users as ayudante_reemplazado', 'pp.ayudante_reemplazado_id', '=', 'ayudante_reemplazado.id')
                ->leftJoin('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                ->where('pp.id', $id)
                ->select(
                    'pp.*',
                    'encargado.name as encargado_nombre',
                    'ayudante.name as ayudante_nombre',
                    'encargado_reemplazado.name as encargado_reemplazado_nombre',
                    'ayudante_reemplazado.name as ayudante_reemplazado_nombre',
                    'ps.nombre as parte_nombre',
                    'ps.abreviacion as parte_abreviacion'
                )
                ->first();

            if (!$parte) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parte del programa no encontrada.'
                ], 404);
            }

            // Formatear los datos de reemplazados para que coincidan con lo que espera el frontend
            $parteFormateada = [
                'id' => $parte->id,
                'programa_id' => $parte->programa_id,
                'parte_id' => $parte->parte_id,
                'tiempo' => $parte->tiempo,
                'tema' => $parte->tema,
                'leccion' => $parte->leccion,
                'estado' => $parte->estado,
                'sala_id' => $parte->sala_id,
                'orden' => $parte->orden,
                'encargado_id' => $parte->encargado_id,
                'ayudante_id' => $parte->ayudante_id,
                'encargado_reemplazado_id' => $parte->encargado_reemplazado_id,
                'ayudante_reemplazado_id' => $parte->ayudante_reemplazado_id,
                'encargado' => $parte->encargado_id ? [
                    'id' => $parte->encargado_id,
                    'name' => $parte->encargado_nombre
                ] : null,
                'ayudante' => $parte->ayudante_id ? [
                    'id' => $parte->ayudante_id,
                    'name' => $parte->ayudante_nombre
                ] : null,
                'encargado_reemplazado' => $parte->encargado_reemplazado_id ? [
                    'id' => $parte->encargado_reemplazado_id,
                    'name' => $parte->encargado_reemplazado_nombre
                ] : null,
                'ayudante_reemplazado' => $parte->ayudante_reemplazado_id ? [
                    'id' => $parte->ayudante_reemplazado_id,
                    'name' => $parte->ayudante_reemplazado_nombre
                ] : null,
                'encargado_nombre' => $parte->encargado_nombre,
                'ayudante_nombre' => $parte->ayudante_nombre,
                'parte_nombre' => $parte->parte_nombre,
                'parte_abreviacion' => $parte->parte_abreviacion
            ];

            return response()->json([
                'success' => true,
                'data' => $parteFormateada
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parte del programa no encontrada.'
            ], 404);
        }
    }

    /**
     * Actualizar una parte del programa
     */
    public function update(Request $request, $id)
    {
        $currentUser = auth()->user();
        //No pernmite ver programas si $currentUser->perfil no es 3
        if ($currentUser->perfil != 3) {
            return redirect()->route('home')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        $validator = Validator::make($request->all(), [
            'parte_id' => 'required|exists:partes_seccion,id',
            'tiempo' => 'required|integer|min:1',
            'tema' => 'nullable|string|max:500',
            'encargado_id' => 'required|exists:users,id',
            'ayudante_id' => 'nullable|exists:users,id',
            'leccion' => 'nullable|string|max:500',
            'encargado_reemplazado_id' => 'nullable|exists:users,id',
            'ayudante_reemplazado_id' => 'nullable|exists:users,id',
            'sala_id' => 'required|integer|in:1,2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que la parte seleccionada existe y es válida
        $parteSeccion = ParteSeccion::find($request->parte_id);
        if (!$parteSeccion) {
            return response()->json([
                'success' => false,
                'message' => 'La parte seleccionada no existe.'
            ], 422);
        }

        // Validar sección según el contexto
        $user = Auth::user();
        // Para coordinadores, permitir secciones 1, 2 y 3
        if (!in_array($parteSeccion->seccion_id, [1, 2, 3])) {
            return response()->json([
                'success' => false,
                'message' => 'La parte seleccionada no es válida.'
            ], 422);
        }

        try {
            $partePrograma = PartePrograma::findOrFail($id);
            $partePrograma->parte_id = $request->parte_id;
            $partePrograma->tiempo = $request->tiempo;
            $partePrograma->tema = $request->tema;
            $partePrograma->encargado_id = $request->encargado_id;
            $partePrograma->ayudante_id = $request->ayudante_id;
            $partePrograma->leccion = $request->leccion;
            $partePrograma->encargado_reemplazado_id = $request->encargado_reemplazado_id;
            $partePrograma->ayudante_reemplazado_id = $request->ayudante_reemplazado_id;

            // Asignar sala_id del formulario para todos los usuarios
            $partePrograma->sala_id = $request->sala_id;

            $partePrograma->modificador_id = Auth::id();
            // El campo estado no se modifica en el update
            $partePrograma->save();

            return response()->json([
                'success' => true,
                'message' => 'Parte del programa actualizada exitosamente.',
                'data' => $partePrograma
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la parte del programa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una parte del programa
     */
    public function destroy($id)
    {
        $currentUser = auth()->user();
        //No pernmite ver programas si $currentUser->perfil no es 3
        if ($currentUser->perfil != 3) {
            return redirect()->route('home')
                ->with('error', 'No tienes permiso para acceder a esta sección.');
        }
        try {
            $partePrograma = PartePrograma::findOrFail($id);
            $partePrograma->delete();

            return response()->json([
                'success' => true,
                'message' => 'Parte del programa eliminada exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la parte del programa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener partes de sección para dropdown (solo sección 1)
     */
    public function getPartesSecciones(Request $request)
    {
        try {
            $user = Auth::user();
            $programaId = $request->get('programa_id');
            $parteId = $request->get('parte_id');
            $query = ParteSeccion::where('seccion_id', $parteId ?? 1);

            // Solo aplicar filtro de estado para usuarios que no son administradores
            if ($user && $user->perfil != 1) {
                $query->where('estado', 1);
            }

            // Los coordinadores ahora pueden crear partes en cualquier sala
            if ($user && $programaId) {
                $partesExistentes = DB::table('partes_programa')
                    ->where('programa_id', $programaId)
                    ->where('sala_id', 1)
                    ->pluck('parte_id')
                    ->toArray();

                if (!empty($partesExistentes)) {
                    $query->whereNotIn('id', $partesExistentes);
                }
            }

            $partes = $query->orderBy('orden')
                ->get(['id', 'nombre', 'abreviacion', 'tiempo']);

            return response()->json([
                'success' => true,
                'data' => $partes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las partes de sección: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Obtener usuarios filtrados por asignación de parte y congregación del usuario autenticado
     */
    public function getUsersByParteAndCongregacion($parteId)
    {
        try {
            $user = Auth::user();

            // Obtener la parte de sección para conocer su asignación
            $parteSeccion = ParteSeccion::find($parteId);

            if (!$parteSeccion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parte de sección no encontrada.'
                ], 404);
            }

            // Para perfil=3 (coordinadores), aplicar lógica especial para la segunda sección
            if ($user->perfil == 3 && $parteSeccion->seccion_id == 2) {
                $usuarios = DB::table('users as u')
                    ->join('asignaciones_users as au', 'u.id', '=', 'au.user_id')
                    ->leftJoin(DB::raw('(
                        SELECT
                            user_id,
                            MAX(fecha) as ultima_fecha,
                            (SELECT ps2.abreviacion
                             FROM partes_programa pp2
                             INNER JOIN programas prog2 ON pp2.programa_id = prog2.id
                             INNER JOIN partes_seccion ps2 ON pp2.parte_id = ps2.id
                             WHERE ps2.seccion_id = 2
                             AND (pp2.encargado_id = participaciones.user_id OR pp2.ayudante_id = participaciones.user_id)
                             ORDER BY prog2.fecha DESC
                             LIMIT 1) as parte_abreviacion,
                            (SELECT CASE
                                WHEN pp2.encargado_id = participaciones.user_id THEN "ES"
                                WHEN pp2.ayudante_id = participaciones.user_id THEN "AY"
                                ELSE NULL
                             END
                             FROM partes_programa pp2
                             INNER JOIN programas prog2 ON pp2.programa_id = prog2.id
                             INNER JOIN partes_seccion ps2 ON pp2.parte_id = ps2.id
                             WHERE ps2.seccion_id = 2
                             AND (pp2.encargado_id = participaciones.user_id OR pp2.ayudante_id = participaciones.user_id)
                             ORDER BY prog2.fecha DESC
                             LIMIT 1) as tipo_participacion
                        FROM (
                            SELECT pp.encargado_id as user_id, prog.fecha as fecha
                            FROM partes_programa pp
                            INNER JOIN programas prog ON pp.programa_id = prog.id
                            INNER JOIN partes_seccion ps ON pp.parte_id = ps.id
                            WHERE ps.seccion_id = 2 AND pp.encargado_id IS NOT NULL
                            UNION ALL
                            SELECT pp.ayudante_id as user_id, prog.fecha as fecha
                            FROM partes_programa pp
                            INNER JOIN programas prog ON pp.programa_id = prog.id
                            INNER JOIN partes_seccion ps ON pp.parte_id = ps.id
                            WHERE ps.seccion_id = 2 AND pp.ayudante_id IS NOT NULL
                        ) as participaciones
                        GROUP BY user_id
                    ) as ultima_participacion'), 'u.id', '=', 'ultima_participacion.user_id')
                    ->where('au.asignacion_id', $parteSeccion->asignacion_id)
                    ->where('u.congregacion', $user->congregacion)
                    ->where('u.estado', 1)
                    ->select(
                        'u.id',
                        'u.name',
                        'ultima_participacion.ultima_fecha',
                        'ultima_participacion.parte_abreviacion',
                        'ultima_participacion.tipo_participacion'
                    )
                    ->orderByRaw('ultima_participacion.ultima_fecha IS NULL DESC')
                    ->orderBy('ultima_participacion.ultima_fecha', 'asc')
                    ->orderBy('u.name')
                    ->get();
            } elseif ($user->perfil == 3 && $parteSeccion->seccion_id == 1) {
                // Lógica especial para coordinadores en la primera sección
                // Solo mostrar usuarios que han participado como ENCARGADOS en esa parte específica
                $usuarios = DB::table('users as u')
                    ->join('asignaciones_users as au', 'u.id', '=', 'au.user_id')
                    ->leftJoin(DB::raw('(
                        SELECT
                            pp.encargado_id as user_id,
                            MAX(prog.fecha) as ultima_fecha,
                            (SELECT ps2.abreviacion
                             FROM partes_programa pp2
                             INNER JOIN programas prog2 ON pp2.programa_id = prog2.id
                             INNER JOIN partes_seccion ps2 ON pp2.parte_id = ps2.id
                             WHERE pp2.encargado_id = pp.encargado_id
                             AND pp2.parte_id = ' . $parteId . '
                             ORDER BY prog2.fecha DESC
                             LIMIT 1) as parte_abreviacion
                        FROM partes_programa pp
                        INNER JOIN programas prog ON pp.programa_id = prog.id
                        WHERE pp.parte_id = ' . $parteId . '
                        AND pp.encargado_id IS NOT NULL
                        GROUP BY pp.encargado_id
                    ) as ultima_participacion'), 'u.id', '=', 'ultima_participacion.user_id')
                    ->where('au.asignacion_id', $parteSeccion->asignacion_id)
                    ->where('u.congregacion', $user->congregacion)
                    ->where('u.estado', 1)
                    ->select(
                        'u.id',
                        'u.name',
                        'ultima_participacion.ultima_fecha',
                        'ultima_participacion.parte_abreviacion',
                        DB::raw('"ES" as tipo_participacion') // Siempre encargado para primera sección
                    )
                    ->orderByRaw('ultima_participacion.ultima_fecha IS NULL DESC')
                    ->orderBy('ultima_participacion.ultima_fecha', 'asc')
                    ->orderBy('u.name')
                    ->get();
            } else {
                // Lógica original para otros perfiles
                $usuarios = DB::table('users as u')
                    ->join('asignaciones_users as au', 'u.id', '=', 'au.user_id')
                    ->leftJoin(DB::raw('(
                        SELECT
                            pp.encargado_id as user_id,
                            MAX(p.fecha) as ultima_fecha
                        FROM partes_programa pp
                        INNER JOIN programas p ON pp.programa_id = p.id
                        WHERE pp.parte_id = ' . $parteId . '
                        GROUP BY pp.encargado_id
                    ) as ultima_participacion'), 'u.id', '=', 'ultima_participacion.user_id')
                    ->where('au.asignacion_id', $parteSeccion->asignacion_id)
                    ->where('u.congregacion', $user->congregacion)
                    ->where('u.estado', 1) // Solo usuarios activos
                    ->select(
                        'u.id',
                        'u.name',
                        'ultima_participacion.ultima_fecha',
                        DB::raw('NULL as parte_abreviacion'),
                        DB::raw('NULL as tipo_participacion')
                    )
                    ->orderByRaw('CASE WHEN ultima_participacion.ultima_fecha IS NULL THEN 0 ELSE 1 END')
                    ->orderBy('ultima_participacion.ultima_fecha', 'asc')
                    ->orderBy('u.name')
                    ->get();
            }

            // Formatear la respuesta con nuevo orden: fecha|parte|tipo|nombre
            $usuariosFormateados = $usuarios->map(function($usuario) {
                if ($usuario->ultima_fecha) {
                    $fecha = \Carbon\Carbon::parse($usuario->ultima_fecha)->format('d/m/Y');
                    $parteAbrev = $usuario->parte_abreviacion ?? '__';
                    $tipoParticipacion = $usuario->tipo_participacion ?? '__';
                    $displayText = $fecha . '|' . $parteAbrev . '|' . $tipoParticipacion . '|' . $usuario->name;
                } else {
                    $displayText = 'Primera vez' . '|' . '__' . '|' . '__' . '|' . $usuario->name;
                }

                return [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'display_text' => $displayText,
                    'ultima_fecha' => $usuario->ultima_fecha,
                    'parte_abreviacion' => $usuario->parte_abreviacion ?? '__',
                    'tipo_participacion' => $usuario->tipo_participacion ?? '__'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $usuariosFormateados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuarios disponibles para el modal (todos los usuarios activos)
     */
    public function getUsuariosDisponibles()
    {
        try {
            $user = Auth::user();

            $usuarios = User::where('estado', true)
                ->where('congregacion', $user->congregacion)
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => $usuarios
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos de una parte de sección específica
     */
    public function getParteSeccion($id)
    {
        try {
            $parteSeccion = ParteSeccion::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $parteSeccion->id,
                    'nombre' => $parteSeccion->nombre,
                    'abreviacion' => $parteSeccion->abreviacion,
                    'tiempo' => $parteSeccion->tiempo
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parte de sección no encontrada.'
            ], 404);
        }
    }

    /**
     * Obtener historial de participaciones de un usuario específico
     */
    public function getHistorialParticipaciones($usuarioId, Request $request)
    {
        try {
            $user = Auth::user();

            // Verificar que el usuario autenticado tenga perfil=3 (coordinador)
            if ($user->perfil != 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para acceder a esta información.'
                ], 403);
            }

            $parteId = $request->get('parte_id');
            $tipo = $request->get('tipo', 'all'); // 'encargado', 'ayudante', o 'all'

            $query = DB::table('partes_programa as pp')
                ->join('programas as prog', 'pp.programa_id', '=', 'prog.id')
                ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                ->leftJoin('users as encargado', 'pp.encargado_id', '=', 'encargado.id')
                ->leftJoin('users as ayudante', 'pp.ayudante_id', '=', 'ayudante.id')
                ->where(function($subQuery) use ($usuarioId, $tipo) {
                    if ($tipo === 'encargado') {
                        $subQuery->where('pp.encargado_id', $usuarioId);
                    } elseif ($tipo === 'ayudante') {
                        $subQuery->where('pp.ayudante_id', $usuarioId);
                    } else {
                        $subQuery->where('pp.encargado_id', $usuarioId)
                                 ->orWhere('pp.ayudante_id', $usuarioId);
                    }
                });

            // Filtrar por parte específica si se proporciona
            if ($parteId) {
                $query->where('pp.parte_id', $parteId);
            } else {
                // Si no se especifica parte, mostrar solo segunda sección por defecto
                $query->where('ps.seccion_id', 2);
            }

            $historial = $query->select(
                    'pp.id',
                    'prog.fecha',
                    'ps.abreviacion as parte_abreviacion',
                    'encargado.name as nombre_encargado',
                    'ayudante.name as nombre_ayudante',
                    'pp.encargado_id',
                    'pp.ayudante_id',
                    'ps.seccion_id'
                )
                ->orderBy('prog.fecha', 'desc') // Desde la más reciente a la más antigua
                ->limit(20) // Limitar a las últimas 20 participaciones
                ->get();

            // Formatear los datos según la sección
            $historialFormateado = $historial->map(function($registro) use ($usuarioId) {
                $fecha = \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y');
                $parteAbrev = $registro->parte_abreviacion;

                if ($registro->seccion_id == 2) {
                    // Formato para segunda sección: fecha|parte_abreviacion|ES|nombre_encargado|AY|nombre_ayudante
                    $nombreEncargado = $registro->nombre_encargado ?? '__';
                    $nombreAyudante = $registro->nombre_ayudante ?? '__';

                    // Formatear nombre del encargado con largo fijo de 30 caracteres, completando con puntos a la derecha
                    $nombreEncargadoFormateado = str_pad($nombreEncargado, 30, '.', STR_PAD_RIGHT);

                    $historialTexto = $fecha . '|' . $parteAbrev . '|ES|' . $nombreEncargadoFormateado . '|AY|' . $nombreAyudante;
                } else {
                    // Formato para primera sección: fecha - parte - tipo de participación
                    $tipoParticipacion = ($registro->encargado_id == $usuarioId) ? 'Encargado' : 'Ayudante';
                    $historialTexto = $fecha . ' - ' . $parteAbrev . ' - ' . $tipoParticipacion;
                }

                return [
                    'id' => $registro->id,
                    'historial_texto' => $historialTexto,
                    'fecha' => $registro->fecha,
                    'parte_abreviacion' => $parteAbrev,
                    'encargado_id' => $registro->encargado_id,
                    'ayudante_id' => $registro->ayudante_id,
                    'programa_id' => $registro->id,
                    'nombre_usuario' => ($registro->encargado_id == $usuarioId) ? $registro->nombre_encargado : $registro->nombre_ayudante
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $historialFormateado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial de participaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ayudantes basados en el sexo del encargado y la parte de la sección
     */
    public function getAyudantesByEncargadoAndParte($encargadoId, $parteId, Request $request)
    {
        try {
            $user = Auth::user();

            // Verificar que el usuario autenticado tenga perfil=3 (coordinador)
            if ($user->perfil != 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para acceder a esta información.'
                ], 403);
            }

            // Obtener información del encargado
            $encargado = User::find($encargadoId);
            if (!$encargado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Encargado no encontrado.'
                ], 404);
            }

            // Obtener el asignacion_id de la parte seleccionada
            $parteSeccion = DB::table('partes_seccion')->where('id', $parteId)->first();
            $asignacionId = $parteSeccion ? $parteSeccion->asignacion_id : null;

            // Verificar si la parte es de tipo=3
            $esParteAmbosSexos = ($parteSeccion->tipo == 3);

            // Obtener usuarios únicos de la misma congregación que pueden ser ayudantes
            // Condiciones y relaciones aplicadas:
            // 1. users.id = asignaciones_users.user_id
            // 2. asignaciones_users.asignacion_id = asignaciones.id
            // 3. asignaciones.id = partes_seccion.asignacion_id
            // 4. partes_programa.parte_id = partes_seccion.id (relación clave)
            // 5. partes_seccion.id = "Parte de la Sección" seleccionada ($parteId)
            // 6. Misma congregación que el usuario autenticado
            // 7. Usuario activo (estado = 1)
            $usuariosQuery = DB::table('users as u')
                ->join('asignaciones_users as au', 'u.id', '=', 'au.user_id')
                ->join('asignaciones as a', 'au.asignacion_id', '=', 'a.id')
                ->join('partes_seccion as ps', 'a.id', '=', 'ps.asignacion_id')
                ->whereExists(function ($query) use ($parteId) {
                    $query->select(DB::raw(1))
                          ->from('partes_programa as pp')
                          ->join('partes_seccion as ps2', 'pp.parte_id', '=', 'ps2.id')
                          ->whereColumn('ps2.asignacion_id', 'au.asignacion_id')
                          ->where('ps2.id', $parteId);
                })
                ->where('u.congregacion', $user->congregacion)
                ->where('u.estado', 1);
                // NOTA: Se removió la exclusión del encargado para permitir que aparezca en el listado de ayudantes

            // Aplicar filtros de sexo según las reglas
            if ($esParteAmbosSexos) {
                // Para parte 12: cargar todos los usuarios que pueden participar
                $usuariosQuery->whereIn('u.sexo', [1, 2]); // Ambos sexos
            } else {
                // Para otras partes: solo del mismo sexo que el encargado
                $usuariosQuery->where('u.sexo', $encargado->sexo);
            }

            $usuariosBase = $usuariosQuery->select('u.id', 'u.name', 'u.sexo')
                ->distinct()
                ->get();

            // Obtener el ID de la parte programa que se está editando
            $parteProgramaEditandoId = $request->query('editing_id');
            $fechaEditando = null;
            $ayudanteEditandoId = null;
            $parteEditandoInfo = null;

            // Si hay una parte editándose, obtener su información completa
            if ($parteProgramaEditandoId) {
                $parteEditandoInfo = DB::table('partes_programa as pp')
                    ->join('programas as prog', 'pp.programa_id', '=', 'prog.id')
                    ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                    ->where('pp.id', $parteProgramaEditandoId)
                    ->select('prog.fecha', 'pp.ayudante_id', 'ps.abreviacion', 'pp.id as partes_programa_id')
                    ->first();

                if ($parteEditandoInfo) {
                    $fechaEditando = $parteEditandoInfo->fecha;
                    $ayudanteEditandoId = $parteEditandoInfo->ayudante_id;
                }
            }

            // Para cada usuario, obtener su última participación
            $usuarios = collect();
            foreach ($usuariosBase as $usuario) {
                $query = DB::table('partes_programa as pp')
                    ->join('programas as prog', 'pp.programa_id', '=', 'prog.id')
                    ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                    ->where(function($query) use ($usuario) {
                        $query->where('pp.encargado_id', $usuario->id)
                              ->orWhere('pp.ayudante_id', $usuario->id);
                    });

                $ultimaParticipacion = $query->select(
                        'prog.fecha',
                        'ps.abreviacion',
                        'ps.asignacion_id',
                        'pp.id as partes_programa_id',
                        DB::raw('CASE
                            WHEN pp.encargado_id = ' . $usuario->id . ' THEN "ES"
                            WHEN pp.ayudante_id = ' . $usuario->id . ' THEN "AY"
                            ELSE "AY"
                        END as tipo_participacion')
                    )
                    ->orderBy('prog.fecha', 'desc')
                    ->first();

                $usuarios->push((object)[
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'sexo' => $usuario->sexo,
                    'ultima_fecha' => $ultimaParticipacion ? $ultimaParticipacion->fecha : null,
                    'ultima_parte_abreviacion' => $ultimaParticipacion ? $ultimaParticipacion->abreviacion : null,
                    'partes_programa_id' => $ultimaParticipacion ? $ultimaParticipacion->partes_programa_id : null,
                    'tipo_participacion' => $ultimaParticipacion ? $ultimaParticipacion->tipo_participacion : 'AY',
                    'historial_asignacion_id' => $ultimaParticipacion ? $ultimaParticipacion->asignacion_id : null
                ]);
            }

            // Formatear los datos según el sexo del encargado y la parte
            $usuariosFormateados = [];

            if ($esParteAmbosSexos) {
                // Para parte tipo=3: organizar por secciones de género con encabezados
                $hombres = [];
                $mujeres = [];

                foreach ($usuarios as $usuario) {
                    // Si se está editando y este usuario es el ayudante actual, usar la información del registro editando
                    if ($parteProgramaEditandoId && $ayudanteEditandoId && $usuario->id == $ayudanteEditandoId && $parteEditandoInfo) {
                        // Usar la información del registro que se está editando
                        $fechaTexto = \Carbon\Carbon::parse($parteEditandoInfo->fecha)->format('d/m/Y');
                        $parteTexto = $parteEditandoInfo->abreviacion;
                        $tipoTexto = 'AY'; // Siempre ayudante para el campo Ayudante
                        $parteProgramaIdToShow = $parteProgramaEditandoId;
                    } else {
                        // Usar el historial normal para usuarios que no son el ayudante actual
                        $fechaTexto = $usuario->ultima_fecha
                            ? \Carbon\Carbon::parse($usuario->ultima_fecha)->format('d/m/Y')
                            : 'Primera vez';

                        $parteTexto = $usuario->ultima_parte_abreviacion ?? '__';
                        $tipoTexto = $usuario->ultima_fecha ? ($usuario->tipo_participacion ?? 'AY') : '__'; // Si es primera vez, tipo es "__"
                        $parteProgramaIdToShow = $usuario->partes_programa_id;
                    }

                    // Usar el asignacion_id del historial del usuario, o el de la parte actual si no tiene historial
                    $asignacionIdToShow = $usuario->historial_asignacion_id ?? $asignacionId;

                    $displayText = $fechaTexto . '|' . $parteTexto . '|' . $tipoTexto . '|' . $usuario->name;

                    $usuarioData = [
                        'id' => $usuario->id,
                        'name' => $usuario->name,
                        'display_text' => $displayText,
                        'sexo' => $usuario->sexo,
                        'partes_programa_id' => $parteProgramaIdToShow,
                        'asignacion_id' => $asignacionIdToShow
                    ];

                    if ($usuario->sexo == 1) { // Hombre
                        $hombres[] = $usuarioData;
                    } else { // Mujer
                        $mujeres[] = $usuarioData;
                    }
                }

                // Ordenar cada grupo por fecha (Primera vez primero, luego más antiguos primero)
                usort($hombres, function($a, $b) {
                    $fechaA = substr($a['display_text'], 0, 11); // Incluir "Primera vez"
                    $fechaB = substr($b['display_text'], 0, 11);

                    // Si alguno es "Primera vez", debe ir primero
                    if ($fechaA === 'Primera vez' && $fechaB !== 'Primera vez') {
                        return -1;
                    }
                    if ($fechaB === 'Primera vez' && $fechaA !== 'Primera vez') {
                        return 1;
                    }
                    if ($fechaA === 'Primera vez' && $fechaB === 'Primera vez') {
                        return 0;
                    }

                    // Si ninguno es "Primera vez", ordenar por fecha
                    return strcmp($fechaA, $fechaB);
                });

                usort($mujeres, function($a, $b) {
                    $fechaA = substr($a['display_text'], 0, 11); // Incluir "Primera vez"
                    $fechaB = substr($b['display_text'], 0, 11);

                    // Si alguno es "Primera vez", debe ir primero
                    if ($fechaA === 'Primera vez' && $fechaB !== 'Primera vez') {
                        return -1;
                    }
                    if ($fechaB === 'Primera vez' && $fechaA !== 'Primera vez') {
                        return 1;
                    }
                    if ($fechaA === 'Primera vez' && $fechaB === 'Primera vez') {
                        return 0;
                    }

                    // Si ninguno es "Primera vez", ordenar por fecha
                    return strcmp($fechaA, $fechaB);
                });

                // Crear resultado con secciones de género ordenadas según el sexo del encargado
                $resultado = [];

                if ($encargado->sexo == 1) {
                    // Encargado es hombre: Hombres primero, luego Mujeres
                    if (!empty($hombres)) {
                        $resultado[] = [
                            'id' => 'section_hombres',
                            'name' => '--- Hombres ---',
                            'display_text' => '--- Hombres ---',
                            'sexo' => null,
                            'is_section' => true
                        ];
                        foreach ($hombres as $hombre) {
                            $resultado[] = $hombre;
                        }
                    }

                    if (!empty($mujeres)) {
                        $resultado[] = [
                            'id' => 'section_mujeres',
                            'name' => '--- Mujeres ---',
                            'display_text' => '--- Mujeres ---',
                            'sexo' => null,
                            'is_section' => true
                        ];
                        foreach ($mujeres as $mujer) {
                            $resultado[] = $mujer;
                        }
                    }
                } else {
                    // Encargado es mujer: Mujeres primero, luego Hombres
                    if (!empty($mujeres)) {
                        $resultado[] = [
                            'id' => 'section_mujeres',
                            'name' => '--- Mujeres ---',
                            'display_text' => '--- Mujeres ---',
                            'sexo' => null,
                            'is_section' => true
                        ];
                        foreach ($mujeres as $mujer) {
                            $resultado[] = $mujer;
                        }
                    }

                    if (!empty($hombres)) {
                        $resultado[] = [
                            'id' => 'section_hombres',
                            'name' => '--- Hombres ---',
                            'display_text' => '--- Hombres ---',
                            'sexo' => null,
                            'is_section' => true
                        ];
                        foreach ($hombres as $hombre) {
                            $resultado[] = $hombre;
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'data' => $resultado
                ]);
            } else {
                // Para otras partes: solo mismo sexo, ordenado por fecha
                foreach ($usuarios as $usuario) {
                    // Si se está editando y este usuario es el ayudante actual, usar la información del registro editando
                    if ($parteProgramaEditandoId && $ayudanteEditandoId && $usuario->id == $ayudanteEditandoId && $parteEditandoInfo) {
                        // Usar la información del registro que se está editando
                        $fechaTexto = \Carbon\Carbon::parse($parteEditandoInfo->fecha)->format('d/m/Y');
                        $parteTexto = $parteEditandoInfo->abreviacion;
                        $tipoTexto = 'AY'; // Siempre ayudante para el campo Ayudante
                        $parteProgramaIdToShow = $parteProgramaEditandoId;
                    } else {
                        // Usar el historial normal para usuarios que no son el ayudante actual
                        $fechaTexto = $usuario->ultima_fecha
                            ? \Carbon\Carbon::parse($usuario->ultima_fecha)->format('d/m/Y')
                            : 'Primera vez';

                        $parteTexto = $usuario->ultima_parte_abreviacion ?? '__';
                        $tipoTexto = $usuario->ultima_fecha ? ($usuario->tipo_participacion ?? 'AY') : '__'; // Si es primera vez, tipo es "__"
                        $parteProgramaIdToShow = $usuario->partes_programa_id;
                    }

                    // Usar el asignacion_id del historial del usuario, o el de la parte actual si no tiene historial
                    $asignacionIdToShow = $usuario->historial_asignacion_id ?? $asignacionId;

                    $displayText = $fechaTexto . '|' . $parteTexto . '|' . $tipoTexto . '|' . $usuario->name;

                    $usuariosFormateados[] = [
                        'id' => $usuario->id,
                        'name' => $usuario->name,
                        'display_text' => $displayText,
                        'sexo' => $usuario->sexo,
                        'partes_programa_id' => $parteProgramaIdToShow,
                        'asignacion_id' => $asignacionIdToShow
                    ];
                }

                // Ordenar por fecha (Primera vez primero, luego más antiguos primero)
                usort($usuariosFormateados, function($a, $b) {
                    $fechaA = substr($a['display_text'], 0, 11); // Incluir "Primera vez"
                    $fechaB = substr($b['display_text'], 0, 11);

                    // Si alguno es "Primera vez", debe ir primero
                    if ($fechaA === 'Primera vez' && $fechaB !== 'Primera vez') {
                        return -1;
                    }
                    if ($fechaB === 'Primera vez' && $fechaA !== 'Primera vez') {
                        return 1;
                    }
                    if ($fechaA === 'Primera vez' && $fechaB === 'Primera vez') {
                        return 0;
                    }

                    // Si ninguno es "Primera vez", ordenar por fecha
                    return strcmp($fechaA, $fechaB);
                });
            }

            return response()->json([
                'success' => true,
                'data' => $usuariosFormateados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los ayudantes: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Mover una parte hacia arriba en el orden
     */
    public function moveUp($id)
    {
        try {
            $currentUser = auth()->user();
            //No pernmite ver programas si $currentUser->perfil no es 3
            if ($currentUser->perfil != 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para acceder a esta sección.'
                ], 403);
            }

            $partePrograma = PartePrograma::with('parte')->findOrFail($id);

            // Buscar la parte anterior en el mismo programa y misma sección
            $parteAnterior = PartePrograma::join('partes_seccion as ps', 'partes_programa.parte_id', '=', 'ps.id')
                ->where('partes_programa.programa_id', $partePrograma->programa_id)
                ->where('ps.seccion_id', $partePrograma->parte->seccion_id)
                ->where('partes_programa.orden', '<', $partePrograma->orden)
                ->orderBy('partes_programa.orden', 'desc')
                ->select('partes_programa.*')
                ->first();

            if ($parteAnterior) {
                // Intercambiar los órdenes
                $ordenTemp = $partePrograma->orden;
                $partePrograma->orden = $parteAnterior->orden;
                $parteAnterior->orden = $ordenTemp;

                $partePrograma->modificador_id = Auth::id();
                $parteAnterior->modificador_id = Auth::id();

                $partePrograma->save();
                $parteAnterior->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Parte movida hacia arriba exitosamente.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede mover más arriba.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mover la parte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mover una parte hacia abajo en el orden
     */
    public function moveDown($id)
    {
        try {
            $currentUser = auth()->user();
            //No pernmite ver programas si $currentUser->perfil no es 3
            if ($currentUser->perfil != 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para acceder a esta sección.'
                ], 403);
            }
            $partePrograma = PartePrograma::with('parte')->findOrFail($id);

            // Buscar la parte siguiente en el mismo programa y misma sección
            $parteSiguiente = PartePrograma::join('partes_seccion as ps', 'partes_programa.parte_id', '=', 'ps.id')
                ->where('partes_programa.programa_id', $partePrograma->programa_id)
                ->where('ps.seccion_id', $partePrograma->parte->seccion_id)
                ->where('partes_programa.orden', '>', $partePrograma->orden)
                ->orderBy('partes_programa.orden', 'asc')
                ->select('partes_programa.*')
                ->first();

            if ($parteSiguiente) {
                // Intercambiar los órdenes
                $ordenTemp = $partePrograma->orden;
                $partePrograma->orden = $parteSiguiente->orden;
                $parteSiguiente->orden = $ordenTemp;

                $partePrograma->modificador_id = Auth::id();
                $parteSiguiente->modificador_id = Auth::id();

                $partePrograma->save();
                $parteSiguiente->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Parte movida hacia abajo exitosamente.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede mover más abajo.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mover la parte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ayudantes basados solo en la parte de la sección seleccionada
     */
    public function getAyudantesByParte($parteId, Request $request)
    {
        try {
            $user = Auth::user();

            // Verificar que el usuario autenticado tenga perfil=3 (coordinador)
            if ($user->perfil != 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para acceder a esta información.'
                ], 403);
            }

            // Obtener usuarios únicos de la misma congregación que pueden ser ayudantes
            $usuariosQuery = DB::table('users as u')
                ->join('asignaciones_users as au', 'u.id', '=', 'au.user_id')
                ->join('asignaciones as a', 'au.asignacion_id', '=', 'a.id')
                ->join('partes_seccion as ps', 'a.id', '=', 'ps.asignacion_id')
                ->where('ps.id', $parteId)
                ->where('u.congregacion', $user->congregacion)
                ->where('u.estado', 1);

            $usuariosBase = $usuariosQuery->select('u.id', 'u.name', 'u.sexo')
                ->distinct()
                ->get();

            // Obtener el ID de la parte programa que se está editando
            $parteProgramaEditandoId = $request->query('editing_id');
            $fechaEditando = null;
            $ayudanteEditandoId = null;
            $parteEditandoInfo = null;

            // Si hay una parte editándose, obtener su información completa
            if ($parteProgramaEditandoId) {
                $parteEditandoInfo = DB::table('partes_programa as pp')
                    ->join('programas as prog', 'pp.programa_id', '=', 'prog.id')
                    ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                    ->where('pp.id', $parteProgramaEditandoId)
                    ->select('prog.fecha', 'pp.ayudante_id', 'ps.abreviacion', 'pp.id as partes_programa_id')
                    ->first();

                if ($parteEditandoInfo) {
                    $fechaEditando = $parteEditandoInfo->fecha;
                    $ayudanteEditandoId = $parteEditandoInfo->ayudante_id;
                }
            }

            // Obtener historial de participaciones para cada usuario
            $usuarios = collect();
            foreach ($usuariosBase as $usuario) {
                // Buscar la participación más reciente en la segunda sección como encargado O ayudante
                $query = DB::table('partes_programa as pp')
                    ->join('programas as prog', 'pp.programa_id', '=', 'prog.id')
                    ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                    ->where('ps.seccion_id', 2)
                    ->where(function($subQuery) use ($usuario) {
                        $subQuery->where('pp.encargado_id', $usuario->id)
                                ->orWhere('pp.ayudante_id', $usuario->id);
                    });

                // Excluir la fecha de la parte programa que se está editando
                if ($fechaEditando) {
                    //$query->where('prog.fecha', '!=', $fechaEditando);
                }

                $ultimaParticipacion = $query->select(
                        'prog.fecha',
                        'ps.abreviacion',
                        'pp.id as partes_programa_id',
                        DB::raw('CASE
                            WHEN pp.encargado_id = ' . $usuario->id . ' THEN "ES"
                            WHEN pp.ayudante_id = ' . $usuario->id . ' THEN "AY"
                            ELSE "AY"
                        END as tipo_participacion')
                    )
                    ->orderBy('prog.fecha', 'desc')
                    ->first();

                $usuarios->push((object)[
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'sexo' => $usuario->sexo,
                    'ultima_fecha' => $ultimaParticipacion ? $ultimaParticipacion->fecha : null,
                    'ultima_parte_abreviacion' => $ultimaParticipacion ? $ultimaParticipacion->abreviacion : null,
                    'partes_programa_id' => $ultimaParticipacion ? $ultimaParticipacion->partes_programa_id : null,
                    'tipo_participacion' => $ultimaParticipacion ? $ultimaParticipacion->tipo_participacion : 'AY'
                ]);
            }

            // Formatear los datos
            $usuariosFormateados = [];
            foreach ($usuarios as $usuario) {
                $fechaTexto = $usuario->ultima_fecha
                    ? \Carbon\Carbon::parse($usuario->ultima_fecha)->format('d/m/Y')
                    : 'Primera vez';

                $parteTexto = $usuario->ultima_parte_abreviacion ?? '__';
                $tipoTexto = $usuario->ultima_fecha ? ($usuario->tipo_participacion ?? 'AY') : '__'; // Si es primera vez, tipo es "__"

                $displayText = $fechaTexto . '|' . $parteTexto . '|' . $tipoTexto . '|' . $usuario->name;

                $usuariosFormateados[] = [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'display_text' => $displayText,
                    'sexo' => $usuario->sexo,
                    'partes_programa_id' => $usuario->partes_programa_id
                ];
            }

            // Ordenar por fecha (Primera vez primero, luego más antiguos primero)
            usort($usuariosFormateados, function($a, $b) {
                $fechaA = substr($a['display_text'], 0, 11); // Incluir "Primera vez"
                $fechaB = substr($b['display_text'], 0, 11);

                // Si alguno es "Primera vez", debe ir primero
                if ($fechaA === 'Primera vez' && $fechaB !== 'Primera vez') {
                    return -1;
                }
                if ($fechaB === 'Primera vez' && $fechaA !== 'Primera vez') {
                    return 1;
                }
                if ($fechaA === 'Primera vez' && $fechaB === 'Primera vez') {
                    return 0;
                }

                // Si ninguno es "Primera vez", ordenar por fecha
                return strcmp($fechaA, $fechaB);
            });

            return response()->json([
                'success' => true,
                'data' => $usuariosFormateados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los ayudantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar los sexos de dos usuarios (encargado y ayudante)
     */
    public function verificarSexosUsuarios(Request $request)
    {
        try {
            $user = Auth::user();

            // Verificar que el usuario autenticado tenga perfil=3 (coordinador)
            if ($user->perfil != 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para acceder a esta información.'
                ], 403);
            }

            $encargadoId = $request->get('encargado_id');
            $ayudanteId = $request->get('ayudante_id');

            if (!$encargadoId || !$ayudanteId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Se requieren ambos IDs de usuarios.'
                ], 400);
            }

            // Obtener el sexo de ambos usuarios
            $encargado = User::select('sexo')->where('id', $encargadoId)->first();
            $ayudante = User::select('sexo')->where('id', $ayudanteId)->first();

            if (!$encargado || !$ayudante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uno o ambos usuarios no fueron encontrados.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'encargado_sexo' => $encargado->sexo,
                'ayudante_sexo' => $ayudante->sexo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar los sexos de los usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener encargados basados en el parte_id implementando SQL con UNION
     * Para perfil=3 coordinador con filtro de congregación y parte dinámica
     */
    public function getEncargadosByPartePrograma($parteId, Request $request)
    {
        try {
            $user = Auth::user();

            // Verificar que el usuario autenticado tenga perfil=3 (coordinador)
            if ($user->perfil != 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para acceder a esta información.'
                ], 403);
            }

            // Primera parte del UNION: usuarios que nunca han participado
            $usuariosPrimeraVez = DB::select("SELECT
                'Primera vez' as fecha,
                '__' as abreviacion_parte,
                '__' as sala_abreviacion,
                u.name,
                u.id
                FROM users u
                INNER JOIN asignaciones_users au ON au.user_id = u.id
                INNER JOIN asignaciones a ON a.id = au.asignacion_id
                INNER JOIN partes_seccion ps ON ps.asignacion_id = a.id
                LEFT JOIN (SELECT encargado_id,parte_id FROM partes_programa WHERE parte_id = ?) pp ON pp.encargado_id = u.id
                WHERE pp.encargado_id IS NULL
                    AND ps.id = ?
                    AND u.congregacion = ?
                    AND u.estado = 1
            ", [$parteId,$parteId,$user->congregacion]);

            // Segunda parte del UNION: usuarios con historial de participación
            $usuariosConHistorial = DB::select("SELECT max(p.fecha) as fecha_raw,
                ps.abreviacion as abreviacion_parte,
                        s.abreviacion as sala_abreviacion,
                        u.name,
                        u.id
                FROM partes_programa pp
                INNER JOIN programas p ON p.id = pp.programa_id
                INNER JOIN partes_seccion ps ON pp.parte_id = ps.id
                INNER JOIN salas s ON pp.sala_id = s.id
                INNER JOIN users u ON u.id = pp.encargado_id OR u.id = pp.ayudante_id
                WHERE pp.parte_id = ?
                    AND u.congregacion = ?
                    AND u.estado = 1
                GROUP BY u.id
                ORDER BY fecha_raw ASC
            ", [$parteId, $user->congregacion]);

            // Combinar ambos resultados
            $usuarios = array_merge($usuariosPrimeraVez, $usuariosConHistorial);

            // Formatear los datos para el select2
            $usuariosFormateados = array_map(function($usuario) {
                // Formatear la fecha si no es "Primera vez"
                if (isset($usuario->fecha_raw) && $usuario->fecha_raw) {
                    // Convertir la fecha a formato dd-mm-yyyy
                    $fechaFormateada = \Carbon\Carbon::parse($usuario->fecha_raw)->format('d-m-Y');
                    $fechaDisplay = $fechaFormateada;
                } else {
                    $fechaFormateada = isset($usuario->fecha) ? $usuario->fecha : 'Primera vez';
                    $fechaDisplay = $fechaFormateada;
                }

                // Obtener la abreviación de la sala
                $salaAbreviacion = isset($usuario->sala_abreviacion) ? $usuario->sala_abreviacion : '__';

                return [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'display_text' => $fechaDisplay . '|' . $usuario->abreviacion_parte . '|' . $usuario->name,
                    'fecha' => $fechaDisplay,
                    'sala_abreviacion' => $salaAbreviacion,
                    'parte_abreviacion' => $usuario->abreviacion_parte,
                    'ultima_fecha' => $fechaDisplay === 'Primera vez' ? null : (isset($usuario->fecha_raw) ? $usuario->fecha_raw : null)
                ];
            }, $usuarios);

            // Ordenar: "Primera vez" primero, luego por fecha más antigua
            usort($usuariosFormateados, function($a, $b) {
                if ($a['fecha'] === 'Primera vez' && $b['fecha'] !== 'Primera vez') {
                    return -1;
                }
                if ($b['fecha'] === 'Primera vez' && $a['fecha'] !== 'Primera vez') {
                    return 1;
                }
                if ($a['fecha'] === 'Primera vez' && $b['fecha'] === 'Primera vez') {
                    return 0;
                }

                // Convertir fechas para comparación
                $fechaA = \DateTime::createFromFormat('d-m-Y', $a['fecha']);
                $fechaB = \DateTime::createFromFormat('d-m-Y', $b['fecha']);

                return $fechaA <=> $fechaB;
            });

            return response()->json([
                'success' => true,
                'data' => $usuariosFormateados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los encargados: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEncargadosByParteProgramaSmm($parteId, Request $request)
    {
        try {
            $user = Auth::user();

            // Verificar que el usuario autenticado tenga perfil=3 (coordinador)
            if ($user->perfil != 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para acceder a esta información.'
                ], 403);
            }

            // Primera parte del UNION: usuarios que nunca han participado
            $usuariosPrimeraVez = DB::select("SELECT
                    'Primera vez' as fecha,
                    '__' as abreviacion_parte,
                    '__' as sala_abreviacion,
                    u.name,
                    '__' as tipo,
                    au.asignacion_id AS as1,
                    u.id
                FROM users u
                INNER JOIN asignaciones_users au ON au.user_id = u.id
                INNER JOIN partes_seccion ps ON ps.asignacion_id = au.asignacion_id
                LEFT JOIN (SELECT pp.encargado_id,pp.ayudante_id,pp.parte_id,ps.asignacion_id
                            FROM partes_programa pp
                            INNER JOIN partes_seccion ps ON ps.id=pp.parte_id
                            ) spp ON (spp.encargado_id = u.id or spp.ayudante_id = u.id) AND spp.asignacion_id = ps.asignacion_id
                WHERE (spp.encargado_id IS NULL and spp.ayudante_id IS NULL )
                    AND ps.id= ?
                    AND u.congregacion = ?
                    AND u.estado = 1
            ", [$parteId,$user->congregacion]);

            // Segunda parte del UNION: usuarios con historial de participación
            $usuariosConHistorial = DB::select("SELECT max(p.fecha) as fecha_raw,
                    ps.abreviacion as abreviacion_parte,
                    s.abreviacion as sala_abreviacion,
                    u.name,
                    CASE WHEN pp.encargado_id=u.id THEN 'ES' ELSE 'AY' END AS tipo,
                    u.id
                FROM partes_programa pp
                INNER JOIN programas p ON p.id = pp.programa_id
                INNER JOIN partes_seccion ps ON pp.parte_id = ps.id
                INNER JOIN salas s ON pp.sala_id = s.id
                INNER JOIN users u ON u.id = pp.encargado_id OR u.id = pp.ayudante_id
                INNER JOIN (SELECT asignacion_id FROM partes_seccion ps WHERE ps.id = ?) pa ON pa.asignacion_id = ps.asignacion_id
                WHERE
                    u.congregacion = ?
                    AND u.estado = 1
                GROUP BY u.id
                ORDER BY fecha_raw ASC
            ", [$parteId, $user->congregacion]);

            // Combinar ambos resultados
            $usuarios = array_merge($usuariosPrimeraVez, $usuariosConHistorial);

            // Formatear los datos para el select2
            $usuariosFormateados = array_map(function($usuario) {
                // Formatear la fecha si no es "Primera vez"
                if (isset($usuario->fecha_raw) && $usuario->fecha_raw) {
                    // Convertir la fecha a formato dd-mm-yyyy
                    $fechaFormateada = \Carbon\Carbon::parse($usuario->fecha_raw)->format('d-m-Y');
                    $fechaDisplay = $fechaFormateada;
                } else {
                    $fechaFormateada = isset($usuario->fecha) ? $usuario->fecha : 'Primera vez';
                    $fechaDisplay = $fechaFormateada;
                }

                // Obtener la abreviación de la sala
                $salaAbreviacion = isset($usuario->sala_abreviacion) ? $usuario->sala_abreviacion : '__';

                return [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'display_text' => $fechaDisplay. '|' . $salaAbreviacion . '|' . $usuario->abreviacion_parte . '|' . $usuario->tipo . '|' . $usuario->name,
                    'fecha' => $fechaDisplay,
                    'sala_abreviacion' => $salaAbreviacion,
                    'parte_abreviacion' => $usuario->abreviacion_parte,
                    'ultima_fecha' => $fechaDisplay === 'Primera vez' ? null : (isset($usuario->fecha_raw) ? $usuario->fecha_raw : null)
                ];
            }, $usuarios);

            // Ordenar: "Primera vez" primero, luego por fecha más antigua
            usort($usuariosFormateados, function($a, $b) {
                if ($a['fecha'] === 'Primera vez' && $b['fecha'] !== 'Primera vez') {
                    return -1;
                }
                if ($b['fecha'] === 'Primera vez' && $a['fecha'] !== 'Primera vez') {
                    return 1;
                }
                if ($a['fecha'] === 'Primera vez' && $b['fecha'] === 'Primera vez') {
                    return 0;
                }

                // Convertir fechas para comparación
                $fechaA = \DateTime::createFromFormat('d-m-Y', $a['fecha']);
                $fechaB = \DateTime::createFromFormat('d-m-Y', $b['fecha']);

                return $fechaA <=> $fechaB;
            });

            return response()->json([
                'success' => true,
                'data' => $usuariosFormateados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los encargados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuarios que han participado en partes_programa como encargado o ayudante
     * con las condiciones específicas requeridas
     */
    public function getUsuariosParticipantesPrograma()
    {
        try {
            $user = Auth::user();

            // Debug logging
            \Log::info('=== getUsuariosParticipantesPrograma DEBUG ===');
            \Log::info('User ID: ' . $user->id);
            \Log::info('Congregacion: ' . $user->congregacion);

            // Consulta con todas las relaciones especificadas
            $usuariosConParticipacion = DB::table('users as u')
                ->join('asignaciones_users as au', 'u.id', '=', 'au.user_id')
                ->join('partes_seccion as ps', 'ps.asignacion_id', '=', 'au.asignacion_id')
                ->join('partes_programa as pp', 'pp.parte_id', '=', 'ps.id')
                ->join('programas as prog', 'prog.id', '=', 'pp.programa_id')
                ->where('u.estado', 1)
                ->where('u.congregacion', $user->congregacion)
                ->where(function($query) {
                    $query->whereColumn('pp.encargado_id', 'u.id')
                          ->orWhereColumn('pp.ayudante_id', 'u.id');
                })
                ->select(
                    'u.id',
                    'u.name',
                    'u.sexo',
                    DB::raw('MAX(prog.fecha) as ultima_fecha'),
                    DB::raw('(SELECT ps2.abreviacion
                              FROM partes_programa pp2
                              INNER JOIN partes_seccion ps2 ON pp2.parte_id = ps2.id
                              INNER JOIN programas prog2 ON pp2.programa_id = prog2.id
                              WHERE (pp2.encargado_id = u.id OR pp2.ayudante_id = u.id)
                              ORDER BY prog2.fecha DESC
                              LIMIT 1) as parte_abreviacion'),
                    DB::raw('CASE
                        WHEN pp.encargado_id = u.id THEN "encargado"
                        WHEN pp.ayudante_id = u.id THEN "ayudante"
                        ELSE "encargado"
                    END as tipo_ultima_participacion')
                )
                ->groupBy('u.id', 'u.name', 'u.sexo')
                ->get();

            \Log::info('Usuarios con participación encontrados: ' . $usuariosConParticipacion->count());

            // Obtener todos los usuarios activos de la misma congregación para comparar
            $todosUsuarios = User::where('estado', 1)
                ->where('congregacion', $user->congregacion)
                ->select('id', 'name', 'sexo')
                ->get();

            \Log::info('Total usuarios activos de la congregación: ' . $todosUsuarios->count());

            // Obtener IDs de usuarios que ya tienen participación
            $idsConParticipacion = $usuariosConParticipacion->pluck('id')->toArray();

            // Usuarios sin participación
            $usuariosSinParticipacion = $todosUsuarios->whereNotIn('id', $idsConParticipacion);

            \Log::info('Usuarios sin participación: ' . $usuariosSinParticipacion->count());

            // Convertir usuarios con participación a array
            $usuariosConParticipacionArray = $usuariosConParticipacion->map(function ($usuario) {
                return [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'sexo' => $usuario->sexo,
                    'ultima_fecha' => $usuario->ultima_fecha,
                    'parte_abreviacion' => $usuario->parte_abreviacion,
                    'tipo_ultima_participacion' => $usuario->tipo_ultima_participacion
                ];
            });

            // Convertir usuarios sin participación a array
            $usuariosSinParticipacionArray = $usuariosSinParticipacion->map(function ($usuario) {
                return [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'sexo' => $usuario->sexo,
                    'ultima_fecha' => 'Primera vez',
                    'parte_abreviacion' => '__',
                    'tipo_ultima_participacion' => 'encargado'
                ];
            });

            // Combinar ambos grupos
            $usuarios = $usuariosConParticipacionArray->concat($usuariosSinParticipacionArray);

            // Ordenar: primero por fecha (los que tienen fecha real), luego por nombre
            $usuarios = $usuarios->sortBy([
                ['ultima_fecha', 'desc'],  // Fechas reales primero (desc)
                ['name', 'asc']            // Luego por nombre
            ])->values();

            \Log::info('Total usuarios combinados: ' . $usuarios->count());

            return response()->json([
                'success' => true,
                'data' => $usuarios
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en getUsuariosParticipantesPrograma: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los usuarios participantes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAyudantesByPartePrograma($parteId)
    {
        try {
            $user = Auth::user();

            // Verificar que el usuario autenticado tenga perfil=3 (coordinador)
            if ($user->perfil != 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para acceder a esta información.'
                ], 403);
            }

            $editingId = request()->get('editing_id');
            $encargadoId = request()->get('encargado_id');

            // Obtener información de la parte para verificar el tipo y obtener asignacion_id
            $parteSeccion = DB::table('partes_seccion')->where('id', $parteId)->first();
            if (!$parteSeccion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parte de sección no encontrada.'
                ], 404);
            }

            $esParteAmbosSexos = ($parteSeccion->tipo == 3);
            $asignacionId = $parteSeccion->asignacion_id;

            // Obtener usuarios únicos de la misma congregación que pueden ser ayudantes
            // Usar la misma lógica que getAyudantesByEncargadoAndParte para mantener consistencia
            $usuariosQuery = DB::table('users as u')
                ->join('asignaciones_users as au', 'u.id', '=', 'au.user_id')
                ->join('asignaciones as a', 'au.asignacion_id', '=', 'a.id')
                ->join('partes_seccion as ps', 'a.id', '=', 'ps.asignacion_id')
                ->where('u.congregacion', $user->congregacion)
                ->where('u.estado', 1)
                ->where('au.asignacion_id', $asignacionId)
                ->whereNotIn('u.id', [$encargadoId]); // Excluir el encargado actual
            // Obtener el sexo del encargado seleccionado para determinar el orden
            $encargadoSexo = null;
            if ($encargadoId) {
                $encargadoSexo = DB::table('users')->where('id', $encargadoId)->value('sexo');
            }

            // Aplicar filtros de sexo según las reglas
            if ($esParteAmbosSexos) {
                // Para parte tipo=3: cargar todos los usuarios que pueden participar
                $usuariosQuery->whereIn('u.sexo', [1, 2]); // Ambos sexos
            } else {
                // Para otras partes: solo usuarios del mismo sexo que el encargado
                $usuariosQuery->where('u.sexo', $encargadoSexo);
            }

            $usuariosBase = $usuariosQuery->select('u.id', 'u.name', 'u.sexo')
                ->distinct()
                ->get();

            // Para cada usuario, obtener su última participación
            $usuarios = collect();
            foreach ($usuariosBase as $usuario) {
                $query = DB::table('partes_programa as pp')
                    ->join('programas as prog', 'pp.programa_id', '=', 'prog.id')
                    ->join('partes_seccion as ps', 'pp.parte_id', '=', 'ps.id')
                    ->join('salas as s', 'pp.sala_id', '=', 's.id')
                    ->where(function($query) use ($usuario) {
                        $query->where('pp.encargado_id', $usuario->id)
                              ->orWhere('pp.ayudante_id', $usuario->id);
                    })
                    ->where('ps.asignacion_id', $asignacionId); // Solo segunda sección

                // Excluir la parte que se está editando si corresponde
                if ($editingId) {
                    $query->where('pp.id', '!=', $editingId);
                }

                $ultimaParticipacion = $query->select(
                        'prog.fecha',
                        'ps.abreviacion',
                        's.abreviacion as sala_abreviacion',
                        'ps.asignacion_id',
                        'pp.id as partes_programa_id',
                        DB::raw('CASE
                            WHEN pp.encargado_id = ' . $usuario->id . ' THEN "ES"
                            WHEN pp.ayudante_id = ' . $usuario->id . ' THEN "AY"
                            ELSE "AY"
                        END as tipo_participacion')
                    )
                    ->orderBy('prog.fecha', 'desc')
                    ->first();

                // Formatear datos
                if ($ultimaParticipacion) {
                    $fechaTexto = \Carbon\Carbon::parse($ultimaParticipacion->fecha)->format('d/m/Y');
                    $parteTexto = $ultimaParticipacion->abreviacion;
                    $salaTexto = $ultimaParticipacion->sala_abreviacion;
                    $tipoTexto = $ultimaParticipacion->tipo_participacion;
                    $parteProgramaIdToShow = $ultimaParticipacion->partes_programa_id;
                    $asignacionIdToShow = $ultimaParticipacion->asignacion_id;
                } else {
                    $fechaTexto = 'Primera vez';
                    $parteTexto = '__';
                    $salaTexto = '__';
                    $tipoTexto = '__';
                    $parteProgramaIdToShow = null;
                    $asignacionIdToShow = $asignacionId;
                }

                $displayText = $fechaTexto . '|' . $salaTexto . '|' . $parteTexto . '|' . $usuario->name;

                $usuarios->push((object)[
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'sexo' => $usuario->sexo,
                    'display_text' => $displayText,
                    'ultima_fecha' => $ultimaParticipacion ? $ultimaParticipacion->fecha : null,
                    'partes_programa_id' => $parteProgramaIdToShow,
                    'asignacion_id' => $asignacionIdToShow
                ]);
            }

            // Ordenar por fecha (Primera vez primero, luego más antiguos primero)
            $usuariosOrdenados = $usuarios->sort(function($a, $b) {
                $fechaA = substr($a->display_text, 0, 11); // Incluir "Primera vez"
                $fechaB = substr($b->display_text, 0, 11);

                // Si alguno es "Primera vez", debe ir primero
                if ($fechaA === 'Primera vez' && $fechaB !== 'Primera vez') {
                    return -1;
                }
                if ($fechaB === 'Primera vez' && $fechaA !== 'Primera vez') {
                    return 1;
                }
                if ($fechaA === 'Primera vez' && $fechaB === 'Primera vez') {
                    return 0;
                }

                // Si ninguno es "Primera vez", ordenar por fecha
                return strcmp($fechaA, $fechaB);
            });

            // Si es parte tipo 3 (ambos sexos), organizar por género
            if ($esParteAmbosSexos) {
                // Separar por género
                $hombres = $usuariosOrdenados->filter(function($usuario) {
                    return $usuario->sexo == 1;
                })->values();

                $mujeres = $usuariosOrdenados->filter(function($usuario) {
                    return $usuario->sexo == 2;
                })->values();

                // Agregar metadatos para identificar las secciones en el frontend
                $resultado = [];

                // Ordenar secciones según el sexo del encargado
                if ($encargadoSexo == 1) {
                    // Encargado es hombre: Hombres primero, luego Mujeres
                    if ($hombres->count() > 0) {
                        $resultado[] = [
                            'id' => 'section_hombres',
                            'name' => '--- Hombres ---',
                            'display_text' => '--- Hombres ---',
                            'sexo' => null,
                            'is_section' => true
                        ];
                        foreach ($hombres as $hombre) {
                            $resultado[] = $hombre;
                        }
                    }

                    if ($mujeres->count() > 0) {
                        $resultado[] = [
                            'id' => 'section_mujeres',
                            'name' => '--- Mujeres ---',
                            'display_text' => '--- Mujeres ---',
                            'sexo' => null,
                            'is_section' => true
                        ];
                        foreach ($mujeres as $mujer) {
                            $resultado[] = $mujer;
                        }
                    }
                } else {
                    // Encargado es mujer o no se especificó: Mujeres primero, luego Hombres
                    if ($mujeres->count() > 0) {
                        $resultado[] = [
                            'id' => 'section_mujeres',
                            'name' => '--- Mujeres ---',
                            'display_text' => '--- Mujeres ---',
                            'sexo' => null,
                            'is_section' => true
                        ];
                        foreach ($mujeres as $mujer) {
                            $resultado[] = $mujer;
                        }
                    }

                    if ($hombres->count() > 0) {
                        $resultado[] = [
                            'id' => 'section_hombres',
                            'name' => '--- Hombres ---',
                            'display_text' => '--- Hombres ---',
                            'sexo' => null,
                            'is_section' => true
                        ];
                        foreach ($hombres as $hombre) {
                            $resultado[] = $hombre;
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'data' => $resultado,
                    'has_gender_sections' => true
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $usuariosOrdenados->values()->all(),
                'has_gender_sections' => false
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener ayudantes por parte programa: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar usuarios ayudantes'
            ], 500);
        }
    }
}
