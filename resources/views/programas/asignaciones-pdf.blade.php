<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asignaciones para la Reunión Vida y Ministerio Cristianos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 14px;
        }

        .page {
            page-break-after: always;
            padding: 20px;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .assignment-container {
            width: 100%;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .assignment-box {
            margin-bottom: 0;
            padding: 0;
            min-height: 180px;
            page-break-inside: avoid;
        }

        .assignment-header {
            background-color: #ffffff;
            border-bottom: 0px solid #000;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            padding: 8px;
            text-transform: uppercase;
        }

        .assignment-content {
            padding: 8px;
        }

        .field-row {
            display: flex;
            margin-bottom: 6px;
            align-items: center;
        }

        .field-label {
            font-weight: bold;
            width: 80px;
            flex-shrink: 0;
        }

        .field-value {
            border-bottom: 1px solid #000;
            flex-grow: 1;
            min-height: 18px;
            padding-bottom: 2px;
            margin-left: 5px;
            color: #0000FF;
            font-weight: bold;
        }

        .tipo-intervencion {
            margin: 6px 0;
        }

        .checkbox-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .checkbox {
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            display: inline-block;
        }

        .checkbox.checked {
            background-color: #000;
        }

        .presenta-en {
            margin-top: 6px;
        }

        .salas-group {
            display: flex;
            gap: 20px;
            margin-top: 5px;
        }

        .sala-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nota-section {
            margin-top: 8px;
            font-size: 12px;
        }

        .nota-text {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .guia-text {
            text-align: justify;
            margin-bottom: 5px;
        }

        .codigo-seccion {
            font-size: 12px;
            color: #666;
            text-align: right;
            margin-top: 6px;
        }

        /* Ajustes para 4 asignaciones por página: 2 arriba y 2 abajo usando tabla */
        .assignments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
            border: 0px solid #000;
        }
        .assignments-table td {
            width: 50%;
            padding: 10px;
            vertical-align: top;
            height: 200px;
        }

        .assignments-table tr {
            height: 200px;
        }

        /* Estilos para la cruz divisoria roja */
        .page {
            position: relative;
        }

        .cross-divider {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10;
        }

        .cross-horizontal {
            position: absolute;
            top: 50%;
            left: 20px;
            right: 20px;
            height: 1px;
            background-color: #f0f0f0;
            transform: translateY(-50%);
        }

        .cross-vertical {
            position: absolute;
            left: 50%;
            top: 20px;
            bottom: 20px;
            width: 1px;
            background-color: #f0f0f0;
            transform: translateX(-50%);
        }

    </style>
</head>
<body>
    @foreach($asignacionesAgrupadas as $grupo)
    <div class="page">
        <!-- Cruz divisoria roja en el centro -->
        <div class="cross-divider">
            <div class="cross-horizontal"></div>
            <div class="cross-vertical"></div>
        </div>

        <!-- Tabla para organizar 2 asignaciones arriba y 2 abajo -->
        <table class="assignments-table">
            <!-- Primera fila: 2 asignaciones arriba -->
            <tr>
                @foreach($grupo->take(2) as $index => $asignacion)
                <td>
                    <div class="assignment-box">
                        <div class="assignment-header">
                            ASIGNACION PARA LA REUNIÓN<br>
                            VIDA Y MINISTERIO CRISTIANOS
                        </div>
                        <div class="assignment-content">
                            <div class="field-row">
                                <span class="field-label">Encargado:</span>
                                <span class="field-value">{{ $asignacion->nombre_encargado ?? '' }}</span>
                            </div>

                            <div class="field-row">
                                <span class="field-label">Ayudante:</span>
                                <span class="field-value">{{ $asignacion->nombre_ayudante ?? '' }}</span>
                            </div>

                            <div class="field-row">
                                <span class="field-label">Fecha:</span>
                                <span class="field-value">{{ \Carbon\Carbon::parse($asignacion->fecha)->locale('es')->translatedFormat('d \d\e F \d\e Y') }}</span>
                            </div>

                            <div class="field-row">
                                <span class="field-label">Intervención núm.:</span>
                                <span class="field-value">{{ $asignacion->numero_intervencion ?? '' }}</span>
                            </div>
                            <br>
                            <div class="presenta-en">
                                <div style="font-weight: bold; margin-bottom: 5px;">Se presentará en:</div>
                                <div class="salas-group">
                                    <div class="sala-item">
                                        <span class="checkbox {{ $asignacion->sala_id == 1 ? 'checked' : '' }}"></span>
                                        <span>Sala principal</span>
                                    </div>
                                    <div class="sala-item">
                                        <span class="checkbox {{ $asignacion->sala_id == 2 ? 'checked' : '' }}"></span>
                                        <span>Sala auxiliar 1</span>
                                    </div>
                                    <div class="sala-item">
                                        <span class="checkbox {{ $asignacion->sala_id == 3 ? 'checked' : '' }}"></span>
                                        <span>Sala auxiliar 2</span>
                                    </div>
                                </div>
                            </div>

                            <div class="nota-section">
                                <div class="nota-text">Nota al estudiante:</div>
                                <div class="guia-text">En la <em>Guía de actividades</em> encontrará la información que necesita para su intervención. Repase también las indicaciones que se describen en las Instrucciones para la reunión Vida y Ministerio Cristianos (S-38).</div>
                            </div>

                            <div class="codigo-seccion">
                                S-89-S 11/23
                            </div>
                            <br><br><br><br>
                        </div>
                    </div>
                </td>
                @endforeach
                @if($grupo->count() == 1)
                <td></td> <!-- Celda vacía si solo hay una asignación -->
                @endif
            </tr>

            <!-- Segunda fila: 2 asignaciones abajo -->
            @if($grupo->count() > 2)
            <tr>
                @foreach($grupo->skip(2)->take(2) as $index => $asignacion)
                <td>
                    <div class="assignment-box">
                        <div class="assignment-header">
                            ASIGNACION PARA LA REUNIÓN<br>
                            VIDA Y MINISTERIO CRISTIANOS
                        </div>
                        <div class="assignment-content">
                            <div class="field-row">
                                <span class="field-label">Encargado:</span>
                                <span class="field-value">{{ $asignacion->nombre_encargado ?? '' }}</span>
                            </div>

                            <div class="field-row">
                                <span class="field-label">Ayudante:</span>
                                <span class="field-value">{{ $asignacion->nombre_ayudante ?? '' }}</span>
                            </div>

                            <div class="field-row">
                                <span class="field-label">Fecha:</span>
                                <span class="field-value">{{ \Carbon\Carbon::parse($asignacion->fecha)->locale('es')->translatedFormat('d \d\e F \d\e Y') }}</span>
                            </div>

                            <div class="field-row">
                                <span class="field-label">Intervención núm.:</span>
                                <span class="field-value">{{ $asignacion->numero_intervencion ?? '' }}</span>
                            </div>
                            <br>
                            <div class="presenta-en">
                                <div style="font-weight: bold; margin-bottom: 5px;">Se presentará en:</div>
                                <div class="salas-group">
                                    <div class="sala-item">
                                        <span class="checkbox {{ $asignacion->sala_id == 1 ? 'checked' : '' }}"></span>
                                        <span>Sala principal</span>
                                    </div>
                                    <div class="sala-item">
                                        <span class="checkbox {{ $asignacion->sala_id == 2 ? 'checked' : '' }}"></span>
                                        <span>Sala auxiliar 1</span>
                                    </div>
                                    <div class="sala-item">
                                        <span class="checkbox {{ $asignacion->sala_id == 3 ? 'checked' : '' }}"></span>
                                        <span>Sala auxiliar 2</span>
                                    </div>
                                </div>
                            </div>

                            <div class="nota-section">
                                <div class="nota-text">Nota al estudiante:</div>
                                <div class="guia-text">En la <em>Guía de actividades</em> encontrará la información que necesita para su intervención así como el aspecto de la oratoria que debe preparar con la ayuda del folleto Maestros.</div>
                            </div>

                            <div class="codigo-seccion">
                                S-89-S 11/23
                            </div>
                            <br><br><br>
                        </div>
                    </div>
                </td>
                @endforeach
                @if($grupo->count() == 3)
                <td></td> <!-- Celda vacía si solo hay tres asignaciones -->
                @endif
            </tr>
            @endif
        </table>
    </div>
    @endforeach
</body>
</html>