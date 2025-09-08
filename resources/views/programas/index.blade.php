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
                                <i class="fas fa-calendar me-2"></i>Gestión de Programas
                            </h5>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramaModal">
                                    <i class="fas fa-plus me-2"></i>Nuevo Programa
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Contenedor para alertas -->
                    <div id="alert-container"></div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="programasTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Orador Inicial</th>
                                    <th>Presidencia</th>
                                    <th>Canción Inicial</th>
                                    <th>Canción Intermedia</th>
                                    <th>Canción Final</th>
                                    <th>Orador Final</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($programas as $programa)
                                <tr>
                                    <td data-order="{{ $programa->fecha }}">{{ \Carbon\Carbon::parse($programa->fecha)->format('d/m/Y') }}</td>
                                    <td>{{ $programa->nombre_orador_inicial ?? '-' }}</td>
                                    <td>{{ $programa->nombre_presidencia ?? '-' }}</td>
                                    <td>
                                        @if($programa->nombre_cancion_pre)
                                            @if(Auth::user()->perfil == 3)
                                                {{ $programa->numero_cancion_pre ?? '-' }}
                                            @else
                                                {{ $programa->numero_cancion_pre ? $programa->numero_cancion_pre . ' - ' : '' }}{{ $programa->nombre_cancion_pre }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($programa->nombre_cancion_en)
                                            @if(Auth::user()->perfil == 3)
                                                {{ $programa->numero_cancion_en ?? '-' }}
                                            @else
                                                {{ $programa->numero_cancion_en ? $programa->numero_cancion_en . ' - ' : '' }}{{ $programa->nombre_cancion_en }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($programa->nombre_cancion_post)
                                            @if(Auth::user()->perfil == 3)
                                                {{ $programa->numero_cancion_post ?? '-' }}
                                            @else
                                                {{ $programa->numero_cancion_post ? $programa->numero_cancion_post . ' - ' : '' }}{{ $programa->nombre_cancion_post }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $programa->nombre_orador_final ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $programa->estado ? 'bg-success' : 'bg-danger' }}">
                                            {{ $programa->estado ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('programas.edit', $programa->id) }}" class="btn btn-sm btn-warning"
                                               data-bs-toggle="tooltip"
                                               title="Editar programa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-programa"
                                                    data-id="{{ $programa->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Eliminar programa">
                                                <i class="fas fa-trash"></i>
                                            </button>
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

<!-- Modal Agregar Programa -->
<div class="modal fade" id="addProgramaModal" tabindex="-1" aria-labelledby="addProgramaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProgramaModalLabel">
                    <i class="fas fa-calendar me-2"></i>Agregar Programa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addProgramaForm">
                @csrf
                <div class="modal-body">
                    <!-- Contenedor para alertas -->
                    <div id="add-alert-container"></div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_fecha" class="form-label">Fecha *</label>
                                <input type="date" class="form-control" id="add_fecha" name="fecha" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_estado" class="form-label">Estado *</label>
                                <select class="form-select" id="add_estado" name="estado" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_orador_inicial" class="form-label">Orador Inicial</label>
                                <select class="form-select" id="add_orador_inicial" name="orador_inicial">
                                    <option value="">Seleccionar...</option>
                                    @if(Auth::user()->perfil == 3 && isset($usuariosOradorInicial))
                                        @foreach($usuariosOradorInicial as $usuario)
                                            <option value="{{ $usuario->id }}"
                                                    data-ultima-fecha="{{ $usuario->ultima_fecha ? \Carbon\Carbon::parse($usuario->ultima_fecha)->format('d/m/Y') : '' }}">
                                                @if($usuario->ultima_fecha)
                                                    {{ \Carbon\Carbon::parse($usuario->ultima_fecha)->format('d/m/Y') }} - {{ $usuario->name }}
                                                @else
                                                    Primera vez - {{ $usuario->name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    @else
                                        @foreach($usuarios as $usuario)
                                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_presidencia" class="form-label">Presidencia</label>
                                <select class="form-select" id="add_presidencia" name="presidencia">
                                    <option value="">Seleccionar...</option>
                                    @if(Auth::user()->perfil == 3 && isset($usuariosPresidencia))
                                        @foreach($usuariosPresidencia as $usuario)
                                            <option value="{{ $usuario->id }}"
                                                    data-ultima-fecha="{{ $usuario->ultima_fecha ? \Carbon\Carbon::parse($usuario->ultima_fecha)->format('d/m/Y') : '' }}">
                                                @if($usuario->ultima_fecha)
                                                    {{ \Carbon\Carbon::parse($usuario->ultima_fecha)->format('d/m/Y') }} - {{ $usuario->name }}
                                                @else
                                                    Primera vez - {{ $usuario->name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    @else
                                        @foreach($usuarios as $usuario)
                                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="add_cancion_pre" class="form-label">Canción Inicial</label>
                                <select class="form-select" id="add_cancion_pre" name="cancion_pre">
                                    <option value="">Seleccionar...</option>
                                    @foreach($canciones as $cancion)
                                        <option value="{{ $cancion->id }}">{{ $cancion->numero ? $cancion->numero . ' - ' : '' }}{{ $cancion->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="add_cancion_en" class="form-label">Canción Intermedia</label>
                                <select class="form-select" id="add_cancion_en" name="cancion_en">
                                    <option value="">Seleccionar...</option>
                                    @foreach($canciones as $cancion)
                                        <option value="{{ $cancion->id }}">{{ $cancion->numero ? $cancion->numero . ' - ' : '' }}{{ $cancion->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="add_cancion_post" class="form-label">Canción Final</label>
                                <select class="form-select" id="add_cancion_post" name="cancion_post">
                                    <option value="">Seleccionar...</option>
                                    @foreach($canciones as $cancion)
                                        <option value="{{ $cancion->id }}">{{ $cancion->numero ? $cancion->numero . ' - ' : '' }}{{ $cancion->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_orador_final" class="form-label">Orador Final</label>
                                <select class="form-select" id="add_orador_final" name="orador_final">
                                    <option value="">Seleccionar...</option>
                                    @if(Auth::user()->perfil == 3 && isset($usuariosOradorInicial))
                                        @foreach($usuariosOradorInicial as $usuario)
                                            <option value="{{ $usuario->id }}"
                                                    data-ultima-fecha="{{ $usuario->ultima_fecha ? \Carbon\Carbon::parse($usuario->ultima_fecha)->format('d/m/Y') : '' }}">
                                                @if($usuario->ultima_fecha)
                                                    {{ \Carbon\Carbon::parse($usuario->ultima_fecha)->format('d/m/Y') }} - {{ $usuario->name }}
                                                @else
                                                    Primera vez - {{ $usuario->name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    @else
                                        @foreach($usuarios as $usuario)
                                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="addProgramaBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        Crear Programa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#programasTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        responsive: true,
        order: [[0, 'desc']], // Ordenar por fecha descendente
        columnDefs: [
            { targets: [8], orderable: false } // Columna de acciones no ordenable
        ]
    });

    // Inicializar Select2 para presidencia, orador inicial y orador final solo si es coordinador
    @if(Auth::user()->perfil == 3)
    $('#add_presidencia').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addProgramaModal'),
        placeholder: "Seleccionar presidencia...",
        allowClear: true,
        width: '100%'
    });

    $('#add_orador_inicial').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addProgramaModal'),
        placeholder: "Seleccionar orador inicial...",
        allowClear: true,
        width: '100%'
    });

    $('#add_orador_final').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addProgramaModal'),
        placeholder: "Seleccionar orador final...",
        allowClear: true,
        width: '100%'
    });

    // Inicializar Select2 para canciones
    $('#add_cancion_pre').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addProgramaModal'),
        placeholder: "Seleccionar canción inicial...",
        allowClear: true,
        width: '100%'
    });

    $('#add_cancion_en').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addProgramaModal'),
        placeholder: "Seleccionar canción intermedia...",
        allowClear: true,
        width: '100%'
    });

    $('#add_cancion_post').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addProgramaModal'),
        placeholder: "Seleccionar canción final...",
        allowClear: true,
        width: '100%'
    });
    @endif

    // Manejar envío del formulario de agregar
    $('#addProgramaForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#addProgramaBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        // Deshabilitar botón y mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#add-alert-container').empty();
        
        $.ajax({
            url: '{{ route("programas.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#addProgramaModal').modal('hide');
                    location.reload();
                } else {
                    showAlert('add-alert-container', 'danger', response.message || 'Error al crear el programa');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        $(`#add_${field}`).addClass('is-invalid');
                        $(`#add_${field}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                } else {
                    showAlert('add-alert-container', 'danger', 'Error al crear el programa');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Cargar datos para editar
    $('.edit-programa').click(function() {
        const programaId = $(this).data('id');
        const row = $(this).closest('tr');
        
        // Obtener datos de la fila
        const fecha = row.find('td:eq(0)').text().trim();
        const fechaFormatted = fecha.split('/').reverse().join('-'); // Convertir dd/mm/yyyy a yyyy-mm-dd
        
        $('#edit_programa_id').val(programaId);
        $('#edit_fecha').val(fechaFormatted);
        
        // Aquí podrías hacer una llamada AJAX para obtener todos los datos del programa
        // Por simplicidad, solo establecemos algunos valores por defecto
        $('#edit_estado').val('1');
    });

    // Manejar envío del formulario de editar
    $('#editProgramaForm').submit(function(e) {
        e.preventDefault();
        
        const programaId = $('#edit_programa_id').val();
        const submitBtn = $('#editProgramaBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        // Deshabilitar botón y mostrar spinner
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#edit-alert-container').empty();
        
        $.ajax({
            url: `/programas/${programaId}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editProgramaModal').modal('hide');
                    location.reload();
                } else {
                    showAlert('edit-alert-container', 'danger', response.message || 'Error al actualizar el programa');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        $(`#edit_${field}`).addClass('is-invalid');
                        $(`#edit_${field}`).siblings('.invalid-feedback').text(errors[field][0]);
                    }
                } else {
                    showAlert('edit-alert-container', 'danger', 'Error al actualizar el programa');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });

    // Manejar eliminación
    $('.delete-programa').click(function() {
        const programaId = $(this).data('id');
        
        if (confirm('¿Está seguro que desea eliminar este programa?')) {
            $.ajax({
                url: `/programas/${programaId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error al eliminar el programa');
                    }
                },
                error: function() {
                    alert('Error al eliminar el programa');
                }
            });
        }
    });

    // Función para mostrar alertas
    function showAlert(containerId, type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $(`#${containerId}`).html(alertHtml);
    }

    // Manejar eventos del modal para Select2 (solo para coordinadores)
    @if(Auth::user()->perfil == 3)
    // Cuando se abre el modal, asegurar que Select2 esté inicializado
    $('#addProgramaModal').on('shown.bs.modal', function() {
        if (!$('#add_presidencia').hasClass('select2-hidden-accessible')) {
            $('#add_presidencia').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addProgramaModal'),
                placeholder: "Seleccionar presidencia...",
                allowClear: true,
                width: '100%'
            });
        }
        
        if (!$('#add_orador_inicial').hasClass('select2-hidden-accessible')) {
            $('#add_orador_inicial').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addProgramaModal'),
                placeholder: "Seleccionar orador inicial...",
                allowClear: true,
                width: '100%'
            });
        }
        
        if (!$('#add_orador_final').hasClass('select2-hidden-accessible')) {
            $('#add_orador_final').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addProgramaModal'),
                placeholder: "Seleccionar orador final...",
                allowClear: true,
                width: '100%'
            });
        }
        
        // Inicializar Select2 para canciones
        if (!$('#add_cancion_pre').hasClass('select2-hidden-accessible')) {
            $('#add_cancion_pre').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addProgramaModal'),
                placeholder: "Seleccionar canción inicial...",
                allowClear: true,
                width: '100%'
            });
        }
        
        if (!$('#add_cancion_en').hasClass('select2-hidden-accessible')) {
            $('#add_cancion_en').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addProgramaModal'),
                placeholder: "Seleccionar canción intermedia...",
                allowClear: true,
                width: '100%'
            });
        }
        
        if (!$('#add_cancion_post').hasClass('select2-hidden-accessible')) {
            $('#add_cancion_post').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addProgramaModal'),
                placeholder: "Seleccionar canción final...",
                allowClear: true,
                width: '100%'
            });
        }
    });

    // Cuando se cierra el modal, limpiar Select2
    $('#addProgramaModal').on('hidden.bs.modal', function() {
        if ($('#add_presidencia').hasClass('select2-hidden-accessible')) {
            $('#add_presidencia').select2('destroy');
        }
        
        if ($('#add_orador_inicial').hasClass('select2-hidden-accessible')) {
            $('#add_orador_inicial').select2('destroy');
        }
        
        if ($('#add_orador_final').hasClass('select2-hidden-accessible')) {
            $('#add_orador_final').select2('destroy');
        }
        
        // Limpiar Select2 de canciones
        if ($('#add_cancion_pre').hasClass('select2-hidden-accessible')) {
            $('#add_cancion_pre').select2('destroy');
        }
        
        if ($('#add_cancion_en').hasClass('select2-hidden-accessible')) {
            $('#add_cancion_en').select2('destroy');
        }
        
        if ($('#add_cancion_post').hasClass('select2-hidden-accessible')) {
            $('#add_cancion_post').select2('destroy');
        }
        
        // Limpiar el formulario
        $('#addProgramaForm')[0].reset();
    });
    @endif
});
</script>
@endpush
@endsection