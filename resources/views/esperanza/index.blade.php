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
                                <i class="fas fa-star me-2"></i>Gestión de Esperanzas
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
                                @if(Auth::user()->isAdmin())
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEsperanzaModal">
                                    <i class="fas fa-plus me-2"></i>Agregar Esperanza
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="esperanzasTable" class="table table-striped table-hover">
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
                                @foreach($esperanzas as $esperanza)
                                <tr data-esperanza-id="{{ $esperanza->id }}">
                                    <td>{{ $esperanza->id }}</td>
                                    <td><span class="badge bg-info">{{ $esperanza->nombre }}</span></td>
                                    <td>{{ $esperanza->descripcion ? Str::limit($esperanza->descripcion, 50) : 'Sin descripción' }}</td>
                                    <td>
                                        @if($esperanza->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $esperanza->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if(Auth::user()->isAdmin())
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-warning edit-esperanza"
                                                    data-esperanza-id="{{ $esperanza->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Editar esperanza">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-esperanza"
                                                    data-esperanza-id="{{ $esperanza->id }}"
                                                    data-esperanza-name="{{ $esperanza->nombre }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Eliminar esperanza">
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

<!-- Modal para Agregar Esperanza -->
<div class="modal fade" id="addEsperanzaModal" tabindex="-1" aria-labelledby="addEsperanzaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEsperanzaModalLabel">
                    <i class="fas fa-star me-2"></i>Agregar Nueva Esperanza
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addEsperanzaForm">
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
                    <button type="submit" class="btn btn-primary" id="saveEsperanzaBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Esperanza
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Esperanza -->
<div class="modal fade" id="editEsperanzaModal" tabindex="-1" aria-labelledby="editEsperanzaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEsperanzaModalLabel">
                    <i class="fas fa-star me-2"></i>Editar Esperanza
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEsperanzaForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_esperanza_id" name="esperanza_id">
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
                    <button type="submit" class="btn btn-primary" id="updateEsperanzaBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Esperanza
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Confirmar Eliminación -->
<div class="modal fade" id="deleteEsperanzaModal" tabindex="-1" aria-labelledby="deleteEsperanzaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEsperanzaModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar la esperanza <strong id="deleteEsperanzaName"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    Eliminar Esperanza
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var table = $('#esperanzasTable').DataTable({
        responsive: true,
        language: {
            url: '/js/datatables-es-ES.json'
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

    let esperanzaToDelete = null;

    $('#addEsperanzaModal').on('hidden.bs.modal', function() {
        $('#addEsperanzaForm')[0].reset();
        clearValidationErrors();
        $('#saveEsperanzaBtn .spinner-border').addClass('d-none');
        $('#saveEsperanzaBtn').prop('disabled', false);
    });

    $('#addEsperanzaForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#saveEsperanzaBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        $.ajax({
            url: '{{ route("esperanza.store") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addEsperanzaModal').modal('hide');
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
                    const message = response.message || 'Error al crear la esperanza. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    $(document).on('click', '.edit-esperanza', function() {
        const esperanzaId = $(this).data('esperanza-id');
        
        $('#editEsperanzaForm')[0].reset();
        clearValidationErrors();
        
        $.ajax({
            url: `/esperanza/${esperanzaId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const esperanza = response.esperanza;
                    
                    $('#edit_esperanza_id').val(esperanza.id);
                    $('#edit_nombre').val(esperanza.nombre);
                    $('#edit_descripcion').val(esperanza.descripcion);
                    $('#edit_estado').val(esperanza.estado);
                    
                    $('#editEsperanzaModal').modal('show');
                } else {
                    showAlert('danger', response.message || 'Error al cargar los datos de la esperanza.');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response?.message || 'Error al cargar los datos de la esperanza.';
                showAlert('danger', message);
            }
        });
    });

    $('#editEsperanzaModal').on('hidden.bs.modal', function() {
        $('#editEsperanzaForm')[0].reset();
        clearValidationErrors();
        $('#updateEsperanzaBtn .spinner-border').addClass('d-none');
        $('#updateEsperanzaBtn').prop('disabled', false);
    });

    $('#editEsperanzaForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const esperanzaId = $('#edit_esperanza_id').val();
        const submitBtn = $('#updateEsperanzaBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        $.ajax({
            url: `/esperanza/${esperanzaId}`,
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editEsperanzaModal').modal('hide');
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
                    const message = response.message || 'Error al actualizar la esperanza. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    $(document).on('click', '.delete-esperanza', function() {
        const esperanzaId = $(this).data('esperanza-id');
        const esperanzaName = $(this).data('esperanza-name');
        
        esperanzaToDelete = esperanzaId;
        $('#deleteEsperanzaName').text(esperanzaName);
        $('#deleteEsperanzaModal').modal('show');
    });

    $('#deleteEsperanzaModal').on('hidden.bs.modal', function() {
        esperanzaToDelete = null;
        $('#confirmDeleteBtn .spinner-border').addClass('d-none');
        $('#confirmDeleteBtn').prop('disabled', false);
    });

    $('#confirmDeleteBtn').on('click', function() {
        if (!esperanzaToDelete) return;
        
        const submitBtn = $(this);
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: `/esperanza/${esperanzaToDelete}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteEsperanzaModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response.message || 'Error al eliminar la esperanza. Intente nuevamente.';
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