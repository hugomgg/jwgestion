<?php

namespace App\Http\Controllers;

use App\Models\Informe;
use App\Models\Congregacion;
use App\Models\Grupo;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\InformeGrupalNotification;
use Carbon\Carbon;

class PublicInformeController extends Controller
{
    /**
     * Mostrar formulario público para ingresar informes
     */
    public function show($congregacion_codigo)
    {
        // Buscar congregación por código
        $congregacion = Congregacion::where('codigo', $congregacion_codigo)->first();

        // Si no existe, redirigir a la página de inicio
        if (!$congregacion) {
            return redirect('/')->with('error', 'Código de congregación no válido');
        }

        // Obtener grupos de la congregación
        $grupos = Grupo::where('congregacion_id', $congregacion->id)
                       ->where('estado', 1)
                       ->orderBy('nombre')
                       ->get();

        // Obtener servicios activos
        $servicios = Servicio::where('estado', 1)->whereIn('id', [1,3,4]) //Solo servicios permitidos
                             ->orderBy('nombre')
                             ->get();

        // Generar períodos (mes actual y mes anterior)
        $periodos = $this->getPeriodos();

        return view('public.informe', compact('congregacion', 'grupos', 'servicios', 'periodos'));
    }

    /**
     * Obtener usuarios por grupo
     */
    public function getUsersByGrupo(Request $request)
    {
        $grupo_id = $request->input('grupo_id');

        $usuarios = User::where('grupo', $grupo_id)
                       ->where('estado', 1)
                       ->where('perfil', '!=', 10) // Excluir perfil VISITANTE
                       ->orderBy('name')
                       ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'usuarios' => $usuarios
        ]);
    }

    /**
     * Almacenar nuevo informe público
     */
    public function store(Request $request, $congregacion_codigo)
    {
        // Buscar congregación por código
        $congregacion = Congregacion::where('codigo', $congregacion_codigo)->first();

        if (!$congregacion) {
            return response()->json([
                'success' => false,
                'message' => 'Código de congregación no válido'
            ], 404);
        }

        // Validación de datos
        $validator = Validator::make($request->all(), [
            'grupo_id' => 'required|exists:grupos,id',
            'user_id' => 'required|exists:users,id',
            'periodo' => 'required|string',
            'servicio_id' => 'required|exists:servicios,id',
            'participa' => 'required|boolean',
            'cantidad_estudios' => 'nullable|integer|min:0|max:50',
            'horas' => 'nullable|integer|min:1|max:100',
            'comentario' => 'nullable|string|max:1000',
        ], [
            'grupo_id.required' => 'Debe seleccionar un grupo',
            'user_id.required' => 'Debe seleccionar un usuario',
            'periodo.required' => 'Debe seleccionar un período',
            'servicio_id.required' => 'Debe seleccionar un servicio',
            'servicio_id.exists' => 'El servicio seleccionado no es válido',
            'participa.required' => 'Debe indicar si participó en la actividad',
            'cantidad_estudios.max' => 'La cantidad de estudios no puede ser mayor a 50',
            'horas.min' => 'Las horas deben ser al menos 1',
            'horas.max' => 'Las horas no pueden ser mayores a 100',
            'comentario.max' => 'El comentario no puede exceder 1000 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Parsear el período (formato: "YYYY-MM")
            $periodo_parts = explode('-', $request->periodo);
            $anio = intval($periodo_parts[0]);
            $mes = intval($periodo_parts[1]);

            // Validar que el usuario pertenezca al grupo y congregación correctos
            $usuario = User::find($request->user_id);
            if (!$usuario || $usuario->grupo != $request->grupo_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no pertenece al grupo seleccionado'
                ], 422);
            }

            $grupo = Grupo::find($request->grupo_id);
            if (!$grupo || $grupo->congregacion_id != $congregacion->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El grupo no pertenece a la congregación'
                ], 422);
            }

            // Verificar que no exista ya un informe para el mismo usuario, año y mes
            $existeInforme = Informe::where('congregacion_id', $congregacion->id)
                                   ->where('user_id', $request->user_id)
                                   ->where('anio', $anio)
                                   ->where('mes', $mes)
                                   ->exists();

            if ($existeInforme) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un informe para este usuario en el período seleccionado'
                ], 422);
            }

            // Validaciones adicionales según participación y servicio
            if ($request->participa) {
                // Validar horas según el servicio (solo si participa)
                $servicios_con_horas = [1, 3]; // IDs de servicios que requieren horas
                if (in_array($request->servicio_id, $servicios_con_horas)) {
                    if (!$request->horas) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Debe ingresar las horas de servicio para este tipo de servicio'
                        ], 422);
                    }
                }
            }

            // Crear el informe
            $informe = Informe::create([
                'anio' => $anio,
                'mes' => $mes,
                'user_id' => $request->user_id,
                'grupo_id' => $request->grupo_id,
                'congregacion_id' => $congregacion->id,
                'servicio_id' => $request->servicio_id, // Siempre guardar el servicio
                'participa' => $request->participa,
                'cantidad_estudios' => $request->participa ? ($request->cantidad_estudios ?? 0) : 0,
                'horas' => $request->participa ? $request->horas : null,
                'comentario' => $request->comentario,
                'estado' => 1,
                'creador_id' => $request->user_id,
                'modificador_id' => $request->user_id,
            ]);

            // Enviar email a los responsables del grupo
            $this->enviarEmailInformeGrupal($grupo, $usuario, $anio, $mes);

            return response()->json([
                'success' => true,
                'message' => 'Informe enviado exitosamente',
                'informe' => $informe
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el informe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar períodos disponibles (mes actual y anterior)
     */
    private function getPeriodos()
    {
        $periodos = [];

        // Mes anterior
        $mesAnterior = Carbon::now()->subMonth();
        $periodos[] = [
            'value' => $mesAnterior->format('Y-m'),
            'label' => $this->getNombreMes($mesAnterior->month) . ' ' . $mesAnterior->year
        ];

        //2 meses anteriores
        $dosMesesAnterior = Carbon::now()->subMonths(2);
        $periodos[] = [
            'value' => $dosMesesAnterior->format('Y-m'),
            'label' => $this->getNombreMes($dosMesesAnterior->month) . ' ' . $dosMesesAnterior->year . ' (atrasado)'
        ];

        return $periodos;
    }

    /**
     * Obtener nombre del mes en español
     */
    private function getNombreMes($mes)
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return $meses[$mes] ?? '';
    }

    /**
     * Enviar email a los responsables del grupo con el resumen de informes
     */
    private function enviarEmailInformeGrupal($grupo, $publicador, $anio, $mes)
    {
        try {
            // Obtener usuarios destinatarios (perfiles: COORDINADOR, SUBCOORDINADOR, SECRETARIO, SUBSECRETARIO, ORGANIZADOR)
            $destinatarios = User::where('grupo', $grupo->id)
                ->where('estado', 1)
                ->whereIn('perfil', [3, 4, 5, 6, 7]) // COORDINADOR, SUBCOORDINADOR, SECRETARIO, SUBSECRETARIO, ORGANIZADOR
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();

            // Si no hay destinatarios, no enviar email
            if ($destinatarios->isEmpty()) {
                return;
            }

            // Obtener todos los informes del grupo para el periodo
            $informesData = $this->obtenerInformesDelGrupo($grupo->id, $anio, $mes);

            // Construir el periodo en formato legible
            $periodo = $this->getNombreMes($mes) . ' ' . $anio;

            // Obtener nombre de la congregación
            $congregacion = Congregacion::find($grupo->congregacion_id);
            $congregacionNombre = $congregacion ? $congregacion->nombre : 'Congregación';

            // Enviar notificación a cada destinatario
            Notification::send(
                $destinatarios,
                new InformeGrupalNotification(
                    $grupo->nombre,
                    $publicador->name,
                    $congregacionNombre,
                    $periodo,
                    $informesData
                )
            );

        } catch (\Exception $e) {
            // Registrar el error pero no detener el proceso
            \Log::error('Error al enviar email de informe grupal: ' . $e->getMessage());
        }
    }

    /**
     * Obtener informes del grupo para un periodo específico
     */
    private function obtenerInformesDelGrupo($grupoId, $anio, $mes)
    {
        // Obtener todos los usuarios del grupo
        $usuarios = User::where('grupo', $grupoId)
            ->where('estado', 1)
            ->orderBy('name')
            ->get();

        // Obtener informes existentes para ese periodo y grupo
        $informesExistentes = DB::table('informes as i')
            ->join('servicios as s', 'i.servicio_id', '=', 's.id')
            ->where('i.grupo_id', $grupoId)
            ->where('i.anio', $anio)
            ->where('i.mes', $mes)
            ->where('i.estado', 1)
            ->select(
                'i.user_id',
                'i.participa',
                's.nombre as servicio_nombre',
                'i.cantidad_estudios',
                'i.horas',
                'i.comentario'
            )
            ->get()
            ->keyBy('user_id');

        // Construir array con todos los usuarios e informes
        $resultado = $usuarios->map(function($usuario) use ($informesExistentes) {
            $informe = $informesExistentes->get($usuario->id);

            return [
                'user_id' => $usuario->id,
                'nombre' => $usuario->name,
                'participa' => $informe ? $informe->participa : 0,
                'servicio_nombre' => $informe ? $informe->servicio_nombre : '-',
                'cantidad_estudios' => $informe ? $informe->cantidad_estudios : 0,
                'horas' => $informe ? $informe->horas : 0,
                'comentario' => ($informe && $informe->comentario) ? $informe->comentario : '-'
            ];
        });

        return $resultado->toArray();
    }
}
