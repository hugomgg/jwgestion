$(document).ready(function() {
    // Verificar que el token CSRF esté disponible
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    if (!csrfToken) {
        console.error('CSRF token not found. Make sure the meta tag is present in the HTML.');
        return;
    }

    // Inicializar DataTable
    const programasTable = $('#programasTable').DataTable({
        language: {
            url: '/js/datatables-es-ES.json'
        },
        responsive: true,
        order: [[0, 'desc']], // Ordenar por fecha descendente
        columnDefs: [
            { targets: [7], orderable: false } // Columna de acciones no ordenable
        ]
    });

    // Guardar referencia a la tabla globalmente para el filtrado
    window.programasTable = programasTable;

    // Inicializar Select2 para filtros de año y mes
    initializeFiltrosSelect2();

    // Manejar clic en botón buscar
    $('#btnBuscarProgramas').on('click', function() {
        const anioSeleccionado = $('#filtro_anio').val();
        
        if (!anioSeleccionado) {
            mostrarAlerta('Por favor, seleccione un año para buscar.', 'warning');
            return;
        }
        
        buscarProgramasPorAnio(anioSeleccionado);
    });

    // Función para inicializar Select2 para coordinadores
    function initializeSelect2ForCoordinators() {
        $('#add_presidencia').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addProgramaModal'),
            placeholder: "Seleccionar presidencia...",
            allowClear: true,
            width: '100%'
        });

        $('#add_orador_inicial').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addProgramaModal'),
            placeholder: "Seleccionar orador inicial...",
            allowClear: true,
            width: '100%'
        });

        $('#add_orador_final').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addProgramaModal'),
            placeholder: "Seleccionar orador final...",
            allowClear: true,
            width: '100%'
        });

        // Inicializar Select2 para canciones
        $('#add_cancion_pre').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addProgramaModal'),
            placeholder: "Seleccionar canción inicial...",
            allowClear: true,
            width: '100%'
        });

        $('#add_cancion_en').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addProgramaModal'),
            placeholder: "Seleccionar canción intermedia...",
            allowClear: true,
            width: '100%'
        });

        $('#add_cancion_post').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addProgramaModal'),
            placeholder: "Seleccionar canción final...",
            allowClear: true,
            width: '100%'
        });
    }

    // Inicializar Select2 si es coordinador (esta función será llamada desde el Blade)
    window.initializeSelect2ForCoordinators = initializeSelect2ForCoordinators;

    // Manejar envío del formulario de agregar
    $('#addProgramaForm').submit(function(e) {
        e.preventDefault();

        const submitBtn = $('#addProgramaBtn');
        const spinner = submitBtn.find('.spinner-border');

        // Deshabilitar botón y mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#add-alert-container').empty();

        $.ajax({
            url: '/programas',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addProgramaModal').modal('hide');
                    location.reload();
                } else {
                    showAlert('add-alert-container', 'danger', response.message || 'Error al crear el programa');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        $(`#add_${field}`).addClass('is-invalid');
                        $(`#add_${field}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                } else if (xhr.status === 419) {
                    showAlert('add-alert-container', 'danger', 'Sesión expirada. Por favor, recarga la página e intenta nuevamente.');
                } else {
                    showAlert('add-alert-container', 'danger', 'Error al crear el programa');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Cargar datos para editar
    $('.edit-programa').click(function() {
        const programaId = $(this).data('id');
        const row = $(this).closest('tr');

        // Obtener datos de la fila
        const fecha = row.find('td:eq(0)').text().trim();
        const fechaFormatted = fecha.split('/').reverse().join('-'); // Convertir dd/mm/yyyy a yyyy-mm-dd

        $('#edit_programa_id').val(programaId);
        $('#edit_fecha').val(fechaFormatted);

        // Aquí podrías hacer una llamada AJAX para obtener todos los datos del programa
        // Por simplicidad, solo establecemos algunos valores por defecto
        $('#edit_estado').val('1');
    });

    // Manejar envío del formulario de editar
    $('#editProgramaForm').submit(function(e) {
        e.preventDefault();

        const programaId = $('#edit_programa_id').val();
        const submitBtn = $('#editProgramaBtn');
        const spinner = submitBtn.find('.spinner-border');

        // Deshabilitar botón y mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#edit-alert-container').empty();

        $.ajax({
            url: `/programas/${programaId}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editProgramaModal').modal('hide');
                    location.reload();
                } else {
                    showAlert('edit-alert-container', 'danger', response.message || 'Error al actualizar el programa');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                        for (const field in errors) {
                        $(`#edit_${field}`).addClass('is-invalid');
                        $(`#edit_${field}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                } else {
                    showAlert('edit-alert-container', 'danger', 'Error al actualizar el programa');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Variable para almacenar el ID del programa a eliminar
    let programaIdToDelete = null;

    // Manejar eliminación - abrir modal de confirmación
    $('.delete-programa').click(function() {
        programaIdToDelete = $(this).data('id');
    });

    // Manejar confirmación de eliminación
    $('#confirmDeleteBtn').click(function() {
        if (programaIdToDelete) {
            $.ajax({
                url: `/programas/${programaIdToDelete}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#confirmDeleteModal').modal('hide');
                        location.reload();
                    } else {
                        showAlert('alert-container', 'danger', response.message || 'Error al eliminar el programa');
                        $('#confirmDeleteModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error al eliminar el programa';
                    if (xhr.status === 419) {
                        errorMessage = 'Sesión expirada. Por favor, recarga la página e intenta nuevamente.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showAlert('alert-container', 'danger', errorMessage);
                    $('#confirmDeleteModal').modal('hide');
                }
            });
        }
    });

    // Limpiar el ID cuando se cierre el modal sin confirmar
    $('#confirmDeleteModal').on('hidden.bs.modal', function() {
        programaIdToDelete = null;
    });

    // Limpiar el ID cuando se abre el modal
    $('#confirmDeleteModal').on('show.bs.modal', function() {
        programaIdToDelete = null;
    });    // Función para mostrar alertas
    function showAlert(containerId, type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $(`#${containerId}`).html(alertHtml);
    }

    // Función para manejar eventos del modal para Select2 (será llamada desde el Blade)
    window.handleModalEventsForSelect2 = function() {
        // Cuando se abre el modal, asegurar que Select2 esté inicializado
        $('#addProgramaModal').on('shown.bs.modal', function() {
            if (!$('#add_presidencia').hasClass('select2-hidden-accessible')) {
                $('#add_presidencia').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#addProgramaModal'),
                    placeholder: "Seleccionar presidencia...",
                    allowClear: true,
                    width: '100%'
                });
            }

            if (!$('#add_orador_inicial').hasClass('select2-hidden-accessible')) {
                $('#add_orador_inicial').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#addProgramaModal'),
                    placeholder: "Seleccionar orador inicial...",
                    allowClear: true,
                    width: '100%'
                });
            }

            if (!$('#add_orador_final').hasClass('select2-hidden-accessible')) {
                $('#add_orador_final').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#addProgramaModal'),
                    placeholder: "Seleccionar orador final...",
                    allowClear: true,
                    width: '100%'
                });
            }

            // Inicializar Select2 para canciones
            if (!$('#add_cancion_pre').hasClass('select2-hidden-accessible')) {
                $('#add_cancion_pre').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#addProgramaModal'),
                    placeholder: "Seleccionar canción inicial...",
                    allowClear: true,
                    width: '100%'
                });
            }

            if (!$('#add_cancion_en').hasClass('select2-hidden-accessible')) {
                $('#add_cancion_en').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#addProgramaModal'),
                    placeholder: "Seleccionar canción intermedia...",
                    allowClear: true,
                    width: '100%'
                });
            }

            if (!$('#add_cancion_post').hasClass('select2-hidden-accessible')) {
                $('#add_cancion_post').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#addProgramaModal'),
                    placeholder: "Seleccionar canción final...",
                    allowClear: true,
                    width: '100%'
                });
            }
        });

        // Cuando se cierra el modal, limpiar Select2
        $('#addProgramaModal').on('hidden.bs.modal', function() {
            if ($('#add_presidencia').hasClass('select2-hidden-accessible')) {
                $('#add_presidencia').select2('destroy');
            }

            if ($('#add_orador_inicial').hasClass('select2-hidden-accessible')) {
                $('#add_orador_inicial').select2('destroy');
            }

            if ($('#add_orador_final').hasClass('select2-hidden-accessible')) {
                $('#add_orador_final').select2('destroy');
            }

            // Limpiar Select2 de canciones
            if ($('#add_cancion_pre').hasClass('select2-hidden-accessible')) {
                $('#add_cancion_pre').select2('destroy');
            }

            if ($('#add_cancion_en').hasClass('select2-hidden-accessible')) {
                $('#add_cancion_en').select2('destroy');
            }

            if ($('#add_cancion_post').hasClass('select2-hidden-accessible')) {
                $('#add_cancion_post').select2('destroy');
            }

            // Limpiar el formulario
            $('#addProgramaForm')[0].reset();
        });
    };

    // Función para manejar el estado de los botones de exportación
    function actualizarBotonesExportacion() {
        const anioSeleccionado = $('#filtro_anio').val();
        const mesesSeleccionados = $('#mesDropdownMenu input[type="checkbox"]:checked').map(function() {
            return $(this).val();
        }).get();
        const $btnPdf = $('#exportPdfBtn');
        const $btnProgramaXls = $('#exportProgramaXlsBtn');
        const $btnXls = $('#exportXlsBtn');
        const $btnAsignaciones = $('#exportAsignacionesBtn');

        if (anioSeleccionado && mesesSeleccionados.length > 0) {
            // Habilitar botones
            $btnPdf.removeClass('disabled').prop('disabled', false);
            $btnProgramaXls.removeClass('disabled').prop('disabled', false);
            $btnXls.removeClass('disabled').prop('disabled', false);
            $btnAsignaciones.removeClass('disabled').prop('disabled', false);

            // Guardar URLs en data attributes para uso posterior
            let baseUrl = "?anio=" + anioSeleccionado;
            mesesSeleccionados.forEach(function(mes) {
                baseUrl += "&mes[]=" + mes;
            });

            $btnPdf.data('export-url', window.exportRoutes.pdf + baseUrl);
            $btnProgramaXls.data('export-url', window.exportRoutes.programaXls + baseUrl);
            $btnXls.data('export-url', window.exportRoutes.xls + baseUrl);
            $btnAsignaciones.data('export-url', window.exportRoutes.asignaciones + baseUrl);
        } else {
            // Deshabilitar botones
            $btnPdf.addClass('disabled').prop('disabled', true);
            $btnProgramaXls.addClass('disabled').prop('disabled', true);
            $btnXls.addClass('disabled').prop('disabled', true);
            $btnAsignaciones.addClass('disabled').prop('disabled', true);
            
            $btnPdf.removeData('export-url');
            $btnProgramaXls.removeData('export-url');
            $btnXls.removeData('export-url');
            $btnAsignaciones.removeData('export-url');
        }
    }

    // Hacer la función disponible globalmente
    window.actualizarBotonesExportacion = actualizarBotonesExportacion;

    // Manejar clic en botones de exportación
    $('#exportPdfBtn, #exportProgramaXlsBtn, #exportXlsBtn, #exportAsignacionesBtn').on('click', function(e) {
        e.preventDefault();
        const url = $(this).data('export-url');
        if (url && !$(this).prop('disabled')) {
            window.open(url, '_blank');
        }
    });

    // Escuchar cambios en los filtros para actualizar los botones
    $('#filtro_anio').on('change', function() {
        actualizarBotonesExportacion();
    });

    // Escuchar cambios en los checkboxes de meses
    $(document).on('change', '#mesDropdownMenu input[type="checkbox"]', function() {
        actualizarBotonesExportacion();
    });

    // Inicializar estado de los botones
    actualizarBotonesExportacion();
});

