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
            background-color: #fff;
            color: black;
            padding: 8px;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .fecha-header {
            text-align: center;
            background-color: #666;
            color: white;
            padding: 5px;
            font-size: 14px;
            margin-bottom: 3px;
            font-weight: bold;
        }
        .seccion-header {
            text-align: center;
            color: black;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 2px;
        }
        .tesoros-biblia { background-color: #a2c4c9; }
        .mejores-maestros { background-color: #ffe599; }
        .vida-cristiana { background-color: #ea9999; }

        .parte-item {
            padding: 2px 8px;
            border-bottom: 1px solid #eee;
            font-size: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .parte-content {
            font-size: 12px;
            flex: 1;
            height: 8px;
        }

        .parte-asignado {
            text-align: right;
            font-weight: bold;
            min-width: 80px;
            font-family: "Cascadia Mono", monospace !important;
            font-size: 10px;
            height: 8px;
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
                        {{ $congregacionNombre ?? 'Sin nombre' }}, PROGRAMA NUESTRA VIDA CRISTIANA
                    </div>
                    @endif
                    <!-- Fecha en español -->
                    <div class="fecha-header">
                        {{ \Carbon\Carbon::parse($programa->fecha)->locale('es')->translatedFormat('l j \d\e F \d\e Y') }}
                    </div>
                    <div class="parte-item">
                        <div class="parte-content">
                            Canción: {{ $programa->cancion_pre ?? 'Sin asignar' }}
                        </div>
                        <div class="parte-asignado"> ORADOR INICIAL:
                            {{ $programa->nombre_orador_inicial ?? 'Sin asignar' }}
                        </div>
                    </div>
                    <div class="parte-item">
                        <div class="parte-content">
                            01 min. Palabras de introducción (Presidente)
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

                             $mejoresMaestrosAuxiliar2 = $mejoresMaestros->filter(function($parte) {
                                return $parte->sala_id == 3;
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
                                            | {{ mb_str_pad(substr($parte->ayudante_nombre, 0, 20), 20, '.', STR_PAD_RIGHT) }}
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
                                            | {{ mb_str_pad(substr($parte->ayudante_nombre, 0, 20), 20, '.', STR_PAD_RIGHT) }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- SEAMOS MEJORES MAESTROS - SALA AUXILIAR 2 -->
                        @if($mejoresMaestrosAuxiliar2->count() > 0)
                            <div class="seccion-header mejores-maestros">
                                SEAMOS MEJORES MAESTROS - SALA AUXILIAR 2
                            </div>
                            @foreach($mejoresMaestrosAuxiliar2 as $parte)
                                <div class="parte-item">
                                    <div class="parte-content">
                                        {{ str_pad($parte->tiempo ?? '', 2, '0', STR_PAD_LEFT) }} min. {{ ($loop->iteration + 3) }}) {{ $parte->tema ?? $parte->parte_nombre }}
                                    </div>
                                    <div class="parte-asignado">
                                        {{ $parte->encargado_nombre ?? 'Sin asignar' }}
                                        @if($parte->ayudante_nombre)
                                            | {{ mb_str_pad(substr($parte->ayudante_nombre, 0, 20), 20, '.', STR_PAD_RIGHT) }}
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
                                @if($loop->iteration == 1)
                                    <div class="parte-item">
                                        <div class="parte-content">
                                            Canción: {{ $programa->cancion_en ?? 'Sin asignar' }}
                                        </div>
                                        <div class="parte-asignado"></div>
                                    </div>
                                @endif
                                <div class="parte-item">
                                    <div class="parte-content">
                                        @if($parte->parte_id == 24)
                                            {{ $parte->parte_nombre }}:
                                        @elseif($parte->parte_id == 25)
                                        <!-- Guardar datos PHP para imprimir despues de presidencia y oración final -->
                                        @php
                                                $visitaSC_tiempo = str_pad($parte->tiempo ?? '', 2, '0', STR_PAD_LEFT);
                                                $visitaSC_tema = $parte->tema ?? $parte->parte_nombre;
                                                $visitaSC_encargado = $parte->encargado_nombre ?? 'Sin asignar';
                                        @endphp
                                        @else
                                            {{ str_pad($parte->tiempo ?? '', 2, '0', STR_PAD_LEFT) }} min. {{ ($loop->iteration + $mejoresMaestrosPrincipal->count() + 3) }}) {{ $parte->tema ?? $parte->parte_nombre }}
                                        @endif
                                    </div>
                                    <div class="parte-asignado">
                                        @if($parte->parte_id != 25)
                                            {{ $parte->encargado_nombre ?? 'Sin asignar' }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- Presidencia y oración final -->
                        <div class="parte-item" style="margin-top: 5px;">
                            <div class="parte-content">
                                03 min. Palabras de conclusión (Presidente)
                            </div>
                            <div class="parte-asignado">
                                {{ $programa->nombre_presidencia ?? 'Sin asignar' }}
                            </div>
                        </div>
                        @if($programa->cancion_post || $programa->nombre_orador_final)
                            <div class="parte-item">
                                <div class="parte-content">
                                    Canción: {{ $programa->cancion_post ?? 'Sin asignar' }}
                                </div>
                                <div class="parte-asignado">
                                    ORADOR FINAL: {{ $programa->nombre_orador_final?? 'Sin asignar' }}
                                </div>
                            </div>
                        @endif
                        @if(isset($visitaSC_encargado))
                            <div class="parte-item" style="margin-top: 5px;">
                                <div class="parte-content">
                                    {{ $visitaSC_tiempo }} min. {{ $visitaSC_tema }}
                                </div>
                                <div class="parte-asignado">
                                    {{ $visitaSC_encargado }}
                                </div>
                            </div>
                            @php
                                $visitaSC_tiempo = null;
                                $visitaSC_tema = null;
                                $visitaSC_encargado = null;
                            @endphp
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