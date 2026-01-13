@extends('layouts.app')

@push('styles')
@vite(['resources/css/programas-resumen.css'])
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-0">
                                <i class="fas fa-table me-2"></i>Resumen de Programas
                                @if($anio)
                                    - Año {{ $anio }}
                                    @if($meses && count($meses) > 0)
                                        - {{ count($meses) == 1 ? 'Mes' : 'Meses' }} seleccionado{{ count($meses) > 1 ? 's' : '' }}
                                    @endif
                                @endif
                            </h5>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('programas.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm" id="resumenTable" style="width: 100%;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Parte</th>
                                    <th>Nombre</th>
                                    <th>Participa</th>
                                    <th>Rol</th>
                                    <th>Sala</th>
                                    <th>Reemplazado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $row)
                                <tr data-color-index="{{ $row['color_index'] }}">
                                    <td>{{ $row['fecha'] }}</td>
                                    <td>{{ $row['parte'] }}</td>
                                    <td>{{ $row['nombre'] }}</td>
                                    <td>{{ $row['participaciones'] }}</td>
                                    <td>{{ $row['rol'] }}</td>
                                    <td>{{ $row['sala'] }}</td>
                                    <td style="text-decoration: {{ $row['reemplazado'] ? 'line-through' : 'none' }};">
                                        {{ $row['reemplazado'] }}
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Inicializar DataTable sin paginación
    $('#resumenTable').DataTable({
        language: {
            url: '/js/datatables-es-ES.json'
        },
        responsive: false,
        scrollX: false, // Desactivar scroll de DataTables, usar el del contenedor
        autoWidth: false, // Desactivar auto-ajuste de ancho
        paging: false,  // Sin paginación
        pageLength: -1, // Mostrar todos los registros
        order: [[0, 'asc']], // Ordenar por fecha ascendente
        columnDefs: [
            { targets: [6], orderable: true }, // Todas las columnas ordenables
            { width: '12%', targets: 0 }, // Fecha
            { width: '10%', targets: 1 }, // Parte
            { width: '25%', targets: 2 }, // Nombre
            { width: '15%', targets: 3 }, // Participaciones
            { width: '15%', targets: 4 }, // Rol
            { width: '8%', targets: 5 },  // Sala
            { width: '15%', targets: 6 }  // Reemplazado
        ],
        dom: 'frtip', // f: filtro, r: processing, t: tabla, i: info, p: paginación (removida)
        info: true, // Mostrar información de registros
        searching: true // Habilitar búsqueda
    });
});
</script>
@endpush