// Función para inicializar los filtros de año y mes
function initializeFiltrosSelect2() {
    // Verificar que los elementos existan
    if (!$('#filtro_anio').length) {
        console.error('Elemento #filtro_anio no encontrado');
        return;
    }
    if (!$('#mesDropdownBtn').length) {
        console.error('Elemento #mesDropdownBtn no encontrado');
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Inicializar Select2 para el filtro de año
    $('#filtro_anio').select2({
        theme: 'bootstrap-5',
        placeholder: "Seleccionar año",
        allowClear: true,
        width: '120px'
    });

    // Cargar años disponibles (esto también seleccionará automáticamente el año más reciente)
    cargarAniosDisponibles();

    // Evento cuando cambia el año seleccionado
    $('#filtro_anio').on('change', function() {
        const anioSeleccionado = $(this).val();

        if (anioSeleccionado) {
            // Habilitar el dropdown de mes y cargar meses disponibles
            $('#mesDropdownBtn').prop('disabled', false);

            // Pequeño delay para evitar conflictos con el dropdown
            setTimeout(function() {
                cargarMesesDisponibles(anioSeleccionado);
            }, 100);
        } else {
            // Deshabilitar el dropdown de mes y limpiar selección
            $('#mesDropdownBtn').prop('disabled', true);
            limpiarSeleccionMeses();
        }

        // NO aplicar filtro automáticamente, solo con el botón buscar
        // aplicarFiltroTabla();

        // Actualizar estado del botón Exportar PDF
        if (window.actualizarBotonesExportacion) {
            window.actualizarBotonesExportacion();
        }
    });

    // Evento para manejar cambios en los checkboxes de meses
    $(document).on('change', '#mesDropdownMenu input[type="checkbox"]', function() {
        actualizarTextoBotonMeses();
        aplicarFiltroTabla();
        // Actualizar estado del botón Exportar PDF
        if (window.actualizarBotonesExportacion) {
            window.actualizarBotonesExportacion();
        }
    });

    // Evento para seleccionar todos los meses
    $(document).on('click', '#seleccionarTodosMeses', function(e) {
        e.stopPropagation();
        $('#mesDropdownMenu input[type="checkbox"]').prop('checked', true);
        actualizarTextoBotonMeses();
        aplicarFiltroTabla();
        // Actualizar estado del botón Exportar PDF
        if (window.actualizarBotonesExportacion) {
            window.actualizarBotonesExportacion();
        }
    });

    // Evento para limpiar selección de meses
    $(document).on('click', '#limpiarMeses', function(e) {
        e.stopPropagation();
        $('#mesDropdownMenu input[type="checkbox"]').prop('checked', false);
        actualizarTextoBotonMeses();
        aplicarFiltroTabla();
        // Actualizar estado del botón Exportar PDF
        if (window.actualizarBotonesExportacion) {
            window.actualizarBotonesExportacion();
        }
    });
}

// Función para cargar años disponibles
function cargarAniosDisponibles() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    if (!csrfToken) {
        console.error('CSRF token not found');
        mostrarAlerta('Error de configuración: Token CSRF no encontrado', 'danger');
        return;
    }

    $.ajax({
        url: '/programas/anios-disponibles',
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        success: function(response) {
            if (response && response.success) {
                const $selectAnio = $('#filtro_anio');

                // Limpiar opciones existentes excepto "Todos"
                $selectAnio.find('option:not([value=""])').remove();

                // Agregar años disponibles
                if (response.anios && Array.isArray(response.anios)) {
                    response.anios.forEach(function(anio) {
                        $selectAnio.append(`<option value="${anio}">${anio}</option>`);
                    });

                    // Seleccionar automáticamente el año más reciente si está disponible
                    if (window.anioMasReciente && response.anios.includes(window.anioMasReciente)) {
                        $selectAnio.val(window.anioMasReciente).trigger('change');
                        
                        // NO aplicamos filtro aquí porque los datos ya vienen filtrados del controlador
                    }
                }
            } else {
                const errorMessage = response && response.message ? response.message : 'Respuesta inválida del servidor';
                mostrarAlerta('Error al cargar años disponibles: ' + errorMessage, 'danger');
            }
        },
        error: function(xhr, status, error) {
            let errorMessage = 'Error al conectar con el servidor';
            if (xhr.status === 404) {
                errorMessage = 'Ruta no encontrada';
            } else if (xhr.status === 500) {
                errorMessage = 'Error interno del servidor';
            } else if (xhr.responseText) {
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    errorMessage = errorResponse.message || errorMessage;
                } catch (e) {
                    errorMessage = 'Error desconocido del servidor';
                }
            }

            mostrarAlerta(errorMessage, 'danger');
        }
    });
}

