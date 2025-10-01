@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-key me-2"></i>Recuperar Contraseña
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
                        Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                    </p>

                    <form method="POST" action="{{ route('password.email') }}" id="resetPasswordForm">
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
                                    <i class="fas fa-paper-plane me-2" id="sendIcon"></i>
                                    Enviar Enlace de Recuperación
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
                    
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Nota:</strong> Si no recibes el correo en unos minutos, verifica tu carpeta de spam o correo no deseado.
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
        grecaptcha.execute('{{ config('recaptcha.site_key') }}', {action: 'password_reset'}).then(function(token) {
            document.getElementById('recaptcha_token').value = token;
        });
    });
</script>
@endif

<script>
$(document).ready(function() {
    $('#resetPasswordForm').on('submit', function(e) {
        const submitBtn = $('#submitBtn');
        const spinner = $('#spinner');
        const sendIcon = $('#sendIcon');
        
        @if(config('recaptcha.enabled'))
        // Regenerar token antes de enviar
        e.preventDefault();
        const form = this;
        
        grecaptcha.ready(function() {
            grecaptcha.execute('{{ config('recaptcha.site_key') }}', {action: 'password_reset'}).then(function(token) {
                document.getElementById('recaptcha_token').value = token;
                
                // Deshabilitar botón y mostrar spinner
                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');
                sendIcon.addClass('d-none');
                
                // Enviar formulario
                form.submit();
            });
        });
        @else
        // Si reCAPTCHA está deshabilitado, solo mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        sendIcon.addClass('d-none');
        @endif
    });
});
</script>
@endsection
