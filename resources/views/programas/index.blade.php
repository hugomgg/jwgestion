@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/programas-index.css') }}">
<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
@if(Auth::user()->perfil == 3)
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar me-2"></i>Gestión de Programas
                            </h5>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                <!-- Select2 para filtrar por Año -->
                                <div class="d-flex align-items-center gap-2">
                                    <label for="filtro_anio" class="form-label mb-0 me-2">Año:</label>
                                    <select class="form-select" id="filtro_anio" style="width: 120px;">
                                        <option value="">Todos</option>
                                    </select>
                                </div>

                                <!-- Dropdown para filtrar por Mes -->
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label mb-0 me-2">Mes:</label>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="mesDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" disabled style="width: 180px;">
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

                                <!-- Botón Exportar PDF -->
                                <a href="#" class="btn btn-success disabled" id="exportPdfBtn" target="_blank" disabled>
                                    <i class="fas fa-file-pdf me-2"></i>Exportar PDF
                                </a>

                                <!-- Botón Exportar XLS -->
                                <a href="#" class="btn btn-primary disabled" id="exportXlsBtn" target="_blank" disabled>
                                    <i class="fas fa-file-excel me-2"></i>Exportar XLS
                                </a>

                                <!-- Botón Exportar Asignaciones -->
                                <a href="#" class="btn btn-secondary disabled" id="exportAsignacionesBtn" target="_blank" disabled>
                                    <i class="fas fa-file-export me-2"></i>Exportar Asignaciones
                                </a>

                                <!-- Botón Nuevo Programa -->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramaModal">
                                    <i class="fas fa-plus me-2"></i>Nuevo Programa
                                </button>
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
                                    <th style="width: 20%;">Orador Inicial</th>
                                    <th style="width: 20%;">Presidente</th>
                                    <th style="width: 10%;">Canción Inicial</th>
                                    <th style="width: 10%;">Canción Intermedia</th>
                                    <th style="width: 10%;">Canción Final</th>
                                    <th style="width: 20%;">Orador Final</th>
                                    <th style="width: 10%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($programas as $programa)
                                <tr>
                                    <td data-order="{{ $programa->fecha }}">{{ \Carbon\Carbon::parse($programa->fecha)->format('d/m/Y') }}</td>
                                    <td>{{ $programa->nombre_orador_inicial ?? '-' }}</td>
                                    <td>{{ $programa->nombre_presidencia ?? '-' }}</td>
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
@push('scripts')
<script src="{{ asset('js/select2.min.js') }}"></script>
<script src="{{ asset('js/programas-index.js') }}"></script>
<script>
$(document).ready(function() {
    // Inicializar Select2 para coordinadores
    @if(Auth::user()->perfil == 3)
    initializeSelect2ForCoordinators();
    handleModalEventsForSelect2();
    @endif
});
</script>
@endpush
@endsection