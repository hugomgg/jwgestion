@extends('layouts.app')

@section('content')
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
                            <div class="d-flex justify-content-end">
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
                                                    <th>Tiempo (min)</th>
                                                    <th>Parte</th>
                                                    <th>Encargado</th>
                                                    <th>Tema</th>
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

                    @if(Auth::user()->perfil == 3)
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
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" id="escuelaTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="escuela-sp-tab" data-bs-toggle="tab" data-bs-target="#escuela-sp" type="button" role="tab" aria-controls="escuela-sp" aria-selected="true">
                                                <i class="fas fa-chalkboard-teacher me-2"></i>Escuela (SP)
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="escuela-s1-tab" data-bs-toggle="tab" data-bs-target="#escuela-s1" type="button" role="tab" aria-controls="escuela-s1" aria-selected="false">
                                                <i class="fas fa-chalkboard-teacher me-2"></i>Escuela (S1)
                                            </button>
                                        </li>
                                    </ul>

                                    <!-- Tab content -->
                                    <div class="tab-content mt-3" id="escuelaTabsContent">
                                        <!-- Tab Escuela (SP) -->
                                        <div class="tab-pane fade show active" id="escuela-sp" role="tabpanel" aria-labelledby="escuela-sp-tab">
                                            <h6 class="mb-3">Asignaciones de Seamos Mejores Maestros (Sala Principal)</h6>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Tiempo (min)</th>
                                                            <th>Parte</th>
                                                            <th>Encargado</th>
                                                            <th>Ayudante</th>
                                                            <th>Lección</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="partesSegundaSeccionTableBody">
                                                        <!-- Los datos se cargan dinámicamente -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Tab Escuela (S1) -->
                                        <div class="tab-pane fade" id="escuela-s1" role="tabpanel" aria-labelledby="escuela-s1-tab">
                                            <h6 class="mb-3">Asignaciones de Seamos Mejores Maestros (Sala Auxiliar 1)</h6>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Tiempo (min)</th>
                                                            <th>Parte</th>
                                                            <th>Encargado</th>
                                                            <th>Ayudante</th>
                                                            <th>Lección</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="partesTerceraSeccionTableBody">
                                                        <!-- Los datos se cargan dinámicamente -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

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
                                                    <th>Tiempo (min)</th>
                                                    <th>Parte</th>
                                                    <th>Encargado</th>
                                                    <th>Tema</th>
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

@push('scripts')
<script src="{{ asset('js/programas-show.js') }}"></script>
<script>
$(document).ready(function() {
    initProgramasShow({{ $programa->id }});
});
</script>
@endpush
@endsection