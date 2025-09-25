@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-briefcase me-2"></i>Gestión de Servicios
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
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createServicioModal">
                            <i class="fas fa-plus me-1"></i>Nuevo Servicio
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Contenedor para alertas -->
                    <div id="alert-container"></div>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table id="serviciosTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    @if(Auth::user()->isAdmin())
                                    <th>Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($servicios as $servicio)
                                <tr>
                                    <td>{{ $servicio->id }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $servicio->nombre }}</span>
                                    </td>
                                    <td>{{ $servicio->descripcion ?? 'Sin descripción' }}</td>
                                    <td>
                                        <span class="badge {{ $servicio->estado ? 'bg-success' : 'bg-danger' }}">
                                            {{ $servicio->estado ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>{{ $servicio->created_at->format('d/m/Y H:i') }}</td>
                                    @if(Auth::user()->isAdmin())
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-warning edit-servicio"
                                                    data-id="{{ $servicio->id }}" data-bs-toggle="tooltip" title="Editar servicio">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-servicio"
                                                    data-id="{{ $servicio->id }}" data-name="{{ $servicio->nombre }}"
                                                    data-bs-toggle="tooltip" title="Eliminar servicio">
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
<!-- Modal Crear Servicio -->
<div class="modal fade" id="createServicioModal" tabindex="-1" aria-labelledby="createServicioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createServicioModalLabel">
                    <i class="fas fa-briefcase me-2"></i>Nuevo Servicio
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createServicioForm">
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
                                <label for="create_estado" class="form-label">Estado *</label>
                                <select class="form-select" id="create_estado" name="estado" required>
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
                                <label for="create_descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="create_descripcion" name="descripcion" rows="3" maxlength="500"></textarea>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Máximo 500 caracteres</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="createServicioBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Crear Servicio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Servicio -->
<div class="modal fade" id="editServicioModal" tabindex="-1" aria-labelledby="editServicioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editServicioModalLabel">
                    <i class="fas fa-briefcase me-2"></i>Editar Servicio
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editServicioForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_servicio_id">
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
                                <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3" maxlength="500"></textarea>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Máximo 500 caracteres</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="updateServicioBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Servicio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar Servicio -->
<div class="modal fade" id="deleteServicioModal" tabindex="-1" aria-labelledby="deleteServicioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteServicioModalLabel">
                    <i class="fas fa-trash me-2"></i>Eliminar Servicio
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ¿Está seguro que desea eliminar el servicio <strong id="deleteServicioName"></strong>?
                </div>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteServicio">
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
    const table = $('#serviciosTable').DataTable({
        language: {
            url: '/js/datatables-es-ES.json'
        },
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'asc']],
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
    let currentEstadoFilterServicios = null;

    $('#filtroEstado').on('change', function() {
        const selectedEstado = $(this).val();
        
        // Limpiar filtro anterior si existe
        if (currentEstadoFilterServicios !== null) {
            $.fn.dataTable.ext.search.splice($.fn.dataTable.ext.search.indexOf(currentEstadoFilterServicios), 1);
        }
        
        if (selectedEstado === '') {
            currentEstadoFilterServicios = null;
            table.draw();
        } else {
            // Mapear valores numéricos a textos para la búsqueda
            const textoEstado = selectedEstado === '1' ? 'Activo' : 'Inactivo';
            
            // Crear nueva función de filtro
            currentEstadoFilterServicios = function(settings, data, dataIndex) {
                if (settings.nTable !== table.table().node()) {
                    return true;
                }
                const estadoColumn = data[3]; // Columna 3 es el estado
                return estadoColumn.indexOf(textoEstado) !== -1;
            };
            
            // Agregar el nuevo filtro
            $.fn.dataTable.ext.search.push(currentEstadoFilterServicios);
            table.draw();
        }
    });

    @if(Auth::user()->isAdmin())
    // Crear servicio
    $('#createServicioForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#createServicioBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors('createServicioForm');
        
        $.ajax({
            url: '{{ route("servicios.store") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#createServicioModal').modal('hide');
                    form[0].reset();
                    showAlert('success', response.message);
                    
                    // Recargar la página para mostrar el nuevo servicio
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                
                if (xhr.status === 422 && response.errors) {
                    showValidationErrors('createServicioForm', response.errors);
                } else {
                    const message = response.message || 'Error al crear el servicio. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Editar servicio
    $('.edit-servicio').on('click', function() {
        const servicioId = $(this).data('id');
        
        $.ajax({
            url: `/servicios/${servicioId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const servicio = response.servicio;
                    $('#edit_servicio_id').val(servicio.id);
                    $('#edit_nombre').val(servicio.nombre);
                    $('#edit_descripcion').val(servicio.descripcion);
                    $('#edit_estado').val(servicio.estado);
                    $('#editServicioModal').modal('show');
                }
            },
            error: function() {
                showAlert('danger', 'Error al cargar los datos del servicio.');
            }
        });
    });

    // Actualizar servicio
    $('#editServicioForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#updateServicioBtn');
        const spinner = submitBtn.find('.spinner-border');
        const servicioId = $('#edit_servicio_id').val();
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors('editServicioForm');
        
        $.ajax({
            url: `/servicios/${servicioId}`,
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editServicioModal').modal('hide');
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
                    showValidationErrors('editServicioForm', response.errors);
                } else {
                    const message = response.message || 'Error al actualizar el servicio. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Eliminar servicio
    let servicioToDelete = null;
    
    $('.delete-servicio').on('click', function() {
        servicioToDelete = $(this).data('id');
        const servicioName = $(this).data('name');
        $('#deleteServicioName').text(servicioName);
        $('#deleteServicioModal').modal('show');
    });

    $('#confirmDeleteServicio').on('click', function() {
        const btn = $(this);
        const spinner = btn.find('.spinner-border');
        
        btn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: `/servicios/${servicioToDelete}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteServicioModal').modal('hide');
                    showAlert('success', response.message);
                    
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response.message || 'Error al eliminar el servicio. Intente nuevamente.';
                showAlert('danger', message);
                $('#deleteServicioModal').modal('hide');
            },
            complete: function() {
                btn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Limpiar formularios cuando se cierren los modales
    $('#createServicioModal').on('hidden.bs.modal', function() {
        $('#createServicioForm')[0].reset();
        clearValidationErrors('createServicioForm');
        $('#createServicioBtn .spinner-border').addClass('d-none');
        $('#createServicioBtn').prop('disabled', false);
    });

    $('#editServicioModal').on('hidden.bs.modal', function() {
        clearValidationErrors('editServicioForm');
        $('#updateServicioBtn .spinner-border').addClass('d-none');
        $('#updateServicioBtn').prop('disabled', false);
    });
    @endif
});
</script>
@endsection