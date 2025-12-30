@extends('layouts.app')
@push('styles')
@vite(['resources/css/grupos-index.css'])
@endpush
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="fas fa-users-cog me-2"></i>Gestión de Grupos
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end align-items-center gap-3">
                                <div class="d-flex align-items-center">
                                    <label for="estadoFilter" class="form-label me-2 mb-0">Estado:</label>
                                    <select class="form-select" id="estadoFilter" style="width: auto;">
                                        <option value="">Todos</option>
                                        <option value="1">Habilitado</option>
                                        <option value="0">Deshabilitado</option>
                                    </select>
                                </div>
                                @if(Auth::user()->canModify())
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGrupoModal">
                                    <i class="fas fa-plus me-2"></i>Agregar Grupo
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Mensajes de estado -->
                    <div id="alert-container"></div>
                    
                    <div class="table-responsive">
                        <table id="gruposTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Congregación</th>
                                    <th>Estado</th>
                                    <th>Usuarios Asignados</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán dinámicamente vía AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Grupo -->
<div class="modal fade" id="addGrupoModal" tabindex="-1" aria-labelledby="addGrupoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGrupoModalLabel">
                    <i class="fas fa-users-cog me-2"></i>Agregar Nuevo Grupo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addGrupoForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="congregacion_id" class="form-label">Congregación *</label>
                        <select class="form-select" id="congregacion_id" name="congregacion_id" required {{ count($congregaciones) == 1 ? 'readonly' : '' }} style="color: #000 !important;">
                            @if(count($congregaciones) == 0)
                                <option value="">No hay congregaciones disponibles</option>
                            @elseif(count($congregaciones) == 1)
                                @foreach($congregaciones as $congregacion)
                                    <option value="{{ $congregacion->id }}" selected>{{ $congregacion->nombre }}</option>
                                @endforeach
                            @else
                                <option value="">Seleccionar congregación...</option>
                                @foreach($congregaciones as $congregacion)
                                    <option value="{{ $congregacion->id }}">{{ $congregacion->nombre }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado *</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="">Seleccionar estado...</option>
                            <option value="1">Habilitado</option>
                            <option value="0">Deshabilitado</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="saveGrupoBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Guardar Grupo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Grupo -->
<div class="modal fade" id="editGrupoModal" tabindex="-1" aria-labelledby="editGrupoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editGrupoModalLabel">
                    <i class="fas fa-users-cog me-2"></i>Editar Grupo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editGrupoForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_grupo_id" name="grupo_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_congregacion_id" class="form-label">Congregación *</label>
                        <select class="form-select" id="edit_congregacion_id" name="congregacion_id" required {{ count($congregaciones) == 1 ? 'readonly' : '' }}>
                            @if(count($congregaciones) == 0)
                                <option value="">No hay congregaciones disponibles</option>
                            @elseif(count($congregaciones) == 1)
                                @foreach($congregaciones as $congregacion)
                                    <option value="{{ $congregacion->id }}">{{ $congregacion->nombre }}</option>
                                @endforeach
                            @else
                                <option value="">Seleccionar congregación...</option>
                                @foreach($congregaciones as $congregacion)
                                    <option value="{{ $congregacion->id }}">{{ $congregacion->nombre }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_estado" class="form-label">Estado *</label>
                        <select class="form-select" id="edit_estado" name="estado" required>
                            <option value="">Seleccionar estado...</option>
                            <option value="1">Habilitado</option>
                            <option value="0">Deshabilitado</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="updateGrupoBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Actualizar Grupo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Ver Grupo -->
<div class="modal fade" id="viewGrupoModal" tabindex="-1" aria-labelledby="viewGrupoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewGrupoModalLabel">
                    <i class="fas fa-layer-group me-2"></i>Detalles del Grupo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nombre:</strong>
                        <p id="view_grupo_nombre"></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Congregación:</strong>
                        <p id="view_grupo_congregacion"></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Estado:</strong>
                        <p id="view_grupo_estado"></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Usuarios Asignados:</strong>
                        <p id="view_grupo_usuarios"></p>
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
// Configuración para grupos-index.js
window.gruposIndexConfig = {
    // Rutas
    dataRoute: '{{ route("grupos.data") }}',
    storeRoute: '{{ route("grupos.store") }}',
    
    // CSRF Token
    csrfToken: '{{ csrf_token() }}',
    
    // Permisos
    canModify: @json(Auth::user()->canModify()),
    
    // Congregaciones disponibles
    congregacionesCount: {{ count($congregaciones) }}
};
</script>
<script>
$(document).ready(function() {
    // Si solo hay una congregación, deshabilitar el select visualmente
    if (window.gruposIndexConfig.congregacionesCount === 1) {
        $('#congregacion_id').css('background-color', '#e9ecef').css('pointer-events', 'none');
        $('#edit_congregacion_id').css('background-color', '#e9ecef').css('pointer-events', 'none');
    }
});
</script>
@vite(['resources/js/grupos-index.js'])
@endsection