@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/programas-index.css') }}">
<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
@if($currentUser->isCoordinator() || $currentUser->isOrganizer() ||  $currentUser->isSuborganizer())
@php
    // Calcular el lunes y domingo de la semana actual (solo fechas, sin hora)
    $lunesSemanaActual = \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->startOfDay();
    $domingoSemanaActual = \Carbon\Carbon::now()->endOfWeek(\Carbon\Carbon::SUNDAY)->endOfDay();
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar me-2"></i>Gestión de Programas
                            </h5>
                        </div>
                        <div class="col-md-5">
                            <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                <!-- Botón Generar Documentos (Collapse) -->
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#generarDocumentosCollapse" aria-expanded="false" aria-controls="generarDocumentosCollapse">
                                    <i class="fas fa-download me-2"></i>Exportar Documentos
                                </button>

                                <!-- Select2 para filtrar por Año -->
                                <select class="form-select" id="filtro_anio" style="width: 120px;">
                                    <option value="">Todos</option>
                                </select>

                                <!-- Botón Nuevo Programa -->
                                @if($currentUser->isCoordinator() || $currentUser->isOrganizer())
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramaModal">
                                    <i class="fas fa-plus me-2"></i>Nuevo Programa
                                </button>
                                @endif
                            </div>
                            
                            <!-- Collapse para Generar Documentos -->
                            <div class="collapse mt-3" id="generarDocumentosCollapse">
                                <div class="card card-body">
                                    <h6 class="mb-3"><i class="fas fa-download me-2"></i>Exportar Documentos</h6>
                                    <div class="row g-3 align-items-end">
                                        <!-- Dropdown para filtrar por Mes -->
                                        <div class="col-md-6">
                                            <label class="form-label">Mes</label>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" id="mesDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                                                    Seleccionar meses
                                                </button>
                                                <ul class="dropdown-menu p-3" aria-labelledby="mesDropdownBtn" id="mesDropdownMenu" style="min-width: 200px;">
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li class="px-2">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="seleccionarTodosMeses">Seleccionar Todos</button>
                                                    </li>
                                                    <li class="px-2 mt-2">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="limpiarMeses">Limpiar Selección</button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3 align-items-end">
                                        <!-- Botones de Exportación -->
                                        <div class="col-md-12">
                                            <label class="form-label">Acciones</label>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <!-- Botón Exportar PDF -->
                                                <button type="button" class="btn btn-outline-danger" id="exportPdfBtn" disabled>
                                                    <i class="fas fa-file-pdf me-2"></i>Programa PDF
                                                </button>
                                                <!-- Botón Exportar Programa XLS -->
                                                <button type="button" class="btn btn-outline-success" id="exportProgramaXlsBtn" disabled>
                                                    <i class="fas fa-file-excel me-2"></i>Programa XLS
                                                </button>
                                                <!-- Botón Exportar Asignaciones -->
                                                <button type="button" class="btn btn-outline-danger" id="exportAsignacionesBtn" disabled>
                                                    <i class="fas fa-file-pdf me-2"></i>Asignaciones SMM
                                                </button>
                                                <!-- Botón Exportar XLS -->
                                                <button type="button" class="btn btn-outline-success" id="exportXlsBtn" disabled>
                                                    <i class="fas fa-file-excel me-2"></i>Resumen XLS
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Contenedor para alertas -->
                    <div id="alert-container"></div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="programasTable">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 10%;">Fecha</th>
                                    <th style="width: 25%;">Presidente</th>
                                    <th style="width: 20%;">Orador Inicial</th>
                                    <th style="width: 5%;">Canción Inicial</th>
                                    <th style="width: 5%;">Canción Intermedia</th>
                                    <th style="width: 5%;">Canción Final</th>
                                    <th style="width: 20%;">Orador Final</th>
                                    <th style="width: 10%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($programas as $programa)
                                @php
                                    // Verificar si la fecha del programa está en la semana actual
                                    $esSemanaActual = false;
                                    if (isset($programa->fecha) && $programa->fecha) {
                                        $fechaPrograma = \Carbon\Carbon::parse($programa->fecha)->startOfDay();
                                        $esSemanaActual = $fechaPrograma->between($lunesSemanaActual, $domingoSemanaActual);
                                    }
                                @endphp
                                <tr class="@if($esSemanaActual) semana-actual-row @endif">
                                    <td data-order="{{ $programa->fecha }}">{{ \Carbon\Carbon::parse($programa->fecha)->format('d/m/Y') }}</td>
                                    <td>{{ $programa->nombre_presidencia ?? '-' }}</td>
                                    <td>{{ $programa->nombre_orador_inicial ?? '-' }}</td>
                                    <td>{{ $programa->numero_cancion_pre ?? '-' }}</td>
                                    <td>{{ $programa->numero_cancion_en ?? '-' }}</td>
                                    <td>{{ $programa->numero_cancion_post ?? '-' }}</td>
                                    <td>{{ $programa->nombre_orador_final ?? '-' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('programas.show', $programa->id) }}" class="btn btn-sm btn-info"
                                               data-bs-toggle="tooltip"
                                               title="Ver programa">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($currentUser->isCoordinator() || $currentUser->isOrganizer())
                                            <a href="{{ route('programas.edit', $programa->id) }}" class="btn btn-sm btn-warning"
                                               data-bs-toggle="tooltip"
                                               title="Editar programa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-programa"
                                                    data-id="{{ $programa->id }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmDeleteModal"
                                                    data-bs-toggle="tooltip"
                                                    title="Eliminar programa">
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

