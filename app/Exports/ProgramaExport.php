<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProgramaExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $programas;
    protected $congregacionNombre;
    protected $currentRow = 1;

    public function __construct($programas, $congregacionNombre)
    {
        $this->programas = $programas;
        $this->congregacionNombre = $congregacionNombre;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = collect();

        foreach ($this->programas as $programa) {
            // Título de la congregación (para el primer, tercer, quinto,... programa)
            $this->currentRow++;
            if ($data->isEmpty() || ($this->currentRow-1) % 2 == 1) {
                $data->push([
                    'tema' => $this->congregacionNombre . ', PROGRAMA NUESTRA VIDA CRISTIANA',
                    'asignado' => '',
                    'ayudante' => ''
                ]);
                //$data->push(['', '']); // Línea en blanco
            }

            // Fecha del programa
            $fechaFormateada = \Carbon\Carbon::parse($programa->fecha)->locale('es')->translatedFormat('l j \d\e F \d\e Y');
            $data->push([
                'tema' => $fechaFormateada,
                'asignado' => '',
                'ayudante' => ''
            ]);
            $data->push(['', '']); // Línea en blanco
            // Canción inicial y orador
            $data->push([
                'tema' => 'Canción: ' . ($programa->cancion_pre ?? 'Sin asignar'),
                'asignado' => 'ORADOR INICIAL: ',
                'ayudante' => ($programa->nombre_orador_inicial ?? 'Sin asignar')
            ]);

            // Palabras de introducción
            $data->push([
                'tema' => '01 min. Palabras de introducción (Presidente)',
                'asignado' => '',
                'ayudante' => $programa->nombre_presidencia ?? 'Sin asignar'
            ]);

            if ($programa->partes && count($programa->partes) > 0) {
                // Agrupar partes por sección
                $tesorosBiblia = collect($programa->partes)->filter(function($parte) {
                    return $parte->seccion_id == 1;
                });

                $mejoresMaestros = collect($programa->partes)->filter(function($parte) {
                    return $parte->seccion_id == 2;
                });

                $mejoresMaestrosPrincipal = $mejoresMaestros->filter(function($parte) {
                    return $parte->sala_id == 1;
                });

                $mejoresMaestrosAuxiliar = $mejoresMaestros->filter(function($parte) {
                    return $parte->sala_id == 2;
                });

                $mejoresMaestrosAuxiliar2 = $mejoresMaestros->filter(function($parte) {
                    return $parte->sala_id == 3;
                });

                $vidaCristiana = collect($programa->partes)->filter(function($parte) {
                    return $parte->seccion_id == 3;
                });

                // TESOROS DE LA BIBLIA
                if ($tesorosBiblia->count() > 0) {
                    $data->push(['TESOROS DE LA BIBLIA', '']);
                    $contador = 1;
                    foreach ($tesorosBiblia as $parte) {
                        $tiempo = str_pad($parte->tiempo ?? '', 2, '0', STR_PAD_LEFT);
                        $contenido = "{$tiempo} min. {$contador}) " . ($parte->tema ?? $parte->parte_nombre);
                        $data->push([
                            'tema' => $contenido,
                            'asignado' => '',
                            'ayudante' => $parte->encargado_nombre ?? 'Sin asignar',
                        ]);
                        $contador++;
                    }
                    for($i = $contador; $i <= 4; $i++){
                        $data->push(['', '', '']);
                    }
                }

                // SEAMOS MEJORES MAESTROS - SALA PRINCIPAL
                if ($mejoresMaestrosPrincipal->count() > 0) {
                    $data->push(['SEAMOS MEJORES MAESTROS - SALA PRINCIPAL', '']);
                    $contador = 4;
                    foreach ($mejoresMaestrosPrincipal as $parte) {
                        $tiempo = str_pad($parte->tiempo ?? '', 2, '0', STR_PAD_LEFT);
                        $contenido = "{$tiempo} min. {$contador}) " . ($parte->tema ?? $parte->parte_nombre);
                        $asignado = $parte->encargado_nombre ?? 'Sin asignar';
                        $ayudante = $parte->ayudante_nombre ?? 'Sin asignar';
                        if($parte->encargado_nombre && !$parte->ayudante_nombre){
                            $ayudante = $asignado;
                            $asignado = '';
                        }
                        $data->push([
                            'tema' => $contenido,
                            'asignado' => $asignado,
                            'ayudante' => "|".$ayudante
                        ]);
                        $contador++;
                    }
                    for($i = $contador; $i <= 7; $i++){
                        $data->push(['', '', '']);
                    }
                }

                // SEAMOS MEJORES MAESTROS - SALA AUXILIAR 1
                if ($mejoresMaestrosAuxiliar->count() > 0) {
                    $data->push(['SEAMOS MEJORES MAESTROS - SALA AUXILIAR 1', '']);
                    $contador = 4;
                    foreach ($mejoresMaestrosAuxiliar as $parte) {
                        $tiempo = str_pad($parte->tiempo ?? '', 2, '0', STR_PAD_LEFT);
                        $contenido = "{$tiempo} min. {$contador}) " . ($parte->tema ?? $parte->parte_nombre);
                        $asignado = $parte->encargado_nombre ?? 'Sin asignar';
                        $ayudante = $parte->ayudante_nombre ?? 'Sin asignar';
                         if($parte->encargado_nombre && !$parte->ayudante_nombre){
                            $ayudante = $asignado;
                            $asignado = '';
                        }
                        $data->push([
                            'tema' => $contenido,
                            'asignado' => $asignado,
                            'ayudante' => "|".$ayudante
                        ]);
                        $contador++;
                    }
                    for($i = $contador; $i <= 7; $i++){
                        $data->push(['', '', '']);
                    }
                }

                // SEAMOS MEJORES MAESTROS - SALA AUXILIAR 2
                if ($mejoresMaestrosAuxiliar2->count() > 0) {
                    $data->push(['SEAMOS MEJORES MAESTROS - SALA AUXILIAR 2', '']);
                    $contador = 4;
                    foreach ($mejoresMaestrosAuxiliar2 as $parte) {
                        $tiempo = str_pad($parte->tiempo ?? '', 2, '0', STR_PAD_LEFT);
                        $contenido = "{$tiempo} min. {$contador}) " . ($parte->tema ?? $parte->parte_nombre);
                        $asignado = $parte->encargado_nombre ?? 'Sin asignar';
                        $ayudante = $parte->ayudante_nombre ?? 'Sin asignar';
                         if($parte->encargado_nombre && !$parte->ayudante_nombre){
                            $ayudante = $asignado;
                            $asignado = '';
                        }
                        $data->push([
                            'tema' => $contenido,
                            'asignado' => $asignado,
                            'ayudante' => "|".$ayudante
                        ]);
                        $contador++;
                    }
                    for($i = $contador; $i <= 7; $i++){
                        $data->push(['', '', '']);
                    }
                }

                // NUESTRA VIDA CRISTIANA
                if ($vidaCristiana->count() > 0) {
                    $data->push(['NUESTRA VIDA CRISTIANA', '']);

                    // Canción intermedia
                    $data->push([
                        'tema' => 'Canción: ' . ($programa->cancion_en ?? 'Sin asignar'),
                        'asignado' => '',
                        'ayudante' => ''
                    ]);

                    $contador = $mejoresMaestrosPrincipal->count() + 4;
                    $partesArray = $vidaCristiana->values();
                    $indiceParte=0;
                    $esVisitaSC = false;
                    foreach ($vidaCristiana as $index => $parte) {
                        $tiempo = str_pad($parte->tiempo ?? '', 2, '0', STR_PAD_LEFT);
                        $contenido = $parte->parte_id != 24 ? "{$tiempo} min. {$contador}) " . ($parte->tema ?? $parte->parte_nombre) : $parte->parte_nombre;
                        //Si es parte 25 guardar: tema,asignado='',ayudante=encargado_nombre en variable, en caso contrario push y contar
                        if($parte->parte_id == 25){
                            $sc=[
                                'tema' => "{$tiempo} min. " . ($parte->tema ?? $parte->parte_nombre),
                                'asignado' => '',
                                'ayudante' => $parte->encargado_nombre ?? 'Sin asignar'
                            ];
                            $esVisitaSC = true;
                        }else{
                            $data->push([
                                'tema' => $contenido,
                                'asignado' => '',
                                'ayudante' => $parte->encargado_nombre ?? 'Sin asignar'
                            ]);
                        }
                        $indiceParte++;
                        $contador++;
                    }
                    for($i = $indiceParte; $i <= 3; $i++){
                        $data->push(['', '', '']);
                    }
                }
                if($programa->nombre_presidencia){
                    // Palabras de conclusión
                    $data->push([
                        'tema' => '03 min. Palabras de conclusión (Presidente)',
                        'asignado' => '',
                        'ayudante' => $programa->nombre_presidencia ?? 'Sin asignar'
                    ]);
                    if($esVisitaSC){
                        $data->push($sc);
                    }
                }else{
                    $data->push(['', '', '']);
                }
                // Canción final y orador final
                if ($programa->cancion_post) {
                    $data->push([
                        'tema' => 'Canción: ' . ($programa->cancion_post ?? 'Sin asignar'),
                        'asignado' => 'ORADOR FINAL: ',
                        'ayudante' => ($programa->nombre_orador_final ?? 'Sin asignar')
                    ]);
                }else{
                    // Línea en blanco entre programas
                    $data->push(['', '', '']);
                }
            } else {
                // Si no hay partes, mostrar datos básicos
                $data->push([
                    'tema' => 'Presidente',
                    'asignado' => '',
                    'ayudante' => $programa->nombre_presidencia ?? 'Sin asignar'
                ]);
                $data->push([
                    'tema' => 'Orador inicial',
                    'asignado' => '',
                    'ayudante' => $programa->nombre_orador_inicial ?? 'Sin asignar'
                ]);
                if ($programa->nombre_orador_final) {
                    $data->push([
                        'tema' => 'Oración final',
                        'asignado' => '',
                        'ayudante' => $programa->nombre_orador_final ?? 'Sin asignar'
                    ]);
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            '',
            '',
            ''
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 45,
            'B' => 23,
            'C' => 22,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Programas';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Estilos generales. Con border abajo gris claro, border verticales blancos
        $sheet->getStyle('A1:C' . $highestRow)->applyFromArray([
            'borders' => [
                'vertical' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'horizontal' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D9D9D9'],
                ],
            ],
        ]);

        // Procesar cada fila para aplicar estilos
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            $sheet->getRowDimension($row)->setRowHeight(15);
            // Título de la congregación (primera, tercera, quinta.... fila)
            if ($row == 1 || strpos($cellValue, 'PROGRAMA NUESTRA VIDA CRISTIANA') !== false) {
                $sheet->getRowDimension($row)->setRowHeight(45);
                $sheet->mergeCells('A' . $row . ':C' . $row);
                $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFFFF'],
                    ],
                    'borders' => [
                        'top' => [ // 👈 solo borde superior
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'FFFFFF'], // blanco (hex)
                        ],
                    ],
                ]);
            }
            // Fecha del programa. Expresion regular para fechas=$cellValue, ejemplo: lunes 5 de noviembre de 2024
            elseif (preg_match('/^(lunes|martes|miércoles|jueves|viernes|sábado|domingo) \d{1,2} de (enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre) de \d{4}$/', $cellValue)) {
                $sheet->mergeCells('A' . $row . ':C' . $row);
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '666666'],
                    ],
                ]);
            }
            // Encabezados de sección
            elseif (strpos($cellValue, 'TESOROS DE LA BIBLIA') !== false) {
                $sheet->mergeCells('A' . $row . ':C' . $row);
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'A2C4C9'],
                    ],
                ]);
            }
            elseif (strpos($cellValue, 'SEAMOS MEJORES MAESTROS') !== false) {
                $sheet->mergeCells('A' . $row . ':C' . $row);
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFE599'],
                    ],
                ]);
            }
            elseif (strpos($cellValue, 'NUESTRA VIDA CRISTIANA') !== false) {
                $sheet->mergeCells('A' . $row . ':C' . $row);
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EA9999'],
                    ],
                ]);
            }
            // Palabras clave en negrita
            elseif (strpos($cellValue, 'ORADOR') !== false ||
                    strpos($cellValue, 'Presidente') !== false ||
                    strpos($cellValue, 'conclusión') !== false) {
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'font' => [
                        'bold' => false,
                        'size' => 9,
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle('B' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle('C' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            }else {
                // Estilo por defecto para otras filas columna A
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'font' => [
                        'size' => 10,
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                // Estilo por defecto para otras filas columna B font-family: Cascadia Mono
                $sheet->getStyle('B' . $row)->applyFromArray([
                    'font' => [
                        'size' => 8,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle('C' . $row)->applyFromArray([
                    'font' => [
                        'size' => 8,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            }
        }

        return [];
    }
}
