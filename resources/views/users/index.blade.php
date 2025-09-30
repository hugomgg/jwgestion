@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>Gestión de Usuarios
                            </h5>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                <!-- Botón de Búsqueda Avanzada -->
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearchCollapse" aria-expanded="false" aria-controls="advancedSearchCollapse">
                                    <i class="fas fa-search me-2"></i>Búsqueda Avanzada
                                </button>
                                
                                @if(Auth::user()->isOrganizer() || Auth::user()->isSuborganizer() || Auth::user()->isCoordinator())
                                <div class="d-flex align-items-center">
                                    <label for="asignacionFilter" class="form-label me-2 mb-0">Asignación:</label>
                                    <select class="form-select" id="asignacionFilter" style="width: auto;">
                                        <option value="">Todas</option>
                                        @if(isset($asignaciones) && $asignaciones->count() > 0)
                                            @foreach($asignaciones as $asignacion)
                                                <option value="{{ $asignacion->abreviacion }}">{{ $asignacion->nombre }} ({{ $asignacion->abreviacion }})</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @endif
                                @if(Auth::user()->canModify() && !Auth::user()->isSubsecretary() && !Auth::user()->isSuborganizer())
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    <i class="fas fa-plus me-2"></i>Agregar Usuario
                                </button>
                                @endif
                                @if(Auth::user()->isAdmin())
                                <button type="button" class="btn btn-success" id="exportPdfBtn">
                                    <i class="fas fa-file-pdf me-2"></i>Exportar PDF
                                </button>
                                @endif
                            </div>
                            
                            <!-- Collapse para Búsqueda Avanzada -->
                            <div class="collapse mt-3" id="advancedSearchCollapse">
                                <div class="card card-body">
                                    <div class="row g-3">
                                        @if(!Auth::user()->isCoordinator() && !Auth::user()->isSubcoordinator() && !Auth::user()->isSecretary() && !Auth::user()->isSubsecretary() && !Auth::user()->isOrganizer() && !Auth::user()->isSuborganizer())
                                        <div class="col-md-4">
                                            <label for="congregacionFilter" class="form-label">Congregación:</label>
                                            <select class="form-select" id="congregacionFilter">
                                                <option value="">Todas</option>
                                                @foreach($congregaciones as $congregacion)
                                                    <option value="{{ $congregacion->nombre }}">{{ $congregacion->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                                        <div class="col-md-4">
                                            <label for="grupoFilter" class="form-label">Grupo:</label>
                                            <select class="form-select" id="grupoFilter">
                                                <option value="">Todos</option>
                                                @if(Auth::user()->isCoordinator() || Auth::user()->isSubcoordinator() || Auth::user()->isSecretary() || Auth::user()->isSubsecretary() || Auth::user()->isOrganizer() || Auth::user()->isSuborganizer())
                                                    @foreach($gruposParaFiltro as $grupo)
                                                        <option value="{{ $grupo->nombre }}">{{ $grupo->nombre }}</option>
                                                    @endforeach
                                                @else
                                                    @foreach($grupos as $grupo)
                                                        <option value="{{ $grupo->nombre }}">{{ $grupo->nombre }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="nombramientoFilter" class="form-label">Nombramiento:</label>
                                            <select class="form-select" id="nombramientoFilter">
                                                <option value="">Todos</option>
                                                @foreach($nombramientos as $nombramiento)
                                                    <option value="{{ $nombramiento->nombre }}">{{ $nombramiento->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="servicioFilter" class="form-label">Servicio:</label>
                                            <select class="form-select" id="servicioFilter">
                                                <option value="">Todos</option>
                                                @foreach($servicios as $servicio)
                                                    <option value="{{ $servicio->nombre }}">{{ $servicio->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="estadoEspiritualFilter" class="form-label">Estado Espiritual:</label>
                                            <select class="form-select" id="estadoEspiritualFilter">
                                                <option value="">Todos</option>
                                                @foreach($estadosEspirituales as $estadoEspiritual)
                                                    <option value="{{ $estadoEspiritual->nombre }}">{{ $estadoEspiritual->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="perfilFilter" class="form-label">Perfiles:</label>
                                            <select class="form-select" id="perfilFilter">
                                                <option value="">Todos</option>
                                                @foreach($perfiles as $perfil)
                                                    <option value="{{ $perfil->privilegio }}">{{ $perfil->privilegio }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="estadoFilter" class="form-label">Estado:</label>
                                            <select class="form-select" id="estadoFilter">
                                                <option value="">Todos</option>
                                                <option value="1">Activo</option>
                                                <option value="0">Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Mensajes de estado -->
                    <div id="alert-container"></div>
                    <div class="table-responsive">
                        <table id="usersTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Perfil</th>
                                    @if(!Auth::user()->isCoordinator() && !Auth::user()->isSubcoordinator() && !Auth::user()->isSecretary() && !Auth::user()->isSubsecretary() && !Auth::user()->isOrganizer() && !Auth::user()->isSuborganizer())
                                        <th>Congregación</th>
                                    @endif
                                    <th>Grupo</th>
                                    <th>Nombramiento</th>
                                    <th>Servicio</th>
                                    <th>Estado Espiritual</th>
                                    @if(Auth::user()->isOrganizer() || Auth::user()->isSuborganizer() || Auth::user()->isCoordinator())
                                        <th>Asignación</th>
                                    @endif
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr data-user-id="{{ $user->id }}">
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $user->privilegio_perfil }}</span>
                                    </td>
                                    @if(!Auth::user()->isCoordinator() && !Auth::user()->isSubcoordinator() && !Auth::user()->isSecretary() && !Auth::user()->isSubsecretary() && !Auth::user()->isOrganizer() && !Auth::user()->isSuborganizer())
                                        <td>
                                            <span class="badge bg-secondary">{{ $user->nombre_congregacion }}</span>
                                        </td>
                                    @endif
                                    <td>
                                        <span class="badge bg-dark">{{ $user->nombre_grupo }}</span>
                                    </td>
                                    <td>
                                        @if($user->nombre_nombramiento)
                                            <span class="badge bg-primary">{{ $user->nombre_nombramiento }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->nombre_servicio)
                                            <span class="badge bg-warning text-dark">{{ $user->nombre_servicio }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $user->nombre_estado_espiritual }}</span>
                                    </td>
                                    @if(Auth::user()->isOrganizer() || Auth::user()->isSuborganizer() || Auth::user()->isCoordinator())
                                        <td>
                                            @if($user->asignaciones && $user->asignaciones->count() > 0)
                                                @foreach($user->asignaciones as $asignacion)
                                                    <span class="badge bg-info me-1">{{ $asignacion->abreviacion }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td>
                                        @if($user->estado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info view-user"
                                                    data-user-id="{{ $user->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Ver usuario">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if(Auth::user()->canModify() && !Auth::user()->isSubsecretary() && !Auth::user()->isSuborganizer())
                                                <button type="button" class="btn btn-sm btn-warning edit-user"
                                                        data-user-id="{{ $user->id }}"
                                                        data-bs-toggle="tooltip"
                                                        title="Editar usuario">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Usuario -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">
                    <i class="fas fa-users me-2"></i>Agregar Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addUserForm">
                @csrf
                <div class="modal-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="addUserTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                                <i class="fas fa-user me-2"></i>Datos Generales
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="additional-tab" data-bs-toggle="tab" data-bs-target="#additional" type="button" role="tab" aria-controls="additional" aria-selected="false">
                                <i class="fas fa-info-circle me-2"></i>Datos Adicionales
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Tab panes -->
                    <div class="tab-content mt-3" id="addUserTabContent">
                        <!-- Datos Generales -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nombre *</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nombre_completo" class="form-label">Nombre Completo</label>
                                        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" maxlength="255">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="congregacion" class="form-label">Congregación *</label>
                                        <select class="form-select" id="congregacion" name="congregacion" required>
                                            <option value="">Seleccionar congregación...</option>
                                            @foreach($congregaciones as $congregacion)
                                                <option value="{{ $congregacion->id }}">{{ $congregacion->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="perfil" class="form-label">Perfil *</label>
                                        <select class="form-select" id="perfil" name="perfil" required>
                                            <option value="">Seleccionar perfil...</option>
                                            @foreach($perfilesModal as $perfil)
                                                <option value="{{ $perfil->id }}">{{ $perfil->privilegio }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="esperanza" class="form-label">Esperanza *</label>
                                        <select class="form-select" id="esperanza" name="esperanza" required>
                                            <option value="">Seleccionar esperanza...</option>
                                            @foreach($esperanzas as $esperanza)
                                                <option value="{{ $esperanza->id }}">{{ $esperanza->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="grupo" class="form-label">Grupo *</label>
                                        <select class="form-select" id="grupo" name="grupo" required>
                                            <option value="">Seleccionar grupo...</option>
                                            @foreach($grupos as $grupo)
                                                <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="estado_espiritual" class="form-label">Estado Espiritual *</label>
                                        <select class="form-select" id="estado_espiritual" name="estado_espiritual" required>
                                            <option value="">Seleccionar estado espiritual...</option>
                                            @foreach($estadosEspirituales as $estadoEspiritual)
                                                <option value="{{ $estadoEspiritual->id }}">{{ $estadoEspiritual->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sexo" class="form-label">Sexo *</label>
                                        <select class="form-select" id="sexo" name="sexo" required>
                                            <option value="">Seleccionar sexo...</option>
                                            @foreach($sexos as $sexoItem)
                                                <option value="{{ $sexoItem->id }}">{{ $sexoItem->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="servicio" class="form-label">Servicio</label>
                                        <select class="form-select" id="servicio" name="servicio">
                                            <option value="">Seleccionar servicio...</option>
                                            @foreach($servicios as $servicio)
                                                <option value="{{ $servicio->id }}">{{ $servicio->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nombramiento" class="form-label">Nombramiento</label>
                                        <select class="form-select" id="nombramiento" name="nombramiento">
                                            <option value="">Seleccionar nombramiento...</option>
                                            @foreach($nombramientos as $nombramiento)
                                                <option value="{{ $nombramiento->id }}">{{ $nombramiento->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telefono" class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" id="telefono" name="telefono" maxlength="20">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="estado" class="form-label">Estado *</label>
                                        <select class="form-select" id="estado" name="estado" required>
                                            <option value="">Seleccionar estado...</option>
                                            <option value="1" selected>Activo</option>
                                            <option value="0">Inactivo</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datos Adicionales -->
                        <div class="tab-pane fade" id="additional" role="tabpanel" aria-labelledby="additional-tab">
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="asignaciones" class="form-label">Asignaciones (Opcional)</label>
                                        <select class="form-select" id="asignaciones" name="asignaciones[]" multiple>
                                            @if(isset($asignaciones) && $asignaciones->count() > 0)
                                                @foreach($asignaciones as $asignacion)
                                                    <option value="{{ $asignacion->id }}">{{ $asignacion->nombre }}</option>
                                                @endforeach
                                            @else
                                                <option disabled>No hay asignaciones disponibles</option>
                                            @endif
                                        </select>
                                        <div class="invalid-feedback"></div>
                                        <small class="form-text text-muted">Utiliza la búsqueda para encontrar y seleccionar múltiples asignaciones</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="persona_contacto" class="form-label">Persona de Contacto</label>
                                        <input type="text" class="form-control" id="persona_contacto" name="persona_contacto">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telefono_contacto" class="form-label">Teléfono de Contacto</label>
                                        <input type="text" class="form-control" id="telefono_contacto" name="telefono_contacto" maxlength="20">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha_bautismo" class="form-label">Fecha de Bautismo</label>
                                        <input type="date" class="form-control" id="fecha_bautismo" name="fecha_bautismo">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                        <div class="invalid-feedback"></div>
                                        <small class="form-text text-muted">Mínimo 8 caracteres (opcional)</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="observacion" class="form-label">Observación</label>
                                        <textarea class="form-control" id="observacion" name="observacion" rows="3" maxlength="1000"></textarea>
                                        <div class="invalid-feedback"></div>
                                        <small class="form-text text-muted">Máximo 1000 caracteres</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveUserBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">
                    <i class="fas fa-users me-2"></i>Editar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="modal-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="editUserTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="edit-general-tab" data-bs-toggle="tab" data-bs-target="#edit-general" type="button" role="tab" aria-controls="edit-general" aria-selected="true">
                                <i class="fas fa-user me-2"></i>Datos Generales
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="edit-additional-tab" data-bs-toggle="tab" data-bs-target="#edit-additional" type="button" role="tab" aria-controls="edit-additional" aria-selected="false">
                                <i class="fas fa-info-circle me-2"></i>Datos Adicionales
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Tab panes -->
                    <div class="tab-content mt-3" id="editUserTabContent">
                        <!-- Datos Generales -->
                        <div class="tab-pane fade show active" id="edit-general" role="tabpanel" aria-labelledby="edit-general-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_name" class="form-label">Nombre *</label>
                                        <input type="text" class="form-control" id="edit_name" name="name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_nombre_completo" class="form-label">Nombre Completo</label>
                                        <input type="text" class="form-control" id="edit_nombre_completo" name="nombre_completo" maxlength="255">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_congregacion" class="form-label">Congregación *</label>
                                        <select class="form-select" id="edit_congregacion" name="congregacion" required>
                                            <option value="">Seleccionar congregación...</option>
                                            @foreach($congregaciones as $congregacion)
                                                <option value="{{ $congregacion->id }}">{{ $congregacion->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="edit_email" name="email" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_perfil" class="form-label">Perfil *</label>
                                        <select class="form-select" id="edit_perfil" name="perfil" required>
                                            <option value="">Seleccionar perfil...</option>
                                            @foreach($perfilesModalEdit as $perfil)
                                                <option value="{{ $perfil->id }}">{{ $perfil->privilegio }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_esperanza" class="form-label">Esperanza *</label>
                                        <select class="form-select" id="edit_esperanza" name="esperanza" required>
                                            <option value="">Seleccionar esperanza...</option>
                                            @foreach($esperanzas as $esperanza)
                                                <option value="{{ $esperanza->id }}">{{ $esperanza->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_grupo" class="form-label">Grupo *</label>
                                        <select class="form-select" id="edit_grupo" name="grupo" required>
                                            <option value="">Seleccionar grupo...</option>
                                            @foreach($grupos as $grupo)
                                                <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_estado_espiritual" class="form-label">Estado Espiritual *</label>
                                        <select class="form-select" id="edit_estado_espiritual" name="estado_espiritual" required>
                                            <option value="">Seleccionar estado espiritual...</option>
                                            @foreach($estadosEspirituales as $estadoEspiritual)
                                                <option value="{{ $estadoEspiritual->id }}">{{ $estadoEspiritual->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_sexo" class="form-label">Sexo *</label>
                                        <select class="form-select" id="edit_sexo" name="sexo" required>
                                            <option value="">Seleccionar sexo...</option>
                                            @foreach($sexos as $sexoItem)
                                                <option value="{{ $sexoItem->id }}">{{ $sexoItem->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_servicio" class="form-label">Servicio</label>
                                        <select class="form-select" id="edit_servicio" name="servicio">
                                            <option value="">Seleccionar servicio...</option>
                                            @foreach($servicios as $servicio)
                                                <option value="{{ $servicio->id }}">{{ $servicio->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_nombramiento" class="form-label">Nombramiento</label>
                                        <select class="form-select" id="edit_nombramiento" name="nombramiento">
                                            <option value="">Seleccionar nombramiento...</option>
                                            @foreach($nombramientos as $nombramiento)
                                                <option value="{{ $nombramiento->id }}">{{ $nombramiento->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_telefono" class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" id="edit_telefono" name="telefono" maxlength="20">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_estado" class="form-label">Estado *</label>
                                        <select class="form-select" id="edit_estado" name="estado" required>
                                            <option value="">Seleccionar estado...</option>
                                            <option value="1">Activo</option>
                                            <option value="0">Inactivo</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datos Adicionales -->
                        <div class="tab-pane fade" id="edit-additional" role="tabpanel" aria-labelledby="edit-additional-tab">
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="edit_asignaciones" class="form-label">Asignaciones (Opcional)</label>
                                        <select class="form-select" id="edit_asignaciones" name="asignaciones[]" multiple>
                                            @if(isset($asignaciones) && $asignaciones->count() > 0)
                                                @foreach($asignaciones as $asignacion)
                                                    <option value="{{ $asignacion->id }}">{{ $asignacion->nombre }}</option>
                                                @endforeach
                                            @else
                                                <option disabled>No hay asignaciones disponibles</option>
                                            @endif
                                        </select>
                                        <div class="invalid-feedback"></div>
                                        <small class="form-text text-muted">Utiliza la búsqueda para encontrar y seleccionar múltiples asignaciones</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_persona_contacto" class="form-label">Persona de Contacto</label>
                                        <input type="text" class="form-control" id="edit_persona_contacto" name="persona_contacto">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_telefono_contacto" class="form-label">Teléfono de Contacto</label>
                                        <input type="text" class="form-control" id="edit_telefono_contacto" name="telefono_contacto" maxlength="20">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                        <input type="date" class="form-control" id="edit_fecha_nacimiento" name="fecha_nacimiento">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_fecha_bautismo" class="form-label">Fecha de Bautismo</label>
                                        <input type="date" class="form-control" id="edit_fecha_bautismo" name="fecha_bautismo">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_password" class="form-label">Nueva Contraseña</label>
                                        <input type="password" class="form-control" id="edit_password" name="password">
                                        <div class="invalid-feedback"></div>
                                        <small class="form-text text-muted">Dejar en blanco para mantener la actual</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                                        <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="edit_observacion" class="form-label">Observación</label>
                                        <textarea class="form-control" id="edit_observacion" name="observacion" rows="3" maxlength="1000"></textarea>
                                        <div class="invalid-feedback"></div>
                                        <small class="form-text text-muted">Máximo 1000 caracteres</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="updateUserBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Confirmar Eliminación -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar al usuario <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    Eliminar Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ver Usuario -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewUserModalLabel">
                    <i class="fas fa-eye me-2"></i>Detalles del Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" id="viewUserTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="view-general-tab" data-bs-toggle="tab" data-bs-target="#view-general" type="button" role="tab" aria-controls="view-general" aria-selected="true">
                            <i class="fas fa-user me-2"></i>Datos Generales
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="view-additional-tab" data-bs-toggle="tab" data-bs-target="#view-additional" type="button" role="tab" aria-controls="view-additional" aria-selected="false">
                            <i class="fas fa-info-circle me-2"></i>Datos Adicionales
                        </button>
                    </li>
                </ul>
                
                <!-- Tab panes -->
                <div class="tab-content mt-3" id="viewUserTabContent">
                    <!-- Datos Generales -->
                    <div class="tab-pane fade show active" id="view-general" role="tabpanel" aria-labelledby="view-general-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nombre:</label>
                                    <p class="form-control-plaintext" id="view_name">-</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nombre Completo:</label>
                                    <p class="form-control-plaintext" id="view_nombre_completo">-</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Congregación:</label>
                                    <p class="form-control-plaintext" id="view_congregacion">-</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email:</label>
                                    <p class="form-control-plaintext" id="view_email">-</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Perfil:</label>
                                    <p class="form-control-plaintext" id="view_perfil">-</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Esperanza:</label>
                                    <p class="form-control-plaintext" id="view_esperanza">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Grupo:</label>
                                    <p class="form-control-plaintext" id="view_grupo">-</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Estado Espiritual:</label>
                                    <p class="form-control-plaintext" id="view_estado_espiritual">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Sexo:</label>
                                    <p class="form-control-plaintext" id="view_sexo">-</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Servicio:</label>
                                    <p class="form-control-plaintext" id="view_servicio">-</p>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nombramiento:</label>
                                    <p class="form-control-plaintext" id="view_nombramiento">-</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Teléfono:</label>
                                    <p class="form-control-plaintext" id="view_telefono">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Estado:</label>
                                    <p class="form-control-plaintext" id="view_estado">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos Adicionales -->
                    <div class="tab-pane fade" id="view-additional" role="tabpanel" aria-labelledby="view-additional-tab">
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Asignaciones:</label>
                                    <div id="view_asignaciones">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Persona de Contacto:</label>
                                    <p class="form-control-plaintext" id="view_persona_contacto">-</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Teléfono de Contacto:</label>
                                    <p class="form-control-plaintext" id="view_telefono_contacto">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Fecha de Nacimiento:</label>
                                    <p class="form-control-plaintext" id="view_fecha_nacimiento">-</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Fecha de Bautismo:</label>
                                    <p class="form-control-plaintext" id="view_fecha_bautismo">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Observación:</label>
                                    <p class="form-control-plaintext" id="view_observacion">-</p>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-muted">Información de Auditoría</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Creado por:</label>
                                    <p class="form-control-plaintext small" id="view_creado_por">-</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Modificado por:</label>
                                    <p class="form-control-plaintext small" id="view_modificado_por">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Configuración para users-index.js
window.usersIndexConfig = {
    // Rutas
    storeRoute: '{{ route("users.store") }}',
    exportPdfRoute: '{{ route("users.export.pdf") }}',
    
    // Configuración de usuario
    @if(Auth::user()->isCoordinator() || Auth::user()->isSecretary() || Auth::user()->isOrganizer())
    userCongregacion: '{{ Auth::user()->congregacion }}',
    @else
    userCongregacion: null,
    @endif
    
    // Configuración de permisos y UI
    isLimitedUser: @json(!Auth::user()->isCoordinator() && !Auth::user()->isSubcoordinator() && !Auth::user()->isSecretary() && !Auth::user()->isSubsecretary() && !Auth::user()->isOrganizer() && !Auth::user()->isSuborganizer()),
    showAsignacionFilter: @json(Auth::user()->isCoordinator() || Auth::user()->isOrganizer() || Auth::user()->isSuborganizer()),
    
    // Configuración de DataTables
    datatablesColumnDefs: [
        @if(Auth::user()->isCoordinator() || Auth::user()->isSubcoordinator() || Auth::user()->isSecretary() || Auth::user()->isSubsecretary() || Auth::user()->isOrganizer() || Auth::user()->isSuborganizer())
            // Para coordinadores/subcoordinadores/secretarios/subsecretarios/organizadores/suborganizadores (sin columna Congregación)
            { responsivePriority: 1, targets: [1] }, // Nombre
            { responsivePriority: 2, targets: [0, 2, 3, 4, 5, 6] }, // ID, Perfil, Grupo, Nombramiento, Servicio, Estado Espiritual, Estado
            { orderable: false, targets: [7] } // Deshabilitar ordenamiento en columna Acciones
        @else
            // Para otros usuarios (con columna Congregación)
            { responsivePriority: 1, targets: [1] }, // Nombre
            { responsivePriority: 2, targets: [0, 2, 3, 4, 5, 6, 7] }, // ID, Perfil, Congregación, Grupo, Nombramiento, Servicio, Estado Espiritual, Estado
            { orderable: false, targets: [8] } // Deshabilitar ordenamiento en columna Acciones
        @endif
    ],
    
    // Índice de columna de estado
    @if(Auth::user()->isOrganizer() || Auth::user()->isSuborganizer())
        estadoColumnIndex: 8, // Para organizadores/suborganizadores: Estado está en columna 8 (sin columna Congregación, con columna Asignación)
    @elseif(Auth::user()->isCoordinator() || Auth::user()->isSubcoordinator() || Auth::user()->isSecretary() || Auth::user()->isSubsecretary())
        estadoColumnIndex: 7, // Para coordinadores/subcoordinadores/secretarios/subsecretarios: Estado está en columna 7 (sin columna Congregación, sin columna Asignación)
    @else
        estadoColumnIndex: 8, // Para otros usuarios: Estado está en columna 8 (con columna Congregación, sin columna Asignación)
    @endif
    
    // Datos para lookups
    congregaciones: @json($congregaciones->map(function($c) { return ['id' => $c->id, 'nombre' => $c->nombre]; })),
    perfiles: @json($perfiles->map(function($p) { return ['id' => $p->id, 'privilegio' => $p->privilegio]; })),
    sexos: @json($sexos->map(function($s) { return ['id' => $s->id, 'nombre' => $s->nombre]; })),
    servicios: @json($servicios->map(function($s) { return ['id' => $s->id, 'nombre' => $s->nombre]; })),
    nombramientos: @json($nombramientos->map(function($n) { return ['id' => $n->id, 'nombre' => $n->nombre]; })),
    esperanzas: @json($esperanzas->map(function($e) { return ['id' => $e->id, 'nombre' => $e->nombre]; })),
    grupos: @json($grupos->map(function($g) { return ['id' => $g->id, 'nombre' => $g->nombre]; })),
    estadosEspirituales: @json($estadosEspirituales->map(function($e) { return ['id' => $e->id, 'nombre' => $e->nombre]; })),
    asignaciones: @json(isset($asignaciones) ? $asignaciones->map(function($a) { return ['id' => $a->id, 'nombre' => $a->nombre]; }) : [])
};
</script>
<script src="{{ asset('js/users-index.js') }}"></script>
@endsection