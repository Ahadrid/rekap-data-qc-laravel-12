<?php
namespace App\Exports\Sheets;

use App\Exports\Styles\StyleTracker;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PengangkutDataBuilder
{
    protected Collection $pengangkuts;
    protected StyleTracker $styleTracker;
    protected Collection $data;
    protected int $tahun;
    protected int $startRow;

    public function __construct(
        Collection $pengangkuts,
        StyleTracker $styleTracker,
        Collection $data,
        int $tahun,
        int $startRow
    ) {
        $this->pengangkuts = $pengangkuts;
        $this->styleTracker = $styleTracker;
        $this->data = $data;
        $this->tahun = $tahun;
        $this->startRow = $startRow;
    }

    public function build(): Collection
    {
        $rows = collect();
        $currentRow = $this->startRow;
        
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $row = $this->buildMonthRow($bulan, $currentRow);
            $rows->push($row);
            $currentRow++;
        }
        
        return $rows;
    }

    protected function buildMonthRow(int $bulan, int $currentRow): array
    {
        $row = [
            $bulan,
            Carbon::create($this->tahun, $bulan)->locale('id')->translatedFormat('F'),
        ];

        $colIdx = 3;
        foreach ($this->pengangkuts as $pengangkut) {
            $rowData = $this->getPengangkutData($pengangkut->id, $bulan);
            $row = array_merge($row, $rowData);
            
            $susutCol = Coordinate::stringFromColumnIndex($colIdx + 3);
            $this->styleTracker->addPercentCell("{$susutCol}{$currentRow}");
            
            $colIdx += 4;
        }
        
        return $row;
    }

    protected function getPengangkutData(int $pengangkutId, int $bulan): array
    {
        $item = $this->data->first(fn ($i) =>
            $i->pengangkut_id === $pengangkutId &&
            Carbon::parse($i->bulan)->year === $this->tahun &&
            Carbon::parse($i->bulan)->month === $bulan
        );

        $nk = $item->netto_kebun ?? 0;
        $n  = $item->netto ?? 0;
        $s  = $item->susut ?? 0;
        $persen = $nk > 0 ? ($s / $nk) * 100 : 0;
        
        return [$nk, $n, $s, $persen];
    }
}