<!-- Modal Agregar Programa -->
<div class="modal fade" id="addProgramaModal" tabindex="-1" aria-labelledby="addProgramaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProgramaModalLabel">
                    <i class="fas fa-calendar me-2"></i>Agregar Programa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addProgramaForm">
                @csrf
                <div class="modal-body">
                    <!-- Contenedor para alertas -->
                    <div id="add-alert-container"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_fecha" class="form-label">Fecha *</label>
                                <input type="date" class="form-control" id="add_fecha" name="fecha" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 d-none">
                                <label for="add_estado" class="form-label">Estado *</label>
                                <select class="form-select" id="add_estado" name="estado" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="add_cancion_pre" class="form-label">Canción Inicial</label>
                                <select class="form-select" id="add_cancion_pre" name="cancion_pre">
                                    <option value="">Seleccionar...</option>
                                    @foreach($canciones as $cancion)
                                        <option value="{{ $cancion->id }}">{{ $cancion->numero ? $cancion->numero . ' - ' : '' }}{{ $cancion->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="add_cancion_en" class="form-label">Canción Intermedia</label>
                                <select class="form-select" id="add_cancion_en" name="cancion_en">
                                    <option value="">Seleccionar...</option>
                                    @foreach($canciones as $cancion)
                                        <option value="{{ $cancion->id }}">{{ $cancion->numero ? $cancion->numero . ' - ' : '' }}{{ $cancion->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="add_cancion_post" class="form-label">Canción Final</label>
                                <select class="form-select" id="add_cancion_post" name="cancion_post">
                                    <option value="">Seleccionar...</option>
                                    @foreach($canciones as $cancion)
                                        <option value="{{ $cancion->id }}">{{ $cancion->numero ? $cancion->numero . ' - ' : '' }}{{ $cancion->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="addProgramaBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Crear Programa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar Programa -->
@if($currentUser->isCoordinator() || $currentUser->isOrganizer())
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
                <p class="mb-0">¿Está seguro de que desea eliminar este Programa?</p>
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
@endif
@endif
@push('scripts')
<script src="{{ asset('js/select2.min.js') }}"></script>
<script src="{{ asset('js/programas-index.js') }}"></script>
<script>
$(document).ready(function() {
    // Inicializar Select2
    initializeSelect2ForCoordinators();
    handleModalEventsForSelect2();

    // Definir rutas para exportación
    window.exportRoutes = {
        pdf: `{{ route('programas.export.pdf') }}`,
        programaXls: `{{ route('programas.export.programa-xls') }}`,
        xls: `{{ route('programas.export.xls') }}`,
        asignaciones: `{{ route('programas.export.asignaciones') }}`
    };

    // Pasar el año más reciente desde el controlador
    window.anioMasReciente = {{ $anioMasReciente ?? 'null' }};

    // Indicar si el usuario es coordinador u organizador
    window.isCoordinatorOrOrganizer = {{ ($currentUser->isCoordinator() || $currentUser->isOrganizer()) ? 'true' : 'false' }};
});
</script>
@endpush
@endsection