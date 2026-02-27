<?php
namespace App\Exports\Styles;

use App\Exports\Styles\StyleTracker;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapSheetStyler
{
    protected Worksheet $sheet;
    protected StyleTracker $tracker;

    public function __construct(Worksheet $sheet, StyleTracker $tracker)
    {
        $this->sheet = $sheet;
        $this->tracker = $tracker;
    }

    public function apply(): void
    {
        $this->sheet->freezePane('C1');
        $this->applyMerges();
        $this->applyNumberFormats();
        $this->applyBorders();
        $this->applyFonts();
        $this->applyTahunStyles();
        $this->applyAlignments();
        $this->applyColumnWidths();
    }

    protected function applyMerges(): void
    {
        foreach ($this->tracker->mergeCells as $range) {
            $this->sheet->mergeCells($range);
        }
    }

    protected function applyNumberFormats(): void
    {
        $highestRow    = $this->sheet->getHighestRow();
        $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
            $this->sheet->getHighestColumn()
        );

        for ($row = 1; $row <= $highestRow; $row++) {
            // Mulai dari kolom C (index 3)
            for ($colIdx = 3; $colIdx <= $highestColIdx; $colIdx++) {
                $col   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $cell  = $this->sheet->getCell("{$col}{$row}");
                $value = $cell->getValue();

                if (is_numeric($value) && $value !== null && $value !== '') {
                    $cell->getStyle()->getNumberFormat()->setFormatCode('#,##0');
                }
            }
        }

        // ✅ Format persen yang benar: nilai desimal * 100 ditampilkan sebagai %
        foreach ($this->tracker->percentCells as $cell) {
            $this->sheet->getStyle($cell)
                ->getNumberFormat()
                ->setFormatCode('0.00%');
        }
    }

    protected function applyBorders(): void
    {
        foreach ($this->tracker->borderRanges as $range) {
            $this->sheet->getStyle($range)
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
        }
        
        foreach ($this->tracker->redBorderColumns as $border) {
            $this->sheet->getStyle($border['range'])->applyFromArray([
                'borders' => [
                    $border['side'] => [
                        'borderStyle' => Border::BORDER_THICK,
                        'color' => ['rgb' => 'FF0000'],
                    ],
                ],
            ]);
        }
    }

    protected function applyFonts(): void
    {
        foreach ($this->tracker->headerCells as $range) {
            $this->sheet->getStyle($range)->getFont()->setBold(true);
        }
    }

    protected function applyTahunStyles(): void
    {
        foreach ($this->tracker->tahunCells as $range) {
            $this->sheet->getStyle($range)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '92D050'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            
            if (preg_match('/(\d+)/', $range, $matches)) {
                $this->sheet->getRowDimension($matches[1])->setRowHeight(25);
            }
        }
    }

    protected function applyAlignments(): void
    {
        $highestRow    = $this->sheet->getHighestRow();
        $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
            $this->sheet->getHighestColumn()
        );

        foreach ($this->tracker->mergeCells as $range) {
            $this->sheet->getStyle($range)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }

        for ($row = 1; $row <= $highestRow; $row++) {
            // ✅ Gunakan index numerik, aman di PHP 8+
            for ($colIdx = 1; $colIdx <= $highestColIdx; $colIdx++) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                if ($col !== 'B') {
                    $this->sheet->getStyle("{$col}{$row}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            }
        }
    }

    protected function applyColumnWidths(): void
    {
        $this->sheet->getColumnDimension('A')->setWidth(5);
        
        $highestColumn = $this->sheet->getHighestColumn();
        foreach (range('B', $highestColumn) as $col) {
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}