// Función para cargar meses disponibles para un año específico
function cargarMesesDisponibles(anio) {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    if (!csrfToken) {
        console.error('CSRF token not found');
        mostrarAlerta('Error de configuración: Token CSRF no encontrado', 'danger');
        return;
    }

    $.ajax({
        url: `/programas/meses-disponibles/${anio}`,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        success: function(response) {
            if (response && response.success) {
                const $dropdownMenu = $('#mesDropdownMenu');

                // Limpiar completamente el dropdown y reconstruirlo
                const originalContent = `
                    <li><hr class="dropdown-divider"></li>
                    <li class="px-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="seleccionarTodosMeses">Seleccionar Todos</button>
                    </li>
                    <li class="px-2 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="limpiarMeses">Limpiar Selección</button>
                    </li>
                `;

                $dropdownMenu.html(originalContent);

                // Agregar meses disponibles como checkboxes
                if (response.meses && Array.isArray(response.meses)) {
                    response.meses.forEach(function(mes) {
                        const checkboxHtml = `
                            <li class="px-2">
                                <div class="form-check">
                                    <input class="form-check-input mes-checkbox" type="checkbox" value="${mes.numero_mes}" id="mes_${mes.numero_mes}">
                                    <label class="form-check-label" for="mes_${mes.numero_mes}">
                                        ${mes.nombre}
                                    </label>
                                </div>
                            </li>
                        `;

                        // Insertar checkbox antes del primer botón
                        const $primerBoton = $dropdownMenu.find('button').first();

                        if ($primerBoton.length > 0) {
                            $primerBoton.closest('li').before(checkboxHtml);
                        } else {
                            // Fallback: insertar al final del dropdown
                            $dropdownMenu.append(checkboxHtml);
                        }
                    });
                }

                // Actualizar texto del botón
                actualizarTextoBotonMeses();

                // Actualizar estado del botón Exportar PDF
                if (window.actualizarBotonesExportacion) {
                    window.actualizarBotonesExportacion();
                }
            } else {
                const errorMessage = response && response.message ? response.message : 'Respuesta inválida del servidor';
                mostrarAlerta('Error al cargar meses disponibles: ' + errorMessage, 'danger');
            }
        },
        error: function(xhr, status, error) {
            let errorMessage = 'Error al conectar con el servidor';
            if (xhr.status === 404) {
                errorMessage = 'Ruta no encontrada';
            } else if (xhr.status === 500) {
                errorMessage = 'Error interno del servidor';
            } else if (xhr.responseText) {
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    errorMessage = errorResponse.message || errorMessage;
                } catch (e) {
                    errorMessage = 'Error desconocido del servidor';
                }
            }

            mostrarAlerta(errorMessage, 'danger');
        }
    });
}

