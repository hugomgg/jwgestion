@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Contenedor para alertas -->
            <div id="alert-container"></div>

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h5 class="mb-0">
                                <i class="fas fa-tasks me-2"></i>Gestión de Asignaciones
                            </h5>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-end align-items-center gap-3">
                                <div class="d-flex align-items-center">
                                    <label for="estadoFilter" class="form-label me-2 mb-0">Filtrar por estado:</label>
                                    <select class="form-select" id="estadoFilter" style="width: auto;">
                                        <option value="">Todos los estados</option>
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                                @if(Auth::user()->canModify())
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAsignacionModal">
                                    <i class="fas fa-plus me-2"></i>Agregar Asignación
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="asignacionesTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Abreviación</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Fecha de Creación</th>
                                    @if(Auth::user()->canModify())
                                    <th>Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asignaciones as $asignacion)
                                <tr data-asignacion-id="{{ $asignacion->id }}" data-estado="{{ $asignacion->estado }}">
                                    <td>{{ $asignacion->id }}</td>
                                    <td>{{ $asignacion->nombre }}</td>
                                    <td><span class="badge bg-info">{{ $asignacion->abreviacion }}</span></td>
                                    <td>{{ Str::limit($asignacion->descripcion, 50) }}</td>
                                    <td>
                                        @if($asignacion->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $asignacion->created_at->format('d/m/Y H:i') }}</td>
                                    @if(Auth::user()->canModify())
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-warning edit-asignacion"
                                                    data-asignacion-id="{{ $asignacion->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Editar asignación">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-asignacion"
                                                    data-asignacion-id="{{ $asignacion->id }}"
                                                    data-asignacion-name="{{ $asignacion->nombre }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Eliminar asignación">
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

<!-- Modal para Agregar Asignación -->
<div class="modal fade" id="addAsignacionModal" tabindex="-1" aria-labelledby="addAsignacionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAsignacionModalLabel">
                    <i class="fas fa-tasks me-2"></i>Agregar Nueva Asignación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAsignacionForm">
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
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="abreviacion" class="form-label">Abreviación *</label>
                                <input type="text" class="form-control" id="abreviacion" name="abreviacion" value="A" maxlength="10" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado *</label>
                                <select class="form-select" id="estado" name="estado" required>
                                    <option value="">Seleccionar estado...</option>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción *</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Máximo 500 caracteres</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveAsignacionBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Asignación -->
<div class="modal fade" id="editAsignacionModal" tabindex="-1" aria-labelledby="editAsignacionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAsignacionModalLabel">
                    <i class="fas fa-tasks me-2"></i>Editar Asignación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAsignacionForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_asignacion_id" name="asignacion_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="edit_abreviacion" class="form-label">Abreviación *</label>
                                <input type="text" class="form-control" id="edit_abreviacion" name="abreviacion" maxlength="10" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="edit_estado" class="form-label">Estado *</label>
                                <select class="form-select" id="edit_estado" name="estado" required>
                                    <option value="">Seleccionar estado...</option>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="edit_descripcion" class="form-label">Descripción *</label>
                                <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="4" required></textarea>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Máximo 500 caracteres</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="updateAsignacionBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Confirmar Eliminación -->
