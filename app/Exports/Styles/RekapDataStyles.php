<?php
// app/Exports/Styles/RekapDataStyles.php
namespace App\Exports\Styles;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapDataStyles
{
    public static function headerStyle(Worksheet $sheet): array
    {
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->freezePane('A1');

        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical'   => 'center',
                    'wrapText'   => true,
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => 'A9D08E'],
                ],
            ],
        ];
    }

    public static function registerEvents($export): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) use ($export) {
                $sheet = $event->sheet->getDelegate();
                
                self::applyColumnWidths($sheet);
                self::applyAutoSize($sheet);
                self::formatSusutColumn($sheet);
                self::formatDateColumn($sheet);
                self::applyBorders($sheet);
                self::insertMonthlySubtotals($sheet, $export);
            }
        ];
    }

    protected static function applyColumnWidths(Worksheet $sheet): void
    {
        foreach (['G','H','I','J','K','L'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(12);
        }
        foreach (['M','N'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(10);
        }
    }

    protected static function applyAutoSize(Worksheet $sheet): void
    {
        $highestCol = $sheet->getHighestColumn();
        $fixed = ['G','H','I','J','K','L','M','N'];
        
        foreach (range('A', $highestCol) as $col) {
            if (!in_array($col, $fixed)) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }
    }

    protected static function formatSusutColumn(Worksheet $sheet): void
    {
        $highestRow = $sheet->getHighestRow();
        
        for ($row = 2; $row <= $highestRow; $row++) {
            $cell = $sheet->getCell("M{$row}");
            if ($cell->getValue() === null || $cell->getValue() === '') {
                $cell->setValueExplicit(0, DataType::TYPE_NUMERIC);
            }
        }
    }

    protected static function formatDateColumn(Worksheet $sheet): void
    {
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("B2:B{$highestRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }

    protected static function applyBorders(Worksheet $sheet): void
    {
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        
        $sheet->getStyle("A1:{$highestCol}{$highestRow}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D9D9D9'],
                    ],
                ],
            ]);
    }

    protected static function insertMonthlySubtotals(Worksheet $sheet, $export): void
    {
        $export->finalizeMonthlyData();
        $monthRows = $export->getMonthRows();
        $highestCol = $sheet->getHighestColumn();
        $rowOffset = 0;

        foreach ($monthRows as $monthData) {
            $insertAt = $monthData['end'] + 1 + $rowOffset;
            $sheet->insertNewRowBefore($insertAt, 1);

            $start = $monthData['start'] + $rowOffset;
            $end   = $monthData['end'] + $rowOffset;

            self::setSubtotalFormulas($sheet, $insertAt, $start, $end);
            self::styleSubtotalRow($sheet, $insertAt, $highestCol);

            $rowOffset++;
        }
    }

    protected static function setSubtotalFormulas(Worksheet $sheet, int $row, int $start, int $end): void
    {
        $sheet->setCellValue("I{$row}", "=SUM(I{$start}:I{$end})");
        $sheet->setCellValue("L{$row}", "=SUM(L{$start}:L{$end})");
        $sheet->setCellValue("M{$row}", "=SUM(M{$start}:M{$end})");
        $sheet->setCellValue("N{$row}", "=IF(I{$row}=0,0,M{$row}/I{$row}*100)");
    }

    protected static function styleSubtotalRow(Worksheet $sheet, int $row, string $highestCol): void
    {
        $sheet->getStyle("A{$row}:{$highestCol}{$row}")
            ->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => 'FFFF00'],
                ],
            ]);
    }
}