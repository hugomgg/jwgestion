<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Programas de Entre Semana</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.1;
            margin: 8px;
            padding: 0;
        }
        .programa-container {
            width: 100%;
            margin-bottom: 20px;
            break-inside: avoid;
            page-break-inside: avoid;
            clear: both;
        }
        .programa-header {
            text-align: center;
            background-color: #333;
            color: white;
            padding: 8px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .fecha-header {
            text-align: center;
            background-color: #666;
            color: white;
            padding: 5px;
            font-size: 10px;
            margin-bottom: 3px;
        }
        .seccion-header {
            color: white;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 2px;
        }
        .tesoros-biblia { background-color: #4a90e2; }
        .mejores-maestros { background-color: #f5a623; }
        .vida-cristiana { background-color: #e94b3c; }
        
        .parte-item {
            padding: 3px 8px;
            border-bottom: 1px solid #eee;
            font-size: 8px;
        }
        .clear { clear: both; }
        .page-break {
            page-break-after: always;
            clear: both;
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 15px; font-size: 10px;">
        <strong>Congregación: {{ $congregacionNombre ?? 'Sin nombre' }}</strong>
    </div>

    @if($programas && $programas->count() > 0)
        @foreach($programas->chunk(2) as $programasPar)
            @foreach($programasPar as $programa)
                <div class="programa-container">
                    <!-- Header del programa -->
                    <div class="programa-header">
                        Programa de entre semana
                    </div>
                    
                    <!-- Fecha -->
                    <div class="fecha-header">
                        {{ date('l j F Y', strtotime($programa->fecha)) }}
                    </div>
                    
                    @if($programa->partes && count($programa->partes) > 0)
                        @php
                            $tesorosBiblia = $programa->partes->filter(function($parte) {
                                return stripos($parte->parte_nombre, 'tesoro') !== false || 
                                       stripos($parte->parte_nombre, 'perla') !== false ||
                                       stripos($parte->parte_nombre, 'lectura') !== false;
                            });
                            
                            $mejoresMaestros = $programa->partes->filter(function($parte) {
                                return stripos($parte->parte_nombre, 'maestro') !== false ||
                                       stripos($parte->parte_nombre, 'conversacion') !== false;
                            });
                            
                            $vidaCristiana = $programa->partes->filter(function($parte) {
                                return stripos($parte->parte_nombre, 'vida') !== false ||
                                       stripos($parte->parte_nombre, 'estudio') !== false ||
                                       stripos($parte->parte_nombre, 'informe') !== false;
                            });
                        @endphp
                        
                        <!-- TESOROS DE LA BIBLIA -->
                        @if($tesorosBiblia->count() > 0)
                            <div class="seccion-header tesoros-biblia">
                                TESOROS DE LA BIBLIA
                            </div>
                            @foreach($tesorosBiblia as $parte)
                                <div class="parte-item">
                                    {{ $parte->tiempo ?? '' }}min. - {{ $parte->tema ?? $parte->parte_nombre }} 
                                    - {{ $parte->encargado_nombre ?? 'Sin asignar' }}
                                </div>
                            @endforeach
                        @endif
                        
                        <!-- SEAMOS MEJORES MAESTROS -->
                        @if($mejoresMaestros->count() > 0)
                            <div class="seccion-header mejores-maestros">
                                SEAMOS MEJORES MAESTROS
                            </div>
                            @foreach($mejoresMaestros as $parte)
                                <div class="parte-item">
                                    {{ $parte->tiempo ?? '' }}min. - {{ $parte->tema ?? $parte->parte_nombre }} 
                                    - {{ $parte->encargado_nombre ?? 'Sin asignar' }}
                                    @if($parte->ayudante_nombre)
                                        | {{ $parte->ayudante_nombre }}
                                    @endif
                                </div>
                            @endforeach
                        @endif
                        
                        <!-- NUESTRA VIDA CRISTIANA -->
                        @if($vidaCristiana->count() > 0)
                            <div class="seccion-header vida-cristiana">
                                NUESTRA VIDA CRISTIANA
                            </div>
                            @foreach($vidaCristiana as $parte)
                                <div class="parte-item">
                                    {{ $parte->tiempo ?? '' }}min. - {{ $parte->tema ?? $parte->parte_nombre }} 
                                    - {{ $parte->encargado_nombre ?? 'Sin asignar' }}
                                </div>
                            @endforeach
                        @endif
                        
                        <!-- Presidencia y oración final -->
                        <div class="parte-item" style="margin-top: 5px;">
                            Presidente: {{ $programa->nombre_presidencia ?? 'Sin asignar' }}
                        </div>
                        @if($programa->nombre_orador_final)
                            <div class="parte-item">
                                Oración final: {{ $programa->nombre_orador_final }}
                            </div>
                        @endif
                        
                    @else
                        <!-- Si no hay partes, mostrar datos básicos -->
                        <div class="parte-item">
                            Presidente: {{ $programa->nombre_presidencia ?? 'Sin asignar' }}
                        </div>
                        <div class="parte-item">
                            Orador inicial: {{ $programa->nombre_orador_inicial ?? 'Sin asignar' }}
                        </div>
                        @if($programa->nombre_orador_final)
                            <div class="parte-item">
                                Oración final: {{ $programa->nombre_orador_final }}
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach
            <div class="clear page-break"></div>
        @endforeach
    @else
        <div style="text-align: center; padding: 40px;">
            No hay programas disponibles para exportar
        </div>
    @endif
</body>
</html>