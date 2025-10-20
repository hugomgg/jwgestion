<?php

namespace App\Http\Controllers;

use App\Models\Informe;
use App\Models\User;
use App\Models\Grupo;
use App\Models\Congregacion;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InformeController extends Controller
{
    /**
     * Display a listing of the informes.
     */
    public function index()
    {
        $currentUser = auth()->user();

        // Filtrar informes según el rol del usuario
        $query = DB::table('informes as i')
            ->join('users as u', 'i.user_id', '=', 'u.id')
            ->join('grupos as g', 'i.grupo_id', '=', 'g.id')
            ->join('congregaciones as c', 'i.congregacion_id', '=', 'c.id')
            ->join('servicios as s', 'i.servicio_id', '=', 's.id')
            ->select([
                'i.id',
                'i.anio',
                'i.mes',
                'i.participa',
                'i.cantidad_estudios',
                'i.horas',
                'i.comentario',
                'i.estado',
                'u.name as usuario_nombre',
                'g.nombre as grupo_nombre',
                'c.nombre as congregacion_nombre',
                's.nombre as servicio_nombre',
                'i.created_at',
                'i.updated_at'
            ]);
        //Obtener todos los años de los informes existentes
        $anios = DB::table('informes')
            ->select(DB::raw('DISTINCT anio'))
            ->orderBy('anio', 'desc')
            ->pluck('anio')
            ->toArray();
        
        // Aplicar filtros según el rol del usuario
        if ($currentUser->isCoordinator() || $currentUser->isSubcoordinator() ||
            $currentUser->isSecretary() || $currentUser->isSubsecretary() ||
            $currentUser->isOrganizer() || $currentUser->isSuborganizer()) {
            // Solo mostrar informes de su congregación
            $query->where('i.congregacion_id', $currentUser->congregacion);
        }

        $informes = $query->where('i.estado', 1)
                         ->orderBy('i.anio', 'desc')
                         ->orderBy('i.mes', 'desc')
                         ->orderBy('u.name')
                         ->get();

        // Obtener datos para los formularios (filtros y modales)
        $usuarios = $this->getUsuariosParaFormulario($currentUser);
        $grupos = $this->getGruposParaFormulario($currentUser);
        $congregaciones = $this->getCongregacionesParaFormulario($currentUser);
        $servicios = Servicio::where('estado', 1)->get();

        return view('informes.index', compact(
            'informes',
            'usuarios',
            'grupos',
            'congregaciones',
            'servicios'
            ,'anios'
        ));
    }

    /**
     * Store a newly created informe in storage.
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();

        // Validación de datos
        $validator = Validator::make($request->all(), [
            'anio' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'mes' => 'required|integer|min:1|max:12',
            'user_id' => 'required|exists:users,id',
            'grupo_id' => 'required|exists:grupos,id',
            'servicio_id' => 'required|exists:servicios,id',
            'participa' => 'required|boolean',
            'cantidad_estudios' => 'nullable|integer|min:0',
            'horas' => 'nullable|integer|min:0',
            'comentario' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verificar que el usuario tenga permisos para crear informes
            $usuario = User::find($request->user_id);
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario especificado no existe.'
                ], 404);
            }

            if (!$this->canManageUserInforme($currentUser, $usuario)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para crear informes para este usuario.'
                ], 403);
            }

            // Verificar que no exista ya un informe para el mismo usuario, año y mes
            $existeInforme = Informe::where('user_id', $request->user_id)
                                   ->where('anio', $request->anio)
                                   ->where('mes', $request->mes)
                                   ->exists();

            if ($existeInforme) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un informe para este usuario en el año y mes especificado.'
                ], 422);
            }

            // Obtener la congregación del usuario
            $congregacionId = $usuario->congregacion;

            // Crear el informe
            $informe = Informe::create([
                'anio' => $request->anio,
                'mes' => $request->mes,
                'user_id' => $request->user_id,
                'grupo_id' => $request->grupo_id,
                'congregacion_id' => $congregacionId,
                'servicio_id' => $request->servicio_id,
                'participa' => $request->participa,
                'cantidad_estudios' => $request->cantidad_estudios ?? 0,
                'horas' => $request->horas,
                'comentario' => $request->comentario,
                'estado' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Informe creado exitosamente.',
                'informe' => $informe
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el informe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified informe.
     */
    public function show($id)
    {
        $currentUser = auth()->user();

        $informe = DB::table('informes as i')
            ->join('users as u', 'i.user_id', '=', 'u.id')
            ->join('grupos as g', 'i.grupo_id', '=', 'g.id')
            ->join('congregaciones as c', 'i.congregacion_id', '=', 'c.id')
            ->join('servicios as s', 'i.servicio_id', '=', 's.id')
            ->select([
                'i.*',
                'u.name as usuario_nombre',
                'u.nombre_completo as usuario_nombre_completo',
                'g.nombre as grupo_nombre',
                'c.nombre as congregacion_nombre',
                's.nombre as servicio_nombre'
            ])
            ->where('i.id', $id)
            ->first();

        if (!$informe) {
            return response()->json([
                'success' => false,
                'message' => 'Informe no encontrado.'
            ], 404);
        }

        // Verificar permisos
        if (!$this->canViewInforme($currentUser, $informe)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para ver este informe.'
            ], 403);
        }

        // Agregar nombres de meses
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $informe->nombre_mes = $meses[$informe->mes] ?? '';
        $informe->participa_texto = $informe->participa ? 'Sí' : 'No';

        return response()->json([
            'success' => true,
            'informe' => $informe
        ]);
    }

    /**
     * Show the form for editing the specified informe.
     */
    public function edit($id)
    {
        $currentUser = auth()->user();
        $informe = Informe::with(['usuario', 'grupo', 'congregacion', 'servicio'])->find($id);

        if (!$informe) {
            return response()->json([
                'success' => false,
                'message' => 'Informe no encontrado.'
            ], 404);
        }

        // Verificar permisos
        if (!$this->canManageUserInforme($currentUser, $informe->usuario)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para editar este informe.'
            ], 403);
        }

        // Obtener datos para los formularios
        $usuarios = $this->getUsuariosParaFormulario($currentUser);
        $grupos = $this->getGruposParaFormulario($currentUser);
        $servicios = Servicio::where('estado', 1)->get();

        return response()->json([
            'success' => true,
            'informe' => $informe,
            'usuarios' => $usuarios,
            'grupos' => $grupos,
            'servicios' => $servicios
        ]);
    }

    /**
     * Update the specified informe in storage.
     */
    public function update(Request $request, $id)
    {
        $currentUser = auth()->user();
        $informe = Informe::find($id);

        if (!$informe) {
            return response()->json([
                'success' => false,
                'message' => 'Informe no encontrado.'
            ], 404);
        }

        // Verificar permisos
        $usuario = User::find($request->user_id ?? $informe->user_id);
        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario especificado no existe.'
            ], 404);
        }

        if (!$this->canManageUserInforme($currentUser, $usuario)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para editar este informe.'
            ], 403);
        }

        // Validación de datos
        $validator = Validator::make($request->all(), [
            'anio' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'mes' => 'required|integer|min:1|max:12',
            'user_id' => 'required|exists:users,id',
            'grupo_id' => 'required|exists:grupos,id',
            'servicio_id' => 'required|exists:servicios,id',
            'participa' => 'required|boolean',
            'cantidad_estudios' => 'nullable|integer|min:0',
            'horas' => 'nullable|integer|min:0',
            'comentario' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verificar que no exista ya un informe para el mismo usuario, año y mes (excluyendo el actual)
            $existeInforme = Informe::where('user_id', $request->user_id)
                                   ->where('anio', $request->anio)
                                   ->where('mes', $request->mes)
                                   ->where('id', '!=', $id)
                                   ->exists();

            if ($existeInforme) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un informe para este usuario en el año y mes especificado.'
                ], 422);
            }

            // Actualizar el informe
            $informe->update([
                'anio' => $request->anio,
                'mes' => $request->mes,
                'user_id' => $request->user_id,
                'grupo_id' => $request->grupo_id,
                'servicio_id' => $request->servicio_id,
                'participa' => $request->participa,
                'cantidad_estudios' => $request->cantidad_estudios ?? 0,
                'horas' => $request->horas,
                'comentario' => $request->comentario,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Informe actualizado exitosamente.',
                'informe' => $informe
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el informe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified informe from storage.
     */
    public function destroy($id)
    {
        $currentUser = auth()->user();
        $informe = Informe::with('usuario')->find($id);

        if (!$informe) {
            return response()->json([
                'success' => false,
                'message' => 'Informe no encontrado.'
            ], 404);
        }

        // Verificar permisos
        if (!$this->canManageUserInforme($currentUser, $informe->usuario)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar este informe.'
            ], 403);
        }

        try {
            // Cambiar estado a deshabilitado en lugar de eliminar físicamente
            $informe->update(['estado' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Informe eliminado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el informe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si el usuario actual puede gestionar informes de un usuario específico.
     */
    private function canManageUserInforme($currentUser, $targetUser)
    {
        // Administradores y supervisores pueden gestionar cualquier informe
        if ($currentUser->isAdmin() || $currentUser->isSupervisor()) {
            return true;
        }

        // Coordinadores, secretarios y organizadores solo pueden gestionar informes de su congregación
        if ($currentUser->isCoordinator() || $currentUser->isSubcoordinator() ||
            $currentUser->isSecretary() || $currentUser->isSubsecretary() ||
            $currentUser->isOrganizer() || $currentUser->isSuborganizer()) {
            return $currentUser->congregacion == $targetUser->congregacion;
        }

        return false;
    }

    /**
     * Verificar si el usuario actual puede ver un informe específico.
     */
    private function canViewInforme($currentUser, $informe)
    {
        // Administradores y supervisores pueden ver cualquier informe
        if ($currentUser->isAdmin() || $currentUser->isSupervisor()) {
            return true;
        }

        // Coordinadores, secretarios y organizadores solo pueden ver informes de su congregación
        if ($currentUser->isCoordinator() || $currentUser->isSubcoordinator() ||
            $currentUser->isSecretary() || $currentUser->isSubsecretary() ||
            $currentUser->isOrganizer() || $currentUser->isSuborganizer()) {
            return $currentUser->congregacion == $informe->congregacion_id;
        }

        return false;
    }

    /**
     * Obtener usuarios para formularios según permisos del usuario actual.
     */
    private function getUsuariosParaFormulario($currentUser)
    {
        if ($currentUser->isAdmin() || $currentUser->isSupervisor()) {
            return User::where('estado', 1)->select('id', 'name', 'congregacion')->get();
        }

        if ($currentUser->isCoordinator() || $currentUser->isSubcoordinator() ||
            $currentUser->isSecretary() || $currentUser->isSubsecretary() ||
            $currentUser->isOrganizer() || $currentUser->isSuborganizer()) {
            return User::where('congregacion', $currentUser->congregacion)
                      ->where('estado', 1)
                      ->select('id', 'name', 'congregacion')
                      ->get();
        }

        return collect([]);
    }

    /**
     * Obtener grupos para formularios según permisos del usuario actual.
     */
    private function getGruposParaFormulario($currentUser)
    {
        if ($currentUser->isAdmin() || $currentUser->isSupervisor()) {
            return Grupo::where('estado', 1)->select('id', 'nombre', 'congregacion_id')->get();
        }

        if ($currentUser->isCoordinator() || $currentUser->isSubcoordinator() ||
            $currentUser->isSecretary() || $currentUser->isSubsecretary() ||
            $currentUser->isOrganizer() || $currentUser->isSuborganizer()) {
            return Grupo::where('congregacion_id', $currentUser->congregacion)
                       ->where('estado', 1)
                       ->select('id', 'nombre', 'congregacion_id')
                       ->get();
        }

        return collect([]);
    }

    /**
     * Obtener congregaciones para formularios según permisos del usuario actual.
     */
    private function getCongregacionesParaFormulario($currentUser)
    {
        if ($currentUser->isAdmin() || $currentUser->isSupervisor()) {
            return Congregacion::where('estado', 1)->select('id', 'nombre')->get();
        }

        if ($currentUser->isCoordinator() || $currentUser->isSubcoordinator() ||
            $currentUser->isSecretary() || $currentUser->isSubsecretary() ||
            $currentUser->isOrganizer() || $currentUser->isSuborganizer()) {
            return Congregacion::where('id', $currentUser->congregacion)
                              ->where('estado', 1)
                              ->select('id', 'nombre')
                              ->get();
        }

        return collect([]);
    }

    /**
     * Obtener usuarios filtrados por grupo (AJAX).
     */
    public function getUsersByGroup(Request $request)
    {
        $currentUser = auth()->user();
        $grupoId = $request->input('grupo_id');

        if (!$grupoId) {
            return response()->json([
                'success' => false,
                'message' => 'ID de grupo requerido'
            ], 400);
        }

        // Verificar que el grupo existe y el usuario tiene permisos
        $grupo = Grupo::find($grupoId);
        if (!$grupo) {
            return response()->json([
                'success' => false,
                'message' => 'Grupo no encontrado'
            ], 404);
        }

        // Verificar permisos según el tipo de usuario
        $query = User::where('grupo', $grupoId)->where('estado', 1);

        if ($currentUser->isAdmin() || $currentUser->isSupervisor()) {
            // Administradores y supervisores pueden ver usuarios de cualquier grupo
        } else if ($currentUser->isCoordinator() || $currentUser->isSubcoordinator() ||
                   $currentUser->isSecretary() || $currentUser->isSubsecretary() ||
                   $currentUser->isOrganizer() || $currentUser->isSuborganizer()) {
            // Solo pueden ver usuarios de su misma congregación
            $query->where('congregacion', $currentUser->congregacion);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para ver esta información'
            ], 403);
        }

        $usuarios = $query->select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'usuarios' => $usuarios
        ]);
    }
}