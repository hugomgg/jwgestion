<?php

namespace App\Http\Controllers;

use App\Models\Esperanza;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EsperanzaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $esperanzas = Esperanza::with(['creador', 'modificador'])->get();
        return view('esperanza.index', compact('esperanzas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:esperanzas',
            'descripcion' => 'nullable|string|max:500',
            'estado' => 'required|integer|in:0,1'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'nombre.unique' => 'Ya existe una esperanza con este nombre.',
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
            $esperanza = Esperanza::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Esperanza creada exitosamente.',
                'esperanza' => $esperanza->load(['creador', 'modificador']),
                'audit_info' => $esperanza->getAuditInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la esperanza: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $esperanza = Esperanza::findOrFail($id);
            return response()->json([
                'success' => true,
                'esperanza' => $esperanza
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Esperanza no encontrada.'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $esperanza = Esperanza::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:esperanzas,nombre,' . $id,
                'descripcion' => 'nullable|string|max:500',
                'estado' => 'required|integer|in:0,1'
            ], [
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
                'nombre.unique' => 'Ya existe una esperanza con este nombre.',
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

            $esperanza->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Esperanza actualizada exitosamente.',
                'esperanza' => $esperanza->load(['creador', 'modificador']),
                'audit_info' => $esperanza->getAuditInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la esperanza: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $esperanza = Esperanza::findOrFail($id);
            $esperanza->delete();

            return response()->json([
                'success' => true,
                'message' => 'Esperanza eliminada exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la esperanza: ' . $e->getMessage()
            ], 500);
        }
    }
}
