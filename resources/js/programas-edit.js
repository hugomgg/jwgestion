// Variables globales que serán definidas desde el Blade template
// let partesTable; - Declarado en Blade template
// let partesSegundaSeccionTable; - Declarado en Blade template
// let isEditMode = false; - Declarado en Blade template
// window.editingParteTwoData = false; - Declarado en Blade template
// let programmaticChange = false; - Declarado en Blade template

$(document).ready(function() {
    // Inicializar DataTable para partes del programa
    initPartesDataTable();

    // Cargar partes del programa
    loadPartesPrograma();

    // Inicializar DataTable para partes de la segunda sección
    initPartesSegundaSeccionDataTable();
    initPartesNVDataTable();

    // Cargar partes de la segunda sección
    loadPartesSegundaSeccion();
    // Cargar partes de Nuestra Vida Cristiana
    loadPartesNV();

    // Manejar envío del formulario de partes del programa
    $('#parteProgramaForm').submit(function(e) {
        e.preventDefault();
        submitPartePrograma();
    });

    // Manejar envío del formulario de partes de NVC
    $('#parteProgramaNVForm').submit(function(e) {
        e.preventDefault();
        submitParteProgramaNV();
    });

    // Manejar cambio en el select de parte_id para autocompletar el tiempo y filtrar encargados
    $(document).on('change', '#parte_id', function() {
        const selectedOption = $(this).find('option:selected');
        const tiempo = selectedOption.data('tiempo');
        const parteId = $(this).val();

        // Autocompletar tiempo
        if (tiempo) {
            $('#tiempo_parte').val(tiempo);
        } else {
            $('#tiempo_parte').val('');
        }

        // Para coordinadores, habilitar/deshabilitar botón de buscar encargado
        if (parteId) {
            $('#btn-buscar-encargado').prop('disabled', false);
        } else {
            $('#btn-buscar-encargado').prop('disabled', true);
        }
        // También limpiar los campos si no hay parte seleccionada
        $('#encargado_id').val('');
        $('#encargado_display').val('');
        $('#btn-historial-encargado').prop('disabled', true);
        $('#btn-agregar-reemplazado').prop('disabled', true);
    });

    // Manejar cambio en el select de parte_id_nv para autocompletar el tiempo y filtrar encargados
    $(document).on('change', '#parte_id_nv', function() {
        const selectedOption = $(this).find('option:selected');
        const tiempo = selectedOption.data('tiempo');
        const parteId = $(this).val();

        // Autocompletar tiempo
        if (tiempo) {
            $('#tiempo_parte_nv').val(tiempo);
        } else {
            $('#tiempo_parte_nv').val('');
        }

        // Habilitar/deshabilitar botón de buscar encargado
        if (parteId) {
            $('#btn-buscar-encargado-nv').prop('disabled', false);
        } else {
            $('#btn-buscar-encargado-nv').prop('disabled', true);
            // También limpiar los campos si no hay parte seleccionada
        }
        $('#encargado_id_nv').val('');
        $('#encargado_display_nv').val('');
        $('#btn-agregar-reemplazado-nv').prop('disabled', true);
    });

    // Manejar cambio en el campo fecha para guardar automáticamente
    $('#fecha').on('change', function() {
        const nuevaFecha = $(this).val();
        const programaId = $('#programa_id').val();

        if (!nuevaFecha) {
            alert('Por favor seleccione una fecha válida');
            return;
        }

        // Actualizar el campo hidden con la nueva fecha
        $('#fecha_programa_hidden').val(nuevaFecha);

        // Preparar datos para la actualización
        const updateData = {
            fecha: nuevaFecha,
            estado: $('#estado').val() || 1, // Usar estado activo por defecto si no está disponible
            // Incluir otros campos que puedan existir para evitar errores de validación
            orador_inicial: $('#orador_inicial').val() || null,
            presidencia: $('#presidencia').val() || null,
            cancion_pre: $('#cancion_pre').val() || null,
            cancion_en: $('#cancion_en').val() || null,
            cancion_post: $('#cancion_post').val() || null,
            orador_final: $('#orador_final').val() || null
        };

        // Hacer la llamada AJAX para actualizar el programa
        $.ajax({
            url: `/programas/${programaId}`,
            type: 'PUT',
            data: updateData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Mostrar modal de éxito
                    $('#successModal').modal('show');

                    // Ocultar el modal automáticamente después de 3 segundos con fade out
                    setTimeout(function() {
                        $('#successModal').modal('hide');
                    }, 2000);
                } else {
                    alert('Error al guardar la fecha: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error al guardar la fecha';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage += ': ' + errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    });

    // Manejar envío del formulario de editar programa
    $('#editProgramaForm').submit(function(e) {
        e.preventDefault();

        const programaId = $('#programa_id').val();
        const submitBtn = $('#updateProgramaBtn');
        const spinner = submitBtn.find('.spinner-border');

        // Deshabilitar botón y mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#alert-container').empty();

        $.ajax({
            url: `/programas/${programaId}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    // Mostrar mensaje de éxito y recargar la página para mantener la vista de edición
                    showAlert('alert-container', 'success', 'Programa actualizado exitosamente');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('alert-container', 'danger', response.message || 'Error al actualizar el programa');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#${field}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                } else {
                    showAlert('alert-container', 'danger', 'Error al actualizar el programa');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Función para mostrar alertas
    function showAlert(container, type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $(`#${container}`).html(alertHtml);
    }

    // Funciones para partes del programa
    function initPartesDataTable() {
        partesTable = $('#partesTable').DataTable({
            language: {
                url: '/js/datatables-es-ES.json'
            },
            responsive: true,
            paging: false,
            ordering: false,
            order: [[0, 'asc']], // Ordenar por la primera columna (Número) por defecto
            info: false,
            searching: false,
            columnDefs: [
                { orderable: false, targets: 0 }, // Columna Número ordenable
                { orderable: false, targets: [1, 2, 3, 4, 5] } // Otras columnas no ordenables
            ]
        });
    }

    function initPartesSegundaSeccionDataTable() {
        partesSegundaSeccionTable = $('#partesSegundaSeccionTable').DataTable({
            language: {
                emptyTable: "No hay partes asignadas en la segunda sección",
                zeroRecords: "No se encontraron partes que coincidan con la búsqueda"
            },
            responsive: true,
            paging: false,
            ordering: false,
            order: [[0, 'asc']], // Ordenar por la primera columna (Número) por defecto
            info: false,
            searching: false,
            columnDefs: [
                { orderable: false, targets: 0 }, // Columna Número ordenable
                { orderable: false, targets: [1, 2, 3, 4, 5, 6, 7] } // Otras columnas no ordenables
            ]
        });
    }

    function loadPartesSegundaSeccion() {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes-smm`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    partesSegundaSeccionTable.clear();

                    response.data.forEach(function(parte, index) {
                        const numero = parte.numero; // Número incremental empezando desde 1
                        const upDisabled = parte.es_primero ? 'disabled' : '';
                        const downDisabled = parte.es_ultimo ? 'disabled' : '';

                        let acciones = `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteSegundaSeccionUp(${parte.id})" title="Subir" ${upDisabled}>
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteSegundaSeccionDown(${parte.id})" title="Bajar" ${downDisabled}>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="verAsignacionDesdeTabla(${parte.id})" title="Ver Asignación">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editParteSegundaSeccion(${parte.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteParteSegundaSeccion(${parte.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;

                        let salaBadge = '';
                        if (parte.sala_abreviacion === 'SP') {
                            salaBadge = `<span class="badge bg-primary">${parte.sala_abreviacion}</span>`;
                        } else if (parte.sala_abreviacion === 'S1') {
                            salaBadge = `<span class="badge bg-warning">${parte.sala_abreviacion}</span>`;
                        } else if (parte.sala_abreviacion === 'S2') {
                            salaBadge = `<span class="badge bg-success">${parte.sala_abreviacion}</span>`;
                        } else {
                            salaBadge = '<span class="badge bg-secondary">-</span>';
                        }

                        let rowData = [
                            numero, // Columna Número
                            parte.parte_abreviacion || '-',
                            parte.encargado_nombre || '-',
                            parte.ayudante_nombre || '-',
                            salaBadge,
                            parte.tiempo || '-',
                            parte.leccion || '-',
                            acciones
                        ];

                        partesSegundaSeccionTable.row.add(rowData);
                    });

                    partesSegundaSeccionTable.draw();
                } else {
                    console.error('Error al cargar las partes de la segunda sección:', response.message);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar las partes de la segunda sección:', xhr.responseText);
            }
        });
    }

    function loadPartesPrograma() {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    partesTable.clear();

                    response.data.forEach(function(parte, index) {
                        const numero = index + 1; // Número incremental empezando desde 1
                        const upDisabled = parte.es_primero ? 'disabled' : '';
                        const downDisabled = parte.es_ultimo ? 'disabled' : '';

                        let acciones = `
                            <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteUp(${parte.id})" title="Subir" ${upDisabled}>
                                <i class="fas fa-chevron-up"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteDown(${parte.id})" title="Bajar" ${downDisabled}>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editParte(${parte.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteParte(${parte.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            </div>
                        `;

                        let rowData = [
                            numero, // Columna Número
                            parte.parte_abreviacion || '-',
                            parte.encargado_nombre || '-',
                            parte.tema || '-',
                            parte.tiempo || '-'
                        ];

                        rowData.push(acciones);

                        partesTable.row.add(rowData);
                    });

                    partesTable.draw();
                }
            },
            error: function(xhr) {
                showAlert('alert-container', 'danger', 'Error al cargar las partes del programa');
            }
        });
    }

    function openCreateParteModal() {
        isEditMode = false;
        $('#saveParteBtn').text('Guardar Asignación');
        $('#parteProgramaForm')[0].reset();

        // Limpiar alertas del modal
        $('#modal-alert-container').empty();

        // Mostrar select y ocultar input de texto para "Asignación" en modo "nuevo"
        $('#parte_id').show();
        $('#parte_display').hide();

        // Ocultar campo y botón de encargado reemplazado en modo "nuevo"
        $('#encargado_reemplazado_display').closest('.col-md-6').hide();
        $('#btn-agregar-reemplazado').hide();
        $('#parte_programa_id').val('');
        $('#btn-buscar-encargado').prop('disabled', true);

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Restablecer campos de encargado según el perfil del usuario
        $('#encargado_id').val('');
        $('#encargado_display').val('');

        $('#btn-agregar-reemplazado').prop('disabled', true);
        // Limpiar campos de encargado reemplazado
        $('#encargado_reemplazado_id').val('');
        $('#encargado_reemplazado_display').val('');
        $('#btn-eliminar-reemplazado').prop('disabled', true);

        // Cargar partes de sección disponibles con Ajax
        loadPartesSecciones();

        $('#parteProgramaModal').modal('show');
    }

    function editParte(id) {
        isEditMode = true;
        $('#parteProgramaModalLabel').text('Editar Asignación de Tesoros de la Biblia');
        $('#saveParteBtn').text('Actualizar Asignación');

        // Limpiar alertas del modal
        $('#modal-alert-container').empty();
        $('#encargado_reemplazado_id').val('');
        $('#encargado_reemplazado_display').val('');

        // Ocultar select y mostrar input de texto para "Asignación" en modo "editar"
        $('#parte_id').hide();
        $('#parte_display').show();

        // Mostrar campo y botón de encargado reemplazado en modo "editar"
        $('#encargado_reemplazado_display').closest('.col-md-6').show();
        $('#btn-agregar-reemplazado').show();


        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: `/partes-programa/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const parte = response.data;
                    $('#parte_programa_id').val(parte.id);
                    $('#tiempo_parte').val(parte.tiempo);
                    $('#tema_parte').val(parte.tema);

                    // Cargar solo la parte correspondiente en modo edición
                    loadPartesSeccionesForEdit(parte.parte_id, function() {
                        // Manejar el campo encargado según el perfil del usuario
                        $('#encargado_id').val(parte.encargado_id);
                        $('#encargado_display').val(parte.encargado ? parte.encargado.name : '');

                        // Cargar encargado reemplazado si existe
                        if (parte.encargado_reemplazado_id && parte.encargado_reemplazado) {
                            $('#encargado_reemplazado_id').val(parte.encargado_reemplazado_id);
                            $('#encargado_reemplazado_display').val(parte.encargado_reemplazado.name);
                            $('#btn-eliminar-reemplazado').prop('disabled', false);
                        }

                        // Habilitar el botón de buscar encargado ya que hay una parte seleccionada
                        $('#btn-buscar-encargado').prop('disabled', false);

                        // Habilitar/deshabilitar el botón de historial según si hay encargado
                        if (parte.encargado_id) {
                            $('#btn-historial-encargado').prop('disabled', false);
                            $('#btn-agregar-reemplazado').prop('disabled', false);
                        } else {
                            $('#btn-historial-encargado').prop('disabled', true);
                            $('#btn-agregar-reemplazado').prop('disabled', true);
                        }

                        $('#parteProgramaModal').modal('show');
                    });
                }
            },
            error: function(xhr) {
                showAlert('alert-container', 'danger', 'Error al cargar los datos de la parte');
            }
        });
    }

    function submitPartePrograma() {
        const submitBtn = $('#saveParteBtn');
        const spinner = submitBtn.find('.spinner-border');

        // Deshabilitar botón y mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Limpiar alertas del modal
        $('#modal-alert-container').empty();

        // Validar campo tiempo
        const tiempoValue = $('#tiempo_parte').val();
        if (!tiempoValue || tiempoValue < 1) {
            $('#tiempo_parte').addClass('is-invalid');
            $('#tiempo_parte').siblings('.invalid-feedback').text('El campo Tiempo es obligatorio y debe ser mayor a 0.');
            submitBtn.prop('disabled', false);
            spinner.addClass('d-none');
            return;
        }

        // Detectar qué modal está activo para usar el campo de lección correcto
        let leccionFieldId = '';
        let leccionValue = '';

        if ($('#parteProgramaModal').hasClass('show')) {
            // Primera sección - solo tiene campo de lección si NO es coordinador
            if (!userIsCoordinator) {
                leccionFieldId = 'leccion_parte';
                leccionValue = $('#leccion_parte').val();
            }
        } else if ($('#parteProgramaSegundaSeccionModal').hasClass('show')) {
            // Segunda sección (coordinadores)
            leccionFieldId = 'leccion_segunda_seccion';
            leccionValue = $('#leccion_segunda_seccion').val();
        }

        // Validar campo leccion solo si existe el campo
        if (leccionFieldId && (!leccionValue || leccionValue.trim() === '')) {
            $(`#${leccionFieldId}`).addClass('is-invalid');
            $(`#${leccionFieldId}`).siblings('.invalid-feedback').text('El campo Lección es obligatorio.');
            submitBtn.prop('disabled', false);
            spinner.addClass('d-none');
            return;
        }

        // Validar que el encargado y el encargado reemplazado sean distintos
        const encargadoId = $('#encargado_id').val();
        const encargadoReemplazadoId = $('#encargado_reemplazado_id').val();
        if (encargadoId && encargadoReemplazadoId && encargadoId === encargadoReemplazadoId) {
            showAlert('modal-alert-container', 'warning', 'El Encargado y el Encargado Reemplazado no pueden ser la misma persona.');
            submitBtn.prop('disabled', false);
            spinner.addClass('d-none');
            return;
        }

        // Crear FormData manualmente para asegurar que incluya todos los campos
        const formDataObj = {
            'programa_id': $('#programa_id_parte').val(),
            'parte_id': isEditMode ? $('#parte_id_hidden').val() : $('#parte_id').val(),
            'tiempo': $('#tiempo_parte').val(),
            'tema': $('#tema_parte').val(),
            'encargado_id': $('#encargado_id').val(),
            'encargado_reemplazado_id': $('#encargado_reemplazado_id').val(),
            'sala_id': $('#sala_id').val(), // Incluir sala_id si existe
            '_token': $('meta[name="csrf-token"]').attr('content')
        };

        // Agregar lección al formDataObj solo si existe el campo
        if (leccionValue) {
            formDataObj['leccion'] = leccionValue;
        }

        const url = isEditMode ? `/partes-programa/${$('#parte_programa_id').val()}` : '/partes-programa';
        const method = isEditMode ? 'PUT' : 'POST';

        // Para métodos PUT, agregar el método al formulario
        if (method === 'PUT') {
            formDataObj['_method'] = 'PUT';
        }

        $.ajax({
            url: url,
            method: 'POST', // Laravel maneja PUT a través de POST con _method
            data: formDataObj,
            success: function(response) {
                if (response.success) {
                    $('#parteProgramaModal').modal('hide');
                    loadPartesPrograma();
                    // Mostrar modal de éxito
                    $('#successModal').modal('show');
                    // Ocultar el modal automáticamente después de 3 segundos con fade out
                    setTimeout(function() {
                        $('#successModal').modal('hide');
                    }, 2000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        let fieldName = field;
                        if (field === 'tiempo') fieldName = 'tiempo_parte';
                        if (field === 'tema') fieldName = 'tema_parte';
                        if (field === 'leccion' && leccionFieldId) fieldName = leccionFieldId; // Solo usar si existe el campo

                        $(`#${fieldName}`).addClass('is-invalid');
                        $(`#${fieldName}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                } else {
                    const errorMessage = xhr.responseJSON?.message || `Error ${xhr.status}: ${xhr.statusText}`;
                    showAlert('alert-container', 'danger', errorMessage);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    }

    function submitParteProgramaNV() {
        const submitBtn = $('#saveParteNVBtn');
        const spinner = submitBtn.find('.spinner-border');

        // Deshabilitar botón y mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Validar campo tiempo
        const tiempoValue = $('#tiempo_parte_nv').val();
        if (!tiempoValue || tiempoValue < 1) {
            $('#tiempo_parte_nv').addClass('is-invalid');
            $('#tiempo_parte_nv').siblings('.invalid-feedback').text('El campo Tiempo es obligatorio y debe ser mayor a 0.');
            submitBtn.prop('disabled', false);
            spinner.addClass('d-none');
            return;
        }

        // Validar que el encargado y el encargado reemplazado sean distintos
        const encargadoId = $('#encargado_id_nv').val();
        const encargadoReemplazadoId = $('#encargado_reemplazado_id_nv').val();
        if (encargadoId && encargadoReemplazadoId && encargadoId === encargadoReemplazadoId) {
            showAlert('modal-alert-container-nv', 'warning', 'El Encargado y el Encargado Reemplazado no pueden ser la misma persona.');
            submitBtn.prop('disabled', false);
            spinner.addClass('d-none');
            return;
        }

        // Crear FormData para NVC
        const formDataObj = {
            'programa_id': $('#programa_id_parte_nv').val(),
            'parte_id': $('#parte_id_nv').val(),
            'tiempo': $('#tiempo_parte_nv').val(),
            'tema': $('#tema_parte_nv').val(),
            'encargado_id': $('#encargado_id_nv').val(),
            'encargado_reemplazado_id': $('#encargado_reemplazado_id_nv').val(),
            'sala_id': $('#sala_id_nv').val(), // Incluir sala_id si existe
            '_token': $('meta[name="csrf-token"]').attr('content')
        };

        const url = isEditMode ? `/partes-programa/${$('#parte_programa_nv_id').val()}` : '/partes-programa';
        const method = isEditMode ? 'PUT' : 'POST';

        // Para métodos PUT, agregar el método al formulario
        if (method === 'PUT') {
            formDataObj['_method'] = 'PUT';
        }

        $.ajax({
            url: url,
            method: 'POST', // Laravel maneja PUT a través de POST con _method
            data: formDataObj,
            success: function(response) {
                if (response.success) {
                    $('#parteProgramaNVModal').modal('hide');
                    loadPartesNV();

                    // Mostrar modal de éxito
                    $('#successModal').modal('show');

                    // Ocultar el modal automáticamente después de 3 segundos con fade out
                    setTimeout(function() {
                        $('#successModal').modal('hide');
                    }, 2000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        let fieldName = field;
                        if (field === 'tiempo') fieldName = 'tiempo_parte_nv';
                        if (field === 'tema') fieldName = 'tema_parte_nv';
                        if (field === 'leccion') fieldName = 'leccion_parte_nv';

                        $(`#${fieldName}`).addClass('is-invalid');
                        $(`#${fieldName}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                } else {
                    const errorMessage = xhr.responseJSON?.message || `Error ${xhr.status}: ${xhr.statusText}`;
                    showAlert('alert-container', 'danger', errorMessage);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    }

    function deleteParte(id, callback = null) {
        // Mostrar el modal de confirmación
        $('#confirmDeleteModal').modal('show');

        // Manejar la confirmación de eliminación
        $('#confirmDeleteBtn').off('click').on('click', function() {
            const deleteBtn = $(this);
            const originalText = deleteBtn.html();

            // Deshabilitar botón y mostrar spinner
            deleteBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Eliminando...');

            $.ajax({
                url: `/partes-programa/${id}`,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#confirmDeleteModal').modal('hide');
                        // Usar el callback proporcionado o loadPartesPrograma por defecto
                        if (callback && typeof callback === 'function') {
                            callback();
                        } else {
                            loadPartesPrograma();
                        }

                        // Actualizar y mostrar modal de éxito
                        $('#successModalMessage').text('Asignación eliminada exitosamente');
                        $('#successModal').modal('show');

                        // Ocultar el modal automáticamente después de 2 segundos
                        setTimeout(function() {
                            $('#successModal').modal('hide');
                        }, 2000);
                    }
                },
                error: function(xhr) {
                    $('#confirmDeleteModal').modal('hide');
                    showAlert('alert-container', 'danger', xhr.responseJSON?.message || 'Error al eliminar la parte');
                },
                complete: function() {
                    // Restaurar el botón
                    deleteBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    }

    function loadPartesSecciones() {
        const programaId = $('#programa_id').val();

        $('#parte_id').empty().append('<option value="">Cargando...</option>');

        $.ajax({
            url: '/partes-secciones',
            method: 'GET',
            data: {
                programa_id: programaId,
                parte_id: 1
            },
            success: function(response) {
                if (response.success) {
                    $('#parte_id').empty().append('<option value="">Seleccionar...</option>');

                    response.data.forEach(function(parte) {
                        $('#parte_id').append(
                            `<option value="${parte.id}" data-tiempo="${parte.tiempo || ''}">${parte.nombre} (${parte.abreviacion})</option>`
                        );
                    });
                } else {
                    $('#parte_id').empty().append('<option value="">No hay partes disponibles</option>');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar partes de sección:', xhr);
                $('#parte_id').empty().append('<option value="">Error al cargar</option>');
            }
        });
    }

    function loadPartesSeccionesForEdit(parteId, callback) {
        $('#parte_id').empty().append('<option value="">Cargando...</option>');

        $.ajax({
            url: `/partes-seccion/${parteId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const parte = response.data;
                    $('#parte_id').empty();

                    // Solo agregar la parte que corresponde al registro que se está editando
                    $('#parte_id').append(
                        `<option value="${parte.id}" data-tiempo="${parte.tiempo || ''}" selected>${parte.nombre} (${parte.abreviacion})</option>`
                    );

                    // Llenar el campo de texto deshabilitado para el modo editar
                    $('#parte_display').val(parte.nombre);
                    $('#parte_id_hidden').val(parte.id);

                    // Autocompletar el tiempo si está disponible
                    if (parte.tiempo) {
                        $('#tiempo_parte').val(parte.tiempo);
                    }

                    // Ejecutar callback si se proporciona
                    if (typeof callback === 'function') {
                        callback();
                    }
                } else {
                    $('#parte_id').empty().append('<option value="">Error al cargar la parte</option>');

                    // Ejecutar callback incluso si hay error
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error al cargar parte de sección:', xhr);
                $('#parte_id').empty().append('<option value="">Error al cargar</option>');

                // Ejecutar callback incluso en caso de error
                if (typeof callback === 'function') {
                    callback();
                }
            }
        });
    }


    // Manejar envío del formulario de segunda sección
    $('#parteProgramaSegundaSeccionForm').submit(function(e) {
        e.preventDefault();
        submitParteSegundaSeccion();
    });

    // Habilitar los botones Buscar Encargado y Buscar Ayudante al seleccionar una parte en la segunda sección
    $(document).on('change', '#parte_id_segunda_seccion', function() {
        const parteId = $(this).val();
        const btnBuscarEncargado = $('#btn-buscar-encargado-segunda');
        const btnBuscarAyudante = $('#btn-buscar-ayudante-segunda');
        // Obtener el tiempo de la opción seleccionada (data-tiempo)
        const selectedOption = $(this).find('option:selected');
        const tiempo = selectedOption.data('tiempo');

        // Limpiar campos de encargado y ayudante al seleccionar una parte
        $('#encargado_display_segunda_seccion').val('');
        $('#encargado_id_segunda_seccion').val('');
        $('#ayudante_display_segunda_seccion').val('');
        $('#ayudante_id_segunda_seccion').val('');
        // Limpiar la variable global para permitir nuevos cambios
        window.ultimoValorEncargado = null;
        // También limpiar el select2 del ayudante (sin trigger para evitar loops)
        $('#ayudante_id_segunda_seccion').trigger('change');
        $('#btn-encargado-reemplazado-segunda').prop('disabled', true);
        $('#btn-ayudante-reemplazado-segunda').prop('disabled', true);
        if (parteId) {
            btnBuscarEncargado.prop('disabled', false).attr('title', 'Buscar Estudiante');
            // El botón "Buscar Ayudante" se mantiene deshabilitado hasta que se seleccione un encargado
            btnBuscarAyudante.prop('disabled', true).attr('title', 'Seleccionar un estudiante primero');

            // Cargar el tiempo en el campo correspondiente
            if (typeof tiempo !== 'undefined' && tiempo !== null && tiempo !== '') {
                $('#tiempo_segunda_seccion').val(tiempo);
            } else {
                $('#tiempo_segunda_seccion').val('');
            }
        } else {
            btnBuscarEncargado.prop('disabled', true).attr('title', 'Seleccionar una parte primero');
            btnBuscarAyudante.prop('disabled', true).attr('title', 'Seleccionar una parte primero');
            $('#tiempo_segunda_seccion').val('');
        }
    });

    // Manejar cambio en el select de sala en la segunda sección
    $(document).on('change', '#sala_id_segunda_seccion', function() {
        const salaId = $(this).val();
        const selectedOption = $(this).find('option:selected');
        const salaNombre = selectedOption.text();

        // Actualizar el título del modal
        if (salaId) {
            $('#parteProgramaSegundaSeccionModalLabel').text('Nueva Asignación (' + salaNombre.split(' - ')[1] + ')');
        } else {
            $('#parteProgramaSegundaSeccionModalLabel').text('Nueva Asignación');
        }
    });

    // Función para continuar el cambio de encargado
    function continueEncargadoChange(encargadoSeleccionado, parteSeleccionada, ayudanteSelect) {
        if (encargadoSeleccionado && parteSeleccionada) {
            // Verificar si el encargado y ayudante actual son del mismo sexo
            const ayudanteActual = ayudanteSelect.val();
            if (ayudanteActual) {
                // Verificar sexos antes de recargar
                verificarSexosYCargarAyudantes(encargadoSeleccionado, ayudanteActual, parteSeleccionada);
            } else {
                // Si no hay ayudante actual, cargar normalmente
                loadAyudantesByEncargadoAndParte(encargadoSeleccionado, parteSeleccionada);
            }

        } else {
            // Si no hay encargado o parte seleccionada, limpiar ayudantes
            programmaticChange = true; // Marcar como cambio programático
            ayudanteSelect.empty().append('<option value="">Seleccionar encargado y parte primero...</option>').trigger('change');
            programmaticChange = false; // Resetear flag
            clearHistorialEncargado();
        }
    }

    // Función para verificar sexos y decidir si recargar ayudantes
    function verificarSexosYCargarAyudantes(encargadoId, ayudanteId, parteId) {
        $.ajax({
            url: '/verificar-sexos-usuarios',
            method: 'GET',
            data: {
                encargado_id: encargadoId,
                ayudante_id: ayudanteId
            },
            success: function(response) {
                if (response.success) {
                    const encargadoSexo = response.encargado_sexo;
                    const ayudanteSexo = response.ayudante_sexo;

                    // Si ambos son del mismo sexo, no recargar ayudantes
                    if (encargadoSexo === ayudanteSexo) {

                        return;
                    }

                    // Si son de diferente sexo, recargar ayudantes
                    loadAyudantesByEncargadoAndParte(encargadoId, parteId);
                } else {
                    // En caso de error, cargar normalmente
                    loadAyudantesByEncargadoAndParte(encargadoId, parteId);
                }
            },
            error: function(xhr) {
                console.error('Error al verificar sexos:', xhr.responseText);
                // En caso de error, cargar normalmente
                loadAyudantesByEncargadoAndParte(encargadoId, parteId);
            }
        });
    }


    function openCreateParteSegundaSeccionModal(isSalaAuxiliar = null) {
        isEditMode = false;

        // Si no se especifica la sala, siempre usar Sala Principal (SP) para el botón principal
        if (isSalaAuxiliar === null) {
            isSalaAuxiliar = false; // Siempre SP para el botón principal
        }

        // Actualizar el título del modal
        $('#parteProgramaSegundaSeccionModalLabel').text('Nueva Asignación Seamos Mejores Maestros');

        $('#parteProgramaSegundaSeccionForm')[0].reset();
        $('#parte_programa_segunda_seccion_id').val('');
        $('#saveParteSegundaSeccionBtn').text('Guardar Asignación');
        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Limpiar alertas del modal
        $('#modal-alert-container-segunda-seccion').empty();

        // Cargar partes de la segunda sección
        loadPartesSeccionesSegundaSeccion();

        // Para perfil=3, dejar el campo encargado vacío hasta que se seleccione una parte
        $('#encargado_display_segunda_seccion').val('Seleccionar una parte primero...');
        $('#encargado_id_segunda_seccion').val('');

        // Inicializar campo ayudante
        $('#ayudante_display_segunda_seccion').val('Seleccionar encargado y parte primero...');
        $('#ayudante_id_segunda_seccion').val('');

        // Limpiar historial de encargado
        clearHistorialEncargado();

        // Limpiar historial de ayudante
        clearHistorialAyudante();

        // Ocultar campos de reemplazados en modo nuevo
        $('#campos-reemplazados-segunda-seccion').hide();

        // Ocultar botones de agregar reemplazado en modo nuevo
        $('#btn-agregar-encargado-reemplazado').hide();
        $('#btn-agregar-ayudante-reemplazado').hide();

        // Ocultar botones de reemplazado en modo nuevo
        $('#btn-encargado-reemplazado-segunda').hide();
        $('#btn-ayudante-reemplazado-segunda').hide();

        // Limpiar historial de ayudante
        clearHistorialAyudante();

        // Inicializar estado de los botones "Buscar Encargado" y "Buscar Ayudante" (deshabilitados por defecto)
        $('#btn-buscar-encargado-segunda').prop('disabled', true);
        $('#btn-buscar-encargado-segunda').attr('title', 'Seleccionar una parte primero');
        $('#btn-buscar-ayudante-segunda').prop('disabled', true);
        $('#btn-buscar-ayudante-segunda').attr('title', 'Seleccionar una parte primero');
    }

    function editParteSegundaSeccion(id) {
        isEditMode = true;
        $('#parteProgramaSegundaSeccionModalLabel').text('Editar Asignación');
        $('#parte_programa_segunda_seccion_id').val(id);
        $('#saveParteSegundaSeccionBtn').text('Actualizar Asignación');

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Limpiar alertas del modal
        $('#modal-alert-container-segunda-seccion').empty();

        // Limpiar completamente el formulario antes de cargar datos
        $('#parteProgramaSegundaSeccionForm')[0].reset();

        // Limpiar todos los selects
        $('#parte_id_segunda_seccion').empty().append('<option value="">Seleccionar...</option>');
        $('#encargado_display_segunda_seccion').val('Seleccionar...');
        $('#encargado_id_segunda_seccion').val('');
        $('#ayudante_display_segunda_seccion').val('Seleccionar...');
        $('#ayudante_id_segunda_seccion').val('');

        // Limpiar campos de reemplazados
        clearEncargadoReemplazado();
        clearAyudanteReemplazado();

        // Mostrar campos de reemplazados en modo edición
        $('#campos-reemplazados-segunda-seccion').show();

        // Mostrar botones de agregar reemplazado en modo edición
        $('#btn-agregar-encargado-reemplazado').show();
        $('#btn-agregar-ayudante-reemplazado').show();

        // Mostrar botones de reemplazado en modo edición
        $('#btn-encargado-reemplazado-segunda').show();
        $('#btn-ayudante-reemplazado-segunda').show();

        // Cargar datos necesarios de la asignación
        loadPartesSeccionesForEditSegundaSeccion(id);
        // Variable para controlar si estamos en modo edición para evitar eventos conflictivos
        window.editingParteTwoData = true;

        $.ajax({
            url: `/partes-programa/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const parte = response.data;

                    // Establecer el sala_id correcto y actualizar el título del modal
                    $('#parteProgramaSegundaSeccionModalLabel').text('Editar Asignación Seamos Mejores Maestros');
                    $('#sala_id_segunda_seccion').val(parte.sala_id);

                    $('#parte_id_segunda_seccion').val(parte.parte_id);
                    $('#tiempo_segunda_seccion').val(parte.tiempo);
                    $('#leccion_segunda_seccion').val(parte.leccion);

                    if (parte.encargado_id) {
                        $('#encargado_id_segunda_seccion').val(parte.encargado_id);
                        $('#encargado_display_segunda_seccion').val(parte.encargado_nombre);
                    }
                    $('#ayudante_id_segunda_seccion').val(parte.ayudante_id);
                    $('#ayudante_display_segunda_seccion').val(parte.ayudante_nombre);
                    $('#tema_segunda_seccion').val(parte.tema);

                    // Cargar encargado reemplazado
                    if (parte.encargado_reemplazado) {
                        $('#encargado_reemplazado_segunda_seccion').val(parte.encargado_reemplazado.name);
                        $('#encargado_reemplazado_id_segunda_seccion').val(parte.encargado_reemplazado.id);
                    } else {
                        $('#encargado_reemplazado_segunda_seccion').val('');
                        $('#encargado_reemplazado_id_segunda_seccion').val('');
                    }

                    // Cargar ayudante reemplazado
                    if (parte.ayudante_reemplazado) {
                        $('#ayudante_reemplazado_segunda_seccion').val(parte.ayudante_reemplazado.name);
                        $('#ayudante_reemplazado_id_segunda_seccion').val(parte.ayudante_reemplazado.id);
                    } else {
                        $('#ayudante_reemplazado_segunda_seccion').val('');
                        $('#ayudante_reemplazado_id_segunda_seccion').val('');
                    }

                    $('#parteProgramaSegundaSeccionModal').modal('show');

                    // Actualizar estado de botones según los datos cargados
                    updateButtonStatesSegundaSeccion();
                }
            },
            error: function(xhr) {
                console.error('Error al cargar la parte:', xhr.responseText);
                window.editingParteTwoData = false;
            }
        });
    }

    function deleteParteSegundaSeccion(id) {
        $('#confirmDeleteBtn').off('click').on('click', function() {
            $.ajax({
                url: `/partes-programa/${id}`,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Determinar qué datatable actualizar según el sala_id de la parte eliminada
                        // Como estamos eliminando desde la tabla de segunda sección, pero puede ser sala auxiliar,
                        // necesitamos verificar si hay una manera de determinar el sala_id
                        // Por ahora, recargamos la tabla de segunda sección
                        loadPartesSegundaSeccion();
                        $('#confirmDeleteModal').modal('hide');

                        // Actualizar y mostrar modal de éxito
                        $('#successModalMessage').text('Asignación eliminada exitosamente');
                        $('#successModal').modal('show');

                        // Ocultar el modal automáticamente después de 2 segundos
                        setTimeout(function() {
                            $('#successModal').modal('hide');
                        }, 2000);
                    }
                },
                error: function(xhr) {
                    console.error('Error al eliminar la parte:', xhr.responseText);
                }
            });
        });
        $('#confirmDeleteModal').modal('show');
    }

    function submitParteSegundaSeccion() {
        // Validar que Encargado y Ayudante no sean la misma persona
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        const ayudanteId = $('#ayudante_id_segunda_seccion').val();
        const parteSeleccionada = $('#parte_id_segunda_seccion').val();

        if (encargadoId && ayudanteId && encargadoId === ayudanteId) {
            showAlert('modal-alert-container-segunda-seccion', 'warning', 'El Estudiante y el Ayudante no pueden ser la misma persona.');
            return;
        }

        // Validar que el encargado y el encargado reemplazado sean distintos
        const encargadoReemplazadoId = $('#encargado_reemplazado_id_segunda_seccion').val();
        if (encargadoId && encargadoReemplazadoId && encargadoId === encargadoReemplazadoId) {
            showAlert('modal-alert-container-segunda-seccion', 'warning', 'El Estudiante y el Estudiante Reemplazado no pueden ser la misma persona.');
            return;
        }

        // Validar que el ayudante y el ayudante reemplazado sean distintos
        const ayudanteReemplazadoId = $('#ayudante_reemplazado_id_segunda_seccion').val();
        if (ayudanteId && ayudanteReemplazadoId && ayudanteId === ayudanteReemplazadoId) {
            showAlert('modal-alert-container-segunda-seccion', 'warning', 'El Ayudante y el Ayudante Reemplazado no pueden ser la misma persona.');
            return;
        }

        // Validar que para partes tipo 2 o 3, tanto Encargado como Ayudante sean obligatorios
        if (parteSeleccionada) {
            const selectedOption = $('#parte_id_segunda_seccion').find('option:selected');
            const tipo = selectedOption.data('tipo');

                if (!encargadoId || encargadoId === '') {
                    showAlert('modal-alert-container-segunda-seccion', 'warning', 'Para esta Asignación es obligatorio seleccionar un Estudiante.');
                    $('#encargado_display_segunda_seccion').addClass('is-invalid');
                    return;
                }
            if (tipo == 2 || tipo == 3) {
                if (!ayudanteId || ayudanteId === '') {
                    showAlert('modal-alert-container-segunda-seccion', 'warning', 'Para esta Asignación es obligatorio seleccionar un Ayudante.');
                    $('#ayudante_display_segunda_seccion').addClass('is-invalid');
                    return;
                }
            }
        }

        // Validar campo tiempo
        const tiempoValue = $('#tiempo_segunda_seccion').val();
        if (!tiempoValue || tiempoValue < 1) {
            showAlert('modal-alert-container-segunda-seccion', 'warning', 'El campo Tiempo es obligatorio y debe ser mayor a 0.');
            $('#tiempo_segunda_seccion').addClass('is-invalid');
            return;
        }

        // Campo leccion ahora es opcional

        const isEdit = isEditMode;
        const url = isEdit ? `/partes-programa/${$('#parte_programa_segunda_seccion_id').val()}` : '/partes-programa';
        const method = isEdit ? 'PUT' : 'POST';

        const formData = $('#parteProgramaSegundaSeccionForm').serialize();

        const submitBtn = $('#saveParteSegundaSeccionBtn');
        const spinner = submitBtn.find('.spinner-border');

        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Limpiar alertas del modal
        $('#modal-alert-container-segunda-seccion').empty();

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#parteProgramaSegundaSeccionModal').modal('hide');

                    // Solo actualizar tabla de segunda sección
                    loadPartesSegundaSeccion();

                    // Mostrar modal de éxito
                    $('#successModal').modal('show');

                    // Ocultar el modal automáticamente después de 3 segundos con fade out
                    setTimeout(function() {
                        $('#successModal').modal('hide');
                    }, 2000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        let fieldName = field;
                        if (field === 'tiempo') fieldName = 'tiempo_segunda_seccion';
                        if (field === 'encargado_id') fieldName = 'encargado_id_segunda_seccion';
                        if (field === 'ayudante_id') fieldName = 'ayudante_id_segunda_seccion';
                        if (field === 'parte_id') fieldName = 'parte_id_segunda_seccion';
                        if (field === 'leccion') fieldName = 'leccion_segunda_seccion';

                        $(`#${fieldName}`).addClass('is-invalid');
                        $(`#${fieldName}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                } else {
                    const errorMessage = xhr.responseJSON?.message || `Error ${xhr.status}: ${xhr.statusText}`;
                    showAlert('alert-container', 'danger', errorMessage);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    }

    function loadPartesSeccionesSegundaSeccion() {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes-segunda-seccion-disponibles`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#parte_id_segunda_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    // Las partes ya vienen filtradas desde el backend
                    response.data.forEach(function(parte) {
                        select.append(`<option value="${parte.id}" data-tiempo="${parte.tiempo}" data-tipo="${parte.tipo}" data-abreviacion="${parte.abreviacion}">${parte.abreviacion} - ${parte.nombre}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error al cargar las partes disponibles:', xhr.responseText);
            }
        });
    }

    function loadPartesSeccionesForEditSegundaSeccion(parteIdSeleccionada) {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes-segunda-seccion-disponibles`,
            method: 'GET',
            data: {
                include_selected: parteIdSeleccionada  // Incluir la parte seleccionada aunque no esté activa
            },
            async: false,
            success: function(response) {
                if (response.success) {
                    const select = $('#parte_id_segunda_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    // Cargar todas las partes activas y la parte seleccionada
                    response.data.forEach(function(parte) {
                        const selected = parte.id == parteIdSeleccionada ? 'selected' : '';
                        select.append(`<option value="${parte.id}"  data-tiempo="${parte.tiempo}" data-tipo="${parte.tipo}" data-abreviacion="${parte.abreviacion}" ${selected}>${parte.abreviacion} - ${parte.nombre}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error al cargar las partes disponibles:', xhr.responseText);
            }
        });
    }

    function loadAyudantesByEncargadoAndParte(encargadoId, parteId, ayudanteSeleccionado = null) {
        // Obtener el ID de la parte programa que se está editando
        const editingId = $('#parte_programa_segunda_seccion_id').val();
        const url = `/ayudantes-por-encargado/${encargadoId}/${parteId}` + (editingId ? `?editing_id=${editingId}` : '');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#ayudante_id_segunda_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(usuario) {
                            if (usuario.is_section) {
                                // Agregar encabezado de sección (deshabilitado para selección)
                                select.append(`<option value="" disabled style="font-weight: bold; background-color: #f8f9fa;">${usuario.name}</option>`);
                            } else {
                                // Agregar usuario normal
                                const selected = ayudanteSeleccionado && usuario.id == ayudanteSeleccionado ? 'selected' : '';
                                select.append(`<option value="${usuario.id}" ${selected}>${usuario.display_text}</option>`);
                            }
                        });
                    }

                    select.trigger('change');
                } else {
                    const select = $('#ayudante_id_segunda_seccion');
                    select.empty().append('<option value="">No hay ayudantes disponibles</option>').trigger('change');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar ayudantes:', xhr.responseText);
                const select = $('#ayudante_id_segunda_seccion');
                select.empty().append('<option value="">Error al cargar ayudantes</option>').trigger('change');
            }
        });
    }


    // Funciones para limpiar campos de reemplazados
    function clearEncargadoReemplazado() {
        $('#encargado_reemplazado_segunda_seccion').val('');
        $('#encargado_reemplazado_id_segunda_seccion').val('');
    }

    function clearAyudanteReemplazado() {
        $('#ayudante_reemplazado_segunda_seccion').val('');
        $('#ayudante_reemplazado_id_segunda_seccion').val('');
    }

    function agregarEncargadoReemplazado() {
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        const encargadoNombre = $('#encargado_display_segunda_seccion').val();

        if (encargadoId && encargadoNombre) {
            // Agregar el nombre al campo visible
            $('#encargado_reemplazado_segunda_seccion').val(encargadoNombre);

            // Agregar el ID al campo oculto para ser guardado en la BD
            $('#encargado_reemplazado_id_segunda_seccion').val(encargadoId);


        } else {
            alert('Por favor seleccione un encargado primero');
        }
    }

    function agregarAyudanteReemplazado() {
        const ayudanteId = $('#ayudante_id_segunda_seccion').val();
        const ayudanteNombre = $('#ayudante_display_segunda_seccion').val();

        if (ayudanteId && ayudanteNombre) {
            // Agregar el nombre al campo visible
            $('#ayudante_reemplazado_segunda_seccion').val(ayudanteNombre);

            // Agregar el ID al campo oculto para ser guardado en la BD
            $('#ayudante_reemplazado_id_segunda_seccion').val(ayudanteId);


        } else {
            alert('Por favor seleccione un ayudante primero');
        }
    }

    // Funciones para manejar reemplazados
    function manejarEncargadoReemplazado() {
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        const encargadoNombre = $('#encargado_display_segunda_seccion').val();

        if (encargadoId && encargadoNombre) {
            // Mostrar campos de reemplazados si están ocultos
            $('#campos-reemplazados-segunda-seccion').show();

            // Copiar el encargado actual como reemplazado
            $('#encargado_reemplazado_segunda_seccion').val(encargadoNombre);
            $('#encargado_reemplazado_id_segunda_seccion').val(encargadoId);
        } else {
            alert('Por favor seleccione un encargado primero');
        }
    }

    function manejarAyudanteReemplazado() {
        const ayudanteId = $('#ayudante_id_segunda_seccion').val();
        const ayudanteNombre = $('#ayudante_display_segunda_seccion').val();

        if (ayudanteId && ayudanteNombre) {
            // Mostrar campos de reemplazados si están ocultos
            $('#campos-reemplazados-segunda-seccion').show();

            // Copiar el ayudante actual como reemplazado
            $('#ayudante_reemplazado_segunda_seccion').val(ayudanteNombre);
            $('#ayudante_reemplazado_id_segunda_seccion').val(ayudanteId);
        } else {
            alert('Por favor seleccione un ayudante primero');
        }
    }

    // Funciones para los botones de la segunda sección
    function buscarEncargadoSegundaSeccion() {
        const parteId = $('#parte_id_segunda_seccion').val();
        //obstenemos la abreviacion de la parte seleccionada
        const abreviacionParte = $('#parte_id_segunda_seccion').find('option:selected').data('abreviacion');
        if (!parteId) {
            alert('Por favor seleccione una parte primero');
            return;
        }

        // Obtener el tipo de la parte seleccionada
        const selectedOption = $('#parte_id_segunda_seccion').find('option:selected');
        const tipoParte = selectedOption.data('tipo');
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        // Abrir modal y cargar usuarios con participaciones en la parte seleccionada
        $('#buscarEncargadoSegundaSeccionModal').modal('show');

        // Mostrar/ocultar filtros de sexo según el tipo de parte
        const contenedorFiltroSexo = $('#filtro_sexo_encargado_segunda_container');
        if (tipoParte == 1) {
            // Tipo 1: Solo mostrar "Hombres" y forzar filtro a hombres
            contenedorFiltroSexo.show();
            $('input[name="filtro_sexo_encargado_segunda"][value="2"]').closest('.form-check').hide(); // Ocultar Mujeres
            $('input[name="filtro_sexo_encargado_segunda"][value="1"]').closest('.form-check').show(); // Mostrar Hombres
            $('input[name="filtro_sexo_encargado_segunda"][value="1"]').prop('checked', true); // Forzar selección Hombres
            $('input[name="filtro_sexo_encargado_segunda"]').prop('disabled', true); // Deshabilitar cambio
        } else {
            // Otros tipos: Mostrar ambos filtros
            contenedorFiltroSexo.show();
            $('input[name="filtro_sexo_encargado_segunda"][value="1"]').closest('.form-check').show(); // Mostrar Hombres
            $('input[name="filtro_sexo_encargado_segunda"][value="2"]').closest('.form-check').show(); // Mostrar Mujeres
            $('input[name="filtro_sexo_encargado_segunda"]').prop('disabled', false); // Habilitar cambio
        }

        // Al abrir el modal, deshabilitar el botón Seleccionar
        $('#confirmarEncargadoSegundaSeccion').prop('disabled', true);

        // Determinar el sexo por defecto basado en el tipo de parte y el encargado actual
        let sexoPorDefecto = 2; // Por defecto: Mujeres
        if (encargadoId) {
            $.ajax({
                url: `/verificar-sexo-encargado`,
                method: 'GET',
                data: { encargado_id: encargadoId },
                async: false,
                success: function(response) {
                    if (response.success && response.encargado_sexo) {
                        sexoPorDefecto = response.encargado_sexo;
                    }
                }
            });
        }
        // Listener para cambios en los radio buttons de filtro por sexo
        $(document).off('change.filtro_sexo_encargado_segunda').on('change.filtro_sexo_encargado_segunda', 'input[name="filtro_sexo_encargado_segunda"]', function() {
            cargarEncargadosSegundaSeccionPorSexo(parteId);
        });

        // Si el tipo de parte es 1, forzar sexo a Hombres (1)
        if (tipoParte == 1) {
            sexoPorDefecto = 1;
        }
        // Seleccionar el radio button correspondiente (solo si está visible)
        const radioButton = $(`input[name="filtro_sexo_encargado_segunda"][value="${sexoPorDefecto}"]`);
        radioButton.prop('checked', true);

        // Cargar usuarios con el filtro determinado
        $.ajax({
            url: `/encargados-por-parte-programa-smm/${parteId}`,
            method: 'GET',
            data: { sexo: sexoPorDefecto },
            async: false,
            success: function(response) {
                if (response.success) {
                    const select = $('#select_encargado_segunda_seccion');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar encargado...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarEncargadoSegundaSeccionModal')
                        });
                    }

                    select.append('<option value="">Seleccionar encargado...</option>');

                    // Agregar opciones con el formato: fecha|abreviacion|nombre
                    response.data.forEach(function(usuario) {
                        select.append(`<option value="${usuario.id}">${usuario.display_text}</option>`);
                    });

                    // Inicializar Select2 para el historial si no está ya inicializado
                    const selectHistorial = $('#select_historial_encargado_segunda_seccion');
                    if (!selectHistorial.hasClass('select2-hidden-accessible')) {
                        selectHistorial.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Historial del Encargado...",
                            width: '100%',
                            dropdownParent: $('#buscarEncargadoSegundaSeccionModal')
                        });
                    }

                    // Preseleccionar el encargado actual si existe
                    const encargadoActual = $('#encargado_id_segunda_seccion').val();

                    // Preseleccionar el encargado actual si existe (ya declarado arriba)
                    if (encargadoActual) {
                        select.val(encargadoActual);
                        loadHistorialEncargadoSegundaSeccion(encargadoActual,parteId,abreviacionParte);
                    }

                    // Cuando se seleccione un encargado, habilitar el botón Seleccionar y cargar historial
                    select.off('change.select_encargado_segunda_seccion').on('change.select_encargado_segunda_seccion', function() {
                        const encargadoSeleccionado = $(this).val();
                        const parteId = $('#parte_id_segunda_seccion').val();
                        //Vaciamos el select de historial
                        $('#confirmarEncargadoSegundaSeccion').prop('disabled', !encargadoSeleccionado);
                        // Cargar historial del encargado seleccionado
                        if (encargadoSeleccionado) {
                            loadHistorialEncargadoSegundaSeccion(encargadoSeleccionado,parteId,abreviacionParte);
                        } else {
                            clearHistorialEncargadoSegundaSeccion();
                        }
                    });
                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios participantes');
                console.error(xhr);
            }
        });
    }

    // Función para cargar encargados filtrados por sexo
    function cargarEncargadosSegundaSeccionPorSexo(parteId) {
        const sexoSeleccionado = $('input[name="filtro_sexo_encargado_segunda"]:checked').val();
        //deshabilitar select mientras carga
        $('#select_encargado_segunda_seccion').prop('disabled', true);
        $.ajax({
            url: `/encargados-por-parte-programa-smm/${parteId}`,
            method: 'GET',
            data: { sexo: sexoSeleccionado },
            success: function(response) {
                if (response.success) {
                    const select = $('#select_encargado_segunda_seccion');

                    // Guardar el valor actual antes de limpiar
                    const valorActual = select.val();

                    select.empty();
                    select.append('<option value="">Seleccionar encargado...</option>');

                    // Agregar opciones con el formato: fecha|abreviacion|nombre
                    response.data.forEach(function(usuario) {
                        select.append(`<option value="${usuario.id}">${usuario.display_text}</option>`);
                    });

                    // Intentar mantener la selección anterior si el usuario sigue en la lista
                    if (valorActual && select.find(`option[value="${valorActual}"]`).length > 0) {
                        select.val(valorActual).trigger('change');
                    } else {
                        select.val('').trigger('change');
                    }
                    $('#select_encargado_segunda_seccion').prop('disabled', false);
                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios participantes');
                console.error(xhr);
            }
        });
    }

    function buscarAyudanteSegundaSeccion() {
        const parteId = $('#parte_id_segunda_seccion').val();
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        const ayudanteId = $('#ayudante_id_segunda_seccion').val();
        const selectedOption = $('#parte_id_segunda_seccion').find('option:selected');
        const tipo = selectedOption.data('tipo');
        const encargadoNombre = $('#encargado_display_segunda_seccion').val();
        $('#buscarAyudanteSegundaSeccionModalLabel').text(`Buscar Ayudante para: ${encargadoNombre || '...'}`);
        if (!parteId) {
            alert('Por favor seleccione una parte primero');
            return;
        }

        // Validar que el tipo de parte no sea 1
        if (tipo == 1) {
            alert('No se puede buscar ayudante para esta Asignación');
            return;
        }

        // Abrir modal y cargar usuarios con participaciones como ayudante
        $('#buscarAyudanteSegundaSeccionModal').modal('show');

        // Determinar si mostrar filtros de sexo (solo para tipo=3)
        const contenedorFiltroSexo = $('#filtro_sexo_ayudante_segunda_container');
        let sexoForzado = null;
        if (tipo == 3) { //Asignación Hombre y Mujer
            contenedorFiltroSexo.show();
            // Obtener el sexo del encargado
            if (ayudanteId) {
                 // Se buscan ayudantes según el sexo del seleccionado
                $.ajax({
                    url: `/verificar-sexos-usuarios`,
                    method: 'GET',
                    data: { encargado_id: encargadoId, ayudante_id: ayudanteId },
                    async: false,
                    success: function(response) {
                        if (response.success && response.ayudante_sexo) {
                            sexoForzado = response.ayudante_sexo;
                        }
                    }
                });
            }else{
                // Se buscan ayudantes según el sexo del encargado
                $.ajax({
                    url: `/verificar-sexo-encargado`,
                    method: 'GET',
                    data: { encargado_id: encargadoId },
                    async: false,
                    success: function(response) {
                        if (response.success && response.encargado_sexo) {
                            sexoForzado = response.encargado_sexo;
                        }
                    }
                });
            }

            if (sexoForzado == 1) {
                // Encargado es hombre: Solo mostrar "Hombres"
                $('input[name="filtro_sexo_ayudante_segunda"][value="1"]').prop('checked', true);
                //$('input[name="filtro_sexo_ayudante_segunda"]').prop('disabled', true);
            } else if (sexoForzado == 2) {
                // Encargado es mujer: Solo mostrar "Mujeres"
                $('input[name="filtro_sexo_ayudante_segunda"][value="2"]').prop('checked', true);
                //$('input[name="filtro_sexo_ayudante_segunda"]').prop('disabled', true);
            } else {
                // No hay encargado: Mostrar ambos pero forzar Mujeres por defecto
                $('input[name="filtro_sexo_ayudante_segunda"][value="2"]').prop('checked', true);
                sexoForzado = 2; // Forzar Mujeres si no hay encargado
            }
        } else {
            // Otros tipos: Ocultar filtros
            contenedorFiltroSexo.hide();
        }

        // Al abrir el modal, deshabilitar el botón Seleccionar
        $('#confirmarAyudanteSegundaSeccion').prop('disabled', true);

        // Cuando se seleccione un ayudante, habilitar el botón Seleccionar y cargar historial
        $(document).off('change.select_ayudante_segunda_seccion').on('change.select_ayudante_segunda_seccion', '#select_ayudante_segunda_seccion', function() {
            const val = $(this).val();
            $('#confirmarAyudanteSegundaSeccion').prop('disabled', !val);

            // Cargar historial del ayudante seleccionado
            if (val) {
                loadHistorialAyudanteSegundaSeccion(val, parteId);
            } else {
                clearHistorialAyudanteSegundaSeccion();
            }
        });

        // Listener para cambios en los radio buttons de filtro por sexo (solo si tipo=3)
        if (tipo == 3) {
            $(document).off('change.filtro_sexo_ayudante_segunda').on('change.filtro_sexo_ayudante_segunda', 'input[name="filtro_sexo_ayudante_segunda"]', function() {
                cargarAyudantesSegundaSeccionPorSexo(parteId, encargadoId,tipo);
            });
        }

        // Cargar usuarios que han participado como ayudantes
        let url = `/ayudantes-por-parte-programa-smm/${parteId}`;
        let params = {};

        if (encargadoId) {
            params.encargado_id = encargadoId;
        }

        // Si es tipo 3, agregar el filtro de sexo forzado
        if (tipo == 3 && sexoForzado) {
            params.sexo = sexoForzado;
        }

        $.ajax({
            url: url,
            method: 'GET',
            data: params,
            async: false,
            success: function(response) {
                if (response.success) {
                    const select = $('#select_ayudante_segunda_seccion');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar ayudante...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarAyudanteSegundaSeccionModal')
                        });
                    }

                    select.append('<option value="">Seleccionar ayudante...</option>');

                    // Verificar si hay secciones de género
                    if (response.has_gender_sections) {
                        // Agregar opciones con secciones de género
                        response.data.forEach(function(usuario) {
                            if (usuario.is_section) {
                                // Es una sección (Hombres o Mujeres)
                                select.append(`<option disabled style="font-weight: bold;">${usuario.display_text}</option>`);
                            } else {
                                // Es un usuario normal
                                select.append(`<option value="${usuario.id}">${usuario.display_text}</option>`);
                            }
                        });
                    } else {
                        // Sin secciones de género, agregar normalmente
                        response.data.forEach(function(usuario) {
                            select.append(`<option value="${usuario.id}">${usuario.display_text}</option>`);
                        });
                    }

                    // Inicializar Select2 para el historial si no está ya inicializado
                    const selectHistorial = $('#select_historial_ayudante_segunda_seccion');
                    if (!selectHistorial.hasClass('select2-hidden-accessible')) {
                        selectHistorial.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Seleccionar un ayudante primero...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarAyudanteSegundaSeccionModal')
                        });
                    }

                    // Preseleccionar el ayudante actual si existe
                    const ayudanteActual = $('#ayudante_id_segunda_seccion').val();
                    if (ayudanteActual) {
                        select.val(ayudanteActual).trigger('change');
                    }
                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios ayudantes');
                console.error(xhr);
            }
        });
    }

     // Función para cargar ayudantes filtrados por sexo
    function cargarAyudantesSegundaSeccionPorSexo(parteId,encargadoId,tipo) {
        const sexoSeleccionado = $('input[name="filtro_sexo_ayudante_segunda"]:checked').val();
        //deshabilitar el select mientras carga
        $('#select_ayudante_segunda_seccion').prop('disabled', true);

        let url = `/ayudantes-por-parte-programa-smm/${parteId}`;
        let params = {};

        if (encargadoId) {
            params.encargado_id = encargadoId;
        }

        // Si es tipo 3 y hay sexo seleccionado, agregarlo
        if (tipo == 3 && sexoSeleccionado) {
            params.sexo = sexoSeleccionado;
        }

        $.ajax({
            url: url,
            method: 'GET',
            data: params,
            success: function(response) {
                if (response.success) {
                    const select = $('#select_ayudante_segunda_seccion');

                    // Guardar el valor actual antes de limpiar
                    const valorActual = select.val();

                    select.empty();
                    select.append('<option value="">Seleccionar ayudante...</option>');

                    // Verificar si hay secciones de género
                    if (response.has_gender_sections) {
                        // Agregar opciones con secciones de género
                        response.data.forEach(function(usuario) {
                            if (usuario.is_section) {
                                select.append(`<option disabled style="font-weight: bold;">${usuario.display_text}</option>`);
                            } else {
                                select.append(`<option value="${usuario.id}">${usuario.display_text}</option>`);
                            }
                        });
                    } else {
                        // Sin secciones de género, agregar normalmente
                        response.data.forEach(function(usuario) {
                            select.append(`<option value="${usuario.id}">${usuario.display_text}</option>`);
                        });
                    }

                    // Intentar mantener la selección anterior si el usuario sigue en la lista
                    if (valorActual && select.find(`option[value="${valorActual}"]`).length > 0) {
                        select.val(valorActual).trigger('change');
                    } else {
                        select.val('').trigger('change');
                    }
                    $('#select_ayudante_segunda_seccion').prop('disabled', false);
                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios ayudantes');
                console.error(xhr);
            }
        });
    }

    function verHistorialAyudanteSegundaSeccion() {
        const ayudanteId = $('#ayudante_id_segunda_seccion').val();
        if (!ayudanteId) {
            alert('No hay ayudante seleccionado');
            return;
        }

        // Aquí iría la lógica para mostrar el historial del ayudante
        alert('Función verHistorialAyudanteSegundaSeccion() - Por implementar\nAyudante ID: ' + ayudanteId);
    }

    // Función para actualizar el estado de los botones de la segunda sección
    function updateButtonStatesSegundaSeccion() {
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        const ayudanteId = $('#ayudante_id_segunda_seccion').val();
        const parteSeleccionada = $('#parte_id_segunda_seccion').val();
        const btnBuscarEncargado = $('#btn-buscar-encargado-segunda');
        const btnBuscarAyudante = $('#btn-buscar-ayudante-segunda');

        // Botones del encargado
        if (encargadoId) {
            $('#btn-encargado-reemplazado-segunda').prop('disabled', false);
            btnBuscarEncargado.prop('disabled', false);
            btnBuscarEncargado.attr('title', 'Buscar Estudiante');
        } else {
            $('#btn-encargado-reemplazado-segunda').prop('disabled', true);
            btnBuscarEncargado.prop('disabled', true);
            btnBuscarEncargado.attr('title', 'Seleccionar estudiante primero');
        }

        if (encargadoId && encargadoId !== '' && parteSeleccionada) {
            const selectedOption = $('#parte_id_segunda_seccion').find('option:selected');
            const tipo = selectedOption.data('tipo');

            if (tipo == 2 || tipo == 3) {
                btnBuscarAyudante.prop('disabled', false);
                btnBuscarAyudante.attr('title', 'Buscar Ayudante');
            } else {
                btnBuscarAyudante.prop('disabled', true);
                btnBuscarAyudante.attr('title', 'No disponible para este tipo de parte');
            }
        } else {
            btnBuscarAyudante.prop('disabled', true);
            btnBuscarAyudante.attr('title', 'Seleccionar estudiante y parte con tipo 2 o 3');
        }

        // Botones del ayudante
        if (ayudanteId) {
            $('#btn-ayudante-reemplazado-segunda').prop('disabled', false);
        } else {
            $('#btn-ayudante-reemplazado-segunda').prop('disabled', true);
        }
    }


    // Funciones globales para uso en onclick
    window.openCreateParteModal = openCreateParteModal;
    window.editParte = editParte;
    window.deleteParte = deleteParte;
    window.loadPartesSecciones = loadPartesSecciones;
    window.loadPartesSeccionesForEdit = loadPartesSeccionesForEdit;
    window.openCreateParteSegundaSeccionModal = openCreateParteSegundaSeccionModal;
    window.editParteSegundaSeccion = editParteSegundaSeccion;
    window.deleteParteSegundaSeccion = deleteParteSegundaSeccion;
    window.moveParteUp = moveParteUp;
    window.moveParteDown = moveParteDown;
    window.moveParteSegundaSeccionUp = moveParteSegundaSeccionUp;
    window.moveParteSegundaSeccionDown = moveParteSegundaSeccionDown;
    window.clearEncargadoReemplazado = clearEncargadoReemplazado;
    window.clearAyudanteReemplazado = clearAyudanteReemplazado;
    window.agregarEncargadoReemplazado = agregarEncargadoReemplazado;
    window.agregarAyudanteReemplazado = agregarAyudanteReemplazado;
    window.manejarEncargadoReemplazado = manejarEncargadoReemplazado;
    window.manejarAyudanteReemplazado = manejarAyudanteReemplazado;

    // Funciones para Nuestra Vida Cristiana (NVC)
    window.openCreateParteNVModal = openCreateParteNVModal;
    window.editParteNV = editParteNV;
    window.deleteParteNV = deleteParteNV;
    window.moveParteNVUp = moveParteNVUp;
    window.moveParteNVDown = moveParteNVDown;
    window.agregarEncargadoReemplazadoNV = agregarEncargadoReemplazadoNV;
    window.eliminarEncargadoReemplazadoNV = eliminarEncargadoReemplazadoNV;

    // Funciones para mover partes arriba y abajo
    function moveParteUp(id) {
        // Verificar si el botón está deshabilitado
        const button = event.target.closest('button');
        if (button && button.disabled) {
            return;
        }

        moveParte(id, 'up');
    }

    function moveParteDown(id) {
        // Verificar si el botón está deshabilitado
        const button = event.target.closest('button');
        if (button && button.disabled) {
            return;
        }

        moveParte(id, 'down');
    }

    function moveParteSegundaSeccionUp(id) {
        // Verificar si el botón está deshabilitado
        const button = event.target.closest('button');
        if (button && button.disabled) {
            return;
        }

        moveParte(id, 'up');
    }

    function moveParteSegundaSeccionDown(id) {
        // Verificar si el botón está deshabilitado
        const button = event.target.closest('button');
        if (button && button.disabled) {
            return;
        }

        moveParte(id, 'down');
    }

    function moveParte(id, direction, callback) {
        const url = `/partes-programa/${id}/move-${direction}`;

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Si se proporciona un callback específico, úsalo
                    if (callback && typeof callback === 'function') {
                        callback();
                    } else {
                        // Recargar ambas tablas para reflejar los cambios (comportamiento por defecto)
                        loadPartesPrograma();
                        loadPartesSegundaSeccion();
                    }
                    showAlert('alert-container', 'success', response.message);
                } else {
                    showAlert('alert-container', 'warning', response.message);
                }
            },
            error: function(xhr) {
                console.error('AJAX error:', xhr);
                const errorMessage = xhr.responseJSON?.message || 'Error al mover la parte';
                showAlert('alert-container', 'danger', errorMessage);
            }
        });
    }

    // Funciones para los botones del campo Orador Inicial (solo para coordinadores)
    function buscarOradorInicial() {
        // Abrir modal y cargar usuarios con asignación de oración
        $('#buscarOradorInicialModal').modal('show');

        // Cargar usuarios con asignación_id=23 y su historial
        $.ajax({
            url: '/usuarios-orador-inicial',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_orador_inicial');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar orador...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarOradorInicialModal')
                        });
                    }

                    select.append('<option value="">Seleccionar orador inicial...</option>');

                    // Agregar opciones con el formato: fecha - nombre
                    response.data.forEach(function(usuario) {
                        let fechaTexto = 'Primera vez';
                        if (usuario.ultima_fecha) {
                            const fecha = new Date(usuario.ultima_fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            fechaTexto = `${dia}/${mes}/${año}`;
                        }
                        const textoOpcion = `${fechaTexto} - ${usuario.name}`;
                        select.append(`<option value="${usuario.id}">${textoOpcion}</option>`);
                    });

                    // Preseleccionar el orador actual si existe
                    const oradorActual = $('#orador_inicial').val();
                    if (oradorActual) {
                        select.val(oradorActual).trigger('change');
                    }

                    // Inicializar el select2 del historial también
                    const selectHistorial = $('#select_historial_orador');
                    if (!selectHistorial.hasClass('select2-hidden-accessible')) {
                        selectHistorial.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Historial del Encargado...",
                            width: '100%',
                            dropdownParent: $('#buscarOradorInicialModal')
                        });
                    }
                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios para orador inicial');
                console.error(xhr);
            }
        });
    }

    function verHistorialOradorInicial() {
        const oradorId = $('#orador_inicial').val();
        if (!oradorId) {
            alert('No hay orador inicial seleccionado para mostrar historial');
            return;
        }

        // Abrir modal principal y cargar historial del orador
        $('#buscarOradorInicialModal').modal('show');

        // Cargar historial de participaciones del usuario
        $.ajax({
            url: `/usuarios/${oradorId}/historial-orador`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_historial_orador');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Historial del Encargado...",
                            width: '100%',
                            dropdownParent: $('#buscarOradorInicialModal')
                        });
                    }

                    if (response.data.length > 0) {
                        select.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato: fecha - nombre - tipo
                        response.data.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            const tipoOracion = participacion.tipo === 'inicial' ? 'Orador Inicial' : 'Orador Final';
                            const textoOpcion = `${fechaTexto} - ${participacion.nombre_usuario} - ${tipoOracion}`;

                            select.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        // Habilitar el select
                        select.prop('disabled', false);

                        // Seleccionar automáticamente el primer elemento (índice 1, ya que 0 es "Seleccionar participación...")
                        select.val(response.data[0].programa_id).trigger('change');

                        // Actualizar el título del modal con el nombre del usuario
                        $('#buscarOradorInicialModalLabel').html(`<i class="fas fa-history me-2"></i>Historial de ${response.data[0].nombre_usuario}`);
                    } else {
                        select.append('<option value="">No hay participaciones registradas</option>');
                        select.prop('disabled', true);
                    }
                } else {
                    alert('Error al cargar el historial: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar el historial del orador inicial');
                console.error(xhr);
            }
        });
    }

    // Evento para confirmar la selección del orador inicial
    // Evento para confirmar la selección del orador inicial
    $('#confirmarOradorInicial').on('click', function() {
        const oradorSeleccionado = $('#select_orador_inicial').val();
        const textoSeleccionado = $('#select_orador_inicial option:selected').text();

        if (!oradorSeleccionado) {
            alert('Por favor seleccione un orador inicial');
            return;
        }

        // Extraer solo el nombre del formato "fecha - nombre"
        let nombreOrador = textoSeleccionado;
        if (textoSeleccionado.includes(' - ')) {
            nombreOrador = textoSeleccionado.split(' - ')[1];
        }

        // Obtener datos del formulario
        const programaId = $('#programa_id').val();
        const fecha = $('#fecha').val();
        const estado = $('#estado').val();

        // Preparar datos para la actualización
        const updateData = {
            fecha: fecha,
            orador_inicial: oradorSeleccionado,
            estado: estado,
            // Incluir otros campos que puedan existir para evitar errores de validación
            presidencia: $('#presidencia').val() || null,
            cancion_pre: $('#cancion_pre').val() || null,
            cancion_en: $('#cancion_en').val() || null,
            cancion_post: $('#cancion_post').val() || null,
            orador_final: $('#orador_final').val() || null
        };

        // Mostrar indicador de carga
        const button = $(this);
        const originalText = button.html();
        button.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...').prop('disabled', true);

        // Hacer la llamada AJAX para actualizar el programa
        $.ajax({
            url: `/programas/${programaId}`,
            type: 'PUT',
            data: updateData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Actualizar los campos del formulario
                    $('#orador_inicial').val(oradorSeleccionado);
                    $('#orador_inicial_display').val(nombreOrador);

                    // Cerrar modal
                    $('#buscarOradorInicialModal').modal('hide');

                    // Mostrar modal de éxito
                    $('#successModal').modal('show');

                    // Ocultar el modal automáticamente después de 2 segundos con fade out
                    setTimeout(function() {
                        $('#successModal').modal('hide');
                    }, 2000);
                } else {
                    alert('Error al guardar el orador inicial: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error al guardar el orador inicial';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage += ': ' + errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                }
                alert(errorMessage);
            },
            complete: function() {
                // Restaurar el botón original
                button.html(originalText).prop('disabled', false);
            }
        });
    });

    // Limpiar Select2 cuando se cierre el modal
    $('#buscarOradorInicialModal').on('hidden.bs.modal', function() {
        const select = $('#select_orador_inicial');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');

        // Limpiar también el select del historial
        const selectHistorial = $('#select_historial_orador');
        if (selectHistorial.hasClass('select2-hidden-accessible')) {
            selectHistorial.select2('destroy');
        }
        selectHistorial.empty().append('<option value="">Seleccione un orador para ver historial...</option>');
        selectHistorial.prop('disabled', true);

        // Restaurar el título original del modal
        $('#buscarOradorInicialModalLabel').html('<i class="fas fa-search me-2"></i>Buscar Orador Inicial');
    });

    // Evento para cargar historial automáticamente cuando se selecciona un orador
    $('#select_orador_inicial').on('change', function() {
        const oradorId = $(this).val();
        if (!oradorId) {
            // Limpiar el historial si no hay orador seleccionado
            const selectHistorial = $('#select_historial_orador');
            selectHistorial.empty().append('<option value="">Seleccione un orador para ver historial...</option>');
            selectHistorial.prop('disabled', true);
            return;
        }

        // Cargar historial del orador seleccionado
        $.ajax({
            url: `/usuarios/${oradorId}/historial-orador`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_historial_orador');
                    select.empty();

                    if (response.data.length > 0) {
                        select.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato: fecha - nombre - tipo
                        response.data.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            const tipoOracion = participacion.tipo === 'inicial' ? 'Orador Inicial' : 'Orador Final';
                            const textoOpcion = `${fechaTexto} - ${participacion.nombre_usuario} - ${tipoOracion}`;

                            select.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        // Habilitar el select
                        select.prop('disabled', false);

                        // Seleccionar automáticamente el primer elemento
                        select.val(response.data[0].programa_id).trigger('change');
                    } else {
                        select.append('<option value="">No hay participaciones registradas</option>');
                        select.prop('disabled', true);
                    }
                } else {
                    console.error('Error al cargar el historial:', response.message);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar el historial del orador inicial:', xhr);
            }
        });
    });

    // Evento para confirmar la selección del orador final
    $('#confirmarOradorFinal').on('click', function() {
        const oradorSeleccionado = $('#select_orador_final').val();
        const textoSeleccionado = $('#select_orador_final option:selected').text();

        if (!oradorSeleccionado) {
            alert('Por favor seleccione un orador final');
            return;
        }

        // Extraer solo el nombre del formato "fecha - nombre"
        let nombreOrador = textoSeleccionado;
        if (textoSeleccionado.includes(' - ')) {
            nombreOrador = textoSeleccionado.split(' - ')[1];
        }

        // Obtener datos del formulario
        const programaId = $('#programa_id').val();
        const fecha = $('#fecha').val();
        const estado = $('#estado').val();

        // Preparar datos para la actualización
        const updateData = {
            fecha: fecha,
            orador_final: oradorSeleccionado,
            estado: estado,
            // Incluir otros campos que puedan existir para evitar errores de validación
            orador_inicial: $('#orador_inicial').val() || null,
            presidencia: $('#presidencia').val() || null,
            cancion_pre: $('#cancion_pre').val() || null,
            cancion_en: $('#cancion_en').val() || null,
            cancion_post: $('#cancion_post').val() || null
        };

        // Mostrar indicador de carga
        const button = $(this);
        const originalText = button.html();
        button.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...').prop('disabled', true);

        // Hacer la llamada AJAX para actualizar el programa
        $.ajax({
            url: `/programas/${programaId}`,
            type: 'PUT',
            data: updateData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Actualizar los campos del formulario
                    $('#orador_final').val(oradorSeleccionado);
                    $('#orador_final_display').val(nombreOrador);

                    // Cerrar modal
                    $('#buscarOradorFinalModal').modal('hide');

                    // Mostrar modal de éxito
                    $('#successModal').modal('show');

                    // Ocultar el modal automáticamente después de 2 segundos con fade out
                    setTimeout(function() {
                        $('#successModal').modal('hide');
                    }, 2000);
                } else {
                    alert('Error al guardar el orador final: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error al guardar el orador final';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage += ': ' + errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                }
                alert(errorMessage);
            },
            complete: function() {
                // Restaurar el botón original
                button.html(originalText).prop('disabled', false);
            }
        });
    });

    // Limpiar Select2 cuando se cierre el modal
    $('#buscarOradorFinalModal').on('hidden.bs.modal', function() {
        const select = $('#select_orador_final');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');

        // Limpiar también el select del historial
        const selectHistorial = $('#select_historial_orador_final');
        if (selectHistorial.hasClass('select2-hidden-accessible')) {
            selectHistorial.select2('destroy');
        }
        selectHistorial.empty().append('<option value="">Seleccione un orador para ver historial...</option>');
        selectHistorial.prop('disabled', true);

        // Restaurar el título original del modal
        $('#buscarOradorFinalModalLabel').html('<i class="fas fa-search me-2"></i>Buscar Orador Final');
    });

    // Evento para cargar historial automáticamente cuando se selecciona un orador final
    $('#select_orador_final').on('change', function() {
        const oradorId = $(this).val();
        if (oradorId) {
            // Cargar historial del orador seleccionado
            $.ajax({
                url: `/usuarios/${oradorId}/historial-orador`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const select = $('#select_historial_orador_final');
                        select.empty();

                        if (response.data.length > 0) {
                            select.append('<option value="">Seleccionar participación...</option>');

                            // Agregar opciones con el formato: fecha - nombre - tipo
                            response.data.forEach(function(participacion) {
                                const fecha = new Date(participacion.fecha);
                                const dia = String(fecha.getDate()).padStart(2, '0');
                                const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                                const año = fecha.getFullYear();
                                const fechaTexto = `${dia}/${mes}/${año}`;

                                const tipoOracion = participacion.tipo === 'inicial' ? 'Orador Inicial' : 'Orador Final';
                                const textoOpcion = `${fechaTexto} - ${participacion.nombre_usuario} - ${tipoOracion}`;

                                select.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                            });

                            // Habilitar el select
                            select.prop('disabled', false);

                            // Seleccionar automáticamente el primer elemento
                            select.val(response.data[0].programa_id).trigger('change');
                        } else {
                            select.append('<option value="">No hay participaciones registradas</option>');
                            select.prop('disabled', true);
                        }
                    } else {
                        console.error('Error al cargar el historial: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Error al cargar el historial de orador final', xhr);
                }
            });
        } else {
            // Limpiar el select del historial si no hay orador seleccionado
            const select = $('#select_historial_orador_final');
            select.empty().append('<option value="">Seleccione un orador para ver historial...</option>');
            select.prop('disabled', true);
        }
    });

    // Evento para confirmar la selección de presidencia
    $('#confirmarPresidencia').on('click', function() {
        const presidenteSeleccionado = $('#select_presidencia').val();
        const textoSeleccionado = $('#select_presidencia option:selected').text();

        if (!presidenteSeleccionado) {
            alert('Por favor seleccione un presidente');
            return;
        }

        // Extraer solo el nombre del formato "fecha - nombre"
        let nombrePresidente = textoSeleccionado;
        if (textoSeleccionado.includes(' - ')) {
            nombrePresidente = textoSeleccionado.split(' - ')[1];
        }

        // Obtener datos del formulario
        const programaId = $('#programa_id').val();
        const fecha = $('#fecha').val();
        const estado = $('#estado').val();

        // Preparar datos para la actualización
        const updateData = {
            fecha: fecha,
            presidencia: presidenteSeleccionado,
            estado: estado,
            // Incluir otros campos que puedan existir para evitar errores de validación
            orador_inicial: $('#orador_inicial').val() || null,
            cancion_pre: $('#cancion_pre').val() || null,
            cancion_en: $('#cancion_en').val() || null,
            cancion_post: $('#cancion_post').val() || null,
            orador_final: $('#orador_final').val() || null
        };

        // Mostrar indicador de carga
        const button = $(this);
        const originalText = button.html();
        button.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...').prop('disabled', true);

        // Hacer la llamada AJAX para actualizar el programa
        $.ajax({
            url: `/programas/${programaId}`,
            type: 'PUT',
            data: updateData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Actualizar los campos del formulario
                    $('#presidencia').val(presidenteSeleccionado);
                    $('#presidencia_display').val(nombrePresidente);

                    // Cerrar modal
                    $('#buscarPresidenciaModal').modal('hide');

                    // Mostrar modal de éxito
                    $('#successModal').modal('show');

                    // Ocultar el modal automáticamente después de 3 segundos con fade out
                    setTimeout(function() {
                        $('#successModal').modal('hide');
                    }, 2000);
                } else {
                    alert('Error al guardar el presidente: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error al guardar el presidente';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage += ': ' + errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                }
                alert(errorMessage);
            },
            complete: function() {
                // Restaurar el botón original
                button.html(originalText).prop('disabled', false);
            }
        });
    });

    // Limpiar Select2 cuando se cierre el modal
    $('#buscarPresidenciaModal').on('hidden.bs.modal', function() {
        const select = $('#select_presidencia');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');

        // Limpiar también el select del historial
        const selectHistorial = $('#select_historial_presidencia');
        if (selectHistorial.hasClass('select2-hidden-accessible')) {
            selectHistorial.select2('destroy');
        }
        selectHistorial.empty().append('<option value="">Seleccione un presidente para ver historial...</option>');
        selectHistorial.prop('disabled', true);

        // Restaurar el título original del modal
        $('#buscarPresidenciaModalLabel').html('<i class="fas fa-search me-2"></i>Buscar Presidentes');
    });

    // Evento para cargar historial automáticamente cuando se selecciona un presidente
    $('#select_presidencia').on('change', function() {
        const presidenteId = $(this).val();
        if (presidenteId) {
            // Cargar historial del presidente seleccionado
            $.ajax({
                url: `/usuarios/${presidenteId}/historial-presidencia`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const select = $('#select_historial_presidencia');
                        select.empty();

                        if (response.data.length > 0) {
                            select.append('<option value="">Seleccionar participación...</option>');

                            // Agregar opciones con el formato: fecha - nombre
                            response.data.forEach(function(participacion) {
                                const fecha = new Date(participacion.fecha);
                                const dia = String(fecha.getDate()).padStart(2, '0');
                                const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                                const año = fecha.getFullYear();
                                const fechaTexto = `${dia}/${mes}/${año}`;

                                const textoOpcion = `${fechaTexto} - ${participacion.nombre_usuario}`;

                                select.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                            });

                            // Habilitar el select
                            select.prop('disabled', false);

                            // Seleccionar automáticamente el primer elemento
                            select.val(response.data[0].programa_id).trigger('change');
                        } else {
                            select.append('<option value="">No hay participaciones registradas</option>');
                            select.prop('disabled', true);
                        }
                    } else {
                        console.error('Error al cargar el historial: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Error al cargar el historial de presidencia', xhr);
                }
            });
        } else {
            // Limpiar el select del historial si no hay presidente seleccionado
            const select = $('#select_historial_presidencia');
            select.empty().append('<option value="">Seleccione un presidente para ver historial...</option>');
            select.prop('disabled', true);
        }
    });

    // Eventos para confirmar selección de canciones
    $('#confirmarCancionInicial').on('click', function() {
        const cancionSeleccionada = $('#select_cancion_inicial').val();
        const textoSeleccionado = $('#select_cancion_inicial option:selected').text();

        if (!cancionSeleccionada) {
            alert('Por favor seleccione una canción');
            return;
        }

        // Obtener datos del formulario
        const programaId = $('#programa_id').val();
        const fecha = $('#fecha').val();
        const estado = $('#estado').val();

        // Preparar datos para la actualización
        const updateData = {
            fecha: fecha,
            cancion_pre: cancionSeleccionada,
            estado: estado,
            // Incluir otros campos que puedan existir para evitar errores de validación
            orador_inicial: $('#orador_inicial').val() || null,
            presidencia: $('#presidencia').val() || null,
            cancion_en: $('#cancion_en').val() || null,
            cancion_post: $('#cancion_post').val() || null,
            orador_final: $('#orador_final').val() || null
        };

        // Mostrar indicador de carga
        const button = $(this);
        const originalText = button.html();
        button.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...').prop('disabled', true);

        // Hacer la llamada AJAX para actualizar el programa
        $.ajax({
            url: `/programas/${programaId}`,
            type: 'PUT',
            data: updateData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Actualizar los campos del formulario
                    $('#cancion_pre').val(cancionSeleccionada);
                    $('#cancion_pre_display').val(textoSeleccionado);

                    // Cerrar modal
                    $('#buscarCancionInicialModal').modal('hide');

                    // Mostrar modal de éxito
                    $('#successModal').modal('show');

                    // Ocultar el modal automáticamente después de 3 segundos con fade out
                    setTimeout(function() {
                        $('#successModal').modal('hide');
                    }, 2000);
                } else {
                    alert('Error al guardar la canción inicial: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error al guardar la canción inicial';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage += ': ' + errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                }
                alert(errorMessage);
            },
            complete: function() {
                // Restaurar el botón original
                button.html(originalText).prop('disabled', false);
            }
        });
    });

    $('#confirmarCancionIntermedia').on('click', function() {
        const cancionSeleccionada = $('#select_cancion_intermedia').val();
        const textoSeleccionado = $('#select_cancion_intermedia option:selected').text();

        if (!cancionSeleccionada) {
            alert('Por favor seleccione una canción');
            return;
        }

        // Obtener datos del formulario
        const programaId = $('#programa_id').val();
        const fecha = $('#fecha').val();
        const estado = $('#estado').val();

        // Preparar datos para la actualización
        const updateData = {
            fecha: fecha,
            cancion_en: cancionSeleccionada,
            estado: estado,
            // Incluir otros campos que puedan existir para evitar errores de validación
            orador_inicial: $('#orador_inicial').val() || null,
            presidencia: $('#presidencia').val() || null,
            cancion_pre: $('#cancion_pre').val() || null,
            cancion_post: $('#cancion_post').val() || null,
            orador_final: $('#orador_final').val() || null
        };

        // Mostrar indicador de carga
        const button = $(this);
        const originalText = button.html();
        button.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...').prop('disabled', true);

        // Hacer la llamada AJAX para actualizar el programa
        $.ajax({
            url: `/programas/${programaId}`,
            type: 'PUT',
            data: updateData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Actualizar los campos del formulario
                    $('#cancion_en').val(cancionSeleccionada);
                    $('#cancion_en_display').val(textoSeleccionado);

                    // Cerrar modal
                    $('#buscarCancionIntermediaModal').modal('hide');

                    // Mostrar modal de éxito
                    $('#successModal').modal('show');

                    // Ocultar el modal automáticamente después de 2 segundos con fade out
                    setTimeout(function() {
                        $('#successModal').modal('hide');
                    }, 2000);
                } else {
                    alert('Error al guardar la canción intermedia: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error al guardar la canción intermedia';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage += ': ' + errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                }
                alert(errorMessage);
            },
            complete: function() {
                // Restaurar el botón original
                button.html(originalText).prop('disabled', false);
            }
        });
    });

    $('#confirmarCancionFinal').on('click', function() {
        const cancionSeleccionada = $('#select_cancion_final').val();
        const textoSeleccionado = $('#select_cancion_final option:selected').text();

        if (!cancionSeleccionada) {
            alert('Por favor seleccione una canción');
            return;
        }

        // Obtener datos del formulario
        const programaId = $('#programa_id').val();
        const fecha = $('#fecha').val();
        const estado = $('#estado').val();

        // Preparar datos para la actualización
        const updateData = {
            fecha: fecha,
            cancion_post: cancionSeleccionada,
            estado: estado,
            // Incluir otros campos que puedan existir para evitar errores de validación
            orador_inicial: $('#orador_inicial').val() || null,
            presidencia: $('#presidencia').val() || null,
            cancion_pre: $('#cancion_pre').val() || null,
            cancion_en: $('#cancion_en').val() || null,
            orador_final: $('#orador_final').val() || null
        };

        // Mostrar indicador de carga
        const button = $(this);
        const originalText = button.html();
        button.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...').prop('disabled', true);

        // Hacer la llamada AJAX para actualizar el programa
        $.ajax({
            url: `/programas/${programaId}`,
            type: 'PUT',
            data: updateData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Actualizar los campos del formulario
                    $('#cancion_post').val(cancionSeleccionada);
                    $('#cancion_post_display').val(textoSeleccionado);

                    // Cerrar modal
                    $('#buscarCancionFinalModal').modal('hide');

                    // Mostrar modal de éxito
                    $('#successModal').modal('show');

                    // Ocultar el modal automáticamente después de 2 segundos con fade out
                    setTimeout(function() {
                        $('#successModal').modal('hide');
                    }, 2000);
                } else {
                    alert('Error al guardar la canción final: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error al guardar la canción final';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage += ': ' + errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                }
                alert(errorMessage);
            },
            complete: function() {
                // Restaurar el botón original
                button.html(originalText).prop('disabled', false);
            }
        });
    });

    // Limpiar Select2 cuando se cierren los modales de canciones
    $('#buscarCancionInicialModal').on('hidden.bs.modal', function() {
        const select = $('#select_cancion_inicial');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando canciones...</option>');
    });

    $('#buscarCancionIntermediaModal').on('hidden.bs.modal', function() {
        const select = $('#select_cancion_intermedia');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando canciones...</option>');
    });

    $('#buscarCancionFinalModal').on('hidden.bs.modal', function() {
        const select = $('#select_cancion_final');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando canciones...</option>');
    });

    // Funciones para los botones del campo Orador Final (solo para coordinadores)
    function buscarOradorFinal() {
        // Abrir modal y cargar usuarios con asignación de oración
        $('#buscarOradorFinalModal').modal('show');

        // Cargar usuarios con asignación_id=23 y su historial (misma función que orador inicial)
        $.ajax({
            url: '/usuarios-orador-inicial',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_orador_final');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar orador...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarOradorFinalModal')
                        });
                    }

                    select.append('<option value="">Seleccionar orador final...</option>');

                    // Agregar opciones con el formato: fecha - nombre
                    response.data.forEach(function(usuario) {
                        let fechaTexto = 'Primera vez';
                        if (usuario.ultima_fecha) {
                            const fecha = new Date(usuario.ultima_fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            fechaTexto = `${dia}/${mes}/${año}`;
                        }
                        const textoOpcion = `${fechaTexto} - ${usuario.name}`;
                        select.append(`<option value="${usuario.id}">${textoOpcion}</option>`);
                    });

                    // Preseleccionar el orador actual si existe
                    const oradorActual = $('#orador_final').val();
                    if (oradorActual) {
                        select.val(oradorActual).trigger('change');
                    }

                    // Inicializar el select2 del historial también
                    const selectHistorial = $('#select_historial_orador_final');
                    if (!selectHistorial.hasClass('select2-hidden-accessible')) {
                        selectHistorial.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Historial del Encargado...",
                            width: '100%',
                            dropdownParent: $('#buscarOradorFinalModal')
                        });
                    }
                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios para orador final');
                console.error(xhr);
            }
        });
    }

    function verHistorialOradorFinal() {
        const oradorId = $('#orador_final').val();
        if (!oradorId) {
            alert('No hay orador final seleccionado para mostrar historial');
            return;
        }

        // Abrir modal principal y cargar historial del orador
        $('#buscarOradorFinalModal').modal('show');

        // Cargar historial de participaciones del usuario (misma función que orador inicial)
        $.ajax({
            url: `/usuarios/${oradorId}/historial-orador`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_historial_orador_final');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Historial de Encargado...",
                            width: '100%',
                            dropdownParent: $('#buscarOradorFinalModal')
                        });
                    }

                    if (response.data.length > 0) {
                        select.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato: fecha - nombre - tipo
                        response.data.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            const tipoOracion = participacion.tipo === 'inicial' ? 'Orador Inicial' : 'Orador Final';
                            const textoOpcion = `${fechaTexto} - ${participacion.nombre_usuario} - ${tipoOracion}`;

                            select.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        // Habilitar el select
                        select.prop('disabled', false);

                        // Seleccionar automáticamente el primer elemento (índice 1, ya que 0 es "Seleccionar participación...")
                        select.val(response.data[0].programa_id).trigger('change');

                        // Actualizar el título del modal con el nombre del usuario
                        $('#buscarOradorFinalModalLabel').html(`<i class="fas fa-history me-2"></i>Historial de ${response.data[0].nombre_usuario}`);
                    } else {
                        select.append('<option value="">No hay participaciones registradas</option>');
                        select.prop('disabled', true);
                    }
                } else {
                    alert('Error al cargar el historial: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar el historial del orador final');
                console.error(xhr);
            }
        });
    }

    // Funciones para los botones del campo Presidencia (solo para coordinadores)
    function buscarPresidencia() {
        // Abrir modal y cargar usuarios con asignación de presidencia
        $('#buscarPresidenciaModal').modal('show');

        // Cargar usuarios con asignación_id=1 y su historial
        $.ajax({
            url: '/usuarios-presidencia',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_presidencia');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar presidente...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarPresidenciaModal')
                        });
                    }

                    select.append('<option value="">Seleccionar presidente...</option>');

                    // Agregar opciones con el formato: fecha - nombre
                    response.data.forEach(function(usuario) {
                        let fechaTexto = 'Primera vez';
                        if (usuario.ultima_fecha) {
                            const fecha = new Date(usuario.ultima_fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            fechaTexto = `${dia}/${mes}/${año}`;
                        }
                        const textoOpcion = `${fechaTexto} - ${usuario.name}`;
                        select.append(`<option value="${usuario.id}">${textoOpcion}</option>`);
                    });

                    // Preseleccionar el presidente actual si existe
                    const presidenteActual = $('#presidencia').val();
                    if (presidenteActual) {
                        select.val(presidenteActual).trigger('change');
                    }

                    // Inicializar el select2 del historial también
                    const selectHistorial = $('#select_historial_presidencia');
                    if (!selectHistorial.hasClass('select2-hidden-accessible')) {
                        selectHistorial.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Historial del Encargado...",
                            width: '100%',
                            dropdownParent: $('#buscarPresidenciaModal')
                        });
                    }
                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios para presidencia');
                console.error(xhr);
            }
        });
    }

    function verHistorialPresidencia() {
        const presidenteId = $('#presidencia').val();
        if (!presidenteId) {
            alert('No hay presidente seleccionado para mostrar historial');
            return;
        }

        // Abrir modal principal y cargar historial del presidente
        $('#buscarPresidenciaModal').modal('show');

        // Cargar historial de participaciones del usuario
        $.ajax({
            url: `/usuarios/${presidenteId}/historial-presidencia`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_historial_presidencia');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Historial del Encargado...",
                            width: '100%',
                            dropdownParent: $('#buscarPresidenciaModal')
                        });
                    }

                    if (response.data.length > 0) {
                        select.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato: fecha - nombre
                        response.data.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            const textoOpcion = `${fechaTexto} - ${participacion.nombre_usuario}`;

                            select.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        // Habilitar el select
                        select.prop('disabled', false);

                        // Seleccionar automáticamente el primer elemento (índice 1, ya que 0 es "Seleccionar participación...")
                        select.val(response.data[0].programa_id).trigger('change');

                        // Actualizar el título del modal con el nombre del usuario
                        $('#buscarPresidenciaModalLabel').html(`<i class="fas fa-history me-2"></i>Historial de ${response.data[0].nombre_usuario}`);
                    } else {
                        select.append('<option value="">No hay participaciones registradas</option>');
                        select.prop('disabled', true);
                    }
                } else {
                    alert('Error al cargar el historial: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar el historial de presidencia');
                console.error(xhr);
            }
        });
    }

    // Funciones para los botones de canciones (solo para coordinadores)
    function buscarCancionInicial() {
        $('#buscarCancionInicialModal').modal('show');
        cargarCanciones('select_cancion_inicial', '#cancion_pre');
    }

    function buscarCancionIntermedia() {
        $('#buscarCancionIntermediaModal').modal('show');
        cargarCanciones('select_cancion_intermedia', '#cancion_en');
    }

    function buscarCancionFinal() {
        $('#buscarCancionFinalModal').modal('show');
        cargarCanciones('select_cancion_final', '#cancion_post');
    }

    function cargarCanciones(selectId, campoActualId) {
        $.ajax({
            url: '/canciones-disponibles',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#' + selectId);
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        const modalId = selectId.includes('inicial') ? '#buscarCancionInicialModal' :
                                       selectId.includes('intermedia') ? '#buscarCancionIntermediaModal' :
                                       '#buscarCancionFinalModal';

                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar canción...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $(modalId)
                        });
                    }

                    select.append('<option value="">Seleccionar canción...</option>');

                    // Agregar opciones con el formato: número - nombre
                    response.data.forEach(function(cancion) {
                        const textoOpcion = cancion.numero ? `${cancion.numero} - ${cancion.nombre}` : cancion.nombre;
                        select.append(`<option value="${cancion.id}">${textoOpcion}</option>`);
                    });

                    // Preseleccionar la canción actual si existe
                    const cancionActual = $(campoActualId).val();
                    if (cancionActual) {
                        select.val(cancionActual).trigger('change');
                    }
                } else {
                    alert('Error al cargar las canciones: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar las canciones');
                console.error(xhr);
            }
        });
    }
// Funciones para los botones del campo Encargado del datatable 1 (solo para coordinadores)
    function buscarEncargadoParte() {
        const parteId = $('#parte_id').val();
        if (!parteId) {
            alert('Por favor seleccione una parte primero');
            return;
        }

        // Restaurar el título original del modal
        $('#buscarEncargadoParteModalLabel').html('<i class="fas fa-search me-2"></i>Buscar Encargado');

        // Abrir modal y cargar usuarios con participaciones en la parte seleccionada
        $('#buscarEncargadoParteModal').modal('show');
        // Al abrir el modal, deshabilitar el botón Seleccionar
        $('#confirmarEncargadoParte').prop('disabled', true);
        // Cargar usuarios que han participado como encargados en esta parte
        $.ajax({
            url: `/encargados-por-parte-programa/${parteId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_encargado_parte');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar encargado...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarEncargadoParteModal')
                        });
                    }

                    select.append('<option value="">Seleccionar encargado...</option>');

                    // Agregar opciones con el formato: fecha (dd/mm/AAAA) - Nombre del usuario
                    response.data.forEach(function(usuario) {
                        select.append(`<option value="${usuario.id}">${usuario.display_text}</option>`);
                    });

                    // Inicializar el select de historial si no está inicializado
                    const historialSelect = $('#select_historial_encargado_parte');
                    if (!historialSelect.hasClass('select2-hidden-accessible')) {
                        historialSelect.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Historial del Encargado...",
                            width: '100%',
                            dropdownParent: $('#buscarEncargadoParteModal')
                        });
                    }

                    // Preseleccionar el encargado actual si existe
                    const encargadoActual = $('#encargado_id').val();
                    if (encargadoActual) {
                        select.val(encargadoActual);
                        cargarHistorialEncargado(encargadoActual, parteId);
                    }

                    // Agregar event listener para cargar historial cuando se selecciona un encargado
                    select.off('change.historial').on('change.historial', function() {
                        const encargadoSeleccionado = $(this).val();
                        const parteId = $('#parte_id').val();
                        $('#confirmarEncargadoParte').prop('disabled', !encargadoSeleccionado);
                        if (encargadoSeleccionado && parteId) {
                            cargarHistorialEncargado(encargadoSeleccionado, parteId);
                        } else {
                            // Limpiar historial si no hay selección
                            const historialSelect = $('#select_historial_encargado_parte');
                            historialSelect.empty().append('<option value="">Seleccione un encargado primero...</option>');
                            historialSelect.prop('disabled', true);
                        }
                    });

                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios para encargado');
                console.error(xhr);
            }
        });
    }

    // Función para cargar historial de encargado
    function cargarHistorialEncargado(encargadoId, parteId) {
        const historialSelect = $('#select_historial_encargado_parte');
        historialSelect.empty();
        historialSelect.append('<option value="">Cargando historial...</option>');
        historialSelect.prop('disabled', true);

        $.ajax({
            url: `/usuarios/${encargadoId}/historial-participaciones`,
            method: 'GET',
            data: {
                parte_id: parteId,
                tipo: 'encargado'
            },
            success: function(response) {
                if (response.success) {
                    historialSelect.empty();

                    if (response.data.length > 0) {
                        historialSelect.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato: fecha - parte - tipo
                        response.data.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            const textoOpcion = `${fechaTexto}|${participacion.parte_abreviacion}|${participacion.nombre_usuario || 'Usuario'}`;
                            historialSelect.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        // Habilitar el select
                        historialSelect.prop('disabled', false);

                        // Seleccionar automáticamente el primer elemento
                        historialSelect.val(response.data[0].programa_id).trigger('change');
                    } else {
                        historialSelect.append('<option value="">No hay participaciones registradas como encargado</option>');
                        historialSelect.prop('disabled', true);
                    }
                } else {
                    historialSelect.empty();
                    historialSelect.append('<option value="">Error al cargar historial</option>');
                    historialSelect.prop('disabled', true);
                }
            },
            error: function(xhr) {
                historialSelect.empty();
                historialSelect.append('<option value="">Error al cargar historial</option>');
                historialSelect.prop('disabled', true);
                console.error(xhr);
            }
        });
    }

    // Evento para confirmar la selección del encargado NVC
    $('#confirmarEncargadoParteNV').on('click', function() {
        const encargadoSeleccionado = $('#select_encargado_parte_nv').val();
        const textoSeleccionado = $('#select_encargado_parte_nv option:selected').text();

        if (!encargadoSeleccionado) {
            alert('Por favor seleccione un encargado');
            return;
        }

        // Extraer solo el nombre del formato "fecha - nombre"
        let nombreEncargado = textoSeleccionado;
        if (textoSeleccionado.includes('|')) {
            nombreEncargado = textoSeleccionado.split('|')[2];
        }

        // Actualizar los campos
        $('#encargado_id_nv').val(encargadoSeleccionado);
        $('#encargado_display_nv').val(nombreEncargado);

        // Habilitar los botones ahora que hay un encargado seleccionado
        $('#btn-buscar-encargado-nv').prop('disabled', false);

        // Cerrar modal
        $('#buscarEncargadoParteNVModal').modal('hide');
    });

    // Limpiar Select2 cuando se cierre el modal de buscar encargado NVC
    $('#buscarEncargadoParteNVModal').on('hidden.bs.modal', function() {
        const select = $('#select_encargado_parte_nv');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');

        const historialSelect = $('#select_historial_encargado_parte_nv');
        if (historialSelect.hasClass('select2-hidden-accessible')) {
            historialSelect.select2('destroy');
        }
        historialSelect.empty().append('<option value="">Seleccione un encargado primero...</option>');
        historialSelect.prop('disabled', true);

        // Restaurar el título original del modal
        $('#buscarEncargadoParteNVModalLabel').html('<i class="fas fa-search me-2"></i>Buscar Encargado NVC');
    });

    // Hacer la función global para uso en onclick
    window.buscarEncargadoParteNV = buscarEncargadoParteNV;

    function verHistorialEncargadoParte() {
        const encargadoId = $('#encargado_id').val();
        const parteId = $('#parte_id').val();

        if (!parteId) {
            alert('No hay parte seleccionada para mostrar historial');
            return;
        }

        // Cambiar el título del modal para indicar que es para ver historial
        $('#buscarEncargadoParteModalLabel').html('<i class="fas fa-history me-2"></i>Historial de Encargado');

        // Abrir el modal de búsqueda de encargados que ahora incluye el historial
        buscarEncargadoParte();

        // Si hay un encargado seleccionado, cargar su historial
        if (encargadoId) {
            setTimeout(function() {
                cargarHistorialEncargado(encargadoId, parteId);
            }, 500); // Pequeño delay para asegurar que el modal esté abierto
        }
    }

    // Evento para confirmar la selección del encargado
    $('#confirmarEncargadoParte').on('click', function() {
        const encargadoSeleccionado = $('#select_encargado_parte').val();
        const textoSeleccionado = $('#select_encargado_parte option:selected').text();

        if (!encargadoSeleccionado) {
            alert('Por favor seleccione un encargado');
            return;
        }

        // Extraer solo el nombre del formato "fecha - nombre"
        let nombreEncargado = textoSeleccionado;
        if (textoSeleccionado.includes('|')) {
            nombreEncargado = textoSeleccionado.split('|')[2];
        }

        // Actualizar los campos
        $('#encargado_id').val(encargadoSeleccionado);
        $('#encargado_display').val(nombreEncargado);

        // Habilitar los botones ahora que hay un encargado seleccionado
        $('#btn-historial-encargado').prop('disabled', false);
        $('#btn-agregar-reemplazado').prop('disabled', false);

        // Cerrar modal
        $('#buscarEncargadoParteModal').modal('hide');


    });

    // Limpiar Select2 cuando se cierre el modal de buscar encargado
    $('#buscarEncargadoParteModal').on('hidden.bs.modal', function() {
        const select = $('#select_encargado_parte');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');
    });

    // Limpiar Select2 cuando se cierre el modal de búsqueda de encargado
    $('#buscarEncargadoParteModal').on('hidden.bs.modal', function() {
        const select = $('#select_encargado_parte');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');

        const historialSelect = $('#select_historial_encargado_parte');
        if (historialSelect.hasClass('select2-hidden-accessible')) {
            historialSelect.select2('destroy');
        }
        historialSelect.empty().append('<option value="">Seleccione un encargado primero...</option>');
        historialSelect.prop('disabled', true);

        // Restaurar el título original del modal
        $('#buscarEncargadoParteModalLabel').html('<i class="fas fa-search me-2"></i>Buscar Encargado');
    });

    // Hacer las funciones globales para uso en onclick
    window.buscarOradorInicial = buscarOradorInicial;
    window.verHistorialOradorInicial = verHistorialOradorInicial;
    window.buscarOradorFinal = buscarOradorFinal;
    window.verHistorialOradorFinal = verHistorialOradorFinal;
    window.buscarPresidencia = buscarPresidencia;
    window.verHistorialPresidencia = verHistorialPresidencia;
    window.buscarCancionInicial = buscarCancionInicial;
    window.buscarCancionIntermedia = buscarCancionIntermedia;
    // Función para agregar encargado como reemplazado
    function agregarEncargadoReemplazado() {
        const encargadoId = $('#encargado_id').val();
        const encargadoNombre = $('#encargado_display').val();

        if (!encargadoId || !encargadoNombre) {
            alert('No hay encargado seleccionado para agregar como reemplazado');
            return;
        }

        // Agregar directamente sin confirmación
        $('#encargado_reemplazado_id').val(encargadoId);
        $('#encargado_reemplazado_display').val(encargadoNombre);

        // Habilitar el botón de eliminar
        $('#btn-eliminar-reemplazado').prop('disabled', false);
    }

    // Función para eliminar encargado reemplazado
    function eliminarEncargadoReemplazado() {
        const encargadoReemplazadoNombre = $('#encargado_reemplazado_display').val();

        if (!encargadoReemplazadoNombre) {
            alert('No hay estudiante reemplazado para eliminar');
            return;
        }

        // Eliminar directamente sin confirmación
        $('#encargado_reemplazado_id').val('');
        $('#encargado_reemplazado_display').val('');

        // Deshabilitar el botón de eliminar
        $('#btn-eliminar-reemplazado').prop('disabled', true);
    }

    // Event listeners para los modales de confirmación
    $('#confirmarAgregarReemplazado').on('click', function() {
        const encargadoId = $('#encargado_id').val();
        const encargadoNombre = $('#encargado_display').val();

        // Establecer los valores en los campos de reemplazado
        $('#encargado_reemplazado_id').val(encargadoId);
        $('#encargado_reemplazado_display').val(encargadoNombre);

        // Habilitar el botón de eliminar
        $('#btn-eliminar-reemplazado').prop('disabled', false);

        // Cerrar el modal
        $('#confirmarAgregarReemplazadoModal').modal('hide');


    });

    $('#confirmarEliminarReemplazado').on('click', function() {
        // Limpiar los campos
        $('#encargado_reemplazado_id').val('');
        $('#encargado_reemplazado_display').val('');

        // Deshabilitar el botón de eliminar
        $('#btn-eliminar-reemplazado').prop('disabled', true);

        // Cerrar el modal
        $('#confirmarEliminarReemplazadoModal').modal('hide');


    });

    window.buscarCancionFinal = buscarCancionFinal;
    window.buscarEncargadoParte = buscarEncargadoParte;
    window.verHistorialEncargadoParte = verHistorialEncargadoParte;
    window.agregarEncargadoReemplazado = agregarEncargadoReemplazado;
    window.eliminarEncargadoReemplazado = eliminarEncargadoReemplazado;
    window.buscarEncargadoSegundaSeccion = buscarEncargadoSegundaSeccion;
    window.buscarAyudanteSegundaSeccion = buscarAyudanteSegundaSeccion;
    window.verHistorialAyudanteSegundaSeccion = verHistorialAyudanteSegundaSeccion;

    // Funciones para limpiar historiales (segunda sección)
    function clearHistorialEncargado() {
        // Esta función se llama para mantener consistencia en la segunda sección
        // No hay campos específicos de historial que limpiar en esta sección
    }

    function clearHistorialAyudante() {
        // Esta función se llama para mantener consistencia en la segunda sección
        // No hay campos específicos de historial que limpiar en esta sección
    }

    // Función para cargar historial del encargado (funcionalidad específica)
    function loadHistorialEncargado(encargadoId) {
        // Aquí se implementaría la lógica para cargar el historial del encargado
        // Esta función se puede expandir en el futuro si se necesita mostrar historial específico
    }

    // Hacer las funciones globales para uso en otros contextos
    window.clearHistorialEncargado = clearHistorialEncargado;
    window.clearHistorialAyudante = clearHistorialAyudante;
    window.loadHistorialEncargado = loadHistorialEncargado;
    window.loadHistorialEncargadoSegundaSeccion = loadHistorialEncargadoSegundaSeccion;
    window.clearHistorialEncargadoSegundaSeccion = clearHistorialEncargadoSegundaSeccion;

    // Evento para confirmar la selección del encargado de segunda sección
    $('#confirmarEncargadoSegundaSeccion').on('click', function() {
        const encargadoSeleccionado = $('#select_encargado_segunda_seccion').val();
        const textoSeleccionado = $('#select_encargado_segunda_seccion option:selected').text();

        if (!encargadoSeleccionado) {
            alert('Por favor seleccione un encargado');
            return;
        }

        // Extraer solo el nombre limpio del formato "fecha - abreviacion - nombre (tipo)" o "fecha|parte|tipo|nombre"
        let nombreEncargado = textoSeleccionado;
        if (textoSeleccionado.includes(' - ')) {
            const partes = textoSeleccionado.split(' - ');
            nombreEncargado = partes[partes.length - 1].replace(/\s*\([^)]*\)\s*$/, '').trim();
        } else if (textoSeleccionado.includes('|')) {
            const partes = textoSeleccionado.split('|');
            nombreEncargado = partes[partes.length - 1].replace(/\s*\([^)]*\)\s*$/, '').trim();
        } else {
            nombreEncargado = nombreEncargado.replace(/\s*\([^)]*\)\s*$/, '').trim();
        }

        // Actualizar los campos
        $('#encargado_id_segunda_seccion').val(encargadoSeleccionado);
        $('#encargado_display_segunda_seccion').val(nombreEncargado);

        // Verificar si necesitamos resetear el ayudante por diferencia de sexo
        const parteSeleccionada = $('#parte_id_segunda_seccion').val();
        const ayudanteActual = $('#ayudante_id_segunda_seccion').val();

        if (parteSeleccionada && ayudanteActual) {
            const selectedOption = $('#parte_id_segunda_seccion').find('option:selected');
            const tipo = selectedOption.data('tipo');

            // Solo verificar si la parte es de tipo 2
            if (tipo == 2) {
                // Obtener el sexo del encargado y del ayudante
                $.ajax({
                    url: '/verificar-sexos-usuarios',
                    method: 'GET',
                    data: {
                        encargado_id: encargadoSeleccionado,
                        ayudante_id: ayudanteActual
                    },
                    success: function(response) {
                        if (response.success) {
                            const encargadoSexo = response.encargado_sexo;
                            const ayudanteSexo = response.ayudante_sexo;

                            // Si los sexos son diferentes, resetear el campo ayudante
                            if (encargadoSexo !== ayudanteSexo) {
                                $('#ayudante_id_segunda_seccion').val('').trigger('change');
                                $('#ayudante_display_segunda_seccion').val('');
                                $('#btn-ayudante-reemplazado-segunda').prop('disabled', true);
                                clearHistorialAyudanteSegundaSeccion();
                                showAlert('modal-alert-container-segunda-seccion', 'info', 'El ayudante ha sido removido porque tiene un sexo diferente al encargado.');

                                // Limpiar filtros de sexo antes de cerrar
                                limpiarFiltroSexoEncargadoSegunda();

                                // Cerrar modal inmediatamente después de mostrar el mensaje
                                $('#buscarEncargadoSegundaSeccionModal').modal('hide');
                            } else {
                                // Limpiar filtros de sexo antes de cerrar
                                limpiarFiltroSexoEncargadoSegunda();

                                // Cerrar modal inmediatamente si no hay cambios
                                $('#buscarEncargadoSegundaSeccionModal').modal('hide');
                            }
                        } else {
                            // Limpiar filtros de sexo antes de cerrar
                            limpiarFiltroSexoEncargadoSegunda();

                            // Cerrar modal incluso si hay error en la respuesta
                            $('#buscarEncargadoSegundaSeccionModal').modal('hide');
                        }
                    },
                    error: function(xhr) {
                        // Limpiar filtros de sexo antes de cerrar
                        limpiarFiltroSexoEncargadoSegunda();

                        // Cerrar modal incluso si hay error en la petición
                        $('#buscarEncargadoSegundaSeccionModal').modal('hide');
                    }
                });

                // Actualizar campos y botones antes de cerrar el modal
                updateButtonStatesSegundaSeccion();
                return; // Salir para evitar cerrar el modal dos veces
            }
        }

        // Si no hay verificación de sexos, actualizar campos y cerrar modal normalmente
        updateButtonStatesSegundaSeccion();

        // Limpiar filtros de sexo antes de cerrar el modal
        limpiarFiltroSexoEncargadoSegunda();

        $('#buscarEncargadoSegundaSeccionModal').modal('hide');
    });

    // Función para limpiar/resetear el filtro de sexo
    function limpiarFiltroSexoEncargadoSegunda() {
        // Resetear radio buttons al estado por defecto (Mujeres seleccionado)
        $('input[name="filtro_sexo_encargado_segunda"]').prop('checked', false);
        $('#filtro_mujeres_encargado_segunda').prop('checked', true);

        // Habilitar ambos radio buttons
        $('input[name="filtro_sexo_encargado_segunda"]').prop('disabled', false);

        // Mostrar ambos radio buttons
        $('input[name="filtro_sexo_encargado_segunda"][value="1"]').closest('.form-check').show();
        $('input[name="filtro_sexo_encargado_segunda"][value="2"]').closest('.form-check').show();
    }

    // Limpiar Select2 cuando se cierre el modal de buscar encargado segunda sección
    $('#buscarEncargadoSegundaSeccionModal').on('hidden.bs.modal', function() {
        const select = $('#select_encargado_segunda_seccion');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');

        // Limpiar también el historial
        const selectHistorial = $('#select_historial_encargado_segunda_seccion');
        if (selectHistorial.hasClass('select2-hidden-accessible')) {
            selectHistorial.select2('destroy');
        }
        selectHistorial.empty().append('<option value="">Seleccionar un encargado primero...</option>');
        selectHistorial.prop('disabled', true);
    });

    // Función para cargar el historial del encargado en la segunda sección
    function loadHistorialEncargadoSegundaSeccion(encargadoId,parteId,abreviacionParte) {
        const selectHistorial = $('#select_historial_encargado_segunda_seccion');

        // Limpiar el select y mostrar "Cargando..."
        selectHistorial.empty().append('<option value="">Cargando historial...</option>');
        selectHistorial.prop('disabled', false);

        $.ajax({
            url: `/usuarios/${encargadoId}/${parteId}/historial-segunda-seccion`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    selectHistorial.empty();

                    if (response.historial.length > 0) {
                        selectHistorial.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato requerido: fecha | sala | parte | ES | encargado(20chars) | AY | ayudante
                        response.historial.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            // Usar nombres formateados desde el backend con str_pad
                            const encargadoNombre = participacion.encargado_nombre_formateado || '';
                            const ayudanteNombre = participacion.ayudante_nombre_formateado || '';

                            // Formato: fecha|sala|parte|ES|encargado|AY|ayudante (sin espacios alrededor de |)
                            const textoOpcion = `${fechaTexto}|${participacion.sala_abreviacion}|${participacion.parte_abreviacion}|ES|${encargadoNombre}|AY|${ayudanteNombre}`;

                            selectHistorial.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        selectHistorial.prop('disabled', false);
                        //Filtrar por ES por defecto
                        //selectHistorial.select2('open');
                        //$('.select2-search__field').val(`|${abreviacionParte}|`).trigger('input');

                        // Seleccionar automáticamente el primer elemento (índice 1, ya que 0 es "Seleccionar participación...")
                        if (response.historial.length > 0) {
                            selectHistorial.val(response.historial[0].programa_id).trigger('change');
                        }
                    } else {
                        selectHistorial.append('<option value="">No hay participaciones registradas</option>');
                        selectHistorial.prop('disabled', true);
                    }
                } else {
                    selectHistorial.empty().append('<option value="">Error al cargar historial</option>');
                    selectHistorial.prop('disabled', true);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar historial:', xhr.responseText);
                selectHistorial.empty().append('<option value="">Error al cargar historial</option>');
                selectHistorial.prop('disabled', true);
            }
        });
    }

    // Función para limpiar el historial del encargado en la segunda sección
    function clearHistorialEncargadoSegundaSeccion() {
        const selectHistorial = $('#select_historial_encargado_segunda_seccion');
        selectHistorial.empty().append('<option value="">Seleccionar un encargado primero...</option>');
        selectHistorial.prop('disabled', true);
    }

    // Función para cargar el historial del ayudante en la segunda sección
    function loadHistorialAyudanteSegundaSeccion(ayudanteId,parteId) {
        const selectHistorial = $('#select_historial_ayudante_segunda_seccion');

        // Limpiar el select y mostrar "Cargando..."
        selectHistorial.empty().append('<option value="">Cargando historial...</option>');
        selectHistorial.prop('disabled', false);

        $.ajax({
            url: `/usuarios/${ayudanteId}/${parteId}/historial-segunda-seccion`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    selectHistorial.empty();

                    if (response.historial.length > 0) {
                        selectHistorial.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato requerido: fecha | sala | parte | ES | encargado(25chars) | AY | ayudante
                        response.historial.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            // Usar nombres formateados desde el backend con str_pad
                            const encargadoNombre = participacion.encargado_nombre_formateado || '';
                            const ayudanteNombre = participacion.ayudante_nombre_formateado || '';

                            // Formato: fecha|sala|parte|ES|encargado|AY|ayudante (sin espacios alrededor de |)
                            const textoOpcion = `${fechaTexto}|${participacion.sala_abreviacion}|${participacion.parte_abreviacion}|ES|${encargadoNombre}|AY|${ayudanteNombre}`;

                            selectHistorial.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        selectHistorial.prop('disabled', false);

                        // Seleccionar automáticamente el primer elemento (índice 1, ya que 0 es "Seleccionar participación...")
                        if (response.historial.length > 0) {
                            selectHistorial.val(response.historial[0].programa_id).trigger('change');
                        }
                    } else {
                        selectHistorial.append('<option value="">No hay participaciones registradas</option>');
                        selectHistorial.prop('disabled', true);
                    }
                } else {
                    selectHistorial.empty().append('<option value="">Error al cargar historial</option>');
                    selectHistorial.prop('disabled', true);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar historial:', xhr.responseText);
                selectHistorial.empty().append('<option value="">Error al cargar historial</option>');
                selectHistorial.prop('disabled', true);
            }
        });
    }

    // Función para limpiar el historial del ayudante en la segunda sección
    function clearHistorialAyudanteSegundaSeccion() {
        const selectHistorial = $('#select_historial_ayudante_segunda_seccion');
        selectHistorial.empty().append('<option value="">Seleccionar un ayudante primero...</option>');
        selectHistorial.prop('disabled', true);
    }

    // Evento para confirmar la selección del ayudante de segunda sección
    // Función para limpiar/resetear el filtro de sexo de ayudante
    function limpiarFiltroSexoAyudanteSegunda() {
        // Resetear radio buttons
        $('input[name="filtro_sexo_ayudante_segunda"]').prop('checked', false);

        // Habilitar ambos radio buttons
        $('input[name="filtro_sexo_ayudante_segunda"]').prop('disabled', false);

        // Mostrar ambos radio buttons
        $('input[name="filtro_sexo_ayudante_segunda"][value="1"]').closest('.form-check').show();
        $('input[name="filtro_sexo_ayudante_segunda"][value="2"]').closest('.form-check').show();

        // Ocultar el contenedor de filtros
        $('#filtro_sexo_ayudante_segunda_container').hide();
    }

    $('#confirmarAyudanteSegundaSeccion').on('click', function() {
        const ayudanteSeleccionado = $('#select_ayudante_segunda_seccion').val();
        const textoSeleccionado = $('#select_ayudante_segunda_seccion option:selected').text();

        if (!ayudanteSeleccionado) {
            alert('Por favor seleccione un ayudante');
            return;
        }

        // Extraer solo el nombre del formato "fecha|sala_abrev|parte_abrev|nombre"
        let nombreAyudante = textoSeleccionado;
        if (textoSeleccionado.includes('|')) {
            const partes = textoSeleccionado.split('|');
            if (partes.length >= 4) {
                // El nombre está en la cuarta parte (índice 3)
                nombreAyudante = partes[4].trim();
            }
        }

        // Actualizar los campos
        $('#ayudante_id_segunda_seccion').val(ayudanteSeleccionado);
        $('#ayudante_display_segunda_seccion').val(nombreAyudante);

        // Actualizar el estado de los botones
        updateButtonStatesSegundaSeccion();

        // Limpiar filtros de sexo antes de cerrar
        limpiarFiltroSexoAyudanteSegunda();

        // Cerrar modal
        $('#buscarAyudanteSegundaSeccionModal').modal('hide');
    });

    // Limpiar Select2 cuando se cierre el modal de buscar ayudante segunda sección
    $('#buscarAyudanteSegundaSeccionModal').on('hidden.bs.modal', function() {
        const select = $('#select_ayudante_segunda_seccion');
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }
        select.empty().append('<option value="">Cargando usuarios...</option>');

        // Limpiar también el historial
        const selectHistorial = $('#select_historial_ayudante_segunda_seccion');
        if (selectHistorial.hasClass('select2-hidden-accessible')) {
            selectHistorial.select2('destroy');
        }
        selectHistorial.empty().append('<option value="">Seleccionar un ayudante primero...</option>');
        selectHistorial.prop('disabled', true);
    });

    // Funciones para Nuestra Vida Cristiana (Sección NVC)
    function initPartesNVDataTable() {
        partesNVTable = $('#partesNVTable').DataTable({
            language: {
                emptyTable: "No hay partes asignadas en Nuestra Vida Cristiana",
                zeroRecords: "No se encontraron partes que coincidan con la búsqueda"
            },
            responsive: true,
            ordering: false,
            paging: false,
            info: false,
            searching: false,
            columnDefs: [
                { orderable: false, targets: '_all' } // Ninguna columna ordenable
            ]
        });
    }

    function loadPartesNV() {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes-nvc`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Limpiar la tabla
                    partesNVTable.clear();

                    response.data.forEach(function(parte, index) {
                        const numero = parte.numero; // Número incremental empezando desde 1
                        const upDisabled = parte.es_primero ? 'disabled' : '';
                        const downDisabled = parte.es_ultimo ? 'disabled' : '';

                        let acciones = `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteNVUp(${parte.id})" title="Subir" ${upDisabled}>
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteNVDown(${parte.id})" title="Bajar" ${downDisabled}>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editParteNV(${parte.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteParteNV(${parte.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;

                        let rowData = [
                            numero, // Columna Número
                            parte.parte_abreviacion || '-',
                            parte.encargado_nombre || '-',
                            parte.tema || '-',
                            parte.tiempo || '-',
                            acciones
                        ];

                        partesNVTable.row.add(rowData);
                    });

                    // Dibujar la tabla
                    partesNVTable.draw();
                } else {
                    console.error('Error en la respuesta:', response);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar las partes de Nuestra Vida Cristiana:', xhr.responseText);
                showAlert('alert-container', 'danger', 'Error al cargar las partes de Nuestra Vida Cristiana');
            }
        });
    }

    // Funciones para mover partes de NVC
    function moveParteNVUp(id) {
        moveParte(id, 'up', loadPartesNV);
    }

    function moveParteNVDown(id) {
        moveParte(id, 'down', loadPartesNV);
    }

    // Función para editar parte de NVC
    function editParteNV(id) {
        openEditParteNVModal(id);
    }

    // Función para eliminar parte de NVC
    function deleteParteNV(id) {
        deleteParte(id, loadPartesNV);
    }

    // Función para abrir modal de crear parte NVC
    function openCreateParteNVModal() {
        isEditMode = false;
        $('#parteProgramaNVModalLabel').text('Nueva Asignación de Nuestra Vida Cristiana');
        $('#saveParteNVBtn').text('Guardar Asignación');
        $('#parteProgramaNVForm')[0].reset();
        $('#programa_id_parte_nv').val($('#programa_id').val());

        // Limpiar alertas del modal
        $('#modal-alert-container-nv').empty();

        // Mostrar select y ocultar input de texto para "Asignación" en modo "nuevo"
        $('#parte_id_nv').show();
        $('#parte_display_nv').hide();

        // Limpiar campos de reemplazados

        $('#encargado_reemplazado_display_nv').closest('.col-md-6').hide();
        $('#btn-agregar-reemplazado-nv').hide();
        $('#parte_programa_nv_id').val('');
        $('#btn-buscar-encargado-nv').prop('disabled', true);

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Restablecer campos de encargado según el perfil del usuario
        $('#encargado_id_nv').val('');
        $('#encargado_display_nv').val('');

        $('#btn-agregar-reemplazado').prop('disabled', true);
        // Limpiar campos de encargado reemplazado
        $('#encargado_reemplazado_id_nv').val('');
        $('#encargado_reemplazado_display_nv').val('');
        $('#btn-eliminar-reemplazado-nv').prop('disabled', true);


        // Cargar partes de la sección NVC
        loadPartesSeccionNV();

        $('#parteProgramaNVModal').modal('show');
    }

    // Función para abrir modal de editar parte NVC
    function openEditParteNVModal(id) {
        isEditMode = true;
        $('#parteProgramaNVModalLabel').text('Editar Asignación de Nuestra Vida Cristiana');
        $('#saveParteNVBtn').text('Actualizar Asignación');

        // Limpiar alertas del modal
        $('#modal-alert-container-nv').empty();

        // Ocultar select y mostrar input de texto para "Asignación" en modo "editar"
        $('#parte_id_nv').hide();
        $('#parte_display_nv').show();

        $('#encargado_reemplazado_display_nv').val('');
        $('#encargado_reemplazado_id_nv').val('');

        // Mostrar el campo Encargado Reemplazado y el botón Agregar Reemplazado cuando se edita
        $('#encargado_reemplazado_display_nv').closest('.col-md-6').show();
        $('#btn-agregar-reemplazado-nv').show();

        $.ajax({
            url: `/partes-programa/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const parte = response.data;

                    // Llenar el formulario con los datos
                    $('#parte_programa_nv_id').val(parte.id);
                    $('#programa_id_parte_nv').val(parte.programa_id);
                    $('#tiempo_parte_nv').val(parte.tiempo);
                    $('#tema_parte_nv').val(parte.tema);

                    // Seleccionar la parte
                    $('#parte_id_nv').val(parte.parte_id).trigger('change');

                    $('#encargado_display_nv').val(parte.encargado.name);
                    $('#encargado_id_nv').val(parte.encargado.id);
                    $('#btn-buscar-encargado-nv').prop('disabled', false);
                    $('#btn-agregar-reemplazado-nv').prop('disabled', false);
                    // Manejar encargado reemplazado
                    if (parte.encargado_reemplazado) {
                        $('#encargado_reemplazado_display_nv').val(parte.encargado_reemplazado.name);
                        $('#encargado_reemplazado_id_nv').val(parte.encargado_reemplazado.id);
                        $('#btn-eliminar-reemplazado-nv').prop('disabled', false);
                    }

                    // Cargar partes de la sección NVC
                    loadPartesSeccionesNVForEdit(parte.parte_id);

                    $('#parteProgramaNVModal').modal('show');
                } else {
                    showAlert('alert-container', 'danger', response.message || 'Error al cargar los datos de la parte');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar la parte:', xhr.responseText);
                showAlert('alert-container', 'danger', 'Error al cargar los datos de la parte');
            }
        });
    }

    // Función para cargar partes de la sección NVC
    function loadPartesSeccionNV() {
        const programaId = $('#programa_id').val();

        $('#parte_id').empty().append('<option value="">Cargando...</option>');
        $.ajax({
            url: '/partes-secciones',
            method: 'GET',
            data: {
                    programa_id: programaId,
                    parte_id: 3
                },
                success: function(response) {
                if (response.success) {
                    const select = $('#parte_id_nv');
                    select.empty();
                    select.append('<option value="">Seleccionar...</option>');

                    response.data.forEach(function(parte) {
                            $('#parte_id_nv').append(
                                `<option value="${parte.id}" data-tiempo="${parte.tiempo || ''}">${parte.nombre} (${parte.abreviacion})</option>`
                            );
                        });
                } else {
                    console.error('Error al cargar las partes de sección:', response.message);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar las partes de sección:', xhr.responseText);
            }
        });
    }

    function loadPartesSeccionesNVForEdit(parteId) {
            $('#parte_id').empty().append('<option value="">Cargando...</option>');

            $.ajax({
                url: `/partes-seccion/${parteId}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const parte = response.data;
                        $('#parte_id_nv').empty();

                        // Solo agregar la parte que corresponde al registro que se está editando
                        $('#parte_id_nv').append(
                            `<option value="${parte.id}" data-tiempo="${parte.tiempo || ''}" selected>${parte.nombre} (${parte.abreviacion})</option>`
                        );

                        // Llenar el campo de texto deshabilitado para el modo editar
                        $('#parte_display_nv').val(parte.nombre);
                        $('#parte_id_hidden_nv').val(parte.id);

                        // Autocompletar el tiempo si está disponible
                        if (parte.tiempo) {
                            $('#tiempo_parte_nv').val(parte.tiempo);
                        }

                    } else {
                        $('#parte_id_nv').empty().append('<option value="">Error al cargar la parte</option>');
                    }
                },
                error: function(xhr) {
                    console.error('Error al cargar parte de sección:', xhr);
                    $('#parte_id_nv').empty().append('<option value="">Error al cargar</option>');
                }
            });
        }
    // Función para buscar encargado en NVC
    function buscarEncargadoParteNV() {
        const parteId = $('#parte_id_nv').val();
        if (!parteId) {
            alert('Por favor seleccione una parte primero');
            return;
        }

        // Restaurar el título original del modal
        $('#buscarEncargadoParteNVModalLabel').html('<i class="fas fa-search me-2"></i>Buscar Encargado NVC');

        // Abrir modal y cargar usuarios con participaciones en la parte seleccionada
        $('#buscarEncargadoParteNVModal').modal('show');

        // Al abrir el modal, deshabilitar el botón Seleccionar
        $('#confirmarEncargadoParteNV').prop('disabled', true);
        // Cargar usuarios que han participado como encargados en esta parte
        $.ajax({
            url: `/encargados-por-parte-programa/${parteId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#select_encargado_parte_nv');
                    select.empty();

                    // Inicializar Select2 si no está ya inicializado
                    if (!select.hasClass('select2-hidden-accessible')) {
                        select.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Buscar y seleccionar encargado...",
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#buscarEncargadoParteNVModal')
                        });
                    }

                    select.append('<option value="">Seleccionar encargado...</option>');

                    // Agregar opciones con el formato: fecha (dd/mm/AAAA) - Nombre del usuario
                    response.data.forEach(function(usuario) {
                        select.append(`<option value="${usuario.id}">${usuario.display_text}</option>`);
                    });

                    // Inicializar el select de historial si no está inicializado
                    const historialSelect = $('#select_historial_encargado_parte_nv');
                    if (!historialSelect.hasClass('select2-hidden-accessible')) {
                        historialSelect.select2({
                            theme: 'bootstrap-5',
                            placeholder: "Historial del Encargado...",
                            width: '100%',
                            dropdownParent: $('#buscarEncargadoParteNVModal')
                        });
                    }

                    // Preseleccionar el encargado actual si existe
                    const encargadoActual = $('#encargado_id_nv').val();
                    if (encargadoActual) {
                        select.val(encargadoActual);
                        cargarHistorialEncargadoNV(encargadoActual, parteId);
                    }

                    // Agregar event listener para cargar historial cuando se selecciona un encargado
                    select.off('change.historial_nv').on('change.historial_nv', function() {
                        const encargadoSeleccionado = $(this).val();
                        const parteId = $('#parte_id_nv').val();
                        $('#confirmarEncargadoParteNV').prop('disabled', !encargadoSeleccionado);
                        if (encargadoSeleccionado && parteId) {
                            cargarHistorialEncargadoNV(encargadoSeleccionado, parteId);
                        } else {
                            // Limpiar historial si no hay selección
                            const historialSelect = $('#select_historial_encargado_parte_nv');
                            historialSelect.empty().append('<option value="">Seleccione un encargado primero...</option>');
                            historialSelect.prop('disabled', true);
                        }
                    });

                } else {
                    alert('Error al cargar los usuarios: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al cargar los usuarios para encargado NVC');
                console.error(xhr);
            }
        });
    }

    // Event handler para restaurar la visibilidad del campo Encargado Reemplazado y el botón cuando se cierra el modal
    $('#parteProgramaNVModal').on('hidden.bs.modal', function() {
        // Mostrar el campo Encargado Reemplazado y el botón Agregar Reemplazado por defecto cuando se cierra el modal
        $('#encargado_reemplazado_display_nv').closest('.col-md-6').show();
        $('#btn-agregar-reemplazado-nv').show();
    });

    // Función para cargar historial de encargado en NVC
    function cargarHistorialEncargadoNV(encargadoId, parteId) {
        const historialSelect = $('#select_historial_encargado_parte_nv');
        historialSelect.empty();
        historialSelect.append('<option value="">Cargando historial...</option>');
        historialSelect.prop('disabled', true);

        $.ajax({
            url: `/usuarios/${encargadoId}/historial-participaciones`,
            method: 'GET',
            data: {
                parte_id: parteId,
                tipo: 'encargado'
            },
            success: function(response) {
                if (response.success) {
                    historialSelect.empty();

                    if (response.data.length > 0) {
                        historialSelect.append('<option value="">Seleccionar participación...</option>');

                        // Agregar opciones con el formato: fecha - parte - tipo
                        response.data.forEach(function(participacion) {
                            const fecha = new Date(participacion.fecha);
                            const dia = String(fecha.getDate()).padStart(2, '0');
                            const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                            const año = fecha.getFullYear();
                            const fechaTexto = `${dia}/${mes}/${año}`;

                            const textoOpcion = `${fechaTexto}|${participacion.parte_abreviacion}|${participacion.nombre_usuario || 'Usuario'}`;
                            historialSelect.append(`<option value="${participacion.programa_id}">${textoOpcion}</option>`);
                        });

                        // Habilitar el select
                        historialSelect.prop('disabled', false);

                        // Seleccionar automáticamente el primer elemento
                        historialSelect.val(response.data[0].programa_id).trigger('change');
                    } else {
                        historialSelect.append('<option value="">No hay participaciones registradas como encargado</option>');
                        historialSelect.prop('disabled', true);
                    }
                } else {
                    historialSelect.empty();
                    historialSelect.append('<option value="">Error al cargar historial</option>');
                    historialSelect.prop('disabled', true);
                }
            },
            error: function(xhr) {
                historialSelect.empty();
                historialSelect.append('<option value="">Error al cargar historial</option>');
                historialSelect.prop('disabled', true);
                console.error(xhr);
            }
        });
    }

    // Función para agregar encargado reemplazado en NVC
    function agregarEncargadoReemplazadoNV() {
        const encargadoId = $('#encargado_id_nv').val();
        const encargadoNombre = $('#encargado_display_nv').val();

        if (encargadoId && encargadoNombre) {
            // Agregar el nombre al campo visible
            $('#encargado_reemplazado_display_nv').val(encargadoNombre);

            // Agregar el ID al campo oculto para ser guardado en la BD
            $('#encargado_reemplazado_id_nv').val(encargadoId);

            // Habilitar el botón de eliminar reemplazado
            $('#btn-eliminar-reemplazado-nv').prop('disabled', false);
        } else {
            alert('Por favor seleccione un encargado primero');
        }
    }

    // Función para eliminar encargado reemplazado en NVC
    function eliminarEncargadoReemplazadoNV() {
        const encargadoReemplazadoNombre = $('#encargado_reemplazado_display_nv').val();

        if (!encargadoReemplazadoNombre) {
            alert('No hay estudiante reemplazado para eliminar');
            return;
        }

        // Eliminar directamente sin confirmación
        $('#encargado_reemplazado_id_nv').val('');
        $('#encargado_reemplazado_display_nv').val('Sin estudiante reemplazado...');

        // Deshabilitar el botón de eliminar
        $('#btn-eliminar-reemplazado-nv').prop('disabled', true);
    }

    // Exponer funciones globalmente para que puedan ser llamadas desde onclick en HTML
    window.moveParteSegundaSeccionUp = moveParteSegundaSeccionUp;
    window.moveParteSegundaSeccionDown = moveParteSegundaSeccionDown;
    window.editParteSegundaSeccion = editParteSegundaSeccion;
    window.deleteParteSegundaSeccion = deleteParteSegundaSeccion;
    window.moveParteUp = moveParteUp;
    window.moveParteDown = moveParteDown;
    window.editParte = editParte;
    window.deleteParte = deleteParte;
    window.moveParteNVUp = moveParteNVUp;
    window.moveParteNVDown = moveParteNVDown;
    window.editParteNV = editParteNV;
    window.deleteParteNV = deleteParteNV;
});

/**
 * Función para ver asignación desde la tabla (usando el partes_programa.id)
 */
function verAsignacionDesdeTabla(parteProgramaId) {
    if (!parteProgramaId) {
        showAlert('alert-container', 'warning', 'No se pudo obtener el ID de la asignación.');
        return;
    }
    
    // Mostrar el modal
    $('#verAsignacionModal').modal('show');
    
    // Cargar la asignación por parte_programa_id
    cargarAsignacionPorId(parteProgramaId);
}

/**
 * Función para cargar una asignación específica por partes_programa.id
 */
function cargarAsignacionPorId(parteProgramaId) {
    const container = $('#asignacion-card-container');
    
    // Mostrar spinner de carga
    container.html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `);
    
    // Realizar petición AJAX
    $.ajax({
        url: `/programas/asignacion-por-id/${parteProgramaId}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                mostrarAsignacion(response.data);
            } else {
                showModalAlert('ver-asignacion-alert-container', 'danger', response.message || 'Error al cargar la asignación.');
                container.html(`
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-circle me-2"></i>Error al cargar la asignación
                    </div>
                `);
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Error al cargar la asignación.';
            showModalAlert('ver-asignacion-alert-container', 'danger', message);
            container.html(`
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>Error al cargar la asignación
                </div>
            `);
        }
    });
}

/**
 * Función para mostrar una asignación individual
 */
function mostrarAsignacion(asignacion) {
    const container = $('#asignacion-card-container');
    
    if (!asignacion) {
        container.html(`
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>No se encontró la asignación.
            </div>
        `);
        return;
    }

    const fechaFormateada = asignacion.fecha_formateada;
    
    // Obtener el nombre del encargado (estudiante)
    const nombreUsuario = asignacion.nombre_encargado || '';
    
    // Determinar el nombre del ayudante si existe
    let nombreAyudante = asignacion.nombre_ayudante || '';
    
    // Determinar en qué sala se presenta (basado en sala_id)
    const salaId = parseInt(asignacion.sala_id) || 1;
    const salaPrincipal = salaId === 1;
    const salaAuxiliar1 = salaId === 2;
    const salaAuxiliar2 = salaId === 3;
    
    // Generar HTML de la tarjeta de asignación
    let html = `
        <div class="asignacion-card" id="asignacion-para-imprimir">
            <h4>ASIGNACIÓN PARA LA REUNIÓN<br>VIDA Y MINISTERIO CRISTIANOS</h4>
            
            <div class="info-row">
                <span class="info-label">Nombre:</span>
                <span class="info-value">${nombreUsuario}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Ayudante:</span>
                <span class="info-value">${nombreAyudante || ''}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span class="info-value">${fechaFormateada}</span>
            </div>
            
            <div class="info-row">
                <span class="info-intervencion">Intervención núm.:</span>
                <span class="info-value">${asignacion.numero_intervencion}</span>
            </div>
            
            <div class="sala-section">
                <div style="font-weight: bold; margin-bottom: 10px;">Se presentará en:</div>
                <div class="checkbox-row">
                    <span class="checkbox ${salaPrincipal ? 'checked' : ''}"></span>
                    <span>Sala principal</span>
                </div>
                <div class="checkbox-row">
                    <span class="checkbox ${salaAuxiliar1 ? 'checked' : ''}"></span>
                    <span>Sala auxiliar 1</span>
                </div>
                <div class="checkbox-row">
                    <span class="checkbox ${salaAuxiliar2 ? 'checked' : ''}"></span>
                    <span>Sala auxiliar 2</span>
                </div>
            </div>
            
            <div class="nota-section">
                <div class="nota-title">Nota al estudiante:</div>
                <div class="nota-text">
                    En la <i>Guía de actividades</i> encontrará la información que necesita para su intervención. 
                    Repase también las indicaciones que se describen en las Instrucciones para la reunión 
                    Vida y Ministerio Cristianos (S-38).
                </div>
                <div class="nota-text mt-2">
                    S-38-S 11/23
                </div>
            </div>
        </div>
    `;
    
    container.html(html);
}

/**
 * Función auxiliar para mostrar alertas en el modal
 */
function showModalAlert(containerId, type, message) {
    const alertContainer = $('#' + containerId);
    const alert = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    alertContainer.html(alert);
    
    // Auto-cerrar después de 5 segundos para alertas de éxito
    if (type === 'success') {
        setTimeout(() => {
            alertContainer.find('.alert').alert('close');
        }, 5000);
    }
}

// Exponer funciones globalmente para que puedan ser llamadas desde onclick en HTML
window.verAsignacionDesdeTabla = verAsignacionDesdeTabla;
