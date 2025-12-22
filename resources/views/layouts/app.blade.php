<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Script inline para evitar flash de tema (DEBE ir primero) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('app-theme');
            let theme = 'light';
            
            if (savedTheme) {
                // Si hay tema guardado, usarlo
                theme = savedTheme;
            } else {
                // Si no hay tema guardado, detectar preferencia del sistema
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    theme = 'dark';
                }
            }
            
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('css/fontawesome/all.min.css') }}">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('css/datatables/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/datatables/responsive.bootstrap5.min.css') }}">
    
    <!-- Select2 CSS -->
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet" />

    <!-- Dark Mode CSS -->
    <link href="{{ asset('css/dark-mode.css') }}" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <!-- Dark Mode Script (debe cargarse temprano para evitar flash) -->
    <script src="{{ asset('js/dark-mode.js') }}"></script>
    
    <!-- Estilos para el menú lateral -->
    <style>
        .sidebar {
            min-height: 100vh;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px;
            z-index: 1000;
            transition: left 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar.show {
            left: 0;
        }
        
        .sidebar-header {
            padding: 1rem;
            background: #343a40;
            color: white;
            border-bottom: 1px solid #dee2e6;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            border-bottom: 1px solid #dee2e6;
        }
        
        .sidebar-menu .nav-category {
            background: #e9ecef;
            color: #495057;
            font-weight: bold;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            text-transform: uppercase;
        }
        
        .sidebar-menu .nav-category.accordion-toggle {
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu .nav-category.accordion-toggle:hover {
            background: #dee2e6;
        }
        
        .sidebar-menu .nav-category.accordion-toggle .accordion-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
        }
        
        .sidebar-menu .nav-category.accordion-toggle.collapsed .accordion-icon {
            transform: translateY(-50%) rotate(-90deg);
        }
        
        .sidebar-menu .nav-link {
            display: block;
            padding: 0.75rem 1rem;
            color: #495057;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
        }
        
        .sidebar-menu .nav-link:hover {
            background: #e9ecef;
            color: #007bff;
            text-decoration: none;
        }
        
        .sidebar-menu .nav-link.active {
            background: #007bff;
            color: white;
        }
        
        .sidebar-menu .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 0.5rem;
        }
        
        .accordion-content {
            display: none;
        }
        
        .accordion-content.show {
            display: block;
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
        
        .main-content {
            transition: margin-left 0.3s ease;
        }
        
        .navbar-toggler-sidebar {
            border: none;
            background: transparent;
            font-size: 1.25rem;
            color: #495057;
            margin-right: 1rem;
        }
        
        /* Estilos para navbar-brand responsivo */
        @media (max-width: 768px) {
            .navbar-brand {
                display: flex;
                flex-direction: column;
                align-items: flex-start !important;
                line-height: 1.3;
            }
            
            .navbar-brand small,
            .navbar-brand .badge {
                display: block;
                margin-left: 0 !important;
                margin-top: 0.25rem;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 280px;
                left: -280px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div id="app">
        <!-- Overlay para dispositivos móviles -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Menú lateral -->
        @auth
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h5 class="mb-0">
                    <i class="fas fa-landmark me-2"></i>
                    {{ config('app.name', 'Laravel') }}
                </h5>
                @php
                    $congregacion = Auth::user()->congregacion()->first();
                @endphp
                @if(Auth::user() && Auth::user()->congregacion)
                    <small class="text-light">{{ $congregacion->nombre ?? 'Sin Congregación' }}</small>
                @endif
            </div>
            
            <ul class="sidebar-menu">
                <li class="nav-category">
                    <i class="fas fa-home me-2"></i>Inicio
                </li>
                <li>
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="fas fa-dashboard"></i>Dashboard
                    </a>
                </li>
                
                @can('can.access.admin.menu')
                <li class="nav-category">
                    <i class="fas fa-tools me-2"></i>ADMINISTRACIÓN
                </li>
                <li>
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>Usuarios
                    </a>
                </li>
                @if(auth()->user()->isCoordinator() || auth()->user()->isSubcoordinator() || auth()->user()->isSecretary() || auth()->user()->isSubsecretary() || auth()->user()->isOrganizer() || auth()->user()->isSuborganizer())
                <li>
                    <a href="{{ route('grupos.index') }}" class="nav-link {{ request()->routeIs('grupos.*') ? 'active' : '' }}">
                        <i class="fas fa-users-cog"></i>Grupos
                    </a>
                </li>
                @endif
                @if(auth()->user()->isCoordinator() || auth()->user()->isOrganizer() || auth()->user()->isSubsecretary() || auth()->user()->isSuborganizer())
                <li>
                    <a href="{{ route('programas.index') }}" class="nav-link {{ request()->routeIs('programas.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar"></i>Programas
                    </a>
                </li>
                @endif
                @if(auth()->user()->isCoordinator() || auth()->user()->isSubcoordinator() || auth()->user()->isSecretary() || auth()->user()->isSubsecretary() || auth()->user()->isOrganizer() || auth()->user()->isSuborganizer())
                <li>
                    <a href="{{ route('informes.index') }}" class="nav-link {{ request()->routeIs('informes.*') ? 'active' : '' }}">
                        <i class="fas fa-id-card"></i>Informes
                    </a>
                </li>
                @endif
                @endcan
                
                <!-- Administración para otros perfiles que no tienen acceso al menú de administración principal -->
                @if(auth()->user()->canAccessPeopleManagementMenu() && !auth()->user()->canAccessAdminMenu())
                <li class="nav-category">
                    <i class="fas fa-tools me-2"></i>ADMINISTRACIÓN
                </li>
                <li>
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>Usuarios
                    </a>
                </li>
                @if(auth()->user()->isCoordinator() || auth()->user()->isSubcoordinator() || auth()->user()->isSecretary() || auth()->user()->isSubsecretary() || auth()->user()->isOrganizer() || auth()->user()->isSuborganizer())
                <li>
                    <a href="{{ route('grupos.index') }}" class="nav-link {{ request()->routeIs('grupos.*') ? 'active' : '' }}">
                        <i class="fas fa-users-cog"></i>Grupos
                    </a>
                </li>
                @endif
                @if(auth()->user()->isCoordinator() || auth()->user()->isOrganizer() || auth()->user()->isSubsecretary() || auth()->user()->isSuborganizer())
                <li>
                    <a href="{{ route('programas.index') }}" class="nav-link {{ request()->routeIs('programas.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar"></i>Programas
                    </a>
                </li>
                @endif
                @if(auth()->user()->isCoordinator() || auth()->user()->isSubcoordinator() || auth()->user()->isSecretary() || auth()->user()->isSubsecretary() || auth()->user()->isOrganizer() || auth()->user()->isSuborganizer())
                <li>
                    <a href="{{ route('informes.index') }}" class="nav-link {{ request()->routeIs('informes.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>Informes
                    </a>
                </li>
                @endif
                @endif
                
                @can('can.access.admin.menu')
                @if(auth()->user()->isSupervisor())
                <li class="nav-category accordion-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#config-supervisor-menu" aria-expanded="false">
                    <i class="fas fa-cogs me-2"></i>CONFIGURACIÓN
                    <i class="fas fa-chevron-down accordion-icon"></i>
                </li>
                <div class="collapse accordion-content" id="config-supervisor-menu">
                    <li>
                        <a href="{{ route('perfiles.index') }}" class="nav-link {{ request()->routeIs('perfiles.*') ? 'active' : '' }}">
                            <i class="fas fa-user-tag"></i>Perfiles
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('nombramiento.index') }}" class="nav-link {{ request()->routeIs('nombramiento.*') ? 'active' : '' }}">
                            <i class="fas fa-award"></i>Nombramientos
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('asignaciones.index') }}" class="nav-link {{ request()->routeIs('asignaciones.*') ? 'active' : '' }}">
                            <i class="fas fa-tasks"></i>Asignaciones
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('servicios.index') }}" class="nav-link {{ request()->routeIs('servicios.*') ? 'active' : '' }}">
                            <i class="fas fa-briefcase"></i>Servicios
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('congregaciones.index') }}" class="nav-link {{ request()->routeIs('congregaciones.*') ? 'active' : '' }}">
                            <i class="fas fa-landmark"></i>Congregaciones
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('grupos.index') }}" class="nav-link {{ request()->routeIs('grupos.*') ? 'active' : '' }}">
                            <i class="fas fa-users-cog"></i>Grupos
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('estados-espirituales.index') }}" class="nav-link {{ request()->routeIs('estados-espirituales.*') ? 'active' : '' }}">
                            <i class="fas fa-heart"></i>Estados Espirituales
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('canciones.index') }}" class="nav-link {{ request()->routeIs('canciones.*') ? 'active' : '' }}">
                            <i class="fas fa-music"></i>Canciones
                        </a>
                    </li>
                </div>
                @endif
                @endcan
                
                @can('admin')
                <li class="nav-category accordion-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#config-admin-menu" aria-expanded="false">
                    <i class="fas fa-cogs me-2"></i>CONFIGURACIÓN
                    <i class="fas fa-chevron-down accordion-icon"></i>
                </li>
                <div class="collapse accordion-content" id="config-admin-menu">
                    <li>
                        <a href="{{ route('perfiles.index') }}" class="nav-link {{ request()->routeIs('perfiles.*') ? 'active' : '' }}">
                            <i class="fas fa-user-tag"></i>Perfiles
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('nombramiento.index') }}" class="nav-link {{ request()->routeIs('nombramiento.*') ? 'active' : '' }}">
                            <i class="fas fa-award"></i>Nombramientos
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('asignaciones.index') }}" class="nav-link {{ request()->routeIs('asignaciones.*') ? 'active' : '' }}">
                            <i class="fas fa-tasks"></i>Asignaciones
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('servicios.index') }}" class="nav-link {{ request()->routeIs('servicios.*') ? 'active' : '' }}">
                            <i class="fas fa-briefcase"></i>Servicios
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('congregaciones.index') }}" class="nav-link {{ request()->routeIs('congregaciones.*') ? 'active' : '' }}">
                            <i class="fas fa-landmark"></i>Congregaciones
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('grupos.index') }}" class="nav-link {{ request()->routeIs('grupos.*') ? 'active' : '' }}">
                            <i class="fas fa-users-cog"></i>Grupos
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('estados-espirituales.index') }}" class="nav-link {{ request()->routeIs('estados-espirituales.*') ? 'active' : '' }}">
                            <i class="fas fa-heart"></i>Estados Espirituales
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('canciones.index') }}" class="nav-link {{ request()->routeIs('canciones.*') ? 'active' : '' }}">
                            <i class="fas fa-music"></i>Canciones
                        </a>
