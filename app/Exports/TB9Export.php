<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMapping;

class TB9Export implements FromCollection, WithHeadings, WithStyles
{
    protected $data;
    protected $headers;
    protected $ano;
    protected $mes;
    protected $jornada;
    protected $profesiones;

    public function __construct($data, $headers, $ano, $mes, $jornada, $profesiones = [])
    {
        $this->data = $data;
        $this->headers = $headers;
        $this->ano = $ano;
        $this->mes = $mes;
        $this->jornada = $jornada;
        $this->profesiones = $profesiones;
    }

    public function collection()
    {
        $collection = collect();

        foreach ($this->data as $rango => $mD) {
            $row = [
                'Rango' => $rango
            ];
            
            $rowTotal = 0;
            foreach ($this->headers as $dateStr) {
                $val = $mD['dates'][$dateStr] ?? 0;
                $row[$dateStr] = $val;
                $rowTotal += $val;
            }
            
            $row['Total'] = $rowTotal;
            $collection->push($row);
        }

        // Fila de Totales
        $totalsRow = ['Total General'];
        $grandTotal = 0;
        foreach ($this->headers as $dateStr) {
            $colTotal = 0;
            foreach ($this->data as $mD) {
                $colTotal += ($mD['dates'][$dateStr] ?? 0);
            }
            $totalsRow[] = $colTotal;
            $grandTotal += $colTotal;
        }
        $totalsRow[] = $grandTotal;
        $collection->push($totalsRow);

        return $collection;
    }

    public function headings(): array
    {
        $h = ['RANGO DE EDAD'];
        foreach ($this->headers as $dateStr) {
            $h[] = $dateStr;
        }
        $h[] = 'TOTAL MES';
        return $h;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->data) + 2; // +1 for header, +1 for total row
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headers) + 2);
        $fullRange = 'A1:' . $lastColLetter . $lastRow;

        // Estilo general: Bordes y Alineación Central
        $sheet->getStyle($fullRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Alinear izquierda la primera columna (descripciones)
        $sheet->getStyle('A1:A' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Estilo del Encabezado
        $headerRange = 'A1:' . $lastColLetter . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'],
            ],
        ]);

        // Rotación de Fechas (Columnas interactivas entre A y la última)
        $sheet->getRowDimension(1)->setRowHeight(100); // Espacio para texto vertical
        
        for ($i = 2; $i <= count($this->headers) + 1; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getStyle($colLetter . '1')->getAlignment()->setTextRotation(90);
            $sheet->getColumnDimension($colLetter)->setWidth(5);
        }

        // Ajustar anchos de columnas extremos
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension($lastColLetter)->setWidth(15);

        // Estilo de la Fila de Totales
        $totalsRange = 'A' . $lastRow . ':' . $lastColLetter . $lastRow;
        $sheet->getStyle($totalsRange)->getFont()->setBold(true);

        return [];
    }
}
