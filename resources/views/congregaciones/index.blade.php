@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-landmark me-2"></i>Gestión de Congregaciones
                    </h5>
                    <div class="d-flex align-items-center gap-3">
                        <!-- Filtro de Estado -->
                        <div class="d-flex align-items-center">
                            <label for="filtroEstado" class="form-label me-2 mb-0">Filtrar por Estado:</label>
                            <select id="filtroEstado" class="form-select" style="width: auto; min-width: 150px;">
                                <option value="">Todos los estados</option>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        @if(Auth::user()->isAdmin())
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCongregacionModal">
                            <i class="fas fa-plus me-1"></i>Nueva Congregación
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Contenedor para alertas -->
                    <div id="alert-container"></div>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table id="congregacionesTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Dirección</th>
                                    <th>Teléfono</th>
                                    <th>Persona Contacto</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    @if(Auth::user()->isAdmin())
                                    <th>Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($congregaciones as $congregacion)
                                <tr>
                                    <td>{{ $congregacion->id }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $congregacion->nombre }}</span>
                                    </td>
                                    <td>{{ $congregacion->direccion ?? 'Sin dirección' }}</td>
                                    <td>{{ $congregacion->telefono ?? 'Sin teléfono' }}</td>
                                    <td>{{ $congregacion->persona_contacto ?? 'Sin contacto' }}</td>
                                    <td>
                                        <span class="badge {{ $congregacion->estado ? 'bg-success' : 'bg-danger' }}">
                                            {{ $congregacion->estado ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>{{ $congregacion->created_at->format('d/m/Y H:i') }}</td>
                                    @if(Auth::user()->isAdmin())
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-warning edit-congregacion"
                                                    data-id="{{ $congregacion->id }}" data-bs-toggle="tooltip" title="Editar congregación">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-congregacion"
                                                    data-id="{{ $congregacion->id }}" data-name="{{ $congregacion->nombre }}"
                                                    data-bs-toggle="tooltip" title="Eliminar congregación">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    @endif
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
<!-- Modal Crear Congregación -->
<div class="modal fade" id="createCongregacionModal" tabindex="-1" aria-labelledby="createCongregacionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCongregacionModalLabel">
                    <i class="fas fa-plus me-2"></i>Nueva Congregación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createCongregacionForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="create_nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="create_nombre" name="nombre" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="create_telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="create_telefono" name="telefono" maxlength="20">
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Máximo 20 caracteres</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="create_persona_contacto" class="form-label">Persona de Contacto</label>
                                <input type="text" class="form-control" id="create_persona_contacto" name="persona_contacto" maxlength="255">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="create_estado" class="form-label">Estado *</label>
                                <select class="form-select" id="create_estado" name="estado" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="create_direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="create_direccion" name="direccion" rows="3" maxlength="500"></textarea>
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Máximo 500 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="createCongregacionBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Crear Congregación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Congregación -->
<div class="modal fade" id="editCongregacionModal" tabindex="-1" aria-labelledby="editCongregacionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCongregacionModalLabel">
                    <i class="fas fa-landmark me-2"></i>Editar Congregación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCongregacionForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_congregacion_id">
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
                                <label for="edit_telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="edit_telefono" name="telefono" maxlength="20">
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Máximo 20 caracteres</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_persona_contacto" class="form-label">Persona de Contacto</label>
                                <input type="text" class="form-control" id="edit_persona_contacto" name="persona_contacto" maxlength="255">
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
                    <div class="mb-3">
                        <label for="edit_direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="edit_direccion" name="direccion" rows="3" maxlength="500"></textarea>
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Máximo 500 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="updateCongregacionBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Congregación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar Congregación -->
<div class="modal fade" id="deleteCongregacionModal" tabindex="-1" aria-labelledby="deleteCongregacionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCongregacionModalLabel">
                    <i class="fas fa-trash me-2"></i>Eliminar Congregación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ¿Está seguro que desea eliminar la congregación <strong id="deleteCongregacionName"></strong>?
                </div>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteCongregacion">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Inicializar DataTable
    const table = $('#congregacionesTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[1, 'asc']], // Ordenar por nombre
        columnDefs: [
            @if(Auth::user()->isAdmin())
            { orderable: false, targets: [-1] } // Última columna (acciones) no ordenable
            @endif
        ]
    });

    // Inicializar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Función para mostrar alertas
    function showAlert(type, message) {
        const alertContainer = $('#alert-container');
        const alert = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        alertContainer.html(alert);
        
        if (type === 'success') {
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
    }

    // Función para limpiar errores de validación
    function clearValidationErrors(formId) {
        $(`#${formId} .form-control`).removeClass('is-invalid');
        $(`#${formId} .invalid-feedback`).text('');
    }

    // Función para mostrar errores de validación
    function showValidationErrors(formId, errors) {
        clearValidationErrors(formId);
        $.each(errors, function(field, messages) {
            const input = $(`#${formId} [name="${field}"]`);
            input.addClass('is-invalid');
            input.siblings('.invalid-feedback').text(messages[0]);
        });
    }

    // Filtro por estado
    let currentEstadoFilterCongregaciones = null;

    $('#filtroEstado').on('change', function() {
        const selectedEstado = $(this).val();
        
        // Limpiar filtro anterior si existe
        if (currentEstadoFilterCongregaciones !== null) {
            $.fn.dataTable.ext.search.splice($.fn.dataTable.ext.search.indexOf(currentEstadoFilterCongregaciones), 1);
        }
        
        if (selectedEstado === '') {
            currentEstadoFilterCongregaciones = null;
            table.draw();
        } else {
            // Mapear valores numéricos a textos para la búsqueda
            const textoEstado = selectedEstado === '1' ? 'Activo' : 'Inactivo';
            
            // Crear nueva función de filtro
            currentEstadoFilterCongregaciones = function(settings, data, dataIndex) {
                if (settings.nTable !== table.table().node()) {
                    return true;
                }
                const estadoColumn = data[5]; // Columna 5 es el estado
                return estadoColumn.indexOf(textoEstado) !== -1;
            };
            
            // Agregar el nuevo filtro
            $.fn.dataTable.ext.search.push(currentEstadoFilterCongregaciones);
            table.draw();
        }
    });

    @if(Auth::user()->isAdmin())
    // Crear congregación
    $('#createCongregacionForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#createCongregacionBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors('createCongregacionForm');
        
        $.ajax({
            url: '{{ route("congregaciones.store") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#createCongregacionModal').modal('hide');
                    form[0].reset();
                    showAlert('success', response.message);
                    
                    // Recargar la página para mostrar la nueva congregación
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                
                if (xhr.status === 422 && response.errors) {
                    showValidationErrors('createCongregacionForm', response.errors);
                } else {
                    const message = response.message || 'Error al crear la congregación. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Editar congregación
    $('.edit-congregacion').on('click', function() {
        const congregacionId = $(this).data('id');
        
        $.ajax({
            url: `/congregaciones/${congregacionId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const congregacion = response.congregacion;
                    $('#edit_congregacion_id').val(congregacion.id);
                    $('#edit_nombre').val(congregacion.nombre);
                    $('#edit_direccion').val(congregacion.direccion);
                    $('#edit_telefono').val(congregacion.telefono);
                    $('#edit_persona_contacto').val(congregacion.persona_contacto);
                    $('#edit_estado').val(congregacion.estado);
                    $('#editCongregacionModal').modal('show');
                }
            },
            error: function() {
                showAlert('danger', 'Error al cargar los datos de la congregación.');
            }
        });
    });

    // Actualizar congregación
    $('#editCongregacionForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#updateCongregacionBtn');
        const spinner = submitBtn.find('.spinner-border');
        const congregacionId = $('#edit_congregacion_id').val();
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors('editCongregacionForm');
        
        $.ajax({
            url: `/congregaciones/${congregacionId}`,
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editCongregacionModal').modal('hide');
                    showAlert('success', response.message);
                    
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                
                if (xhr.status === 422 && response.errors) {
                    showValidationErrors('editCongregacionForm', response.errors);
                } else {
                    const message = response.message || 'Error al actualizar la congregación. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Eliminar congregación
    let congregacionToDelete = null;
    
    $('.delete-congregacion').on('click', function() {
        congregacionToDelete = $(this).data('id');
        const congregacionName = $(this).data('name');
        $('#deleteCongregacionName').text(congregacionName);
        $('#deleteCongregacionModal').modal('show');
    });

    $('#confirmDeleteCongregacion').on('click', function() {
        const btn = $(this);
        const spinner = btn.find('.spinner-border');
        
        btn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: `/congregaciones/${congregacionToDelete}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteCongregacionModal').modal('hide');
                    showAlert('success', response.message);
                    
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response.message || 'Error al eliminar la congregación. Intente nuevamente.';
                showAlert('danger', message);
                $('#deleteCongregacionModal').modal('hide');
            },
            complete: function() {
                btn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Limpiar formularios cuando se cierren los modales
    $('#createCongregacionModal').on('hidden.bs.modal', function() {
        $('#createCongregacionForm')[0].reset();
        clearValidationErrors('createCongregacionForm');
        $('#createCongregacionBtn .spinner-border').addClass('d-none');
        $('#createCongregacionBtn').prop('disabled', false);
    });

    $('#editCongregacionModal').on('hidden.bs.modal', function() {
        clearValidationErrors('editCongregacionForm');
        $('#updateCongregacionBtn .spinner-border').addClass('d-none');
        $('#updateCongregacionBtn').prop('disabled', false);
    });
    @endif
});
</script>
@endsection