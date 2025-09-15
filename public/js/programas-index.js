$(document).ready(function() {
    // Verificar que el token CSRF esté disponible
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    if (!csrfToken) {
        console.error('CSRF token not found. Make sure the meta tag is present in the HTML.');
        return;
    }

    // Inicializar DataTable
    $('#programasTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        responsive: true,
        order: [[0, 'desc']], // Ordenar por fecha descendente
        columnDefs: [
            { targets: [8], orderable: false } // Columna de acciones no ordenable
        ]
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
});