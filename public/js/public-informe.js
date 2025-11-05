$(document).ready(function() {
    // Obtener configuración global
    const config = window.publicInformeConfig;
    
    // Configurar CSRF token para todas las peticiones AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': config.csrfToken
        }
    });
    
    // IDs de servicios que requieren horas (Precursor Regular=1, Precursor Especial=3)
    const serviciosConHoras = [1, 3];

    /**
     * Mostrar alerta
     */
    function showAlert(message, type = 'info') {
        const alertClass = type === 'error' ? 'danger' : type;
        const iconClass = type === 'error' ? 'exclamation-triangle' : 
                         type === 'success' ? 'check-circle' : 'info-circle';
        
        const alert = $(`
            <div class="alert alert-${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas fa-${iconClass} me-2"></i>
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
        
        $('#alert-container').html(alert);
        
        // Scroll al inicio
        $('html, body').animate({ scrollTop: 0 }, 300);
        
        // Auto-cerrar después de 8 segundos
        setTimeout(function() {
            alert.fadeOut('slow', function() {
                $(this).remove();
            });
        }, 8000);
    }

    /**
     * Limpiar errores de validación
     */
    function clearValidationErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    /**
     * Mostrar errores de validación
     */
    function showValidationErrors(errors) {
        clearValidationErrors();
        
        $.each(errors, function(field, messages) {
            const input = $(`[name="${field}"]`);
            input.addClass('is-invalid');
            input.siblings('.invalid-feedback').text(messages[0]);
        });
    }

    /**
     * Cargar usuarios por grupo
     */
    $('#grupo_id').on('change', function() {
        const grupoId = $(this).val();
        const userSelect = $('#user_id');
        
        // Resetear select de usuarios
        userSelect.html('<option value="">Cargando usuarios...</option>');
        userSelect.prop('disabled', true);
        
        if (!grupoId) {
            userSelect.html('<option value="">Seleccione primero un grupo...</option>');
            validateForm();
            return;
        }
        
        // Realizar petición AJAX
        $.ajax({
            url: config.getUsersByGrupoUrl,
            method: 'GET',
            data: { grupo_id: grupoId },
            success: function(response) {
                if (response.success && response.usuarios.length > 0) {
                    let options = '<option value="">Seleccione un usuario...</option>';
                    response.usuarios.forEach(function(usuario) {
                        options += `<option value="${usuario.id}">${usuario.name}</option>`;
                    });
                    userSelect.html(options);
                    userSelect.prop('disabled', false);
                } else {
                    userSelect.html('<option value="">No hay usuarios en este grupo</option>');
                    showAlert('No se encontraron usuarios activos en el grupo seleccionado', 'warning');
                }
                validateForm();
            },
            error: function() {
                userSelect.html('<option value="">Error al cargar usuarios</option>');
                showAlert('Error al cargar los usuarios del grupo', 'error');
                validateForm();
            }
        });
    });

    /**
     * Validar campos obligatorios y habilitar/deshabilitar botón de envío
     */
    function validateForm() {
        const grupoId = $('#grupo_id').val();
        const userId = $('#user_id').val();
        const periodo = $('#periodo').val();
        const servicioId = $('#servicio_id').val();
        const participa = $('#participa').is(':checked');
        const horas = $('#horas').val();
        const cantidadEstudios = $('#cantidad_estudios').val();
        
        // Servicio es siempre obligatorio
        let isValid = grupoId && userId && periodo && servicioId;
        
        // Si participa Y el servicio requiere horas, validar horas
        if (participa) {
            const servicioIdInt = parseInt(servicioId);
            if (serviciosConHoras.includes(servicioIdInt)) {
                isValid = isValid && (horas > 0 && horas <= 100 && horas);
            }
        }
        isValid = isValid && (cantidadEstudios >= 0 && cantidadEstudios <= 50 && cantidadEstudios);
        // Habilitar/deshabilitar botón de envío
        $('#submitBtn').prop('disabled', !isValid);
        
        // Mostrar/ocultar mensaje de ayuda
        if (isValid) {
            $('#submitBtnHint').fadeOut();
        } else {
            $('#submitBtnHint').fadeIn();
        }
    }

    /**
     * Control de checkbox "Participa"
     */
    $('#participa').on('change', function() {
        const participa = $(this).is(':checked');
        const servicioId = parseInt($('#servicio_id').val());
        const horasInput = $('#horas');
        const horasContainer = $('#horas_container');
        const cantidadEstudiosContainer = $('#cantidad_estudios_container');
        const cantidadEstudiosInput = $('#cantidad_estudios');
        
        if (!participa) {
            // Ocultar campo de estudios con animación
            cantidadEstudiosContainer.slideUp(300);
            cantidadEstudiosInput.val('0').prop('disabled', true);
            
            // Ocultar y resetear campo de horas
            horasContainer.slideUp(300);
            horasInput.val('').prop('disabled', true);
            horasInput.attr('required', false);
        } else {
            // Mostrar campo de estudios con animación
            cantidadEstudiosContainer.slideDown(300);
            cantidadEstudiosInput.prop('disabled', false);
            
            // Si participa y el servicio requiere horas, mostrar y habilitar horas
            if (serviciosConHoras.includes(servicioId)) {
                horasContainer.slideDown(300);
                horasInput.prop('disabled', false);
                horasInput.attr('required', true);
            }
        }

        // Validar formulario
        validateForm();
    });

    /**
     * Control del campo cantidad de estudios según checkbox "Participa"
     */
    $('#cantidad_estudios').on('input change', function() {
        validateForm();
    });

    /**
     * Control del campo Horas según el Servicio seleccionado
     */
    $('#servicio_id').on('change', function() {
        const participa = $('#participa').is(':checked');
        const servicioId = parseInt($(this).val());
        const horasInput = $('#horas');
        const horasContainer = $('#horas_container');
        
        if(participa) {
            if (serviciosConHoras.includes(servicioId)) {
                // Mostrar y habilitar horas para servicios específicos
                horasContainer.slideDown(300);
                horasInput.prop('disabled', false);
                horasInput.attr('required', true);
            } else {
                // Ocultar y deshabilitar horas para otros servicios
                horasContainer.slideUp(300);
                horasInput.val('');
                horasInput.prop('disabled', true);
                horasInput.attr('required', false);
            }
        }
        // Validar formulario
        validateForm();
    });

    /**
     * Función para enviar el formulario (después de obtener token reCAPTCHA si está habilitado)
     */
    function submitFormData() {
        // Limpiar errores previos
        clearValidationErrors();
        
        // Deshabilitar botón de envío
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Enviando...');
        
        // Preparar datos del formulario
        const formData = {
            grupo_id: $('#grupo_id').val(),
            user_id: $('#user_id').val(),
            periodo: $('#periodo').val(),
            participa: $('#participa').is(':checked') ? 1 : 0,
            servicio_id: $('#servicio_id').val() || null,
            cantidad_estudios: $('#cantidad_estudios').val() || 0,
            horas: $('#horas').val() || null,
            comentario: $('#comentario').val() || null
        };
        
        // Agregar token de reCAPTCHA si está habilitado
        if (config.recaptchaEnabled) {
            formData['g-recaptcha-response'] = $('#g-recaptcha-response-informe').val();
        }
        
        // Enviar datos
        $.ajax({
            url: config.storeUrl,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('¡Informe enviado exitosamente! Gracias por su colaboración.', 'success');
                    
                    // Resetear formulario
                    $('#informeForm')[0].reset();
                    $('#cantidad_estudios').prop('disabled', true);
                    $('#horas').prop('disabled', true);
                    $('#user_id').html('<option value="">Seleccione primero un grupo...</option>').prop('disabled', true);
                    
                    // Deshabilitar botón de envío después de resetear
                    $('#submitBtn').prop('disabled', true);
                    
                    // Scroll al inicio
                    $('html, body').animate({ scrollTop: 0 }, 300);
                } else {
                    showAlert(response.message || 'Error al enviar el informe', 'error');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Errores de validación
                    const errors = xhr.responseJSON.errors;
                    showValidationErrors(errors);
                    showAlert(xhr.responseJSON?.message || 'Por favor, corrija los errores en el formulario', 'error');
                } else {
                    const message = xhr.responseJSON?.message || 'Error al enviar el informe. Por favor, intente nuevamente.';
                    showAlert(message, 'error');
                }
            },
            complete: function() {
                // Habilitar botón de envío
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
                
                // Regenerar token de reCAPTCHA si está habilitado
                if (config.recaptchaEnabled && typeof grecaptcha !== 'undefined') {
                    grecaptcha.ready(function() {
                        grecaptcha.execute(config.recaptchaSiteKey, {action: 'informe_submit'})
                            .then(function(token) {
                                $('#g-recaptcha-response-informe').val(token);
                            });
                    });
                }
            }
        });
    }

    /**
     * Envío del formulario
     */
    $('#informeForm').on('submit', function(e) {
        e.preventDefault();
        
        // Si reCAPTCHA está habilitado, generar token antes de enviar
        if (config.recaptchaEnabled && typeof grecaptcha !== 'undefined') {
            grecaptcha.ready(function() {
                grecaptcha.execute(config.recaptchaSiteKey, {action: 'informe_submit'})
                    .then(function(token) {
                        $('#g-recaptcha-response-informe').val(token);
                        submitFormData();
                    })
                    .catch(function(error) {
                        console.error('Error al obtener token de reCAPTCHA:', error);
                        showAlert('Error de verificación de seguridad. Por favor, recargue la página e intente nuevamente.', 'error');
                    });
            });
        } else {
            // Si reCAPTCHA no está habilitado, enviar directamente
            submitFormData();
        }
    });

    /**
     * Validación en tiempo real
     */
    $('select[required], input[required]').on('change blur', function() {
        const input = $(this);
        if (input.val()) {
            input.removeClass('is-invalid');
            input.siblings('.invalid-feedback').text('');
        }
        validateForm();
    });
    
    // Validar también cuando cambia user_id y periodo
    $('#user_id, #periodo').on('change', function() {
        validateForm();
    });
    
    // Validar cuando cambian las horas
    $('#horas').on('input change', function() {
        validateForm();
    });

    /**
     * Contador de caracteres para comentarios
     */
    $('#comentario').on('input', function() {
        const maxLength = 1000;
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;
        
        let counterText = `${currentLength} / ${maxLength} caracteres`;
        if (remaining < 100) {
            counterText = `<span class="text-warning">${counterText} (${remaining} restantes)</span>`;
        }
        
        // Actualizar o crear contador
        let counter = $(this).siblings('.char-counter');
        if (counter.length === 0) {
            counter = $('<small class="form-text text-muted char-counter"></small>');
            $(this).after(counter);
        }
        counter.html(counterText);
    });

    /**
     * Animación de focus en inputs
     */
    $('.form-control, .form-select').on('focus', function() {
        $(this).closest('.mb-4').addClass('focused');
    }).on('blur', function() {
        $(this).closest('.mb-4').removeClass('focused');
    });
    
    /**
     * Inicializar estado del botón de envío (deshabilitado al cargar)
     */
    $('#submitBtn').prop('disabled', true);
    validateForm();
    
    /**
     * Inicializar reCAPTCHA si está habilitado
     */
    if (config.recaptchaEnabled && typeof grecaptcha !== 'undefined') {
        grecaptcha.ready(function() {
            grecaptcha.execute(config.recaptchaSiteKey, {action: 'informe_submit'})
                .then(function(token) {
                    $('#g-recaptcha-response-informe').val(token);
                })
                .catch(function(error) {
                    console.error('Error al inicializar reCAPTCHA:', error);
                });
        });
    }
});
