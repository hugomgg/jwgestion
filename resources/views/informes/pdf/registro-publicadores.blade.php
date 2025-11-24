<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Registro de Publicadores {{ $anio }}</title>
    <style>
        @page {
            margin: 1cm;
            size: letter portrait;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .publicador-container {
            width: 100%;
            margin-bottom: 0.5cm;
        }
        
        .info-publicador {
            margin-bottom: 10px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f8f9fa;
        }
        
        .info-row {
            margin-bottom: 3px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        
        .info-value {
            font-weight: bold;
            display: inline-block;
            width: 300px;
        }
        .checkbox-group {
            margin-top: 5px;
        }
        
        .checkbox-item {
            display: inline-block;
            margin-right: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        table th {
            background-color: #343a40;
            color: white;
            padding: 4px 3px;
            text-align: center;
            border: 1px solid #000;
            font-size: 10px;
            font-weight: bold;
        }
        
        table td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: center;
            font-size: 10px;
        }
        
        table td:first-child {
            text-align: left;
            font-weight: 500;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        
        table td:last-child {
            text-align: left;
            font-size: 10px;
        }
        
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        
        .checkbox-pdf {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #000;
            text-align: center;
            line-height: 10px;
            font-size: 10px;
        }
        
        .checkbox-pdf.checked::before {
            content: "X";
        }
        
        .table-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    @foreach($datosPublicadores as $index => $datos)
        <div class="publicador-container">
            <!-- Información del publicador -->
            <div class="info-publicador">
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">{{ $datos['user_info']['nombre'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de nacimiento:</span>
                    <span class="info-value">{{ $datos['user_info']['fecha_nacimiento'] }}</span>
                    <span style="margin-left: 20px;">
                        <span class="checkbox-pdf {{ $datos['user_info']['sexo'] == 1 ? 'checked' : '' }}"></span> Hombre
                        <span style="margin-left: 10px;" class="checkbox-pdf {{ $datos['user_info']['sexo'] == 2 ? 'checked' : '' }}"></span> Mujer
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de bautismo:</span>
                    <span class="info-value">{{ $datos['user_info']['fecha_bautismo'] }}</span>
                    <span style="margin-left: 20px;">
                        <span class="checkbox-pdf {{ $datos['user_info']['esperanza'] == 2 ? 'checked' : '' }}"></span> Otras ovejas
                        <span style="margin-left: 10px;" class="checkbox-pdf {{ $datos['user_info']['esperanza'] == 1 ? 'checked' : '' }}"></span> Ungido
                    </span>
                </div>
                <div class="checkbox-group">
                    <span class="checkbox-item">
                        <span class="checkbox-pdf {{ $datos['user_info']['es_anciano'] ? 'checked' : '' }}"></span> Anciano
                    </span>
                    <span class="checkbox-item">
                        <span class="checkbox-pdf {{ $datos['user_info']['es_siervo'] ? 'checked' : '' }}"></span> Siervo ministerial
                    </span>
                    <span class="checkbox-item">
                        <span class="checkbox-pdf {{ $datos['user_info']['es_precursor_regular'] ? 'checked' : '' }}"></span> Precursor regular
                    </span>
                    <span class="checkbox-item">
                        <span class="checkbox-pdf {{ $datos['user_info']['es_precursor_especial'] ? 'checked' : '' }}"></span> Precursor especial
                    </span>
                    <span class="checkbox-item">
                        <span class="checkbox-pdf {{ $datos['user_info']['es_misionero'] ? 'checked' : '' }}"></span> Misionero que sirve en el campo
                    </span>
                </div>
            </div>

            <!-- Tabla año actual -->
            <div class="table-title"></div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">Año de servicio {{ $datos['anio_actual'] }}</th>
                        <th style="width: 50px;">Participación</th>
                        <th style="width: 50px;">Cursos bíblicos</th>
                        <th style="width: 50px;">Precursor auxiliar</th>
                        <th style="width: 60px;">Horas</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datos['registro_actual'] as $item)
                        <tr class="{{ $item['mes_nombre'] === 'Total' ? 'total-row' : '' }}">
                            <td>{{ $item['mes_nombre'] }}</td>
                            <td>
                                @if($item['mes_nombre'] !== 'Total')
                                    <span class="checkbox-pdf {{ $item['participa'] == 1 ? 'checked' : '' }}"></span>
                                @endif
                            </td>
                            <td>{{ $item['cantidad_estudios'] > 0 ? $item['cantidad_estudios'] : '' }}</td>
                            <td>
                                @if($item['mes_nombre'] !== 'Total')
                                    <span class="checkbox-pdf {{ $item['es_precursor_auxiliar'] == 1 ? 'checked' : '' }}"></span>
                                @endif
                            </td>
                            <td>{{ $item['horas'] !== null && $item['horas'] > 0 ? $item['horas'] : '' }}</td>
                            <td>{{ $item['nota'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Tabla año anterior -->
            <div class="table-title"></div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">Año de servicio {{ $datos['anio_anterior'] }}</th>
                        <th style="width: 50px;">Participación</th>
                        <th style="width: 50px;">Cursos bíblicos</th>
                        <th style="width: 50px;">Precursor auxiliar</th>
                        <th style="width: 60px;">Horas</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datos['registro_anterior'] as $item)
                        <tr class="{{ $item['mes_nombre'] === 'Total' ? 'total-row' : '' }}">
                            <td>{{ $item['mes_nombre'] }}</td>
                            <td>
                                @if($item['mes_nombre'] !== 'Total')
                                    <span class="checkbox-pdf {{ $item['participa'] == 1 ? 'checked' : '' }}"></span>
                                @endif
                            </td>
                            <td>{{ $item['cantidad_estudios'] > 0 ? $item['cantidad_estudios'] : '' }}</td>
                            <td>
                                @if($item['mes_nombre'] !== 'Total')
                                    <span class="checkbox-pdf {{ $item['es_precursor_auxiliar'] == 1 ? 'checked' : '' }}"></span>
                                @endif
                            </td>
                            <td>{{ $item['horas'] !== null && $item['horas'] > 0 ? $item['horas'] : '' }}</td>
                            <td>{{ $item['nota'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
