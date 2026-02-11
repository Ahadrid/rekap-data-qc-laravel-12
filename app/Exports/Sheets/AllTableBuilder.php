<?php
namespace App\Exports\Sheets;

use App\Exports\Styles\StyleTracker;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AllTableBuilder
{
    protected StyleTracker $styleTracker;
    protected Collection $data;
    protected int $tahun;
    protected int $startRow;

    public function __construct(StyleTracker $styleTracker, Collection $data, int $tahun, int &$startRow)
    {
        $this->styleTracker = $styleTracker;
        $this->data = $data;
        $this->tahun = $tahun;
        $this->startRow = &$startRow;
    }

    public function build(): Collection
    {
        $rows = collect();
        
        $rows->push(array_fill(0, 6, null));
        $this->startRow++;
        
        $this->addHeaders($rows);
        $this->addMonthlyData($rows);
        
        return $rows;
    }

    protected function addHeaders(Collection &$rows): void
    {
        $headerRow = $this->startRow;
        $rows->push(['No', 'Bulan', 'ALL', '', '', '']);
        
        $this->styleTracker->addMerge("A{$this->startRow}:A" . ($this->startRow + 1));
        $this->styleTracker->addMerge("B{$this->startRow}:B" . ($this->startRow + 1));
        $this->styleTracker->addMerge("C{$this->startRow}:F{$this->startRow}");
        
        $this->styleTracker->addHeaderCell("A{$this->startRow}:A" . ($this->startRow + 1));
        $this->styleTracker->addHeaderCell("B{$this->startRow}:B" . ($this->startRow + 1));
        $this->styleTracker->addHeaderCell("C{$this->startRow}:F{$this->startRow}");
        
        $this->startRow++;
        
        $rows->push(['', '', 'Netto Kebun', 'Netto', 'Susut', 'Susut%']);
        
        foreach (['C', 'D', 'E', 'F'] as $col) {
            $this->styleTracker->addHeaderCell("{$col}{$this->startRow}");
        }
        
        $this->styleTracker->addRedBorder("C{$headerRow}:C" . ($this->startRow + 12), 'left');
        $this->styleTracker->addRedBorder("F{$headerRow}:F" . ($this->startRow + 12), 'right');
        
        $allEndRow = $this->startRow + 12;
        $this->styleTracker->addBorderRange("A{$headerRow}:F{$allEndRow}");
        
        $this->startRow++;
    }

    protected function addMonthlyData(Collection &$rows): void
    {
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $items = $this->data->filter(fn ($i) =>
                Carbon::parse($i->bulan)->year === $this->tahun &&
                Carbon::parse($i->bulan)->month === $bulan
            );

            $nk = $items->sum('netto_kebun');
            $n  = $items->sum('netto');
            $s  = $items->sum('susut');
            $persen = $nk > 0 ? ($s / $nk) * 100 : 0;
            
            $rows->push([
                $bulan,
                Carbon::create($this->tahun, $bulan)->locale('id')->translatedFormat('F'),
                $nk,
                $n,
                $s,
                $persen,
            ]);
            
            $this->styleTracker->addPercentCell("F{$this->startRow}");
            $this->startRow++;
        }
    }
}