$(document).ready(function() {
    // Inicializar DataTable
    let informesTable = $('#informesTable').DataTable({
        responsive: true,
        language: {
            url: '/js/datatables-es-ES.json'
        },
        columnDefs: window.informesIndexConfig.datatablesColumnDefs,
        order: [[1, 'desc'], [2, 'desc']], // Ordenar por año y mes descendente
        pageLength: 25,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center"f>>rtip',
        drawCallback: function() {
            // Reinicializar tooltips después de cada redibujado
            $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
        }
    });

    // Aplicar filtros de búsqueda avanzada
    $('#anioFilter, #mesFilter, #grupoFilter, #servicioFilter').on('change', function() {
        applyAdvancedFilters();
    });

    // Función para aplicar filtros avanzados
    function applyAdvancedFilters() {
        let anio = $('#anioFilter').val();
        let mes = $('#mesFilter').val();
        let grupo = $('#grupoFilter').val();
        let servicio = $('#servicioFilter').val();

        // Aplicar filtros a la tabla
        informesTable
            .columns(1).search(anio) // Año (columna 1)
            .columns(2).search(mes ? (['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'][parseInt(mes)] || '') : '') // Mes (columna 2)
            .columns(3).search(grupo) // Grupo (columna 3)
            .columns(6).search(servicio) // Servicio (columna 6)
            .draw();
    }

    // Limpiar errores de validación
    function clearValidationErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').empty();
    }

    // Limpiar errores sobre las pestañas
    function clearErrorsAboveTabs(prefix) {
        $(`#${prefix}InformeErrorContainer`).addClass('d-none');
        $(`#${prefix}InformeErrorList`).empty();
    }

    // Mostrar errores de validación
    function showValidationErrors(errors, prefix = '') {
        clearValidationErrors();

        let errorList = [];
        let hasFieldErrors = false;

        $.each(errors, function(field, messages) {
            let fieldId = prefix ? `${prefix}_${field}` : field;
            let $field = $(`#${fieldId}`);

            if ($field.length) {
                hasFieldErrors = true;
                $field.addClass('is-invalid');
                $field.siblings('.invalid-feedback').html(messages[0]);
            }

            // Agregar a la lista de errores general
            $.each(messages, function(index, message) {
                errorList.push(`<li>${message}</li>`);
            });
        });

        // Mostrar errores sobre las pestañas si hay errores
        if (errorList.length > 0) {
            let containerPrefix = prefix || 'add';
            $(`#${containerPrefix}InformeErrorContainer`).removeClass('d-none');
            $(`#${containerPrefix}InformeErrorList`).html(errorList.join(''));
        }
    }

    // Mostrar alerta
    function showAlert(message, type = 'success') {
        let alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        let icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

        let alert = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        $('#alert-container').html(alert);

        // Auto-ocultar después de 5 segundos
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }

    // Limpiar formulario
    function clearForm(formId) {
        $(`#${formId}`)[0].reset();
        clearValidationErrors();
        clearErrorsAboveTabs(formId.replace('Form', '').replace('Informe', ''));
    }

    // Formatear nombre de mes
    function getMonthName(monthNumber) {
        const months = [
            '', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        return months[parseInt(monthNumber)] || monthNumber;
    }

    // Función para filtrar usuarios por grupo
    function filterUsersByGroup(grupoId, targetSelectId) {
        const $userSelect = $(`#${targetSelectId}`);
        const currentUserId = $userSelect.val(); // Preservar selección actual si es posible

        // Limpiar opciones excepto la primera
        $userSelect.find('option:not(:first)').remove();

        if (!grupoId) {
            // Si no hay grupo seleccionado, no mostrar usuarios
            // Solo mantener la opción por defecto "Seleccionar publicador..."
            return;
        }

        // Mostrar indicador de carga
        $userSelect.append('<option value="" disabled>Cargando publicadores...</option>');
        $userSelect.prop('disabled', true);

        // Hacer petición AJAX para obtener usuarios del grupo
        $.ajax({
            url: window.informesIndexConfig.usuariosPorGrupoRoute,
            type: 'GET',
            data: { grupo_id: grupoId },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                // Limpiar opciones de carga
                $userSelect.find('option:not(:first)').remove();

                if (response.success && response.usuarios) {
                    response.usuarios.forEach(function(usuario) {
                        const selected = usuario.id == currentUserId ? 'selected' : '';
                        $userSelect.append(`<option value="${usuario.id}" ${selected}>${usuario.name}</option>`);
                    });

                    // Si no hay usuarios en el grupo
                    if (response.usuarios.length === 0) {
                        $userSelect.append('<option value="" disabled>No hay publicadores en este grupo</option>');
                    }
                } else {
                    $userSelect.append('<option value="" disabled>Error al cargar publicadores</option>');
                    showAlert(response.message || 'Error al cargar usuarios del grupo', 'error');
                }
            },
            error: function(xhr) {
                // Limpiar opciones de carga
                $userSelect.find('option:not(:first)').remove();
                $userSelect.append('<option value="" disabled>Error al cargar publicadores</option>');
                showAlert('Error al cargar usuarios del grupo', 'error');
            },
            complete: function() {
                // Rehabilitar el select
                $userSelect.prop('disabled', false);
            }
        });
    }

    // Evento para filtrar usuarios cuando cambia el grupo (modal agregar)
    $('#grupo_id').on('change', function() {
        const grupoId = $(this).val();
        filterUsersByGroup(grupoId, 'user_id');
    });

    // Evento al abrir modal de agregar
    $('#addInformeModal').on('shown.bs.modal', function() {
        clearForm('addInformeForm');
        // Preseleccionar el año actual
        $('#anio').val(new Date().getFullYear());
        // No cargar usuarios hasta que se seleccione un grupo
        // Los usuarios se cargarán dinámicamente cuando se seleccione un grupo
    });

    // Evento al abrir modal de editar
    $('#editInformeModal').on('shown.bs.modal', function() {
        // No limpiar el formulario en el modal de editar porque los datos
        // ya se cargan desde el AJAX del botón editar
        // Solo enfocar el primer campo editable
        $('#edit_servicio_id').focus();
    });

    // Manejar formulario de agregar informe
    $('#addInformeForm').on('submit', function(e) {
        e.preventDefault();

        // Validar formulario antes de enviar
        if (!validateForm('addInformeForm')) {
            return false;
        }

        clearValidationErrors();
        clearErrorsAboveTabs('add');

        let $submitBtn = $('#saveInformeBtn');
        let $spinner = $submitBtn.find('.spinner-border');

        // Mostrar spinner
        $spinner.removeClass('d-none');
        $submitBtn.prop('disabled', true);

        // Obtener datos del formulario
        let formData = new FormData(this);
        let csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Agregar token CSRF manualmente por si acaso
        formData.append('_token', csrfToken);

        $.ajax({
            url: window.informesIndexConfig.storeRoute,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    $('#addInformeModal').modal('hide');
                    showAlert(response.message, 'success');
                    location.reload(); // Recargar para mostrar los nuevos datos
                } else {
                    showAlert(response.message || 'Error al crear el informe', 'error');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    showValidationErrors(errors, '');
                } else if (xhr.status === 419) {
                    showAlert('Token CSRF expirado. Por favor, recarga la página.', 'error');
                } else {
                    let message = xhr.responseJSON?.message || xhr.statusText || 'Error interno del servidor';
                    showAlert(message, 'error');
                }
            },
            complete: function() {
                // Ocultar spinner
                $spinner.addClass('d-none');
                $submitBtn.prop('disabled', false);
            }
        });
    });

    // Manejar clic en ver informe
    $(document).on('click', '.view-informe', function() {
        let informeId = $(this).data('informe-id');

        $.ajax({
            url: window.informesIndexConfig.showRoute.replace(':id', informeId),
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let informe = response.informe;

                    // Llenar los campos del modal
                    $('#view_anio').text(informe.anio);
                    $('#view_mes').text(informe.nombre_mes);
                    $('#view_usuario').text(informe.usuario_nombre);
                    $('#view_grupo').text(informe.grupo_nombre);
                    $('#view_servicio').text(informe.servicio_nombre);
                    $('#view_participa').text(informe.participa_texto);
                    $('#view_cantidad_estudios').text(informe.cantidad_estudios || '0');
                    $('#view_horas').text(informe.horas || '-');
                    $('#view_comentario').text(informe.comentario || '-');

                    if ($('#view_congregacion').length) {
                        $('#view_congregacion').text(informe.congregacion_nombre);
                    }

                    $('#viewInformeModal').modal('show');
                } else {
                    showAlert(response.message || 'Error al cargar el informe', 'error');
                }
            },
            error: function(xhr) {
                let message = xhr.responseJSON?.message || 'Error al cargar el informe';
                showAlert(message, 'error');
            }
        });
    });

    // Manejar clic en editar informe
    $(document).on('click', '.edit-informe', function() {
        let informeId = $(this).data('informe-id');

        $.ajax({
            url: window.informesIndexConfig.editRoute.replace(':id', informeId),
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let informe = response.informe;

                    // Array de nombres de meses
                    const meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                                  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

                    // Llenar los campos del formulario de edición
                    $('#edit_informe_id').val(informe.id);

                    // Campos deshabilitados (solo lectura) con valores ocultos
                    $('#edit_anio_display').val(informe.anio);
                    $('#edit_anio').val(informe.anio);

                    $('#edit_mes_display').val(meses[informe.mes] || informe.mes);
                    $('#edit_mes').val(informe.mes);

                    $('#edit_grupo_display').val(informe.grupo ? informe.grupo.nombre : 'Sin grupo');
                    $('#edit_grupo_id').val(informe.grupo_id);

                    $('#edit_user_display').val(informe.usuario ? informe.usuario.name : 'Sin usuario');
                    $('#edit_user_id').val(informe.user_id);

                    // Campos editables
                    $('#edit_servicio_id').val(informe.servicio_id);
                    $('#edit_participa').val(informe.participa ? '1' : '0');
                    $('#edit_cantidad_estudios').val(informe.cantidad_estudios);
                    $('#edit_horas').val(informe.horas);
                    $('#edit_comentario').val(informe.comentario);

                    $('#editInformeModal').modal('show');
                } else {
                    showAlert(response.message || 'Error al cargar el informe', 'error');
                }
            },
            error: function(xhr) {
                let message = xhr.responseJSON?.message || 'Error al cargar el informe';
                showAlert(message, 'error');
            }
        });
    });

    // Manejar formulario de editar informe
    $('#editInformeForm').on('submit', function(e) {
        e.preventDefault();

        // Validar formulario antes de enviar
        if (!validateForm('editInformeForm')) {
            return false;
        }

        clearValidationErrors();
        clearErrorsAboveTabs('edit');

        let $submitBtn = $('#updateInformeBtn');
        let $spinner = $submitBtn.find('.spinner-border');
        let informeId = $('#edit_informe_id').val();

        // Mostrar spinner
        $spinner.removeClass('d-none');
        $submitBtn.prop('disabled', true);

        let formData = $(this).serialize();

        $.ajax({
            url: window.informesIndexConfig.updateRoute.replace(':id', informeId),
            type: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#editInformeModal').modal('hide');
                    showAlert(response.message, 'success');
                    
                    // Actualizar la fila en DataTable sin recargar la página
                    if (response.data && informesTable) {
                        updateTableRow(response.data);
                    }
                } else {
                    showAlert(response.message || 'Error al actualizar el informe', 'error');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    showValidationErrors(errors, 'edit');
                } else {
                    let message = xhr.responseJSON?.message || 'Error interno del servidor';
                    showAlert(message, 'error');
                }
            },
            complete: function() {
                // Ocultar spinner
                $spinner.addClass('d-none');
                $submitBtn.prop('disabled', false);
            }
        });
    });

    // Manejar clic en eliminar informe
    $(document).on('click', '.delete-informe', function() {
        let informeId = $(this).data('informe-id');
        let informeName = $(this).data('informe-name');

        $('#deleteInformeName').text(informeName);
        $('#deleteInformeModal').modal('show');

        // Guardar el ID del informe para usar en la confirmación
        $('#confirmDeleteInformeBtn').data('informe-id', informeId);
    });

    // Confirmar eliminación de informe
    $('#confirmDeleteInformeBtn').on('click', function() {
        let informeId = $(this).data('informe-id');
        let $deleteBtn = $(this);
        let $spinner = $deleteBtn.find('.spinner-border');

        // Mostrar spinner
        $spinner.removeClass('d-none');
        $deleteBtn.prop('disabled', true);

        $.ajax({
            url: window.informesIndexConfig.destroyRoute.replace(':id', informeId),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteInformeModal').modal('hide');
                    showAlert(response.message, 'success');

                    // Remover la fila de la tabla
                    $(`tr[data-informe-id="${informeId}"]`).fadeOut(300, function() {
                        informesTable.row($(this)).remove().draw();
                    });
                } else {
                    showAlert(response.message || 'Error al eliminar el informe', 'error');
                }
            },
            error: function(xhr) {
                let message = xhr.responseJSON?.message || 'Error al eliminar el informe';
                showAlert(message, 'error');
            },
            complete: function() {
                // Ocultar spinner
                $spinner.addClass('d-none');
                $deleteBtn.prop('disabled', false);
            }
        });
    });

    // Inicializar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Limpiar formularios al cerrar modales
    $('#addInformeModal, #editInformeModal').on('hidden.bs.modal', function() {
        let modalId = $(this).attr('id');
        let formId = modalId.replace('Modal', 'Form');
        clearForm(formId);
    });

    // Manejar validación en tiempo real
    $('#addInformeForm input, #addInformeForm select, #editInformeForm input, #editInformeForm select').on('change blur', function() {
        if ($(this).hasClass('is-invalid')) {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').empty();
        }
    });

    // Funcionalidad de búsqueda rápida personalizada
    $('#informesTable_filter input').on('keyup', function() {
        let searchTerm = this.value.toLowerCase();

        // Si hay término de búsqueda, limpiar filtros avanzados
        if (searchTerm.length > 0) {
            $('#anioFilter, #mesFilter, #usuarioFilter, #grupoFilter, #servicioFilter, #participaFilter, #congregacionFilter').val('');
        }
    });

    // Resetear filtros avanzados al limpiar búsqueda
    $('#informesTable_filter input').on('keyup', function() {
        if (this.value === '') {
            $('#anioFilter, #mesFilter, #usuarioFilter, #grupoFilter, #servicioFilter, #participaFilter, #congregacionFilter').val('');
            informesTable.search('').columns().search('').draw();
        }
    });

    // Validaciones adicionales del formulario
    function validateForm(formId) {
        let isValid = true;
        let $form = $(`#${formId}`);

        // Validar campos requeridos
        $form.find('[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                $(this).siblings('.invalid-feedback').text('Este campo es requerido.');
                isValid = false;
            }
        });

        // Validación específica para números
        $form.find('input[type="number"]').each(function() {
            let value = $(this).val();
            let min = $(this).attr('min');

            if (value && min && parseFloat(value) < parseFloat(min)) {
                $(this).addClass('is-invalid');
                $(this).siblings('.invalid-feedback').text(`El valor mínimo es ${min}.`);
                isValid = false;
            }
        });

        return isValid;
    }

    // Función para exportar datos (para uso futuro)
    window.exportInformes = function(format) {
        // Esta función puede ser implementada más tarde para exportar datos
        // TODO: Implementar exportación de informes
    };

    // Función para actualizar una fila de la tabla sin recargar
    function updateTableRow(data) {
        // Obtener meses
        const meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        
        // Buscar la fila en DataTable
        let rowNode = informesTable.rows().nodes().toArray().find(function(node) {
            return $(node).find('.edit-informe').data('informe-id') == data.id;
        });

        if (rowNode) {
            let $row = $(rowNode);
            let columns = [];
            
            // ID
            columns.push(data.id);
            
            // Año
            columns.push(data.anio);
            
            // Mes
            columns.push(meses[data.mes] || data.mes);
            
            // Grupo
            columns.push(`<span class="badge bg-dark">${data.grupo_nombre || ''}</span>`);
            
            // Usuario
            columns.push(data.usuario_nombre || '');
            
            // Participa
            if (data.participa) {
                columns.push('<span class="badge bg-success">Sí</span>');
            } else {
                columns.push('<span class="badge bg-danger">No</span>');
            }
            
            // Servicio
            columns.push(`<span class="badge bg-warning text-dark">${data.servicio_nombre || ''}</span>`);
            
            // Congregación (solo para admin/supervisor)
            if (window.informesIndexConfig.isAdmin || window.informesIndexConfig.isSupervisor) {
                columns.push(`<span class="badge bg-secondary">${data.congregacion_nombre || ''}</span>`);
            }
            
            // Acciones (mantener los botones existentes)
            let actionsHtml = `
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-info view-informe"
                            data-informe-id="${data.id}"
                            data-bs-toggle="tooltip"
                            title="Ver informe">
                        <i class="fas fa-eye"></i>
                    </button>`;
            
            if (window.informesIndexConfig.canModify) {
                actionsHtml += `
                    <button type="button" class="btn btn-sm btn-warning edit-informe"
                            data-informe-id="${data.id}"
                            data-bs-toggle="tooltip"
                            title="Editar informe">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger delete-informe"
                            data-informe-id="${data.id}"
                            data-informe-name="${data.usuario_nombre} - ${meses[data.mes] || data.mes} ${data.anio}"
                            data-bs-toggle="tooltip"
                            title="Eliminar informe">
                        <i class="fas fa-trash"></i>
                    </button>`;
            }
            
            actionsHtml += `</div>`;
            columns.push(actionsHtml);
            
            // Actualizar la fila
            informesTable.row($row).data(columns).draw(false);
            
            // Reinicializar tooltips
            $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
        }
    }
});
// ============================================
// MODAL VER INFORMES POR GRUPO
// ============================================

