<?php

namespace App\Http\Controllers;

use App\Models\EstadoEspiritual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EstadoEspiritualController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $estados_espirituales = EstadoEspiritual::with(['creador', 'modificador'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('estados_espirituales.index', compact('estados_espirituales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Log para depuración
            \Log::info('EstadoEspiritual Store - Datos recibidos:', $request->all());
            
            $request->validate([
                'nombre' => 'required|string|max:255|unique:estado_espiritual,nombre',
                'estado' => 'required|in:0,1'
            ], [
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.unique' => 'Ya existe un estado espiritual con este nombre.',
                'nombre.max' => 'El nombre no puede exceder los 255 caracteres.',
                'estado.required' => 'El estado es obligatorio.',
                'estado.in' => 'El estado debe ser 0 (Inactivo) o 1 (Activo).'
            ]);

            $estadoValue = (int) $request->estado;
            \Log::info('EstadoEspiritual Store - Valor convertido:', ['estado' => $estadoValue]);

            $estadoEspiritual = EstadoEspiritual::create([
                'nombre' => $request->nombre,
                'estado' => $estadoValue
            ]);

            \Log::info('EstadoEspiritual Store - Creado exitosamente:', $estadoEspiritual->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Estado espiritual creado exitosamente.',
                'estado_espiritual' => $estadoEspiritual->load(['creador', 'modificador']),
                'audit_info' => $estadoEspiritual->getAuditInfo()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('EstadoEspiritual Store - Error de validación:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('EstadoEspiritual Store - Error general:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el estado espiritual: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $estadoEspiritual = EstadoEspiritual::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'estado_espiritual' => $estadoEspiritual
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Estado espiritual no encontrado.'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Log para depuración
            \Log::info('EstadoEspiritual Update - Datos recibidos:', $request->all());
            \Log::info('EstadoEspiritual Update - ID:', ['id' => $id]);

            $estadoEspiritual = EstadoEspiritual::findOrFail($id);

            $request->validate([
                'nombre' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('estado_espiritual', 'nombre')->ignore($estadoEspiritual->id)
                ],
                'estado' => 'required|in:0,1'
            ], [
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.unique' => 'Ya existe un estado espiritual con este nombre.',
                'nombre.max' => 'El nombre no puede exceder los 255 caracteres.',
                'estado.required' => 'El estado es obligatorio.',
                'estado.in' => 'El estado debe ser 0 (Inactivo) o 1 (Activo).'
            ]);

            $estadoValue = (int) $request->estado;
            \Log::info('EstadoEspiritual Update - Valor convertido:', ['estado' => $estadoValue]);

            $estadoEspiritual->update([
                'nombre' => $request->nombre,
                'estado' => $estadoValue
            ]);

            \Log::info('EstadoEspiritual Update - Actualizado exitosamente:', $estadoEspiritual->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Estado espiritual actualizado exitosamente.',
                'estado_espiritual' => $estadoEspiritual->load(['creador', 'modificador']),
                'audit_info' => $estadoEspiritual->getAuditInfo()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('EstadoEspiritual Update - Error de validación:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('EstadoEspiritual Update - Error general:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado espiritual: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $estadoEspiritual = EstadoEspiritual::findOrFail($id);
            
            // Verificar si se puede eliminar (opcional: agregar lógica de validación)
            $estadoEspiritual->delete();

            return response()->json([
                'success' => true,
                'message' => 'Estado espiritual eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el estado espiritual. Intente nuevamente.'
            ], 500);
        }
    }
}
