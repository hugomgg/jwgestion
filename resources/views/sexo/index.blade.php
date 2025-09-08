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
                                <i class="fas fa-venus-mars me-2"></i>Gestión de Sexos
                            </h5>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-end align-items-center gap-3">
                                <div class="d-flex align-items-center">
                                    <label for="estadoFilter" class="form-label me-2 mb-0">Filtrar por estado:</label>
                                    <select class="form-select" id="estadoFilter" style="width: auto;">
                                        <option value="">Todos los estados</option>
                                        <option value="Activo">Activo</option>
                                        <option value="Inactivo">Inactivo</option>
                                    </select>
                                </div>
                                @if(Auth::user()->canModify())
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSexoModal">
                                    <i class="fas fa-plus me-2"></i>Agregar Sexo
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="sexosTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Fecha de Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sexos as $sexo)
                                <tr data-sexo-id="{{ $sexo->id }}">
                                    <td>{{ $sexo->id }}</td>
                                    <td><span class="badge bg-info">{{ $sexo->nombre }}</span></td>
                                    <td>{{ $sexo->descripcion ? Str::limit($sexo->descripcion, 50) : 'Sin descripción' }}</td>
                                    <td>
                                        @if($sexo->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $sexo->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if(Auth::user()->canModify())
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-warning edit-sexo"
                                                    data-sexo-id="{{ $sexo->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Editar sexo">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-sexo"
                                                    data-sexo-id="{{ $sexo->id }}"
                                                    data-sexo-name="{{ $sexo->nombre }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Eliminar sexo">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        @else
                                        <span class="text-muted small">Solo lectura</span>
                                        @endif
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

<!-- Modal para Agregar Sexo -->
<div class="modal fade" id="addSexoModal" tabindex="-1" aria-labelledby="addSexoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSexoModalLabel">
                    <i class="fas fa-venus-mars me-2"></i>Agregar Nuevo Sexo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addSexoForm">
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
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Máximo 500 caracteres (opcional)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveSexoBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Sexo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Sexo -->
<div class="modal fade" id="editSexoModal" tabindex="-1" aria-labelledby="editSexoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSexoModalLabel">
                    <i class="fas fa-venus-mars me-2"></i>Editar Sexo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSexoForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_sexo_id" name="sexo_id">
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
                                <label for="edit_descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Máximo 500 caracteres (opcional)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="updateSexoBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Sexo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Confirmar Eliminación -->
<div class="modal fade" id="deleteSexoModal" tabindex="-1" aria-labelledby="deleteSexoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSexoModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el sexo <strong id="deleteSexoName"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    Eliminar Sexo
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var table = $('#sexosTable').DataTable({
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
            { orderable: false, targets: [5] }
        ]
    });

    $('#estadoFilter').on('change', function() {
        const selectedEstado = $(this).val();
        if (selectedEstado === '') {
            table.column(3).search('').draw();
        } else {
            table.column(3).search(selectedEstado).draw();
        }
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

    let sexoToDelete = null;

    $('#addSexoModal').on('hidden.bs.modal', function() {
        $('#addSexoForm')[0].reset();
        clearValidationErrors();
        $('#saveSexoBtn .spinner-border').addClass('d-none');
        $('#saveSexoBtn').prop('disabled', false);
    });

    $('#addSexoForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#saveSexoBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        $.ajax({
            url: '{{ route("sexo.store") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addSexoModal').modal('hide');
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
                    const message = response.message || 'Error al crear el sexo. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    $(document).on('click', '.edit-sexo', function() {
        const sexoId = $(this).data('sexo-id');
        
        $('#editSexoForm')[0].reset();
        clearValidationErrors();
        
        $.ajax({
            url: `/sexo/${sexoId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const sexo = response.sexo;
                    
                    $('#edit_sexo_id').val(sexo.id);
                    $('#edit_nombre').val(sexo.nombre);
                    $('#edit_descripcion').val(sexo.descripcion);
                    $('#edit_estado').val(sexo.estado);
                    
                    $('#editSexoModal').modal('show');
                } else {
                    showAlert('danger', response.message || 'Error al cargar los datos del sexo.');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response?.message || 'Error al cargar los datos del sexo.';
                showAlert('danger', message);
            }
        });
    });

    $('#editSexoModal').on('hidden.bs.modal', function() {
        $('#editSexoForm')[0].reset();
        clearValidationErrors();
        $('#updateSexoBtn .spinner-border').addClass('d-none');
        $('#updateSexoBtn').prop('disabled', false);
    });

    $('#editSexoForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const sexoId = $('#edit_sexo_id').val();
        const submitBtn = $('#updateSexoBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        $.ajax({
            url: `/sexo/${sexoId}`,
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editSexoModal').modal('hide');
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
                    const message = response.message || 'Error al actualizar el sexo. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    $(document).on('click', '.delete-sexo', function() {
        const sexoId = $(this).data('sexo-id');
        const sexoName = $(this).data('sexo-name');
        
        sexoToDelete = sexoId;
        $('#deleteSexoName').text(sexoName);
        $('#deleteSexoModal').modal('show');
    });

    $('#deleteSexoModal').on('hidden.bs.modal', function() {
        sexoToDelete = null;
        $('#confirmDeleteBtn .spinner-border').addClass('d-none');
        $('#confirmDeleteBtn').prop('disabled', false);
    });

    $('#confirmDeleteBtn').on('click', function() {
        if (!sexoToDelete) return;
        
        const submitBtn = $(this);
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: `/sexo/${sexoToDelete}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteSexoModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response.message || 'Error al eliminar el sexo. Intente nuevamente.';
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