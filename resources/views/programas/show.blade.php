@extends('layouts.app')

@section('content')
@if($currentUser->isCoordinator() || $currentUser->isOrganizer() || $currentUser->isSubsecretary() || $currentUser->isSuborganizer())
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

                                <!-- Botón Programa Anterior -->
                                @if(isset($programaAnterior) && $programaAnterior)
                                <a href="{{ route('programas.show', $programaAnterior->id) }}" 
                                   class="btn btn-outline-primary" 
                                   title="Programa anterior">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                @else
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        disabled 
                                        title="No hay programa anterior">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                @endif

                                <!-- Botón Programa Posterior -->
                                @if(isset($programaPosterior) && $programaPosterior)
                                <a href="{{ route('programas.show', $programaPosterior->id) }}" 
                                   class="btn btn-outline-primary" 
                                   title="Programa posterior">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                                @else
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        disabled 
                                        title="No hay programa posterior">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
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
@endif
@push('scripts')
<script src="{{ asset('js/programas-show.js') }}"></script>
<script>
$(document).ready(function() {
    initProgramasShow({{ $programa->id }});
});
</script>
@endpush
@endsection