// Cargar periodos al abrir el modal
$('#verInformesModal').on('show.bs.modal', function() {
    // Limpiar select de grupo
    $('#grupoFilterModal').val('');
    
    // Limpiar tabla
    $('#informesGrupoTableBody').empty();
    
    // Ocultar tabla y mostrar mensaje inicial
    $('#informesGrupoContainer').addClass('d-none');
    $('#noDataMessage').removeClass('d-none').html(
        '<i class="fas fa-info-circle me-2"></i>Seleccione un periodo y grupo para ver los informes.'
    );
    
    // Cargar periodos
    cargarPeriodos();
});

// Función para cargar periodos disponibles
function cargarPeriodos() {
    $.ajax({
        url: window.informesIndexConfig.periodosRoute,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let $select = $('#periodoFilterModal');
                $select.empty();
                $select.append('<option value="">Seleccione un periodo</option>');
                
                const meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                
                response.periodos.forEach(function(periodo) {
                    let mesNombre = meses[periodo.mes] || periodo.mes;
                    let texto = `${mesNombre} ${periodo.anio}`;
                    $select.append(`<option value="${periodo.anio}-${periodo.mes}">${texto}</option>`);
                });
                
                // Seleccionar el periodo más reciente (primer elemento después de "Seleccione...")
                if (response.periodos.length > 0) {
                    $select.val(`${response.periodos[0].anio}-${response.periodos[0].mes}`);
                }
            }
        },
        error: function(xhr) {
            console.error('Error al cargar periodos:', xhr);
        }
    });
}

