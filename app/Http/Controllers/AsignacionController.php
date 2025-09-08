<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AsignacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $asignaciones = Asignacion::with(['creador', 'modificador'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('asignaciones.index', compact('asignaciones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:asignaciones',
            'abreviacion' => 'required|string|max:10|unique:asignaciones,abreviacion',
            'descripcion' => 'required|string|max:500',
            'estado' => 'required|integer|in:0,1'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una asignación con este nombre.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'abreviacion.required' => 'La abreviación es obligatoria.',
            'abreviacion.unique' => 'Ya existe una asignación con esta abreviación.',
            'abreviacion.max' => 'La abreviación no puede tener más de 10 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede tener más de 500 caracteres.',
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
            $asignacion = Asignacion::create([
                'nombre' => $request->nombre,
                'abreviacion' => $request->abreviacion,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asignación creada exitosamente.',
                'asignacion' => $asignacion->load(['creador', 'modificador']),
                'audit_info' => $asignacion->getAuditInfo()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la asignación. Intente nuevamente.'
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $asignacion = Asignacion::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'asignacion' => $asignacion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Asignación no encontrada.'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $asignacion = Asignacion::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nombre' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('asignaciones')->ignore($asignacion->id)
                ],
                'abreviacion' => [
                    'required',
                    'string',
                    'max:10',
                    Rule::unique('asignaciones', 'abreviacion')->ignore($asignacion->id)
                ],
                'descripcion' => 'required|string|max:500',
                'estado' => 'required|integer|in:0,1'
            ], [
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.unique' => 'Ya existe una asignación con este nombre.',
                'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
                'abreviacion.required' => 'La abreviación es obligatoria.',
                'abreviacion.unique' => 'Ya existe una asignación con esta abreviación.',
                'abreviacion.max' => 'La abreviación no puede tener más de 10 caracteres.',
                'descripcion.required' => 'La descripción es obligatoria.',
                'descripcion.max' => 'La descripción no puede tener más de 500 caracteres.',
                'estado.required' => 'El estado es obligatorio.',
                'estado.in' => 'El estado debe ser Activo o Inactivo.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $asignacion->update([
                'nombre' => $request->nombre,
                'abreviacion' => $request->abreviacion,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asignación actualizada exitosamente.',
                'asignacion' => $asignacion->load(['creador', 'modificador']),
                'audit_info' => $asignacion->getAuditInfo()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la asignación. Intente nuevamente.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $asignacion = Asignacion::findOrFail($id);
            
            $asignacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Asignación eliminada exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la asignación. Intente nuevamente.'
            ], 500);
        }
    }
}
