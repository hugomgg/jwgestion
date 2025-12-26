$(document).ready(function() {
    // Inicializar DataTable con AJAX
    var table = $('#gruposTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: window.gruposIndexConfig.dataRoute,
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'id',
                width: '5%'
            },
            { 
                data: 'nombre',
                width: '25%'
            },
            { 
                data: 'congregacion',
                width: '20%',
                render: function(data, type, row) {
                    return `<span class="badge bg-primary">${data}</span>`;
                }
            },
            { 
                data: 'estado',
                width: '15%',
                render: function(data, type, row) {
                    if (data == 1) {
                        return '<span class="badge bg-success">Habilitado</span>';
                    } else {
                        return '<span class="badge bg-danger">Deshabilitado</span>';
                    }
                }
            },
            { 
                data: 'usuarios_count',
                width: '15%',
                render: function(data, type, row) {
                    return `<span class="badge bg-info">${data}</span>`;
                }
            },
            { 
                data: null,
                width: '20%',
                orderable: false,
                render: function(data, type, row) {
                    let buttons = `
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-info view-grupo"
                                    data-grupo-id="${row.id}"
                                    data-bs-toggle="tooltip"
                                    title="Ver grupo">
                                <i class="fas fa-eye"></i>
                            </button>`;
                    
                    if (window.gruposIndexConfig.canModify) {
                        buttons += `
                            <button type="button" class="btn btn-sm btn-warning edit-grupo"
                                    data-grupo-id="${row.id}"
                                    data-bs-toggle="tooltip"
                                    title="Editar grupo">
                                <i class="fas fa-edit"></i>
                            </button>`;
                    }
                    
                    buttons += '</div>';
                    return buttons;
                }
            }
        ],
        language: {
            url: '/js/datatables-es-ES.json'
        },
        order: [[1, 'asc']], // Ordenar por nombre
        drawCallback: function() {
            // Reinicializar tooltips después de cada dibujado
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
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
            const textoEstado = selectedEstado === '1' ? 'Habilitado' : 'Deshabilitado';
            
            // Crear nueva función de filtro
            currentEstadoFilter = function(settings, data, dataIndex) {
                if (settings.nTable !== table.table().node()) {
                    return true;
                }
                const estadoColumn = data[3]; // Columna 3 es el estado (ahora que agregamos congregación)
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
            url: window.gruposIndexConfig.storeRoute,
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    $('#addGrupoModal').modal('hide');
                    form[0].reset();
                    // Recargar solo la DataTable sin recargar la página
                    table.ajax.reload(null, false);
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

    // Manejar clic en ver grupo (delegación de eventos)
    $(document).on('click', '.view-grupo', function() {
        const grupoId = $(this).data('grupo-id');
        
        $.ajax({
            url: `/grupos/${grupoId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const grupo = response.grupo;
                    $('#view_grupo_nombre').text(grupo.nombre);
                    $('#view_grupo_congregacion').text(grupo.congregacion ? grupo.congregacion.nombre : 'Sin asignar');
                    $('#view_grupo_estado').html(grupo.estado == 1 ? '<span class="badge bg-success">Habilitado</span>' : '<span class="badge bg-danger">Deshabilitado</span>');
                    $('#view_grupo_usuarios').text(grupo.usuarios_count);
                    $('#viewGrupoModal').modal('show');
                }
            },
            error: function() {
                showAlert('danger', 'Error al cargar los datos del grupo.');
            }
        });
    });

    // Manejar clic en editar grupo (delegación de eventos)
    $(document).on('click', '.edit-grupo', function() {
        const grupoId = $(this).data('grupo-id');
        
        $.ajax({
            url: `/grupos/${grupoId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const grupo = response.grupo;
                    $('#edit_grupo_id').val(grupo.id);
                    $('#edit_nombre').val(grupo.nombre);
                    $('#edit_congregacion_id').val(grupo.congregacion_id);
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
                    // Recargar solo la DataTable sin recargar la página
                    table.ajax.reload(null, false);
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

    // Limpiar modales cuando se cierren
    $('.modal').on('hidden.bs.modal', function() {
        clearValidationErrors();
        $(this).find('form')[0].reset();
        $(this).find('.spinner-border').addClass('d-none');
        $(this).find('button[type="submit"]').prop('disabled', false);
    });
});
