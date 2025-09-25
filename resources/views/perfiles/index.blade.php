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
                                <i class="fas fa-user-tag me-2"></i>Gestión de Perfiles
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
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPerfilModal">
                                    <i class="fas fa-plus me-2"></i>Agregar Perfil
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="perfilesTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Privilegio</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Fecha de Creación</th>
                                    @if(Auth::user()->canModify())
                                    <th>Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($perfiles as $perfil)
                                <tr data-perfil-id="{{ $perfil->id }}">
                                    <td>{{ $perfil->id }}</td>
                                    <td>{{ $perfil->nombre }}</td>
                                    <td><span class="badge bg-info">{{ $perfil->privilegio }}</span></td>
                                    <td>{{ Str::limit($perfil->descripcion, 50) }}</td>
                                    <td>
                                        @if($perfil->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $perfil->created_at->format('d/m/Y H:i') }}</td>
                                    @if(Auth::user()->canModify())
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-warning edit-perfil"
                                                    data-perfil-id="{{ $perfil->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Editar perfil">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-perfil"
                                                    data-perfil-id="{{ $perfil->id }}"
                                                    data-perfil-name="{{ $perfil->nombre }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Eliminar perfil">
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

<!-- Modal para Agregar Perfil -->
<div class="modal fade" id="addPerfilModal" tabindex="-1" aria-labelledby="addPerfilModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPerfilModalLabel">
                    <i class="fas fa-user-tag me-2"></i>Agregar Nuevo Perfil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPerfilForm">
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
                                <label for="privilegio" class="form-label">Privilegio *</label>
                                <input type="text" class="form-control" id="privilegio" name="privilegio" value="Anciano" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
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
                        <div class="col-md-6"></div>
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
                    <button type="submit" class="btn btn-primary" id="savePerfilBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Perfil
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Perfil -->
<div class="modal fade" id="editPerfilModal" tabindex="-1" aria-labelledby="editPerfilModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPerfilModalLabel">
                    <i class="fas fa-user-tag me-2"></i>Editar Perfil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPerfilForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_perfil_id" name="perfil_id">
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
                                <label for="edit_privilegio" class="form-label">Privilegio *</label>
                                <input type="text" class="form-control" id="edit_privilegio" name="privilegio" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
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
                        <div class="col-md-6"></div>
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
                    <button type="submit" class="btn btn-primary" id="updatePerfilBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Perfil
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Confirmar Eliminación -->
<div class="modal fade" id="deletePerfilModal" tabindex="-1" aria-labelledby="deletePerfilModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePerfilModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el perfil <strong id="deletePerfilName"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer y solo es posible si no hay usuarios asignados a este perfil.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    Eliminar Perfil
                </button>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var table = $('#perfilesTable').DataTable({
        responsive: true,
        language: {
            url: '/js/datatables-es-ES.json'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        columnDefs: [
            { responsivePriority: 1, targets: [1, 2, 3] },
            { responsivePriority: 2, targets: [0, 4, 5] },
            @if(Auth::user()->canModify())
            { orderable: false, targets: [6] }
            @endif
        ]
    });

    $('#estadoFilter').on('change', function() {
        const selectedEstado = $(this).val();
        if (selectedEstado === '') {
            table.column(4).search('').draw();
        } else {
            table.column(4).search(selectedEstado).draw();
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

    let perfilToDelete = null;

    $('#addPerfilModal').on('hidden.bs.modal', function() {
        $('#addPerfilForm')[0].reset();
        clearValidationErrors();
        $('#savePerfilBtn .spinner-border').addClass('d-none');
        $('#savePerfilBtn').prop('disabled', false);
    });

    $('#addPerfilForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#savePerfilBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        $.ajax({
            url: '{{ route("perfiles.store") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addPerfilModal').modal('hide');
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
                    const message = response.message || 'Error al crear el perfil. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    $(document).on('click', '.edit-perfil', function() {
        const perfilId = $(this).data('perfil-id');
        
        $('#editPerfilForm')[0].reset();
        clearValidationErrors();
        
        $.ajax({
            url: `/perfiles/${perfilId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const perfil = response.perfil;
                    
                    $('#edit_perfil_id').val(perfil.id);
                    $('#edit_nombre').val(perfil.nombre);
                    $('#edit_privilegio').val(perfil.privilegio);
                    $('#edit_descripcion').val(perfil.descripcion);
                    $('#edit_estado').val(perfil.estado);
                    
                    $('#editPerfilModal').modal('show');
                } else {
                    showAlert('danger', response.message || 'Error al cargar los datos del perfil.');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response?.message || 'Error al cargar los datos del perfil.';
                showAlert('danger', message);
            }
        });
    });

    $('#editPerfilModal').on('hidden.bs.modal', function() {
        $('#editPerfilForm')[0].reset();
        clearValidationErrors();
        $('#updatePerfilBtn .spinner-border').addClass('d-none');
        $('#updatePerfilBtn').prop('disabled', false);
    });

    $('#editPerfilForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const perfilId = $('#edit_perfil_id').val();
        const submitBtn = $('#updatePerfilBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        $.ajax({
            url: `/perfiles/${perfilId}`,
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editPerfilModal').modal('hide');
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
                    const message = response.message || 'Error al actualizar el perfil. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    $(document).on('click', '.delete-perfil', function() {
        const perfilId = $(this).data('perfil-id');
        const perfilName = $(this).data('perfil-name');
        
        perfilToDelete = perfilId;
        $('#deletePerfilName').text(perfilName);
        $('#deletePerfilModal').modal('show');
    });

    $('#deletePerfilModal').on('hidden.bs.modal', function() {
        perfilToDelete = null;
        $('#confirmDeleteBtn .spinner-border').addClass('d-none');
        $('#confirmDeleteBtn').prop('disabled', false);
    });

    $('#confirmDeleteBtn').on('click', function() {
        if (!perfilToDelete) return;
        
        const submitBtn = $(this);
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: `/perfiles/${perfilToDelete}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#deletePerfilModal').modal('hide');
                    showAlert('success', response.message);
                    
                    const row = $(`tr[data-perfil-id="${perfilToDelete}"]`);
                    table.row(row).remove().draw();
                    
                    perfilToDelete = null;
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response?.message || 'Error al eliminar el perfil. Intente nuevamente.';
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