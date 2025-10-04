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
        // Verificar que el usuario pueda acceder al menú de administración (perfil 1 y 2)
        if (!Auth::user()->canAccessAdminMenu()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $grupos = Grupo::with('congregacion')->orderBy('nombre')->get();
        $congregaciones = Congregacion::where('estado', 1)->orderBy('nombre')->get();
        
        return view('grupos.index', compact('grupos', 'congregaciones'));
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
        // Verificar que solo los administradores puedan crear
        if (!Auth::user()->isAdmin()) {
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
            'estado.in' => 'El estado debe ser Activo o Inactivo.'
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
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.'
            ], 403);
        }

        try {
            $grupo = Grupo::with('congregacion')->findOrFail($id);
            
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
        // Verificar que solo los administradores puedan editar
        if (!Auth::user()->isAdmin()) {
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
            'estado.in' => 'El estado debe ser Activo o Inactivo.'
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
        // Verificar que solo los administradores puedan eliminar
        if (!Auth::user()->isAdmin()) {
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
