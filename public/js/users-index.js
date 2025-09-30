$(document).ready(function() {
    // Inicializar Select2 cuando se abran los modales
    $('#addUserModal').on('shown.bs.modal', function() {
        // Destruir Select2 si ya existe
        if ($('#asignaciones').hasClass('select2-hidden-accessible')) {
            $('#asignaciones').select2('destroy');
        }

        // Inicializar Select2 para modal de agregar
        $('#asignaciones').select2({
            theme: 'bootstrap-5',
            placeholder: "Selecciona asignaciones...",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#addUserModal'),
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });

        // Preseleccionar congregación para perfiles específicos
        if (window.usersIndexConfig.userCongregacion) {
            $('#congregacion').val(window.usersIndexConfig.userCongregacion);
        }
    });

    $('#editUserModal').on('shown.bs.modal', function() {
        // Destruir Select2 si ya existe
        if ($('#edit_asignaciones').hasClass('select2-hidden-accessible')) {
            $('#edit_asignaciones').select2('destroy');
        }

        // Inicializar Select2 para modal de editar
        $('#edit_asignaciones').select2({
            theme: 'bootstrap-5',
            placeholder: "Selecciona asignaciones...",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#editUserModal'),
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });
    });

    // Destruir Select2 cuando se cierren los modales
    $('#addUserModal').on('hidden.bs.modal', function() {
        if ($('#asignaciones').hasClass('select2-hidden-accessible')) {
            $('#asignaciones').select2('destroy');
        }
    });

    $('#editUserModal').on('hidden.bs.modal', function() {
        if ($('#edit_asignaciones').hasClass('select2-hidden-accessible')) {
            $('#edit_asignaciones').select2('destroy');
        }
    });

    // Inicializar DataTable
    var table = $('#usersTable').DataTable({
        responsive: true,
        language: {
            url: '/js/datatables-es-ES.json'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        columnDefs: window.usersIndexConfig.datatablesColumnDefs
    });

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

        // Auto-hide success alerts after 5 seconds
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

    // Filtro por congregación
    $('#congregacionFilter').on('change', function() {
        const selectedCongregacion = $(this).val();

        if (selectedCongregacion === '') {
            table.column(3).search('').draw();
        } else {
            table.column(3).search(selectedCongregacion).draw();
        }
    });

    // Filtro por grupo
    $('#grupoFilter').on('change', function() {
        const selectedGrupo = $(this).val();
        const grupoColumnIndex = window.usersIndexConfig.isLimitedUser ? 4 : 3;

        if (selectedGrupo === '') {
            table.column(grupoColumnIndex).search('').draw();
        } else {
            table.column(grupoColumnIndex).search(selectedGrupo).draw();
        }
    });

    // Filtro por nombramiento
    $('#nombramientoFilter').on('change', function() {
        const selectedNombramiento = $(this).val();
        const nombramientoColumnIndex = window.usersIndexConfig.isLimitedUser ? 5 : 4;

        if (selectedNombramiento === '') {
            table.column(nombramientoColumnIndex).search('').draw();
        } else {
            table.column(nombramientoColumnIndex).search(selectedNombramiento).draw();
        }
    });

    // Filtro por servicio
    $('#servicioFilter').on('change', function() {
        const selectedServicio = $(this).val();
        const servicioColumnIndex = window.usersIndexConfig.isLimitedUser ? 6 : 5;

        if (selectedServicio === '') {
            table.column(servicioColumnIndex).search('').draw();
        } else {
            table.column(servicioColumnIndex).search(selectedServicio).draw();
        }
    });

    // Filtro por perfil
    $('#perfilFilter').on('change', function() {
        const selectedPerfil = $(this).val();

        if (selectedPerfil === '') {
            table.column(2).search('').draw();
        } else {
            table.column(2).search(selectedPerfil).draw();
        }
    });

    // Filtro por estado espiritual
    let currentEstadoEspiritualFilter = null;

    $('#estadoEspiritualFilter').on('change', function() {
        const selectedEstadoEspiritual = $(this).val();

        // Limpiar filtro anterior si existe
        if (currentEstadoEspiritualFilter !== null) {
            $.fn.dataTable.ext.search.pop();
        }

        if (selectedEstadoEspiritual) {
            currentEstadoEspiritualFilter = selectedEstadoEspiritual;
            const estadoEspiritualColumnIndex = window.usersIndexConfig.isLimitedUser ? 7 : 6;

            // Agregar nuevo filtro
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    const estadoEspiritualColumn = data[estadoEspiritualColumnIndex];
                    return estadoEspiritualColumn.includes(selectedEstadoEspiritual);
                }
            );
        } else {
            currentEstadoEspiritualFilter = null;
        }

        table.draw();
    });

    // Filtro por asignación (para coordinadores, organizadores y suborganizadores)
    if (window.usersIndexConfig.showAsignacionFilter) {
        let currentAsignacionFilter = null;

        $('#asignacionFilter').on('change', function() {
            const selectedAsignacion = $(this).val();

            // Limpiar filtro anterior si existe
            if (currentAsignacionFilter !== null) {
                $.fn.dataTable.ext.search.splice($.fn.dataTable.ext.search.indexOf(currentAsignacionFilter), 1);
            }

            if (selectedAsignacion === '') {
                currentAsignacionFilter = null;
                table.draw();
            } else {
                // Para coordinadores, organizadores y suborganizadores: Asignación está en columna 7
                const asignacionColumnIndex = 7;

                // Crear nueva función de filtro
                currentAsignacionFilter = function(settings, data, dataIndex) {
                    if (settings.nTable !== table.table().node()) {
                        return true;
                    }
                    const asignacionColumn = data[asignacionColumnIndex];
                    return asignacionColumn.includes(selectedAsignacion);
                };

                // Agregar el nuevo filtro
                $.fn.dataTable.ext.search.push(currentAsignacionFilter);
                table.draw();
            }
        });
    }

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
            const textoEstado = selectedEstado === '1' ? 'Activo' : 'Inactivo';
            const estadoColumnIndex = window.usersIndexConfig.estadoColumnIndex;

            // Crear nueva función de filtro
            currentEstadoFilter = function(settings, data, dataIndex) {
                if (settings.nTable !== table.table().node()) {
                    return true;
                }
                const estadoColumn = data[estadoColumnIndex];
                return estadoColumn.indexOf(textoEstado) !== -1;
            };

            // Agregar el nuevo filtro
            $.fn.dataTable.ext.search.push(currentEstadoFilter);
            table.draw();
        }
    });

    // Manejar clic en botón Ver
    $(document).on('click', '.view-user', function() {
        const userId = $(this).data('user-id');

        // Cargar datos del usuario
        $.ajax({
            url: `/usuarios/${userId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const user = response.user;

                    // Llenar los campos del modal
                    $('#view_name').text(user.name || '-');
                    $('#view_nombre_completo').text(user.nombre_completo || '-');
                    $('#view_email').text(user.email || '-');

                    // Cargar datos de las relaciones
                    loadUserRelationData(user);

                    // Mostrar información de auditoría
                    $('#view_creado_por').text(user.creado_por_nombre ?
                        `${user.creado_por_nombre} - ${user.creado_por_timestamp}` : '-');
                    $('#view_modificado_por').text(user.modificado_por_nombre ?
                        `${user.modificado_por_nombre} - ${user.modificado_por_timestamp}` : '-');

                    // Mostrar modal
                    $('#viewUserModal').modal('show');
                } else {
                    showAlert('danger', response.message || 'Error al cargar los datos del usuario.');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response?.message || 'Error al cargar los datos del usuario.';
                showAlert('danger', message);
            }
        });
    });

    // Función para cargar datos de relaciones
    function loadUserRelationData(user) {
        // Buscar congregación
        if (window.usersIndexConfig.congregaciones) {
            window.usersIndexConfig.congregaciones.forEach(function(congregacion) {
                if (congregacion.id == user.congregacion) {
                    $('#view_congregacion').text(congregacion.nombre);
                }
            });
        }

        // Buscar perfil
        if (window.usersIndexConfig.perfiles) {
            window.usersIndexConfig.perfiles.forEach(function(perfil) {
                if (perfil.id == user.perfil) {
                    $('#view_perfil').text(perfil.privilegio);
                }
            });
        }

        // Buscar sexo
        if (window.usersIndexConfig.sexos) {
            window.usersIndexConfig.sexos.forEach(function(sexoItem) {
                if (sexoItem.id == user.sexo) {
                    $('#view_sexo').text(sexoItem.nombre);
                }
            });
        }

        // Buscar servicio
        let servicioEncontrado = false;
        if (window.usersIndexConfig.servicios) {
            window.usersIndexConfig.servicios.forEach(function(servicio) {
                if (servicio.id == user.servicio) {
                    $('#view_servicio').text(servicio.nombre);
                    servicioEncontrado = true;
                }
            });
        }
        if (!servicioEncontrado) {
            $('#view_servicio').text('-');
        }

        // Buscar nombramiento
        if (window.usersIndexConfig.nombramientos) {
            window.usersIndexConfig.nombramientos.forEach(function(nombramiento) {
                if (nombramiento.id == user.nombramiento) {
                    $('#view_nombramiento').text(nombramiento.nombre);
                }
            });
        }

        // Buscar esperanza
        if (window.usersIndexConfig.esperanzas) {
            window.usersIndexConfig.esperanzas.forEach(function(esperanza) {
                if (esperanza.id == user.esperanza) {
                    $('#view_esperanza').text(esperanza.nombre);
                }
            });
        }

        // Buscar grupo
        if (window.usersIndexConfig.grupos) {
            window.usersIndexConfig.grupos.forEach(function(grupo) {
                if (grupo.id == user.grupo_id) {
                    $('#view_grupo').text(grupo.nombre);
                }
            });
        }

        // Buscar estado espiritual
        if (window.usersIndexConfig.estadosEspirituales) {
            window.usersIndexConfig.estadosEspirituales.forEach(function(estadoEspiritual) {
                if (estadoEspiritual.id == user.estado_espiritual) {
                    $('#view_estado_espiritual').text(estadoEspiritual.nombre);
                }
            });
        }

        // Campos directos
        $('#view_fecha_nacimiento').text(user.fecha_nacimiento || '-');
        $('#view_fecha_bautismo').text(user.fecha_bautismo || '-');
        $('#view_telefono').text(user.telefono || '-');
        $('#view_persona_contacto').text(user.persona_contacto || '-');
        $('#view_telefono_contacto').text(user.telefono_contacto || '-');
        $('#view_observacion').text(user.observacion || '-');
        $('#view_estado').html(user.estado == 1 ?
            '<span class="badge bg-success">Activo</span>' :
            '<span class="badge bg-danger">Inactivo</span>');

        // Mostrar asignaciones
        if (user.asignaciones && Array.isArray(user.asignaciones) && user.asignaciones.length > 0) {
            let asignacionesHtml = '';
            user.asignaciones.forEach(function(asignacionId) {
                if (window.usersIndexConfig.asignaciones) {
                    window.usersIndexConfig.asignaciones.forEach(function(asignacion) {
                        if (asignacion.id == asignacionId) {
                            asignacionesHtml += '<span class="badge bg-info me-1 mb-1">' + asignacion.nombre + '</span>';
                        }
                    });
                }
            });
            $('#view_asignaciones').html(asignacionesHtml || '-');
        } else {
            $('#view_asignaciones').text('-');
        }
    }

    // Limpiar formulario al cerrar modal
    $('#addUserModal').on('hidden.bs.modal', function() {
        $('#addUserForm')[0].reset();
        clearValidationErrors();
        $('#saveUserBtn .spinner-border').addClass('d-none');
        $('#saveUserBtn').prop('disabled', false);
        // Limpiar Select2 si existe
        if ($('#asignaciones').hasClass('select2-hidden-accessible')) {
            $('#asignaciones').val([]).trigger('change');
        }

        // Preseleccionar congregación después del reset para perfiles específicos
        if (window.usersIndexConfig.userCongregacion) {
            setTimeout(function() {
                $('#congregacion').val(window.usersIndexConfig.userCongregacion);
            }, 100);
        }
    });

    // Envío del formulario
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $('#saveUserBtn');
        const spinner = submitBtn.find('.spinner-border');

        // Deshabilitar botón y mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        // Limpiar errores previos
        clearValidationErrors();

        $.ajax({
            url: window.usersIndexConfig.storeRoute,
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Cerrar modal
                    $('#addUserModal').modal('hide');

                    // Mostrar mensaje de éxito
                    showAlert('success', response.message);

                    // Recargar la página para actualizar la tabla
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;

                if (xhr.status === 422 && response.errors) {
                    // Errores de validación
                    showValidationErrors(response.errors);
                } else {
                    // Otros errores
                    const message = response.message || 'Error al crear el usuario. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                // Rehabilitar botón y ocultar spinner
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Inicializar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Variables para almacenar datos del usuario a eliminar
    let userToDelete = null;

    // Manejar clic en botón Editar
    $(document).on('click', '.edit-user', function() {
        const userId = $(this).data('user-id');

        // Limpiar formulario y errores
        $('#editUserForm')[0].reset();
        clearValidationErrors();

        // Cargar datos del usuario
        $.ajax({
            url: `/usuarios/${userId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const user = response.user;

                    // Llenar formulario
                    $('#edit_user_id').val(user.id);
                    $('#edit_name').val(user.name);
                    $('#edit_nombre_completo').val(user.nombre_completo);
                    $('#edit_email').val(user.email);
                    $('#edit_perfil').val(user.perfil);
                    $('#edit_estado').val(user.estado);
                    $('#edit_congregacion').val(user.congregacion);
                    $('#edit_fecha_nacimiento').val(user.fecha_nacimiento);
                    $('#edit_fecha_bautismo').val(user.fecha_bautismo);
                    $('#edit_telefono').val(user.telefono);
                    $('#edit_persona_contacto').val(user.persona_contacto);
                    $('#edit_telefono_contacto').val(user.telefono_contacto);
                    $('#edit_sexo').val(user.sexo);
                    $('#edit_servicio').val(user.servicio);
                    $('#edit_nombramiento').val(user.nombramiento);
                    $('#edit_esperanza').val(user.esperanza);
                    $('#edit_grupo').val(user.grupo_id);
                    $('#edit_estado_espiritual').val(user.estado_espiritual);
                    $('#edit_observacion').val(user.observacion);

                    // Cargar asignaciones del usuario
                    if (user.asignaciones && Array.isArray(user.asignaciones)) {
                        $('#edit_asignaciones').val(user.asignaciones).trigger('change');
                    } else {
                        $('#edit_asignaciones').val([]).trigger('change');
                    }

                    // Mostrar modal
                    $('#editUserModal').modal('show');
                } else {
                    showAlert('danger', response.message || 'Error al cargar los datos del usuario.');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                const message = response?.message || 'Error al cargar los datos del usuario.';
                showAlert('danger', message);
            }
        });
    });

    // Limpiar formulario de edición al cerrar modal
    $('#editUserModal').on('hidden.bs.modal', function() {
        $('#editUserForm')[0].reset();
        clearValidationErrors();
        $('#updateUserBtn .spinner-border').addClass('d-none');
        $('#updateUserBtn').prop('disabled', false);
        // Limpiar Select2 si existe
        if ($('#edit_asignaciones').hasClass('select2-hidden-accessible')) {
            $('#edit_asignaciones').val([]).trigger('change');
        }
    });

    // Envío del formulario de edición
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const userId = $('#edit_user_id').val();
        const submitBtn = $('#updateUserBtn');
        const spinner = submitBtn.find('.spinner-border');

        // Deshabilitar botón y mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        // Limpiar errores previos
        clearValidationErrors();

        $.ajax({
            url: `/usuarios/${userId}`,
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Cerrar modal
                    $('#editUserModal').modal('hide');

                    // Mostrar mensaje de éxito
                    showAlert('success', response.message);

                    // Recargar la página para actualizar la tabla
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;

                if (xhr.status === 422 && response.errors) {
                    // Errores de validación
                    showValidationErrors(response.errors);
                } else {
                    // Otros errores
                    const message = response.message || 'Error al actualizar el usuario. Intente nuevamente.';
                    showAlert('danger', message);
                }
            },
            complete: function() {
                // Rehabilitar botón y ocultar spinner
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Manejar exportación PDF
    $('#exportPdfBtn').on('click', function(e) {
        e.preventDefault();

        // Obtener los filtros actuales
        const filters = {
            congregacion: $('#congregacionFilter').val(),
            grupo: $('#grupoFilter').val(),
            nombramiento: $('#nombramientoFilter').val(),
            servicio: $('#servicioFilter').val(),
            estadoEspiritual: $('#estadoEspiritualFilter').val(),
            perfil: $('#perfilFilter').val(),
            estado: $('#estadoFilter').val(),
            asignacion: $('#asignacionFilter').val()
        };

        // Construir la URL con los parámetros de filtro
        const params = new URLSearchParams();
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                params.append(key, filters[key]);
            }
        });

        // Crear la URL completa
        const url = `${window.usersIndexConfig.exportPdfRoute}?${params.toString()}`;

        // Usar window.location.href para mantener la sesión
        window.location.href = url;
    });

});