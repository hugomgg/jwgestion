@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/informes-index.css') }}">
<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="fas fa-id-card me-2"></i>Gestión de Informes
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                <!-- Botón de Búsqueda Avanzada -->
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearchCollapse" aria-expanded="false" aria-controls="advancedSearchCollapse">
                                    <i class="fas fa-search me-2"></i>Búsqueda Avanzada
                                </button>
                                
                                <!-- Botón Ver Informes -->
                                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#verInformesModal">
                                    <i class="fas fa-eye me-2"></i>Informes por Grupo
                                </button>
                                
                                <!-- Botón Informe Congregación -->
                                <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#informeCongregacionModal">
                                    <i class="fas fa-chart-bar me-2"></i>Informe Congregación
                                </button>
                                
                                <!-- Botón Registro por Publicador -->
                                <button class="btn btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#registroPublicadorModal">
                                    <i class="fas fa-clipboard-list me-2"></i>Registro por Publicador
                                </button>
                            </div>
                            
                            <!-- Collapse para Búsqueda Avanzada -->
                            <div class="collapse mt-3" id="advancedSearchCollapse">
                                <div class="card card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="anioFilter" class="form-label">Año:</label>
                                            <select class="form-select" id="anioFilter">
                                                <option value="">Todos</option>
                                                @foreach($anios as $i)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="mesFilter" class="form-label">Mes:</label>
                                            <select class="form-select" id="mesFilter">
                                                <option value="">Todos</option>
                                                <option value="1">Enero</option>
                                                <option value="2">Febrero</option>
                                                <option value="3">Marzo</option>
                                                <option value="4">Abril</option>
                                                <option value="5">Mayo</option>
                                                <option value="6">Junio</option>
                                                <option value="7">Julio</option>
                                                <option value="8">Agosto</option>
                                                <option value="9">Septiembre</option>
                                                <option value="10">Octubre</option>
                                                <option value="11">Noviembre</option>
                                                <option value="12">Diciembre</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="grupoFilter" class="form-label">Grupo:</label>
                                            <select class="form-select" id="grupoFilter">
                                                <option value="">Todos</option>
                                                @foreach($grupos as $grupo)
                                                    <option value="{{ $grupo->nombre }}">{{ $grupo->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="servicioFilter" class="form-label">Servicio:</label>
                                            <select class="form-select" id="servicioFilter">
                                                <option value="">Todos</option>
                                                @foreach($servicios as $servicio)
                                                    <option value="{{ $servicio->nombre }}">{{ $servicio->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Mensajes de estado -->
                    <div id="alert-container"></div>
                    <div class="table-responsive">
                        <table id="informesTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="10%">ID</th>
                                    <th width="10%">Año</th>
                                    <th width="15%">Mes</th>
                                    <th width="10%">Grupo</th>
                                    <th width="25%">Usuario</th>
                                    <th width="5%">Participa</th>
                                    <th width="15%">Servicio</th>
                                    <th width="10%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($informes as $informe)
                                <tr data-informe-id="{{ $informe->id }}">
                                    <td>{{ $informe->id }}</td>
                                    <td>{{ $informe->anio }}</td>
                                    <td>
                                        @php
                                            $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                                        @endphp
                                        {{ $meses[$informe->mes] ?? $informe->mes }}
                                    </td>
                                    <td>
                                        <span class="badge bg-dark">{{ $informe->grupo_nombre }}</span>
                                    </td>
                                    <td>{{ $informe->usuario_nombre }}</td>
                                    <td>
                                        @if($informe->participa)
                                            <span class="badge bg-success">Sí</span>
                                        @else
                                            <span class="badge bg-danger">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">{{ $informe->servicio_nombre }}</span>
                                    </td>
                                    @if(Auth::user()->isAdmin() || Auth::user()->isSupervisor())
                                        <td>
                                            <span class="badge bg-secondary">{{ $informe->congregacion_nombre }}</span>
                                        </td>
                                    @endif
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info view-informe"
                                                    data-informe-id="{{ $informe->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Ver informe">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if(Auth::user()->canModify() && !Auth::user()->isSubsecretary() && !Auth::user()->isSuborganizer())
                                                <button type="button" class="btn btn-sm btn-warning edit-informe"
                                                        data-informe-id="{{ $informe->id }}"
                                                        data-bs-toggle="tooltip"
                                                        title="Editar informe">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger delete-informe"
                                                        data-informe-id="{{ $informe->id }}"
                                                        data-informe-name="{{ $informe->usuario_nombre }} - {{ $meses[$informe->mes] ?? $informe->mes }} {{ $informe->anio }}"
                                                        data-bs-toggle="tooltip"
                                                        title="Eliminar informe">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Informe -->
<div class="modal fade" id="addInformeModal" tabindex="-1" aria-labelledby="addInformeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInformeModalLabel">
                    <i class="fas fa-id-card me-2"></i>Agregar Nuevo Informe
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addInformeForm" novalidate>
                @csrf
                <div class="modal-body">
                    <!-- Contenedor de errores -->
                    <div id="addInformeErrorContainer" class="alert alert-danger d-none" role="alert">
                        <strong><i class="fas fa-exclamation-triangle me-2"></i>Errores de validación:</strong>
                        <ul id="addInformeErrorList" class="mb-0 mt-2"></ul>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="anio" class="form-label">Año *</label>
                                <select class="form-select" id="anio" name="anio" required>
                                    <option value="">Seleccionar año...</option>
                                    @foreach($anios as $i)
                                        <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mes" class="form-label">Mes *</label>
                                <select class="form-select" id="mes" name="mes" required>
                                    <option value="">Seleccionar mes...</option>
                                    <option value="1">Enero</option>
                                    <option value="2">Febrero</option>
                                    <option value="3">Marzo</option>
                                    <option value="4">Abril</option>
                                    <option value="5">Mayo</option>
                                    <option value="6">Junio</option>
                                    <option value="7">Julio</option>
                                    <option value="8">Agosto</option>
                                    <option value="9">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="grupo_id" class="form-label">Grupo *</label>
                                <select class="form-select" id="grupo_id" name="grupo_id" required>
                                    <option value="">Seleccionar grupo...</option>
                                    @foreach($grupos as $grupo)
                                        <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_id" class="form-label">Publicador *</label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">Seleccionar publicador...</option>
                                    {{-- Los usuarios se cargarán dinámicamente al seleccionar un grupo --}}
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="participa" class="form-label">Participación *</label>
                                <select class="form-select" id="participa" name="participa" required>
                                    <option value="">Seleccionar participación...</option>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="servicio_id" class="form-label">Servicio *</label>
                                <select class="form-select" id="servicio_id" name="servicio_id" required>
                                    <option value="">Seleccionar servicio...</option>
                                    @foreach($servicios as $servicio)
                                        <option value="{{ $servicio->id }}">{{ $servicio->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cantidad_estudios" class="form-label">Cantidad de Estudios</label>
                                <input type="number" class="form-control" id="cantidad_estudios" name="cantidad_estudios" min="0" value="0">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="horas" class="form-label">Horas de Servicio</label>
                                <input type="number" class="form-control" id="horas" name="horas" min="0" step="0.5">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="comentario" class="form-label">Comentario</label>
                                <textarea class="form-control" id="comentario" name="comentario" rows="3" maxlength="1000"></textarea>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Máximo 1000 caracteres</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveInformeBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Informe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Informe -->
<div class="modal fade" id="editInformeModal" tabindex="-1" aria-labelledby="editInformeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInformeModalLabel">
                    <i class="fas fa-id-card me-2"></i>Editar Informe
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editInformeForm" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_informe_id" name="informe_id">
                <div class="modal-body">
                    <!-- Contenedor de errores -->
                    <div id="editInformeErrorContainer" class="alert alert-danger d-none" role="alert">
                        <strong><i class="fas fa-exclamation-triangle me-2"></i>Errores de validación:</strong>
                        <ul id="editInformeErrorList" class="mb-0 mt-2"></ul>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_anio" class="form-label">Año *</label>
                                <input type="text" class="form-control" id="edit_anio_display" readonly>
                                <input type="hidden" id="edit_anio" name="anio">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_mes" class="form-label">Mes *</label>
                                <input type="text" class="form-control" id="edit_mes_display" readonly>
                                <input type="hidden" id="edit_mes" name="mes">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_grupo_id" class="form-label">Grupo *</label>
                                <input type="text" class="form-control" id="edit_grupo_display" readonly>
                                <input type="hidden" id="edit_grupo_id" name="grupo_id">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_user_id" class="form-label">Publicador *</label>
                                <input type="text" class="form-control" id="edit_user_display" readonly>
                                <input type="hidden" id="edit_user_id" name="user_id">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_participa" class="form-label">Participación *</label>
                                <select class="form-select" id="edit_participa" name="participa" required>
                                    <option value="">Seleccionar participación...</option>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_servicio_id" class="form-label">Servicio *</label>
                                <select class="form-select" id="edit_servicio_id" name="servicio_id" required>
                                    <option value="">Seleccionar servicio...</option>
                                    @foreach($servicios as $servicio)
                                        <option value="{{ $servicio->id }}">{{ $servicio->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_cantidad_estudios" class="form-label">Cantidad de Estudios</label>
                                <input type="number" class="form-control" id="edit_cantidad_estudios" name="cantidad_estudios" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_horas" class="form-label">Horas de Servicio</label>
                                <input type="number" class="form-control" id="edit_horas" name="horas" min="0" step="0.5">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="edit_comentario" class="form-label">Comentario</label>
                                <textarea class="form-control" id="edit_comentario" name="comentario" rows="3" maxlength="1000"></textarea>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Máximo 1000 caracteres</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="edit_nota" class="form-label">Nota</label>
                                <textarea class="form-control" id="edit_nota" name="nota" rows="3" maxlength="1000"></textarea>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Máximo 1000 caracteres</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="updateInformeBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Informe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Ver Informe -->
<div class="modal fade" id="viewInformeModal" tabindex="-1" aria-labelledby="viewInformeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewInformeModalLabel">
                    <i class="fas fa-eye me-2"></i>Detalles del Informe
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Año:</label>
                            <p class="form-control-plaintext" id="view_anio">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mes:</label>
                            <p class="form-control-plaintext" id="view_mes">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Grupo:</label>
                            <p class="form-control-plaintext" id="view_grupo">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Usuario:</label>
                            <p class="form-control-plaintext" id="view_usuario">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Servicio:</label>
                            <p class="form-control-plaintext" id="view_servicio">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Participación:</label>
                            <p class="form-control-plaintext" id="view_participa">-</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cantidad de Estudios:</label>
                            <p class="form-control-plaintext" id="view_cantidad_estudios">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Horas de Servicio:</label>
                            <p class="form-control-plaintext" id="view_horas">-</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Comentario:</label>
                            <p class="form-control-plaintext" id="view_comentario">-</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nota:</label>
                            <p class="form-control-plaintext" id="view_nota">-</p>
                        </div>
                    </div>
                </div>

                @if(Auth::user()->isAdmin() || Auth::user()->isSupervisor())
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Congregación:</label>
                            <p class="form-control-plaintext" id="view_congregacion">-</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Confirmar Eliminación -->
<div class="modal fade" id="deleteInformeModal" tabindex="-1" aria-labelledby="deleteInformeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteInformeModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el informe de <strong id="deleteInformeName"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteInformeBtn">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    Eliminar Informe
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Informes -->
<div class="modal fade" id="verInformesModal" tabindex="-1" aria-labelledby="verInformesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verInformesModalLabel">
                    <i class="fas fa-eye me-2"></i>Ver Informes por Grupo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filtros -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="periodoFilterModal" class="form-label">Periodo:</label>
                        <select class="form-select" id="periodoFilterModal">
                            <option value="">Seleccione un periodo</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="grupoFilterModal" class="form-label">Grupo:</label>
                        <select class="form-select" id="grupoFilterModal">
                            <option value="">Seleccione un grupo</option>
                            @foreach($grupos as $grupo)
                                <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Tabla de informes -->
                <div id="informesGrupoContainer" class="d-none">
                    <div class="table-responsive">
                        <table id="informesGrupoTable" class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nombre</th>
                                    <th width="10%" class="text-center">Participa</th>
                                    <th width="15%">Servicio</th>
                                    <th width="10%" class="text-center">Estudios</th>
                                    <th width="10%" class="text-center">Horas</th>
                                    <th width="20%">Comentario</th>
                                </tr>
                            </thead>
                            <tbody id="informesGrupoTableBody">
                                <!-- Los datos se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mensaje cuando no hay datos -->
                <div id="noDataMessage" class="alert alert-info d-none" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    Seleccione un periodo y grupo para ver los informes.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Informe Congregación -->
<div class="modal fade" id="informeCongregacionModal" tabindex="-1" aria-labelledby="informeCongregacionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="informeCongregacionModalLabel">
                    <i class="fas fa-chart-bar me-2"></i>Informe de Congregación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filtro de Periodo -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="periodoFilterCongregacion" class="form-label fw-bold">Periodo:</label>
                        <select class="form-select" id="periodoFilterCongregacion">
                            <option value="">Seleccione un periodo</option>
                        </select>
                    </div>
                </div>

                <!-- Indicador de carga -->
                <div id="loadingCongregacionStats" class="text-center d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando estadísticas...</p>
                </div>

                <!-- Contenedor de estadísticas -->
                <div id="congregacionStatsContainer" class="d-none">
                    <div class="row g-3">
                        <!-- Usuarios Activos -->
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-users me-2"></i>Usuarios Activos
                                    </h6>
                                    <h3 class="mb-0" id="stat_usuarios_activos">-</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Usuarios Inactivos -->
                        <div class="col-md-6">
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <h6 class="card-title text-secondary">
                                        <i class="fas fa-users me-2"></i>Usuarios Inactivos
                                    </h6>
                                    <h3 class="mb-0" id="stat_usuarios_inactivos">-</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Publicadores -->
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h6 class="card-title text-info">
                                        <i class="fas fa-user-tie me-2"></i>Publicadores
                                    </h6>
                                    <h3 class="mb-0" id="stat_publicadores">-</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Estudios de Publicadores -->
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h6 class="card-title text-info">
                                        <i class="fas fa-book-reader me-2"></i>Estudios de Publicadores
                                    </h6>
                                    <h3 class="mb-0" id="stat_estudios_publicadores">-</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Precursores Auxiliares -->
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h6 class="card-title text-warning">
                                        <i class="fas fa-user-tie me-2"></i>Precursores Auxiliares
                                    </h6>
                                    <h3 class="mb-0" id="stat_precursores_auxiliares">-</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Estudios de Precursores Auxiliares -->
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h6 class="card-title text-warning">
                                        <i class="fas fa-book-reader me-2"></i>Estudios de Prec. Auxiliares
                                    </h6>
                                    <h3 class="mb-0" id="stat_estudios_precursores_auxiliares">-</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Precursores Regulares -->
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h6 class="card-title text-success">
                                        <i class="fas fa-user-tie me-2"></i>Precursores Regulares
                                    </h6>
                                    <h3 class="mb-0" id="stat_precursores_regulares">-</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Estudios de Precursores Regulares -->
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h6 class="card-title text-success">
                                        <i class="fas fa-book-reader me-2"></i>Estudios de Prec. Regulares
                                    </h6>
                                    <h3 class="mb-0" id="stat_estudios_precursores_regulares">-</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje cuando no hay datos -->
                <div id="noDataCongregacionMessage" class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    Seleccione un periodo para ver las estadísticas.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registro por Publicador -->
<div class="modal fade" id="registroPublicadorModal" tabindex="-1" aria-labelledby="registroPublicadorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registroPublicadorModalLabel">
                    <i class="fas fa-clipboard-list me-2"></i>Registro de Publicador de la Congregación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filtros -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="registroAnioFilter" class="form-label fw-bold">Año de servicio:</label>
                        <select class="form-select" id="registroAnioFilter">
                            <option value="">Seleccione un año</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="registroGrupoFilter" class="form-label fw-bold">Grupo:</label>
                        <select class="form-select" id="registroGrupoFilter">
                            <option value="">Seleccione un grupo</option>
                            @foreach($grupos as $grupo)
                                <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="registroPublicadorFilter" class="form-label fw-bold">Publicador:</label>
                        <select class="form-select" id="registroPublicadorFilter">
                            <option value="">Seleccione un publicador</option>
                        </select>
                    </div>
                </div>

                <!-- Información del publicador -->
                <div id="registroPublicadorInfo" class="d-none mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Nombre:</strong> <span id="info_nombre">-</span></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Fecha de nacimiento:</strong> <span id="info_fecha_nacimiento">-</span></p>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="info_hombre" disabled>
                                        <label class="form-check-label" for="info_hombre">Hombre</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="info_mujer" disabled>
                                        <label class="form-check-label" for="info_mujer">Mujer</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Fecha de bautismo:</strong> <span id="info_fecha_bautismo">-</span></p>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="info_otras_ovejas" disabled>
                                        <label class="form-check-label" for="info_otras_ovejas">Otras ovejas</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="info_ungido" disabled>
                                        <label class="form-check-label" for="info_ungido">Ungido</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="info_anciano" disabled>
                                        <label class="form-check-label" for="info_anciano">Anciano</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="info_siervo" disabled>
                                        <label class="form-check-label" for="info_siervo">Siervo ministerial</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="info_precursor_regular" disabled>
                                        <label class="form-check-label" for="info_precursor_regular">Precursor regular</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="info_precursor_especial" disabled>
                                        <label class="form-check-label" for="info_precursor_especial">Precursor especial</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="info_misionero" disabled>
                                        <label class="form-check-label" for="info_misionero">Misionero que sirve en el campo</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Indicador de carga -->
                <div id="loadingRegistroPublicador" class="text-center d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando registro...</p>
                </div>

                <!-- Tabs de registro -->
                <div id="registroPublicadorContainer" class="d-none">
                    <ul class="nav nav-tabs" id="registroPublicadorTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-anio-actual" data-bs-toggle="tab" data-bs-target="#tabpanel-anio-actual" type="button" role="tab" aria-controls="tabpanel-anio-actual" aria-selected="true">
                                <span id="tab-anio-actual-text">Año Actual</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-anio-anterior" data-bs-toggle="tab" data-bs-target="#tabpanel-anio-anterior" type="button" role="tab" aria-controls="tabpanel-anio-anterior" aria-selected="false">
                                <span id="tab-anio-anterior-text">Año Anterior</span>
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="registroPublicadorTabsContent">
                        <!-- Tab Año Actual -->
                        <div class="tab-pane fade show active" id="tabpanel-anio-actual" role="tabpanel" aria-labelledby="tab-anio-actual">
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-sm" id="registroPublicadorTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 100px;">Año de servicio</th>
                                            <th style="width: 80px;" class="text-center">Participación en el ministerio</th>
                                            <th style="width: 80px;" class="text-center">Cursos bíblicos</th>
                                            <th style="width: 80px;" class="text-center">Precursor auxiliar</th>
                                            <th style="width: 100px;" class="text-center">Horas<br><small>(Si es precursor o misionero que sirve en el campo)</small></th>
                                            <th>Notas</th>
                                        </tr>
                                    </thead>
                                    <tbody id="registroPublicadorTableBody">
                                        <!-- Los datos se cargarán dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Año Anterior -->
                        <div class="tab-pane fade" id="tabpanel-anio-anterior" role="tabpanel" aria-labelledby="tab-anio-anterior">
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-sm" id="registroPublicadorTableAnterior">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 100px;">Año de servicio</th>
                                            <th style="width: 80px;" class="text-center">Participación en el ministerio</th>
                                            <th style="width: 80px;" class="text-center">Cursos bíblicos</th>
                                            <th style="width: 80px;" class="text-center">Precursor auxiliar</th>
                                            <th style="width: 100px;" class="text-center">Horas<br><small>(Si es precursor o misionero que sirve en el campo)</small></th>
                                            <th>Notas</th>
                                        </tr>
                                    </thead>
                                    <tbody id="registroPublicadorTableAnteriorBody">
                                        <!-- Los datos se cargarán dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje cuando no hay datos -->
                <div id="noDataRegistroMessage" class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    Seleccione un año, grupo y publicador para ver el registro.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Configuración para informes-index.js
window.informesIndexConfig = {
    // Rutas
    storeRoute: '{{ route("informes.store") }}',
    showRoute: '{{ route("informes.show", ":id") }}',
    editRoute: '{{ route("informes.edit", ":id") }}',
    updateRoute: '{{ route("informes.update", ":id") }}',
    destroyRoute: '{{ route("informes.destroy", ":id") }}',
    usuariosPorGrupoRoute: '{{ route("informes.usuarios-por-grupo") }}',
    informeCongregacionRoute: '{{ route("informes.informe-congregacion") }}',
    registroPublicadorRoute: '{{ route("informes.registro-publicador") }}',
    aniosRegistroRoute: '{{ route("informes.anios-registro") }}',
    informesPorGrupoRoute: '{{ route("informes.informes-por-grupo") }}',
    periodosRoute: '{{ route("informes.periodos") }}',
    
    // Configuración de permisos y UI
    canModify: @json(Auth::user()->canModify() && !Auth::user()->isSubsecretary() && !Auth::user()->isSuborganizer()),
    isAdmin: @json(Auth::user()->isAdmin()),
    isSupervisor: @json(Auth::user()->isSupervisor()),
    
    // Configuración de DataTables
    datatablesColumnDefs: [
        @if(Auth::user()->isAdmin() || Auth::user()->isSupervisor())
            // Para administradores/supervisores (con columna Congregación)
            { responsivePriority: 1, targets: [1, 2, 3] }, // Año, Mes, Usuario
            { responsivePriority: 2, targets: [0, 4, 5, 6, 7] }, // ID, Grupo, Servicio, Participa, Congregación
            { orderable: false, targets: [8] } // Deshabilitar ordenamiento en columna Acciones
        @else
            // Para otros usuarios (sin columna Congregación)
            { responsivePriority: 1, targets: [1, 2, 3] }, // Año, Mes, Usuario
            { responsivePriority: 2, targets: [0, 4, 5, 6] }, // ID, Grupo, Servicio, Participa
            { orderable: false, targets: [7] } // Deshabilitar ordenamiento en columna Acciones
        @endif
    ],
    
    // Datos para lookups
    usuarios: @json($usuarios->map(function($u) { return ['id' => $u->id, 'name' => $u->name]; })),
    grupos: @json($grupos->map(function($g) { return ['id' => $g->id, 'nombre' => $g->nombre]; })),
    servicios: @json($servicios->map(function($s) { return ['id' => $s->id, 'nombre' => $s->nombre]; })),
    @if(isset($congregaciones))
    congregaciones: @json($congregaciones->map(function($c) { return ['id' => $c->id, 'nombre' => $c->nombre]; }))
    @else
    congregaciones: []
    @endif
};
</script>
<script src="{{ asset('js/informes-index.js') }}"></script>
@endsection