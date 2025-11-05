<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Congregacion;

class CongregacionController extends Controller
{
    /**
     * Display a listing of the congregaciones.
     */
    public function index()
    {
        $congregaciones = Congregacion::with(['creador', 'modificador'])->orderBy('nombre')->get();
        return view('congregaciones.index', compact('congregaciones'));
    }

    /**
     * Store a newly created congregacion in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:congregaciones,nombre',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:20',
            'persona_contacto' => 'nullable|string|max:255',
            'codigo' => 'nullable|string|max:64|unique:congregaciones,codigo',
            'estado' => 'required|boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'nombre.unique' => 'Ya existe una congregación con este nombre.',
            'direccion.max' => 'La dirección no puede exceder 500 caracteres.',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
            'persona_contacto.max' => 'La persona de contacto no puede exceder 255 caracteres.',
            'codigo.max' => 'El código no puede exceder 64 caracteres.',
            'codigo.unique' => 'Este código ya está en uso por otra congregación.',
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
            $congregacion = Congregacion::create([
                'nombre' => $request->nombre,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'persona_contacto' => $request->persona_contacto,
                'codigo' => $request->codigo,
                'estado' => $request->estado,
            ]);

            // Obtener información de auditoría
            $auditInfo = $congregacion->getAuditInfo();

            return response()->json([
                'success' => true,
                'message' => 'Congregación creada exitosamente.',
                'congregacion' => $congregacion->load(['creador', 'modificador']),
                'audit_info' => $auditInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la congregación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified congregacion.
     */
    public function edit($id)
    {
        try {
            $congregacion = Congregacion::findOrFail($id);
            return response()->json([
                'success' => true,
                'congregacion' => $congregacion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Congregación no encontrada.'
            ], 404);
        }
    }

    /**
     * Update the specified congregacion in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $congregacion = Congregacion::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:congregaciones,nombre,' . $id,
                'direccion' => 'nullable|string|max:500',
                'telefono' => 'nullable|string|max:20',
                'persona_contacto' => 'nullable|string|max:255',
                'codigo' => 'nullable|string|max:64|unique:congregaciones,codigo,' . $id,
                'estado' => 'required|boolean',
            ], [
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
                'nombre.unique' => 'Ya existe una congregación con este nombre.',
                'direccion.max' => 'La dirección no puede exceder 500 caracteres.',
                'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
                'persona_contacto.max' => 'La persona de contacto no puede exceder 255 caracteres.',
                'codigo.max' => 'El código no puede exceder 64 caracteres.',
                'codigo.unique' => 'Este código ya está en uso por otra congregación.',
                'estado.required' => 'El estado es obligatorio.',
                'estado.boolean' => 'El estado debe ser válido.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $congregacion->update([
                'nombre' => $request->nombre,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'persona_contacto' => $request->persona_contacto,
                'codigo' => $request->codigo,
                'estado' => $request->estado,
            ]);

            // Obtener información de auditoría actualizada
            $auditInfo = $congregacion->fresh()->getAuditInfo();

            return response()->json([
                'success' => true,
                'message' => 'Congregación actualizada exitosamente.',
                'congregacion' => $congregacion->load(['creador', 'modificador']),
                'audit_info' => $auditInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la congregación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified congregacion from storage.
     */
    public function destroy($id)
    {
        try {
            $congregacion = Congregacion::findOrFail($id);
            $congregacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Congregación eliminada exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la congregación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the user's own congregation.
     */
    public function updateOwn(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Verificar que el usuario tenga una congregación asignada
            if (!$user->congregacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes una congregación asignada.'
                ], 400);
            }

            $congregacion = $user->congregacion()->first();
            $congregacionId = $congregacion->id;

            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:congregaciones,nombre,' . $congregacionId,
                'direccion' => 'nullable|string|max:1000',
                'telefono' => 'nullable|string|max:20',
                'persona_contacto' => 'nullable|string|max:255',
                'codigo' => 'nullable|string|max:64|unique:congregaciones,codigo,' . $congregacionId,
            ], [
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
                'nombre.unique' => 'Ya existe una congregación con este nombre.',
                'direccion.max' => 'La dirección no puede exceder 1000 caracteres.',
                'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
                'persona_contacto.max' => 'La persona de contacto no puede exceder 255 caracteres.',
                'codigo.max' => 'El código no puede exceder 64 caracteres.',
                'codigo.unique' => 'Este código ya está en uso por otra congregación.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $congregacion->update([
                'nombre' => $request->nombre,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'persona_contacto' => $request->persona_contacto,
                'codigo' => $request->codigo,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Congregación actualizada exitosamente.',
                'congregacion' => $congregacion->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la congregación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a unique random code for congregation.
     */
    public function generarCodigo(Request $request)
    {
        try {
            $codigo = $this->generarCodigoUnico();
            
            return response()->json([
                'success' => true,
                'codigo' => $codigo,
                'message' => 'Código generado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el código: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a unique 64-character code.
     */
    private function generarCodigoUnico()
    {
        $intentos = 0;
        $maxIntentos = 10;
        
        do {
            // Generar código aleatorio de 64 caracteres (letras y números)
            $codigo = $this->generarCodigoAleatorio(64);
            
            // Verificar si el código ya existe
            $existe = Congregacion::where('codigo', $codigo)->exists();
            
            $intentos++;
            
            if ($intentos >= $maxIntentos && $existe) {
                throw new \Exception('No se pudo generar un código único después de ' . $maxIntentos . ' intentos.');
            }
            
        } while ($existe);
        
        return $codigo;
    }

    /**
     * Generate random alphanumeric code.
     */
    private function generarCodigoAleatorio($longitud = 64)
    {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codigo = '';
        $maxIndex = strlen($caracteres) - 1;
        
        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $caracteres[random_int(0, $maxIndex)];
        }
        
        return $codigo;
    }
}