// Cambios en los filtros del modal
$('#periodoFilterModal, #grupoFilterModal').on('change', function() {
    cargarInformesGrupo();
});

// Función para cargar informes por grupo
function cargarInformesGrupo() {
    let periodo = $('#periodoFilterModal').val();
    let grupoId = $('#grupoFilterModal').val();
    
    if (!periodo || !grupoId) {
        $('#informesGrupoContainer').addClass('d-none');
        $('#noDataMessage').removeClass('d-none');
        return;
    }
    
    // Separar año y mes del periodo
    let [anio, mes] = periodo.split('-');
    
    $.ajax({
        url: window.informesIndexConfig.informesPorGrupoRoute,
        type: 'GET',
        data: {
            grupo_id: grupoId,
            anio: anio,
            mes: mes
        },
        success: function(response) {
            if (response.success) {
                mostrarInformesGrupo(response.data);
            }
        },
        error: function(xhr) {
            console.error('Error al cargar informes:', xhr);
            showAlert('Error al cargar los informes', 'danger');
        }
    });
}

// Función para mostrar la tabla de informes
function mostrarInformesGrupo(data) {
    if (data.length === 0) {
        $('#informesGrupoContainer').addClass('d-none');
        $('#noDataMessage').removeClass('d-none').html(
            '<i class="fas fa-info-circle me-2"></i>No hay usuarios en el grupo seleccionado.'
        );
        return;
    }
    
    $('#noDataMessage').addClass('d-none');
    $('#informesGrupoContainer').removeClass('d-none');
    
    let tbody = $('#informesGrupoTableBody');
    tbody.empty();
    
    data.forEach(function(item) {
        let participaIcon = item.participa == 1 
            ? '<i class="fas fa-check-square text-success" title="Participa"></i>' 
            : '<i class="fas fa-times text-danger" title="No participa"></i>';
        
        let row = `
            <tr>
                <td>${item.nombre}</td>
                <td class="text-center">${participaIcon}</td>
                <td>${item.servicio_nombre}</td>
                <td class="text-center">${item.cantidad_estudios}</td>
                <td class="text-center">${item.horas}</td>
                <td>${item.comentario}</td>
            </tr>
        `;
        tbody.append(row);
    });
}

