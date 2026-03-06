<?php
namespace App\Exports\Sheets;

use App\Exports\Styles\StyleTracker;
use App\Models\RekapData;
use Illuminate\Support\Collection;

class RekapSupplierLuarSheetBuilder extends RekapSheetBuilder
{
    /**
     * Override template method — pakai MitraHeaderBuilder & MitraDataBuilder.
     */
    protected function addEntityTable(Collection &$rows, int $tahun, int $kolomCount): void
    {
        $startRow = $this->currentRow;

        $headerBuilder = new MitraHeaderBuilder($this->pengangkuts, $this->styleTracker, $this->currentRow);
        $headers = $headerBuilder->build();

        $rows->push($headers['header1']);
        $this->currentRow++;
        $rows->push($headers['header2']);
        $this->currentRow++;

        $dataBuilder = new MitraDataBuilder(
            $this->pengangkuts,
            $this->styleTracker,
            $this->data,
            $tahun,
            $this->currentRow
        );

        $dataRows = $dataBuilder->build();
        foreach ($dataRows as $row) {
            $rows->push($row);
            $this->currentRow++;
        }

        $endRow = $this->currentRow - 1;
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($kolomCount);
        $this->styleTracker->addBorderRange("A{$startRow}:{$lastCol}{$endRow}");
    }

    /**
     * Override loadData — groupBy mitra_id, tanpa filter mode.
     */
    protected function loadData(): Collection
    {
        return RekapData::query()
            ->when(!empty($this->filters['produk_id']),
                fn($q) => $q->where('produk_id', $this->filters['produk_id'])
            )
            ->when(
                !empty($this->filters['tanggal_mulai']) && !empty($this->filters['tanggal_akhir']),
                fn($q) => $q->whereBetween('tanggal', [
                    $this->filters['tanggal_mulai'],
                    $this->filters['tanggal_akhir'],
                ])
            )
            ->whereHas('mitra', fn($q) => $q->where('tipe_mitra', 'suplier_luar'))
            ->selectRaw("
                DATE_TRUNC('month', tanggal) as bulan,
                mitra_id,
                SUM(netto_kebun) as netto_kebun,
                SUM(netto) as netto,
                SUM(susut) as susut
            ")
            ->groupByRaw("DATE_TRUNC('month', tanggal)")
            ->groupBy('mitra_id')
            ->get();
    }
}