<?php

namespace App\Http\Controllers;

use App\Models\SeccionReunion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SeccionReunionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Consulta con JOIN para obtener información de auditoría
        $secciones = DB::table('secciones_reunion')
            ->leftJoin('users as creador', 'secciones_reunion.creador_id', '=', 'creador.id')
            ->leftJoin('users as modificador', 'secciones_reunion.modificador_id', '=', 'modificador.id')
            ->select(
                'secciones_reunion.id',
                'secciones_reunion.nombre',
                'secciones_reunion.abreviacion',
                'secciones_reunion.estado',
                'secciones_reunion.creado_por_timestamp',
                'secciones_reunion.modificado_por_timestamp',
                'creador.name as creado_por_nombre',
                'modificador.name as modificado_por_nombre'
            )
            ->orderBy('secciones_reunion.nombre')
            ->get();

        return view('secciones-reunion.index', compact('secciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('secciones-reunion.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:secciones_reunion,nombre',
            'abreviacion' => 'required|string|max:10|unique:secciones_reunion,abreviacion',
            'estado' => 'required|integer|in:0,1',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'nombre.unique' => 'Ya existe una sección de reunión con este nombre.',
            'abreviacion.required' => 'La abreviación es obligatoria.',
            'abreviacion.max' => 'La abreviación no puede exceder 10 caracteres.',
            'abreviacion.unique' => 'Ya existe una sección de reunión con esta abreviación.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser Activo o Inactivo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $seccion = SeccionReunion::create([
                'nombre' => $request->nombre,
                'abreviacion' => $request->abreviacion,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sección de reunión creada exitosamente.',
                'seccion' => $seccion->load(['creador', 'modificador']),
                'audit_info' => $seccion->getAuditInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la sección de reunión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $seccion = SeccionReunion::with(['creador', 'modificador'])->findOrFail($id);
            return view('secciones-reunion.show', compact('seccion'));
        } catch (\Exception $e) {
            return redirect()->route('secciones-reunion.index')->with('error', 'Sección de reunión no encontrada.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $seccion = SeccionReunion::with(['creador', 'modificador'])->findOrFail($id);
            
            // Formatear la información de auditoría
            $seccionData = $seccion->toArray();
            $seccionData['creado_por_nombre'] = $seccion->creador ? $seccion->creador->name : null;
            $seccionData['modificado_por_nombre'] = $seccion->modificador ? $seccion->modificador->name : null;
            $seccionData['creado_por_timestamp'] = $seccion->creado_por_timestamp ?
                $seccion->creado_por_timestamp->format('d/m/Y H:i:s') : null;
            $seccionData['modificado_por_timestamp'] = $seccion->modificado_por_timestamp ?
                $seccion->modificado_por_timestamp->format('d/m/Y H:i:s') : null;
            
            return response()->json([
                'success' => true,
                'seccion' => $seccionData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sección de reunión no encontrada: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $seccion = SeccionReunion::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:secciones_reunion,nombre,' . $id,
                'abreviacion' => 'required|string|max:10|unique:secciones_reunion,abreviacion,' . $id,
                'estado' => 'required|integer|in:0,1',
            ], [
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
                'nombre.unique' => 'Ya existe una sección de reunión con este nombre.',
                'abreviacion.required' => 'La abreviación es obligatoria.',
                'abreviacion.max' => 'La abreviación no puede exceder 10 caracteres.',
                'abreviacion.unique' => 'Ya existe una sección de reunión con esta abreviación.',
                'estado.required' => 'El estado es obligatorio.',
                'estado.in' => 'El estado debe ser Activo o Inactivo.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $seccion->update([
                'nombre' => $request->nombre,
                'abreviacion' => $request->abreviacion,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sección de reunión actualizada exitosamente.',
                'seccion' => $seccion->load(['creador', 'modificador']),
                'audit_info' => $seccion->getAuditInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la sección de reunión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $seccion = SeccionReunion::findOrFail($id);
            
            // Verificar si se puede eliminar (aquí podrías agregar validaciones adicionales)
            // Por ejemplo, verificar si está siendo utilizada en otras tablas
            
            $seccion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sección de reunión eliminada exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la sección de reunión: ' . $e->getMessage()
            ], 500);
        }
    }
}