// ===== INFORME CONGREGACIÓN =====

// Cargar periodos para el modal de Informe Congregación
$('#informeCongregacionModal').on('show.bs.modal', function() {
    cargarPeriodosCongregacion();
});

// Función para cargar periodos en el selector
function cargarPeriodosCongregacion() {
    $.ajax({
        url: window.informesIndexConfig.periodosRoute,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let select = $('#periodoFilterCongregacion');
                select.empty();
                select.append('<option value="">Seleccione un periodo</option>');
                
                const meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                
                response.periodos.forEach(function(periodo) {
                    let mesNombre = meses[periodo.mes] || periodo.mes;
                    let valor = `${periodo.anio}-${periodo.mes}`;
                    let texto = `${mesNombre} ${periodo.anio}`;
                    select.append(`<option value="${valor}">${texto}</option>`);
                });
                
                // Seleccionar el periodo más reciente (primera opción después de "Seleccione...")
                if (response.periodos.length > 0) {
                    select.val(`${response.periodos[0].anio}-${response.periodos[0].mes}`);
                    cargarEstadisticasCongregacion();
                }
            }
        },
        error: function() {
            console.error('Error al cargar periodos');
        }
    });
}

// Evento change para el selector de periodo
$('#periodoFilterCongregacion').on('change', function() {
    cargarEstadisticasCongregacion();
});

