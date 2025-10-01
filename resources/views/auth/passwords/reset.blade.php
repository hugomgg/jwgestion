@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-lock-open me-2"></i>Restablecer Contraseña
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <p class="text-muted mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        Ingresa tu nueva contraseña. Debe tener al menos 8 caracteres.
                    </p>

                    <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">
                                <i class="fas fa-envelope me-1"></i>Correo Electrónico
                            </label>

                            <div class="col-md-6">
                                <input id="email" 
                                       type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" 
                                       value="{{ $email ?? old('email') }}" 
                                       required 
                                       autocomplete="email" 
                                       autofocus
                                       readonly>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">
                                <i class="fas fa-key me-1"></i>Nueva Contraseña
                            </label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="password" 
                                           type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           name="password" 
                                           required 
                                           autocomplete="new-password"
                                           placeholder="Mínimo 8 caracteres">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye" id="eyeIcon"></i>
                                    </button>
                                </div>

                                @error('password')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

                                <div class="form-text">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    La contraseña debe tener al menos 8 caracteres
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">
                                <i class="fas fa-check-double me-1"></i>Confirmar Contraseña
                            </label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="password-confirm" 
                                           type="password" 
                                           class="form-control" 
                                           name="password_confirmation" 
                                           required 
                                           autocomplete="new-password"
                                           placeholder="Repite la contraseña">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                        <i class="fas fa-eye" id="eyeIconConfirm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- reCAPTCHA v3 Token -->
                        <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                        
                        @error('recaptcha_token')
                            <div class="row mb-3">
                                <div class="col-md-6 offset-md-4">
                                    <div class="alert alert-danger" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        {{ $message }}
                                    </div>
                                </div>
                            </div>
                        @enderror

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" id="spinner"></span>
                                    <i class="fas fa-lock-open me-2" id="lockIcon"></i>
                                    Restablecer Contraseña
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 offset-md-4">
                                <a href="{{ route('login') }}" class="btn btn-link">
                                    <i class="fas fa-arrow-left me-1"></i>Volver al inicio de sesión
                                </a>
                            </div>
                        </div>
                    </form>

                    <hr class="my-4">
                    
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Importante:</strong> Este enlace expirará en 60 minutos. Si necesitas un nuevo enlace, solicita otra recuperación de contraseña.
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
        grecaptcha.execute('{{ config('recaptcha.site_key') }}', {action: 'reset_password'}).then(function(token) {
            document.getElementById('recaptcha_token').value = token;
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

    // Toggle password confirmation visibility
    $('#togglePasswordConfirm').on('click', function() {
        const passwordInput = $('#password-confirm');
        const eyeIcon = $('#eyeIconConfirm');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Form submission
    $('#resetPasswordForm').on('submit', function(e) {
        const submitBtn = $('#submitBtn');
        const spinner = $('#spinner');
        const lockIcon = $('#lockIcon');
        
        @if(config('recaptcha.enabled'))
        // Regenerar token antes de enviar
        e.preventDefault();
        const form = this;
        
        grecaptcha.ready(function() {
            grecaptcha.execute('{{ config('recaptcha.site_key') }}', {action: 'reset_password'}).then(function(token) {
                document.getElementById('recaptcha_token').value = token;
                
                // Deshabilitar botón y mostrar spinner
                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');
                lockIcon.addClass('d-none');
                
                // Enviar formulario
                form.submit();
            });
        });
        @else
        // Si reCAPTCHA está deshabilitado, solo mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        lockIcon.addClass('d-none');
        @endif
    });

    // Password strength indicator (opcional)
    $('#password').on('input', function() {
        const password = $(this).val();
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[^a-zA-Z0-9]+/)) strength++;
        
        // Visual feedback could be added here
    });

    // Password match indicator
    $('#password-confirm').on('input', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        } else {
            $(this).removeClass('is-valid is-invalid');
        }
    });
});
</script>
@endsection
