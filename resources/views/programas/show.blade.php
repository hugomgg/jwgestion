@extends('layouts.app')

@section('content')
@if($currentUser->isCoordinator() || $currentUser->isSubcoordinator() || $currentUser->isOrganizer() || $currentUser->isSubsecretary() || $currentUser->isSuborganizer())
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="fas fa-eye me-2"></i>Ver Programa
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end gap-2">
                                <!-- Botón Editar (solo para Organizador y Coordinador) -->
                                @if($currentUser->isOrganizer() || $currentUser->isCoordinator())
                                <a href="{{ route('programas.edit', $programa->id) }}" 
                                   class="btn btn-warning" 
                                   title="Editar programa">
                                    <i class="fas fa-edit me-2"></i>Editar
                                </a>
                                @endif



                                <!-- Botón Volver -->
                                <a href="{{ route('programas.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Volver a Programas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Contenedor para alertas -->
                    <div id="alert-container"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Fecha</label>
                                <p class="form-control-plaintext">{{ $programa->fecha ? $programa->fecha->format('d/m/Y') : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Orador Inicial</label>
                                <p class="form-control-plaintext">{{ $programa->oradorInicial ? $programa->oradorInicial->name : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Canción Inicial</label>
                                <p class="form-control-plaintext">
                                    @if($programa->cancionPre)
                                        {{ $programa->cancionPre->numero ? $programa->cancionPre->numero . ' - ' : '' }}{{ $programa->cancionPre->nombre }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Presidencia</label>
                                <p class="form-control-plaintext">{{ $programa->presidenciaUsuario ? $programa->presidenciaUsuario->name : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sección TB -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-header" style="background-color: #BBE6FC;">
                                    <h6 class="mb-0">
                                        <i class="fas fa-book me-2"></i>TESOROS DE LA BIBLIA
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th style="width: 10%;">Núm.</th>
                                                    <th style="width: 10%;">Parte</th>
                                                    <th style="width: 25%;">Encargado</th>
                                                    <th style="width: 45%;">Tema</th>
                                                    <th style="width: 10%;">Tiempo (min)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="partesTableBody">
                                                <!-- Los datos se cargarán vía AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Escuela con Tabs -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-header" style="background-color: #FCF2BB;">
                                    <h6 class="mb-0">
                                        <i class="fas fa-graduation-cap me-2"></i>ESCUELA SEAMOS MEJORES MAESTROS
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th style="width: 10%;">Núm.</th>
                                                    <th style="width: 10%;">Parte</th>
                                                    <th style="width: 25%;">Encargado</th>
                                                    <th style="width: 25%;">Ayudante</th>
                                                    <th style="width: 10%;">Sala</th>
                                                    <th style="width: 10%;">Tiempo (min)</th>
                                                    <th style="width: 10%;">Lección</th>
                                                </tr>
                                            </thead>
                                            <tbody id="partesSegundaSeccionTableBody">
                                                <!-- Los datos se cargan dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección Nuestra Vida Cristiana -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-header" style="background-color: #FCBBBF;">
                                    <h6 class="mb-0">
                                        <i class="fas fa-briefcase me-2"></i>NUESTRA VIDA CRISTIANA
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th style="width: 10%;">Núm.</th>
                                                    <th style="width: 10%;">Parte</th>
                                                    <th style="width: 25%;">Encargado</th>
                                                    <th style="width: 45%;">Tema</th>
                                                    <th style="width: 10%;">Tiempo (min)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="partesNVTableBody">
                                                <!-- Los datos se cargarán vía AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Canción Intermedia</label>
                                <p class="form-control-plaintext">
                                    @if($programa->cancionEn)
                                        {{ $programa->cancionEn->numero ? $programa->cancionEn->numero . ' - ' : '' }}{{ $programa->cancionEn->nombre }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Canción Final</label>
                                <p class="form-control-plaintext">
                                    @if($programa->cancionPost)
                                        {{ $programa->cancionPost->numero ? $programa->cancionPost->numero . ' - ' : '' }}{{ $programa->cancionPost->nombre }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Orador Final</label>
                                <p class="form-control-plaintext">{{ $programa->oradorFinal ? $programa->oradorFinal->name : '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Botones flotantes de navegación entre programas -->
@if(isset($programaAnterior) && $programaAnterior)
<a href="{{ route('programas.show', $programaAnterior->id) }}"
   class="btn-nav-flotante btn-nav-anterior"
   title="Programa anterior">
    <i class="fas fa-chevron-left"></i>
    <span class="btn-nav-label">Anterior</span>
</a>
@else
<button type="button" class="btn-nav-flotante btn-nav-anterior disabled" disabled title="No hay programa anterior">
    <i class="fas fa-chevron-left"></i>
    <span class="btn-nav-label">Anterior</span>
</button>
@endif

@if(isset($programaPosterior) && $programaPosterior)
<a href="{{ route('programas.show', $programaPosterior->id) }}"
   class="btn-nav-flotante btn-nav-posterior"
   title="Programa posterior">
    <span class="btn-nav-label">Posterior</span>
    <i class="fas fa-chevron-right"></i>
</a>
@else
<button type="button" class="btn-nav-flotante btn-nav-posterior disabled" disabled title="No hay programa posterior">
    <span class="btn-nav-label">Posterior</span>
    <i class="fas fa-chevron-right"></i>
</button>
@endif

<style>
.btn-nav-flotante {
    position: fixed;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1050;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 12px 14px;
    border-radius: 8px;
    background: #0d6efd;
    color: #fff;
    border: none;
    box-shadow: 0 4px 16px rgba(13,110,253,0.35);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    transition: background 0.2s, box-shadow 0.2s, max-width 0.3s ease;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    max-width: 46px;
}
.btn-nav-flotante:hover {
    background: #0b5ed7;
    color: #fff;
    box-shadow: 0 6px 22px rgba(13,110,253,0.5);
    max-width: 160px;
    transform: translateY(-50%);
}
.btn-nav-flotante.disabled {
    background: #adb5bd;
    box-shadow: none;
    cursor: not-allowed;
    pointer-events: none;
}
.btn-nav-flotante .btn-nav-label {
    display: inline-block;
    max-width: 0;
    overflow: hidden;
    white-space: nowrap;
    transition: max-width 0.3s ease, opacity 0.3s ease;
    opacity: 0;
}
.btn-nav-flotante:hover .btn-nav-label {
    max-width: 120px;
    opacity: 1;
}
.btn-nav-anterior {
    left: 8px;
}
.btn-nav-posterior {
    right: 8px;
}
</style>

@endif
@push('scripts')
@vite(['resources/js/programas-show.js'])
<script>
$(document).ready(function() {
    initProgramasShow({{ $programa->id }});
});
</script>
@endpush
@endsection