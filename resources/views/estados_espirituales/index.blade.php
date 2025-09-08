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
                        <div class="col-md-6">
                            <h3 class="mb-0">
                                <i class="fas fa-heart me-2"></i>Gestión de Estados Espirituales
                            </h3>
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
                                @if(Auth::user()->isAdmin())
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEstadoEspiritualModal">
                                    <i class="fas fa-plus me-2"></i>Agregar Estado Espiritual
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="estadosEspiritualesTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Estado</th>
                                    <th>Fecha de Creación</th>
                                    @if(Auth::user()->isAdmin())
                                    <th>Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estados_espirituales as $estado)
                                <tr data-estado-id="{{ $estado->id }}">
                                    <td>{{ $estado->id }}</td>
                                    <td>{{ $estado->nombre }}</td>
                                    <td>
                                        @if($estado->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $estado->created_at->format('d/m/Y H:i') }}</td>
                                    @if(Auth::user()->isAdmin())
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-warning edit-estado"
                                                    data-estado-id="{{ $estado->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Editar estado espiritual">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-estado"
                                                    data-estado-id="{{ $estado->id }}"
                                                    data-estado-name="{{ $estado->nombre }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Eliminar estado espiritual">
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

<!-- Modal para Agregar Estado Espiritual -->
<div class="modal fade" id="addEstadoEspiritualModal" tabindex="-1" aria-labelledby="addEstadoEspiritualModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEstadoEspiritualModalLabel">
                    <i class="fas fa-heart me-2"></i>Agregar Nuevo Estado Espiritual
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addEstadoEspiritualForm">
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
                    <button type="submit" class="btn btn-primary" id="saveEstadoEspiritualBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Estado Espiritual
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Estado Espiritual -->
<div class="modal fade" id="editEstadoEspiritualModal" tabindex="-1" aria-labelledby="editEstadoEspiritualModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEstadoEspiritualModalLabel">
                    <i class="fas fa-heart me-2"></i>Editar Estado Espiritual
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEstadoEspiritualForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_estado_id" name="estado_id">
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
                    <button type="submit" class="btn btn-primary" id="updateEstadoEspiritualBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Estado Espiritual
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Confirmar Eliminación -->
<div class="modal fade" id="deleteEstadoEspiritualModal" tabindex="-1" aria-labelledby="deleteEstadoEspiritualModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEstadoEspiritualModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el estado espiritual <strong id="deleteEstadoEspiritualName"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    Eliminar Estado Espiritual
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var table = $('#estadosEspiritualesTable').DataTable({
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        order: [[0, 'asc']] // Cambio de 'desc' a 'asc' para ordenar por ID ascendente
    });

    // Filtro por estado
    let currentEstadoFilterEstadosEspirituales = null;

    $('#estadoFilter').on('change', function() {
        const selectedEstado = $(this).val();
        
        // Limpiar filtro anterior si existe
        if (currentEstadoFilterEstadosEspirituales !== null) {
            $.fn.dataTable.ext.search.splice($.fn.dataTable.ext.search.indexOf(currentEstadoFilterEstadosEspirituales), 1);
        }
        
        if (selectedEstado === '') {
            currentEstadoFilterEstadosEspirituales = null;
            table.draw();
        } else {
            // Mapear valores numéricos a textos para la búsqueda
            const textoEstado = selectedEstado === '1' ? 'Activo' : 'Inactivo';
            
            // Crear nueva función de filtro
            currentEstadoFilterEstadosEspirituales = function(settings, data, dataIndex) {
                if (settings.nTable !== table.table().node()) {
                    return true;
                }
                const estadoColumn = data[2]; // Columna 2 es el estado
                return estadoColumn.indexOf(textoEstado) !== -1;
            };
            
            // Agregar el nuevo filtro
            $.fn.dataTable.ext.search.push(currentEstadoFilterEstadosEspirituales);
            table.draw();
        }
    });

    function showAlert(type, message) {
        const alertContainer = $('#alert-container');
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        alertContainer.html(alertHtml);
        
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
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

    let estadoToDelete = null;

    $('#addEstadoEspiritualModal').on('hidden.bs.modal', function() {
        $('#addEstadoEspiritualForm')[0].reset();
        clearValidationErrors();
        $('#saveEstadoEspiritualBtn .spinner-border').addClass('d-none');
        $('#saveEstadoEspiritualBtn').prop('disabled', false);
    });

    $('#addEstadoEspiritualForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#saveEstadoEspiritualBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        // Verificación adicional del valor del estado
        const estadoValue = $('#estado').val();
        
        
        // Validación manual del estado
        if (estadoValue === '' || estadoValue === null || estadoValue === undefined) {
            showAlert('danger', 'Por favor seleccione un estado válido.');
            return;
        }
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        // Preparar datos manualmente para asegurar valores correctos
        const formData = {
            _token: $('input[name="_token"]').val(),
            nombre: $('#nombre').val(),
            estado: estadoValue
        };
        
        
        
        $.ajax({
            url: '{{ route("estados-espirituales.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                
                if (response.success) {
                    $('#addEstadoEspiritualModal').modal('hide');
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
                    const message = response.message || 'Error al crear el estado espiritual. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    $(document).on('click', '.edit-estado', function() {
        const estadoId = $(this).data('estado-id');
        
        $('#editEstadoEspiritualForm')[0].reset();
        clearValidationErrors();
        
        $.ajax({
            url: `/estados-espirituales/${estadoId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const estado = response.estado_espiritual;
                    
                    $('#edit_estado_id').val(estado.id);
                    $('#edit_nombre').val(estado.nombre);
                    $('#edit_estado').val(estado.estado.toString());
                    
                    $('#editEstadoEspiritualModal').modal('show');
                } else {
                    showAlert('danger', response.message || 'Error al cargar los datos del estado espiritual.');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response?.message || 'Error al cargar los datos del estado espiritual.';
                showAlert('danger', message);
            }
        });
    });

    $('#editEstadoEspiritualModal').on('hidden.bs.modal', function() {
        $('#editEstadoEspiritualForm')[0].reset();
        clearValidationErrors();
        $('#updateEstadoEspiritualBtn .spinner-border').addClass('d-none');
        $('#updateEstadoEspiritualBtn').prop('disabled', false);
    });

    $('#editEstadoEspiritualForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const estadoId = $('#edit_estado_id').val();
        const submitBtn = $('#updateEstadoEspiritualBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        // Verificación adicional del valor del estado
        const estadoValue = $('#edit_estado').val();
        
        
        // Validación manual del estado
        if (estadoValue === '' || estadoValue === null || estadoValue === undefined) {
            showAlert('danger', 'Por favor seleccione un estado válido.');
            return;
        }
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        clearValidationErrors();
        
        // Preparar datos manualmente para asegurar valores correctos
        const formData = {
            _token: $('input[name="_token"]').val(),
            _method: 'PUT',
            nombre: $('#edit_nombre').val(),
            estado: estadoValue
        };
        
        
        
        $.ajax({
            url: `/estados-espirituales/${estadoId}`,
            method: 'POST', // Usamos POST con _method PUT
            data: formData,
            success: function(response) {
                
                if (response.success) {
                    $('#editEstadoEspiritualModal').modal('hide');
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
                    const message = response.message || 'Error al actualizar el estado espiritual. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    $(document).on('click', '.delete-estado', function() {
        estadoToDelete = $(this).data('estado-id');
        const estadoName = $(this).data('estado-name');
        
        $('#deleteEstadoEspiritualName').text(estadoName);
        $('#deleteEstadoEspiritualModal').modal('show');
    });

    $('#confirmDeleteBtn').on('click', function() {
        if (!estadoToDelete) return;
        
        const submitBtn = $(this);
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: `/estados-espirituales/${estadoToDelete}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteEstadoEspiritualModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response?.message || 'Error al eliminar el estado espiritual. Intente nuevamente.';
                showAlert('danger', message);
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
                estadoToDelete = null;
            }
        });
    });
});
</script>
@endsection