<div class="modal fade" id="deleteAsignacionModal" tabindex="-1" aria-labelledby="deleteAsignacionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAsignacionModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar la asignación <strong id="deleteAsignacionName"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    Eliminar Asignación
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var table = $('#asignacionesTable').DataTable({
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        columnDefs: [
            { responsivePriority: 1, targets: [1, 2] },
            { responsivePriority: 2, targets: [0, 3, 4] },
            @if(Auth::user()->canModify())
            { orderable: false, targets: [6] }
            @endif
        ]
    });

    $('#estadoFilter').on('change', function() {
        const selectedEstado = $(this).val();
        
        if (selectedEstado === '') {
            // Mostrar todas las filas
            $('#asignacionesTable tbody tr').show();
        } else {
            // Ocultar todas las filas primero
            $('#asignacionesTable tbody tr').hide();
            
            // Mostrar solo las filas que coinciden con el estado seleccionado
            $('#asignacionesTable tbody tr[data-estado="' + selectedEstado + '"]').show();
        }
        
        // Recalcular la informaci?n de la tabla
        table.draw('page');
    });

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

    function clearValidationErrors() {
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    function showValidationErrors(errors) {
        clearValidationErrors();
        $.each(errors, function(field, messages) {
            const input = $(`[name="${field}"]`);
            input.addClass('is-invalid');
            input.siblings('.invalid-feedback').text(messages[0]);
        });
    }

    $('[data-bs-toggle="tooltip"]').tooltip();

    let asignacionToDelete = null;

    $('#addAsignacionModal').on('hidden.bs.modal', function() {
        $('#addAsignacionForm')[0].reset();
        clearValidationErrors();
        $('#saveAsignacionBtn .spinner-border').addClass('d-none');
        $('#saveAsignacionBtn').prop('disabled', false);
    });

    $('#addAsignacionForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#saveAsignacionBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        $.ajax({
            url: '{{ route("asignaciones.store") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addAsignacionModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                
                if (xhr.status === 422 && response.errors) {
                    showValidationErrors(response.errors);
                } else {
                    const message = response.message || 'Error al crear la asignaci?n. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    $(document).on('click', '.edit-asignacion', function() {
        const asignacionId = $(this).data('asignacion-id');
        
        $('#editAsignacionForm')[0].reset();
        clearValidationErrors();
        
        $.ajax({
            url: `/asignaciones/${asignacionId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const asignacion = response.asignacion;
                    
                    $('#edit_asignacion_id').val(asignacion.id);
                    $('#edit_nombre').val(asignacion.nombre);
                    $('#edit_abreviacion').val(asignacion.abreviacion);
                    $('#edit_descripcion').val(asignacion.descripcion);
                    $('#edit_estado').val(asignacion.estado);
                    
                    $('#editAsignacionModal').modal('show');
                } else {
                    showAlert('danger', response.message || 'Error al cargar los datos de la asignaci?n.');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response?.message || 'Error al cargar los datos de la asignaci?n.';
                showAlert('danger', message);
            }
        });
    });

    $('#editAsignacionModal').on('hidden.bs.modal', function() {
        $('#editAsignacionForm')[0].reset();
        clearValidationErrors();
        $('#updateAsignacionBtn .spinner-border').addClass('d-none');
        $('#updateAsignacionBtn').prop('disabled', false);
    });

    $('#editAsignacionForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const asignacionId = $('#edit_asignacion_id').val();
        const submitBtn = $('#updateAsignacionBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        $.ajax({
            url: `/asignaciones/${asignacionId}`,
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editAsignacionModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                
                if (xhr.status === 422 && response.errors) {
                    showValidationErrors(response.errors);
                } else {
                    const message = response.message || 'Error al actualizar la asignaci?n. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    $(document).on('click', '.delete-asignacion', function() {
        const asignacionId = $(this).data('asignacion-id');
        const asignacionName = $(this).data('asignacion-name');
        
        asignacionToDelete = asignacionId;
        $('#deleteAsignacionName').text(asignacionName);
        $('#deleteAsignacionModal').modal('show');
    });

    $('#deleteAsignacionModal').on('hidden.bs.modal', function() {
        asignacionToDelete = null;
        $('#confirmDeleteBtn .spinner-border').addClass('d-none');
        $('#confirmDeleteBtn').prop('disabled', false);
    });

    $('#confirmDeleteBtn').on('click', function() {
        if (!asignacionToDelete) return;
        
        const submitBtn = $(this);
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: `/asignaciones/${asignacionToDelete}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteAsignacionModal').modal('hide');
                    showAlert('success', response.message);
                    
                    const row = $(`tr[data-asignacion-id="${asignacionToDelete}"]`);
                    table.row(row).remove().draw();
                    
                    asignacionToDelete = null;
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response?.message || 'Error al eliminar la asignaci?n. Intente nuevamente.';
                showAlert('danger', message);
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });
});
</script>
@endsection