<?php

namespace App\Http\Controllers;

use App\Models\Sexo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SexoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sexos = Sexo::with(['creador', 'modificador'])->get();
        return view('sexo.index', compact('sexos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:sexo',
            'descripcion' => 'nullable|string|max:500',
            'estado' => 'required|integer|in:0,1'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'nombre.unique' => 'Ya existe un registro con este nombre.',
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
            $sexo = Sexo::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registro creado exitosamente.',
                'sexo' => $sexo->load(['creador', 'modificador']),
                'audit_info' => $sexo->getAuditInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $sexo = Sexo::findOrFail($id);
            return response()->json([
                'success' => true,
                'sexo' => $sexo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registro no encontrado.'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $sexo = Sexo::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:sexo,nombre,' . $id,
                'descripcion' => 'nullable|string|max:500',
                'estado' => 'required|integer|in:0,1'
            ], [
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
                'nombre.unique' => 'Ya existe un registro con este nombre.',
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

            $sexo->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registro actualizado exitosamente.',
                'sexo' => $sexo->load(['creador', 'modificador']),
                'audit_info' => $sexo->getAuditInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $sexo = Sexo::findOrFail($id);
            $sexo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro: ' . $e->getMessage()
            ], 500);
        }
    }
}
