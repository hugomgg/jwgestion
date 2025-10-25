@extends('layouts.app')

@section('content')
@if($currentUser->isCoordinator() || $currentUser->isOrganizer())
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
                            <div class="d-flex justify-content-end gap-2">
                                <!-- Botón Programa Anterior -->
                                @if(isset($programaAnterior) && $programaAnterior)
                                <a href="{{ route('programas.edit', $programaAnterior->id) }}" 
                                   class="btn btn-outline-primary" 
                                   title="Programa anterior">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                @else
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        disabled 
                                        title="No hay programa anterior">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                @endif

                                <!-- Botón Programa Posterior -->
                                @if(isset($programaPosterior) && $programaPosterior)
                                <a href="{{ route('programas.edit', $programaPosterior->id) }}" 
                                   class="btn btn-outline-primary" 
                                   title="Programa posterior">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                                @else
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        disabled 
                                        title="No hay programa posterior">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                                @endif

                                <!-- Botón Volver -->
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
                                    <label for="fecha" class="form-label">Fecha * </label>
                                    <input type="date" class="form-control" id="fecha" name="fecha" value="{{ $programa->fecha ? $programa->fecha->format('Y-m-d') : '' }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="orador_inicial" class="form-label">Orador Inicial</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="orador_inicial_display" name="orador_inicial_display"
                                                   value="{{ $programa->oradorInicial ? $programa->oradorInicial->name : '' }}" disabled>
                                            <button type="button" class="btn btn-outline-primary" id="btn-buscar-orador-inicial"
                                                    title="Buscar Orador Inicial" onclick="buscarOradorInicial()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" id="orador_inicial" name="orador_inicial" value="{{ $programa->orador_inicial }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cancion_pre" class="form-label">Canción Inicial</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="cancion_pre_display" name="cancion_pre_display"
                                                   value="{{ $programa->cancionPre ? ($programa->cancionPre->numero ? $programa->cancionPre->numero . ' - ' : '') . $programa->cancionPre->nombre : '' }}" disabled>
                                            <button type="button" class="btn btn-outline-primary" id="btn-buscar-cancion-inicial"
                                                    title="Buscar Canción Inicial" onclick="buscarCancionInicial()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" id="cancion_pre" name="cancion_pre" value="{{ $programa->cancion_pre }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="presidencia" class="form-label">Presidencia</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="presidencia_display" name="presidencia_display"
                                                   value="{{ $programa->presidenciaUsuario ? $programa->presidenciaUsuario->name : '' }}" disabled>
                                            <button type="button" class="btn btn-outline-primary" id="btn-buscar-presidencia"
                                                    title="Buscar Presidentes" onclick="buscarPresidencia()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" id="presidencia" name="presidencia" value="{{ $programa->presidencia }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección TB -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-header" style="background-color: #BBE6FC;">
                                        <div class="row align-items-center">
                                            <div class="d-flex justify-content-between align-items-center mb-0">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-book me-2"></i>TESOROS DE LA BIBLIA
                                                </h6>
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
                                                        <th style="width: 10%;">Número</th>
                                                        <th style="width: 10%;">Tiempo (min)</th>
                                                        <th style="width: 10%;">Parte</th>
                                                        <th style="width: 30%;">Encargado</th>
                                                        <th style="width: 30%;">Tema</th>
                                                        <th style="width: 10%;">Acciones</th>
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

                        <!-- Sección de Escuela  -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-header mb-0" style="background-color: #FCF2BB;">
                                        <div class="row align-items-center">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0" style="margin-top:10px;">
                                                    <i class="fas fa-graduation-cap me-2"></i>ESCUELA SEAMOS MEJORES MAESTROS
                                                </h6>
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#parteProgramaSegundaSeccionModal" onclick="openCreateParteSegundaSeccionModal()">
                                                        <i class="fas fa-plus me-2"></i>Nueva Asignación SMM
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Tabla principal de Escuela por Sala-->
                                        <div class="mb-4">
                                            <div class="table-responsive">
                                                <table id="partesSegundaSeccionTable" class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 6%">Número</th>
                                                            <th style="width: 8%">Sala</th>
                                                            <th style="width: 9%">Tiempo (min)</th>
                                                            <th style="width: 9%">Parte</th>
                                                            <th style="width: 25%">Estudiante</th>
                                                            <th style="width: 25%">Ayudante</th>
                                                            <th style="width: 7%">Lección</th>
                                                            <th style="width: 11%;">Acciones</th>
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
                        </div>
                        <!-- Sección Nuestra Vida Cristiana -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-header" style="background-color: #FCBBBF;">
                                        <div class="row align-items-center">
                                            <div class="d-flex justify-content-between align-items-center mb-0">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-briefcase me-2"></i>NUESTRA VIDA CRISTIANA
                                                </h6>
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#parteProgramaNVModal" onclick="openCreateParteNVModal()">
                                                        <i class="fas fa-plus me-2"></i>Nueva Asignación NVC
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="partesNVTable" class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 10%;">Número</th>
                                                        <th style="width: 10%;">Tiempo (min)</th>
                                                        <th style="width: 10%;">Parte</th>
                                                        <th style="width: 30%;">Encargado</th>
                                                        <th style="width: 30%;">Tema</th>
                                                        <th style="width: 10%;">Acciones</th>
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cancion_en" class="form-label">Canción Intermedia</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="cancion_en_display" name="cancion_en_display"
                                                   value="{{ $programa->cancionEn ? ($programa->cancionEn->numero ? $programa->cancionEn->numero . ' - ' : '') . $programa->cancionEn->nombre : '' }}" disabled>
                                            <button type="button" class="btn btn-outline-primary" id="btn-buscar-cancion-intermedia"
                                                    title="Buscar Canción Intermedia" onclick="buscarCancionIntermedia()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" id="cancion_en" name="cancion_en" value="{{ $programa->cancion_en }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cancion_post" class="form-label">Canción Final</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="cancion_post_display" name="cancion_post_display"
                                                   value="{{ $programa->cancionPost ? ($programa->cancionPost->numero ? $programa->cancionPost->numero . ' - ' : '') . $programa->cancionPost->nombre : '' }}" disabled>
                                            <button type="button" class="btn btn-outline-primary" id="btn-buscar-cancion-final"
                                                    title="Buscar Canción Final" onclick="buscarCancionFinal()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" id="cancion_post" name="cancion_post" value="{{ $programa->cancion_post }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="orador_final" class="form-label">Orador Final</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="orador_final_display" name="orador_final_display"
                                                   value="{{ $programa->oradorFinal ? $programa->oradorFinal->name : '' }}" disabled>
                                            <button type="button" class="btn btn-outline-primary" id="btn-buscar-orador-final"
                                                    title="Buscar Orador Final" onclick="buscarOradorFinal()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" id="orador_final" name="orador_final" value="{{ $programa->orador_final }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 d-none">
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
                                    <a href="{{ route('programas.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver a Programas
                                    </a>
                                    <button type="submit" class="btn btn-success d-none" id="updateProgramaBtn">
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

