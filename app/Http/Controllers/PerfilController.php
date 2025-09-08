<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerfilController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perfiles = Perfil::with(['creador', 'modificador'])->get();
        return view('perfiles.index', compact('perfiles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:perfiles',
            'privilegio' => 'required|string|max:255',
            'descripcion' => 'required|string|max:500',
            'estado' => 'required|integer|in:0,1'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'nombre.unique' => 'Ya existe un perfil con este nombre.',
            'privilegio.required' => 'El privilegio es obligatorio.',
            'privilegio.max' => 'El privilegio no puede exceder 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
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
            $perfil = Perfil::create([
                'nombre' => $request->nombre,
                'privilegio' => $request->privilegio,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Perfil creado exitosamente.',
                'perfil' => $perfil->load(['creador', 'modificador']),
                'audit_info' => $perfil->getAuditInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $perfil = Perfil::findOrFail($id);
            return response()->json([
                'success' => true,
                'perfil' => $perfil
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil no encontrado.'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $perfil = Perfil::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:perfiles,nombre,' . $id,
                'privilegio' => 'required|string|max:255',
                'descripcion' => 'required|string|max:500',
                'estado' => 'required|integer|in:0,1'
            ], [
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
                'nombre.unique' => 'Ya existe un perfil con este nombre.',
                'privilegio.required' => 'El privilegio es obligatorio.',
                'privilegio.max' => 'El privilegio no puede exceder 255 caracteres.',
                'descripcion.required' => 'La descripción es obligatoria.',
                'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
                'estado.required' => 'El estado es obligatorio.',
                'estado.in' => 'El estado debe ser Activo o Inactivo.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $perfil->update([
                'nombre' => $request->nombre,
                'privilegio' => $request->privilegio,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente.',
                'perfil' => $perfil->load(['creador', 'modificador']),
                'audit_info' => $perfil->getAuditInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $perfil = Perfil::findOrFail($id);
            
            // Verificar si hay usuarios usando este perfil
            $usersWithProfile = $perfil->users()->count();
            if ($usersWithProfile > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el perfil porque hay ' . $usersWithProfile . ' usuario(s) asignado(s) a este perfil.'
                ], 422);
            }

            $perfil->delete();

            return response()->json([
                'success' => true,
                'message' => 'Perfil eliminado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el perfil: ' . $e->getMessage()
            ], 500);
        }
    }
}
