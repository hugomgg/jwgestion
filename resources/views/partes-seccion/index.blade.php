@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h5 class="mb-0">
                                <i class="fas fa-list-ol me-2"></i>Gestión de Partes de Sección
                            </h5>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                <!-- Filtros -->
                                <div class="d-flex align-items-center">
                                    <label for="seccionFilter" class="form-label me-2 mb-0">Sección:</label>
                                    <select class="form-select" id="seccionFilter" style="width: auto;">
                                        <option value="">Todas</option>
                                        @foreach($secciones as $seccion)
                                            <option value="{{ $seccion->nombre }}">{{ $seccion->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <label for="asignacionFilter" class="form-label me-2 mb-0">Asignación:</label>
                                    <select class="form-select" id="asignacionFilter" style="width: auto;">
                                        <option value="">Todas</option>
                                        @foreach($asignaciones as $asignacion)
                                            <option value="{{ $asignacion->nombre }}">{{ $asignacion->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <label for="estadoFilter" class="form-label me-2 mb-0">Estado:</label>
                                    <select class="form-select" id="estadoFilter" style="width: auto;">
                                        <option value="">Todos</option>
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                                
                                @if(Auth::user()->isAdmin())
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addParteModal">
                                    <i class="fas fa-plus me-2"></i>Agregar Parte
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Mensajes de estado -->
                    <div id="alert-container"></div>
                    <div class="table-responsive">
                        <table id="partesTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Abreviación</th>
                                    <th>Orden</th>
                                    <th>Tiempo (min)</th>
                                    <th>Tipo</th>
                                    <th>Sección</th>
                                    <th>Asignación</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($partes as $parte)
                                <tr data-parte-id="{{ $parte->id }}">
                                    <td>{{ $parte->id }}</td>
                                    <td>{{ $parte->nombre }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $parte->abreviacion }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $parte->orden }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $parte->tiempo }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $tipoTexto = $tipos[$parte->tipo] ?? 'Desconocido';
                                            $tipoClass = $parte->tipo == 1 ? 'bg-success' : ($parte->tipo == 2 ? 'bg-warning text-dark' : 'bg-info');
                                        @endphp
                                        <span class="badge {{ $tipoClass }}">{{ $tipoTexto }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark">{{ $parte->seccion_nombre }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">{{ $parte->asignacion_nombre }}</span>
                                    </td>
                                    <td>
                                        @if($parte->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if(Auth::user()->isAdmin())
                                            <button type="button" class="btn btn-sm btn-warning"
                                                    onclick="editParte({{ $parte->id }})"
                                                    data-bs-toggle="tooltip"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="deleteParte({{ $parte->id }}, '{{ $parte->nombre }}')"
                                                    data-bs-toggle="tooltip"
                                                    title="Eliminar">
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

@if(Auth::user()->isAdmin())
<!-- Modal para agregar parte -->
<div class="modal fade" id="addParteModal" tabindex="-1" aria-labelledby="addParteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addParteModalLabel">
                    <i class="fas fa-list-ol me-2"></i>Agregar Nueva Parte de Sección
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addParteForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="abreviacion" class="form-label">Abreviación *</label>
                                <input type="text" class="form-control" id="abreviacion" name="abreviacion" maxlength="10" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="orden" class="form-label">Orden *</label>
                                <input type="number" class="form-control" id="orden" name="orden" min="1" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tiempo" class="form-label">Tiempo (minutos) *</label>
                                <input type="number" class="form-control" id="tiempo" name="tiempo" min="1" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="seccion_id" class="form-label">Sección *</label>
                                <select class="form-select" id="seccion_id" name="seccion_id" required>
                                    <option value="">Seleccionar sección</option>
                                    @foreach($secciones as $seccion)
                                        <option value="{{ $seccion->id }}">{{ $seccion->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asignacion_id" class="form-label">Asignación *</label>
                                <select class="form-select" id="asignacion_id" name="asignacion_id" required>
                                    <option value="">Seleccionar asignación</option>
                                    @foreach($asignaciones as $asignacion)
                                        <option value="{{ $asignacion->id }}">{{ $asignacion->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo *</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    @foreach($tipos as $valor => $nombre)
                                        <option value="{{ $valor }}" {{ $valor == 1 ? 'selected' : '' }}>{{ $nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado *</label>
                                <select class="form-select" id="estado" name="estado" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        Guardar Parte Sección
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar parte -->
<div class="modal fade" id="editParteModal" tabindex="-1" aria-labelledby="editParteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editParteModalLabel">
                    <i class="fas fa-list-ol me-2"></i>Editar Parte de Sección
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editParteForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_parte_id" name="parte_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_abreviacion" class="form-label">Abreviación *</label>
                                <input type="text" class="form-control" id="edit_abreviacion" name="abreviacion" maxlength="10" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_orden" class="form-label">Orden *</label>
                                <input type="number" class="form-control" id="edit_orden" name="orden" min="1" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_tiempo" class="form-label">Tiempo (minutos) *</label>
                                <input type="number" class="form-control" id="edit_tiempo" name="tiempo" min="1" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_seccion_id" class="form-label">Sección *</label>
                                <select class="form-select" id="edit_seccion_id" name="seccion_id" required>
                                    <option value="">Seleccionar sección</option>
                                    @foreach($secciones as $seccion)
                                        <option value="{{ $seccion->id }}">{{ $seccion->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_asignacion_id" class="form-label">Asignación *</label>
                                <select class="form-select" id="edit_asignacion_id" name="asignacion_id" required>
                                    <option value="">Seleccionar asignación</option>
                                    @foreach($asignaciones as $asignacion)
                                        <option value="{{ $asignacion->id }}">{{ $asignacion->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_tipo" class="form-label">Tipo *</label>
                                <select class="form-select" id="edit_tipo" name="tipo" required>
                                    @foreach($tipos as $valor => $nombre)
                                        <option value="{{ $valor }}">{{ $nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_estado" class="form-label">Estado *</label>
                                <select class="form-select" id="edit_estado" name="estado" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información de auditoría -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Información de Auditoría</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Creado por:</strong> <span id="edit_creado_por"></span></p>
                                            <p class="mb-0"><strong>Fecha creación:</strong> <span id="edit_creado_fecha"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Modificado por:</strong> <span id="edit_modificado_por"></span></p>
                                            <p class="mb-0"><strong>Fecha modificación:</strong> <span id="edit_modificado_fecha"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        Actualizar Parte Sección
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Inicializar DataTable
    const table = $('#partesTable').DataTable({
        language: {
            "decimal": "",
            "emptyTable": "No hay datos disponibles en la tabla",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron registros coincidentes",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        responsive: true,
        pageLength: 10,
        columnDefs: [
            { targets: [9], orderable: false } // Columna de acciones no ordenable
        ]
    });

    // Función de filtro personalizada para estado
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'partesTable') {
            return true;
        }
        
        const estadoFilter = $('#estadoFilter').val();
        
        if (estadoFilter === '') {
            return true; // Mostrar todos si no hay filtro
        }
        
        // Obtener el contenido de la columna de estado (columna 8, índice 8)
        const estadoCell = data[8];
        
        if (estadoFilter === '1') {
            // Buscar "Activo" en el contenido de la celda
            return estadoCell.includes('Activo');
        } else if (estadoFilter === '0') {
            // Buscar "Inactivo" en el contenido de la celda
            return estadoCell.includes('Inactivo');
        }
        
        return true;
    });

    // Filtros
    $('#seccionFilter, #asignacionFilter, #estadoFilter').on('change', function() {
        const seccionFilter = $('#seccionFilter').val();
        const asignacionFilter = $('#asignacionFilter').val();

        // Aplicar filtros normales (sección y asignación)
        table.column(6).search(seccionFilter); // Sección
        table.column(7).search(asignacionFilter); // Asignación
        
        // El filtro de estado se maneja con la función personalizada
        table.draw();
    });

    // Agregar parte
    $('#addParteForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '{{ route("partes-seccion.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#addParteModal').modal('hide');
                    $('#addParteForm')[0].reset();
                    showAlert('success', response.message);
                    location.reload();
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                clearFormErrors('#addParteForm');
                
                if (errors) {
                    Object.keys(errors).forEach(function(field) {
                        showFieldError('#addParteForm', field, errors[field][0]);
                    });
                } else {
                    showAlert('danger', xhr.responseJSON.message || 'Error al guardar la parte');
                }
            }
        });
    });

    // Editar parte
    $('#editParteForm').on('submit', function(e) {
        e.preventDefault();
        
        const parteId = $('#edit_parte_id').val();
        const formData = new FormData(this);
        
        $.ajax({
            url: `/partes-seccion/${parteId}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#editParteModal').modal('hide');
                    showAlert('success', response.message);
                    location.reload();
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                clearFormErrors('#editParteForm');
                
                if (errors) {
                    Object.keys(errors).forEach(function(field) {
                        showFieldError('#editParteForm', field, errors[field][0]);
                    });
                } else {
                    showAlert('danger', xhr.responseJSON.message || 'Error al actualizar la parte');
                }
            }
        });
    });
});

// Función para editar parte
function editParte(id) {
    $.ajax({
        url: `/partes-seccion/${id}/edit`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const parte = response.parte;
                
                $('#edit_parte_id').val(parte.id);
                $('#edit_nombre').val(parte.nombre);
                $('#edit_abreviacion').val(parte.abreviacion);
                $('#edit_orden').val(parte.orden);
                $('#edit_tiempo').val(parte.tiempo);
                $('#edit_seccion_id').val(parte.seccion_id);
                $('#edit_asignacion_id').val(parte.asignacion_id);
                $('#edit_tipo').val(parte.tipo);
                $('#edit_estado').val(parte.estado);
                
                // Información de auditoría
                $('#edit_creado_por').text(parte.creado_por_nombre || 'N/A');
                $('#edit_creado_fecha').text(parte.creado_por_timestamp || 'N/A');
                $('#edit_modificado_por').text(parte.modificado_por_nombre || 'N/A');
                $('#edit_modificado_fecha').text(parte.modificado_por_timestamp || 'N/A');
                
                $('#editParteModal').modal('show');
            }
        },
        error: function(xhr) {
            showAlert('danger', 'Error al cargar los datos de la parte');
        }
    });
}

// Función para eliminar parte
function deleteParte(id, nombre) {
    if (confirm(`¿Está seguro de que desea eliminar la parte "${nombre}"?`)) {
        $.ajax({
            url: `/partes-seccion/${id}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    location.reload();
                }
            },
            error: function(xhr) {
                showAlert('danger', xhr.responseJSON.message || 'Error al eliminar la parte');
            }
        });
    }
}

// Funciones auxiliares
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    $('#alert-container').html(alertHtml);
}

function showFieldError(formSelector, field, message) {
    const fieldElement = $(`${formSelector} [name="${field}"]`);
    fieldElement.addClass('is-invalid');
    fieldElement.siblings('.invalid-feedback').text(message);
}

function clearFormErrors(formSelector) {
    $(`${formSelector} .is-invalid`).removeClass('is-invalid');
    $(`${formSelector} .invalid-feedback`).text('');
}
</script>
@endsection