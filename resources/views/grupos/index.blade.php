@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="fas fa-users-cog me-2"></i>Gestión de Grupos
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end align-items-center gap-3">
                                <div class="d-flex align-items-center">
                                    <label for="estadoFilter" class="form-label me-2 mb-0">Estado:</label>
                                    <select class="form-select" id="estadoFilter" style="width: auto;">
                                        <option value="">Todos</option>
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                                @if(Auth::user()->canModify())
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGrupoModal">
                                    <i class="fas fa-plus me-2"></i>Agregar Grupo
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
                        <table id="gruposTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Estado</th>
                                    <th>Usuarios Asignados</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grupos as $grupo)
                                <tr data-grupo-id="{{ $grupo->id }}">
                                    <td>{{ $grupo->id }}</td>
                                    <td>{{ $grupo->nombre }}</td>
                                    <td>
                                        @if($grupo->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $grupo->usuarios->count() }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info view-grupo"
                                                    data-grupo-id="{{ $grupo->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Ver grupo">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if(Auth::user()->canModify())
                                            <button type="button" class="btn btn-sm btn-warning edit-grupo"
                                                    data-grupo-id="{{ $grupo->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Editar grupo">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-grupo"
                                                    data-grupo-id="{{ $grupo->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Eliminar grupo">
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

<!-- Modal para Agregar Grupo -->
<div class="modal fade" id="addGrupoModal" tabindex="-1" aria-labelledby="addGrupoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGrupoModalLabel">
                    <i class="fas fa-layer-group me-2"></i>Agregar Nuevo Grupo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addGrupoForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveGrupoBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Grupo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Grupo -->
<div class="modal fade" id="editGrupoModal" tabindex="-1" aria-labelledby="editGrupoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editGrupoModalLabel">
                    <i class="fas fa-layer-group me-2"></i>Editar Grupo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editGrupoForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_grupo_id" name="grupo_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="updateGrupoBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Grupo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Ver Grupo -->
<div class="modal fade" id="viewGrupoModal" tabindex="-1" aria-labelledby="viewGrupoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewGrupoModalLabel">
                    <i class="fas fa-layer-group me-2"></i>Detalles del Grupo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>ID:</strong>
                        <p id="view_grupo_id"></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Nombre:</strong>
                        <p id="view_grupo_nombre"></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Estado:</strong>
                        <p id="view_grupo_estado"></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Usuarios Asignados:</strong>
                        <p id="view_grupo_usuarios"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Inicializar DataTable
    var table = $('#gruposTable').DataTable({
        responsive: true,
        language: {
            url: '/js/datatables-es-ES.json'
        },
        order: [[1, 'asc']] // Ordenar por nombre
    });

    // Filtro por estado
    let currentEstadoFilter = null;

    $('#estadoFilter').on('change', function() {
        const selectedEstado = $(this).val();
        
        // Limpiar filtro anterior si existe
        if (currentEstadoFilter !== null) {
            $.fn.dataTable.ext.search.splice($.fn.dataTable.ext.search.indexOf(currentEstadoFilter), 1);
        }
        
        if (selectedEstado === '') {
            currentEstadoFilter = null;
            table.draw();
        } else {
            // Mapear valores numéricos a textos para la búsqueda
            const textoEstado = selectedEstado === '1' ? 'Activo' : 'Inactivo';
            
            // Crear nueva función de filtro
            currentEstadoFilter = function(settings, data, dataIndex) {
                if (settings.nTable !== table.table().node()) {
                    return true;
                }
                const estadoColumn = data[2]; // Columna 2 es el estado
                return estadoColumn.indexOf(textoEstado) !== -1;
            };
            
            // Agregar el nuevo filtro
            $.fn.dataTable.ext.search.push(currentEstadoFilter);
            table.draw();
        }
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
    function clearValidationErrors() {
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    // Función para mostrar errores de validación
    function showValidationErrors(errors) {
        clearValidationErrors();
        $.each(errors, function(field, messages) {
            const input = $(`[name="${field}"]`);
            input.addClass('is-invalid');
            input.siblings('.invalid-feedback').text(messages[0]);
        });
    }

    // Manejar el envío del formulario de agregar
    $('#addGrupoForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#saveGrupoBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        $.ajax({
            url: '{{ route("grupos.store") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    $('#addGrupoModal').modal('hide');
                    form[0].reset();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                
                if (xhr.status === 422 && response.errors) {
                    showValidationErrors(response.errors);
                } else {
                    const message = response.message || 'Error al crear el grupo. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Manejar clic en ver grupo
    $('.view-grupo').on('click', function() {
        const grupoId = $(this).data('grupo-id');
        
        $.ajax({
            url: `/grupos/${grupoId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const grupo = response.grupo;
                    $('#view_grupo_id').text(grupo.id);
                    $('#view_grupo_nombre').text(grupo.nombre);
                    $('#view_grupo_estado').html(grupo.estado == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>');
                    $('#view_grupo_usuarios').text('Ver en la tabla principal');
                    $('#viewGrupoModal').modal('show');
                }
            },
            error: function() {
                showAlert('danger', 'Error al cargar los datos del grupo.');
            }
        });
    });

    // Manejar clic en editar grupo
    $('.edit-grupo').on('click', function() {
        const grupoId = $(this).data('grupo-id');
        
        $.ajax({
            url: `/grupos/${grupoId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const grupo = response.grupo;
                    $('#edit_grupo_id').val(grupo.id);
                    $('#edit_nombre').val(grupo.nombre);
                    $('#edit_estado').val(grupo.estado);
                    $('#editGrupoModal').modal('show');
                }
            },
            error: function() {
                showAlert('danger', 'Error al cargar los datos del grupo.');
            }
        });
    });

    // Manejar el envío del formulario de editar
    $('#editGrupoForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const grupoId = $('#edit_grupo_id').val();
        const submitBtn = $('#updateGrupoBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        $.ajax({
            url: `/grupos/${grupoId}`,
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    $('#editGrupoModal').modal('hide');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                
                if (xhr.status === 422 && response.errors) {
                    showValidationErrors(response.errors);
                } else {
                    const message = response.message || 'Error al actualizar el grupo. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Manejar clic en eliminar grupo
    $('.delete-grupo').on('click', function() {
        const grupoId = $(this).data('grupo-id');
        const grupoRow = $(this).closest('tr');
        const grupoNombre = grupoRow.find('td:nth-child(2)').text();
        
        if (confirm(`¿Está seguro que desea eliminar el grupo "${grupoNombre}"?`)) {
            $.ajax({
                url: `/grupos/${grupoId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    const message = response.message || 'Error al eliminar el grupo. Intente nuevamente.';
                    showAlert('danger', message);
                }
            });
        }
    });

    // Limpiar modales cuando se cierren
    $('.modal').on('hidden.bs.modal', function() {
        clearValidationErrors();
        $(this).find('form')[0].reset();
        $(this).find('.spinner-border').addClass('d-none');
        $(this).find('button[type="submit"]').prop('disabled', false);
    });
});
</script>
@endsection