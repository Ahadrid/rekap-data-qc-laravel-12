<?php
namespace App\Exports\Styles;

class StyleTracker
{
    public array $mergeCells = [];
    public array $borderRanges = [];
    public array $percentCells = [];
    public array $headerCells = [];
    public array $redBorderColumns = [];
    public array $tahunCells = [];

    public function addMerge(string $range): void
    {
        $this->mergeCells[] = $range;
    }

    public function addBorderRange(string $range): void
    {
        $this->borderRanges[] = $range;
    }

    public function addPercentCell(string $cell): void
    {
        $this->percentCells[] = $cell;
    }

    public function addHeaderCell(string $cell): void
    {
        $this->headerCells[] = $cell;
    }

    public function addRedBorder(string $range, string $side): void
    {
        $this->redBorderColumns[] = ['range' => $range, 'side' => $side];
    }

    public function addTahunCell(string $range): void
    {
        $this->tahunCells[] = $range;
    }
}