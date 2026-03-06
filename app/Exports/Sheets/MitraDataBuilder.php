<?php
// App/Exports/Sheets/MitraDataBuilder.php
namespace App\Exports\Sheets;

use App\Exports\Styles\StyleTracker;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class MitraDataBuilder
{
    protected Collection $mitras;
    protected StyleTracker $styleTracker;
    protected Collection $data;
    protected int $tahun;
    protected int $startRow;

    public function __construct(
        Collection $mitras,
        StyleTracker $styleTracker,
        Collection $data,
        int $tahun,
        int $startRow
    ) {
        $this->mitras      = $mitras;
        $this->styleTracker = $styleTracker;
        $this->data        = $data;
        $this->tahun       = $tahun;
        $this->startRow    = $startRow;
    }

    public function build(): Collection
    {
        $rows = collect();
        $currentRow = $this->startRow;

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $rows->push($this->buildMonthRow($bulan, $currentRow));
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
        foreach ($this->mitras as $mitra) {
            $rowData = $this->getMitraData($mitra->id, $bulan);
            $row = array_merge($row, $rowData);

            // ✅ Kolom Susut% tetap di posisi ke-4 tiap grup
            $susutCol = Coordinate::stringFromColumnIndex($colIdx + 3);
            $this->styleTracker->addPercentCell("{$susutCol}{$currentRow}");

            $colIdx += 4;
        }

        return $row;
    }

    protected function getMitraData(int $mitraId, int $bulan): array
    {
        // ✅ Lookup by mitra_id, bukan pengangkut_id
        $item = $this->data->first(fn($i) =>
            (int) $i->mitra_id === $mitraId &&
            Carbon::parse($i->bulan)->year === $this->tahun &&
            Carbon::parse($i->bulan)->month === $bulan
        );

        $nk     = (float) ($item->netto_kebun ?? 0);
        $n      = (float) ($item->netto ?? 0);
        $s      = (float) ($item->susut ?? 0);
        $persen = $nk != 0 ? $s / $nk : 0;

        return [$nk ?: null, $n ?: null, $s ?: null, $persen ?: null];
    }
}