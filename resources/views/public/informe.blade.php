<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Envío de Informe - {{ $congregacion->nombre }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/public-informe.css') }}">
</head>
<body>
    <div class="container-fluid">
        <div class="row min-vh-100">
            <!-- Columna izquierda con imagen/info -->
            <div class="col-lg-5 left-panel d-none d-lg-flex flex-column justify-content-center align-items-center">
                <div class="text-center text-white p-5">
                    <i class="fas fa-file-alt icon-large mb-4"></i>
                    <h2 class="mb-4">Sistema de Informes</h2>
                    <h3 class="mb-3">{{ $congregacion->nombre }}</h3>
                    <p class="lead">Complete su informe mensual de actividades de forma rápida y sencilla</p>
                </div>
            </div>

            <!-- Columna derecha con formulario -->
            <div class="col-lg-7 right-panel d-flex align-items-center">
                <div class="form-container">
                    <div class="card border-0 shadow-lg">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">
                                <i class="fas fa-clipboard-check me-2"></i>
                                Envío de Informe Mensual
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <!-- Mensajes de alerta -->
                            <div id="alert-container"></div>

                            <form id="informeForm" novalidate>
                                @csrf
                                
                                <!-- Grupo -->
                                <div class="mb-4">
                                    <label for="grupo_id" class="form-label">
                                        <i class="fas fa-users me-2"></i>Grupo *
                                    </label>
                                    <select class="form-select form-select-lg" id="grupo_id" name="grupo_id" required>
                                        <option value="">Seleccione un grupo...</option>
                                        @foreach($grupos as $grupo)
                                            <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Usuario -->
                                <div class="mb-4">
                                    <label for="user_id" class="form-label">
                                        <i class="fas fa-user me-2"></i>Usuario *
                                    </label>
                                    <select class="form-select form-select-lg" id="user_id" name="user_id" required disabled>
                                        <option value="">Seleccione primero un grupo...</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Período -->
                                <div class="mb-4">
                                    <label for="periodo" class="form-label">
                                        <i class="fas fa-calendar-alt me-2"></i>Período *
                                    </label>
                                    <select class="form-select form-select-lg" id="periodo" name="periodo" required>
                                        <option value="">Seleccione un período...</option>
                                        @foreach($periodos as $periodo)
                                            <option value="{{ $periodo['value'] }}">{{ $periodo['label'] }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Servicio -->
                                <div class="mb-4">
                                    <label for="servicio_id" class="form-label">
                                        <i class="fas fa-hands-helping me-2"></i>Servicio *
                                    </label>
                                    <select class="form-select form-select-lg" id="servicio_id" name="servicio_id" required>
                                        <option value="">Seleccione un servicio...</option>
                                        @foreach($servicios as $servicio)
                                            <option value="{{ $servicio->id }}">{{ $servicio->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Actividad (Participa) -->
                                <div class="mb-4">
                                    <div class="form-check form-switch form-check-lg">
                                        <input class="form-check-input" type="checkbox" id="participa" name="participa" value="1">
                                        <label class="form-check-label" for="participa">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <strong>Participé en actividades este mes</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">Active esta opción si participó en el ministerio durante el período seleccionado</small>
                                </div>

                                <!-- Estudios -->
                                <div class="mb-4">
                                    <label for="cantidad_estudios" class="form-label">
                                        <i class="fas fa-book-reader me-2"></i>Cantidad de Estudios
                                    </label>
                                    <input type="number" class="form-control form-control-lg" 
                                           id="cantidad_estudios" name="cantidad_estudios" 
                                           min="0" max="50" value="0" disabled>
                                    <div class="invalid-feedback"></div>
                                    <small class="text-muted">Número de estudios bíblicos realizados (0-50)</small>
                                </div>

                                <!-- Horas -->
                                <div class="mb-4">
                                    <label for="horas" class="form-label">
                                        <i class="fas fa-clock me-2"></i>Horas de Servicio
                                    </label>
                                    <input type="number" class="form-control form-control-lg" 
                                           id="horas" name="horas" 
                                           min="1" max="100"  value="0" disabled>
                                    <div class="invalid-feedback"></div>
                                    <small class="text-muted">Horas dedicadas al ministerio (1-100)</small>
                                </div>

                                <!-- Comentarios -->
                                <div class="mb-4">
                                    <label for="comentario" class="form-label">
                                        <i class="fas fa-comment me-2"></i>Comentarios
                                    </label>
                                    <textarea class="form-control" id="comentario" name="comentario" 
                                              rows="4" maxlength="1000" 
                                              placeholder="Comparta cualquier comentario adicional sobre su actividad..."></textarea>
                                    <div class="invalid-feedback"></div>
                                    <small class="text-muted">Máximo 1000 caracteres</small>
                                </div>

                                <!-- reCAPTCHA -->
                                @if(config('recaptcha.enabled'))
                                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-informe">
                                @endif

                                <!-- Botón de envío -->
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Enviar Informe
                                    </button>
                                    <small class="text-muted text-center" id="submitBtnHint">
                                        <i class="fas fa-lock me-1"></i>
                                        Complete todos los campos obligatorios para habilitar el botón
                                    </small>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-center text-muted bg-light">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Los campos marcados con * son obligatorios
                            </small>
                            @if(config('recaptcha.enabled'))
                            <div class="mt-2">
                                <small class="text-muted">
                                    Este sitio está protegido por reCAPTCHA y aplican la
                                    <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Política de Privacidad</a> y los
                                    <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Términos de Servicio</a> de Google.
                                </small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    @if(config('recaptcha.enabled'))
    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}"></script>
    @endif
    
    <!-- Custom JS -->
    <script src="{{ asset('js/public-informe.js') }}"></script>
    
    <script>
        // Configuración global
        window.publicInformeConfig = {
            congregacionId: {{ $congregacion->id }},
            congregacionCodigo: '{{ $congregacion->codigo }}',
            getUsersByGrupoUrl: '{{ route("public.informe.usuarios-por-grupo", $congregacion->codigo) }}',
            storeUrl: '{{ route("public.informe.store", $congregacion->codigo) }}',
            csrfToken: '{{ csrf_token() }}',
            recaptchaEnabled: {{ config('recaptcha.enabled') ? 'true' : 'false' }},
            recaptchaSiteKey: '{{ config('recaptcha.site_key') }}'
        };
    </script>
</body>
</html>
