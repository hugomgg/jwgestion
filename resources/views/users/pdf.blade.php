<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Usuarios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 18px;
        }
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        .info-left, .info-right {
            width: 48%;
        }
        
        .filtros {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #007bff;
        }
        
        .filtros h3 {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #007bff;
        }
        
        .filtros ul {
            margin: 0;
            padding-left: 15px;
            font-size: 10px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        
        .table th {
            background-color: #007bff;
            color: white;
            padding: 8px 4px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: bold;
        }
        
        .table td {
            padding: 6px 4px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 40px;
        }
        
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-secondary { background-color: #6c757d; color: white; }
        .badge-dark { background-color: #343a40; color: white; }
        .badge-primary { background-color: #007bff; color: white; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-light { background-color: #f8f9fa; color: #212529; border: 1px solid #dee2e6; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 5px;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .asignaciones {
            font-size: 9px;
        }
        
        .asignaciones .badge {
            margin-right: 2px;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-users"></i> Listado de Usuarios</h1>
    </div>

    <div class="info-section">
        <div class="info-left">
            <strong>Fecha de generación:</strong> {{ $fecha_generacion }}<br>
            <strong>Generado por:</strong> {{ $generado_por }}
        </div>
        <div class="info-right">
            <strong>Total de usuarios:</strong> {{ $total_usuarios }}<br>
            <strong>Página:</strong> <span class="pagenum"></span>
        </div>
    </div>

    @if(count($filtros_aplicados) > 1 || $filtros_aplicados[0] !== 'Sin filtros aplicados')
    <div class="filtros">
        <h3>Filtros Aplicados:</h3>
        <ul>
            @foreach($filtros_aplicados as $filtro)
                <li>{{ $filtro }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 20%;">Nombre</th>
                <th style="width: 10%;">Perfil</th>
                <th style="width: 12%;">Congregación</th>
                <th style="width: 10%;">Grupo</th>
                <th style="width: 12%;">Nombramiento</th>
                <th style="width: 10%;">Servicio</th>
                <th style="width: 12%;">Estado Espiritual</th>
                <th style="width: 12%;">Asignaciones</th>
                <th style="width: 7%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>
                    <span class="badge badge-info">{{ $user->privilegio_perfil }}</span>
                </td>
                <td>
                    <span class="badge badge-secondary">{{ $user->nombre_congregacion }}</span>
                </td>
                <td>
                    <span class="badge badge-dark">{{ $user->nombre_grupo }}</span>
                </td>
                <td>
                    @if($user->nombre_nombramiento)
                        <span class="badge badge-primary">{{ $user->nombre_nombramiento }}</span>
                    @else
                        <span style="color: #6c757d;">-</span>
                    @endif
                </td>
                <td>
                    @if($user->nombre_servicio)
                        <span class="badge badge-warning">{{ $user->nombre_servicio }}</span>
                    @else
                        <span style="color: #6c757d;">-</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-light">{{ $user->nombre_estado_espiritual }}</span>
                </td>
                <td class="asignaciones">
                    @if($user->asignaciones && $user->asignaciones->count() > 0)
                        @foreach($user->asignaciones as $asignacion)
                            <span class="badge badge-info">{{ $asignacion->abreviacion }}</span>
                        @endforeach
                    @else
                        <span style="color: #6c757d;">-</span>
                    @endif
                </td>
                <td>
                    @if($user->estado == 1)
                        <span class="badge badge-success">Activo</span>
                    @else
                        <span class="badge badge-danger">Inactivo</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema de Gestión de Usuarios - Generado el {{ $fecha_generacion }}</p>
    </div>
</body>
</html>