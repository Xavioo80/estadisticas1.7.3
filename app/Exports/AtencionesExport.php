<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AtencionesExport implements FromCollection, WithHeadings, WithStyles
{
    protected $data;
    protected $fechasObjs;
    protected $ano;
    protected $mes;

    public function __construct($data, $fechasObjs, $ano, $mes)
    {
        $this->data = $data;
        $this->fechasObjs = $fechasObjs;
        $this->ano = $ano;
        $this->mes = $mes;
    }

    private function getEpiWeek($date) {
        $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) to 6 (Saturday)
        $wednesday = $date->copy()->addDays(3 - $dayOfWeek)->startOfDay();
        $year = $wednesday->year;
        $jan4 = \Carbon\Carbon::create($year, 1, 4)->startOfDay();
        $wednesdayOfJan4 = $jan4->copy()->addDays(3 - $jan4->dayOfWeek)->startOfDay();
        $diffDays = (int) round(($wednesday->timestamp - $wednesdayOfJan4->timestamp) / 86400);
        return (int) round($diffDays / 7) + 1;
    }

    public function collection()
    {
        $collection = collect();
        
        $weeks = [];
        foreach($this->fechasObjs as $fo) {
            $w = $this->getEpiWeek($fo['obj']);
            if(!isset($weeks[$w])) $weeks[$w] = [];
            $weeks[$w][] = $fo;
        }

        foreach ($this->data as $medico => $mD) {
            $row = [$medico];
            $monthTotal = 0;
            
            foreach ($weeks as $weekNum => $days) {
                $weekTotal = 0;
                foreach ($days as $day) {
                    $val = $mD['dates'][$day['fecha']] ?? 0;
                    $row[] = $val > 0 ? $val : 0;
                    $weekTotal += $val;
                    $monthTotal += $val;
                }
                $row[] = $weekTotal;
            }
            $row[] = $monthTotal;
            $collection->push($row);
        }

        // Fila de Totales
        $totalsRow = ['TOTAL GENERAL'];
        $grandTotalMonth = 0;
        foreach ($weeks as $weekNum => $days) {
            $weekGrandTotal = 0;
            foreach ($days as $day) {
                $colTotal = 0;
                foreach ($this->data as $mD) {
                    $colTotal += ($mD['dates'][$day['fecha']] ?? 0);
                }
                $totalsRow[] = $colTotal;
                $weekGrandTotal += $colTotal;
                $grandTotalMonth += $colTotal;
            }
            $totalsRow[] = $weekGrandTotal;
        }
        $totalsRow[] = $grandTotalMonth;
        $collection->push($totalsRow);

        return $collection;
    }

    public function headings(): array
    {
        $h = ['MÉDICO / PROFESIONAL'];
        
        $weeks = [];
        foreach($this->fechasObjs as $fo) {
            $w = $this->getEpiWeek($fo['obj']);
            if(!isset($weeks[$w])) $weeks[$w] = [];
            $weeks[$w][] = $fo;
        }

        foreach ($weeks as $weekNum => $days) {
            foreach ($days as $day) {
                $h[] = $day['fecha'];
            }
            $h[] = 'SEM ' . $weekNum;
        }
        $h[] = 'TOTAL MES';
        return $h;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->data) + 2;
        $columnCount = count($this->headings());
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
        
        // Rotar fechas y SEM
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
