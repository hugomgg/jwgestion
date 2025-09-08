<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Servicio;

class ServicioController extends Controller
{
    /**
     * Display a listing of the servicios.
     */
    public function index()
    {
        $servicios = Servicio::with(['creador', 'modificador'])->orderBy('id')->get();
        return view('servicios.index', compact('servicios'));
    }

    /**
     * Store a newly created servicio in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:servicios,nombre',
            'descripcion' => 'nullable|string|max:500',
            'estado' => 'required|boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'nombre.unique' => 'Ya existe un servicio con este nombre.',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.boolean' => 'El estado debe ser válido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $servicio = Servicio::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
            ]);

            // Obtener información de auditoría
            $auditInfo = $servicio->getAuditInfo();

            return response()->json([
                'success' => true,
                'message' => 'Servicio creado exitosamente.',
                'servicio' => $servicio->load(['creador', 'modificador']),
                'audit_info' => $auditInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el servicio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified servicio.
     */
    public function edit($id)
    {
        try {
            $servicio = Servicio::findOrFail($id);
            return response()->json([
                'success' => true,
                'servicio' => $servicio
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado.'
            ], 404);
        }
    }

    /**
     * Update the specified servicio in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $servicio = Servicio::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:servicios,nombre,' . $id,
                'descripcion' => 'nullable|string|max:500',
                'estado' => 'required|boolean',
            ], [
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
                'nombre.unique' => 'Ya existe un servicio con este nombre.',
                'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
                'estado.required' => 'El estado es obligatorio.',
                'estado.boolean' => 'El estado debe ser válido.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $servicio->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
            ]);

            // Obtener información de auditoría actualizada
            $auditInfo = $servicio->fresh()->getAuditInfo();

            return response()->json([
                'success' => true,
                'message' => 'Servicio actualizado exitosamente.',
                'servicio' => $servicio->load(['creador', 'modificador']),
                'audit_info' => $auditInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el servicio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified servicio from storage.
     */
    public function destroy($id)
    {
        try {
            $servicio = Servicio::findOrFail($id);
            $servicio->delete();

            return response()->json([
                'success' => true,
                'message' => 'Servicio eliminado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el servicio: ' . $e->getMessage()
            ], 500);
        }
    }
}
