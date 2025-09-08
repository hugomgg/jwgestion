@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-music me-2"></i>Gestión de Canciones
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
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCancionModal">
                            <i class="fas fa-plus me-1"></i>Nueva Canción
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Contenedor para alertas -->
                    <div id="alert-container"></div>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table id="cancionesTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Número</th>
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
                                @foreach($canciones as $cancion)
                                <tr>
                                    <td>{{ $cancion->id }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $cancion->numero }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $cancion->nombre }}</span>
                                    </td>
                                    <td>{{ $cancion->descripcion ?? 'Sin descripción' }}</td>
                                    <td>
                                        <span class="badge {{ $cancion->estado ? 'bg-success' : 'bg-danger' }}">
                                            {{ $cancion->estado ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>{{ $cancion->created_at->format('d/m/Y H:i') }}</td>
                                    @if(Auth::user()->isAdmin())
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-warning edit-cancion"
                                                    data-id="{{ $cancion->id }}" data-bs-toggle="tooltip" title="Editar canción">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-cancion"
                                                    data-id="{{ $cancion->id }}" data-name="{{ $cancion->nombre }}"
                                                    data-bs-toggle="tooltip" title="Eliminar canción">
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
<!-- Modal Crear Canción -->
<div class="modal fade" id="createCancionModal" tabindex="-1" aria-labelledby="createCancionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCancionModalLabel">
                    <i class="fas fa-music me-2"></i>Nueva Canción
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createCancionForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="create_numero" class="form-label">Número *</label>
                                <input type="number" class="form-control" id="create_numero" name="numero" value="1" min="1" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="create_nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="create_nombre" name="nombre" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
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
                    <button type="submit" class="btn btn-primary" id="createCancionBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Crear Canción
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Canción -->
<div class="modal fade" id="editCancionModal" tabindex="-1" aria-labelledby="editCancionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCancionModalLabel">
                    <i class="fas fa-music me-2"></i>Editar Canción
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCancionForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_cancion_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_numero" class="form-label">Número *</label>
                                <input type="number" class="form-control" id="edit_numero" name="numero" min="1" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
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
                    <button type="submit" class="btn btn-primary" id="updateCancionBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Canción
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar Canción -->
<div class="modal fade" id="deleteCancionModal" tabindex="-1" aria-labelledby="deleteCancionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCancionModalLabel">
                    <i class="fas fa-trash me-2"></i>Eliminar Canción
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ¿Está seguro que desea eliminar la canción <strong id="deleteCancionName"></strong>?
                </div>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteCancion">
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
    const table = $('#cancionesTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
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
    let currentEstadoFilterCanciones = null;

    $('#filtroEstado').on('change', function() {
        const selectedEstado = $(this).val();
        
        // Limpiar filtro anterior si existe
        if (currentEstadoFilterCanciones !== null) {
            $.fn.dataTable.ext.search.splice($.fn.dataTable.ext.search.indexOf(currentEstadoFilterCanciones), 1);
        }
        
        if (selectedEstado === '') {
            currentEstadoFilterCanciones = null;
            table.draw();
        } else {
            // Mapear valores numéricos a textos para la búsqueda
            const textoEstado = selectedEstado === '1' ? 'Activo' : 'Inactivo';
            
            // Crear nueva función de filtro
            currentEstadoFilterCanciones = function(settings, data, dataIndex) {
                if (settings.nTable !== table.table().node()) {
                    return true;
                }
                const estadoColumn = data[4]; // Columna 4 es el estado
                return estadoColumn.indexOf(textoEstado) !== -1;
            };
            
            // Agregar el nuevo filtro
            $.fn.dataTable.ext.search.push(currentEstadoFilterCanciones);
            table.draw();
        }
    });

    @if(Auth::user()->isAdmin())
    // Crear canción
    $('#createCancionForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#createCancionBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors('createCancionForm');
        
        $.ajax({
            url: '{{ route("canciones.store") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#createCancionModal').modal('hide');
                    form[0].reset();
                    showAlert('success', response.message);
                    
                    // Recargar la página para mostrar la nueva canción
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                
                if (xhr.status === 422 && response.errors) {
                    showValidationErrors('createCancionForm', response.errors);
                } else {
                    const message = response.message || 'Error al crear la canción. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Editar canción
    $('.edit-cancion').on('click', function() {
        const cancionId = $(this).data('id');
        
        $.ajax({
            url: `/canciones/${cancionId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const cancion = response.cancion;
                    $('#edit_cancion_id').val(cancion.id);
                    $('#edit_numero').val(cancion.numero);
                    $('#edit_nombre').val(cancion.nombre);
                    $('#edit_descripcion').val(cancion.descripcion);
                    $('#edit_estado').val(cancion.estado ? '1' : '0');
                    $('#editCancionModal').modal('show');
                }
            },
            error: function() {
                showAlert('danger', 'Error al cargar los datos de la canción.');
            }
        });
    });

    // Actualizar canción
    $('#editCancionForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#updateCancionBtn');
        const spinner = submitBtn.find('.spinner-border');
        const cancionId = $('#edit_cancion_id').val();
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors('editCancionForm');
        
        $.ajax({
            url: `/canciones/${cancionId}`,
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editCancionModal').modal('hide');
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
                    showValidationErrors('editCancionForm', response.errors);
                } else {
                    const message = response.message || 'Error al actualizar la canción. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Eliminar canción
    let cancionToDelete = null;
    
    $('.delete-cancion').on('click', function() {
        cancionToDelete = $(this).data('id');
        const cancionName = $(this).data('name');
        $('#deleteCancionName').text(cancionName);
        $('#deleteCancionModal').modal('show');
    });

    $('#confirmDeleteCancion').on('click', function() {
        const btn = $(this);
        const spinner = btn.find('.spinner-border');
        
        btn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: `/canciones/${cancionToDelete}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteCancionModal').modal('hide');
                    showAlert('success', response.message);
                    
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response.message || 'Error al eliminar la canción. Intente nuevamente.';
                showAlert('danger', message);
                $('#deleteCancionModal').modal('hide');
            },
            complete: function() {
                btn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });
    @endif
});
</script>
@endsection