<!-- Modal para Crear/Editar Primera Parte del Programa -->
<div class="modal fade" id="parteProgramaModal" tabindex="-1" aria-labelledby="parteProgramaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="parteProgramaModalLabel">Nueva Asignación de Tesoros de la Biblia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="parteProgramaForm">
                <div class="modal-body">
                    <div id="modal-alert-container"></div>
                    @csrf
                    <input type="hidden" id="parte_programa_id" name="parte_programa_id">
                    <input type="hidden" id="programa_id_parte" name="programa_id" value="{{ $programa->id }}">
                    <input type="hidden" id="sala_id" name="sala_id" value="1">
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
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="encargado_display" name="encargado_display"
                                               placeholder="Seleccionar encargado..." disabled>
                                        <button type="button" class="btn btn-outline-primary" id="btn-buscar-encargado"
                                                title="Buscar Encargado" onclick="buscarEncargadoParte()" disabled>
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success" id="btn-agregar-reemplazado"
                                                title="Agregar como Encargado Reemplazado" onclick="agregarEncargadoReemplazado()" disabled>
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" id="encargado_id" name="encargado_id" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="encargado_reemplazado_display" class="form-label">Encargado Reemplazado</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="encargado_reemplazado_display" name="encargado_reemplazado_display"
                                           placeholder="Sin Encargado reemplazado..." disabled>
                                    <button type="button" class="btn btn-outline-danger" id="btn-eliminar-reemplazado"
                                            title="Eliminar Encargado Reemplazado" onclick="eliminarEncargadoReemplazado()" disabled>
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="encargado_reemplazado_id" name="encargado_reemplazado_id">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveParteBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal para Crear/Editar Parte Seamos Mejores Maestros -->
<div class="modal fade" id="parteProgramaSegundaSeccionModal" tabindex="-1" aria-labelledby="parteProgramaSegundaSeccionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="parteProgramaSegundaSeccionModalLabel">Nueva Asignación Seamos Mejores Maestros</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="parteProgramaSegundaSeccionForm">
                <div class="modal-body">
                    <!-- Contenedor de alertas del modal -->
                    <div id="modal-alert-container-segunda-seccion"></div>

                    @csrf
                    <input type="hidden" id="parte_programa_segunda_seccion_id" name="parte_programa_id">
                    <input type="hidden" id="programa_id_segunda_seccion" name="programa_id" value="{{ $programa->id }}">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sala_id_segunda_seccion" class="form-label">Sala <span class="text-danger">*</span></label>
                                <select class="form-select" id="sala_id_segunda_seccion" name="sala_id" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($salas as $sala)
                                        <option value="{{ $sala->id }}" {{ $sala->id == 1 ? 'selected' : '' }}>{{ $sala->abreviacion }} - {{ $sala->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="parte_id_segunda_seccion" class="form-label">Asignación <span class="text-danger">*</span></label>
                                <select class="form-select" id="parte_id_segunda_seccion" name="parte_id" required>
                                    <option value="">Cargando...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="encargado_display_segunda_seccion" class="form-label">Estudiante <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="encargado_display_segunda_seccion" name="encargado_display" placeholder="Seleccionar una parte primero..." disabled>
                                    <button type="button" class="btn btn-outline-primary" id="btn-buscar-encargado-segunda" onclick="buscarEncargadoSegundaSeccion()" title="Buscar Estudiante" disabled>
                                        <i class="fas fa-search"></i>
                                    </button>

                                    <button type="button" class="btn btn-outline-success" id="btn-agregar-encargado-reemplazado-segunda" onclick="agregarEncargadoReemplazado()" title="Agregar Estudiante como Reemplazado" disabled style="display: none;">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="btn-encargado-reemplazado-segunda" onclick="manejarEncargadoReemplazado()" title="Estudiante reemplazado" disabled>
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
                                <label for="encargado_reemplazado_segunda_seccion" class="form-label">Estudiante Reemplazado</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="encargado_reemplazado_segunda_seccion" readonly>
                                    <input type="hidden" id="encargado_reemplazado_id_segunda_seccion" name="encargado_reemplazado_id">
                                    <button type="button" class="btn btn-outline-danger" onclick="clearEncargadoReemplazado()" title="Eliminar estudiante reemplazado">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ayudante_reemplazado_segunda_seccion" class="form-label">Ayudante Reemplazado</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="ayudante_reemplazado_segunda_seccion" readonly>
                                    <input type="hidden" id="ayudante_reemplazado_id_segunda_seccion" name="ayudante_reemplazado_id">
                                    <button type="button" class="btn btn-outline-danger" onclick="clearAyudanteReemplazado()" title="Eliminar ayudante reemplazado">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tiempo_segunda_seccion" class="form-label">Tiempo (minutos) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="tiempo_segunda_seccion" name="tiempo" min="1" max="60" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="leccion_segunda_seccion" class="form-label">Lección </label>
                                <input type="text" class="form-control" id="leccion_segunda_seccion" name="leccion" maxlength="500">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveParteSegundaSeccionBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                                <label for="encargado_id_tercera_seccion" class="form-label">Estudiante <span class="text-danger">*</span></label>
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
                        <label for="leccion_tercera_seccion" class="form-label">Lección </label>
                        <input type="text" class="form-control" id="leccion_tercera_seccion" name="leccion" maxlength="500">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveTerceraSeccionBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Crear/Editar Parte del Programa NVC -->
<div class="modal fade" id="parteProgramaNVModal" tabindex="-1" aria-labelledby="parteProgramaNVModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="parteProgramaNVModalLabel">Nueva Asignación de Nuestra Vida Cristiana</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="parteProgramaNVForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="parte_programa_nv_id" name="parte_programa_id">
                    <input type="hidden" id="sala_id_nv" name="sala_id_nv" value="1">
                    <input type="hidden" id="programa_id_parte_nv" name="programa_id" value="{{ $programa->id }}">
                    <div id="modal-alert-container-nv"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="parte_id_nv" class="form-label">Asignación <span class="text-danger">*</span></label>
                                <select class="form-select" id="parte_id_nv" name="parte_id" required>
                                    <option value="">Cargando...</option>
                                </select>
                                <input type="text" class="form-control" id="parte_display_nv" style="display: none;" disabled>
                                <input type="hidden" id="parte_id_hidden_nv" name="parte_id">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tiempo_parte_nv" class="form-label">Tiempo (minutos) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="tiempo_parte_nv" name="tiempo" min="1" max="60" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tema_parte_nv" class="form-label">Tema</label>
                        <textarea class="form-control" id="tema_parte_nv" name="tema" rows="2" maxlength="500"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="encargado_id_nv" class="form-label">Encargado <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="encargado_display_nv" name="encargado_display"
                                               placeholder="Seleccionar encargado..." disabled>
                                        <button type="button" class="btn btn-outline-primary" id="btn-buscar-encargado-nv"
                                                title="Buscar Encargado" onclick="buscarEncargadoParteNV()" disabled>
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success" id="btn-agregar-reemplazado-nv"
                                                title="Agregar como Encargado Reemplazado" onclick="agregarEncargadoReemplazadoNV()" disabled>
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" id="encargado_id_nv" name="encargado_id" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="encargado_reemplazado_display_nv" class="form-label">Encargado Reemplazado</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="encargado_reemplazado_display_nv" name="encargado_reemplazado_display"
                                           placeholder="Sin encargado reemplazado..." disabled>
                                    <button type="button" class="btn btn-outline-danger" id="btn-eliminar-reemplazado-nv"
                                            title="Eliminar Encargado Reemplazado" onclick="eliminarEncargadoReemplazadoNV()" disabled>
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="encargado_reemplazado_id_nv" name="encargado_reemplazado_id">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveParteNVBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
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
                <p class="mb-0">¿Está seguro de que desea eliminar esta Asignación del programa?</p>
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
                <div class="mb-3">
                    <label for="select_historial_orador" class="form-label">Historial de Participaciones como Orador</label>
                    <select class="form-select" id="select_historial_orador" style="width: 100%;" disabled>
                        <option value="">Cargando historial...</option>
                    </select>
                    <small class="form-text text-muted">Participaciones ordenadas desde la más reciente</small>
                </div>
            </div>
            <br><br><br><br><br><br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarOradorInicial">
                    <i class="fas fa-check me-2"></i>Guardar Orador Inicial
                </button>
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
                <div class="mb-3">
                    <label for="select_historial_orador_final" class="form-label">Historial de Participaciones como Orador</label>
                    <select class="form-select" id="select_historial_orador_final" style="width: 100%;" disabled>
                        <option value="">Seleccione un orador para ver historial...</option>
                    </select>
                    <small class="form-text text-muted">Participaciones ordenadas desde la más reciente</small>
                </div>
            </div>
            <br><br><br><br><br><br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarOradorFinal">
                    <i class="fas fa-check me-2"></i>Guardar Orador Final
                </button>
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
                <div class="mb-3">
                    <label for="select_historial_presidencia" class="form-label">Historial de Participaciones como Presidente</label>
                    <select class="form-select" id="select_historial_presidencia" style="width: 100%;" disabled>
                        <option value="">Seleccione un presidente para ver historial...</option>
                    </select>
                    <small class="form-text text-muted">Participaciones ordenadas desde la más reciente</small>
                </div>
            </div>
            <br><br><br><br><br><br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarPresidencia">
                    <i class="fas fa-check me-2"></i>Guardar Presidente
                </button>
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
            <br><br><br><br><br><br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarCancionInicial">
                    <i class="fas fa-check me-2"></i>Guardar Canción Inicial
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
            <br><br><br><br><br><br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarCancionIntermedia">
                    <i class="fas fa-check me-2"></i>Guardar Canción Intermedia
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
            <br><br><br><br><br><br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarCancionFinal">
                    <i class="fas fa-check me-2"></i>Guardar Canción Final
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
                </div>
                <div class="mb-3">
                    <label for="select_historial_encargado_parte" class="form-label">Historial del Encargado</label>
                    <select class="form-select" id="select_historial_encargado_parte" style="width: 100%;" disabled>
                        <option value="">Seleccione un encargado primero...</option>
                    </select>
                    <small class="form-text text-muted">Participaciones como encargado en la parte actual, ordenadas desde la más reciente</small>
                </div>
            </div>
            <br><br><br><br><br><br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarEncargadoParte">
                    <i class="fas fa-check me-2"></i>Seleccionar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Buscar Encargado del Datatable NVC -->
<div class="modal fade" id="buscarEncargadoParteNVModal" tabindex="-1" aria-labelledby="buscarEncargadoParteNVModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarEncargadoParteNVModalLabel">
                    <i class="fas fa-search me-2"></i>Buscar Encargado NVC
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_encargado_parte_nv" class="form-label">Seleccionar Encargado</label>
                    <select class="form-select" id="select_encargado_parte_nv" style="width: 100%;">
                        <option value="">Cargando usuarios...</option>
                    </select>
                    <small class="form-text text-muted">Usuarios con participaciones en la parte seleccionada, ordenados por fecha más reciente</small>
                </div>
                <div class="mb-3">
                    <label for="select_historial_encargado_parte_nv" class="form-label">Historial del Encargado</label>
                    <select class="form-select" id="select_historial_encargado_parte_nv" style="width: 100%;" disabled>
                        <option value="">Seleccione un encargado primero...</option>
                    </select>
                    <small class="form-text text-muted">Participaciones como encargado en la parte actual, ordenadas desde la más reciente</small>
                </div>
            </div>
            <br><br><br><br><br><br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarEncargadoParteNV">
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
                    <i class="fas fa-search me-2"></i>Buscar Estudiante (Sala Principal)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3" id="filtro_sexo_encargado_segunda_container">
                    <label class="form-label">Filtrar por Sexo</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="filtro_sexo_encargado_segunda" id="filtro_hombres_encargado_segunda" value="1">
                            <label class="form-check-label" for="filtro_hombres_encargado_segunda">
                                <i class="fas fa-male me-1"></i>Hombres
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="filtro_sexo_encargado_segunda" id="filtro_mujeres_encargado_segunda" value="2" checked>
                            <label class="form-check-label" for="filtro_mujeres_encargado_segunda">
                                <i class="fas fa-female me-1"></i>Mujeres
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="select_encargado_segunda_seccion" class="form-label">Seleccionar Estudiante</label>
                    <select class="form-select" id="select_encargado_segunda_seccion" style="width: 100%;">
                        <option value="">Cargando estudiantes...</option>
                    </select>
                    <small class="form-text text-muted">Estudiantes o ayudante que han participado en partes_programa, ordenados por fecha más reciente.</small>
                </div>

                <div class="mb-3">
                    <label for="select_historial_encargado_segunda_seccion" class="form-label">Historial del Estudiante</label>
                    <select class="form-select" id="select_historial_encargado_segunda_seccion" style="width: 100%;" disabled>
                        <option value="">Seleccionar un estudiante primero...</option>
                    </select>
                    <small class="form-text text-muted">Historial de participaciones del estudiante seleccionado en la segunda sección, ordenadas desde la más reciente.</small>
                </div>
            </div>
            <br><br><br><br><br><br>
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
                    <i class="fas fa-search me-2"></i>Buscar Ayudante
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3" id="filtro_sexo_ayudante_segunda_container" style="display: none;">
                    <label class="form-label">Filtrar por Sexo</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="filtro_sexo_ayudante_segunda" id="filtro_hombres_ayudante_segunda" value="1">
                            <label class="form-check-label" for="filtro_hombres_ayudante_segunda">
                                <i class="fas fa-male me-1"></i>Hombres
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="filtro_sexo_ayudante_segunda" id="filtro_mujeres_ayudante_segunda" value="2">
                            <label class="form-check-label" for="filtro_mujeres_ayudante_segunda">
                                <i class="fas fa-female me-1"></i>Mujeres
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="select_ayudante_segunda_seccion" class="form-label">Seleccionar Ayudante</label>
                    <select class="form-select" id="select_ayudante_segunda_seccion" style="width: 100%;">
                        <option value="">Cargando ayudantes...</option>
                    </select>
                    <small class="form-text text-muted">Ayudantes que han participado en partes_programa, ordenados por fecha más reciente.</small>
                </div>

                <div class="mb-3">
                    <label for="select_historial_ayudante_segunda_seccion" class="form-label">Historial del Ayudante</label>
                    <select class="form-select" id="select_historial_ayudante_segunda_seccion" style="width: 100%;" disabled>
                        <option value="">Seleccionar un ayudante primero...</option>
                    </select>
                    <small class="form-text text-muted">Historial de participaciones del ayudante seleccionado en la segunda sección, ordenadas desde la más reciente.</small>
                </div>
            </div>
            <br><br><br><br><br><br>
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
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    El historial de participaciones ahora se muestra en el modal de búsqueda de encargados.
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
                    <i class="fas fa-user-plus me-2"></i>Confirmar Agregar Estudiante Reemplazado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Desea agregar a <strong id="nombreEncargadoAgregar"></strong> como Estudiante Reemplazado?</p>
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
                    <i class="fas fa-user-times me-2"></i>Confirmar Eliminar Estudiante Reemplazado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Desea eliminar a <strong id="nombreEncargadoEliminar"></strong> como Estudiante Reemplazado?</p>
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

<!-- Modal de Éxito para Guardado -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <h5 class="modal-title text-success" id="successModalLabel">¡Éxito!</h5>
                <p class="mb-0" id="successModalMessage">Programa guardado exitosamente</p>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
let partesTable;
let partesSegundaSeccionTable;
let partesNVTable;
let isEditMode = false;
const userIsCoordinator = {{ (Auth::user()->isCoordinator() || Auth::user()->isOrganizer()) ? 'true' : 'false' }};
window.editingParteTwoData = false; // Variable para controlar la carga en modo edición

// Variables para manejar reemplazados
let programmaticChange = false; // Variable para evitar detección de reemplazado en cambios programáticos
</script>
<script src="{{ asset('js/programas-edit.js') }}"></script>
@endpush
@push('styles')
<link rel="stylesheet" href="{{ asset('css/programas-edit.css') }}">
@endpush
@endsection
