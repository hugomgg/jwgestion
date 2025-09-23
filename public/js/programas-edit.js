// Variables globales que serán definidas desde el Blade template
// let partesTable; - Declarado en Blade template
// let partesSegundaSeccionTable; - Declarado en Blade template
// let partesTerceraSeccionTable; - Declarado en Blade template
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
    initPartesTerceraSeccionDataTable();
    initPartesNVDataTable();

    // Cargar partes de la segunda sección
    loadPartesSegundaSeccion();
    // Cargar partes de la tercera sección
    loadPartesTerceraSeccion();
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
                    console.log('Fecha guardada exitosamente');
                    // Opcional: mostrar un mensaje de éxito sutil
                    // alert('Fecha guardada exitosamente');
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
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            responsive: true,
            paging: false,
            ordering: false,
            info: false,
            searching: false
        });
    }

    function initPartesSegundaSeccionDataTable() {
        partesSegundaSeccionTable = $('#partesSegundaSeccionTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            responsive: true,
            paging: false,
            ordering: false,
            info: false,
            searching: false
        });
    }

    function initPartesTerceraSeccionDataTable() {
        partesTerceraSeccionTable = $('#partesTerceraSeccionTable').DataTable({
            language: {
                emptyTable: "No hay partes asignadas en esta sección",
                zeroRecords: "No se encontraron partes que coincidan con la búsqueda"
            },
            responsive: true,
            ordering: false,
            paging: false,
            info: false,
            searching: false
        });
    }

    function loadPartesSegundaSeccion() {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes-segunda-seccion`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    partesSegundaSeccionTable.clear();

                    response.data.forEach(function(parte) {
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
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editParteSegundaSeccion(${parte.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteParteSegundaSeccion(${parte.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;

                        let rowData = [
                            parte.tiempo || '-',
                            parte.parte_abreviacion || '-',
                            parte.encargado_nombre || '-',
                            parte.ayudante_nombre || '-',
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

    function loadPartesTerceraSeccion() {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes-tercera-seccion`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Limpiar la tabla
                    partesTerceraSeccionTable.clear();

                    response.data.forEach(function(parte) {
                        const upDisabled = parte.es_primero ? 'disabled' : '';
                        const downDisabled = parte.es_ultimo ? 'disabled' : '';

                        let acciones = `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteTerceraSeccionUp(${parte.id})" title="Subir" ${upDisabled}>
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveParteTerceraSeccionDown(${parte.id})" title="Bajar" ${downDisabled}>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editParteSegundaSeccion(${parte.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteParteTerceraSeccion(${parte.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;

                        let rowData = [
                            parte.tiempo || '-',
                            parte.parte_abreviacion || '-',
                            parte.encargado_nombre || '-',
                            parte.ayudante_nombre || '-',
                            parte.leccion || '-',
                            acciones
                        ];

                        partesTerceraSeccionTable.row.add(rowData);
                    });

                    // Dibujar la tabla
                    partesTerceraSeccionTable.draw();
                } else {
                    console.error('Error en la respuesta:', response);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar las partes de la tercera sección:', xhr.responseText);
                showAlert('alert-container', 'danger', 'Error al cargar las partes de la tercera sección');
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

                    response.data.forEach(function(parte) {
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
                            parte.tiempo || '-',
                            parte.parte_abreviacion || '-',
                            parte.encargado_nombre || '-',
                            parte.tema || '-'
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
        const seccionNombre = 'Primera Sección'; // Valor por defecto, será sobrescrito desde Blade
        $('#parteProgramaModalLabel').text('Nueva Asignación de ' + seccionNombre);
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
        $('#parteProgramaModalLabel').text('Editar Asignación del Programa');
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
        } else if ($('#parteProgramaTerceraSeccionModal').hasClass('show')) {
            // Tercera sección (coordinadores)
            leccionFieldId = 'leccion_tercera_seccion';
            leccionValue = $('#leccion_tercera_seccion').val();
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
                    showAlert('alert-container', 'success', response.message);
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
                    showAlert('alert-container', 'success', response.message);
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

    function deleteParte(id) {
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
                        loadPartesPrograma();
                        showAlert('alert-container', 'success', response.message);
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

    // Inicializar Select2 para el modal de tercera sección
    $('#encargado_id_tercera_seccion').select2({
        placeholder: 'Seleccionar...',
        allowClear: true,
        dropdownParent: $('#parteProgramaTerceraSeccionModal'),
        theme: 'bootstrap-5',
        width: '100%',
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            }
        }
    });

    $('#ayudante_id_tercera_seccion').select2({
        placeholder: 'Seleccionar...',
        allowClear: true,
        dropdownParent: $('#parteProgramaTerceraSeccionModal'),
        theme: 'bootstrap-5',
        width: '100%',
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            }
        }
    });

    // Manejar envío del formulario de segunda sección
    $('#parteProgramaSegundaSeccionForm').submit(function(e) {
        e.preventDefault();
        submitParteSegundaSeccion();
    });

    // Manejar envío del formulario de tercera sección
    $('#parteProgramaTerceraSeccionForm').submit(function(e) {
        e.preventDefault();
        submitParteTerceraSeccion();
    });

    // Habilitar los botones Buscar Encargado y Buscar Ayudante al seleccionar una parte en la segunda sección
    $(document).on('change', '#parte_id_segunda_seccion', function() {
        const parteId = $(this).val();
        const btnBuscarEncargado = $('#btn-buscar-encargado-segunda');
        const btnBuscarAyudante = $('#btn-buscar-ayudante-segunda');
        // Obtener el tiempo de la opción seleccionada (data-tiempo)
        const selectedOption = $(this).find('option:selected');
        const tiempo = selectedOption.data('tiempo');
        const tipo = selectedOption.data('tipo');

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
            btnBuscarEncargado.prop('disabled', false).attr('title', 'Buscar Encargado');
            // El botón "Buscar Ayudante" se mantiene deshabilitado hasta que se seleccione un encargado
            btnBuscarAyudante.prop('disabled', true).attr('title', 'Seleccionar un encargado primero');

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

    // Manejar cambio en el select de parte_id para tercera sección
    $(document).on('change', '#parte_id_tercera_seccion', function() {
        const selectedOption = $(this).find('option:selected');
        const tiempo = selectedOption.data('tiempo');
        const parteId = $(this).val();
        const encargadoSeleccionado = $('#encargado_id_tercera_seccion').val();

        // Autocompletar tiempo
        if (tiempo) {
            $('#tiempo_tercera_seccion').val(tiempo);
        } else {
            $('#tiempo_tercera_seccion').val('');
        }

        // Filtrar usuarios del campo encargado basado en la parte seleccionada
        if (parteId) {
            loadEncargadosByParteTerceraSeccion(parteId);

            // Si ya hay un encargado seleccionado, actualizar ayudantes con la nueva lógica
            if (encargadoSeleccionado) {
                loadAyudantesByEncargadoAndParteTercera(encargadoSeleccionado, parteId);
            } else {
                // Si no hay encargado seleccionado, cargar ayudantes por parte
                const ayudanteActual = $('#ayudante_id_tercera_seccion').val();
                loadAyudantesByParteTerceraSeccion(ayudanteActual);
            }
        } else {
            loadUsuariosDisponiblesTerceraSeccion();
            loadAyudantesByParteTerceraSeccion();
        }
    });

    // Manejar cambio en el select de encargado para cargar ayudantes (tercera sección)
    $(document).on('change', '#encargado_id_tercera_seccion', function() {
        // No procesar eventos durante la carga en modo edición
        if (window.editingParteTerceraData) {
            return;
        }

        const encargadoSeleccionado = $(this).val();
        const parteSeleccionada = $('#parte_id_tercera_seccion').val();

        // Limpiar historial anterior
        clearHistorialEncargadoTercera();

        if (encargadoSeleccionado) {
            // Cargar historial del encargado seleccionado siempre
            if (parteSeleccionada) {
                // Cargar ayudantes usando la nueva lógica
                loadAyudantesByEncargadoAndParteTercera(encargadoSeleccionado, parteSeleccionada);
            } else {
                // Si no hay parte seleccionada, cargar ayudantes generales
                loadAyudantesByParteTerceraSeccion();
            }
        } else {
            // Si no hay encargado seleccionado
            if (parteSeleccionada) {
                // Si hay parte seleccionada pero no encargado, cargar ayudantes por parte
                loadAyudantesByParteTerceraSeccion();
            } else {
                // Si no hay parte seleccionada, cargar todos los ayudantes disponibles
                loadAyudantesByParteTerceraSeccion();
            }
        }
    });

    // Manejar cambio en el select de ayudante para tercera sección
    $(document).on('change', '#ayudante_id_tercera_seccion', function() {
        // No procesar eventos durante la carga en modo edición
        if (window.editingParteTerceraData) {
            return;
        }

        const ayudanteSeleccionado = $(this).val();
        const encargadoSelect = $('#encargado_id_tercera_seccion');

        // Limpiar historial anterior
        clearHistorialAyudanteTercera();

        // Continuar con la lógica del ayudante
        continueAyudanteChangeTercera(ayudanteSeleccionado, encargadoSelect);
    });

    function openCreateParteSegundaSeccionModal(isSalaAuxiliar = false) {
        isEditMode = false;

        if (isSalaAuxiliar) {
            $('#parteProgramaSegundaSeccionModalLabel').text('Nueva Asignación (Sala Auxiliar 1)');
            // Cambiar sala_id a 2 para sala auxiliar
            $('#parteProgramaSegundaSeccionForm input[name="sala_id"]').val('2');
        } else {
            $('#parteProgramaSegundaSeccionModalLabel').text('Nueva Asignación (Sala Principal)');
            // Mantener sala_id en 1 para sala principal
            $('#parteProgramaSegundaSeccionForm input[name="sala_id"]').val('1');
        }

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

        // Actualizar el estado de los botones al limpiar los campos
        updateButtonStatesSegundaSeccion();

        // Limpiar campos de reemplazados
        clearEncargadoReemplazado();
        clearAyudanteReemplazado();

        // Pequeño delay para asegurar que los campos se hayan limpiado
        setTimeout(function() {
            // Limpiar historiales
            clearHistorialEncargado();
            clearHistorialAyudante();

            // Mostrar campos de reemplazados en modo edición
            $('#campos-reemplazados-segunda-seccion').show();

            // Mostrar botones de agregar reemplazado en modo edición
            $('#btn-agregar-encargado-reemplazado').show();
            $('#btn-agregar-ayudante-reemplazado').show();

            // Mostrar botones de reemplazado en modo edición
            $('#btn-encargado-reemplazado-segunda').show();
            $('#btn-ayudante-reemplazado-segunda').show();
        }, 50);

        // Variable para controlar si estamos en modo edición para evitar eventos conflictivos
        window.editingParteTwoData = true;

        $.ajax({
            url: `/partes-programa/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const parte = response.data;

                    // Establecer el sala_id correcto y actualizar el título del modal
                    if (parte.sala_id == 2) {
                        $('#parteProgramaSegundaSeccionModalLabel').text('Editar Asignación (Sala Auxiliar 1)');
                        $('#parteProgramaSegundaSeccionForm input[name="sala_id"]').val('2');
                    } else {
                        $('#parteProgramaSegundaSeccionModalLabel').text('Editar Asignación (Sala Principal)');
                        $('#parteProgramaSegundaSeccionForm input[name="sala_id"]').val('1');
                    }
                    $('#parte_id_segunda_seccion').val(parte.parte_id);
                    $('#tiempo_segunda_seccion').val(parte.tiempo);
                    $('#leccion_segunda_seccion').val(parte.leccion);

                    // Cargar nombres de usuarios reemplazados si existen
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

                    // Asegurar que los campos de reemplazados sean visibles
                    $('#campos-reemplazados-segunda-seccion').show();

                    // Verificar después de un delay que los campos se hayan cargado correctamente
                    setTimeout(function() {
                        // Verificación completada
                    }, 500);                    // Las variables de control de reemplazados se inicializarán después de cargar los selects

                    // Cargar datos necesarios en secuencia
                    loadPartesSeccionesForEditSegundaSeccion(parte.parte_id);

                    // Esperar un poco y luego cargar encargados
                    setTimeout(function() {
                        loadEncargadosByParteSegundaSeccion(parte.parte_id, parte.encargado_id);

                        // Establecer directamente el encargado seleccionado después de un momento
                        setTimeout(function() {
                            if (parte.encargado_id) {
                                $('#encargado_id_segunda_seccion').val(parte.encargado_id);
                                $('#encargado_display_segunda_seccion').val(parte.encargado_nombre);
                            }
                        }, 100);

                        // Esperar otro poco y cargar ayudantes
                        setTimeout(function() {
                            if (parte.encargado_id && parte.parte_id) {
                                loadAyudantesByEncargadoAndParte(parte.encargado_id, parte.parte_id, parte.ayudante_id);
                            } else {
                                loadAyudantesByParteSegundaSeccion(parte.ayudante_id);
                            }

                            // Establecer directamente el ayudante seleccionado después de un momento
                            setTimeout(function() {
                                if (parte.ayudante_id) {
                                    $('#ayudante_id_segunda_seccion').val(parte.ayudante_id);
                                    $('#ayudante_display_segunda_seccion').val(parte.ayudante_nombre);
                                }

                                // Actualizar el estado de los botones después de cargar los datos
                                updateButtonStatesSegundaSeccion();
                            }, 600);

                            // Cargar historial del encargado si existe
                            if (parte.encargado_id) {
                                loadHistorialEncargado(parte.encargado_id);
                            } else {
                                clearHistorialEncargado();
                            }

                            // Liberar el flag después de todo el proceso
                            setTimeout(function() {
                                window.editingParteTwoData = false;

                                // Inicializar variables para control de reemplazados después de que todo esté cargado
                                encargadoAnterior = $('#encargado_id_segunda_seccion').val();
                                const encargadoOption = $('#encargado_id_segunda_seccion').find('option:selected');
                                encargadoAnteriorNombre = encargadoOption.text() || '';

                                ayudanteAnterior = $('#ayudante_id_segunda_seccion').val();
                                const ayudanteOption = $('#ayudante_id_segunda_seccion').find('option:selected');
                                ayudanteAnteriorNombre = ayudanteOption.text() || '';
                            }, 400);

                        }, 300);
                    }, 200);

                    $('#parteProgramaSegundaSeccionModal').modal('show');
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
                        // Por ahora, recargamos ambas tablas para asegurar consistencia
                        loadPartesSegundaSeccion();
                        loadPartesTerceraSeccion();
                        $('#confirmDeleteModal').modal('hide');
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
            showAlert('modal-alert-container-segunda-seccion', 'warning', 'El Encargado y el Ayudante no pueden ser la misma persona.');
            return;
        }

        // Validar que el encargado y el encargado reemplazado sean distintos
        const encargadoReemplazadoId = $('#encargado_reemplazado_id_segunda_seccion').val();
        if (encargadoId && encargadoReemplazadoId && encargadoId === encargadoReemplazadoId) {
            showAlert('modal-alert-container-segunda-seccion', 'warning', 'El Encargado y el Encargado Reemplazado no pueden ser la misma persona.');
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
                    showAlert('modal-alert-container-segunda-seccion', 'warning', 'Para esta Asignación es obligatorio seleccionar un Encargado.');
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

                    // Determinar qué datatable actualizar según el sala_id
                    const salaId = $('#parteProgramaSegundaSeccionForm input[name="sala_id"]').val();
                    if (salaId == '2') {
                        loadPartesTerceraSeccion(); // Sala auxiliar → actualizar tabla de tercera sección
                    } else {
                        loadPartesSegundaSeccion(); // Sala principal → actualizar tabla de segunda sección
                    }

                    showAlert('alert-container', 'success', response.message);
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

    function submitParteTerceraSeccion() {
        // Validar que Encargado y Ayudante no sean la misma persona
        const encargadoId = $('#encargado_id_tercera_seccion').val();
        const ayudanteId = $('#ayudante_id_tercera_seccion').val();
        const parteSeleccionada = $('#parte_id_tercera_seccion').val();

        if (encargadoId && ayudanteId && encargadoId === ayudanteId) {
            showAlert('modal-alert-container-tercera-seccion', 'warning', 'El Encargado y el Ayudante no pueden ser la misma persona.');
            return;
        }

        // Validar que para partes tipo 2 o 3, tanto Encargado como Ayudante sean obligatorios
        if (parteSeleccionada) {
            const selectedOption = $('#parte_id_tercera_seccion').find('option:selected');
            const tipo = selectedOption.data('tipo');


                if (!encargadoId || encargadoId === '') {
                    showAlert('modal-alert-container-tercera-seccion', 'warning', 'Para esta Asignación es obligatorio seleccionar un Encargado.');
                    $('#encargado_id_tercera_seccion').addClass('is-invalid');
                    return;
                }
            if (tipo == 2 || tipo == 3) {
                if (!ayudanteId || ayudanteId === '') {
                    showAlert('modal-alert-container-tercera-seccion', 'warning', 'Para esta Asignación es obligatorio seleccionar un Ayudante.');
                    $('#ayudante_id_tercera_seccion').addClass('is-invalid');
                    return;
                }
            }
        }

        // Validar campo tiempo
        const tiempoValue = $('#tiempo_tercera_seccion').val();
        if (!tiempoValue || tiempoValue < 1) {
            showAlert('modal-alert-container-tercera-seccion', 'warning', 'El campo Tiempo es obligatorio y debe ser mayor a 0.');
            $('#tiempo_tercera_seccion').addClass('is-invalid');
            return;
        }

        // Campo leccion ahora es opcional

        const isEdit = isEditMode;
        const url = isEdit ? `/partes-programa/${$('#parte_programa_tercera_seccion_id').val()}` : '/partes-programa';
        const method = isEdit ? 'PUT' : 'POST';

        const formData = $('#parteProgramaTerceraSeccionForm').serialize();

        const submitBtn = $('#saveTerceraSeccionBtn');
        const spinner = submitBtn.find('.spinner-border');

        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Limpiar alertas del modal
        $('#modal-alert-container-tercera-seccion').empty();

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#parteProgramaTerceraSeccionModal').modal('hide');
                    loadPartesTerceraSeccion();
                    showAlert('alert-container', 'success', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        let fieldName = field;
                        if (field === 'tiempo') fieldName = 'tiempo_tercera_seccion';
                        if (field === 'encargado_id') fieldName = 'encargado_id_tercera_seccion';
                        if (field === 'ayudante_id') fieldName = 'ayudante_id_tercera_seccion';
                        if (field === 'parte_id') fieldName = 'parte_id_tercera_seccion';
                        if (field === 'leccion') fieldName = 'leccion_tercera_seccion';

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
                        select.append(`<option value="${parte.id}" data-tiempo="${parte.tiempo}" data-tipo="${parte.tipo}">${parte.abreviacion} - ${parte.nombre}</option>`);
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
            success: function(response) {
                if (response.success) {
                    const select = $('#parte_id_segunda_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    // Cargar todas las partes activas y la parte seleccionada
                    response.data.forEach(function(parte) {
                        const selected = parte.id == parteIdSeleccionada ? 'selected' : '';
                        select.append(`<option value="${parte.id}" data-tiempo="${parte.tiempo}" data-tipo="${parte.tipo}" ${selected}>${parte.abreviacion} - ${parte.nombre}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error al cargar las partes disponibles:', xhr.responseText);
            }
        });
    }

    function loadEncargadosByParteSegundaSeccion(parteId, encargadoSeleccionado = null) {
        // Obtener el ID de la parte programa que se está editando
        const editingId = $('#parte_programa_segunda_seccion_id').val();
        const url = `/encargados-por-parte-programa/${parteId}` + (editingId ? `?editing_id=${editingId}` : '');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#encargado_id_segunda_seccion');
                    const ayudanteSeleccionado = $('#ayudante_id_segunda_seccion').val();

                    select.empty().append('<option value="">Seleccionar...</option>');

                    // Separar usuarios por sexo
                    const mujeres = response.data.filter(usuario => usuario.sexo == 2);
                    const hombres = response.data.filter(usuario => usuario.sexo == 1);

                    // Agregar sección de Mujeres
                    if (mujeres.length > 0) {
                        select.append('<option disabled style="font-weight: bold;">--- Mujeres ---</option>');
                        mujeres.forEach(function(usuario) {
                            const selected = encargadoSeleccionado && usuario.id == encargadoSeleccionado ? 'selected' : '';
                            select.append(`<option value="${usuario.id}" ${selected}>${usuario.display_text}</option>`);
                        });
                    }

                    // Agregar sección de Hombres
                    if (hombres.length > 0) {
                        select.append('<option disabled style="font-weight: bold;">--- Hombres ---</option>');
                        hombres.forEach(function(usuario) {
                            const selected = encargadoSeleccionado && usuario.id == encargadoSeleccionado ? 'selected' : '';
                            select.append(`<option value="${usuario.id}" ${selected}>${usuario.display_text}</option>`);
                        });
                    }

                    select.trigger('change');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar encargados:', xhr.responseText);
            }
        });
    }

    function loadUsuariosDisponiblesSegundaSeccion() {
        $.ajax({
            url: '/usuarios-disponibles',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#encargado_id_segunda_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    response.data.forEach(function(usuario) {
                        select.append(`<option value="${usuario.id}">${usuario.name}</option>`);
                    });

                    select.trigger('change');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar usuarios disponibles:', xhr.responseText);
            }
        });
    }

    function loadAyudantesDisponiblesSegundaSeccion() {
        $.ajax({
            url: '/usuarios-disponibles',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#ayudante_id_segunda_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    response.data.forEach(function(usuario) {
                        select.append(`<option value="${usuario.id}">${usuario.name}</option>`);
                    });

                    select.trigger('change');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar ayudantes disponibles:', xhr.responseText);
            }
        });
    }

    function loadAyudantesByParteSegundaSeccion(ayudanteSeleccionado = null) {
        const parteId = $('#parte_id_segunda_seccion').val();

        if (!parteId) {
            // Si no hay parte seleccionada, limpiar el select
            programmaticChange = true;
            $('#ayudante_id_segunda_seccion').empty().append('<option value="">Seleccionar parte primero...</option>').trigger('change');
            programmaticChange = false;
            return;
        }

        // Obtener IDs auxiliares
        const editingId = $('#parte_programa_segunda_seccion_id').val();
        const encargadoId = $('#encargado_id_segunda_seccion').val();

        // Construir URL usando el endpoint unificado con soporte de secciones por género
        const params = [];
        if (editingId) params.push(`editing_id=${editingId}`);
        if (encargadoId) params.push(`encargado_id=${encargadoId}`);
        const url = `/ayudantes-por-parte-programa/${parteId}` + (params.length ? `?${params.join('&')}` : '');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                const select = $('#ayudante_id_segunda_seccion');
                const encargadoSeleccionado = $('#encargado_id_segunda_seccion').val();

                select.empty().append('<option value="">Seleccionar...</option>');

                if (response.success && Array.isArray(response.data)) {
                    response.data.forEach(function(usuario) {
                        if (usuario.is_section) {
                            // Encabezado de sección deshabilitado (Hombres/Mujeres)
                            const label = usuario.display_text || usuario.name || '—';
                            select.append(`<option value="" disabled style="font-weight: bold; background-color: #f8f9fa;">${label}</option>`);
                        } else {
                            // Opción normal
                            const disabled = encargadoSeleccionado && usuario.id == encargadoSeleccionado ? 'disabled' : '';
                            const selected = ayudanteSeleccionado && usuario.id == ayudanteSeleccionado ? 'selected' : '';
                            const displayText = usuario.display_text || usuario.name;
                            select.append(`<option value="${usuario.id}" ${disabled} ${selected}>${displayText}</option>`);
                        }
                    });
                } else {
                    programmaticChange = true;
                    select.empty().append('<option value="">No hay ayudantes disponibles</option>').trigger('change');
                    programmaticChange = false;
                    return;
                }

                select.trigger('change');
            },
            error: function(xhr) {
                console.error('Error al cargar ayudantes por parte (segunda):', xhr.responseText);
                programmaticChange = true;
                $('#ayudante_id_segunda_seccion').empty().append('<option value="">Error al cargar ayudantes</option>').trigger('change');
                programmaticChange = false;
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
        if (!parteId) {
            alert('Por favor seleccione una parte primero');
            return;
        }

        // Abrir modal y cargar usuarios con participaciones en la parte seleccionada
        $('#buscarEncargadoSegundaSeccionModal').modal('show');

        // Al abrir el modal, deshabilitar el botón Seleccionar
        $('#confirmarEncargadoSegundaSeccion').prop('disabled', true);

        // Cuando se seleccione un encargado, habilitar el botón Seleccionar y cargar historial
        $(document).off('change.select_encargado_segunda_seccion').on('change.select_encargado_segunda_seccion', '#select_encargado_segunda_seccion', function() {
            const val = $(this).val();
            $('#confirmarEncargadoSegundaSeccion').prop('disabled', !val);

            // Cargar historial del encargado seleccionado
            if (val) {
                loadHistorialEncargadoSegundaSeccion(val,parteId);
            } else {
                clearHistorialEncargadoSegundaSeccion();
            }
        });

        // Cargar usuarios que han participado como encargados en esta parte
        $.ajax({
            url: `/encargados-por-parte-programa-smm/${parteId}`,
            method: 'GET',
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

                    // Preseleccionar el encargado actual si existe
                    const encargadoActual = $('#encargado_id_segunda_seccion').val();
                    if (encargadoActual) {
                        select.val(encargadoActual).trigger('change');
                    }

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

                    // Preseleccionar el encargado actual si existe (ya declarado arriba)
                    if (encargadoActual) {
                        select.val(encargadoActual).trigger('change');
                    }
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

    function verHistorialEncargadoSegundaSeccion() {
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        if (!encargadoId) {
            alert('No hay encargado seleccionado');
            return;
        }

        // Aquí iría la lógica para mostrar el historial del encargado
        // Similar a verHistorialEncargadoParte() pero para segunda sección
        alert('Función verHistorialEncargadoSegundaSeccion() - Por implementar\nEncargado ID: ' + encargadoId);
    }

    function buscarAyudanteSegundaSeccion() {
        const parteId = $('#parte_id_segunda_seccion').val();
        const encargadoId = $('#encargado_id_segunda_seccion').val();
        const selectedOption = $('#parte_id_segunda_seccion').find('option:selected');
        const tipo = selectedOption.data('tipo');

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

        // Cargar usuarios que han participado como ayudantes (endpoint unificado) y ordenar secciones según encargado si aplica
        let url = `/ayudantes-por-parte-programa/${parteId}`;
        if (encargadoId) {
            url += `?encargado_id=${encargadoId}`;
        }

        $.ajax({
            url: url,
            method: 'GET',
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
        const btnBuscarAyudante2 = $('#btn-buscar-ayudante2-segunda');

        // Botones del encargado
        if (encargadoId) {
            $('#btn-encargado-reemplazado-segunda').prop('disabled', false);
            btnBuscarEncargado.prop('disabled', false);
            btnBuscarEncargado.attr('title', 'Buscar Encargado');
        } else {
            $('#btn-encargado-reemplazado-segunda').prop('disabled', true);
            btnBuscarEncargado.prop('disabled', true);
            btnBuscarEncargado.attr('title', 'Seleccionar encargado primero');
        }
        //console.log('encargado=', encargadoId, 'parte=', parteSeleccionada);
        // Botón "Buscar Ayudante" - aplicar la nueva lógica
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
            btnBuscarAyudante.attr('title', 'Seleccionar encargado y parte con tipo 2 o 3');
        }

        // Botones del ayudante
        if (ayudanteId) {
            $('#btn-ayudante-reemplazado-segunda').prop('disabled', false);
        } else {
            $('#btn-ayudante-reemplazado-segunda').prop('disabled', true);
        }
    }

    // Funciones para la tercera sección
    function openCreateParteTerceraSeccionModal() {
        isEditMode = false;
        $('#parteProgramaTerceraSeccionModalLabel').text('Nueva Asignación de Seamos Mejores Maestros');
        $('#parteProgramaTerceraSeccionForm')[0].reset();

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Limpiar alertas del modal
        $('#modal-alert-container-tercera-seccion').empty();

        // Cargar partes usando las mismas condiciones que la segunda sección
        loadPartesSeccionesTerceraSeccion();

        // Dejar campos Encargado y Ayudante vacíos hasta que se seleccione una parte
        $('#encargado_id_tercera_seccion').empty().append('<option value="">Seleccionar una parte primero...</option>').trigger('change');
        $('#ayudante_id_tercera_seccion').empty().append('<option value="">Seleccionar...</option>').trigger('change');

        // Limpiar campos de reemplazados en modo nuevo
        $('#campos-reemplazados-tercera-seccion').hide();
        clearEncargadoReemplazadoTercera();
        clearAyudanteReemplazadoTercera();

        // Ocultar botones de agregar reemplazado en modo nuevo
        $('#btn-agregar-encargado-reemplazado-tercera').hide();
        $('#btn-agregar-ayudante-reemplazado-tercera').hide();

        // Limpiar historial de encargado y ayudante
        clearHistorialEncargadoTercera();
        clearHistorialAyudanteTercera();
    }

    function loadPartesSeccionesTerceraSeccion(callback) {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes-segunda-seccion-disponibles`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#parte_id_tercera_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    // Usar las mismas partes que la segunda sección
                    response.data.forEach(function(parte) {
                        select.append(`<option value="${parte.id}" data-tiempo="${parte.tiempo}">${parte.abreviacion} - ${parte.nombre}</option>`);
                    });

                    // Ejecutar callback si se proporciona
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error al cargar las partes disponibles:', xhr.responseText);
            }
        });
    }

    function clearEncargadoReemplazadoTercera() {
        $('#encargado_reemplazado_tercera_seccion').val('');
        $('#encargado_reemplazado_id_tercera_seccion').val('');
    }

    function clearAyudanteReemplazadoTercera() {
        $('#ayudante_reemplazado_tercera_seccion').val('');
        $('#ayudante_reemplazado_id_tercera_seccion').val('');
    }

    function agregarEncargadoReemplazadoTercera() {
        const encargadoSelect = $('#encargado_id_tercera_seccion');
        const encargadoSeleccionado = encargadoSelect.val();

        if (encargadoSeleccionado) {
            const selectedOption = encargadoSelect.find('option:selected');
            const textoCompleto = selectedOption.text();

            // Extraer solo el nombre del usuario del formato: fecha|parte|tipo|nombre
            let nombreEncargado = textoCompleto;
            if (textoCompleto.includes('|')) {
                const partes = textoCompleto.split('|');
                if (partes.length >= 4) {
                    nombreEncargado = partes[3].trim();
                }
            }

            // Agregar el nombre al campo visible
            $('#encargado_reemplazado_tercera_seccion').val(nombreEncargado);

            // Agregar el ID al campo oculto para ser guardado en la BD
            $('#encargado_reemplazado_id_tercera_seccion').val(encargadoSeleccionado);


        } else {
            alert('Por favor seleccione un encargado primero');
        }
    }

    function agregarAyudanteReemplazadoTercera() {
        const ayudanteSelect = $('#ayudante_id_tercera_seccion');
        const ayudanteSeleccionado = ayudanteSelect.val();

        if (ayudanteSeleccionado) {
            const selectedOption = ayudanteSelect.find('option:selected');
            const textoCompleto = selectedOption.text();

            // Extraer solo el nombre del usuario del formato: fecha|parte|tipo|nombre
            let nombreAyudante = textoCompleto;
            if (textoCompleto.includes('|')) {
                const partes = textoCompleto.split('|');
                if (partes.length >= 4) {
                    nombreAyudante = partes[3].trim();
                }
            }

            // Agregar el nombre al campo visible
            $('#ayudante_reemplazado_tercera_seccion').val(nombreAyudante);

            // Agregar el ID al campo oculto para ser guardado en la BD
            $('#ayudante_reemplazado_id_tercera_seccion').val(ayudanteSeleccionado);


        } else {
            alert('Por favor seleccione un ayudante primero');
        }
    }

    function continueAyudanteChangeTercera(ayudanteSeleccionado, encargadoSelect) {
        if (ayudanteSeleccionado) {
            // Cargar historial del ayudante seleccionado

        } else {
            // Limpiar historial del ayudante
            clearHistorialAyudanteTercera();
        }

        // Actualizar Select2 para reflejar los cambios
        encargadoSelect.trigger('change.select2');
    }

    function loadEncargadosByParteTerceraSeccion(parteId, encargadoSeleccionado = null) {
        const editingId = $('#parte_programa_tercera_seccion_id').val();
        const url = `/encargados-por-parte-programa/${parteId}` + (editingId ? `?editing_id=${editingId}` : '');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#encargado_id_tercera_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    // Separar usuarios por sexo
                    const mujeres = response.data.filter(usuario => usuario.sexo == 2);
                    const hombres = response.data.filter(usuario => usuario.sexo == 1);

                    // Agregar sección de Mujeres
                    if (mujeres.length > 0) {
                        select.append('<option disabled style="font-weight: bold;">--- Mujeres ---</option>');
                        mujeres.forEach(function(usuario) {
                            const selected = encargadoSeleccionado && usuario.id == encargadoSeleccionado ? 'selected' : '';
                            select.append(`<option value="${usuario.id}" ${selected}>${usuario.display_text}</option>`);
                        });
                    }

                    // Agregar sección de Hombres
                    if (hombres.length > 0) {
                        select.append('<option disabled style="font-weight: bold;">--- Hombres ---</option>');
                        hombres.forEach(function(usuario) {
                            const selected = encargadoSeleccionado && usuario.id == encargadoSeleccionado ? 'selected' : '';
                            select.append(`<option value="${usuario.id}" ${selected}>${usuario.display_text}</option>`);
                        });
                    }

                    select.trigger('change');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar encargados por parte (tercera):', xhr.responseText);
                const select = $('#encargado_id_tercera_seccion');
                select.empty().append('<option value="">Error al cargar encargados</option>').trigger('change');
            }
        });
    }

    function loadUsuariosDisponiblesTerceraSeccion() {
        $.ajax({
            url: '/usuarios-disponibles',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const select = $('#encargado_id_tercera_seccion');
                    select.empty().append('<option value="">Seleccionar...</option>');

                    response.data.forEach(function(usuario) {
                        select.append(`<option value="${usuario.id}">${usuario.name}</option>`);
                    });

                    select.trigger('change');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar encargados por parte (tercera):', xhr.responseText);
                const select = $('#encargado_id_tercera_seccion');
                select.empty().append('<option value="">Error al cargar encargados</option>').trigger('change');
            }
        });
    }

    function loadAyudantesByParteTerceraSeccion(ayudanteSeleccionado = null) {
        const parteId = $('#parte_id_tercera_seccion').val();
        if (!parteId) {
            return;
        }

        const editingId = $('#parte_programa_tercera_seccion_id').val();
        const encargadoId = $('#encargado_id_tercera_seccion').val();

        const params = [];
        if (editingId) params.push(`editing_id=${editingId}`);
        if (encargadoId) params.push(`encargado_id=${encargadoId}`);
        const url = `/ayudantes-por-parte-programa/${parteId}` + (params.length ? `?${params.join('&')}` : '');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                const select = $('#ayudante_id_tercera_seccion');
                const encargadoSeleccionado = $('#encargado_id_tercera_seccion').val();

                select.empty().append('<option value="">Seleccionar...</option>');

                if (response.success && Array.isArray(response.data)) {
                    response.data.forEach(function(usuario) {
                        if (usuario.is_section) {
                            const label = usuario.display_text || usuario.name || '—';
                            select.append(`<option value="" disabled style="font-weight: bold; background-color: #f8f9fa;">${label}</option>`);
                        } else {
                            const selected = ayudanteSeleccionado && usuario.id == ayudanteSeleccionado ? 'selected' : '';
                            const disabled = encargadoSeleccionado && usuario.id == encargadoSeleccionado ? 'disabled' : '';
                            const displayText = usuario.display_text || usuario.name;
                            select.append(`<option value="${usuario.id}" ${selected} ${disabled}>${displayText}</option>`);
                        }
                    });
                    select.trigger('change');
                } else {
                    select.empty().append('<option value="">No hay ayudantes disponibles</option>').trigger('change');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar ayudantes por parte (tercera):', xhr.responseText);
                const select = $('#ayudante_id_tercera_seccion');
                select.empty().append('<option value="">Error al cargar ayudantes</option>').trigger('change');
            }
        });
    }

    function loadAyudantesByEncargadoAndParteTercera(encargadoId, parteId, ayudanteIdToSelect = null, callback = null) {
        const editingId = $('#parte_programa_tercera_seccion_id').val();

        $.ajax({
            url: `/ayudantes-por-encargado/${encargadoId}/${parteId}` + (editingId ? `?editing_id=${editingId}` : ''),
            method: 'GET',
            success: function(response) {
                if (response.success && Array.isArray(response.data)) {
                    const select = $('#ayudante_id_tercera_seccion');

                    select.empty().append('<option value="">Seleccionar...</option>');

                    response.data.forEach(function(usuario) {
                        if (usuario.is_section) {
                            const label = usuario.display_text || usuario.name || '—';
                            select.append(`<option value="" disabled style="font-weight: bold; background-color: #f8f9fa;">${label}</option>`);
                        } else {
                            select.append(`<option value="${usuario.id}">${usuario.display_text || usuario.name}</option>`);
                        }
                    });

                    // Preseleccionar ayudante si se especifica
                    if (ayudanteIdToSelect) {
                        select.val(ayudanteIdToSelect).trigger('change');
                    } else {
                        select.trigger('change');
                    }

                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error al cargar ayudantes por encargado/parte (tercera):', xhr.responseText);
            }
        });
    }

    function editParteTerceraSeccion(id) {
        isEditMode = true;
        $('#parteProgramaTerceraSeccionModalLabel').text('Editar Asignación de Seamos Mejores Maestros');
        $('#parte_programa_tercera_seccion_id').val(id);

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Limpiar alertas del modal
        $('#modal-alert-container-tercera-seccion').empty();

        // Limpiar completamente el formulario antes de cargar datos
        $('#parteProgramaTerceraSeccionForm')[0].reset();

        // Limpiar todos los selects
        $('#parte_id_tercera_seccion').empty().append('<option value="">Seleccionar...</option>');
        $('#encargado_id_tercera_seccion').empty().append('<option value="">Seleccionar...</option>');
        $('#ayudante_id_tercera_seccion').empty().append('<option value="">Seleccionar...</option>');

        // Limpiar campos de reemplazados
        clearEncargadoReemplazadoTercera();
        clearAyudanteReemplazadoTercera();

        // Limpiar historiales
        clearHistorialEncargadoTercera();
        clearHistorialAyudanteTercera();

        // Mostrar campos de reemplazados en modo edición
        $('#campos-reemplazados-tercera-seccion').show();

        // Mostrar botones de agregar reemplazado en modo edición
        $('#btn-agregar-encargado-reemplazado-tercera').show();
        $('#btn-agregar-ayudante-reemplazado-tercera').show();

        // Variable para controlar si estamos en modo edición para evitar eventos conflictivos
        window.editingParteTerceraData = true;

        $.ajax({
            url: `/partes-programa/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const parte = response.data;
                    $('#parte_id_tercera_seccion').val(parte.parte_id);
                    $('#tiempo_tercera_seccion').val(parte.tiempo);
                    $('#leccion_tercera_seccion').val(parte.leccion);

                    // Cargar nombres de usuarios reemplazados si existen
                    if (parte.encargado_reemplazado) {
                        $('#encargado_reemplazado_tercera_seccion').val(parte.encargado_reemplazado.name);
                        $('#encargado_reemplazado_id_tercera_seccion').val(parte.encargado_reemplazado.id);
                    } else {
                        $('#encargado_reemplazado_tercera_seccion').val('');
                        $('#encargado_reemplazado_id_tercera_seccion').val('');
                    }

                    if (parte.ayudante_reemplazado) {
                        $('#ayudante_reemplazado_tercera_seccion').val(parte.ayudante_reemplazado.name);
                        $('#ayudante_reemplazado_id_tercera_seccion').val(parte.ayudante_reemplazado.id);
                    } else {
                        $('#ayudante_reemplazado_tercera_seccion').val('');
                        $('#ayudante_reemplazado_id_tercera_seccion').val('');
                    }

                    // Cargar datos necesarios en secuencia
                    loadPartesSeccionesTerceraSeccion(function() {
                        // Preseleccionar la parte después de cargar las opciones
                        $('#parte_id_tercera_seccion').val(parte.parte_id).trigger('change');

                        setTimeout(function() {
                            loadEncargadosByParteTerceraSeccion(parte.parte_id, parte.encargado_id);

                            setTimeout(function() {
                                if (parte.encargado_id) {
                                    $('#encargado_id_tercera_seccion').val(parte.encargado_id).trigger('change');
                                }
                            }, 100);

                            setTimeout(function() {
                                if (parte.encargado_id && parte.parte_id) {
                                    loadAyudantesByEncargadoAndParteTercera(parte.encargado_id, parte.parte_id, parte.ayudante_id, function() {
                                        // Callback después de cargar ayudantes




                                        // Liberar el flag después de todo el proceso
                                        setTimeout(function() {
                                            window.editingParteTerceraData = false;
                                        }, 200);
                                    });
                                } else {
                                    loadAyudantesByParteTerceraSeccion(parte.ayudante_id);

                                    setTimeout(function() {
                                        if (parte.ayudante_id) {
                                            $('#ayudante_id_tercera_seccion').val(parte.ayudante_id).trigger('change');
                                        }
                                    }, 200);





                                    // Liberar el flag después de todo el proceso
                                    setTimeout(function() {
                                        window.editingParteTerceraData = false;
                                    }, 400);
                                }
                            }, 300);
                        }, 200);
                    });

                    $('#parteProgramaTerceraSeccionModal').modal('show');
                }
            },
            error: function(xhr) {
                console.error('Error al cargar la parte:', xhr.responseText);
                window.editingParteTerceraData = false;
            }
        });
    }

    function deleteParteTerceraSeccion(id) {
        $('#confirmDeleteBtn').off('click').on('click', function() {
            $.ajax({
                url: `/partes-programa/${id}`,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        loadPartesTerceraSeccion();
                        $('#confirmDeleteModal').modal('hide');
                        showAlert('alert-container', 'success', response.message);
                    }
                },
                error: function(xhr) {
                    $('#confirmDeleteModal').modal('hide');
                    showAlert('alert-container', 'danger', xhr.responseJSON?.message || 'Error al eliminar la parte');
                }
            });
        });

        $('#confirmDeleteModal').modal('show');
    }

    function moveParteTerceraSeccionUp(id) {
        const button = event.target.closest('button');
        if (button && button.disabled) {
            return;
        }
        moveParteTerceraSeccion(id, 'up');
    }

    function moveParteTerceraSeccionDown(id) {
        const button = event.target.closest('button');
        if (button && button.disabled) {
            return;
        }
        moveParteTerceraSeccion(id, 'down');
    }

    function moveParteTerceraSeccion(id, direction) {
        const url = `/partes-programa/${id}/move-${direction}`;

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    loadPartesTerceraSeccion();
                    showAlert('alert-container', 'success', response.message);
                } else {
                    showAlert('alert-container', 'warning', response.message);
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || 'Error al mover la parte';
                showAlert('alert-container', 'danger', errorMessage);
            }
        });
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
    // Funciones para tercera sección
    window.openCreateParteTerceraSeccionModal = openCreateParteTerceraSeccionModal;
    window.editParteTerceraSeccion = editParteTerceraSeccion;
    window.deleteParteTerceraSeccion = deleteParteTerceraSeccion;
    window.moveParteTerceraSeccionUp = moveParteTerceraSeccionUp;
    window.moveParteTerceraSeccionDown = moveParteTerceraSeccionDown;
    window.clearEncargadoReemplazadoTercera = clearEncargadoReemplazadoTercera;
    window.clearAyudanteReemplazadoTercera = clearAyudanteReemplazadoTercera;
    window.agregarEncargadoReemplazadoTercera = agregarEncargadoReemplazadoTercera;
    window.agregarAyudanteReemplazadoTercera = agregarAyudanteReemplazadoTercera;

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
                        select.val(encargadoActual).trigger('change');
                    }

                    // Agregar event listener para cargar historial cuando se selecciona un encargado
                    select.off('change.historial').on('change.historial', function() {
                        const encargadoSeleccionado = $(this).val();
                        const parteId = $('#parte_id').val();

                        if (encargadoSeleccionado && parteId) {
                            cargarHistorialEncargado(encargadoSeleccionado, parteId);
                        } else {
                            // Limpiar historial si no hay selección
                            const historialSelect = $('#select_historial_encargado_parte');
                            historialSelect.empty();
                            historialSelect.append('<option value="">Seleccione un encargado primero...</option>');
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
            alert('No hay encargado reemplazado para eliminar');
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

    // Funciones para limpiar historiales (tercera sección)
    function clearHistorialEncargadoTercera() {
        // Esta función se llama para mantener consistencia en la tercera sección
        // No hay campos específicos de historial que limpiar en esta sección
    }

    function clearHistorialAyudanteTercera() {
        // Esta función se llama para mantener consistencia en la tercera sección
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
    window.clearHistorialEncargadoTercera = clearHistorialEncargadoTercera;
    window.clearHistorialAyudanteTercera = clearHistorialAyudanteTercera;
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
                                // Cerrar modal inmediatamente después de mostrar el mensaje
                                $('#buscarEncargadoSegundaSeccionModal').modal('hide');
                            } else {
                                // Cerrar modal inmediatamente si no hay cambios
                                $('#buscarEncargadoSegundaSeccionModal').modal('hide');
                            }
                        } else {
                            // Cerrar modal incluso si hay error en la respuesta
                            $('#buscarEncargadoSegundaSeccionModal').modal('hide');
                        }
                    },
                    error: function(xhr) {
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
        $('#buscarEncargadoSegundaSeccionModal').modal('hide');
    });

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
    function loadHistorialEncargadoSegundaSeccion(encargadoId,parteId) {
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
                nombreAyudante = partes[3].trim();
            }
        }

        // Actualizar los campos
        $('#ayudante_id_segunda_seccion').val(ayudanteSeleccionado);
        $('#ayudante_display_segunda_seccion').val(nombreAyudante);

        // Actualizar el estado de los botones
        updateButtonStatesSegundaSeccion();

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
            searching: false
        });
    }

    function loadPartesNV() {
        const programaId = $('#programa_id').val();

        $.ajax({
            url: `/programas/${programaId}/partes-nv`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Limpiar la tabla
                    partesNVTable.clear();

                    response.data.forEach(function(parte) {
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
                            parte.tiempo || '-',
                            parte.parte_abreviacion || '-',
                            parte.encargado_nombre || '-',
                            parte.tema || '-',
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
                        select.val(encargadoActual).trigger('change');
                    }

                    // Agregar event listener para cargar historial cuando se selecciona un encargado
                    select.off('change.historial_nv').on('change.historial_nv', function() {
                        const encargadoSeleccionado = $(this).val();
                        const parteId = $('#parte_id_nv').val();

                        if (encargadoSeleccionado && parteId) {
                            cargarHistorialEncargadoNV(encargadoSeleccionado, parteId);
                        } else {
                            // Limpiar historial si no hay selección
                            const historialSelect = $('#select_historial_encargado_parte_nv');
                            historialSelect.empty();
                            historialSelect.append('<option value="">Seleccione un encargado primero...</option>');
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
            alert('No hay encargado reemplazado para eliminar');
            return;
        }

        // Eliminar directamente sin confirmación
        $('#encargado_reemplazado_id_nv').val('');
        $('#encargado_reemplazado_display_nv').val('Sin encargado reemplazado...');

        // Deshabilitar el botón de eliminar
        $('#btn-eliminar-reemplazado-nv').prop('disabled', true);
    }
});