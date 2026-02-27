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
    protected int $startRow; // by VALUE, tidak by reference

    public function __construct(StyleTracker $styleTracker, Collection $data, int $tahun, int $startRow)
    {
        $this->styleTracker = $styleTracker;
        $this->data         = $data;
        $this->tahun        = $tahun;
        $this->startRow     = $startRow; // tidak pakai &, tidak increment internal
    }

    public function build(): Collection
    {
        $rows = collect();
        $this->addHeaders($rows);
        $this->addMonthlyData($rows);
        return $rows;
    }

    protected function addHeaders(Collection &$rows): void
    {
        $r = $this->startRow; // header1 row
        
        $rows->push(['No', 'Bulan', 'ALL', '', '', '']);

        $this->styleTracker->addMerge("A{$r}:A" . ($r + 1));
        $this->styleTracker->addMerge("B{$r}:B" . ($r + 1));
        $this->styleTracker->addMerge("C{$r}:F{$r}");

        $this->styleTracker->addHeaderCell("A{$r}:A" . ($r + 1));
        $this->styleTracker->addHeaderCell("B{$r}:B" . ($r + 1));
        $this->styleTracker->addHeaderCell("C{$r}:F{$r}");

        $subR = $r + 1; // header2 row
        $rows->push(['', '', 'Netto Kebun', 'Netto', 'Susut', 'Susut%']);

        foreach (['C', 'D', 'E', 'F'] as $col) {
            $this->styleTracker->addHeaderCell("{$col}{$subR}");
        }

        $dataEndRow = $r + 1 + 12; // subheader + 12 bulan
        $this->styleTracker->addRedBorder("C{$r}:C{$dataEndRow}", 'left');
        $this->styleTracker->addRedBorder("F{$r}:F{$dataEndRow}", 'right');
        $this->styleTracker->addBorderRange("A{$r}:F{$dataEndRow}");
    }

    protected function addMonthlyData(Collection &$rows): void
    {
        // Data mulai di startRow + 2 (setelah header1 dan header2)
        $dataStart = $this->startRow + 2;

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $items = $this->data->filter(fn ($i) =>
                Carbon::parse($i->bulan)->year  === $this->tahun &&
                Carbon::parse($i->bulan)->month === $bulan
            );

            $nk     = (float) $items->sum('netto_kebun');
            $n      = (float) $items->sum('netto');
            $s      = (float) $items->sum('susut');
            $persen = $nk != 0 ? $s / $nk : 0;

            $rows->push([
                $bulan,
                Carbon::create($this->tahun, $bulan)->locale('id')->translatedFormat('F'),
                $nk ?: null,
                $n  ?: null,
                $s  ?: null,
                $persen ?: null,
            ]);

            // Posisi row dihitung dari offset, bukan increment
            $this->styleTracker->addPercentCell('F' . ($dataStart + $bulan - 1));
        }
    }
}