<li>
                        <a href="{{ route('secciones-reunion.index') }}" class="nav-link {{ request()->routeIs('secciones-reunion.*') ? 'active' : '' }}">
                            <i class="fas fa-list-alt"></i>Secciones Reunión
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('partes-seccion.index') }}" class="nav-link {{ request()->routeIs('partes-seccion.*') ? 'active' : '' }}">
                            <i class="fas fa-list-ol"></i>Partes Sección
                        </a>
                    </li>
                </div>
                @endcan
                
                
                <li class="nav-category">
                    <i class="fas fa-user me-2"></i>Mi Cuenta
                </li>
                <li>
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#misDatosModal">
                        <i class="fas fa-user-edit"></i>Mis Datos
                    </a>
                </li>
                @if(auth()->user()->isCoordinator() || auth()->user()->isSecretary())
                <li>
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#miCongregacionModal">
                        <i class="fas fa-landmark"></i>Mi Congregación
                    </a>
                </li>
                @endif
                <li>
                    <a href="{{ route('logout') }}" class="nav-link"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>Salir
                    </a>
                </li>
            </ul>
        </div>
        @endauth
        <!-- Barra de navegación superior -->
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                @auth
                <button class="navbar-toggler-sidebar" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                @endauth
                <a class="navbar-brand" href="{{ url('/') }}">
                    @guest
                        {{ config('app.name', 'Laravel') }}
                    @else
                        {{ Auth::user()->name }}
                        <small class="text-muted ms-2">({{ Auth::user()->role_name }})</small>
                        @if(Auth::user() && Auth::user()->congregacion)
                            <span class="badge bg-primary ms-2">{{ $congregacion->nombre }}</span>
                        @endif
                    @endguest
                </a>
                
                <!-- Botón de cambio de tema -->
                <button class="theme-toggle-btn ms-auto" type="button" aria-label="Cambiar tema">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </nav>

        <!-- Formulario de logout oculto -->
        @auth
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
        @endauth

        <div class="main-content">
            <main class="py-4">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Mis Datos -->
    <div class="modal fade" id="misDatosModal" tabindex="-1" aria-labelledby="misDatosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="misDatosModalLabel">
                        <i class="fas fa-user-edit me-2"></i>Mis Datos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="misDatosForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <!-- Contenedor para alertas -->
                        <div id="misDatos-alert-container"></div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mis_datos_name" class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="mis_datos_name" name="name" value="{{ Auth::user()->name ?? '' }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mis_datos_email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="mis_datos_email" name="email" value="{{ Auth::user()->email ?? '' }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <h6 class="mb-3">Cambiar Contraseña (opcional)</h6>
                                <p class="text-muted small">Deje los campos en blanco si no desea cambiar la contraseña</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mis_datos_password" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="mis_datos_password" name="password">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Mínimo 8 caracteres</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mis_datos_password_confirmation" class="form-label">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="mis_datos_password_confirmation" name="password_confirmation">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="updateMisDatosBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                            Actualizar Datos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Mi Congregación -->
    <div class="modal fade" id="miCongregacionModal" tabindex="-1" aria-labelledby="miCongregacionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="miCongregacionModalLabel">
                        <i class="fas fa-landmark me-2"></i>Mi Congregación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="miCongregacionForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <!-- Contenedor para alertas -->
                        <div id="miCongregacion-alert-container"></div>
                        
                        @if(Auth::user() && Auth::user()->congregacion)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="congregacion_nombre" class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="congregacion_nombre" name="nombre" value="{{ $congregacion->nombre }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="congregacion_codigo" class="form-label">Código</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="congregacion_codigo" name="codigo" value="{{ $congregacion->codigo }}" readonly>
                                        <button type="button" class="btn btn-outline-secondary" id="btn_refresh_codigo" title="Generar código aleatorio">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Código único de 64 caracteres</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="congregacion_telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="congregacion_telefono" name="telefono" value="{{ $congregacion->telefono }}" >
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="congregacion_persona_contacto" class="form-label">Persona de Contacto</label>
                                    <input type="text" class="form-control" id="congregacion_persona_contacto" name="persona_contacto" value="{{ $congregacion->persona_contacto }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="congregacion_direccion" class="form-label">Dirección</label>
                                    <textarea class="form-control" id="congregacion_direccion" name="direccion" rows="3">{{ $congregacion->direccion }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No tienes una congregación asignada.
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        @if(Auth::user() && Auth::user()->congregacion)
                        <button type="submit" class="btn btn-primary" id="updateCongregacionBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                            Actualizar Congregación
                        </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    
    <!-- DataTables JS -->
    <script src="{{ asset('js/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('js/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/datatables/responsive.bootstrap5.min.js') }}"></script>
    
    <!-- Select2 JS -->
    <script src="{{ asset('js/select2.min.js') }}"></script>
<!-- Configuración global de Select2 en español -->
    <script>
    $(document).ready(function() {
        // Configurar Select2 globalmente para usar español
        $.fn.select2.defaults.set('language', {
            noResults: function() {
                return 'No se encontraron resultados';
            },
            searching: function() {
                return 'Buscando...';
            },
            loadingMore: function() {
                return 'Cargando más resultados...';
            },
            maximumSelected: function(args) {
                return 'Solo puedes seleccionar ' + args.maximum + ' elemento' + (args.maximum == 1 ? '' : 's');
            },
            inputTooLong: function(args) {
                return 'Por favor, elimina ' + (args.input.length - args.maximum) + ' carácter' + (args.input.length - args.maximum == 1 ? '' : 'es');
            },
            inputTooShort: function(args) {
                return 'Por favor, ingresa ' + (args.minimum - args.input.length) + ' carácter' + (args.minimum - args.input.length == 1 ? '' : 'es') + ' o más';
            },
            errorLoading: function() {
                return 'No se pudieron cargar los resultados';
            }
        });
    });
    </script>

    @yield('scripts')
    @stack('scripts')
    
    <!-- JavaScript para Modal Mis Datos -->
    <script>
    $(document).ready(function() {
        // Configuración global de CSRF para todas las peticiones AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Función para mostrar alertas en el modal
        function showMisDatosAlert(type, message) {
            const alertContainer = $('#misDatos-alert-container');
            const alert = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            alertContainer.html(alert);
            
            if (type === 'success') {
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }
        }

        // Función para limpiar errores de validación
        function clearMisDatosValidationErrors() {
            $('#misDatosForm .form-control').removeClass('is-invalid');
            $('#misDatosForm .invalid-feedback').text('');
        }

        // Función para mostrar errores de validación
        function showMisDatosValidationErrors(errors) {
            clearMisDatosValidationErrors();
            $.each(errors, function(field, messages) {
                const input = $(`#misDatosForm [name="${field}"]`);
                input.addClass('is-invalid');
                input.siblings('.invalid-feedback').text(messages[0]);
            });
        }

        // Manejar el envío del formulario Mis Datos
        $('#misDatosForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = $('#updateMisDatosBtn');
            const spinner = submitBtn.find('.spinner-border');
            
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');
            clearMisDatosValidationErrors();
            
            // Limpiar alertas previas
            $('#misDatos-alert-container').empty();
            
            $.ajax({
                url: '/profile/update',
                method: 'PUT',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        showMisDatosAlert('success', response.message);
                        
                        // Actualizar los valores en el formulario con los nuevos datos
                        if (response.user) {
                            $('#mis_datos_name').val(response.user.name);
                            $('#mis_datos_email').val(response.user.email);
                            
                            // Actualizar el nombre en el navbar
                            $('.navbar .dropdown-toggle').contents().filter(function() {
                                return this.nodeType === 3; // Text node
                            }).first().replaceWith(' ' + response.user.name + ' ');
                        }
                        
                        // Limpiar campos de contraseña
                        $('#mis_datos_password').val('');
                        $('#mis_datos_password_confirmation').val('');
                        
                        // Cerrar el modal y refrescar la página después de un breve retraso
                        setTimeout(function() {
                            $('#misDatosModal').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    
                    if (xhr.status === 422 && response.errors) {
                        showMisDatosValidationErrors(response.errors);
                    } else {
                        const message = response.message || 'Error al actualizar los datos. Intente nuevamente.';
                        showMisDatosAlert('danger', message);
                    }
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                }
            });
        });

        // Limpiar el modal cuando se cierre
        $('#misDatosModal').on('hidden.bs.modal', function() {
            clearMisDatosValidationErrors();
            $('#misDatos-alert-container').empty();
            $('#updateMisDatosBtn .spinner-border').addClass('d-none');
            $('#updateMisDatosBtn').prop('disabled', false);
            
            // Restaurar valores originales si no se guardaron cambios
            $('#mis_datos_name').val('{{ Auth::user()->name ?? '' }}');
            $('#mis_datos_email').val('{{ Auth::user()->email ?? '' }}');
            $('#mis_datos_password').val('');
            $('#mis_datos_password_confirmation').val('');
        });

        // JavaScript para el menú lateral
        const sidebar = $('#sidebar');
        const sidebarToggle = $('#sidebarToggle');
        const sidebarOverlay = $('#sidebarOverlay');
        const mainContent = $('.main-content');

        // Toggle del menú lateral
        sidebarToggle.on('click', function() {
            sidebar.toggleClass('show');
            
            if (sidebar.hasClass('show')) {
                sidebarOverlay.fadeIn(300);
                
                // En desktop, ajustar el margen del contenido principal
                if ($(window).width() > 768) {
                    mainContent.css('margin-left', '250px');
                }
            } else {
                sidebarOverlay.fadeOut(300);
                mainContent.css('margin-left', '0');
            }
        });

        // Cerrar menú al hacer clic en el overlay
        sidebarOverlay.on('click', function() {
            sidebar.removeClass('show');
            sidebarOverlay.fadeOut(300);
            mainContent.css('margin-left', '0');
        });

        // Cerrar menú en móviles al hacer clic en un enlace
        $('.sidebar-menu .nav-link').on('click', function() {
            if ($(window).width() <= 768) {
                sidebar.removeClass('show');
                sidebarOverlay.fadeOut(300);
            }
        });

        // JavaScript para el acordeón de configuración
        $('.accordion-toggle').on('click', function(e) {
            e.preventDefault();
            
            const target = $(this).attr('data-bs-target');
            const targetElement = $(target);
            const icon = $(this).find('.accordion-icon');
            
            // Toggle del acordeón con animación suave
            targetElement.slideToggle(300);
            
            // Toggle de las clases y atributos
            $(this).toggleClass('collapsed');
            
            // Actualizar el ícono y atributos
            if ($(this).hasClass('collapsed')) {
                $(this).attr('aria-expanded', 'false');
                icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            } else {
                $(this).attr('aria-expanded', 'true');
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            }
        });

        // Ajustar el comportamiento al redimensionar la ventana
        $(window).on('resize', function() {
            if ($(window).width() <= 768) {
                // En móviles, siempre ocultar y quitar márgenes
                sidebar.removeClass('show');
                sidebarOverlay.hide();
                mainContent.css('margin-left', '0');
            } else {
                // En desktop, mantener el estado del menú
                if (sidebar.hasClass('show')) {
                    mainContent.css('margin-left', '250px');
                } else {
                    mainContent.css('margin-left', '0');
                }
                sidebarOverlay.hide();
            }
        });

        // Cerrar menú con tecla Escape
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.hasClass('show')) {
                sidebar.removeClass('show');
                sidebarOverlay.fadeOut(300);
                mainContent.css('margin-left', '0');
            }
        });

        // Función para mostrar alertas en el modal Mi Congregación
        function showMiCongregacionAlert(type, message) {
            const alertContainer = $('#miCongregacion-alert-container');
            const alert = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            alertContainer.html(alert);
            
            if (type === 'success') {
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }
        }

        // Función para limpiar errores de validación Mi Congregación
        function clearMiCongregacionValidationErrors() {
            $('#miCongregacionForm .form-control').removeClass('is-invalid');
            $('#miCongregacionForm .invalid-feedback').text('');
        }

        // Función para mostrar errores de validación Mi Congregación
        function showMiCongregacionValidationErrors(errors) {
            clearMiCongregacionValidationErrors();
            $.each(errors, function(field, messages) {
                const input = $(`#miCongregacionForm [name="${field}"]`);
                input.addClass('is-invalid');
                input.siblings('.invalid-feedback').text(messages[0]);
            });
        }

        // Manejar el botón de refrescar código
        $('#btn_refresh_codigo').on('click', function(e) {
            e.preventDefault();
            const btn = $(this);
            const icon = btn.find('i');
            
            // Deshabilitar el botón y rotar el icono
            btn.prop('disabled', true);
            icon.addClass('fa-spin');
            
            $.ajax({
                url: '/congregacion/generar-codigo',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#congregacion_codigo').val(response.codigo);
                        showMiCongregacionAlert('success', 'Código generado exitosamente.');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    const message = response.message || 'Error al generar el código. Intente nuevamente.';
                    showMiCongregacionAlert('danger', message);
                },
                complete: function() {
                    btn.prop('disabled', false);
                    icon.removeClass('fa-spin');
                }
            });
        });

        // Manejar el envío del formulario Mi Congregación
        $('#miCongregacionForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = $('#updateCongregacionBtn');
            const spinner = submitBtn.find('.spinner-border');
            
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');
            clearMiCongregacionValidationErrors();
            
            // Limpiar alertas previas
            $('#miCongregacion-alert-container').empty();
            
            $.ajax({
                url: '/congregacion/update',
                method: 'PUT',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        showMiCongregacionAlert('success', response.message);
                        
                        // Actualizar los valores en el formulario con los nuevos datos
                        if (response.congregacion) {
                            $('#congregacion_nombre').val(response.congregacion.nombre);
                            $('#congregacion_direccion').val(response.congregacion.direccion);
                            $('#congregacion_telefono').val(response.congregacion.telefono);
                            $('#congregacion_persona_contacto').val(response.congregacion.persona_contacto);
                            $('#congregacion_codigo').val(response.congregacion.codigo);
                        }
                        
                        // Cerrar el modal y refrescar la página después de un breve retraso
                        setTimeout(function() {
                            $('#miCongregacionModal').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    
                    if (xhr.status === 422 && response.errors) {
                        showMiCongregacionValidationErrors(response.errors);
                    } else {
                        const message = response.message || 'Error al actualizar la congregación. Intente nuevamente.';
                        showMiCongregacionAlert('danger', message);
                    }
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                }
            });
        });

        // Limpiar el modal Mi Congregación cuando se cierre
        $('#miCongregacionModal').on('hidden.bs.modal', function() {
            clearMiCongregacionValidationErrors();
            $('#miCongregacion-alert-container').empty();
            $('#updateCongregacionBtn .spinner-border').addClass('d-none');
            $('#updateCongregacionBtn').prop('disabled', false);
            
            // Restaurar valores originales si no se guardaron cambios
            @if(Auth::user() && Auth::user()->congregacion)
            $('#congregacion_nombre').val('{{ $congregacion->nombre ?? '' }}');
            $('#congregacion_direccion').val('{{ $congregacion->direccion ?? '' }}');
            $('#congregacion_telefono').val('{{ $congregacion->telefono ?? '' }}');
            $('#congregacion_persona_contacto').val('{{ $congregacion->persona_contacto ?? '' }}');
            $('#congregacion_codigo').val('{{ $congregacion->codigo ?? '' }}');
            @endif
        });

    });
    </script>
</body>
</html>
