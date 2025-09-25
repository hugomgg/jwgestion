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
                                <i class="fas fa-award me-2"></i>Gestión de Nombramientos
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
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNombramientoModal">
                                    <i class="fas fa-plus me-2"></i>Agregar Nombramiento
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="nombramientosTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Fecha de Creación</th>
                                    @if(Auth::user()->canModify())
                                    <th>Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nombramientos as $nombramiento)
                                <tr data-nombramiento-id="{{ $nombramiento->id }}">
                                    <td>{{ $nombramiento->id }}</td>
                                    <td><span class="badge bg-primary">{{ $nombramiento->nombre }}</span></td>
                                    <td>{{ $nombramiento->descripcion ? Str::limit($nombramiento->descripcion, 50) : 'Sin descripción' }}</td>
                                    <td>
                                        @if($nombramiento->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $nombramiento->created_at->format('d/m/Y H:i') }}</td>
                                    @if(Auth::user()->canModify())
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-warning edit-nombramiento"
                                                    data-nombramiento-id="{{ $nombramiento->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Editar nombramiento">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-nombramiento"
                                                    data-nombramiento-id="{{ $nombramiento->id }}"
                                                    data-nombramiento-name="{{ $nombramiento->nombre }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Eliminar nombramiento">
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

<!-- Modal para Agregar Nombramiento -->
<div class="modal fade" id="addNombramientoModal" tabindex="-1" aria-labelledby="addNombramientoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNombramientoModalLabel">
                    <i class="fas fa-award me-2"></i>Agregar Nuevo Nombramiento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addNombramientoForm">
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
                    <button type="submit" class="btn btn-primary" id="saveNombramientoBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Nombramiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Nombramiento -->
<div class="modal fade" id="editNombramientoModal" tabindex="-1" aria-labelledby="editNombramientoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editNombramientoModalLabel">
                    <i class="fas fa-award me-2"></i>Editar Nombramiento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editNombramientoForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_nombramiento_id" name="nombramiento_id">
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
                    <button type="submit" class="btn btn-primary" id="updateNombramientoBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Nombramiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Confirmar Eliminación -->
<div class="modal fade" id="deleteNombramientoModal" tabindex="-1" aria-labelledby="deleteNombramientoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteNombramientoModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el nombramiento <strong id="deleteNombramientoName"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    Eliminar Nombramiento
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var table = $('#nombramientosTable').DataTable({
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
            @if(Auth::user()->canModify())
            { orderable: false, targets: [5] }
            @endif
        ]
    });

    // Variable para almacenar el filtro actual
    let currentFilter = null;

    $('#estadoFilter').on('change', function() {
        const selectedEstado = $(this).val();
        
        // Limpiar filtro anterior si existe
        if (currentFilter !== null) {
            $.fn.dataTable.ext.search.splice($.fn.dataTable.ext.search.indexOf(currentFilter), 1);
        }
        
        if (selectedEstado === '') {
            // Mostrar todos los registros
            currentFilter = null;
            table.draw();
        } else {
            // Mapear valores numéricos a textos para la búsqueda
            const textoEstado = selectedEstado === '1' ? 'Activo' : 'Inactivo';
            
            // Crear nueva función de filtro
            currentFilter = function(settings, data, dataIndex) {
                if (settings.nTable !== table.table().node()) {
                    return true;
                }
                const estadoColumn = data[3]; // Columna 3 es el estado
                return estadoColumn.indexOf(textoEstado) !== -1;
            };
            
            // Agregar el nuevo filtro
            $.fn.dataTable.ext.search.push(currentFilter);
            table.draw();
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

    let nombramientoToDelete = null;

    $('#addNombramientoModal').on('hidden.bs.modal', function() {
        $('#addNombramientoForm')[0].reset();
        clearValidationErrors();
        $('#saveNombramientoBtn .spinner-border').addClass('d-none');
        $('#saveNombramientoBtn').prop('disabled', false);
    });

    $('#addNombramientoForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#saveNombramientoBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        $.ajax({
            url: '{{ route("nombramiento.store") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addNombramientoModal').modal('hide');
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
                    const message = response.message || 'Error al crear el nombramiento. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    $(document).on('click', '.edit-nombramiento', function() {
        const nombramientoId = $(this).data('nombramiento-id');
        
        $('#editNombramientoForm')[0].reset();
        clearValidationErrors();
        
        $.ajax({
            url: `/nombramiento/${nombramientoId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const nombramiento = response.nombramiento;
                    
                    $('#edit_nombramiento_id').val(nombramiento.id);
                    $('#edit_nombre').val(nombramiento.nombre);
                    $('#edit_descripcion').val(nombramiento.descripcion);
                    $('#edit_estado').val(nombramiento.estado);
                    
                    $('#editNombramientoModal').modal('show');
                } else {
                    showAlert('danger', response.message || 'Error al cargar los datos del nombramiento.');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response?.message || 'Error al cargar los datos del nombramiento.';
                showAlert('danger', message);
            }
        });
    });

    $('#editNombramientoModal').on('hidden.bs.modal', function() {
        $('#editNombramientoForm')[0].reset();
        clearValidationErrors();
        $('#updateNombramientoBtn .spinner-border').addClass('d-none');
        $('#updateNombramientoBtn').prop('disabled', false);
    });

    $('#editNombramientoForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const nombramientoId = $('#edit_nombramiento_id').val();
        const submitBtn = $('#updateNombramientoBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        $.ajax({
            url: `/nombramiento/${nombramientoId}`,
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editNombramientoModal').modal('hide');
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
                    const message = response.message || 'Error al actualizar el nombramiento. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    $(document).on('click', '.delete-nombramiento', function() {
        const nombramientoId = $(this).data('nombramiento-id');
        const nombramientoName = $(this).data('nombramiento-name');
        
        nombramientoToDelete = nombramientoId;
        $('#deleteNombramientoName').text(nombramientoName);
        $('#deleteNombramientoModal').modal('show');
    });

    $('#deleteNombramientoModal').on('hidden.bs.modal', function() {
        nombramientoToDelete = null;
        $('#confirmDeleteBtn .spinner-border').addClass('d-none');
        $('#confirmDeleteBtn').prop('disabled', false);
    });

    $('#confirmDeleteBtn').on('click', function() {
        if (!nombramientoToDelete) return;
        
        const submitBtn = $(this);
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: `/nombramiento/${nombramientoToDelete}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteNombramientoModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response.message || 'Error al eliminar el nombramiento. Intente nuevamente.';
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