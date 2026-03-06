<?php
// App/Exports/Sheets/RekapSupplierLuarSheetBuilder.php
namespace App\Exports\Sheets;

use App\Exports\Styles\StyleTracker;
use App\Models\RekapData;
use Illuminate\Support\Collection;

class RekapSupplierLuarSheetBuilder extends RekapSheetBuilder
{
    // $this->pengangkuts di parent akan berisi Collection of Mitra
    // tidak ada perubahan constructor — reuse sepenuhnya

    /**
     * Override: groupBy mitra_id, bukan pengangkut_id
     * Override: tidak ada filter mode
     */
    protected function loadData(): Collection
    {
        return RekapData::query()
            ->when(!empty($this->filters['produk_id']), fn($q) =>
                $q->where('produk_id', $this->filters['produk_id'])
            )
            ->when(
                !empty($this->filters['tanggal_mulai']) && !empty($this->filters['tanggal_akhir']),
                fn($q) => $q->whereBetween('tanggal', [
                    $this->filters['tanggal_mulai'],
                    $this->filters['tanggal_akhir'],
                ])
            )
            // ✅ Filter hanya supplier_luar, tanpa filter mode
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

    /**
     * Override: addPengangkutTable pakai builder khusus mitra
     */
    protected function addPengangkutTable(Collection &$rows, int $tahun, int $kolomPengangkut): void
    {
        $startRow = $this->currentRow;

        // ✅ Pakai header builder khusus mitra (nama_mitra, bukan nama pengangkut)
        $headerBuilder = new MitraHeaderBuilder($this->pengangkuts, $this->styleTracker, $this->currentRow);
        $headers = $headerBuilder->build();

        $rows->push($headers['header1']);
        $this->currentRow++;
        $rows->push($headers['header2']);
        $this->currentRow++;

        // ✅ Pakai data builder khusus mitra (lookup by mitra_id, bukan pengangkut_id)
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
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($kolomPengangkut);
        $this->styleTracker->addBorderRange("A{$startRow}:{$lastCol}{$endRow}");
    }
}