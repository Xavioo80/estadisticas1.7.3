<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImplantesExport implements FromCollection, WithHeadings, WithStyles
{
    protected $data;
    protected $headers;
    protected $ano;
    protected $mes;

    public function __construct($data, $headers, $ano, $mes)
    {
        $this->data = $data;
        $this->headers = $headers;
        $this->ano = $ano;
        $this->mes = $mes;
    }

    public function collection()
    {
        $collection = collect();

        foreach ($this->data as $medico => $mD) {
            $row = [$medico];
            $rowTotal = 0;
            foreach ($this->headers as $dateStr) {
                $val = $mD['dates'][$dateStr] ?? 0;
                $row[] = $val;
                $rowTotal += $val;
            }
            $row[] = $rowTotal;
            $collection->push($row);
        }

        // Fila de Totales
        $totalsRow = ['TOTAL GENERAL'];
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
        $h = ['MÉDICO / PROFESIONAL'];
        foreach ($this->headers as $dateStr) {
            $h[] = $dateStr;
        }
        $h[] = 'TOTAL MES';
        return $h;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->data) + 2;
        $columnCount = count($this->headers) + 2;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount);
        $fullRange = 'A1:' . $lastColLetter . $lastRow;

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

        $sheet->getStyle('A1:A' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $headerRange = 'A1:' . $lastColLetter . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(100);
        
        for ($i = 2; $i <= $columnCount; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getStyle($colLetter . '1')->getAlignment()->setTextRotation(90);
            $sheet->getColumnDimension($colLetter)->setWidth(5);
        }

        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension($lastColLetter)->setWidth(15);

        $totalsRange = 'A' . $lastRow . ':' . $lastColLetter . $lastRow;
        $sheet->getStyle($totalsRange)->getFont()->setBold(true);

        return [];
    }
}
