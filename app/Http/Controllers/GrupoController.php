<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Congregacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Verificar que el usuario pueda acceder al menú de administración o al menú de gestión de personas
        if (!Auth::user()->canAccessAdminMenu() && !Auth::user()->canAccessPeopleManagementMenu()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        // Si es Admin o Supervisor, mostrar todas las congregaciones
        // Si no, solo mostrar la congregación del usuario autenticado
        if (Auth::user()->isAdmin() || Auth::user()->isSupervisor()) {
            $congregaciones = Congregacion::where('estado', 1)->orderBy('nombre')->get();
        } else {
            $congregaciones = Congregacion::where('id', Auth::user()->congregacion)
                                         ->where('estado', 1)
                                         ->get();
        }
        
        return view('grupos.index', compact('congregaciones'));
    }

    /**
     * Get data for DataTables via AJAX
     */
    public function getData()
    {
        // Verificar que el usuario pueda acceder
        if (!Auth::user()->canAccessAdminMenu() && !Auth::user()->canAccessPeopleManagementMenu()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $grupos = Grupo::with('congregacion')->orderBy('nombre')->get();

        return response()->json([
            'data' => $grupos->map(function($grupo) {
                return [
                    'id' => $grupo->id,
                    'nombre' => $grupo->nombre,
                    'congregacion' => $grupo->congregacion ? $grupo->congregacion->nombre : 'Sin asignar',
                    'estado' => $grupo->estado,
                    'usuarios_count' => $grupo->usuarios->count(),
                    'can_modify' => Auth::user()->canModify()
                ];
            })
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Este método no se usa ya que usamos modal
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Verificar que el usuario pueda modificar (Admin, Coordinator, Secretary, Organizer)
        if (!Auth::user()->isAdmin() && !Auth::user()->canModify()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:grupos,nombre',
            'congregacion_id' => 'required|integer|exists:congregaciones,id',
            'estado' => 'required|integer|in:0,1'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe un grupo con este nombre.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'congregacion_id.required' => 'La congregación es obligatoria.',
            'congregacion_id.exists' => 'La congregación seleccionada no existe.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser Habilitado o Deshabilitado.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $grupo = Grupo::create([
                'nombre' => $request->nombre,
                'congregacion_id' => $request->congregacion_id,
                'estado' => $request->estado,
                'creador' => Auth::id(),
                'modificador' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Grupo creado exitosamente.',
                'grupo' => $grupo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el grupo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Verificar permisos
        if (!Auth::user()->isAdmin() && !Auth::user()->canAccessPeopleManagementMenu()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.'
            ], 403);
        }

        try {
            $grupo = Grupo::with('congregacion')->findOrFail($id);
            $grupo->usuarios_count = $grupo->usuarios()->count();
            return response()->json([
                'success' => true,
                'grupo' => $grupo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Grupo no encontrado.'
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Este método no se usa ya que usamos modal
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Verificar que el usuario pueda modificar (Admin, Coordinator, Secretary, Organizer)
        if (!Auth::user()->isAdmin() && !Auth::user()->canModify()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:grupos,nombre,' . $id,
            'congregacion_id' => 'required|integer|exists:congregaciones,id',
            'estado' => 'required|integer|in:0,1'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe un grupo con este nombre.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'congregacion_id.required' => 'La congregación es obligatoria.',
            'congregacion_id.exists' => 'La congregación seleccionada no existe.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser Habilitado o Deshabilitado.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $grupo = Grupo::findOrFail($id);
            
            $grupo->update([
                'nombre' => $request->nombre,
                'congregacion_id' => $request->congregacion_id,
                'estado' => $request->estado,
                'modificador' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Grupo actualizado exitosamente.',
                'grupo' => $grupo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el grupo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Verificar que el usuario pueda modificar (Admin, Coordinator, Secretary, Organizer)
        if (!Auth::user()->isAdmin() && !Auth::user()->canModify()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.'
            ], 403);
        }

        try {
            $grupo = Grupo::findOrFail($id);
            
            // Verificar si hay usuarios asignados a este grupo
            $usersCount = $grupo->usuarios()->count();
            if ($usersCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar el grupo porque tiene {$usersCount} usuario(s) asignado(s)."
                ], 400);
            }

            $grupo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Grupo eliminado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el grupo: ' . $e->getMessage()
            ], 500);
        }
    }
}
