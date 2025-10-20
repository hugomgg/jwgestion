@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/informes-index.css') }}">
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
                                
                                @if(Auth::user()->canModify() && !Auth::user()->isSubsecretary() && !Auth::user()->isSuborganizer())
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInformeModal">
                                    <i class="fas fa-plus me-2"></i>Agregar Informe
                                </button>
                                @endif
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
                                    <th>ID</th>
                                    <th>Año</th>
                                    <th>Mes</th>
                                    <th>Usuario</th>
                                    <th>Grupo</th>
                                    <th>Servicio</th>
                                    <th>Participa</th>
                                    @if(Auth::user()->isAdmin() || Auth::user()->isSupervisor())
                                        <th>Congregación</th>
                                    @endif
                                    <th>Acciones</th>
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
                                    <td>{{ $informe->usuario_nombre }}</td>
                                    <td>
                                        <span class="badge bg-dark">{{ $informe->grupo_nombre }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">{{ $informe->servicio_nombre }}</span>
                                    </td>
                                    <td>
                                        @if($informe->participa)
                                            <span class="badge bg-success">Sí</span>
                                        @else
                                            <span class="badge bg-danger">No</span>
                                        @endif
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
                                <label for="anio" class="form-label">Año </label>
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
                                <label for="mes" class="form-label">Mes </label>
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
                                <label for="grupo_id" class="form-label">Grupo </label>
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
                                <label for="user_id" class="form-label">Publicador </label>
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
                                <label for="servicio_id" class="form-label">Servicio </label>
                                <select class="form-select" id="servicio_id" name="servicio_id" required>
                                    <option value="">Seleccionar servicio...</option>
                                    @foreach($servicios as $servicio)
                                        <option value="{{ $servicio->id }}">{{ $servicio->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="participa" class="form-label">Participación </label>
                                <select class="form-select" id="participa" name="participa" required>
                                    <option value="">Seleccionar participación...</option>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
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
                                <label for="edit_anio" class="form-label">Año </label>
                                <input type="text" class="form-control" id="edit_anio_display" readonly>
                                <input type="hidden" id="edit_anio" name="anio">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_mes" class="form-label">Mes </label>
                                <input type="text" class="form-control" id="edit_mes_display" readonly>
                                <input type="hidden" id="edit_mes" name="mes">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_grupo_id" class="form-label">Grupo </label>
                                <input type="text" class="form-control" id="edit_grupo_display" readonly>
                                <input type="hidden" id="edit_grupo_id" name="grupo_id">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_user_id" class="form-label">Publicador </label>
                                <input type="text" class="form-control" id="edit_user_display" readonly>
                                <input type="hidden" id="edit_user_id" name="user_id">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_servicio_id" class="form-label">Servicio </label>
                                <select class="form-select" id="edit_servicio_id" name="servicio_id" required>
                                    <option value="">Seleccionar servicio...</option>
                                    @foreach($servicios as $servicio)
                                        <option value="{{ $servicio->id }}">{{ $servicio->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_participa" class="form-label">Participación </label>
                                <select class="form-select" id="edit_participa" name="participa" required>
                                    <option value="">Seleccionar participación...</option>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
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