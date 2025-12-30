@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-tachometer-alt me-2"></i>Panel de Opciones
                </h5>
            </div>

            <div class="card-body">
                <div class="row">
                    @if(Auth::user()->canAccessAdminMenu())
                        <div class="col-12 mt-3">
                            @if(Auth::user()->isAdmin())
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Bienvenido, {{ Auth::user()->name }}!</strong>
                                <span class="badge bg-primary ms-2">{{ Auth::user()->role_name }}</span>
                                <br><small>Como {{ Auth::user()->role_name }}, tienes acceso completo a todas las funcionalidades del sistema.</small>
                            </div>
                            @else
                            <div class="alert alert-warning">
                                <i class="fas fa-eye me-2"></i>
                                <strong>Bienvenido, {{ Auth::user()->name }}!</strong>
                                <span class="badge bg-warning ms-2">{{ Auth::user()->role_name }}</span>
                                <br><small>Como {{ Auth::user()->role_name }}, tienes acceso a la gestión o visualización de usuarios, grupos y/o informes y/o programas.</small>
                            </div>
                            @endif
                        </div>
                        <!-- Dashboard para Usuarios con Acceso al Menú -->
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                    <h5>Gestión de Usuarios</h5>
                                    <p class="text-muted">
                                        @if(Auth::user()->isAdmin())
                                            Crear, editar y administrar usuarios del sistema
                                        @else
                                            Ver y consultar listado de usuarios del sistema
                                        @endif
                                    </p>
                                    <a href="{{ route('users.index') }}" class="btn btn-primary">
                                        <i class="fas fa-arrow-right me-2"></i>Acceder
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-3x text-success mb-3"></i>
                                    <h5>Gestión de Informes de Predicación</h5>
                                    <p class="text-muted">
                                        @if(Auth::user()->isAdmin())
                                            Crear, editar y administrar informes de predicación
                                        @else
                                            Ver y consultar listado de informes de predicación
                                        @endif
                                    </p>
                                    <a href="{{ route('users.index') }}" class="btn btn-success">
                                        <i class="fas fa-arrow-right me-2"></i>Acceder
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-3x text-warning mb-3"></i>
                                    <h5>Gestión de Grupos de Predicación</h5>
                                    <p class="text-muted">
                                        @if(Auth::user()->isAdmin())
                                            Crear, editar y administrar informes de predicación
                                        @else
                                            Ver y consultar listado de informes de predicación
                                        @endif
                                    </p>
                                    <a href="{{ route('grupos.index') }}" class="btn btn-warning">
                                        <i class="fas fa-arrow-right me-2"></i>Acceder
                                    </a>
                                </div>
                            </div>
                        </div>
                        @if(Auth::user()->isCoordinator() || Auth::user()->isSubcoordinator() || Auth::user()->isOrganizer() || Auth::user()->isSuborganizer())
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-3x text-info mb-3"></i>
                                    <h5>Gestión de Programas de Reunión</h5>
                                    <p class="text-muted">
                                        @if(Auth::user()->isCoordinator() || Auth::user()->isOrganizer())
                                            Crear, editar y administrar informes de predicación
                                        @else
                                            Ver y consultar listado de informes de predicación
                                        @endif
                                    </p>
                                    <a href="{{ route('programas.index') }}" class="btn btn-info">
                                        <i class="fas fa-arrow-right me-2"></i>Acceder
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="col-12 mt-3">
                            <div class="alert alert-success">
                                <i class="fas fa-graduation-cap me-2"></i>
                                <strong>Bienvenido, {{ Auth::user()->name }}!</strong>
                                <span class="badge bg-success ms-2">{{ Auth::user()->role_name }}</span>
                                <br><small>Como {{ Auth::user()->role_name }}, accede a tus cursos y recursos de aprendizaje desde este panel.</small>
                            </div>
                        </div>
                        <!-- Dashboard para Estudiantes -->
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-book fa-3x text-info mb-3"></i>
                                    <h5>Mis Cursos</h5>
                                    <p class="text-muted">Accede a tus cursos y materiales</p>
                                    <button class="btn btn-info" disabled>
                                        <i class="fas fa-clock me-2"></i>Próximamente
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-tasks fa-3x text-warning mb-3"></i>
                                    <h5>Mis Tareas</h5>
                                    <p class="text-muted">Revisa tus tareas pendientes</p>
                                    <button class="btn btn-warning" disabled>
                                        <i class="fas fa-clock me-2"></i>Próximamente
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