// Función para aplicar filtro a la tabla DataTable
function aplicarFiltroTabla() {
    const anioSeleccionado = $('#filtro_anio').val();
    const mesesSeleccionados = $('#mesDropdownMenu input[type="checkbox"]:checked').map(function() {
        return $(this).val();
    }).get();

    // Si DataTable está inicializado
    let table = window.programasTable;
    
    if (!table && $.fn.DataTable.isDataTable('#programasTable')) {
        table = $('#programasTable').DataTable();
    }

    if (table) {
        // Remover filtros anteriores
        $.fn.dataTable.ext.search.pop();

        // Solo aplicar filtro si hay un año seleccionado
        if (anioSeleccionado) {
            // Agregar nuevo filtro
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const fechaPrograma = data[0]; // Primera columna es la fecha
                const fechaParts = fechaPrograma.split('/');

                if (fechaParts.length === 3) {
                    const dia = fechaParts[0];
                    const mes = fechaParts[1].padStart(2, '0'); // Asegurar formato de 2 dígitos
                    const anio = fechaParts[2];

                    // Verificar filtro de año
                    if (anioSeleccionado && anio !== anioSeleccionado) {
                        return false;
                    }

                    // Verificar filtro de meses (si hay meses seleccionados)
                    if (mesesSeleccionados.length > 0 && !mesesSeleccionados.includes(mes)) {
                        return false;
                    }
                }

                return true;
            });
        }

        // Redibujar la tabla
        table.draw();
    }
}

