<?php
namespace App\Exports\Sheets;

use App\Exports\Styles\StyleTracker;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PengangkutHeaderBuilder
{
    protected Collection $pengangkuts;
    protected StyleTracker $styleTracker;
    protected int $startRow;

    public function __construct(Collection $pengangkuts, StyleTracker $styleTracker, int $startRow)
    {
        $this->pengangkuts = $pengangkuts;
        $this->styleTracker = $styleTracker;
        $this->startRow = $startRow;
    }

    public function build(): array
    {
        $header1 = ['No', 'Bulan'];
        $header2 = ['', ''];
        
        $this->addNoAndBulanMerge();
        
        $colIndex = 3;
        foreach ($this->pengangkuts as $pengangkut) {
            $this->addPengangkutHeader($header1, $header2, $pengangkut, $colIndex);
            $colIndex += 4;
        }
        
        return ['header1' => $header1, 'header2' => $header2];
    }

    protected function addNoAndBulanMerge(): void
    {
        $mergeA = "A{$this->startRow}:A" . ($this->startRow + 1);
        $mergeB = "B{$this->startRow}:B" . ($this->startRow + 1);
        
        $this->styleTracker->addMerge($mergeA);
        $this->styleTracker->addMerge($mergeB);
        $this->styleTracker->addHeaderCell($mergeA);
        $this->styleTracker->addHeaderCell($mergeB);
    }

    protected function addPengangkutHeader(array &$header1, array &$header2, $pengangkut, int $colIndex): void
    {
        $header1 = array_merge($header1, [$pengangkut->kode, '', '', '']);
        $header2 = array_merge($header2, ['Netto Kebun', 'Netto', 'Susut', 'Susut%']);
        
        $startCol = Coordinate::stringFromColumnIndex($colIndex);
        $endCol = Coordinate::stringFromColumnIndex($colIndex + 3);
        
        $mergeRange = "{$startCol}{$this->startRow}:{$endCol}{$this->startRow}";
        $this->styleTracker->addMerge($mergeRange);
        $this->styleTracker->addHeaderCell($mergeRange);
        
        for ($i = 0; $i < 4; $i++) {
            $col = Coordinate::stringFromColumnIndex($colIndex + $i);
            $this->styleTracker->addHeaderCell($col . ($this->startRow + 1));
        }
        
        $this->addRedBorders($startCol, $endCol);
    }

    protected function addRedBorders(string $startCol, string $endCol): void
    {
        $borderRange = "{$startCol}{$this->startRow}:{$startCol}" . ($this->startRow + 13);
        $this->styleTracker->addRedBorder($borderRange, 'left');
        
        $borderRange = "{$endCol}{$this->startRow}:{$endCol}" . ($this->startRow + 13);
        $this->styleTracker->addRedBorder($borderRange, 'right');
    }
}