@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar me-2"></i>Editar Programa
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('programas.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver a Programas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Contenedor para alertas -->
                    <div id="alert-container"></div>

                    <form id="editProgramaForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="programa_id" name="programa_id" value="{{ $programa->id }}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha" class="form-label">Fecha *</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha" value="{{ $programa->fecha ? $programa->fecha->format('Y-m-d') : '' }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="orador_inicial" class="form-label">Orador Inicial</label>
                                    @if(Auth::user()->perfil == 3)
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="orador_inicial_display" name="orador_inicial_display"
                                                   value="{{ $programa->oradorInicial ? $programa->oradorInicial->name : '' }}" disabled>
                                            <button type="button" class="btn btn-outline-primary" id="btn-buscar-orador-inicial"
                                                    title="Buscar Orador Inicial" onclick="buscarOradorInicial()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info" id="btn-historial-orador-inicial"
                                                    title="Historial de Orador Inicial" onclick="verHistorialOradorInicial()"
                                                    {{ !$programa->orador_inicial ? 'disabled' : '' }}>
                                                <i class="fas fa-history"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" id="orador_inicial" name="orador_inicial" value="{{ $programa->orador_inicial }}">
                                    @else
                                        <select class="form-select" id="orador_inicial" name="orador_inicial">
                                            <option value="">Seleccionar...</option>
                                            @foreach($usuarios as $usuario)
                                                <option value="{{ $usuario->id }}" {{ $programa->orador_inicial == $usuario->id ? 'selected' : '' }}>{{ $usuario->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cancion_pre" class="form-label">Canción Inicial</label>
                                    @if(Auth::user()->perfil == 3)
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="cancion_pre_display" name="cancion_pre_display"
                                                   value="{{ $programa->cancionPre ? ($programa->cancionPre->numero ? $programa->cancionPre->numero . ' - ' : '') . $programa->cancionPre->nombre : '' }}" disabled>
                                            <button type="button" class="btn btn-outline-primary" id="btn-buscar-cancion-inicial"
                                                    title="Buscar Canción Inicial" onclick="buscarCancionInicial()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" id="cancion_pre" name="cancion_pre" value="{{ $programa->cancion_pre }}">
                                    @else
                                        <select class="form-select" id="cancion_pre" name="cancion_pre">
                                            <option value="">Seleccionar...</option>
                                            @foreach($canciones as $cancion)
                                                <option value="{{ $cancion->id }}" {{ $programa->cancion_pre == $cancion->id ? 'selected' : '' }}>{{ $cancion->numero ? $cancion->numero . ' - ' : '' }}{{ $cancion->nombre }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="presidencia" class="form-label">Presidencia</label>
                                    @if(Auth::user()->perfil == 3)
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="presidencia_display" name="presidencia_display"
                                                   value="{{ $programa->presidenciaUsuario ? $programa->presidenciaUsuario->name : '' }}" disabled>
                                            <button type="button" class="btn btn-outline-primary" id="btn-buscar-presidencia"
                                                    title="Buscar Presidentes" onclick="buscarPresidencia()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info" id="btn-historial-presidencia"
                                                    title="Historial de Presidente" onclick="verHistorialPresidencia()"
                                                    {{ !$programa->presidencia ? 'disabled' : '' }}>
                                                <i class="fas fa-history"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" id="presidencia" name="presidencia" value="{{ $programa->presidencia }}">
                                    @else
                                        <select class="form-select" id="presidencia" name="presidencia">
                                            <option value="">Seleccionar...</option>
                                            @foreach($usuarios as $usuario)
                                                <option value="{{ $usuario->id }}" {{ $programa->presidencia == $usuario->id ? 'selected' : '' }}>{{ $usuario->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de Partes del Programa -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-list me-2"></i>{{ $seccionReunion ? $seccionReunion->nombre : 'Partes del Programa (Primera Sección)' }}
                                                </h6>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#parteProgramaModal" onclick="openCreateParteModal()">
                                                        <i class="fas fa-plus me-2"></i>Nueva Asignación TB
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="partesTable" class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 120px;">Tiempo (min)</th>
                                                        <th style="width: 120px;">Parte</th>
                                                        <th style="width: 400px;">Encargado</th>
                                                        <th>Tema</th>
                                                        @if(!Auth::user()->isCoordinator())
                                                        <th>Lección</th>
                                                        @endif
                                                        <th style="width: 140px;">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Los datos se cargarán vía AJAX -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de Partes de la Segunda Sección (Solo para Coordinadores) -->
                        @if(Auth::user()->perfil == 3)
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-list me-2"></i>Partes de la Segunda Sección (Sala Principal)
                                                </h6>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#parteProgramaSegundaSeccionModal" onclick="openCreateParteSegundaSeccionModal()">
                                                        <i class="fas fa-plus me-2"></i>Nueva Asignación SP
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="partesSegundaSeccionTable" class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 120px;">Tiempo (min)</th>
                                                        <th style="width: 120px;">Parte</th>
                                                        <th style="width: 400px;">Encargado</th>
                                                        <th style="width: 400px;">Ayudante</th>
                                                        <th>Lección</th>
                                                        <th style="width: 140px;">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Los datos se cargarán vía AJAX -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(Auth::user()->perfil == 3)
                        <!-- Tabla de Partes de Seamos Mejores Maestros -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-chalkboard-teacher me-2"></i>Seamos Mejores Maestros
                                </h6>
                                <div>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#parteProgramaTerceraSeccionModal" onclick="openCreateParteTerceraSeccionModal()">
                                        <i class="fas fa-plus me-2"></i>Nueva Asignación SMM
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="partesTerceraSeccionTable" class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 120px;">Tiempo (min)</th>
                                                <th style="width: 120px;">Parte</th>
                                                <th style="width: 400px;">Encargado</th>
                                                <th style="width: 400px;">Ayudante</th>
                                                <th>Lección</th>
                                                <th style="width: 140px;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Los datos se cargan dinámicamente -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cancion_en" class="form-label">Canción Intermedia</label>
                                    @if(Auth::user()->perfil == 3)
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="cancion_en_display" name="cancion_en_display"
                                                   value="{{ $programa->cancionEn ? ($programa->cancionEn->numero ? $programa->cancionEn->numero . ' - ' : '') . $programa->cancionEn->nombre : '' }}" disabled>
                                            <button type="button" class="btn btn-outline-primary" id="btn-buscar-cancion-intermedia"
                                                    title="Buscar Canción Intermedia" onclick="buscarCancionIntermedia()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" id="cancion_en" name="cancion_en" value="{{ $programa->cancion_en }}">
                                    @else
                                        <select class="form-select" id="cancion_en" name="cancion_en">
                                            <option value="">Seleccionar...</option>
                                            @foreach($canciones as $cancion)
                                                <option value="{{ $cancion->id }}" {{ $programa->cancion_en == $cancion->id ? 'selected' : '' }}>{{ $cancion->numero ? $cancion->numero . ' - ' : '' }}{{ $cancion->nombre }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cancion_post" class="form-label">Canción Final</label>
                                    @if(Auth::user()->perfil == 3)
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="cancion_post_display" name="cancion_post_display"
                                                   value="{{ $programa->cancionPost ? ($programa->cancionPost->numero ? $programa->cancionPost->numero . ' - ' : '') . $programa->cancionPost->nombre : '' }}" disabled>
                                            <button type="button" class="btn btn-outline-primary" id="btn-buscar-cancion-final"
                                                    title="Buscar Canción Final" onclick="buscarCancionFinal()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" id="cancion_post" name="cancion_post" value="{{ $programa->cancion_post }}">
                                    @else
                                        <select class="form-select" id="cancion_post" name="cancion_post">
                                            <option value="">Seleccionar...</option>
                                            @foreach($canciones as $cancion)
                                                <option value="{{ $cancion->id }}" {{ $programa->cancion_post == $cancion->id ? 'selected' : '' }}>{{ $cancion->numero ? $cancion->numero . ' - ' : '' }}{{ $cancion->nombre }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="orador_final" class="form-label">Orador Final</label>
                                    @if(Auth::user()->perfil == 3)
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="orador_final_display" name="orador_final_display"
                                                   value="{{ $programa->oradorFinal ? $programa->oradorFinal->name : '' }}" disabled>
                                            <button type="button" class="btn btn-outline-primary" id="btn-buscar-orador-final"
                                                    title="Buscar Orador Final" onclick="buscarOradorFinal()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info" id="btn-historial-orador-final"
                                                    title="Historial de Orador Final" onclick="verHistorialOradorFinal()"
                                                    {{ !$programa->orador_final ? 'disabled' : '' }}>
                                                <i class="fas fa-history"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" id="orador_final" name="orador_final" value="{{ $programa->orador_final }}">
                                    @else
                                        <select class="form-select" id="orador_final" name="orador_final">
                                            <option value="">Seleccionar...</option>
                                            @foreach($usuarios as $usuario)
                                                <option value="{{ $usuario->id }}" {{ $programa->orador_final == $usuario->id ? 'selected' : '' }}>{{ $usuario->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado *</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="1" {{ $programa->estado ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ !$programa->estado ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('programas.index') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary" id="updateProgramaBtn">
                                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                        Actualizar Programa
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear/Editar Parte del Programa -->
<div class="modal fade" id="parteProgramaModal" tabindex="-1" aria-labelledby="parteProgramaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="parteProgramaModalLabel">Nueva Asignación de {{ $seccionReunion ? $seccionReunion->nombre : 'Primera Sección' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="parteProgramaForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="parte_programa_id" name="parte_programa_id">
                    <input type="hidden" id="programa_id_parte" name="programa_id" value="{{ $programa->id }}">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="parte_id" class="form-label">Asignación <span class="text-danger">*</span></label>
                                <select class="form-select" id="parte_id" name="parte_id" required>
                                    <option value="">Cargando...</option>
                                </select>
                                <input type="text" class="form-control" id="parte_display" style="display: none;" disabled>
                                <input type="hidden" id="parte_id_hidden" name="parte_id">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tiempo_parte" class="form-label">Tiempo (minutos) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="tiempo_parte" name="tiempo" min="1" max="60" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tema_parte" class="form-label">Tema</label>
                        <textarea class="form-control" id="tema_parte" name="tema" rows="2" maxlength="500"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="encargado_id" class="form-label">Encargado <span class="text-danger">*</span></label>
                                @if(Auth::user()->perfil == 3)
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="encargado_display" name="encargado_display"
                                               placeholder="Seleccionar encargado..." disabled>
                                        <button type="button" class="btn btn-outline-primary" id="btn-buscar-encargado"
                                                title="Buscar Encargado" onclick="buscarEncargadoParte()" disabled>
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info" id="btn-historial-encargado"
                                                title="Historial de Encargado" onclick="verHistorialEncargadoParte()" disabled>
                                            <i class="fas fa-history"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success" id="btn-agregar-reemplazado"
                                                title="Agregar como Encargado Reemplazado" onclick="agregarEncargadoReemplazado()" disabled>
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" id="encargado_id" name="encargado_id" required>
                                @else
                                    <select class="form-select select2" id="encargado_id" name="encargado_id" required>
                                        <option value="">Seleccionar una parte primero...</option>
                                    </select>
                                @endif
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        @if(Auth::user()->perfil == 3)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="encargado_reemplazado_display" class="form-label">Encargado Reemplazado</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="encargado_reemplazado_display" name="encargado_reemplazado_display"
                                           placeholder="Sin encargado reemplazado..." disabled>
                                    <button type="button" class="btn btn-outline-danger" id="btn-eliminar-reemplazado"
                                            title="Eliminar Encargado Reemplazado" onclick="eliminarEncargadoReemplazado()" disabled>
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="encargado_reemplazado_id" name="encargado_reemplazado_id">
                            </div>
                        </div>
                        @endif
                        @if(!Auth::user()->isCoordinator())
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="leccion_parte" class="form-label">Lección <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="leccion_parte" name="leccion" maxlength="500" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveParteBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Parte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Crear/Editar Parte de la Segunda Sección -->
@if(Auth::user()->perfil == 3)
<div class="modal fade" id="parteProgramaSegundaSeccionModal" tabindex="-1" aria-labelledby="parteProgramaSegundaSeccionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="parteProgramaSegundaSeccionModalLabel">Nueva Asignación (Sala Principal)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="parteProgramaSegundaSeccionForm">
                <div class="modal-body">
                    <!-- Contenedor de alertas del modal -->
                    <div id="modal-alert-container-segunda-seccion"></div>

                    @csrf
                    <input type="hidden" id="parte_programa_segunda_seccion_id" name="parte_programa_id">
                    <input type="hidden" id="programa_id_segunda_seccion" name="programa_id" value="{{ $programa->id }}">
                    <input type="hidden" name="sala_id" value="1">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="parte_id_segunda_seccion" class="form-label">Asignación <span class="text-danger">*</span></label>
                                <select class="form-select" id="parte_id_segunda_seccion" name="parte_id" required>
                                    <option value="">Cargando...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tiempo_segunda_seccion" class="form-label">Tiempo (minutos) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="tiempo_segunda_seccion" name="tiempo" min="1" max="60" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="encargado_display_segunda_seccion" class="form-label">Encargado <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="encargado_display_segunda_seccion" name="encargado_display" placeholder="Seleccionar una parte primero..." disabled>
                                    <button type="button" class="btn btn-outline-primary" id="btn-buscar-encargado-segunda" onclick="buscarEncargadoSegundaSeccion()" title="Buscar Encargado">
                                        <i class="fas fa-search"></i>
                                    </button>

                                    <button type="button" class="btn btn-outline-success" id="btn-agregar-encargado-reemplazado-segunda" onclick="agregarEncargadoReemplazado()" title="Agregar Encargado como Reemplazado" disabled style="display: none;">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="btn-encargado-reemplazado-segunda" onclick="manejarEncargadoReemplazado()" title="Encargado reemplazado" disabled>
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="encargado_id_segunda_seccion" name="encargado_id" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ayudante_display_segunda_seccion" class="form-label">Ayudante</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="ayudante_display_segunda_seccion" name="ayudante_display" placeholder="Seleccionar..." disabled>
                                    <button type="button" class="btn btn-outline-primary" id="btn-buscar-ayudante-segunda" onclick="buscarAyudanteSegundaSeccion()" title="Buscar Ayudante">
                                        <i class="fas fa-search"></i>
                                    </button>

                                    <button type="button" class="btn btn-outline-success" id="btn-agregar-ayudante-reemplazado-segunda" onclick="agregarAyudanteReemplazado()" title="Agregar Ayudante como Reemplazado" disabled style="display: none;">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="btn-ayudante-reemplazado-segunda" onclick="manejarAyudanteReemplazado()" title="Ayudante reemplazado" disabled>
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="ayudante_id_segunda_seccion" name="ayudante_id">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Campos de Reemplazados -->
                    <div class="row" id="campos-reemplazados-segunda-seccion" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="encargado_reemplazado_segunda_seccion" class="form-label">Encargado Reemplazado</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-danger" onclick="clearEncargadoReemplazado()" title="Eliminar encargado reemplazado">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                    <input type="text" class="form-control" id="encargado_reemplazado_segunda_seccion" readonly>
                                    <input type="hidden" id="encargado_reemplazado_id_segunda_seccion" name="encargado_reemplazado_id">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ayudante_reemplazado_segunda_seccion" class="form-label">Ayudante Reemplazado</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-danger" onclick="clearAyudanteReemplazado()" title="Eliminar ayudante reemplazado">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                    <input type="text" class="form-control" id="ayudante_reemplazado_segunda_seccion" readonly>
                                    <input type="hidden" id="ayudante_reemplazado_id_segunda_seccion" name="ayudante_reemplazado_id">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="leccion_segunda_seccion" class="form-label">Lección <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="leccion_segunda_seccion" name="leccion" maxlength="500" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveParteSegundaSeccionBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Parte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(Auth::user()->perfil == 3)
<!-- Modal para Crear/Editar Parte de Seamos Mejores Maestros -->
<div class="modal fade" id="parteProgramaTerceraSeccionModal" tabindex="-1" aria-labelledby="parteProgramaTerceraSeccionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="parteProgramaTerceraSeccionModalLabel">Nueva Asignación de Seamos Mejores Maestros</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="parteProgramaTerceraSeccionForm">
                <div class="modal-body">
                    <!-- Contenedor de alertas del modal -->
                    <div id="modal-alert-container-tercera-seccion"></div>

                    @csrf
                    <input type="hidden" id="parte_programa_tercera_seccion_id" name="parte_programa_id">
                    <input type="hidden" id="programa_id_tercera_seccion" name="programa_id" value="{{ $programa->id }}">
                    <input type="hidden" name="sala_id" value="2">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="parte_id_tercera_seccion" class="form-label">Asignación <span class="text-danger">*</span></label>
                                <select class="form-select" id="parte_id_tercera_seccion" name="parte_id" required>
                                    <option value="">Cargando...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tiempo_tercera_seccion" class="form-label">Tiempo (minutos) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="tiempo_tercera_seccion" name="tiempo" min="1" max="60" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="encargado_id_tercera_seccion" class="form-label">Encargado <span class="text-danger">*</span></label>
                                <div class="d-flex">
                                    <button type="button" class="btn btn-outline-primary me-2" id="btn-agregar-encargado-reemplazado-tercera" onclick="agregarEncargadoReemplazadoTercera()" title="Agregar como encargado reemplazado" style="display: none;">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                    <select class="form-select select2" id="encargado_id_tercera_seccion" name="encargado_id" required style="flex: 1;">
                                        <option value="">Seleccionar una parte primero...</option>
                                    </select>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ayudante_id_tercera_seccion" class="form-label">Ayudante</label>
                                <div class="d-flex">
                                    <button type="button" class="btn btn-outline-primary me-2" id="btn-agregar-ayudante-reemplazado-tercera" onclick="agregarAyudanteReemplazadoTercera()" title="Agregar como ayudante reemplazado" style="display: none;">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                    <select class="form-select select2" id="ayudante_id_tercera_seccion" name="ayudante_id" style="flex: 1;">
                                        <option value="">Seleccionar...</option>
                                    </select>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Campos de Reemplazados -->
                    <div class="row" id="campos-reemplazados-tercera-seccion">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="encargado_reemplazado_tercera_seccion" class="form-label">Encargado Reemplazado</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-danger" onclick="clearEncargadoReemplazadoTercera()" title="Eliminar encargado reemplazado">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                    <input type="text" class="form-control" id="encargado_reemplazado_tercera_seccion" readonly>
                                    <input type="hidden" id="encargado_reemplazado_id_tercera_seccion" name="encargado_reemplazado_id">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ayudante_reemplazado_tercera_seccion" class="form-label">Ayudante Reemplazado</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-danger" onclick="clearAyudanteReemplazadoTercera()" title="Eliminar ayudante reemplazado">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                    <input type="text" class="form-control" id="ayudante_reemplazado_tercera_seccion" readonly>
                                    <input type="hidden" id="ayudante_reemplazado_id_tercera_seccion" name="ayudante_reemplazado_id">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="leccion_tercera_seccion" class="form-label">Lección <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="leccion_tercera_seccion" name="leccion" maxlength="500" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveTerceraSeccionBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Parte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal de Confirmación para Eliminar Parte del Programa -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Está seguro de que desea eliminar esta parte del programa?</p>
                <small class="text-muted">Esta acción no se puede deshacer.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-2"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Buscar Orador Inicial -->
<div class="modal fade" id="buscarOradorInicialModal" tabindex="-1" aria-labelledby="buscarOradorInicialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarOradorInicialModalLabel">
                    <i class="fas fa-search me-2"></i>Buscar Orador Inicial
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_orador_inicial" class="form-label">Seleccionar Orador Inicial</label>
                    <select class="form-select" id="select_orador_inicial" style="width: 100%;">
                        <option value="">Cargando usuarios...</option>
                    </select>
                    <small class="form-text text-muted">Usuarios con asignación de oración, ordenados por fecha más reciente de participación</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarOradorInicial">
                    <i class="fas fa-check me-2"></i>Seleccionar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Historial de Orador Inicial -->
<div class="modal fade" id="historialOradorInicialModal" tabindex="-1" aria-labelledby="historialOradorInicialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historialOradorInicialModalLabel">
                    <i class="fas fa-history me-2"></i>Historial de Participaciones
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_historial_orador" class="form-label">Historial de Participaciones como Orador</label>
                    <select class="form-select" id="select_historial_orador" style="width: 100%;" disabled>
                        <option value="">Cargando historial...</option>
                    </select>
                    <small class="form-text text-muted">Participaciones ordenadas desde la más reciente</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Buscar Orador Final -->
<div class="modal fade" id="buscarOradorFinalModal" tabindex="-1" aria-labelledby="buscarOradorFinalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarOradorFinalModalLabel">
                    <i class="fas fa-search me-2"></i>Buscar Orador Final
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_orador_final" class="form-label">Seleccionar Orador Final</label>
                    <select class="form-select" id="select_orador_final" style="width: 100%;">
                        <option value="">Cargando usuarios...</option>
                    </select>
                    <small class="form-text text-muted">Usuarios con asignación de oración, ordenados por fecha más reciente de participación</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarOradorFinal">
                    <i class="fas fa-check me-2"></i>Seleccionar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Historial de Orador Final -->
<div class="modal fade" id="historialOradorFinalModal" tabindex="-1" aria-labelledby="historialOradorFinalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historialOradorFinalModalLabel">
                    <i class="fas fa-history me-2"></i>Historial de Participaciones
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_historial_orador_final" class="form-label">Historial de Participaciones como Orador</label>
                    <select class="form-select" id="select_historial_orador_final" style="width: 100%;" disabled>
                        <option value="">Cargando historial...</option>
                    </select>
                    <small class="form-text text-muted">Participaciones ordenadas desde la más reciente</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Buscar Presidencia -->
<div class="modal fade" id="buscarPresidenciaModal" tabindex="-1" aria-labelledby="buscarPresidenciaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarPresidenciaModalLabel">
                    <i class="fas fa-search me-2"></i>Buscar Presidentes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_presidencia" class="form-label">Seleccionar Presidente</label>
                    <select class="form-select" id="select_presidencia" style="width: 100%;">
                        <option value="">Cargando usuarios...</option>
                    </select>
                    <small class="form-text text-muted">Usuarios con asignación de presidencia, ordenados por fecha más reciente de participación</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarPresidencia">
                    <i class="fas fa-check me-2"></i>Seleccionar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Historial de Presidencia -->
<div class="modal fade" id="historialPresidenciaModal" tabindex="-1" aria-labelledby="historialPresidenciaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historialPresidenciaModalLabel">
                    <i class="fas fa-history me-2"></i>Historial de Participaciones
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_historial_presidencia" class="form-label">Historial de Participaciones como Presidente</label>
                    <select class="form-select" id="select_historial_presidencia" style="width: 100%;" disabled>
                        <option value="">Cargando historial...</option>
                    </select>
                    <small class="form-text text-muted">Participaciones ordenadas desde la más reciente</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Buscar Canción Inicial -->
<div class="modal fade" id="buscarCancionInicialModal" tabindex="-1" aria-labelledby="buscarCancionInicialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarCancionInicialModalLabel">
                    <i class="fas fa-search me-2"></i>Buscar Canción Inicial
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_cancion_inicial" class="form-label">Seleccionar Canción</label>
                    <select class="form-select" id="select_cancion_inicial" style="width: 100%;">
                        <option value="">Cargando canciones...</option>
                    </select>
                    <small class="form-text text-muted">Todas las canciones disponibles</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarCancionInicial">
                    <i class="fas fa-check me-2"></i>Seleccionar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Buscar Canción Intermedia -->
<div class="modal fade" id="buscarCancionIntermediaModal" tabindex="-1" aria-labelledby="buscarCancionIntermediaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarCancionIntermediaModalLabel">
                    <i class="fas fa-search me-2"></i>Buscar Canción Intermedia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_cancion_intermedia" class="form-label">Seleccionar Canción</label>
                    <select class="form-select" id="select_cancion_intermedia" style="width: 100%;">
                        <option value="">Cargando canciones...</option>
                    </select>
                    <small class="form-text text-muted">Todas las canciones disponibles</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarCancionIntermedia">
                    <i class="fas fa-check me-2"></i>Seleccionar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Buscar Canción Final -->
<div class="modal fade" id="buscarCancionFinalModal" tabindex="-1" aria-labelledby="buscarCancionFinalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarCancionFinalModalLabel">
                    <i class="fas fa-search me-2"></i>Buscar Canción Final
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_cancion_final" class="form-label">Seleccionar Canción</label>
                    <select class="form-select" id="select_cancion_final" style="width: 100%;">
                        <option value="">Cargando canciones...</option>
                    </select>
                    <small class="form-text text-muted">Todas las canciones disponibles</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarCancionFinal">
                    <i class="fas fa-check me-2"></i>Seleccionar
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Modal para Buscar Encargado del Datatable 1 -->
<div class="modal fade" id="buscarEncargadoParteModal" tabindex="-1" aria-labelledby="buscarEncargadoParteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarEncargadoParteModalLabel">
                    <i class="fas fa-search me-2"></i>Buscar Encargado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_encargado_parte" class="form-label">Seleccionar Encargado</label>
                    <select class="form-select" id="select_encargado_parte" style="width: 100%;">
                        <option value="">Cargando usuarios...</option>
                    </select>
                    <small class="form-text text-muted">Usuarios con participaciones en la parte seleccionada, ordenados por fecha más reciente</small>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarEncargadoParte">
                    <i class="fas fa-check me-2"></i>Seleccionar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Buscar Encargado del Datatable 2 (Segunda Sección) -->
<div class="modal fade" id="buscarEncargadoSegundaSeccionModal" tabindex="-1" aria-labelledby="buscarEncargadoSegundaSeccionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarEncargadoSegundaSeccionModalLabel">
                    <i class="fas fa-search me-2"></i>Buscar Encargado (Sala Principal)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_encargado_segunda_seccion" class="form-label">Seleccionar Encargado</label>
                    <select class="form-select" id="select_encargado_segunda_seccion" style="width: 100%;">
                        <option value="">Cargando usuarios...</option>
                    </select>
                    <small class="form-text text-muted">Usuarios que han participado como encargado o ayudante en partes_programa, ordenados por fecha más reciente. Formato: fecha - parte - nombre (tipo)</small>
                </div>

                <div class="mb-3">
                    <label for="select_historial_encargado_segunda_seccion" class="form-label">Historial del Encargado</label>
                    <select class="form-select" id="select_historial_encargado_segunda_seccion" style="width: 100%;" disabled>
                        <option value="">Seleccionar un encargado primero...</option>
                    </select>
                    <small class="form-text text-muted">Historial de participaciones del encargado seleccionado en la segunda sección, ordenadas desde la más reciente</small>
                </div>
            </div>
            <br><br><br><br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarEncargadoSegundaSeccion">
                    <i class="fas fa-check me-2"></i>Seleccionar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Buscar Ayudante del Datatable 2 (Segunda Sección) -->
<div class="modal fade" id="buscarAyudanteSegundaSeccionModal" tabindex="-1" aria-labelledby="buscarAyudanteSegundaSeccionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarAyudanteSegundaSeccionModalLabel">
                    <i class="fas fa-search me-2"></i>Buscar Ayudante (Sala Principal)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_ayudante_segunda_seccion" class="form-label">Seleccionar Ayudante</label>
                    <select class="form-select" id="select_ayudante_segunda_seccion" style="width: 100%;">
                        <option value="">Cargando usuarios...</option>
                    </select>
                    <small class="form-text text-muted">Usuarios que han participado como ayudante en partes_programa, ordenados por fecha más reciente. Formato: fecha - parte - nombre (tipo)</small>
                </div>

                <div class="mb-3">
                    <label for="select_historial_ayudante_segunda_seccion" class="form-label">Historial del Ayudante</label>
                    <select class="form-select" id="select_historial_ayudante_segunda_seccion" style="width: 100%;" disabled>
                        <option value="">Seleccionar un ayudante primero...</option>
                    </select>
                    <small class="form-text text-muted">Historial de participaciones del ayudante seleccionado en la segunda sección, ordenadas desde la más reciente</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarAyudanteSegundaSeccion">
                    <i class="fas fa-check me-2"></i>Seleccionar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Historial de Encargado del Datatable 1 -->
<div class="modal fade" id="historialEncargadoParteModal" tabindex="-1" aria-labelledby="historialEncargadoParteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historialEncargadoParteModalLabel">
                    <i class="fas fa-history me-2"></i>Historial de Participaciones como Encargado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_historial_encargado_parte" class="form-label">Historial de Participaciones</label>
                    <select class="form-select" id="select_historial_encargado_parte" style="width: 100%;" disabled>
                        <option value="">Cargando historial...</option>
                    </select>
                    <small class="form-text text-muted">Participaciones como encargado en la parte actual, ordenadas desde la más reciente</small>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Agregar Encargado Reemplazado -->
<div class="modal fade" id="confirmarAgregarReemplazadoModal" tabindex="-1" aria-labelledby="confirmarAgregarReemplazadoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarAgregarReemplazadoModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Confirmar Agregar Encargado Reemplazado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Desea agregar a <strong id="nombreEncargadoAgregar"></strong> como Encargado Reemplazado?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmarAgregarReemplazado">
                    <i class="fas fa-check me-2"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar Encargado Reemplazado -->
<div class="modal fade" id="confirmarEliminarReemplazadoModal" tabindex="-1" aria-labelledby="confirmarEliminarReemplazadoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarEliminarReemplazadoModalLabel">
                    <i class="fas fa-user-times me-2"></i>Confirmar Eliminar Encargado Reemplazado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Desea eliminar a <strong id="nombreEncargadoEliminar"></strong> como Encargado Reemplazado?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarEliminarReemplazado">
                    <i class="fas fa-user-times me-2"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let partesTable;
let partesSegundaSeccionTable;
let partesTerceraSeccionTable;
let isEditMode = false;
const userIsCoordinator = {{ Auth::user()->isCoordinator() ? 'true' : 'false' }};
window.editingParteTwoData = false; // Variable para controlar la carga en modo edición

// Variables para manejar reemplazados
let programmaticChange = false; // Variable para evitar detección de reemplazado en cambios programáticos

$(document).ready(function() {
    // Inicializar Select2 para el campo encargado solo si no es coordinador
    @if(Auth::user()->perfil != 3)
    $('#encargado_id').select2({
        placeholder: 'Seleccionar...',
        allowClear: true,
        dropdownParent: $('#parteProgramaModal'),
        theme: 'bootstrap-5',
        width: '100%',
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            },
            searching: function() {
                return "Buscando...";
            }
        }
    });
    @endif

    // Inicializar DataTable para partes del programa
    initPartesDataTable();

    // Cargar partes del programa
    loadPartesPrograma();

    // Inicializar DataTable para partes de la segunda sección (solo para coordinadores)
    @if(Auth::user()->perfil == 3)
    initPartesSegundaSeccionDataTable();
    initPartesTerceraSeccionDataTable();

    // Cargar partes de la segunda sección
    loadPartesSegundaSeccion();
    // Cargar partes de la tercera sección
    loadPartesTerceraSeccion();
    @endif

    // Manejar envío del formulario de partes del programa
    $('#parteProgramaForm').submit(function(e) {
        console.log('Form submit event triggered');
        e.preventDefault();
        submitPartePrograma();
    });

    // Debugging adicional para el botón
    $('#saveParteBtn').on('click', function(e) {
        console.log('Save button clicked - debugging only');
        // No prevenir el evento por defecto aquí para no interferir
    });

    // Manejar cambio en el select de parte_id para autocompletar el tiempo y filtrar encargados
    $(document).on('change', '#parte_id', function() {
        const selectedOption = $(this).find('option:selected');
        const tiempo = selectedOption.data('tiempo');
        const parteId = $(this).val();



        // Autocompletar tiempo
        if (tiempo) {
            $('#tiempo_parte').val(tiempo);
        } else {
            $('#tiempo_parte').val('');
        }

        // Manejar campos de encargado según el perfil del usuario
        if (userIsCoordinator) {
            // Para coordinadores, habilitar/deshabilitar botón de buscar encargado
            if (parteId) {
                $('#btn-buscar-encargado').prop('disabled', false);
            } else {
                $('#btn-buscar-encargado').prop('disabled', true);
                // También limpiar los campos si no hay parte seleccionada
                $('#encargado_id').val('');
                $('#encargado_display').val('');
                $('#btn-historial-encargado').prop('disabled', true);
                $('#btn-agregar-reemplazado').prop('disabled', true);
            }
        } else {
            // Para otros perfiles, filtrar usuarios del campo encargado basado en la parte seleccionada
            if (parteId) {
                loadEncargadosByParte(parteId);
            } else {
                // Si no hay parte seleccionada, cargar todos los usuarios disponibles
                loadUsuariosDisponibles();
            }
        }
    });

    // Manejar envío del formulario de editar programa
    $('#editProgramaForm').submit(function(e) {
        e.preventDefault();

        const programaId = $('#programa_id').val();
        const submitBtn = $('#updateProgramaBtn');
        const spinner = submitBtn.find('.spinner-border');

        // Deshabilitar botón y mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#alert-container').empty();

        $.ajax({
            url: `/programas/${programaId}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    // Mostrar mensaje de éxito y redirigir
                    showAlert('alert-container', 'success', 'Programa actualizado exitosamente');
                    setTimeout(function() {
                        window.location.href = '{{ route("programas.index") }}';
                    }, 1500);
                } else {
                    showAlert('alert-container', 'danger', response.message || 'Error al actualizar el programa');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#${field}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                } else {
                    showAlert('alert-container', 'danger', 'Error al actualizar el programa');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Función para mostrar alertas
    function showAlert(container, type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $(`#${container}`).html(alertHtml);
    }

    // Funciones para partes del programa
    function initPartesDataTable() {
        partesTable = $('#partesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            responsive: true,
            paging: false,
            ordering: false,
            info: false,
            searching: false
        });
    }

    function initPartesSegundaSeccionDataTable() {
        partesSegundaSeccionTable = $('#partesSegundaSeccionTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            responsive: true,
            paging: false,
            ordering: false,
            info: false,
            searching: false
        });
    }

    function initPartesTerceraSeccionDataTable() {
        partesTerceraSeccionTable = $('#partesTerceraSeccionTable').DataTable({
            language: {
                emptyTable: "No hay partes asignadas en esta sección",
                zeroRecords: "No se encontraron partes que coincidan con la búsqueda"
            },
            responsive: true,
            ordering: false,
            paging: false,
            info: false,
            searching: false
        });
    }

    function loadPartesSegundaSeccion() {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes-segunda-seccion`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    partesSegundaSeccionTable.clear();

                    response.data.forEach(function(parte) {
                        const upDisabled = parte.es_primero ? 'disabled' : '';
                        const downDisabled = parte.es_ultimo ? 'disabled' : '';

                        let acciones = `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteSegundaSeccionUp(${parte.id})" title="Subir" ${upDisabled}>
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteSegundaSeccionDown(${parte.id})" title="Bajar" ${downDisabled}>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editParteSegundaSeccion(${parte.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteParteSegundaSeccion(${parte.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;

                        let rowData = [
                            parte.tiempo || '-',
                            parte.parte_abreviacion || '-',
                            parte.encargado_nombre || '-',
                            parte.ayudante_nombre || '-',
                            parte.leccion || '-',
                            acciones
                        ];

                        partesSegundaSeccionTable.row.add(rowData);
                    });

                    partesSegundaSeccionTable.draw();
                } else {
                    console.error('Error al cargar las partes de la segunda sección:', response.message);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar las partes de la segunda sección:', xhr.responseText);
            }
        });
    }

    function loadPartesTerceraSeccion() {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes-tercera-seccion`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Limpiar la tabla
                    partesTerceraSeccionTable.clear();

                    response.data.forEach(function(parte) {
                        const upDisabled = parte.es_primero ? 'disabled' : '';
                        const downDisabled = parte.es_ultimo ? 'disabled' : '';

                        let acciones = `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteTerceraSeccionUp(${parte.id})" title="Subir" ${upDisabled}>
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteTerceraSeccionDown(${parte.id})" title="Bajar" ${downDisabled}>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editParteTerceraSeccion(${parte.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteParteTerceraSeccion(${parte.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;

                        let rowData = [
                            parte.tiempo || '-',
                            parte.parte_abreviacion || '-',
                            parte.encargado_nombre || '-',
                            parte.ayudante_nombre || '-',
                            parte.leccion || '-',
                            acciones
                        ];

                        partesTerceraSeccionTable.row.add(rowData);
                    });

                    // Dibujar la tabla
                    partesTerceraSeccionTable.draw();
                } else {
                    console.error('Error en la respuesta:', response);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar las partes de la tercera sección:', xhr.responseText);
                showAlert('alert-container', 'danger', 'Error al cargar las partes de la tercera sección');
            }
        });
    }

    function loadPartesPrograma() {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    partesTable.clear();

                    response.data.forEach(function(parte) {
                        let acciones = `
                            <div class="btn-group" role="group">
                        `;

                        if (userIsCoordinator) {
                            const upDisabled = parte.es_primero ? 'disabled' : '';
                            const downDisabled = parte.es_ultimo ? 'disabled' : '';

                            acciones += `
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteUp(${parte.id})" title="Subir" ${upDisabled}>
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteDown(${parte.id})" title="Bajar" ${downDisabled}>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            `;
                        }

                        acciones += `
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editParte(${parte.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                        `;

                        if (userIsCoordinator) {
                            acciones += `
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteParte(${parte.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            `;
                        }

                        acciones += `
                            </div>
                        `;

                        let rowData = [
                            parte.tiempo || '-',
                            parte.parte_abreviacion || '-',
                            parte.encargado_nombre || '-',
                            parte.tema || '-'
                        ];

                        // Solo agregar la columna de lección si no es coordinador
                        if (!userIsCoordinator) {
                            rowData.push(parte.leccion || '-');
                        }

                        rowData.push(acciones);

                        partesTable.row.add(rowData);
                    });

                    partesTable.draw();
                }
            },
            error: function(xhr) {
                showAlert('alert-container', 'danger', 'Error al cargar las partes del programa');
            }
        });
    }

    function openCreateParteModal() {
        isEditMode = false;
        const seccionNombre = '{{ $seccionReunion ? $seccionReunion->nombre : "Primera Sección" }}';
        $('#parteProgramaModalLabel').text('Nueva Asignación de ' + seccionNombre);
        $('#saveParteBtn').text('Guardar Parte');
        $('#parteProgramaForm')[0].reset();

        // Mostrar select y ocultar input de texto para "Asignación" en modo "nuevo"
        $('#parte_id').show();
        $('#parte_display').hide();

        // Ocultar campo y botón de encargado reemplazado en modo "nuevo"
        if (userIsCoordinator) {
            $('#encargado_reemplazado_display').closest('.col-md-6').hide();
            $('#btn-agregar-reemplazado').hide();
        }
        $('#parte_programa_id').val('');

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Restablecer campos de encargado según el perfil del usuario
        if (userIsCoordinator) {
            // Para coordinadores, limpiar los campos de texto e input hidden
            $('#encargado_id').val('');
            $('#encargado_display').val('');
            $('#btn-buscar-encargado').prop('disabled', true);
            $('#btn-historial-encargado').prop('disabled', true);
            $('#btn-agregar-reemplazado').prop('disabled', true);
            // Limpiar campos de encargado reemplazado
            $('#encargado_reemplazado_id').val('');
            $('#encargado_reemplazado_display').val('');
            $('#btn-eliminar-reemplazado').prop('disabled', true);
        } else {
            // Para otros perfiles, restablecer el select de encargados
            $('#encargado_id').empty().append('<option value="">Seleccionar una parte primero...</option>').trigger('change');
        }

        // Cargar partes de sección disponibles con Ajax
        loadPartesSecciones();

        $('#parteProgramaModal').modal('show');
    }

    function editParte(id) {
        isEditMode = true;
        $('#parteProgramaModalLabel').text('Editar Asignación del Programa');
        $('#saveParteBtn').text('Actualizar Parte');

        // Ocultar select y mostrar input de texto para "Asignación" en modo "editar"
        $('#parte_id').hide();
        $('#parte_display').show();

        // Mostrar campo y botón de encargado reemplazado en modo "editar"
        if (userIsCoordinator) {
            $('#encargado_reemplazado_display').closest('.col-md-6').show();
            $('#btn-agregar-reemplazado').show();
        }

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: `/partes-programa/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const parte = response.data;
                    $('#parte_programa_id').val(parte.id);
                    $('#tiempo_parte').val(parte.tiempo);
                    $('#tema_parte').val(parte.tema);

                    // Solo establecer lección si no es coordinador
                    if (!userIsCoordinator) {
                        $('#leccion_parte').val(parte.leccion);
                    }

                    // Cargar solo la parte correspondiente en modo edición
                    loadPartesSeccionesForEdit(parte.parte_id, function() {
                        // Manejar el campo encargado según el perfil del usuario
                        if (userIsCoordinator) {
                            // Para coordinadores, usar los campos de texto e input hidden
                            $('#encargado_id').val(parte.encargado_id);
                            $('#encargado_display').val(parte.encargado ? parte.encargado.name : '');

                            // Cargar encargado reemplazado si existe
                            if (parte.encargado_reemplazado_id && parte.encargado_reemplazado) {
                                $('#encargado_reemplazado_id').val(parte.encargado_reemplazado_id);
                                $('#encargado_reemplazado_display').val(parte.encargado_reemplazado.name);
                                $('#btn-eliminar-reemplazado').prop('disabled', false);
                            } else {
                                $('#encargado_reemplazado_id').val('');
                                $('#encargado_reemplazado_display').val('');
                                $('#btn-eliminar-reemplazado').prop('disabled', true);
                            }

                            // Habilitar el botón de buscar encargado ya que hay una parte seleccionada
                            $('#btn-buscar-encargado').prop('disabled', false);

                            // Habilitar/deshabilitar el botón de historial según si hay encargado
                            if (parte.encargado_id) {
                                $('#btn-historial-encargado').prop('disabled', false);
                                $('#btn-agregar-reemplazado').prop('disabled', false);
                            } else {
                                $('#btn-historial-encargado').prop('disabled', true);
                                $('#btn-agregar-reemplazado').prop('disabled', true);
                            }
                        } else {
                            // Para otros perfiles, cargar las opciones del select2
                            if (parte.parte_id) {
                                loadEncargadosByParte(parte.parte_id, function() {
                                    // Después de cargar las opciones, establecer el valor seleccionado
                                    $('#encargado_id').val(parte.encargado_id).trigger('change');
                                });
                            } else {
                                // Si no hay parte_id, solo establecer el encargado_id
                                $('#encargado_id').val(parte.encargado_id).trigger('change');
                            }
                        }

                        $('#parteProgramaModal').modal('show');
                    });
                }
            },
            error: function(xhr) {
                showAlert('alert-container', 'danger', 'Error al cargar los datos de la parte');
            }
        });
    }

    function submitPartePrograma() {
        console.log('submitPartePrograma() called');
        const submitBtn = $('#saveParteBtn');
        const spinner = submitBtn.find('.spinner-border');

        // Deshabilitar botón y mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        console.log('Button disabled and spinner shown');

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        console.log('Errors cleared');

        // Validar campo tiempo
        const tiempoValue = $('#tiempo_parte').val();
        console.log('Tiempo value:', tiempoValue);
        if (!tiempoValue || tiempoValue < 1) {
            console.log('Tiempo validation failed');
            $('#tiempo_parte').addClass('is-invalid');
            $('#tiempo_parte').siblings('.invalid-feedback').text('El campo Tiempo es obligatorio y debe ser mayor a 0.');
            submitBtn.prop('disabled', false);
            spinner.addClass('d-none');
            return;
        }
        console.log('Tiempo validation passed');

        // Detectar qué modal está activo para usar el campo de lección correcto
        let leccionFieldId = '';
        let leccionValue = '';

        if ($('#parteProgramaModal').hasClass('show')) {
            // Primera sección - solo tiene campo de lección si NO es coordinador
            if (!userIsCoordinator) {
                leccionFieldId = 'leccion_parte';
                leccionValue = $('#leccion_parte').val();
                console.log('Using first section leccion field (non-coordinator)');
            } else {
                console.log('First section modal active but user is coordinator - no leccion field');
            }
        } else if ($('#parteProgramaSegundaSeccionModal').hasClass('show')) {
            // Segunda sección (coordinadores)
            leccionFieldId = 'leccion_segunda_seccion';
            leccionValue = $('#leccion_segunda_seccion').val();
            console.log('Using second section leccion field');
        } else if ($('#parteProgramaTerceraSeccionModal').hasClass('show')) {
            // Tercera sección (coordinadores)
            leccionFieldId = 'leccion_tercera_seccion';
            leccionValue = $('#leccion_tercera_seccion').val();
            console.log('Using third section leccion field');
        }

        console.log('userIsCoordinator:', userIsCoordinator);
        console.log('Leccion field ID:', leccionFieldId);
        console.log('Leccion value:', leccionValue);

        // Validar campo leccion solo si existe el campo
        if (leccionFieldId && (!leccionValue || leccionValue.trim() === '')) {
            console.log('Leccion validation failed');
            $(`#${leccionFieldId}`).addClass('is-invalid');
            $(`#${leccionFieldId}`).siblings('.invalid-feedback').text('El campo Lección es obligatorio.');
            submitBtn.prop('disabled', false);
            spinner.addClass('d-none');
            return;
        } else if (leccionFieldId) {
            console.log('Leccion validation passed');
        } else {
            console.log('No leccion field to validate');
        }

        // Crear FormData manualmente para asegurar que incluya todos los campos
        const formDataObj = {
            'programa_id': $('#programa_id_parte').val(),
            'parte_id': isEditMode ? $('#parte_id_hidden').val() : $('#parte_id').val(),
            'tiempo': $('#tiempo_parte').val(),
            'tema': $('#tema_parte').val(),
            'encargado_id': $('#encargado_id').val(),
            'encargado_reemplazado_id': $('#encargado_reemplazado_id').val(),
            '_token': $('meta[name="csrf-token"]').attr('content')
        };

        console.log('Form data object:', formDataObj);

        // Agregar lección al formDataObj solo si existe el campo
        if (leccionValue) {
            formDataObj['leccion'] = leccionValue;
        }

        const url = isEditMode ? `/partes-programa/${$('#parte_programa_id').val()}` : '/partes-programa';
        const method = isEditMode ? 'PUT' : 'POST';

        // Para métodos PUT, agregar el método al formulario
        if (method === 'PUT') {
            formDataObj['_method'] = 'PUT';
        }

        console.log('About to send AJAX request');
        console.log('Final formDataObj:', formDataObj);
        console.log('AJAX URL:', url);
        console.log('AJAX Method:', method);

        $.ajax({
            url: url,
            method: 'POST', // Laravel maneja PUT a través de POST con _method
            data: formDataObj,
            success: function(response) {
                console.log('AJAX success response:', response);
                if (response.success) {
                    $('#parteProgramaModal').modal('hide');
                    loadPartesPrograma();
                    showAlert('alert-container', 'success', response.message);
                }
            },
            error: function(xhr) {
                console.log('AJAX error response:', xhr);
                console.log('AJAX error status:', xhr.status);
                console.log('AJAX error responseText:', xhr.responseText);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    console.log('Validation errors:', errors);
                    for (const field in errors) {
                        let fieldName = field;
                        if (field === 'tiempo') fieldName = 'tiempo_parte';
                        if (field === 'tema') fieldName = 'tema_parte';
                        if (field === 'leccion' && leccionFieldId) fieldName = leccionFieldId; // Solo usar si existe el campo

                        $(`#${fieldName}`).addClass('is-invalid');
                        $(`#${fieldName}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                } else {
                    const errorMessage = xhr.responseJSON?.message || `Error ${xhr.status}: ${xhr.statusText}`;
                    showAlert('alert-container', 'danger', errorMessage);
                }
            },
            complete: function() {
                console.log('AJAX request completed');
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });

        console.log('End of submitPartePrograma function');
    }

    function deleteParte(id) {
        // Mostrar el modal de confirmación
        $('#confirmDeleteModal').modal('show');

        // Manejar la confirmación de eliminación
        $('#confirmDeleteBtn').off('click').on('click', function() {
            const deleteBtn = $(this);
            const originalText = deleteBtn.html();

            // Deshabilitar botón y mostrar spinner
            deleteBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Eliminando...');

            $.ajax({
                url: `/partes-programa/${id}`,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#confirmDeleteModal').modal('hide');
                        loadPartesPrograma();
                        showAlert('alert-container', 'success', response.message);
                    }
                },
                error: function(xhr) {
                    $('#confirmDeleteModal').modal('hide');
                    showAlert('alert-container', 'danger', xhr.responseJSON?.message || 'Error al eliminar la parte');
                },
                complete: function() {
                    // Restaurar el botón
                    deleteBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    }

    function loadEncargadosByParte(parteId, callback) {
        $.ajax({
            url: `/usuarios-por-parte/${parteId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Limpiar el select
                    $('#encargado_id').empty().append('<option value="">Seleccionar...</option>');

                    // Agregar las opciones de usuarios filtrados con fecha de última participación
                    response.data.forEach(function(usuario) {
                        // Formatear como "fecha | nombre"
                        let displayText;
                        if (usuario.ultima_fecha) {
                            // Formatear fecha a dd/mm/AAAA
                            let fecha = new Date(usuario.ultima_fecha);
                            let fechaFormateada = fecha.getDate().toString().padStart(2, '0') + '/' +
                                                (fecha.getMonth() + 1).toString().padStart(2, '0') + '/' +
                                                fecha.getFullYear();
                            displayText = `${fechaFormateada} | ${usuario.name}`;
                        } else {
                            displayText = `Primera vez | ${usuario.name}`;
                        }

                        $('#encargado_id').append(
                            `<option value="${usuario.id}">${displayText}</option>`
                        );
                    });

                    // Actualizar Select2 después de agregar opciones
                    $('#encargado_id').trigger('change');

                    // Ejecutar callback si se proporciona
                    if (typeof callback === 'function') {
                        callback();
                    }
                } else {
                    showAlert('alert-container', 'warning', 'No se encontraron usuarios para esta parte');
                    $('#encargado_id').empty().append('<option value="">Seleccionar...</option>').trigger('change');

                    // Ejecutar callback incluso si no hay usuarios
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            },
            error: function(xhr) {
                showAlert('alert-container', 'danger', 'Error al cargar los usuarios para esta parte');
                $('#encargado_id').empty().append('<option value="">Seleccionar...</option>').trigger('change');

                // Ejecutar callback incluso en caso de error
                if (typeof callback === 'function') {
                    callback();
                }
            }
        });
    }

    function loadPartesSecciones() {
        const programaId = $('#programa_id').val();

        $('#parte_id').empty().append('<option value="">Cargando...</option>');

        $.ajax({
            url: '/partes-secciones',
            method: 'GET',
            data: {
                programa_id: programaId
            },
            success: function(response) {
                if (response.success) {
                    $('#parte_id').empty().append('<option value="">Seleccionar...</option>');

                    response.data.forEach(function(parte) {
                        $('#parte_id').append(
                            `<option value="${parte.id}" data-tiempo="${parte.tiempo || ''}">${parte.nombre} (${parte.abreviacion})</option>`
                        );
                    });

                    // Para coordinadores, mantener el campo encargado vacío por defecto
                    if (userIsCoordinator) {
                        $('#encargado_id').empty().append('<option value="">Seleccionar una parte primero...</option>').trigger('change');
                    } else {
                        // Para otros perfiles, cargar usuarios disponibles inicialmente
                        loadUsuariosDisponibles();
                    }
                } else {
                    $('#parte_id').empty().append('<option value="">No hay partes disponibles</option>');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar partes de sección:', xhr);
                $('#parte_id').empty().append('<option value="">Error al cargar</option>');
            }
        });
    }

    function loadUsuariosDisponibles() {
        $('#encargado_id').empty().append('<option value="">Cargando...</option>');

        $.ajax({
            url: '/usuarios-disponibles',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#encargado_id').empty().append('<option value="">Seleccionar...</option>');

                    response.data.forEach(function(usuario) {
                        // Formatear como "fecha | nombre"
                        let displayText;
                        if (usuario.ultima_fecha) {
                            // Formatear fecha a dd/mm/AAAA
                            let fecha = new Date(usuario.ultima_fecha);
                            let fechaFormateada = fecha.getDate().toString().padStart(2, '0') + '/' +
                                                (fecha.getMonth() + 1).toString().padStart(2, '0') + '/' +
                                                fecha.getFullYear();
                            displayText = `${fechaFormateada} | ${usuario.name}`;
                        } else {
                            displayText = `Primera vez | ${usuario.name}`;
                        }

                        $('#encargado_id').append(`<option value="${usuario.id}">${displayText}</option>`);
                    });

                    // Actualizar Select2 después de agregar opciones
                    $('#encargado_id').trigger('change');
                } else {
                    $('#encargado_id').empty().append('<option value="">No hay usuarios disponibles</option>');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar usuarios:', xhr);
                $('#encargado_id').empty().append('<option value="">Error al cargar</option>');
            }
        });
    }

    function loadPartesSeccionesForEdit(parteId, callback) {
        $('#parte_id').empty().append('<option value="">Cargando...</option>');

        $.ajax({
            url: `/partes-seccion/${parteId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const parte = response.data;
                    $('#parte_id').empty();

                    // Solo agregar la parte que corresponde al registro que se está editando
                    $('#parte_id').append(
                        `<option value="${parte.id}" data-tiempo="${parte.tiempo || ''}" selected>${parte.nombre} (${parte.abreviacion})</option>`
                    );

                    // Llenar el campo de texto deshabilitado para el modo editar
                    $('#parte_display').val(parte.nombre);
                    $('#parte_id_hidden').val(parte.id);

                    // Autocompletar el tiempo si está disponible
                    if (parte.tiempo) {
                        $('#tiempo_parte').val(parte.tiempo);
                    }

                    // Ejecutar callback si se proporciona
                    if (typeof callback === 'function') {
                        callback();
                    }
                } else {
                    $('#parte_id').empty().append('<option value="">Error al cargar la parte</option>');

                    // Ejecutar callback incluso si hay error
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error al cargar parte de sección:', xhr);
                $('#parte_id').empty().append('<option value="">Error al cargar</option>');

                // Ejecutar callback incluso en caso de error
                if (typeof callback === 'function') {
                    callback();
                }
            }
        });
    }

    // Inicializar Select2 para presidencia, oradores y canciones solo si es coordinador
    @if(Auth::user()->perfil == 3)



    @endif

    // Funciones para segunda sección
    @if(Auth::user()->perfil == 3)

    // Inicializar Select2 para el modal de tercera sección
    $('#encargado_id_tercera_seccion').select2({
        placeholder: 'Seleccionar...',
        allowClear: true,
        dropdownParent: $('#parteProgramaTerceraSeccionModal'),
        theme: 'bootstrap-5',
        width: '100%',
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            }
        }
    });

    $('#ayudante_id_tercera_seccion').select2({
        placeholder: 'Seleccionar...',
        allowClear: true,
        dropdownParent: $('#parteProgramaTerceraSeccionModal'),
        theme: 'bootstrap-5',
        width: '100%',
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            }
        }
    });

    // Manejar envío del formulario de segunda sección
    $('#parteProgramaSegundaSeccionForm').submit(function(e) {
        e.preventDefault();
        submitParteSegundaSeccion();
    });

    // Manejar envío del formulario de tercera sección
    $('#parteProgramaTerceraSeccionForm').submit(function(e) {
        e.preventDefault();
        submitParteTerceraSeccion();
    });

    // Habilitar los botones Buscar Encargado y Buscar Ayudante al seleccionar una parte en la segunda sección
    $(document).on('change', '#parte_id_segunda_seccion', function() {
        const parteId = $(this).val();
        const btnBuscarEncargado = $('#btn-buscar-encargado-segunda');
        const btnBuscarAyudante = $('#btn-buscar-ayudante-segunda');
        // Obtener el tiempo de la opción seleccionada (data-tiempo)
        const selectedOption = $(this).find('option:selected');
        const tiempo = selectedOption.data('tiempo');
        const tipo = selectedOption.data('tipo');

        // Limpiar campos de encargado y ayudante al seleccionar una parte
        $('#encargado_display_segunda_seccion').val('');
        $('#encargado_id_segunda_seccion').val('');
        $('#ayudante_display_segunda_seccion').val('');
        $('#ayudante_id_segunda_seccion').val('');
        // Limpiar la variable global para permitir nuevos cambios
        window.ultimoValorEncargado = null;
        // También limpiar el select2 del ayudante (sin trigger para evitar loops)
        $('#ayudante_id_segunda_seccion').trigger('change');
        $('#btn-encargado-reemplazado-segunda').prop('disabled', true);
        $('#btn-ayudante-reemplazado-segunda').prop('disabled', true);
        if (parteId) {
            btnBuscarEncargado.prop('disabled', false).attr('title', 'Buscar Encargado');
            // El botón "Buscar Ayudante" se mantiene deshabilitado hasta que se seleccione un encargado
            btnBuscarAyudante.prop('disabled', true).attr('title', 'Seleccionar un encargado primero');

            // Cargar el tiempo en el campo correspondiente
            if (typeof tiempo !== 'undefined' && tiempo !== null && tiempo !== '') {
                $('#tiempo_segunda_seccion').val(tiempo);
            } else {
                $('#tiempo_segunda_seccion').val('');
            }
        } else {
            btnBuscarEncargado.prop('disabled', true).attr('title', 'Seleccionar una parte primero');
            btnBuscarAyudante.prop('disabled', true).attr('title', 'Seleccionar una parte primero');
            $('#tiempo_segunda_seccion').val('');
        }
    });

    // Función para continuar el cambio de encargado
    function continueEncargadoChange(encargadoSeleccionado, parteSeleccionada, ayudanteSelect) {
        if (encargadoSeleccionado && parteSeleccionada) {
            // Verificar si el encargado y ayudante actual son del mismo sexo
            const ayudanteActual = ayudanteSelect.val();
            if (ayudanteActual) {
                // Verificar sexos antes de recargar
                verificarSexosYCargarAyudantes(encargadoSeleccionado, ayudanteActual, parteSeleccionada);
            } else {
                // Si no hay ayudante actual, cargar normalmente
                loadAyudantesByEncargadoAndParte(encargadoSeleccionado, parteSeleccionada);
            }

        } else {
            // Si no hay encargado o parte seleccionada, limpiar ayudantes
            programmaticChange = true; // Marcar como cambio programático
            ayudanteSelect.empty().append('<option value="">Seleccionar encargado y parte primero...</option>').trigger('change');
            programmaticChange = false; // Resetear flag
            clearHistorialEncargado();
        }
    }

    // Función para verificar sexos y decidir si recargar ayudantes
    function verificarSexosYCargarAyudantes(encargadoId, ayudanteId, parteId) {
        $.ajax({
            url: '/verificar-sexos-usuarios',
            method: 'GET',
            data: {
                encargado_id: encargadoId,
                ayudante_id: ayudanteId
            },
            success: function(response) {
                if (response.success) {
                    const encargadoSexo = response.encargado_sexo;
                    const ayudanteSexo = response.ayudante_sexo;

                    // Si ambos son del mismo sexo, no recargar ayudantes
                    if (encargadoSexo === ayudanteSexo) {

                        return;
                    }

                    // Si son de diferente sexo, recargar ayudantes
                    loadAyudantesByEncargadoAndParte(encargadoId, parteId);
                } else {
                    // En caso de error, cargar normalmente
                    loadAyudantesByEncargadoAndParte(encargadoId, parteId);
                }
            },
            error: function(xhr) {
                console.error('Error al verificar sexos:', xhr.responseText);
                // En caso de error, cargar normalmente
                loadAyudantesByEncargadoAndParte(encargadoId, parteId);
            }
        });
    }

    // Función para continuar el cambio de ayudante
    function continueAyudanteChange(ayudanteSeleccionado, encargadoSelect) {
        if (ayudanteSeleccionado) {
        }

        // Actualizar Select2 para reflejar los cambios
        encargadoSelect.trigger('change.select2');
    }

    // Manejar cambio en el select de parte_id para tercera sección
    $(document).on('change', '#parte_id_tercera_seccion', function() {
        const selectedOption = $(this).find('option:selected');
        const tiempo = selectedOption.data('tiempo');
        const parteId = $(this).val();
        const encargadoSeleccionado = $('#encargado_id_tercera_seccion').val();

        // Autocompletar tiempo
        if (tiempo) {
            $('#tiempo_tercera_seccion').val(tiempo);
        } else {
            $('#tiempo_tercera_seccion').val('');
        }

        // Filtrar usuarios del campo encargado basado en la parte seleccionada
        if (parteId) {
            loadEncargadosByParteTerceraSeccion(parteId);

            // Si ya hay un encargado seleccionado, actualizar ayudantes con la nueva lógica
            if (encargadoSeleccionado) {
                loadAyudantesByEncargadoAndParteTercera(encargadoSeleccionado, parteId);
            } else {
                // Si no hay encargado seleccionado, cargar ayudantes por parte
                const ayudanteActual = $('#ayudante_id_tercera_seccion').val();
                loadAyudantesByParteTerceraSeccion(ayudanteActual);
            }
        } else {
            loadUsuariosDisponiblesTerceraSeccion();
            loadAyudantesByParteTerceraSeccion();
        }
    });

    // Manejar cambio en el select de encargado para cargar ayudantes (tercera sección)
    $(document).on('change', '#encargado_id_tercera_seccion', function() {
        // No procesar eventos durante la carga en modo edición
        if (window.editingParteTerceraData) {
            return;
        }

        const encargadoSeleccionado = $(this).val();
        const parteSeleccionada = $('#parte_id_tercera_seccion').val();

        // Limpiar historial anterior
        clearHistorialEncargadoTercera();

        if (encargadoSeleccionado) {
            // Cargar historial del encargado seleccionado siempre
            if (parteSeleccionada) {
                // Cargar ayudantes usando la nueva lógica
                loadAyudantesByEncargadoAndParteTercera(encargadoSeleccionado, parteSeleccionada);
            } else {
                // Si no hay parte seleccionada, cargar ayudantes generales
                loadAyudantesByParteTerceraSeccion();
            }
        } else {
            // Si no hay encargado seleccionado
            if (parteSeleccionada) {
                // Si hay parte seleccionada pero no encargado, cargar ayudantes por parte
                loadAyudantesByParteTerceraSeccion();
            } else {
                // Si no hay parte seleccionada, cargar todos los ayudantes disponibles
                loadAyudantesByParteTerceraSeccion();
            }
        }
    });

    // Manejar cambio en el select de ayudante para tercera sección
    $(document).on('change', '#ayudante_id_tercera_seccion', function() {
        // No procesar eventos durante la carga en modo edición
        if (window.editingParteTerceraData) {
            return;
        }

        const ayudanteSeleccionado = $(this).val();
        const encargadoSelect = $('#encargado_id_tercera_seccion');

        // Limpiar historial anterior
        clearHistorialAyudanteTercera();

        // Continuar con la lógica del ayudante
        continueAyudanteChangeTercera(ayudanteSeleccionado, encargadoSelect);
    });

    function openCreateParteSegundaSeccionModal() {
        isEditMode = false;
        $('#parteProgramaSegundaSeccionModalLabel').text('Nueva Asignación (Sala Principal)');
        $('#parteProgramaSegundaSeccionForm')[0].reset();
        $('#parte_programa_segunda_seccion_id').val('');

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Limpiar alertas del modal
        $('#modal-alert-container-segunda-seccion').empty();

        // Cargar partes de la segunda sección
        loadPartesSeccionesSegundaSeccion();

        // Para perfil=3, dejar el campo encargado vacío hasta que se seleccione una parte
        $('#encargado_display_segunda_seccion').val('Seleccionar una parte primero...');
        $('#encargado_id_segunda_seccion').val('');

        // Inicializar campo ayudante
        $('#ayudante_display_segunda_seccion').val('Seleccionar encargado y parte primero...');
        $('#ayudante_id_segunda_seccion').val('');

        // Limpiar historial de encargado
        clearHistorialEncargado();

        // Limpiar historial de ayudante
        clearHistorialAyudante();

        // Ocultar campos de reemplazados en modo nuevo
        $('#campos-reemplazados-segunda-seccion').hide();

        // Ocultar botones de agregar reemplazado en modo nuevo
        $('#btn-agregar-encargado-reemplazado').hide();
        $('#btn-agregar-ayudante-reemplazado').hide();

        // Ocultar botones de reemplazado en modo nuevo
        $('#btn-encargado-reemplazado-segunda').hide();
        $('#btn-ayudante-reemplazado-segunda').hide();

        // Limpiar historial de ayudante
        clearHistorialAyudante();

        // Inicializar estado de los botones "Buscar Encargado" y "Buscar Ayudante" (deshabilitados por defecto)
        $('#btn-buscar-encargado-segunda').prop('disabled', true);
        $('#btn-buscar-encargado-segunda').attr('title', 'Seleccionar una parte primero');
        $('#btn-buscar-ayudante-segunda').prop('disabled', true);
        $('#btn-buscar-ayudante-segunda').attr('title', 'Seleccionar una parte primero');
    }

    function editParteSegundaSeccion(id) {
        isEditMode = true;
        $('#parteProgramaSegundaSeccionModalLabel').text('Editar Asignación (Sala Principal)');
        $('#parte_programa_segunda_seccion_id').val(id);

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Limpiar alertas del modal
        $('#modal-alert-container-segunda-seccion').empty();

        // Limpiar completamente el formulario antes de cargar datos
        $('#parteProgramaSegundaSeccionForm')[0].reset();

        // Limpiar todos los selects
        $('#parte_id_segunda_seccion').empty().append('<option value="">Seleccionar...</option>');
        $('#encargado_display_segunda_seccion').val('Seleccionar...');
        $('#encargado_id_segunda_seccion').val('');
        $('#ayudante_display_segunda_seccion').val('Seleccionar...');
        $('#ayudante_id_segunda_seccion').val('');

        // Actualizar el estado de los botones al limpiar los campos
        updateButtonStatesSegundaSeccion();

        // Limpiar campos de reemplazados
        clearEncargadoReemplazado();
        clearAyudanteReemplazado();

        // Pequeño delay para asegurar que los campos se hayan limpiado
        setTimeout(function() {
            // Limpiar historiales
            clearHistorialEncargado();
            clearHistorialAyudante();

            // Mostrar campos de reemplazados en modo edición
            $('#campos-reemplazados-segunda-seccion').show();

            // Mostrar botones de agregar reemplazado en modo edición
            $('#btn-agregar-encargado-reemplazado').show();
            $('#btn-agregar-ayudante-reemplazado').show();

            // Mostrar botones de reemplazado en modo edición
            $('#btn-encargado-reemplazado-segunda').show();
            $('#btn-ayudante-reemplazado-segunda').show();
        }, 50);

        // Variable para controlar si estamos en modo edición para evitar eventos conflictivos
        window.editingParteTwoData = true;

        $.ajax({
            url: `/partes-programa/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const parte = response.data;
                    $('#parte_id_segunda_seccion').val(parte.parte_id);
                    $('#tiempo_segunda_seccion').val(parte.tiempo);
                    $('#leccion_segunda_seccion').val(parte.leccion);

                    // Cargar nombres de usuarios reemplazados si existen
                    // Cargar encargado reemplazado
                    if (parte.encargado_reemplazado) {
                        $('#encargado_reemplazado_segunda_seccion').val(parte.encargado_reemplazado.name);
                        $('#encargado_reemplazado_id_segunda_seccion').val(parte.encargado_reemplazado.id);
                    } else {
                        $('#encargado_reemplazado_segunda_seccion').val('');
                        $('#encargado_reemplazado_id_segunda_seccion').val('');
                    }

                    // Cargar ayudante reemplazado
                    if (parte.ayudante_reemplazado) {
                        $('#ayudante_reemplazado_segunda_seccion').val(parte.ayudante_reemplazado.name);
                        $('#ayudante_reemplazado_id_segunda_seccion').val(parte.ayudante_reemplazado.id);
                    } else {
                        $('#ayudante_reemplazado_segunda_seccion').val('');
                        $('#ayudante_reemplazado_id_segunda_seccion').val('');
                    }

                    // Asegurar que los campos de reemplazados sean visibles
                    $('#campos-reemplazados-segunda-seccion').show();

                    // Verificar después de un delay que los campos se hayan cargado correctamente
                    setTimeout(function() {
                        // Verificación completada
                    }, 500);                    // Las variables de control de reemplazados se inicializarán después de cargar los selects

                    // Cargar datos necesarios en secuencia
                    loadPartesSeccionesForEditSegundaSeccion(parte.parte_id);

                    // Esperar un poco y luego cargar encargados
                    setTimeout(function() {
                        loadEncargadosByParteSegundaSeccion(parte.parte_id, parte.encargado_id);

                        // Establecer directamente el encargado seleccionado después de un momento
                        setTimeout(function() {
                            if (parte.encargado_id) {
                                $('#encargado_id_segunda_seccion').val(parte.encargado_id);
                                $('#encargado_display_segunda_seccion').val(parte.encargado_nombre);
                            }
                        }, 100);

                        // Esperar otro poco y cargar ayudantes
                        setTimeout(function() {
                            if (parte.encargado_id && parte.parte_id) {
                                loadAyudantesByEncargadoAndParte(parte.encargado_id, parte.parte_id, parte.ayudante_id);
                            } else {
                                loadAyudantesByParteSegundaSeccion(parte.ayudante_id);
                            }

                            // Establecer directamente el ayudante seleccionado después de un momento
                            setTimeout(function() {
                                if (parte.ayudante_id) {
                                    $('#ayudante_id_segunda_seccion').val(parte.ayudante_id);
                                    $('#ayudante_display_segunda_seccion').val(parte.ayudante_nombre);
                                }

                                // Actualizar el estado de los botones después de cargar los datos
                                updateButtonStatesSegundaSeccion();
                            }, 200);

                            // Cargar historial del encargado si existe
                            if (parte.encargado_id) {
                                loadHistorialEncargado(parte.encargado_id);
                            } else {
                                clearHistorialEncargado();
                            }

                            // Liberar el flag después de todo el proceso
                            setTimeout(function() {
                                window.editingParteTwoData = false;

                                // Inicializar variables para control de reemplazados después de que todo esté cargado
                                encargadoAnterior = $('#encargado_id_segunda_seccion').val();
                                const encargadoOption = $('#encargado_id_segunda_seccion').find('option:selected');
                                encargadoAnteriorNombre = encargadoOption.text() || '';

                                ayudanteAnterior = $('#ayudante_id_segunda_seccion').val();
                                const ayudanteOption = $('#ayudante_id_segunda_seccion').find('option:selected');
                                ayudanteAnteriorNombre = ayudanteOption.text() || '';
                            }, 400);

                        }, 300);
                    }, 200);

                    $('#parteProgramaSegundaSeccionModal').modal('show');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar la parte:', xhr.responseText);
                window.editingParteTwoData = false;
            }
        });
    }

    function deleteParteSegundaSeccion(id) {
        $('#confirmDeleteBtn').off('click').on('click', function() {
            $.ajax({
                url: `/partes-programa/${id}`,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        loadPartesSegundaSeccion();
                        $('#confirmDeleteModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    console.error('Error al eliminar la parte:', xhr.responseText);
                }
            });
        });
        $('#confirmDeleteModal').modal('show');
    }

    function submitParteSegundaSeccion() {
        // Validar que Encargado y Ayudante no sean la misma persona
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        const ayudanteId = $('#ayudante_id_segunda_seccion').val();
        const parteSeleccionada = $('#parte_id_segunda_seccion').val();

        if (encargadoId && ayudanteId && encargadoId === ayudanteId) {
            showAlert('modal-alert-container-segunda-seccion', 'warning', 'El Encargado y el Ayudante no pueden ser la misma persona.');
            return;
        }

        // Validar que para partes tipo 2 o 3, tanto Encargado como Ayudante sean obligatorios
        if (parteSeleccionada) {
            const selectedOption = $('#parte_id_segunda_seccion').find('option:selected');
            const tipo = selectedOption.data('tipo');


                if (!encargadoId || encargadoId === '') {
                    showAlert('modal-alert-container-segunda-seccion', 'warning', 'Para esta Asignación es obligatorio seleccionar un Encargado.');
                    $('#encargado_display_segunda_seccion').addClass('is-invalid');
                    return;
                }
            if (tipo == 2 || tipo == 3) {
                if (!ayudanteId || ayudanteId === '') {
                    showAlert('modal-alert-container-segunda-seccion', 'warning', 'Para esta Asignación es obligatorio seleccionar un Ayudante.');
                    $('#ayudante_display_segunda_seccion').addClass('is-invalid');
                    return;
                }
            }
        }

        // Validar campo tiempo
        const tiempoValue = $('#tiempo_segunda_seccion').val();
        if (!tiempoValue || tiempoValue < 1) {
            showAlert('modal-alert-container-segunda-seccion', 'warning', 'El campo Tiempo es obligatorio y debe ser mayor a 0.');
            $('#tiempo_segunda_seccion').addClass('is-invalid');
            return;
        }

        // Campo leccion ahora es opcional

        const isEdit = isEditMode;
        const url = isEdit ? `/partes-programa/${$('#parte_programa_segunda_seccion_id').val()}` : '/partes-programa';
        const method = isEdit ? 'PUT' : 'POST';

        const formData = $('#parteProgramaSegundaSeccionForm').serialize();

        const submitBtn = $('#saveParteSegundaSeccionBtn');
        const spinner = submitBtn.find('.spinner-border');

        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Limpiar alertas del modal
        $('#modal-alert-container-segunda-seccion').empty();

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#parteProgramaSegundaSeccionModal').modal('hide');
                    loadPartesSegundaSeccion();
                    showAlert('alert-container', 'success', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        let fieldName = field;
                        if (field === 'tiempo') fieldName = 'tiempo_segunda_seccion';
                        if (field === 'encargado_id') fieldName = 'encargado_id_segunda_seccion';
                        if (field === 'ayudante_id') fieldName = 'ayudante_id_segunda_seccion';
                        if (field === 'parte_id') fieldName = 'parte_id_segunda_seccion';
                        if (field === 'leccion') fieldName = 'leccion_segunda_seccion';

                        $(`#${fieldName}`).addClass('is-invalid');
                        $(`#${fieldName}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                } else {
                    const errorMessage = xhr.responseJSON?.message || `Error ${xhr.status}: ${xhr.statusText}`;
                    showAlert('alert-container', 'danger', errorMessage);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    }

    function submitParteTerceraSeccion() {
        // Validar que Encargado y Ayudante no sean la misma persona
        const encargadoId = $('#encargado_id_tercera_seccion').val();
        const ayudanteId = $('#ayudante_id_tercera_seccion').val();
        const parteSeleccionada = $('#parte_id_tercera_seccion').val();

        if (encargadoId && ayudanteId && encargadoId === ayudanteId) {
            showAlert('modal-alert-container-tercera-seccion', 'warning', 'El Encargado y el Ayudante no pueden ser la misma persona.');
            return;
        }

        // Validar que para partes tipo 2 o 3, tanto Encargado como Ayudante sean obligatorios
        if (parteSeleccionada) {
            const selectedOption = $('#parte_id_tercera_seccion').find('option:selected');
            const tipo = selectedOption.data('tipo');


                if (!encargadoId || encargadoId === '') {
                    showAlert('modal-alert-container-tercera-seccion', 'warning', 'Para esta Asignación es obligatorio seleccionar un Encargado.');
                    $('#encargado_id_tercera_seccion').addClass('is-invalid');
                    return;
                }
            if (tipo == 2 || tipo == 3) {
                if (!ayudanteId || ayudanteId === '') {
                    showAlert('modal-alert-container-tercera-seccion', 'warning', 'Para esta Asignación es obligatorio seleccionar un Ayudante.');
                    $('#ayudante_id_tercera_seccion').addClass('is-invalid');
                    return;
                }
            }
        }

        // Validar campo tiempo
        const tiempoValue = $('#tiempo_tercera_seccion').val();
        if (!tiempoValue || tiempoValue < 1) {
            showAlert('modal-alert-container-tercera-seccion', 'warning', 'El campo Tiempo es obligatorio y debe ser mayor a 0.');
            $('#tiempo_tercera_seccion').addClass('is-invalid');
            return;
        }

        // Campo leccion ahora es opcional

        const isEdit = isEditMode;
        const url = isEdit ? `/partes-programa/${$('#parte_programa_tercera_seccion_id').val()}` : '/partes-programa';
        const method = isEdit ? 'PUT' : 'POST';

        const formData = $('#parteProgramaTerceraSeccionForm').serialize();

        const submitBtn = $('#saveTerceraSeccionBtn');
        const spinner = submitBtn.find('.spinner-border');

        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Limpiar alertas del modal
        $('#modal-alert-container-tercera-seccion').empty();

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#parteProgramaTerceraSeccionModal').modal('hide');
                    loadPartesTerceraSeccion();
                    showAlert('alert-container', 'success', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        let fieldName = field;
                        if (field === 'tiempo') fieldName = 'tiempo_tercera_seccion';
                        if (field === 'encargado_id') fieldName = 'encargado_id_tercera_seccion';
                        if (field === 'ayudante_id') fieldName = 'ayudante_id_tercera_seccion';
                        if (field === 'parte_id') fieldName = 'parte_id_tercera_seccion';
                        if (field === 'leccion') fieldName = 'leccion_tercera_seccion';

                        $(`#${fieldName}`).addClass('is-invalid');
                        $(`#${fieldName}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                } else {
                    const errorMessage = xhr.responseJSON?.message || `Error ${xhr.status}: ${xhr.statusText}`;
                    showAlert('alert-container', 'danger', errorMessage);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    }

    function loadPartesSeccionesSegundaSeccion() {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes-segunda-seccion-disponibles`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#parte_id_segunda_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    // Las partes ya vienen filtradas desde el backend
                    response.data.forEach(function(parte) {
                        select.append(`<option value="${parte.id}" data-tiempo="${parte.tiempo}" data-tipo="${parte.tipo}">${parte.abreviacion} - ${parte.nombre}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error al cargar las partes disponibles:', xhr.responseText);
            }
        });
    }

    function loadPartesSeccionesForEditSegundaSeccion(parteIdSeleccionada) {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes-segunda-seccion-disponibles`,
            method: 'GET',
            data: {
                include_selected: parteIdSeleccionada  // Incluir la parte seleccionada aunque no esté activa
            },
            success: function(response) {
                if (response.success) {
                    const select = $('#parte_id_segunda_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    // Cargar todas las partes activas y la parte seleccionada
                    response.data.forEach(function(parte) {
                        const selected = parte.id == parteIdSeleccionada ? 'selected' : '';
                        select.append(`<option value="${parte.id}" data-tiempo="${parte.tiempo}" data-tipo="${parte.tipo}" ${selected}>${parte.abreviacion} - ${parte.nombre}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error al cargar las partes disponibles:', xhr.responseText);
            }
        });
    }

    function loadEncargadosByParteSegundaSeccion(parteId, encargadoSeleccionado = null) {
        // Obtener el ID de la parte programa que se está editando
        const editingId = $('#parte_programa_segunda_seccion_id').val();
        const url = `/encargados-por-parte-programa/${parteId}` + (editingId ? `?editing_id=${editingId}` : '');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#encargado_id_segunda_seccion');
                    const ayudanteSeleccionado = $('#ayudante_id_segunda_seccion').val();

                    select.empty().append('<option value="">Seleccionar...</option>');

                    // Separar usuarios por sexo
                    const mujeres = response.data.filter(usuario => usuario.sexo == 2);
                    const hombres = response.data.filter(usuario => usuario.sexo == 1);

                    // Agregar sección de Mujeres
                    if (mujeres.length > 0) {
                        select.append('<option disabled style="font-weight: bold;">--- Mujeres ---</option>');
                        mujeres.forEach(function(usuario) {
                            const selected = encargadoSeleccionado && usuario.id == encargadoSeleccionado ? 'selected' : '';
                            select.append(`<option value="${usuario.id}" ${selected}>${usuario.display_text}</option>`);
                        });
                    }

                    // Agregar sección de Hombres
                    if (hombres.length > 0) {
                        select.append('<option disabled style="font-weight: bold;">--- Hombres ---</option>');
                        hombres.forEach(function(usuario) {
                            const selected = encargadoSeleccionado && usuario.id == encargadoSeleccionado ? 'selected' : '';
                            select.append(`<option value="${usuario.id}" ${selected}>${usuario.display_text}</option>`);
                        });
                    }

                    select.trigger('change');
                }
            }
        });
    }

    function loadUsuariosDisponiblesSegundaSeccion() {
        $.ajax({
            url: '/usuarios-disponibles',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#encargado_id_segunda_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    response.data.forEach(function(usuario) {
                        select.append(`<option value="${usuario.id}">${usuario.name}</option>`);
                    });

                    select.trigger('change');
                }
            }
        });
    }

    function loadAyudantesByParteSegundaSeccion(parteId, ayudanteSeleccionado = null) {
        // Obtener el ID de la parte programa que se está editando
        const editingId = $('#parte_programa_segunda_seccion_id').val();
        const url = `/ayudantes-por-parte/${parteId}` + (editingId ? `?editing_id=${editingId}` : '');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#ayudante_id_segunda_seccion');
                    const encargadoSeleccionado = $('#encargado_id_segunda_seccion').val();

                    select.empty().append('<option value="">Seleccionar...</option>');

                    response.data.forEach(function(usuario) {
                        const selected = ayudanteSeleccionado && usuario.id == ayudanteSeleccionado ? 'selected' : '';
                        const disabled = encargadoSeleccionado && usuario.id == encargadoSeleccionado ? 'disabled' : '';
                        // Para perfil=3, usar el display_text que ya viene formateado: fecha|parte|tipo|nombre
                        select.append(`<option value="${usuario.id}" ${selected} ${disabled}>${usuario.display_text}</option>`);
                    });

                    select.trigger('change');
                }
            }
        });
    }

    function loadAyudantesDisponiblesSegundaSeccion() {
        $.ajax({
            url: '/usuarios-disponibles',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#ayudante_id_segunda_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    response.data.forEach(function(usuario) {
                        select.append(`<option value="${usuario.id}">${usuario.name}</option>`);
                    });

                    select.trigger('change');
                }
            }
        });
    }

    function loadAyudantesByParteSegundaSeccion(ayudanteSeleccionado = null) {
        const parteId = $('#parte_id_segunda_seccion').val();

        if (!parteId) {
            // Si no hay parte seleccionada, limpiar el select
            programmaticChange = true;
            $('#ayudante_id_segunda_seccion').empty().append('<option value="">Seleccionar parte primero...</option>').trigger('change');
            programmaticChange = false;
            return;
        }

        // Obtener IDs auxiliares
        const editingId = $('#parte_programa_segunda_seccion_id').val();
        const encargadoId = $('#encargado_id_segunda_seccion').val();

        // Construir URL usando el endpoint unificado con soporte de secciones por género
        const params = [];
        if (editingId) params.push(`editing_id=${editingId}`);
        if (encargadoId) params.push(`encargado_id=${encargadoId}`);
        const url = `/ayudantes-por-parte-programa/${parteId}` + (params.length ? `?${params.join('&')}` : '');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                const select = $('#ayudante_id_segunda_seccion');
                const encargadoSeleccionado = $('#encargado_id_segunda_seccion').val();

                select.empty().append('<option value="">Seleccionar...</option>');

                if (response.success && Array.isArray(response.data)) {
                    response.data.forEach(function(usuario) {
                        if (usuario.is_section) {
                            // Encabezado de sección deshabilitado (Hombres/Mujeres)
                            const label = usuario.display_text || usuario.name || '—';
                            select.append(`<option value="" disabled style="font-weight: bold; background-color: #f8f9fa;">${label}</option>`);
                        } else {
                            // Opción normal
                            const disabled = encargadoSeleccionado && usuario.id == encargadoSeleccionado ? 'disabled' : '';
                            const selected = ayudanteSeleccionado && usuario.id == ayudanteSeleccionado ? 'selected' : '';
                            const displayText = usuario.display_text || usuario.name;
                            select.append(`<option value="${usuario.id}" ${disabled} ${selected}>${displayText}</option>`);
                        }
                    });
                } else {
                    programmaticChange = true;
                    select.empty().append('<option value="">No hay ayudantes disponibles</option>').trigger('change');
                    programmaticChange = false;
                    return;
                }

                select.trigger('change');
            },
            error: function(xhr) {
                console.error('Error al cargar ayudantes por parte (segunda):', xhr.responseText);
                programmaticChange = true;
                $('#ayudante_id_segunda_seccion').empty().append('<option value="">Error al cargar ayudantes</option>').trigger('change');
                programmaticChange = false;
            }
        });
    }

    function loadAyudantesByEncargadoAndParte(encargadoId, parteId, ayudanteSeleccionado = null) {
        // Obtener el ID de la parte programa que se está editando
        const editingId = $('#parte_programa_segunda_seccion_id').val();
        const url = `/ayudantes-por-encargado/${encargadoId}/${parteId}` + (editingId ? `?editing_id=${editingId}` : '');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#ayudante_id_segunda_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(usuario) {
                            if (usuario.is_section) {
                                // Agregar encabezado de sección (deshabilitado para selección)
                                select.append(`<option value="" disabled style="font-weight: bold; background-color: #f8f9fa;">${usuario.name}</option>`);
                            } else {
                                // Agregar usuario normal
                                const selected = ayudanteSeleccionado && usuario.id == ayudanteSeleccionado ? 'selected' : '';
                                select.append(`<option value="${usuario.id}" ${selected}>${usuario.display_text}</option>`);
                            }
                        });
                    }

                    select.trigger('change');
                } else {
                    const select = $('#ayudante_id_segunda_seccion');
                    select.empty().append('<option value="">No hay ayudantes disponibles</option>').trigger('change');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar ayudantes:', xhr.responseText);
                const select = $('#ayudante_id_segunda_seccion');
                select.empty().append('<option value="">Error al cargar ayudantes</option>').trigger('change');
            }
        });
    }

    @endif

    // Funciones para limpiar campos de reemplazados
    function clearEncargadoReemplazado() {
        $('#encargado_reemplazado_segunda_seccion').val('');
        $('#encargado_reemplazado_id_segunda_seccion').val('');
    }

    function clearAyudanteReemplazado() {
        $('#ayudante_reemplazado_segunda_seccion').val('');
        $('#ayudante_reemplazado_id_segunda_seccion').val('');
    }

    function agregarEncargadoReemplazado() {
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        const encargadoNombre = $('#encargado_display_segunda_seccion').val();

        if (encargadoId && encargadoNombre) {
            // Agregar el nombre al campo visible
            $('#encargado_reemplazado_segunda_seccion').val(encargadoNombre);

            // Agregar el ID al campo oculto para ser guardado en la BD
            $('#encargado_reemplazado_id_segunda_seccion').val(encargadoId);


        } else {
            alert('Por favor seleccione un encargado primero');
        }
    }

    function agregarAyudanteReemplazado() {
        const ayudanteId = $('#ayudante_id_segunda_seccion').val();
        const ayudanteNombre = $('#ayudante_display_segunda_seccion').val();

        if (ayudanteId && ayudanteNombre) {
            // Agregar el nombre al campo visible
            $('#ayudante_reemplazado_segunda_seccion').val(ayudanteNombre);

            // Agregar el ID al campo oculto para ser guardado en la BD
            $('#ayudante_reemplazado_id_segunda_seccion').val(ayudanteId);


        } else {
            alert('Por favor seleccione un ayudante primero');
        }
    }

    // Funciones para manejar reemplazados
    function manejarEncargadoReemplazado() {
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        const encargadoNombre = $('#encargado_display_segunda_seccion').val();

        if (encargadoId && encargadoNombre) {
            // Mostrar campos de reemplazados si están ocultos
            $('#campos-reemplazados-segunda-seccion').show();

            // Copiar el encargado actual como reemplazado
            $('#encargado_reemplazado_segunda_seccion').val(encargadoNombre);
            $('#encargado_reemplazado_id_segunda_seccion').val(encargadoId);
        } else {
            alert('Por favor seleccione un encargado primero');
        }
    }

    function manejarAyudanteReemplazado() {
        const ayudanteId = $('#ayudante_id_segunda_seccion').val();
        const ayudanteNombre = $('#ayudante_display_segunda_seccion').val();

        if (ayudanteId && ayudanteNombre) {
            // Mostrar campos de reemplazados si están ocultos
            $('#campos-reemplazados-segunda-seccion').show();

            // Copiar el ayudante actual como reemplazado
            $('#ayudante_reemplazado_segunda_seccion').val(ayudanteNombre);
            $('#ayudante_reemplazado_id_segunda_seccion').val(ayudanteId);
        } else {
            alert('Por favor seleccione un ayudante primero');
        }
    }

    // Funciones para los botones de la segunda sección
    function buscarEncargadoSegundaSeccion() {
        const parteId = $('#parte_id_segunda_seccion').val();
        if (!parteId) {
            alert('Por favor seleccione una parte primero');
            return;
        }

        // Abrir modal y cargar usuarios con participaciones en la parte seleccionada
        $('#buscarEncargadoSegundaSeccionModal').modal('show');

        // Al abrir el modal, deshabilitar el botón Seleccionar
        $('#confirmarEncargadoSegundaSeccion').prop('disabled', true);

        // Cuando se seleccione un encargado, habilitar el botón Seleccionar y cargar historial
        $(document).off('change.select_encargado_segunda_seccion').on('change.select_encargado_segunda_seccion', '#select_encargado_segunda_seccion', function() {
            const val = $(this).val();
            $('#confirmarEncargadoSegundaSeccion').prop('disabled', !val);

            // Cargar historial del encargado seleccionado
            if (val) {
                loadHistorialEncargadoSegundaSeccion(val);
            } else {
                clearHistorialEncargadoSegundaSeccion();
            }
        });

        // Cargar usuarios que han participado como encargados en esta parte
        $.ajax({
            url: `/encargados-por-parte-programa/${parteId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_encargado_segunda_seccion');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar encargado...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarEncargadoSegundaSeccionModal')
                        });
                    }

                    select.append('<option value="">Seleccionar encargado...</option>');

                    // Agregar opciones con el formato: fecha|abreviacion|nombre
                    response.data.forEach(function(usuario) {
                        select.append(`<option value="${usuario.id}">${usuario.display_text}</option>`);
                    });

                    // Inicializar Select2 para el historial si no está ya inicializado
                    const selectHistorial = $('#select_historial_encargado_segunda_seccion');
                    if (!selectHistorial.hasClass('select2-hidden-accessible')) {
                        selectHistorial.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Seleccionar un encargado primero...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarEncargadoSegundaSeccionModal')
                        });
                    }

                    // Preseleccionar el encargado actual si existe
                    const encargadoActual = $('#encargado_id_segunda_seccion').val();
                    if (encargadoActual) {
                        select.val(encargadoActual).trigger('change');
                    }
                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios participantes');
                console.error(xhr);
            }
        });
    }

    function verHistorialEncargadoSegundaSeccion() {
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        if (!encargadoId) {
            alert('No hay encargado seleccionado');
            return;
        }

        // Aquí iría la lógica para mostrar el historial del encargado
        // Similar a verHistorialEncargadoParte() pero para segunda sección
        alert('Función verHistorialEncargadoSegundaSeccion() - Por implementar\nEncargado ID: ' + encargadoId);
    }

    function buscarAyudanteSegundaSeccion() {
        const parteId = $('#parte_id_segunda_seccion').val();
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        const selectedOption = $('#parte_id_segunda_seccion').find('option:selected');
        const tipo = selectedOption.data('tipo');

        if (!parteId) {
            alert('Por favor seleccione una parte primero');
            return;
        }

        // Validar que el tipo de parte no sea 1
        if (tipo == 1) {
            alert('No se puede buscar ayudante para esta Asignación');
            return;
        }

        // Abrir modal y cargar usuarios con participaciones como ayudante
        $('#buscarAyudanteSegundaSeccionModal').modal('show');

        // Al abrir el modal, deshabilitar el botón Seleccionar
        $('#confirmarAyudanteSegundaSeccion').prop('disabled', true);

        // Cuando se seleccione un ayudante, habilitar el botón Seleccionar y cargar historial
        $(document).off('change.select_ayudante_segunda_seccion').on('change.select_ayudante_segunda_seccion', '#select_ayudante_segunda_seccion', function() {
            const val = $(this).val();
            $('#confirmarAyudanteSegundaSeccion').prop('disabled', !val);

            // Cargar historial del ayudante seleccionado
            if (val) {
                loadHistorialAyudanteSegundaSeccion(val);
            } else {
                clearHistorialAyudanteSegundaSeccion();
            }
        });

        // Cargar usuarios que han participado como ayudantes (endpoint unificado) y ordenar secciones según encargado si aplica
        let url = `/ayudantes-por-parte-programa/${parteId}`;
        if (encargadoId) {
            url += `?encargado_id=${encargadoId}`;
        }

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_ayudante_segunda_seccion');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar ayudante...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarAyudanteSegundaSeccionModal')
                        });
                    }

                    select.append('<option value="">Seleccionar ayudante...</option>');

                    // Verificar si hay secciones de género
                    if (response.has_gender_sections) {
                        // Agregar opciones con secciones de género
                        response.data.forEach(function(usuario) {
                            if (usuario.is_section) {
                                // Es una sección (Hombres o Mujeres)
                                select.append(`<option disabled style="font-weight: bold;">${usuario.display_text}</option>`);
                            } else {
                                // Es un usuario normal
                                select.append(`<option value="${usuario.id}">${usuario.display_text}</option>`);
                            }
                        });
                    } else {
                        // Sin secciones de género, agregar normalmente
                        response.data.forEach(function(usuario) {
                            select.append(`<option value="${usuario.id}">${usuario.display_text}</option>`);
                        });
                    }

                    // Inicializar Select2 para el historial si no está ya inicializado
                    const selectHistorial = $('#select_historial_ayudante_segunda_seccion');
                    if (!selectHistorial.hasClass('select2-hidden-accessible')) {
                        selectHistorial.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Seleccionar un ayudante primero...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarAyudanteSegundaSeccionModal')
                        });
                    }

                    // Preseleccionar el ayudante actual si existe
                    const ayudanteActual = $('#ayudante_id_segunda_seccion').val();
                    if (ayudanteActual) {
                        select.val(ayudanteActual).trigger('change');
                    }
                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios ayudantes');
                console.error(xhr);
            }
        });
    }

    function verHistorialAyudanteSegundaSeccion() {
        const ayudanteId = $('#ayudante_id_segunda_seccion').val();
        if (!ayudanteId) {
            alert('No hay ayudante seleccionado');
            return;
        }

        // Aquí iría la lógica para mostrar el historial del ayudante
        alert('Función verHistorialAyudanteSegundaSeccion() - Por implementar\nAyudante ID: ' + ayudanteId);
    }

    // Función para actualizar el estado de los botones de la segunda sección
    function updateButtonStatesSegundaSeccion() {
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        const ayudanteId = $('#ayudante_id_segunda_seccion').val();
        const parteSeleccionada = $('#parte_id_segunda_seccion').val();
        const btnBuscarEncargado = $('#btn-buscar-encargado-segunda');
        const btnBuscarAyudante = $('#btn-buscar-ayudante-segunda');

        // Botones del encargado
        if (encargadoId) {
            $('#btn-encargado-reemplazado-segunda').prop('disabled', false);
            btnBuscarEncargado.prop('disabled', false);
            btnBuscarEncargado.attr('title', 'Buscar Encargado');
        } else {
            $('#btn-encargado-reemplazado-segunda').prop('disabled', true);
            btnBuscarEncargado.prop('disabled', true);
            btnBuscarEncargado.attr('title', 'Seleccionar encargado primero');
        }

        // Botón "Buscar Ayudante" - aplicar la nueva lógica
        if (encargadoId && encargadoId !== '' && parteSeleccionada) {
            const selectedOption = $('#parte_id_segunda_seccion').find('option:selected');
            const tipo = selectedOption.data('tipo');

            if (tipo == 2 || tipo == 3) {
                btnBuscarAyudante.prop('disabled', false);
                btnBuscarAyudante.attr('title', 'Buscar Ayudante');
            } else {
                btnBuscarAyudante.prop('disabled', true);
                btnBuscarAyudante.attr('title', 'No disponible para este tipo de parte');
            }
        } else {
            btnBuscarAyudante.prop('disabled', true);
            btnBuscarAyudante.attr('title', 'Seleccionar encargado y parte con tipo 2 o 3');
        }

        // Botones del ayudante
        if (ayudanteId) {
            $('#btn-ayudante-reemplazado-segunda').prop('disabled', false);
        } else {
            $('#btn-ayudante-reemplazado-segunda').prop('disabled', true);
        }
    }

    // Funciones para la tercera sección
    function openCreateParteTerceraSeccionModal() {
        isEditMode = false;
        $('#parteProgramaTerceraSeccionModalLabel').text('Nueva Asignación de Seamos Mejores Maestros');
        $('#parteProgramaTerceraSeccionForm')[0].reset();

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Limpiar alertas del modal
        $('#modal-alert-container-tercera-seccion').empty();

        // Cargar partes usando las mismas condiciones que la segunda sección
        loadPartesSeccionesTerceraSeccion();

        // Dejar campos Encargado y Ayudante vacíos hasta que se seleccione una parte
        $('#encargado_id_tercera_seccion').empty().append('<option value="">Seleccionar una parte primero...</option>').trigger('change');
        $('#ayudante_id_tercera_seccion').empty().append('<option value="">Seleccionar...</option>').trigger('change');

        // Limpiar campos de reemplazados en modo nuevo
        $('#campos-reemplazados-tercera-seccion').hide();
        clearEncargadoReemplazadoTercera();
        clearAyudanteReemplazadoTercera();

        // Ocultar botones de agregar reemplazado en modo nuevo
        $('#btn-agregar-encargado-reemplazado-tercera').hide();
        $('#btn-agregar-ayudante-reemplazado-tercera').hide();

        // Limpiar historial de encargado y ayudante
        clearHistorialEncargadoTercera();
        clearHistorialAyudanteTercera();
    }

    function loadPartesSeccionesTerceraSeccion(callback) {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes-segunda-seccion-disponibles`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#parte_id_tercera_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    // Usar las mismas partes que la segunda sección
                    response.data.forEach(function(parte) {
                        select.append(`<option value="${parte.id}" data-tiempo="${parte.tiempo}">${parte.abreviacion} - ${parte.nombre}</option>`);
                    });

                    // Ejecutar callback si se proporciona
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error al cargar las partes disponibles:', xhr.responseText);
            }
        });
    }

    function clearEncargadoReemplazadoTercera() {
        $('#encargado_reemplazado_tercera_seccion').val('');
        $('#encargado_reemplazado_id_tercera_seccion').val('');
    }

    function clearAyudanteReemplazadoTercera() {
        $('#ayudante_reemplazado_tercera_seccion').val('');
        $('#ayudante_reemplazado_id_tercera_seccion').val('');
    }

    function agregarEncargadoReemplazadoTercera() {
        const encargadoSelect = $('#encargado_id_tercera_seccion');
        const encargadoSeleccionado = encargadoSelect.val();

        if (encargadoSeleccionado) {
            const selectedOption = encargadoSelect.find('option:selected');
            const textoCompleto = selectedOption.text();

            // Extraer solo el nombre del usuario del formato: fecha|parte|tipo|nombre
            let nombreEncargado = textoCompleto;
            if (textoCompleto.includes('|')) {
                const partes = textoCompleto.split('|');
                if (partes.length >= 4) {
                    nombreEncargado = partes[3].trim();
                }
            }

            // Agregar el nombre al campo visible
            $('#encargado_reemplazado_tercera_seccion').val(nombreEncargado);

            // Agregar el ID al campo oculto para ser guardado en la BD
            $('#encargado_reemplazado_id_tercera_seccion').val(encargadoSeleccionado);


        } else {
            alert('Por favor seleccione un encargado primero');
        }
    }

    function agregarAyudanteReemplazadoTercera() {
        const ayudanteSelect = $('#ayudante_id_tercera_seccion');
        const ayudanteSeleccionado = ayudanteSelect.val();

        if (ayudanteSeleccionado) {
            const selectedOption = ayudanteSelect.find('option:selected');
            const textoCompleto = selectedOption.text();

            // Extraer solo el nombre del usuario del formato: fecha|parte|tipo|nombre
            let nombreAyudante = textoCompleto;
            if (textoCompleto.includes('|')) {
                const partes = textoCompleto.split('|');
                if (partes.length >= 4) {
                    nombreAyudante = partes[3].trim();
                }
            }

            // Agregar el nombre al campo visible
            $('#ayudante_reemplazado_tercera_seccion').val(nombreAyudante);

            // Agregar el ID al campo oculto para ser guardado en la BD
            $('#ayudante_reemplazado_id_tercera_seccion').val(ayudanteSeleccionado);


        } else {
            alert('Por favor seleccione un ayudante primero');
        }
    }

    function continueAyudanteChangeTercera(ayudanteSeleccionado, encargadoSelect) {
        if (ayudanteSeleccionado) {
            // Cargar historial del ayudante seleccionado

        } else {
            // Limpiar historial del ayudante
            clearHistorialAyudanteTercera();
        }

        // Actualizar Select2 para reflejar los cambios
        encargadoSelect.trigger('change.select2');
    }

    function loadEncargadosByParteTerceraSeccion(parteId, encargadoSeleccionado = null) {
        const editingId = $('#parte_programa_tercera_seccion_id').val();
        const url = `/encargados-por-parte-programa/${parteId}` + (editingId ? `?editing_id=${editingId}` : '');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#encargado_id_tercera_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    // Separar usuarios por sexo
                    const mujeres = response.data.filter(usuario => usuario.sexo == 2);
                    const hombres = response.data.filter(usuario => usuario.sexo == 1);

                    // Agregar sección de Mujeres
                    if (mujeres.length > 0) {
                        select.append('<option disabled style="font-weight: bold;">--- Mujeres ---</option>');
                        mujeres.forEach(function(usuario) {
                            const selected = encargadoSeleccionado && usuario.id == encargadoSeleccionado ? 'selected' : '';
                            select.append(`<option value="${usuario.id}" ${selected}>${usuario.display_text}</option>`);
                        });
                    }

                    // Agregar sección de Hombres
                    if (hombres.length > 0) {
                        select.append('<option disabled style="font-weight: bold;">--- Hombres ---</option>');
                        hombres.forEach(function(usuario) {
                            const selected = encargadoSeleccionado && usuario.id == encargadoSeleccionado ? 'selected' : '';
                            select.append(`<option value="${usuario.id}" ${selected}>${usuario.display_text}</option>`);
                        });
                    }

                    select.trigger('change');
                }
            }
        });
    }

    function loadUsuariosDisponiblesTerceraSeccion() {
        $.ajax({
            url: '/usuarios-disponibles',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#encargado_id_tercera_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    response.data.forEach(function(usuario) {
                        select.append(`<option value="${usuario.id}">${usuario.name}</option>`);
                    });

                    select.trigger('change');
                }
            }
        });
    }

    function loadAyudantesByParteTerceraSeccion(ayudanteSeleccionado = null) {
        const parteId = $('#parte_id_tercera_seccion').val();
        if (!parteId) {
            return;
        }

        const editingId = $('#parte_programa_tercera_seccion_id').val();
        const encargadoId = $('#encargado_id_tercera_seccion').val();

        const params = [];
        if (editingId) params.push(`editing_id=${editingId}`);
        if (encargadoId) params.push(`encargado_id=${encargadoId}`);
        const url = `/ayudantes-por-parte-programa/${parteId}` + (params.length ? `?${params.join('&')}` : '');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                const select = $('#ayudante_id_tercera_seccion');
                const encargadoSeleccionado = $('#encargado_id_tercera_seccion').val();

                select.empty().append('<option value="">Seleccionar...</option>');

                if (response.success && Array.isArray(response.data)) {
                    response.data.forEach(function(usuario) {
                        if (usuario.is_section) {
                            const label = usuario.display_text || usuario.name || '—';
                            select.append(`<option value="" disabled style="font-weight: bold; background-color: #f8f9fa;">${label}</option>`);
                        } else {
                            const selected = ayudanteSeleccionado && usuario.id == ayudanteSeleccionado ? 'selected' : '';
                            const disabled = encargadoSeleccionado && usuario.id == encargadoSeleccionado ? 'disabled' : '';
                            const displayText = usuario.display_text || usuario.name;
                            select.append(`<option value="${usuario.id}" ${selected} ${disabled}>${displayText}</option>`);
                        }
                    });
                    select.trigger('change');
                } else {
                    select.empty().append('<option value="">No hay ayudantes disponibles</option>').trigger('change');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar ayudantes por parte (tercera):', xhr.responseText);
                const select = $('#ayudante_id_tercera_seccion');
                select.empty().append('<option value="">Error al cargar ayudantes</option>').trigger('change');
            }
        });
    }

    function loadAyudantesByEncargadoAndParteTercera(encargadoId, parteId, ayudanteIdToSelect = null, callback = null) {
        const editingId = $('#parte_programa_tercera_seccion_id').val();

        $.ajax({
            url: `/ayudantes-por-encargado/${encargadoId}/${parteId}` + (editingId ? `?editing_id=${editingId}` : ''),
            method: 'GET',
            success: function(response) {
                if (response.success && Array.isArray(response.data)) {
                    const select = $('#ayudante_id_tercera_seccion');

                    select.empty().append('<option value="">Seleccionar...</option>');

                    response.data.forEach(function(usuario) {
                        if (usuario.is_section) {
                            const label = usuario.display_text || usuario.name || '—';
                            select.append(`<option value="" disabled style="font-weight: bold; background-color: #f8f9fa;">${label}</option>`);
                        } else {
                            const displayText = usuario.display_text || usuario.name;
                            select.append(`<option value="${usuario.id}">${displayText}</option>`);
                        }
                    });

                    // Preseleccionar ayudante si se especifica
                    if (ayudanteIdToSelect) {
                        select.val(ayudanteIdToSelect).trigger('change');
                    } else {
                        select.trigger('change');
                    }

                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error al cargar ayudantes por encargado/parte (tercera):', xhr.responseText);
            }
        });
    }

    function editParteTerceraSeccion(id) {
        isEditMode = true;
        $('#parteProgramaTerceraSeccionModalLabel').text('Editar Asignación de Seamos Mejores Maestros');
        $('#parte_programa_tercera_seccion_id').val(id);

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Limpiar alertas del modal
        $('#modal-alert-container-tercera-seccion').empty();

        // Limpiar completamente el formulario antes de cargar datos
        $('#parteProgramaTerceraSeccionForm')[0].reset();

        // Limpiar todos los selects
        $('#parte_id_tercera_seccion').empty().append('<option value="">Seleccionar...</option>');
        $('#encargado_id_tercera_seccion').empty().append('<option value="">Seleccionar...</option>');
        $('#ayudante_id_tercera_seccion').empty().append('<option value="">Seleccionar...</option>');

        // Limpiar campos de reemplazados
        clearEncargadoReemplazadoTercera();
        clearAyudanteReemplazadoTercera();

        // Limpiar historiales
        clearHistorialEncargadoTercera();
        clearHistorialAyudanteTercera();

        // Mostrar campos de reemplazados en modo edición
        $('#campos-reemplazados-tercera-seccion').show();

        // Mostrar botones de agregar reemplazado en modo edición
        $('#btn-agregar-encargado-reemplazado-tercera').show();
        $('#btn-agregar-ayudante-reemplazado-tercera').show();

        // Variable para controlar si estamos en modo edición para evitar eventos conflictivos
        window.editingParteTerceraData = true;

        $.ajax({
            url: `/partes-programa/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const parte = response.data;
                    $('#parte_id_tercera_seccion').val(parte.parte_id);
                    $('#tiempo_tercera_seccion').val(parte.tiempo);
                    $('#leccion_tercera_seccion').val(parte.leccion);

                    // Cargar nombres de usuarios reemplazados si existen
                    if (parte.encargado_reemplazado) {
                        $('#encargado_reemplazado_tercera_seccion').val(parte.encargado_reemplazado.name);
                        $('#encargado_reemplazado_id_tercera_seccion').val(parte.encargado_reemplazado.id);
                    } else {
                        $('#encargado_reemplazado_tercera_seccion').val('');
                        $('#encargado_reemplazado_id_tercera_seccion').val('');
                    }

                    if (parte.ayudante_reemplazado) {
                        $('#ayudante_reemplazado_tercera_seccion').val(parte.ayudante_reemplazado.name);
                        $('#ayudante_reemplazado_id_tercera_seccion').val(parte.ayudante_reemplazado.id);
                    } else {
                        $('#ayudante_reemplazado_tercera_seccion').val('');
                        $('#ayudante_reemplazado_id_tercera_seccion').val('');
                    }

                    // Cargar datos necesarios en secuencia
                    loadPartesSeccionesTerceraSeccion(function() {
                        // Preseleccionar la parte después de cargar las opciones
                        $('#parte_id_tercera_seccion').val(parte.parte_id).trigger('change');

                        setTimeout(function() {
                            loadEncargadosByParteTerceraSeccion(parte.parte_id, parte.encargado_id);

                            setTimeout(function() {
                                if (parte.encargado_id) {
                                    $('#encargado_id_tercera_seccion').val(parte.encargado_id).trigger('change');
                                }
                            }, 100);

                            setTimeout(function() {
                                if (parte.encargado_id && parte.parte_id) {
                                    loadAyudantesByEncargadoAndParteTercera(parte.encargado_id, parte.parte_id, parte.ayudante_id, function() {
                                        // Callback después de cargar ayudantes




                                        // Liberar el flag después de todo el proceso
                                        setTimeout(function() {
                                            window.editingParteTerceraData = false;
                                        }, 200);
                                    });
                                } else {
                                    loadAyudantesByParteTerceraSeccion(parte.ayudante_id);

                                    setTimeout(function() {
                                        if (parte.ayudante_id) {
                                            $('#ayudante_id_tercera_seccion').val(parte.ayudante_id).trigger('change');
                                        }
                                    }, 200);





                                    // Liberar el flag después de todo el proceso
                                    setTimeout(function() {
                                        window.editingParteTerceraData = false;
                                    }, 400);
                                }
                            }, 300);
                        }, 200);
                    });

                    $('#parteProgramaTerceraSeccionModal').modal('show');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar la parte:', xhr.responseText);
                window.editingParteTerceraData = false;
            }
        });
    }

    function deleteParteTerceraSeccion(id) {
        $('#confirmDeleteBtn').off('click').on('click', function() {
            $.ajax({
                url: `/partes-programa/${id}`,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        loadPartesTerceraSeccion();
                        $('#confirmDeleteModal').modal('hide');
                        showAlert('alert-container', 'success', response.message);
                    }
                },
                error: function(xhr) {
                    $('#confirmDeleteModal').modal('hide');
                    showAlert('alert-container', 'danger', xhr.responseJSON?.message || 'Error al eliminar la parte');
                }
            });
        });

        $('#confirmDeleteModal').modal('show');
    }

    function moveParteTerceraSeccionUp(id) {
        const button = event.target.closest('button');
        if (button && button.disabled) {
            return;
        }
        moveParteTerceraSeccion(id, 'up');
    }

    function moveParteTerceraSeccionDown(id) {
        const button = event.target.closest('button');
        if (button && button.disabled) {
            return;
        }
        moveParteTerceraSeccion(id, 'down');
    }

    function moveParteTerceraSeccion(id, direction) {
        const url = `/partes-programa/${id}/move-${direction}`;

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    loadPartesTerceraSeccion();
                    showAlert('alert-container', 'success', response.message);
                } else {
                    showAlert('alert-container', 'warning', response.message);
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || 'Error al mover la parte';
                showAlert('alert-container', 'danger', errorMessage);
            }
        });
    }

    // Funciones globales para uso en onclick
    window.openCreateParteModal = openCreateParteModal;
    window.editParte = editParte;
    window.deleteParte = deleteParte;
    window.loadPartesSecciones = loadPartesSecciones;
    window.loadPartesSeccionesForEdit = loadPartesSeccionesForEdit;
    window.loadUsuariosDisponibles = loadUsuariosDisponibles;

    @if(Auth::user()->perfil == 3)
    window.openCreateParteSegundaSeccionModal = openCreateParteSegundaSeccionModal;
    window.editParteSegundaSeccion = editParteSegundaSeccion;
    window.deleteParteSegundaSeccion = deleteParteSegundaSeccion;
    window.moveParteUp = moveParteUp;
    window.moveParteDown = moveParteDown;
    window.moveParteSegundaSeccionUp = moveParteSegundaSeccionUp;
    window.moveParteSegundaSeccionDown = moveParteSegundaSeccionDown;
    window.clearEncargadoReemplazado = clearEncargadoReemplazado;
    window.clearAyudanteReemplazado = clearAyudanteReemplazado;
    window.agregarEncargadoReemplazado = agregarEncargadoReemplazado;
    window.agregarAyudanteReemplazado = agregarAyudanteReemplazado;
    window.manejarEncargadoReemplazado = manejarEncargadoReemplazado;
    window.manejarAyudanteReemplazado = manejarAyudanteReemplazado;
    // Funciones para tercera sección
    window.openCreateParteTerceraSeccionModal = openCreateParteTerceraSeccionModal;
    window.editParteTerceraSeccion = editParteTerceraSeccion;
    window.deleteParteTerceraSeccion = deleteParteTerceraSeccion;
    window.moveParteTerceraSeccionUp = moveParteTerceraSeccionUp;
    window.moveParteTerceraSeccionDown = moveParteTerceraSeccionDown;
    window.clearEncargadoReemplazadoTercera = clearEncargadoReemplazadoTercera;
    window.clearAyudanteReemplazadoTercera = clearAyudanteReemplazadoTercera;
    window.agregarEncargadoReemplazadoTercera = agregarEncargadoReemplazadoTercera;
    window.agregarAyudanteReemplazadoTercera = agregarAyudanteReemplazadoTercera;
    @endif
    // Funciones para mover partes arriba y abajo
    function moveParteUp(id) {
        // Verificar si el botón está deshabilitado
        const button = event.target.closest('button');
        if (button && button.disabled) {
            return;
        }

        moveParte(id, 'up');
    }

    function moveParteDown(id) {
        // Verificar si el botón está deshabilitado
        const button = event.target.closest('button');
        if (button && button.disabled) {
            return;
        }

        moveParte(id, 'down');
    }

    function moveParteSegundaSeccionUp(id) {
        // Verificar si el botón está deshabilitado
        const button = event.target.closest('button');
        if (button && button.disabled) {
            return;
        }

        moveParte(id, 'up');
    }

    function moveParteSegundaSeccionDown(id) {
        // Verificar si el botón está deshabilitado
        const button = event.target.closest('button');
        if (button && button.disabled) {
            return;
        }

        moveParte(id, 'down');
    }

    function moveParte(id, direction) {
        const url = `/partes-programa/${id}/move-${direction}`;

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Recargar ambas tablas para reflejar los cambios
                    loadPartesPrograma();
                    @if(Auth::user()->perfil == 3)
                    loadPartesSegundaSeccion();
                    @endif
                    showAlert('alert-container', 'success', response.message);
                } else {
                    showAlert('alert-container', 'warning', response.message);
                }
            },
            error: function(xhr) {
                console.error('AJAX error:', xhr);
                const errorMessage = xhr.responseJSON?.message || 'Error al mover la parte';
                showAlert('alert-container', 'danger', errorMessage);
            }
        });
    }

    // Funciones para los botones del campo Orador Inicial (solo para coordinadores)
    function buscarOradorInicial() {
        // Abrir modal y cargar usuarios con asignación de oración
        $('#buscarOradorInicialModal').modal('show');

        // Cargar usuarios con asignación_id=23 y su historial
        $.ajax({
            url: '/usuarios-orador-inicial',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_orador_inicial');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar orador...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarOradorInicialModal')
                        });
                    }

                    select.append('<option value="">Seleccionar orador inicial...</option>');

                    // Agregar opciones con el formato: fecha - nombre
                    response.data.forEach(function(usuario) {
                        let fechaTexto = 'Primera vez';
                        if (usuario.ultima_fecha) {
                            const fecha = new Date(usuario.ultima_fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            fechaTexto = `${dia}/${mes}/${año}`;
                        }
                        const textoOpcion = `${fechaTexto} - ${usuario.name}`;
                        select.append(`<option value="${usuario.id}">${textoOpcion}</option>`);
                    });

                    // Preseleccionar el orador actual si existe
                    const oradorActual = $('#orador_inicial').val();
                    if (oradorActual) {
                        select.val(oradorActual).trigger('change');
                    }
                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios para orador inicial');
                console.error(xhr);
            }
        });
    }

    function verHistorialOradorInicial() {
        const oradorId = $('#orador_inicial').val();
        if (!oradorId) {
            alert('No hay orador inicial seleccionado para mostrar historial');
            return;
        }

        // Abrir modal y cargar historial del orador
        $('#historialOradorInicialModal').modal('show');

        // Cargar historial de participaciones del usuario
        $.ajax({
            url: `/usuarios/${oradorId}/historial-orador`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_historial_orador');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Historial de participaciones...",
                            width: '100%',
                            dropdownParent: $('#historialOradorInicialModal')
                        });
                    }

                    if (response.data.length > 0) {
                        select.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato: fecha - nombre - tipo
                        response.data.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            const tipoOracion = participacion.tipo === 'inicial' ? 'Orador Inicial' : 'Orador Final';
                            const textoOpcion = `${fechaTexto} - ${participacion.nombre_usuario} - ${tipoOracion}`;

                            select.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        // Habilitar el select
                        select.prop('disabled', false);

                        // Seleccionar automáticamente el primer elemento (índice 1, ya que 0 es "Seleccionar participación...")
                        select.val(response.data[0].programa_id).trigger('change');

                        // Actualizar el título del modal con el nombre del usuario
                        $('#historialOradorInicialModalLabel').html(`<i class="fas fa-history me-2"></i>Historial de ${response.data[0].nombre_usuario}`);
                    } else {
                        select.append('<option value="">No hay participaciones registradas</option>');
                        select.prop('disabled', true);
                    }
                } else {
                    alert('Error al cargar el historial: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar el historial del orador inicial');
                console.error(xhr);
            }
        });
    }

    // Evento para confirmar la selección del orador inicial
    $('#confirmarOradorInicial').on('click', function() {
        const oradorSeleccionado = $('#select_orador_inicial').val();
        const textoSeleccionado = $('#select_orador_inicial option:selected').text();

        if (!oradorSeleccionado) {
            alert('Por favor seleccione un orador inicial');
            return;
        }

        // Extraer solo el nombre del formato "fecha - nombre"
        let nombreOrador = textoSeleccionado;
        if (textoSeleccionado.includes(' - ')) {
            nombreOrador = textoSeleccionado.split(' - ')[1];
        }

        // Actualizar los campos
        $('#orador_inicial').val(oradorSeleccionado);
        $('#orador_inicial_display').val(nombreOrador);

        // Habilitar el botón de historial ahora que hay un orador seleccionado
        $('#btn-historial-orador-inicial').prop('disabled', false);

        // Cerrar modal
        $('#buscarOradorInicialModal').modal('hide');


    });

    // Limpiar Select2 cuando se cierre el modal
    $('#buscarOradorInicialModal').on('hidden.bs.modal', function() {
        const select = $('#select_orador_inicial');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');
    });

    // Limpiar Select2 cuando se cierre el modal del historial
    $('#historialOradorInicialModal').on('hidden.bs.modal', function() {
        const select = $('#select_historial_orador');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando historial...</option>');
        select.prop('disabled', true);

        // Restaurar el título original del modal
        $('#historialOradorInicialModalLabel').html('<i class="fas fa-history me-2"></i>Historial de Participaciones');
    });

    // Evento para confirmar la selección del orador final
    $('#confirmarOradorFinal').on('click', function() {
        const oradorSeleccionado = $('#select_orador_final').val();
        const textoSeleccionado = $('#select_orador_final option:selected').text();

        if (!oradorSeleccionado) {
            alert('Por favor seleccione un orador final');
            return;
        }

        // Extraer solo el nombre del formato "fecha - nombre"
        let nombreOrador = textoSeleccionado;
        if (textoSeleccionado.includes(' - ')) {
            nombreOrador = textoSeleccionado.split(' - ')[1];
        }

        // Actualizar los campos
        $('#orador_final').val(oradorSeleccionado);
        $('#orador_final_display').val(nombreOrador);

        // Habilitar el botón de historial ahora que hay un orador seleccionado
        $('#btn-historial-orador-final').prop('disabled', false);

        // Cerrar modal
        $('#buscarOradorFinalModal').modal('hide');


    });

    // Limpiar Select2 cuando se cierre el modal de buscar orador final
    $('#buscarOradorFinalModal').on('hidden.bs.modal', function() {
        const select = $('#select_orador_final');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');
    });

    // Limpiar Select2 cuando se cierre el modal del historial orador final
    $('#historialOradorFinalModal').on('hidden.bs.modal', function() {
        const select = $('#select_historial_orador_final');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando historial...</option>');
        select.prop('disabled', true);

        // Restaurar el título original del modal
        $('#historialOradorFinalModalLabel').html('<i class="fas fa-history me-2"></i>Historial de Participaciones');
    });

    // Evento para confirmar la selección de presidencia
    $('#confirmarPresidencia').on('click', function() {
        const presidenteSeleccionado = $('#select_presidencia').val();
        const textoSeleccionado = $('#select_presidencia option:selected').text();

        if (!presidenteSeleccionado) {
            alert('Por favor seleccione un presidente');
            return;
        }

        // Extraer solo el nombre del formato "fecha - nombre"
        let nombrePresidente = textoSeleccionado;
        if (textoSeleccionado.includes(' - ')) {
            nombrePresidente = textoSeleccionado.split(' - ')[1];
        }

        // Actualizar los campos
        $('#presidencia').val(presidenteSeleccionado);
        $('#presidencia_display').val(nombrePresidente);

        // Habilitar el botón de historial ahora que hay un presidente seleccionado
        $('#btn-historial-presidencia').prop('disabled', false);

        // Cerrar modal
        $('#buscarPresidenciaModal').modal('hide');


    });

    // Limpiar Select2 cuando se cierre el modal de buscar presidencia
    $('#buscarPresidenciaModal').on('hidden.bs.modal', function() {
        const select = $('#select_presidencia');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');
    });

    // Limpiar Select2 cuando se cierre el modal del historial presidencia
    $('#historialPresidenciaModal').on('hidden.bs.modal', function() {
        const select = $('#select_historial_presidencia');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando historial...</option>');
        select.prop('disabled', true);

        // Restaurar el título original del modal
        $('#historialPresidenciaModalLabel').html('<i class="fas fa-history me-2"></i>Historial de Participaciones');
    });

    // Eventos para confirmar selección de canciones
    $('#confirmarCancionInicial').on('click', function() {
        confirmarSeleccionCancion('select_cancion_inicial', 'cancion_pre', 'cancion_pre_display', '#buscarCancionInicialModal');
    });

    $('#confirmarCancionIntermedia').on('click', function() {
        confirmarSeleccionCancion('select_cancion_intermedia', 'cancion_en', 'cancion_en_display', '#buscarCancionIntermediaModal');
    });

    $('#confirmarCancionFinal').on('click', function() {
        confirmarSeleccionCancion('select_cancion_final', 'cancion_post', 'cancion_post_display', '#buscarCancionFinalModal');
    });

    function confirmarSeleccionCancion(selectId, campoHiddenId, campoDisplayId, modalId) {
        const cancionSeleccionada = $('#' + selectId).val();
        const textoSeleccionado = $('#' + selectId + ' option:selected').text();

        if (!cancionSeleccionada) {
            alert('Por favor seleccione una canción');
            return;
        }

        // Actualizar los campos
        $('#' + campoHiddenId).val(cancionSeleccionada);
        $('#' + campoDisplayId).val(textoSeleccionado);

        // Cerrar modal
        $(modalId).modal('hide');


    }

    // Limpiar Select2 cuando se cierren los modales de canciones
    $('#buscarCancionInicialModal').on('hidden.bs.modal', function() {
        const select = $('#select_cancion_inicial');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando canciones...</option>');
    });

    $('#buscarCancionIntermediaModal').on('hidden.bs.modal', function() {
        const select = $('#select_cancion_intermedia');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando canciones...</option>');
    });

    $('#buscarCancionFinalModal').on('hidden.bs.modal', function() {
        const select = $('#select_cancion_final');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando canciones...</option>');
    });

    // Funciones para los botones del campo Orador Final (solo para coordinadores)
    function buscarOradorFinal() {
        // Abrir modal y cargar usuarios con asignación de oración
        $('#buscarOradorFinalModal').modal('show');

        // Cargar usuarios con asignación_id=23 y su historial (misma función que orador inicial)
        $.ajax({
            url: '/usuarios-orador-inicial',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_orador_final');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar orador...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarOradorFinalModal')
                        });
                    }

                    select.append('<option value="">Seleccionar orador final...</option>');

                    // Agregar opciones con el formato: fecha - nombre
                    response.data.forEach(function(usuario) {
                        let fechaTexto = 'Primera vez';
                        if (usuario.ultima_fecha) {
                            const fecha = new Date(usuario.ultima_fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            fechaTexto = `${dia}/${mes}/${año}`;
                        }
                        const textoOpcion = `${fechaTexto} - ${usuario.name}`;
                        select.append(`<option value="${usuario.id}">${textoOpcion}</option>`);
                    });

                    // Preseleccionar el orador actual si existe
                    const oradorActual = $('#orador_final').val();
                    if (oradorActual) {
                        select.val(oradorActual).trigger('change');
                    }
                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios para orador final');
                console.error(xhr);
            }
        });
    }

    function verHistorialOradorFinal() {
        const oradorId = $('#orador_final').val();
        if (!oradorId) {
            alert('No hay orador final seleccionado para mostrar historial');
            return;
        }

        // Abrir modal y cargar historial del orador
        $('#historialOradorFinalModal').modal('show');

        // Cargar historial de participaciones del usuario (misma función que orador inicial)
        $.ajax({
            url: `/usuarios/${oradorId}/historial-orador`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_historial_orador_final');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Historial de participaciones...",
                            width: '100%',
                            dropdownParent: $('#historialOradorFinalModal')
                        });
                    }

                    if (response.data.length > 0) {
                        select.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato: fecha - nombre - tipo
                        response.data.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            const tipoOracion = participacion.tipo === 'inicial' ? 'Orador Inicial' : 'Orador Final';
                            const textoOpcion = `${fechaTexto} - ${participacion.nombre_usuario} - ${tipoOracion}`;

                            select.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        // Habilitar el select
                        select.prop('disabled', false);

                        // Seleccionar automáticamente el primer elemento (índice 1, ya que 0 es "Seleccionar participación...")
                        select.val(response.data[0].programa_id).trigger('change');

                        // Actualizar el título del modal con el nombre del usuario
                        $('#historialOradorFinalModalLabel').html(`<i class="fas fa-history me-2"></i>Historial de ${response.data[0].nombre_usuario}`);
                    } else {
                        select.append('<option value="">No hay participaciones registradas</option>');
                        select.prop('disabled', true);
                    }
                } else {
                    alert('Error al cargar el historial: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar el historial del orador final');
                console.error(xhr);
            }
        });
    }

    // Funciones para los botones del campo Presidencia (solo para coordinadores)
    function buscarPresidencia() {
        // Abrir modal y cargar usuarios con asignación de presidencia
        $('#buscarPresidenciaModal').modal('show');

        // Cargar usuarios con asignación_id=1 y su historial
        $.ajax({
            url: '/usuarios-presidencia',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_presidencia');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar presidente...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarPresidenciaModal')
                        });
                    }

                    select.append('<option value="">Seleccionar presidente...</option>');

                    // Agregar opciones con el formato: fecha - nombre
                    response.data.forEach(function(usuario) {
                        let fechaTexto = 'Primera vez';
                        if (usuario.ultima_fecha) {
                            const fecha = new Date(usuario.ultima_fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            fechaTexto = `${dia}/${mes}/${año}`;
                        }
                        const textoOpcion = `${fechaTexto} - ${usuario.name}`;
                        select.append(`<option value="${usuario.id}">${textoOpcion}</option>`);
                    });

                    // Preseleccionar el presidente actual si existe
                    const presidenteActual = $('#presidencia').val();
                    if (presidenteActual) {
                        select.val(presidenteActual).trigger('change');
                    }
                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios para presidencia');
                console.error(xhr);
            }
        });
    }

    function verHistorialPresidencia() {
        const presidenteId = $('#presidencia').val();
        if (!presidenteId) {
            alert('No hay presidente seleccionado para mostrar historial');
            return;
        }

        // Abrir modal y cargar historial del presidente
        $('#historialPresidenciaModal').modal('show');

        // Cargar historial de participaciones del usuario
        $.ajax({
            url: `/usuarios/${presidenteId}/historial-presidencia`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_historial_presidencia');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Historial de participaciones...",
                            width: '100%',
                            dropdownParent: $('#historialPresidenciaModal')
                        });
                    }

                    if (response.data.length > 0) {
                        select.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato: fecha - nombre
                        response.data.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            const textoOpcion = `${fechaTexto} - ${participacion.nombre_usuario}`;

                            select.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        // Habilitar el select
                        select.prop('disabled', false);

                        // Seleccionar automáticamente el primer elemento (índice 1, ya que 0 es "Seleccionar participación...")
                        select.val(response.data[0].programa_id).trigger('change');

                        // Actualizar el título del modal con el nombre del usuario
                        $('#historialPresidenciaModalLabel').html(`<i class="fas fa-history me-2"></i>Historial de ${response.data[0].nombre_usuario}`);
                    } else {
                        select.append('<option value="">No hay participaciones registradas</option>');
                        select.prop('disabled', true);
                    }
                } else {
                    alert('Error al cargar el historial: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar el historial de presidencia');
                console.error(xhr);
            }
        });
    }

    // Funciones para los botones de canciones (solo para coordinadores)
    function buscarCancionInicial() {
        $('#buscarCancionInicialModal').modal('show');
        cargarCanciones('select_cancion_inicial', '#cancion_pre');
    }

    function buscarCancionIntermedia() {
        $('#buscarCancionIntermediaModal').modal('show');
        cargarCanciones('select_cancion_intermedia', '#cancion_en');
    }

    function buscarCancionFinal() {
        $('#buscarCancionFinalModal').modal('show');
        cargarCanciones('select_cancion_final', '#cancion_post');
    }

    function cargarCanciones(selectId, campoActualId) {
        $.ajax({
            url: '/canciones-disponibles',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#' + selectId);
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        const modalId = selectId.includes('inicial') ? '#buscarCancionInicialModal' :
                                       selectId.includes('intermedia') ? '#buscarCancionIntermediaModal' :
                                       '#buscarCancionFinalModal';

                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar canción...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $(modalId)
                        });
                    }

                    select.append('<option value="">Seleccionar canción...</option>');

                    // Agregar opciones con el formato: número - nombre
                    response.data.forEach(function(cancion) {
                        const textoOpcion = cancion.numero ? `${cancion.numero} - ${cancion.nombre}` : cancion.nombre;
                        select.append(`<option value="${cancion.id}">${textoOpcion}</option>`);
                    });

                    // Preseleccionar la canción actual si existe
                    const cancionActual = $(campoActualId).val();
                    if (cancionActual) {
                        select.val(cancionActual).trigger('change');
                    }
                } else {
                    alert('Error al cargar las canciones: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar las canciones');
                console.error(xhr);
            }
        });
    }
