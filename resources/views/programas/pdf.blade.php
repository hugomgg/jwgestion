<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Programas de Entre Semana</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.1;
            margin: 0px;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .parte-content {
            font-size: 12px;
            flex: 1;
            height: 9px;
        }

        .parte-asignado {
            text-align: right;
            font-weight: bold;
            min-width: 80px;
            font-family: "Cascadia Mono", monospace !important;
            font-size: 9px;
            height: 9px;
        }
        .clear { clear: both; }
        .page-break {
            page-break-after: always;
            clear: both;
        }
    </style>
</head>
<body>
    @if($programas && $programas->count() > 0)
        @foreach($programas->chunk(2) as $programasPar)
            @foreach($programasPar as $programa)
                <div class="programa-container">
                    <!-- Header del programa -->
                     <!--Solo la primera vez-->
                     @if($loop->first)
                    <div class="programa-header">
                        {{ $congregacionNombre ?? 'Sin nombre' }}: Programa de entre semana
                    </div>
                    @endif
                    <!-- Fecha -->
                    <div class="fecha-header">
                        {{ date('l j F Y', strtotime($programa->fecha)) }}
                    </div>
                    <div class="parte-item">
                            <div class="parte-content">
                                Orador inicial
                            </div>
                            <div class="parte-asignado">
                                {{ $programa->nombre_orador_inicial ?? 'Sin asignar' }}
                            </div>
                        </div>
                    <div class="parte-item">
                        <div class="parte-content">
                            Presidente
                        </div>
                        <div class="parte-asignado">
                            {{ $programa->nombre_presidencia ?? 'Sin asignar' }}
                        </div>
                    </div>
                    @if($programa->partes && count($programa->partes) > 0)
                        @php
                            $tesorosBiblia = $programa->partes->filter(function($parte) {
                                return $parte->seccion_id == 1;
                            });

                            $mejoresMaestros = $programa->partes->filter(function($parte) {
                                return $parte->seccion_id == 2;
                            });

                            // Separar por sala
                            $mejoresMaestrosPrincipal = $mejoresMaestros->filter(function($parte) {
                                return $parte->sala_id == 1;
                            });

                            $mejoresMaestrosAuxiliar = $mejoresMaestros->filter(function($parte) {
                                return $parte->sala_id == 2;
                            });

                            $vidaCristiana = $programa->partes->filter(function($parte) {
                                return $parte->seccion_id == 3;
                            });
                        @endphp

                        <!-- TESOROS DE LA BIBLIA -->
                        @if($tesorosBiblia->count() > 0)
                            <div class="seccion-header tesoros-biblia">
                                TESOROS DE LA BIBLIA
                            </div>
                            @foreach($tesorosBiblia as $parte)
                                <div class="parte-item">
                                    <div class="parte-content">
                                        {{ str_pad($parte->tiempo ?? '', 2, '0', STR_PAD_LEFT) }} min. {{ $loop->iteration }}) {{ $parte->tema ?? $parte->parte_nombre }}
                                    </div>
                                    <div class="parte-asignado">
                                        {{ $parte->encargado_nombre ?? 'Sin asignar' }}
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- SEAMOS MEJORES MAESTROS - SALA PRINCIPAL -->
                        @if($mejoresMaestrosPrincipal->count() > 0)
                            <div class="seccion-header mejores-maestros">
                                SEAMOS MEJORES MAESTROS - SALA PRINCIPAL
                            </div>
                            @foreach($mejoresMaestrosPrincipal as $parte)
                                <div class="parte-item">
                                    <div class="parte-content">
                                        {{ str_pad($parte->tiempo ?? '', 2, '0', STR_PAD_LEFT) }} min. {{ ($loop->iteration + 3) }}) {{ $parte->tema ?? $parte->parte_nombre }}
                                    </div>
                                    <div class="parte-asignado">
                                        {{ $parte->encargado_nombre ?? 'Sin asignar' }}
                                        @if($parte->ayudante_nombre)
                                            | {{ str_pad($parte->ayudante_nombre, 20, '.', STR_PAD_RIGHT) }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- SEAMOS MEJORES MAESTROS - SALA AUXILIAR 1 -->
                        @if($mejoresMaestrosAuxiliar->count() > 0)
                            <div class="seccion-header mejores-maestros">
                                SEAMOS MEJORES MAESTROS - SALA AUXILIAR 1
                            </div>
                            @foreach($mejoresMaestrosAuxiliar as $parte)
                                <div class="parte-item">
                                    <div class="parte-content">
                                        {{ str_pad($parte->tiempo ?? '', 2, '0', STR_PAD_LEFT) }} min. {{ ($loop->iteration + 3) }}) {{ $parte->tema ?? $parte->parte_nombre }}
                                    </div>
                                    <div class="parte-asignado">
                                        {{ $parte->encargado_nombre ?? 'Sin asignar' }}
                                        @if($parte->ayudante_nombre)
                                            | {{ str_pad($parte->ayudante_nombre, 20, '.', STR_PAD_RIGHT) }}
                                        @endif
                                    </div>
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
                                    <div class="parte-content">
                                        {{ str_pad($parte->tiempo ?? '', 2, '0', STR_PAD_LEFT) }} min. {{ ($loop->iteration + $mejoresMaestrosPrincipal->count() + 3) }}) {{ $parte->tema ?? $parte->parte_nombre }}
                                    </div>
                                    <div class="parte-asignado">
                                        {{ $parte->encargado_nombre ?? 'Sin asignar' }}
                                        <!-- Si es la penultima parte de nuestra vida cristiana, mostrar el nombre del encargado de la última parte y romper el ciclo -->
                                        @if($loop->iteration == ($vidaCristiana->count() - 1) && $vidaCristiana->count() > 1 && $vidaCristiana->last()->parte_id == 24)
                                            | {{ str_pad($vidaCristiana->last()->encargado_nombre, 20, '.', STR_PAD_RIGHT)}} </div></div>
                                            @break
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- Presidencia y oración final -->
                        <div class="parte-item" style="margin-top: 5px;">
                            <div class="parte-content">
                                Presidente
                            </div>
                            <div class="parte-asignado">
                                {{ $programa->nombre_presidencia ?? 'Sin asignar' }}
                            </div>
                        </div>
                        @if($programa->nombre_orador_final)
                            <div class="parte-item">
                                <div class="parte-content">
                                    Oración final
                                </div>
                                <div class="parte-asignado">
                                    {{ $programa->nombre_orador_final }}
                                </div>
                            </div>
                        @endif

                    @else
                        <!-- Si no hay partes, mostrar datos básicos -->
                        <div class="parte-item">
                            <div class="parte-content">
                                Presidente
                            </div>
                            <div class="parte-asignado">
                                {{ $programa->nombre_presidencia ?? 'Sin asignar' }}
                            </div>
                        </div>
                        <div class="parte-item">
                            <div class="parte-content">
                                Orador inicial
                            </div>
                            <div class="parte-asignado">
                                {{ $programa->nombre_orador_inicial ?? 'Sin asignar' }}
                            </div>
                        </div>
                        @if($programa->nombre_orador_final)
                            <div class="parte-item">
                                <div class="parte-content">
                                    Oración final
                                </div>
                                <div class="parte-asignado">
                                    {{ $programa->nombre_orador_final }}
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach
            <!--Si hay más programas, forzar salto de página-->
            @if(!$loop->last)
            <div class="clear page-break"></div>
            @endif
        @endforeach
    @else
        <div style="text-align: center; padding: 40px;">
            No hay programas disponibles para exportar
        </div>
    @endif
</body>
</html>