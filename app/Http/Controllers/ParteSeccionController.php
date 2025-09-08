<?php

namespace App\Http\Controllers;

use App\Models\ParteSeccion;
use App\Models\SeccionReunion;
use App\Models\Asignacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ParteSeccionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Consulta con JOIN para obtener información de auditoría y relaciones
        $partes = DB::table('partes_seccion')
            ->leftJoin('users as creador', 'partes_seccion.creador_id', '=', 'creador.id')
            ->leftJoin('users as modificador', 'partes_seccion.modificador_id', '=', 'modificador.id')
            ->leftJoin('secciones_reunion', 'partes_seccion.seccion_id', '=', 'secciones_reunion.id')
            ->leftJoin('asignaciones', 'partes_seccion.asignacion_id', '=', 'asignaciones.id')
            ->select(
                'partes_seccion.id',
                'partes_seccion.nombre',
                'partes_seccion.abreviacion',
                'partes_seccion.orden',
                'partes_seccion.tiempo',
                'partes_seccion.tipo',
                'partes_seccion.estado',
                'partes_seccion.creado_por_timestamp',
                'partes_seccion.modificado_por_timestamp',
                'secciones_reunion.nombre as seccion_nombre',
                'asignaciones.nombre as asignacion_nombre',
                'creador.name as creado_por_nombre',
                'modificador.name as modificado_por_nombre'
            )
            ->orderBy('partes_seccion.orden')
            ->get();

        // Obtener secciones y asignaciones para los formularios
        $secciones = SeccionReunion::active()->orderBy('nombre')->get();
        $asignaciones = Asignacion::where('estado', 1)->orderBy('nombre')->get();
        $tipos = ParteSeccion::$tipos;

        return view('partes-seccion.index', compact('partes', 'secciones', 'asignaciones', 'tipos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $secciones = SeccionReunion::active()->orderBy('nombre')->get();
        $asignaciones = Asignacion::where('estado', 1)->orderBy('nombre')->get();
        
        return view('partes-seccion.create', compact('secciones', 'asignaciones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'abreviacion' => 'required|string|max:10|unique:partes_seccion,abreviacion',
            'orden' => 'required|integer|min:1',
            'tiempo' => 'required|integer|min:1',
            'seccion_id' => 'required|exists:secciones_reunion,id',
            'asignacion_id' => 'required|exists:asignaciones,id',
            'tipo' => 'required|integer|in:1,2,3',
            'estado' => 'required|integer|in:0,1',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'abreviacion.required' => 'La abreviación es obligatoria.',
            'abreviacion.max' => 'La abreviación no puede exceder 10 caracteres.',
            'abreviacion.unique' => 'Esta abreviación ya está en uso.',
            'orden.required' => 'El orden es obligatorio.',
            'orden.integer' => 'El orden debe ser un número entero.',
            'orden.min' => 'El orden debe ser mayor a 0.',
            'tiempo.required' => 'El tiempo es obligatorio.',
            'tiempo.integer' => 'El tiempo debe ser un número entero.',
            'tiempo.min' => 'El tiempo debe ser mayor a 0.',
            'seccion_id.required' => 'La sección es obligatoria.',
            'seccion_id.exists' => 'La sección seleccionada no existe.',
            'asignacion_id.required' => 'La asignación es obligatoria.',
            'asignacion_id.exists' => 'La asignación seleccionada no existe.',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo debe ser Solo, Acompañado o HombreyMujer.',
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
            $parte = ParteSeccion::create([
                'nombre' => $request->nombre,
                'abreviacion' => $request->abreviacion,
                'orden' => $request->orden,
                'tiempo' => $request->tiempo,
                'seccion_id' => $request->seccion_id,
                'asignacion_id' => $request->asignacion_id,
                'tipo' => $request->tipo,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Parte de sección creada exitosamente.',
                'parte' => $parte->load(['creador', 'modificador', 'seccion', 'asignacion']),
                'audit_info' => $parte->getAuditInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la parte de sección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $parte = ParteSeccion::with(['creador', 'modificador', 'seccion', 'asignacion'])->findOrFail($id);
            return view('partes-seccion.show', compact('parte'));
        } catch (\Exception $e) {
            return redirect()->route('partes-seccion.index')->with('error', 'Parte de sección no encontrada.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $parte = ParteSeccion::with(['creador', 'modificador', 'seccion', 'asignacion'])->findOrFail($id);
            
            // Formatear la información de auditoría
            $parteData = $parte->toArray();
            $parteData['creado_por_nombre'] = $parte->creador ? $parte->creador->name : null;
            $parteData['modificado_por_nombre'] = $parte->modificador ? $parte->modificador->name : null;
            $parteData['seccion_nombre'] = $parte->seccion ? $parte->seccion->nombre : null;
            $parteData['asignacion_nombre'] = $parte->asignacion ? $parte->asignacion->nombre : null;
            $parteData['creado_por_timestamp'] = $parte->creado_por_timestamp ?
                $parte->creado_por_timestamp->format('d/m/Y H:i:s') : null;
            $parteData['modificado_por_timestamp'] = $parte->modificado_por_timestamp ?
                $parte->modificado_por_timestamp->format('d/m/Y H:i:s') : null;
            
            return response()->json([
                'success' => true,
                'parte' => $parteData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parte de sección no encontrada: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $parte = ParteSeccion::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'abreviacion' => 'required|string|max:10|unique:partes_seccion,abreviacion,' . $id,
                'orden' => 'required|integer|min:1',
                'tiempo' => 'required|integer|min:1',
                'seccion_id' => 'required|exists:secciones_reunion,id',
                'asignacion_id' => 'required|exists:asignaciones,id',
                'tipo' => 'required|integer|in:1,2,3',
                'estado' => 'required|integer|in:0,1',
            ], [
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
                'abreviacion.required' => 'La abreviación es obligatoria.',
                'abreviacion.max' => 'La abreviación no puede exceder 10 caracteres.',
                'abreviacion.unique' => 'Esta abreviación ya está en uso.',
                'orden.required' => 'El orden es obligatorio.',
                'orden.integer' => 'El orden debe ser un número entero.',
                'orden.min' => 'El orden debe ser mayor a 0.',
                'tiempo.required' => 'El tiempo es obligatorio.',
                'tiempo.integer' => 'El tiempo debe ser un número entero.',
                'tiempo.min' => 'El tiempo debe ser mayor a 0.',
                'seccion_id.required' => 'La sección es obligatoria.',
                'seccion_id.exists' => 'La sección seleccionada no existe.',
                'asignacion_id.required' => 'La asignación es obligatoria.',
                'asignacion_id.exists' => 'La asignación seleccionada no existe.',
                'tipo.required' => 'El tipo es obligatorio.',
                'tipo.in' => 'El tipo debe ser Solo, Acompañado o HombreyMujer.',
                'estado.required' => 'El estado es obligatorio.',
                'estado.in' => 'El estado debe ser Activo o Inactivo.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $parte->update([
                'nombre' => $request->nombre,
                'abreviacion' => $request->abreviacion,
                'orden' => $request->orden,
                'tiempo' => $request->tiempo,
                'seccion_id' => $request->seccion_id,
                'asignacion_id' => $request->asignacion_id,
                'tipo' => $request->tipo,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Parte de sección actualizada exitosamente.',
                'parte' => $parte->load(['creador', 'modificador', 'seccion', 'asignacion']),
                'audit_info' => $parte->getAuditInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la parte de sección: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $parte = ParteSeccion::findOrFail($id);
            
            // Verificar si se puede eliminar (aquí podrías agregar validaciones adicionales)
            
            $parte->delete();

            return response()->json([
                'success' => true,
                'message' => 'Parte de sección eliminada exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la parte de sección: ' . $e->getMessage()
            ], 500);
        }
    }
}
