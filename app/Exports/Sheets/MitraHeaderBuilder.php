<?php
// App/Exports/Sheets/MitraHeaderBuilder.php
namespace App\Exports\Sheets;

use App\Exports\Styles\StyleTracker;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class MitraHeaderBuilder
{
    protected Collection $mitras;
    protected StyleTracker $styleTracker;
    protected int $startRow;

    public function __construct(Collection $mitras, StyleTracker $styleTracker, int $startRow)
    {
        $this->mitras = $mitras;
        $this->styleTracker = $styleTracker;
        $this->startRow = $startRow;
    }

    public function build(): array
    {
        $header1 = ['No', 'Bulan'];
        $header2 = ['', ''];

        $this->addNoAndBulanMerge();

        $colIndex = 3;
        foreach ($this->mitras as $mitra) {
            $this->addMitraHeader($header1, $header2, $mitra, $colIndex);
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

    protected function addMitraHeader(array &$header1, array &$header2, $mitra, int $colIndex): void
    {
        // ✅ Pakai kode_mitra sebagai label header kolom
        $header1 = array_merge($header1, [$mitra->kode_mitra, '', '', '']);
        $header2 = array_merge($header2, ['Netto Kebun', 'Netto', 'Susut', 'Susut%']);

        $startCol = Coordinate::stringFromColumnIndex($colIndex);
        $endCol   = Coordinate::stringFromColumnIndex($colIndex + 3);

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
        $bottomRow = $this->startRow + 13;

        $this->styleTracker->addRedBorder("{$startCol}{$this->startRow}:{$startCol}{$bottomRow}", 'left');
        $this->styleTracker->addRedBorder("{$endCol}{$this->startRow}:{$endCol}{$bottomRow}", 'right');
    }
}