// Función para cargar estadísticas de congregación
function cargarEstadisticasCongregacion() {
    let periodo = $('#periodoFilterCongregacion').val();
    
    if (!periodo) {
        $('#congregacionStatsContainer').addClass('d-none');
        $('#noDataCongregacionMessage').removeClass('d-none');
        $('#loadingCongregacionStats').addClass('d-none');
        return;
    }
    
    // Dividir periodo en año y mes
    let [anio, mes] = periodo.split('-');
    
    // Mostrar indicador de carga
    $('#loadingCongregacionStats').removeClass('d-none');
    $('#congregacionStatsContainer').addClass('d-none');
    $('#noDataCongregacionMessage').addClass('d-none');
    
    // Realizar petición AJAX
    $.ajax({
        url: window.informesIndexConfig.informeCongregacionRoute,
        method: 'GET',
        data: {
            anio: anio,
            mes: mes
        },
        success: function(response) {
            $('#loadingCongregacionStats').addClass('d-none');
            
            if (response.success) {
                // Actualizar las estadísticas
                $('#stat_usuarios_activos').text(response.data.usuarios_activos);
                $('#stat_usuarios_inactivos').text(response.data.usuarios_inactivos);
                $('#stat_publicadores').text(response.data.publicadores);
                $('#stat_estudios_publicadores').text(response.data.estudios_publicadores);
                $('#stat_precursores_auxiliares').text(response.data.precursores_auxiliares);
                $('#stat_estudios_precursores_auxiliares').text(response.data.estudios_precursores_auxiliares);
                $('#stat_precursores_regulares').text(response.data.precursores_regulares);
                $('#stat_estudios_precursores_regulares').text(response.data.estudios_precursores_regulares);
                
                // Mostrar contenedor de estadísticas
                $('#congregacionStatsContainer').removeClass('d-none');
                $('#noDataCongregacionMessage').addClass('d-none');
            } else {
                $('#noDataCongregacionMessage').removeClass('d-none').html(
                    '<i class="fas fa-exclamation-circle me-2"></i>' + (response.message || 'No se pudieron cargar las estadísticas.')
                );
            }
        },
        error: function(xhr) {
            $('#loadingCongregacionStats').addClass('d-none');
            $('#noDataCongregacionMessage').removeClass('d-none').html(
                '<i class="fas fa-exclamation-triangle me-2"></i>Error al cargar las estadísticas.'
            );
        }
    });
}
