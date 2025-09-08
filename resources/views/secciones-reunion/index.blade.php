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
                                <i class="fas fa-list-alt me-2"></i>Gestión de Secciones Reunión
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                @if(Auth::user()->isAdmin())
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSeccionModal">
                                    <i class="fas fa-plus me-2"></i>Agregar Sección
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Contenedor para alertas -->
                    <div id="alert-container"></div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="seccionesTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Abreviación</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($secciones as $seccion)
                                <tr>
                                    <td>{{ $seccion->id }}</td>
                                    <td>{{ $seccion->nombre }}</td>
                                    <td><span class="badge bg-info">{{ $seccion->abreviacion }}</span></td>
                                    <td>
                                        @if($seccion->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $seccion->creado_por_timestamp ? \Carbon\Carbon::parse($seccion->creado_por_timestamp)->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if(Auth::user()->isAdmin())
                                            <button type="button" class="btn btn-sm btn-warning edit-seccion"
                                                    data-seccion-id="{{ $seccion->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Editar sección">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-seccion"
                                                    data-seccion-id="{{ $seccion->id }}"
                                                    data-seccion-nombre="{{ $seccion->nombre }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Eliminar sección">
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

<!-- Modal Agregar Sección -->
<div class="modal fade" id="addSeccionModal" tabindex="-1" aria-labelledby="addSeccionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSeccionModalLabel">
                    <i class="fas fa-list-alt me-2"></i>Agregar Sección Reunión
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addSeccionForm">
                @csrf
                <div class="modal-body">
                    <!-- Contenedor para alertas -->
                    <div id="add-alert-container"></div>
                    
                    <div class="mb-3">
                        <label for="add_nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="add_nombre" name="nombre" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_abreviacion" class="form-label">Abreviación *</label>
                        <input type="text" class="form-control" id="add_abreviacion" name="abreviacion" maxlength="10" required>
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Máximo 10 caracteres</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_estado" class="form-label">Estado *</label>
                        <select class="form-select" id="add_estado" name="estado" required>
                            <option value="">Seleccionar estado</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveSeccionBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Sección
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Sección -->
<div class="modal fade" id="editSeccionModal" tabindex="-1" aria-labelledby="editSeccionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSeccionModalLabel">
                    <i class="fas fa-list-alt me-2"></i>Editar Sección Reunión
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSeccionForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_seccion_id" name="seccion_id">
                <div class="modal-body">
                    <!-- Contenedor para alertas -->
                    <div id="edit-alert-container"></div>
                    
                    <div class="mb-3">
                        <label for="edit_nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_abreviacion" class="form-label">Abreviación *</label>
                        <input type="text" class="form-control" id="edit_abreviacion" name="abreviacion" maxlength="10" required>
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Máximo 10 caracteres</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_estado" class="form-label">Estado *</label>
                        <select class="form-select" id="edit_estado" name="estado" required>
                            <option value="">Seleccionar estado</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Información de auditoría -->
                    <div class="mt-4">
                        <h6 class="text-muted">Información de Auditoría</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Creado por:</strong> <span id="edit_creado_por">-</span><br>
                                    <strong>Fecha creación:</strong> <span id="edit_creado_fecha">-</span>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>Modificado por:</strong> <span id="edit_modificado_por">-</span><br>
                                    <strong>Fecha modificación:</strong> <span id="edit_modificado_fecha">-</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="updateSeccionBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Sección
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>


// Esperamos a que el DOM esté completamente cargado
$(document).ready(function() {
    
    
    
    
    // Verificar que todos los requisitos estén disponibles
    if (typeof $ === 'undefined') {
        console.error('jQuery no está disponible');
        return;
    }
    
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables no está disponible');
        return;
    }
    
    if ($('#seccionesTable').length === 0) {
        console.error('Tabla #seccionesTable no encontrada');
        return;
    }
    
    
    
    try {
        // Configuración del DataTable con textos personalizados
        var table = $('#seccionesTable').DataTable({
            language: {
                "decimal": "",
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(Filtrado de _MAX_ total registros)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ registros",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            paging: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            responsive: true,
            order: [[0, 'asc']], // Ordenar por ID (columna 0) ascendente por defecto
            columnDefs: [
                { orderable: false, targets: [5] }, // Columna de acciones (ahora es la columna 5)
                { className: "text-center", targets: [0, 2, 3, 5] }, // ID, Abreviación, Estado, Acciones
                { className: "text-nowrap", targets: [4] } // Fecha creación
            ]
        });
        
        
        
        // Verificar que el DataTable se aplicó correctamente
        setTimeout(function() {
            
            
            
            
            
        }, 1000);
        
    } catch (error) {
        console.error('Error al inicializar DataTable:', error);
    }

    // Inicializar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Reinicializar tooltips después de cada redibujado de la tabla
    if (typeof table !== 'undefined') {
        table.on('draw', function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    }

    // Resto de funciones CRUD...
    
    // Manejar envío del formulario de agregar
    $('#addSeccionForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#saveSeccionBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        clearValidationErrors();
        clearAlerts('add-alert-container');
        
        $.ajax({
            url: '{{ route("secciones-reunion.store") }}',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addSeccionModal').modal('hide');
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
                    const message = response.message || 'Error al crear la sección.';
                    showAlert('danger', message, 'add-alert-container');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Manejar clic en botón Editar
    $(document).on('click', '.edit-seccion', function() {
        const seccionId = $(this).data('seccion-id');
        
        $('#editSeccionForm')[0].reset();
        clearValidationErrors();
        clearAlerts('edit-alert-container');
        
        $.ajax({
            url: `/secciones-reunion/${seccionId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const seccion = response.seccion;
                    
                    $('#edit_seccion_id').val(seccion.id);
                    $('#edit_nombre').val(seccion.nombre);
                    $('#edit_abreviacion').val(seccion.abreviacion);
                    $('#edit_estado').val(seccion.estado);
                    
                    $('#edit_creado_por').text(seccion.creado_por_nombre || 'N/A');
                    $('#edit_creado_fecha').text(seccion.creado_por_timestamp || 'N/A');
                    $('#edit_modificado_por').text(seccion.modificado_por_nombre || 'N/A');
                    $('#edit_modificado_fecha').text(seccion.modificado_por_timestamp || 'N/A');
                    
                    $('#editSeccionModal').modal('show');
                } else {
                    showAlert('danger', response.message || 'Error al cargar los datos.');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error al cargar los datos';
                showAlert('danger', message);
            }
        });
    });

    // Manejar envío del formulario de editar
    $('#editSeccionForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const seccionId = $('#edit_seccion_id').val();
        const submitBtn = $('#updateSeccionBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        clearValidationErrors();
        clearAlerts('edit-alert-container');
        
        $.ajax({
            url: `/secciones-reunion/${seccionId}`,
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editSeccionModal').modal('hide');
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
                    const message = response.message || 'Error al actualizar la sección.';
                    showAlert('danger', message, 'edit-alert-container');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Manejar clic en botón Eliminar
    $(document).on('click', '.delete-seccion', function() {
        const seccionId = $(this).data('seccion-id');
        const seccionNombre = $(this).data('seccion-nombre');
        
        if (confirm(`¿Está seguro de eliminar la sección "${seccionNombre}"?`)) {
            $.ajax({
                url: `/secciones-reunion/${seccionId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Error al eliminar la sección';
                    showAlert('danger', message);
                }
            });
        }
    });

    // Funciones auxiliares
    function showAlert(type, message, containerId = 'alert-container') {
        const alertContainer = $('#' + containerId);
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

    function clearAlerts(containerId) {
        $('#' + containerId).empty();
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

    // Limpiar formularios al cerrar modales
    $('#addSeccionModal').on('hidden.bs.modal', function() {
        $('#addSeccionForm')[0].reset();
        clearValidationErrors();
        clearAlerts('add-alert-container');
        $('#saveSeccionBtn .spinner-border').addClass('d-none');
        $('#saveSeccionBtn').prop('disabled', false);
    });

    $('#editSeccionModal').on('hidden.bs.modal', function() {
        $('#editSeccionForm')[0].reset();
        clearValidationErrors();
        clearAlerts('edit-alert-container');
        $('#updateSeccionBtn .spinner-border').addClass('d-none');
        $('#updateSeccionBtn').prop('disabled', false);
    });
});


</script>
@endsection