@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @error('recaptcha')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>{{ $message }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror

                    @error('g-recaptcha-response')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>{{ $message }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror

                    <p class="text-muted mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        Ingresa tus credenciales para acceder al sistema.
                    </p>

                    <form method="POST" action="{{ route('login') }}" id="loginForm">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">
                                <i class="fas fa-envelope me-1"></i>Correo Electrónico
                            </label>

                            <div class="col-md-6">
                                <input id="email" 
                                       type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required 
                                       autocomplete="email" 
                                       autofocus
                                       placeholder="usuario@ejemplo.com">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">
                                <i class="fas fa-lock me-1"></i>Contraseña
                            </label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="password" 
                                           type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           name="password" 
                                           required 
                                           autocomplete="current-password"
                                           placeholder="Ingresa tu contraseña">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye" id="eyeIcon"></i>
                                    </button>
                                </div>

                                @error('password')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="remember" 
                                           id="remember" 
                                           {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        <i class="fas fa-clock me-1"></i>Recordarme
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Campo oculto para token de reCAPTCHA -->
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                        <div class="row mb-3">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" id="spinner"></span>
                                    <i class="fas fa-sign-in-alt me-2" id="loginIcon"></i>
                                    Iniciar Sesión
                                </button>
                            </div>
                        </div>

                        @if (Route::has('password.request'))
                        <div class="row">
                            <div class="col-md-8 offset-md-4">
                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    <i class="fas fa-key me-1"></i>¿Olvidaste tu contraseña?
                                </a>
                            </div>
                        </div>
                        @endif
                    </form>

                    <hr class="my-4">
                    
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Seguridad:</strong> Este sistema utiliza medidas de seguridad avanzadas para proteger tu cuenta. 
                        Solo los usuarios con cuentas activas pueden iniciar sesión.
                    </div>

                    @if(config('recaptcha.enabled'))
                    <div class="text-muted small text-center">
                        <i class="fas fa-shield-alt me-1"></i>
                        Este sitio está protegido por reCAPTCHA y se aplican la 
                        <a href="https://policies.google.com/privacy" target="_blank">Política de Privacidad</a> y 
                        <a href="https://policies.google.com/terms" target="_blank">Términos de Servicio</a> de Google.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Google reCAPTCHA v3 -->
@if(config('recaptcha.enabled'))
<script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}"></script>
<script>
    // Generar token al cargar la página
    grecaptcha.ready(function() {
        grecaptcha.execute('{{ config('recaptcha.site_key') }}', {action: 'login'}).then(function(token) {
            document.getElementById('g-recaptcha-response').value = token;
        });
    });
</script>
@endif

<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#togglePassword').on('click', function() {
        const passwordInput = $('#password');
        const eyeIcon = $('#eyeIcon');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Form submission
    $('#loginForm').on('submit', function(e) {
        const submitBtn = $('#submitBtn');
        const spinner = $('#spinner');
        const loginIcon = $('#loginIcon');
        
        @if(config('recaptcha.enabled'))
        // Regenerar token antes de enviar
        e.preventDefault();
        const form = this;
        
        grecaptcha.ready(function() {
            grecaptcha.execute('{{ config('recaptcha.site_key') }}', {action: 'login'}).then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
                
                // Deshabilitar botón y mostrar spinner
                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');
                loginIcon.addClass('d-none');
                
                // Enviar formulario
                form.submit();
            }).catch(function(error) {
                console.error('Error en reCAPTCHA:', error);
                
                // Mostrar error
                alert('Error en la verificación de seguridad. Por favor, recargue la página e intente nuevamente.');
                
                // Rehabilitar botón
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
                loginIcon.removeClass('d-none');
            });
        });
        @else
        // Si reCAPTCHA está deshabilitado, solo mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        loginIcon.addClass('d-none');
        @endif
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endsection
