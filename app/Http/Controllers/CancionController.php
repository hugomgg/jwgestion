<?php

namespace App\Http\Controllers;

use App\Models\Cancion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CancionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $canciones = Cancion::with(['usuarioCreador', 'usuarioModificador'])
                           ->orderBy('id', 'asc')
                           ->get();
        
        return view('canciones.index', compact('canciones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'numero' => 'required|integer|min:1|unique:canciones,numero',
                'nombre' => 'required|string|max:255|unique:canciones,nombre',
                'descripcion' => 'nullable|string|max:500',
                'estado' => 'required|boolean'
            ], [
                'numero.required' => 'El número es obligatorio.',
                'numero.integer' => 'El número debe ser un valor entero.',
                'numero.min' => 'El número debe ser mayor a 0.',
                'numero.unique' => 'Ya existe una canción con este número.',
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.unique' => 'Ya existe una canción con este nombre.',
                'nombre.max' => 'El nombre no puede exceder los 255 caracteres.',
                'descripcion.max' => 'La descripción no puede exceder los 500 caracteres.',
                'estado.required' => 'El estado es obligatorio.'
            ]);

            $cancion = Cancion::create([
                'numero' => $request->numero,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
                'creador' => Auth::id(),
                'modificador' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Canción creada exitosamente.',
                'cancion' => $cancion
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la canción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cancion $cancion)
    {
        try {
            return response()->json([
                'success' => true,
                'cancion' => $cancion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos de la canción.'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cancion $cancion)
    {
        try {
            $request->validate([
                'numero' => [
                    'required',
                    'integer',
                    'min:1',
                    Rule::unique('canciones', 'numero')->ignore($cancion->id)
                ],
                'nombre' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('canciones', 'nombre')->ignore($cancion->id)
                ],
                'descripcion' => 'nullable|string|max:500',
                'estado' => 'required|boolean'
            ], [
                'numero.required' => 'El número es obligatorio.',
                'numero.integer' => 'El número debe ser un valor entero.',
                'numero.min' => 'El número debe ser mayor a 0.',
                'numero.unique' => 'Ya existe una canción con este número.',
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.unique' => 'Ya existe una canción con este nombre.',
                'nombre.max' => 'El nombre no puede exceder los 255 caracteres.',
                'descripcion.max' => 'La descripción no puede exceder los 500 caracteres.',
                'estado.required' => 'El estado es obligatorio.'
            ]);

            $cancion->update([
                'numero' => $request->numero,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
                'modificador' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Canción actualizada exitosamente.',
                'cancion' => $cancion
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la canción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cancion $cancion)
    {
        try {
            $cancion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Canción eliminada exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la canción: ' . $e->getMessage()
            ], 500);
        }
    }
}
