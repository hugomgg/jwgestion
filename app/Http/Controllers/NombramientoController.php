<?php

namespace App\Http\Controllers;

use App\Models\Nombramiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NombramientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $nombramientos = Nombramiento::with(['creador', 'modificador'])->get();
        return view('nombramiento.index', compact('nombramientos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:nombramiento',
            'descripcion' => 'nullable|string|max:500',
            'estado' => 'required|integer|in:0,1'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'nombre.unique' => 'Ya existe un nombramiento con este nombre.',
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
            $nombramiento = Nombramiento::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nombramiento creado exitosamente.',
                'nombramiento' => $nombramiento->load(['creador', 'modificador']),
                'audit_info' => $nombramiento->getAuditInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el nombramiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $nombramiento = Nombramiento::findOrFail($id);
            return response()->json([
                'success' => true,
                'nombramiento' => $nombramiento
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Nombramiento no encontrado.'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $nombramiento = Nombramiento::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:nombramiento,nombre,' . $id,
                'descripcion' => 'nullable|string|max:500',
                'estado' => 'required|integer|in:0,1'
            ], [
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
                'nombre.unique' => 'Ya existe un nombramiento con este nombre.',
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

            $nombramiento->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nombramiento actualizado exitosamente.',
                'nombramiento' => $nombramiento->load(['creador', 'modificador']),
                'audit_info' => $nombramiento->getAuditInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el nombramiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $nombramiento = Nombramiento::findOrFail($id);
            $nombramiento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nombramiento eliminado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el nombramiento: ' . $e->getMessage()
            ], 500);
        }
    }
}