// Funciones para los botones del campo Encargado del datatable 1 (solo para coordinadores)
    function buscarEncargadoParte() {
        const parteId = $('#parte_id').val();
        if (!parteId) {
            alert('Por favor seleccione una parte primero');
            return;
        }

        // Abrir modal y cargar usuarios con participaciones en la parte seleccionada
        $('#buscarEncargadoParteModal').modal('show');

        // Cargar usuarios que han participado como encargados en esta parte
        $.ajax({
            url: `/encargados-por-parte-programa/${parteId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_encargado_parte');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar encargado...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarEncargadoParteModal')
                        });
                    }

                    select.append('<option value="">Seleccionar encargado...</option>');

                    // Agregar opciones con el formato: fecha (dd/mm/AAAA) - Nombre del usuario
                    response.data.forEach(function(usuario) {
                        let fechaTexto = 'Primera vez';
                        if (usuario.ultima_fecha) {
                            const fecha = new Date(usuario.ultima_fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            fechaTexto = `${dia}/${mes}/${año}`;
                        }
                        const textoOpcion = `${fechaTexto} - ${usuario.name}`;
                        select.append(`<option value="${usuario.id}">${textoOpcion}</option>`);
                    });

                    // Preseleccionar el encargado actual si existe, sino seleccionar el primero
                    const encargadoActual = $('#encargado_id').val();
                    if (encargadoActual) {
                        select.val(encargadoActual).trigger('change');
                    } else if (response.data.length > 0) {
                        // Seleccionar automáticamente el primer elemento de la lista
                        select.val(response.data[0].id).trigger('change');
                    }
                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios para encargado');
                console.error(xhr);
            }
        });
    }

    function verHistorialEncargadoParte() {
        const encargadoId = $('#encargado_id').val();
        const parteId = $('#parte_id').val();

        if (!encargadoId) {
            alert('No hay encargado seleccionado para mostrar historial');
            return;
        }

        if (!parteId) {
            alert('No hay parte seleccionada para mostrar historial');
            return;
        }

        // Abrir modal y cargar historial del encargado en esta parte específica
        $('#historialEncargadoParteModal').modal('show');

        // Cargar historial de participaciones del usuario como encargado en esta parte
        $.ajax({
            url: `/usuarios/${encargadoId}/historial-participaciones`,
            method: 'GET',
            data: {
                parte_id: parteId,
                tipo: 'encargado'
            },
            success: function(response) {
                if (response.success) {
                    const select = $('#select_historial_encargado_parte');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Historial de participaciones...",
                            width: '100%',
                            dropdownParent: $('#historialEncargadoParteModal')
                        });
                    }

                    if (response.data.length > 0) {
                        select.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato: fecha - parte - tipo
                        response.data.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            const textoOpcion = `${fechaTexto}|${participacion.parte_abreviacion}|${participacion.nombre_usuario || 'Usuario'}`;
                            select.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        // Habilitar el select
                        select.prop('disabled', false);

                        // Seleccionar automáticamente el primer elemento
                        select.val(response.data[0].programa_id).trigger('change');

                        // Actualizar el título del modal con el nombre del usuario
                        $('#historialEncargadoParteModalLabel').html(`<i class="fas fa-history me-2"></i>Historial de ${response.data[0].nombre_usuario || 'Usuario'}`);
                    } else {
                        select.append('<option value="">No hay participaciones registradas como encargado</option>');
                        select.prop('disabled', true);
                    }
                } else {
                    alert('Error al cargar el historial: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar el historial del encargado');
                console.error(xhr);
            }
        });
    }

    // Evento para confirmar la selección del encargado
    $('#confirmarEncargadoParte').on('click', function() {
        const encargadoSeleccionado = $('#select_encargado_parte').val();
        const textoSeleccionado = $('#select_encargado_parte option:selected').text();

        if (!encargadoSeleccionado) {
            alert('Por favor seleccione un encargado');
            return;
        }

        // Extraer solo el nombre del formato "fecha - nombre"
        let nombreEncargado = textoSeleccionado;
        if (textoSeleccionado.includes(' - ')) {
            nombreEncargado = textoSeleccionado.split(' - ')[1];
        }

        // Actualizar los campos
        $('#encargado_id').val(encargadoSeleccionado);
        $('#encargado_display').val(nombreEncargado);

        // Habilitar los botones ahora que hay un encargado seleccionado
        $('#btn-historial-encargado').prop('disabled', false);
        $('#btn-agregar-reemplazado').prop('disabled', false);

        // Cerrar modal
        $('#buscarEncargadoParteModal').modal('hide');


    });

    // Limpiar Select2 cuando se cierre el modal de buscar encargado
    $('#buscarEncargadoParteModal').on('hidden.bs.modal', function() {
        const select = $('#select_encargado_parte');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');
    });

    // Limpiar Select2 cuando se cierre el modal del historial de encargado
    $('#historialEncargadoParteModal').on('hidden.bs.modal', function() {
        const select = $('#select_historial_encargado_parte');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando historial...</option>');
        select.prop('disabled', true);

        // Restaurar el título original del modal
        $('#historialEncargadoParteModalLabel').html('<i class="fas fa-history me-2"></i>Historial de Participaciones como Encargado');
    });

    // Hacer las funciones globales para uso en onclick
    window.buscarOradorInicial = buscarOradorInicial;
    window.verHistorialOradorInicial = verHistorialOradorInicial;
    window.buscarOradorFinal = buscarOradorFinal;
    window.verHistorialOradorFinal = verHistorialOradorFinal;
    window.buscarPresidencia = buscarPresidencia;
    window.verHistorialPresidencia = verHistorialPresidencia;
    window.buscarCancionInicial = buscarCancionInicial;
    window.buscarCancionIntermedia = buscarCancionIntermedia;
    // Función para agregar encargado como reemplazado
    function agregarEncargadoReemplazado() {
        const encargadoId = $('#encargado_id').val();
        const encargadoNombre = $('#encargado_display').val();

        if (!encargadoId || !encargadoNombre) {
            alert('No hay encargado seleccionado para agregar como reemplazado');
            return;
        }

        // Agregar directamente sin confirmación
        $('#encargado_reemplazado_id').val(encargadoId);
        $('#encargado_reemplazado_display').val(encargadoNombre);

        // Habilitar el botón de eliminar
        $('#btn-eliminar-reemplazado').prop('disabled', false);
    }

    // Función para eliminar encargado reemplazado
    function eliminarEncargadoReemplazado() {
        const encargadoReemplazadoNombre = $('#encargado_reemplazado_display').val();

        if (!encargadoReemplazadoNombre) {
            alert('No hay encargado reemplazado para eliminar');
            return;
        }

        // Eliminar directamente sin confirmación
        $('#encargado_reemplazado_id').val('');
        $('#encargado_reemplazado_display').val('');

        // Deshabilitar el botón de eliminar
        $('#btn-eliminar-reemplazado').prop('disabled', true);
    }

    // Event listeners para los modales de confirmación
    $('#confirmarAgregarReemplazado').on('click', function() {
        const encargadoId = $('#encargado_id').val();
        const encargadoNombre = $('#encargado_display').val();

        // Establecer los valores en los campos de reemplazado
        $('#encargado_reemplazado_id').val(encargadoId);
        $('#encargado_reemplazado_display').val(encargadoNombre);

        // Habilitar el botón de eliminar
        $('#btn-eliminar-reemplazado').prop('disabled', false);

        // Cerrar el modal
        $('#confirmarAgregarReemplazadoModal').modal('hide');


    });

    $('#confirmarEliminarReemplazado').on('click', function() {
        // Limpiar los campos
        $('#encargado_reemplazado_id').val('');
        $('#encargado_reemplazado_display').val('');

        // Deshabilitar el botón de eliminar
        $('#btn-eliminar-reemplazado').prop('disabled', true);

        // Cerrar el modal
        $('#confirmarEliminarReemplazadoModal').modal('hide');


    });

    window.buscarCancionFinal = buscarCancionFinal;
    window.buscarEncargadoParte = buscarEncargadoParte;
    window.verHistorialEncargadoParte = verHistorialEncargadoParte;
    window.agregarEncargadoReemplazado = agregarEncargadoReemplazado;
    window.eliminarEncargadoReemplazado = eliminarEncargadoReemplazado;
    window.buscarEncargadoSegundaSeccion = buscarEncargadoSegundaSeccion;
    window.buscarAyudanteSegundaSeccion = buscarAyudanteSegundaSeccion;
    window.verHistorialAyudanteSegundaSeccion = verHistorialAyudanteSegundaSeccion;

    // Funciones para limpiar historiales (segunda sección)
    function clearHistorialEncargado() {
        // Esta función se llama para mantener consistencia en la segunda sección
        // No hay campos específicos de historial que limpiar en esta sección
    }

    function clearHistorialAyudante() {
        // Esta función se llama para mantener consistencia en la segunda sección
        // No hay campos específicos de historial que limpiar en esta sección
    }

    // Funciones para limpiar historiales (tercera sección)
    function clearHistorialEncargadoTercera() {
        // Esta función se llama para mantener consistencia en la tercera sección
        // No hay campos específicos de historial que limpiar en esta sección
    }

    function clearHistorialAyudanteTercera() {
        // Esta función se llama para mantener consistencia en la tercera sección
        // No hay campos específicos de historial que limpiar en esta sección
    }

    // Función para cargar historial del encargado (funcionalidad específica)
    function loadHistorialEncargado(encargadoId) {
        // Aquí se implementaría la lógica para cargar el historial del encargado
        // Esta función se puede expandir en el futuro si se necesita mostrar historial específico
    }

    // Hacer las funciones globales para uso en otros contextos
    window.clearHistorialEncargado = clearHistorialEncargado;
    window.clearHistorialAyudante = clearHistorialAyudante;
    window.clearHistorialEncargadoTercera = clearHistorialEncargadoTercera;
    window.clearHistorialAyudanteTercera = clearHistorialAyudanteTercera;
    window.loadHistorialEncargado = loadHistorialEncargado;
    window.loadHistorialEncargadoSegundaSeccion = loadHistorialEncargadoSegundaSeccion;
    window.clearHistorialEncargadoSegundaSeccion = clearHistorialEncargadoSegundaSeccion;

    // Evento para confirmar la selección del encargado de segunda sección
    $('#confirmarEncargadoSegundaSeccion').on('click', function() {
        const encargadoSeleccionado = $('#select_encargado_segunda_seccion').val();
        const textoSeleccionado = $('#select_encargado_segunda_seccion option:selected').text();

        if (!encargadoSeleccionado) {
            alert('Por favor seleccione un encargado');
            return;
        }

        // Extraer solo el nombre limpio del formato "fecha - abreviacion - nombre (tipo)" o "fecha|parte|tipo|nombre"
        let nombreEncargado = textoSeleccionado;
        if (textoSeleccionado.includes(' - ')) {
            const partes = textoSeleccionado.split(' - ');
            nombreEncargado = partes[partes.length - 1].replace(/\s*\([^)]*\)\s*$/, '').trim();
        } else if (textoSeleccionado.includes('|')) {
            const partes = textoSeleccionado.split('|');
            nombreEncargado = partes[partes.length - 1].replace(/\s*\([^)]*\)\s*$/, '').trim();
        } else {
            nombreEncargado = nombreEncargado.replace(/\s*\([^)]*\)\s*$/, '').trim();
        }

        // Actualizar los campos
        $('#encargado_id_segunda_seccion').val(encargadoSeleccionado);
        $('#encargado_display_segunda_seccion').val(nombreEncargado);

        // Verificar si necesitamos resetear el ayudante por diferencia de sexo
        const parteSeleccionada = $('#parte_id_segunda_seccion').val();
        const ayudanteActual = $('#ayudante_id_segunda_seccion').val();

        if (parteSeleccionada && ayudanteActual) {
            const selectedOption = $('#parte_id_segunda_seccion').find('option:selected');
            const tipo = selectedOption.data('tipo');

            // Solo verificar si la parte es de tipo 2
            if (tipo == 2) {
                console.log('Verificando sexos al confirmar encargado - Parte tipo 2');
                // Obtener el sexo del encargado y del ayudante
                $.ajax({
                    url: '/verificar-sexos-usuarios',
                    method: 'GET',
                    data: {
                        encargado_id: encargadoSeleccionado,
                        ayudante_id: ayudanteActual
                    },
                    success: function(response) {
                        if (response.success) {
                            const encargadoSexo = response.encargado_sexo;
                            const ayudanteSexo = response.ayudante_sexo;

                            // Si los sexos son diferentes, resetear el campo ayudante
                            if (encargadoSexo !== ayudanteSexo) {
                                console.log('Sexos diferentes al confirmar encargado - Reseteando ayudante:', {encargadoSexo, ayudanteSexo});
                                $('#ayudante_id_segunda_seccion').val('').trigger('change');
                                $('#ayudante_display_segunda_seccion').val('');
                                $('#btn-ayudante-reemplazado-segunda').prop('disabled', true);
                                clearHistorialAyudanteSegundaSeccion();
                                showAlert('modal-alert-container-segunda-seccion', 'info', 'El ayudante ha sido removido porque tiene un sexo diferente al encargado.');
                                // Cerrar modal inmediatamente después de mostrar el mensaje
                                $('#buscarEncargadoSegundaSeccionModal').modal('hide');
                            } else {
                                console.log('Sexos iguales al confirmar encargado - Manteniendo ayudante:', {encargadoSexo, ayudanteSexo});
                                // Cerrar modal inmediatamente si no hay cambios
                                $('#buscarEncargadoSegundaSeccionModal').modal('hide');
                            }
                        } else {
                            console.log('Error en respuesta de verificación de sexos al confirmar:', response);
                            // Cerrar modal incluso si hay error en la respuesta
                            $('#buscarEncargadoSegundaSeccionModal').modal('hide');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al verificar sexos al confirmar encargado:', xhr.responseText);
                        // Cerrar modal incluso si hay error en la petición
                        $('#buscarEncargadoSegundaSeccionModal').modal('hide');
                    }
                });

                // Actualizar campos y botones antes de cerrar el modal
                updateButtonStatesSegundaSeccion();
                return; // Salir para evitar cerrar el modal dos veces
            }
        }

        // Si no hay verificación de sexos, actualizar campos y cerrar modal normalmente
        updateButtonStatesSegundaSeccion();
        $('#buscarEncargadoSegundaSeccionModal').modal('hide');
    });

    // Limpiar Select2 cuando se cierre el modal de buscar encargado segunda sección
    $('#buscarEncargadoSegundaSeccionModal').on('hidden.bs.modal', function() {
        const select = $('#select_encargado_segunda_seccion');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');

        // Limpiar también el historial
        const selectHistorial = $('#select_historial_encargado_segunda_seccion');
        if (selectHistorial.hasClass('select2-hidden-accessible')) {
            selectHistorial.select2('destroy');
        }
        selectHistorial.empty().append('<option value="">Seleccionar un encargado primero...</option>');
        selectHistorial.prop('disabled', true);
    });

    // Función para cargar el historial del encargado en la segunda sección
    function loadHistorialEncargadoSegundaSeccion(encargadoId) {
        const selectHistorial = $('#select_historial_encargado_segunda_seccion');

        // Limpiar el select y mostrar "Cargando..."
        selectHistorial.empty().append('<option value="">Cargando historial...</option>');
        selectHistorial.prop('disabled', false);

        $.ajax({
            url: `/usuarios/${encargadoId}/historial-segunda-seccion`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    selectHistorial.empty();

                    if (response.historial.length > 0) {
                        selectHistorial.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato requerido: fecha | sala | parte | ES | encargado(20chars) | AY | ayudante
                        response.historial.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            // Usar nombres formateados desde el backend con str_pad
                            const encargadoNombre = participacion.encargado_nombre_formateado || '';
                            const ayudanteNombre = participacion.ayudante_nombre_formateado || '';

                            // Formato: fecha|sala|parte|ES|encargado|AY|ayudante (sin espacios alrededor de |)
                            const textoOpcion = `${fechaTexto}|${participacion.sala_abreviacion}|${participacion.parte_abreviacion}|ES|${encargadoNombre}|AY|${ayudanteNombre}`;

                            selectHistorial.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        selectHistorial.prop('disabled', false);

                        // Seleccionar automáticamente el primer elemento (índice 1, ya que 0 es "Seleccionar participación...")
                        if (response.historial.length > 0) {
                            selectHistorial.val(response.historial[0].programa_id).trigger('change');
                        }
                    } else {
                        selectHistorial.append('<option value="">No hay participaciones registradas</option>');
                        selectHistorial.prop('disabled', true);
                    }
                } else {
                    selectHistorial.empty().append('<option value="">Error al cargar historial</option>');
                    selectHistorial.prop('disabled', true);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar historial:', xhr.responseText);
                selectHistorial.empty().append('<option value="">Error al cargar historial</option>');
                selectHistorial.prop('disabled', true);
            }
        });
    }

    // Función para limpiar el historial del encargado en la segunda sección
    function clearHistorialEncargadoSegundaSeccion() {
        const selectHistorial = $('#select_historial_encargado_segunda_seccion');
        selectHistorial.empty().append('<option value="">Seleccionar un encargado primero...</option>');
        selectHistorial.prop('disabled', true);
    }

    // Función para cargar el historial del ayudante en la segunda sección
    function loadHistorialAyudanteSegundaSeccion(ayudanteId) {
        const selectHistorial = $('#select_historial_ayudante_segunda_seccion');

        // Limpiar el select y mostrar "Cargando..."
        selectHistorial.empty().append('<option value="">Cargando historial...</option>');
        selectHistorial.prop('disabled', false);

        $.ajax({
            url: `/usuarios/${ayudanteId}/historial-segunda-seccion`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    selectHistorial.empty();

                    if (response.historial.length > 0) {
                        selectHistorial.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato requerido: fecha | sala | parte | ES | encargado(25chars) | AY | ayudante
                        response.historial.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            // Usar nombres formateados desde el backend con str_pad
                            const encargadoNombre = participacion.encargado_nombre_formateado || '';
                            const ayudanteNombre = participacion.ayudante_nombre_formateado || '';

                            // Formato: fecha|sala|parte|ES|encargado|AY|ayudante (sin espacios alrededor de |)
                            const textoOpcion = `${fechaTexto}|${participacion.sala_abreviacion}|${participacion.parte_abreviacion}|ES|${encargadoNombre}|AY|${ayudanteNombre}`;

                            selectHistorial.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        selectHistorial.prop('disabled', false);

                        // Seleccionar automáticamente el primer elemento (índice 1, ya que 0 es "Seleccionar participación...")
                        if (response.historial.length > 0) {
                            selectHistorial.val(response.historial[0].programa_id).trigger('change');
                        }
                    } else {
                        selectHistorial.append('<option value="">No hay participaciones registradas</option>');
                        selectHistorial.prop('disabled', true);
                    }
                } else {
                    selectHistorial.empty().append('<option value="">Error al cargar historial</option>');
                    selectHistorial.prop('disabled', true);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar historial:', xhr.responseText);
                selectHistorial.empty().append('<option value="">Error al cargar historial</option>');
                selectHistorial.prop('disabled', true);
            }
        });
    }

    // Función para limpiar el historial del ayudante en la segunda sección
    function clearHistorialAyudanteSegundaSeccion() {
        const selectHistorial = $('#select_historial_ayudante_segunda_seccion');
        selectHistorial.empty().append('<option value="">Seleccionar un ayudante primero...</option>');
        selectHistorial.prop('disabled', true);
    }

    // Evento para confirmar la selección del ayudante de segunda sección
    $('#confirmarAyudanteSegundaSeccion').on('click', function() {
        const ayudanteSeleccionado = $('#select_ayudante_segunda_seccion').val();
        const textoSeleccionado = $('#select_ayudante_segunda_seccion option:selected').text();

        if (!ayudanteSeleccionado) {
            alert('Por favor seleccione un ayudante');
            return;
        }

        // Extraer solo el nombre del formato "fecha|sala_abrev|parte_abrev|nombre"
        let nombreAyudante = textoSeleccionado;
        if (textoSeleccionado.includes('|')) {
            const partes = textoSeleccionado.split('|');
            if (partes.length >= 4) {
                // El nombre está en la cuarta parte (índice 3)
                nombreAyudante = partes[3].trim();
            }
        }

        // Actualizar los campos
        $('#ayudante_id_segunda_seccion').val(ayudanteSeleccionado);
        $('#ayudante_display_segunda_seccion').val(nombreAyudante);

        // Habilitar los botones ahora que hay un ayudante seleccionado
    // ...existing code...

        // Actualizar el estado de los botones
        updateButtonStatesSegundaSeccion();

        // Cerrar modal
        $('#buscarAyudanteSegundaSeccionModal').modal('hide');
    });

    // Limpiar Select2 cuando se cierre el modal de buscar ayudante segunda sección
    $('#buscarAyudanteSegundaSeccionModal').on('hidden.bs.modal', function() {
        const select = $('#select_ayudante_segunda_seccion');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');

        // Limpiar también el historial
        const selectHistorial = $('#select_historial_ayudante_segunda_seccion');
        if (selectHistorial.hasClass('select2-hidden-accessible')) {
            selectHistorial.select2('destroy');
        }
        selectHistorial.empty().append('<option value="">Seleccionar un ayudante primero...</option>');
        selectHistorial.prop('disabled', true);
    });

});
</script>

<style>
/* Aplicar fuente Consolas al select2 del campo Encargado de la Segunda Sección */
#encargado_id_segunda_seccion + .select2-container {
    font-family: "Cascadia Mono", monospace !important;
}

#encargado_id_segunda_seccion + .select2-container .select2-selection {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

#encargado_id_segunda_seccion + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

/* Aplicar fuente Consolas al select2 del campo Ayudante de la Segunda Sección */
#ayudante_id_segunda_seccion + .select2-container {
    font-family: Consolas, "Courier New", monospace;
}

#ayudante_id_segunda_seccion + .select2-container .select2-selection {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px;
}

#ayudante_id_segunda_seccion + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px;
}

/* Para el dropdown también */
.select2-container--bootstrap-5 .select2-dropdown .select2-results__options {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px;
}

/* Tamaño de fuente global para todas las opciones seleccionables de Select2 */
.select2-results__option--selectable {
    font-size: 12px;
}

/* Regla específica para prevenir que Bootstrap 5 sobrescriba con 1rem */
.select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

/* Asegurar que el dropdown específico del campo encargado use Consolas */
.select2-dropdown[aria-labelledby="select2-encargado_id_segunda_seccion-container"] {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px;
}

.select2-dropdown[aria-labelledby="select2-encargado_id_segunda_seccion-container"] .select2-results__option {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px;
}

/* Asegurar que el dropdown específico del campo ayudante use Consolas */
.select2-dropdown[aria-labelledby="select2-ayudante_id_segunda_seccion-container"] {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px;
}
.select2-dropdown[aria-labelledby="select2-ayudante_id_segunda_seccion-container"] .select2-results__option {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px;
}

/* Estilo para el select de orador inicial */
#orador_inicial,
#orador_inicial option,
.form-select#orador_inicial {
    font-size: 12px !important;
    font-family: "Cascadia Mono", monospace !important;
}

/* Asegurar que se mantenga después de la carga */
select#orador_inicial.form-select {
    font-size: 12px !important;
}


/* Aplicar fuente Consolas al Select2 del campo Historial de Encargado */
#historial_encargado_segunda_seccion + .select2-container {
    font-family: "Cascadia Mono", monospace !important;
}

#historial_encargado_segunda_seccion + .select2-container .select2-selection {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

#historial_encargado_segunda_seccion + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

/* Asegurar que el dropdown específico del campo historial use Consolas */
.select2-dropdown[aria-labelledby="select2-historial_encargado_segunda_seccion-container"] {
    ffont-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

.select2-dropdown[aria-labelledby="select2-historial_encargado_segunda_seccion-container"] .select2-results__option {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

/* Aplicar fuente Consolas al Select2 del campo Historial de Ayudante */
#historial_ayudante_segunda_seccion + .select2-container {
    font-family: "Cascadia Mono", monospace !important;
}

#historial_ayudante_segunda_seccion + .select2-container .select2-selection {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px;
}

#historial_ayudante_segunda_seccion + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px;
}

/* Asegurar que el dropdown específico del campo historial del ayudante use Consolas */
.select2-dropdown[aria-labelledby="select2-historial_ayudante_segunda_seccion-container"] {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px;
}

.select2-dropdown[aria-labelledby="select2-historial_ayudante_segunda_seccion-container"] .select2-results__option {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px;
}

/* Estilos para el Select2 del modal buscar orador inicial */
#select_orador_inicial + .select2-container .select2-selection {
    font-size: 12px !important;
}

#select_orador_inicial + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

/* Para el dropdown del modal buscar orador inicial */
.select2-dropdown[aria-labelledby="select2-select_orador_inicial-container"] .select2-results__option {
    font-size: 12px !important;
}

/* Estilos para el Select2 del modal historial orador inicial */
#select_historial_orador + .select2-container .select2-selection {
    font-size: 12px !important;
}

#select_historial_orador + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

/* Para el dropdown del modal historial orador inicial */
.select2-dropdown[aria-labelledby="select2-select_historial_orador-container"] .select2-results__option {
    font-size: 12px !important;
}

/* Estilos para el Select2 del modal buscar orador final */
#select_orador_final + .select2-container .select2-selection {
    font-size: 12px !important;
}

#select_orador_final + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

/* Para el dropdown del modal buscar orador final */
.select2-dropdown[aria-labelledby="select2-select_orador_final-container"] .select2-results__option {
    font-size: 12px !important;
}

/* Estilos para el Select2 del modal historial orador final */
#select_historial_orador_final + .select2-container .select2-selection {
    font-size: 12px !important;
}

#select_historial_orador_final + .select2-container .select2-selection__rendered {
    font-size: 12px !important;
}

/* Para el dropdown del modal historial orador final */
.select2-dropdown[aria-labelledby="select2-select_historial_orador_final-container"] .select2-results__option {
    font-size: 12px !important;
}

/* Estilos para el Select2 del modal buscar presidencia */
#select_presidencia + .select2-container .select2-selection {
    font-size: 12px !important;
}

#select_presidencia + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

/* Para el dropdown del modal buscar presidencia */
.select2-dropdown[aria-labelledby="select2-select_presidencia-container"] .select2-results__option {
    font-size: 12px !important;
}

/* Estilos para el Select2 del modal historial presidencia */
#select_historial_presidencia + .select2-container .select2-selection {
    font-size: 12px !important;
}

#select_historial_presidencia + .select2-container .select2-selection__rendered {
    font-size: 14px !important;
}

/* Para el dropdown del modal historial presidencia */
.select2-dropdown[aria-labelledby="select2-select_historial_presidencia-container"] .select2-results__option {
    font-size: 12px !important;
}

/* Estilos para el Select2 del modal buscar canción inicial */
#select_cancion_inicial + .select2-container .select2-selection {
    font-size: 12px !important;
}

#select_cancion_inicial + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

.select2-dropdown[aria-labelledby="select2-select_cancion_inicial-container"] .select2-results__option {
    font-size: 12px !important;
}

/* Estilos para el Select2 del modal buscar canción intermedia */
#select_cancion_intermedia + .select2-container .select2-selection {
    font-size: 12px !important;
}

#select_cancion_intermedia + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

.select2-dropdown[aria-labelledby="select2-select_cancion_intermedia-container"] .select2-results__option {
    font-size: 12px !important;
}

/* Estilos para el Select2 del modal buscar canción final */
#select_cancion_final + .select2-container .select2-selection {
    font-size: 12px !important;
}

#select_cancion_final + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

.select2-dropdown[aria-labelledby="select2-select_cancion_final-container"] .select2-results__option {
    font-size: 12px !important;
}

/* Estilos para el Select2 del campo Encargado del primer modal (datatable 1) */
#encargado_id + .select2-container .select2-selection {
    font-size: 12px !important;
}

#encargado_id + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

.select2-dropdown[aria-labelledby="select2-encargado_id-container"] .select2-results__option {
    font-size: 12px !important;
}

/* Estilos para el Select2 del modal buscar encargado parte */
#select_encargado_parte + .select2-container .select2-selection {
    font-size: 12px !important;
}

#select_encargado_parte + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

.select2-dropdown[aria-labelledby="select2-select_encargado_parte-container"] .select2-results__option {
    font-size: 12px !important;
}

/* Estilos para el Select2 del modal historial encargado parte */
#select_historial_encargado_parte + .select2-container .select2-selection {
    font-size: 12px !important;
}

#select_historial_encargado_parte + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

.select2-dropdown[aria-labelledby="select2-select_historial_encargado_parte-container"] .select2-results__option {
    font-size: 12px !important;
}

/* Estilos para el Select2 del modal buscar encargado segunda sección */
#select_encargado_segunda_seccion + .select2-container .select2-selection {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

#select_encargado_segunda_seccion + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

.select2-dropdown[aria-labelledby="select2-select_encargado_segunda_seccion-container"] .select2-results__option {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

/* Estilos para el Select2 del historial del encargado segunda sección */
#select_historial_encargado_segunda_seccion + .select2-container .select2-selection {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

#select_historial_encargado_segunda_seccion + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

.select2-dropdown[aria-labelledby="select2-select_historial_encargado_segunda_seccion-container"] {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

.select2-dropdown[aria-labelledby="select2-select_historial_encargado_segunda_seccion-container"] .select2-results__option {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

/* Estilos para el Select2 del modal buscar ayudante segunda sección */
#select_ayudante_segunda_seccion + .select2-container .select2-selection {
    font-size: 12px !important;
}

#select_ayudante_segunda_seccion + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

.select2-dropdown[aria-labelledby="select2-select_ayudante_segunda_seccion-container"] .select2-results__option {
    font-size: 12px !important;
}

/* Estilos para el Select2 del historial del ayudante segunda sección */
#select_historial_ayudante_segunda_seccion + .select2-container .select2-selection {
    font-size: 12px !important;
}

#select_historial_ayudante_segunda_seccion + .select2-container .select2-selection__rendered {
    font-family: "Cascadia Mono", monospace !important;
    font-size: 12px !important;
}

.select2-dropdown[aria-labelledby="select2-select_historial_ayudante_segunda_seccion-container"] {
    font-size: 12px;
}

.select2-dropdown[aria-labelledby="select2-select_historial_ayudante_segunda_seccion-container"] .select2-results__option {
    font-size: 12px !important;
}
</style>

@endpush
@endsection