// Función para mostrar alertas
function mostrarAlerta(mensaje, tipo) {
    const alertaHtml = `
        <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    $('#alert-container').html(alertaHtml);

    // Auto-ocultar después de 5 segundos
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

// Función para limpiar la selección de meses
function limpiarSeleccionMeses() {
    $('#mesDropdownMenu input[type="checkbox"]').prop('checked', false);
    actualizarTextoBotonMeses();
    // Actualizar estado del botón Exportar PDF
    if (window.actualizarBotonesExportacion) {
        window.actualizarBotonesExportacion();
    }
}

// Función para actualizar el texto del botón de meses
function actualizarTextoBotonMeses() {
    const $button = $('#mesDropdownBtn');
    const $checkedBoxes = $('#mesDropdownMenu input[type="checkbox"]:checked');

    if ($checkedBoxes.length === 0) {
        $button.text('Seleccionar meses');
    } else if ($checkedBoxes.length === 1) {
        const mesNombre = $checkedBoxes.closest('li').find('label').text().trim();
        $button.text(mesNombre);
    } else {
        $button.text(`${$checkedBoxes.length} meses seleccionados`);
    }

    // Actualizar estado del botón Exportar PDF
    if (window.actualizarBotonesExportacion) {
        window.actualizarBotonesExportacion();
    }
}

// Función para buscar programas por año mediante AJAX
function buscarProgramasPorAnio(anio) {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    if (!csrfToken) {
        console.error('CSRF token not found');
        mostrarAlerta('Error de configuración: Token CSRF no encontrado', 'danger');
        return;
    }

    // Mostrar indicador de carga
    const $btnBuscar = $('#btnBuscarProgramas');
    const textoOriginal = $btnBuscar.html();
    $btnBuscar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    $.ajax({
        url: '/programas/buscar-por-anio',
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        data: { anio: anio },
        success: function(response) {
            if (response && response.success && response.programas) {
                // Obtener referencia a la tabla DataTable
                let table = window.programasTable;
                
                if (!table && $.fn.DataTable.isDataTable('#programasTable')) {
                    table = $('#programasTable').DataTable();
                }

                if (table) {
                    // Limpiar la tabla
                    table.clear();

                    // Verificar si hay programas
                    if (response.programas.length === 0) {
                        mostrarAlerta(`No se encontraron programas para el año ${anio}.`, 'info');
                        table.draw();
                        return;
                    }

                    // Calcular lunes y domingo de la semana actual para destacar
                    const hoy = new Date();
                    const diaSemana = hoy.getDay();
                    const diffLunes = diaSemana === 0 ? -6 : 1 - diaSemana; // Domingo = 0, ajustar a lunes
                    const diffDomingo = diaSemana === 0 ? 0 : 7 - diaSemana;
                    
                    const lunesSemanaActual = new Date(hoy);
                    lunesSemanaActual.setDate(hoy.getDate() + diffLunes);
                    lunesSemanaActual.setHours(0, 0, 0, 0);
                    
                    const domingoSemanaActual = new Date(hoy);
                    domingoSemanaActual.setDate(hoy.getDate() + diffDomingo);
                    domingoSemanaActual.setHours(23, 59, 59, 999);

                    // Agregar las filas con los nuevos datos
                    response.programas.forEach(function(programa) {
                        // Formatear fecha
                        const fechaParts = programa.fecha.split('-'); // YYYY-MM-DD
                        const fechaFormateada = `${fechaParts[2]}/${fechaParts[1]}/${fechaParts[0]}`; // DD/MM/YYYY
                        
                        // Verificar si está en la semana actual
                        const fechaPrograma = new Date(programa.fecha);
                        fechaPrograma.setHours(0, 0, 0, 0);
                        const esSemanaActual = fechaPrograma >= lunesSemanaActual && fechaPrograma <= domingoSemanaActual;

                        // Construir botones de acción
                        let botonesAccion = `
                            <div class="btn-group" role="group">
                                <a href="/programas/${programa.id}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Ver programa">
                                    <i class="fas fa-eye"></i>
                                </a>`;
                        
                        // Solo agregar botones de editar/eliminar si el usuario es coordinador u organizador
                        if (typeof window.isCoordinatorOrOrganizer !== 'undefined' && window.isCoordinatorOrOrganizer) {
                            botonesAccion += `
                                <a href="/programas/${programa.id}/edit" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Editar programa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger delete-programa" 
                                        data-id="${programa.id}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmDeleteModal"
                                        title="Eliminar programa">
                                    <i class="fas fa-trash"></i>
                                </button>`;
                        }
                        
                        botonesAccion += `</div>`;

                        // Crear elemento TD con data-order para la fecha
                        const fechaTd = `<td data-order="${programa.fecha}">${fechaFormateada}</td>`;

                        // Agregar fila usando HTML directo para evitar problemas de serialización
                        const newRow = table.row.add($(`
                            <tr class="${esSemanaActual ? 'semana-actual-row' : ''}">
                                <td data-order="${programa.fecha}">${fechaFormateada}</td>
                                <td>${programa.nombre_presidencia || '-'}</td>
                                <td>${programa.nombre_orador_inicial || '-'}</td>
                                <td>${programa.numero_cancion_pre != null ? programa.numero_cancion_pre : '-'}</td>
                                <td>${programa.numero_cancion_en != null ? programa.numero_cancion_en : '-'}</td>
                                <td>${programa.numero_cancion_post != null ? programa.numero_cancion_post : '-'}</td>
                                <td>${programa.nombre_orador_final || '-'}</td>
                                <td>${botonesAccion}</td>
                            </tr>
                        `)[0]);
                    });

                    // Redibujar la tabla
                    table.draw();

                    // Re-inicializar tooltips de Bootstrap
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function (tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                    }

                    // Reasignar eventos de eliminación
                    $('.delete-programa').off('click').on('click', function() {
                        window.programaIdToDelete = $(this).data('id');
                    });

                    //mostrarAlerta(`Se encontraron ${response.programas.length} programas para el año ${anio}.`, 'success');
                } else {
                    mostrarAlerta('Error: No se pudo acceder a la tabla de programas.', 'danger');
                }
            } else {
                const errorMessage = response && response.message ? response.message : 'Respuesta inválida del servidor';
                mostrarAlerta('Error al buscar programas: ' + errorMessage, 'danger');
            }
        },
        error: function(xhr, status, error) {
            let errorMessage = 'Error al conectar con el servidor';
            if (xhr.status === 403) {
                errorMessage = 'No tienes permiso para realizar esta acción';
            } else if (xhr.status === 404) {
                errorMessage = 'Ruta no encontrada';
            } else if (xhr.status === 500) {
                errorMessage = 'Error interno del servidor';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            mostrarAlerta(errorMessage, 'danger');
        },
        complete: function() {
            // Restaurar botón
            $btnBuscar.prop('disabled', false).html(textoOriginal);
        }
    });
}
