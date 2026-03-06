<?php
namespace App\Exports\Sheets;

use App\Exports\Styles\StyleTracker;
use App\Models\RekapData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class RekapSheetBuilder
{
    protected Collection $pengangkuts;
    protected StyleTracker $styleTracker;
    protected array $filters;
    protected Collection $data;
    protected int $currentRow = 1;

    public function __construct(Collection $pengangkuts, StyleTracker $styleTracker, array $filters)
    {
        $this->pengangkuts = $pengangkuts;
        $this->styleTracker = $styleTracker;
        $this->filters = $filters;
        $this->data = $this->loadData();
    }

    public function build(): Collection
    {
        $rows = collect();
        foreach ($this->getYearRange() as $tahun) {
            $this->buildYearSection($rows, $tahun);
        }
        return $rows;
    }

    protected function buildYearSection(Collection &$rows, int $tahun): void
    {
        $kolomCount = $this->getColumnCount();

        $this->addYearTitle($rows, $tahun, $kolomCount);
        $this->addEntityTable($rows, $tahun, $kolomCount);  // ✅ nama generic
        $this->addAllTable($rows, $tahun);
        $this->addSpacer($rows, $kolomCount);
    }

    protected function addYearTitle(Collection &$rows, int $tahun, int $kolomCount): void
    {
        $titleRow = $this->currentRow;
        $rows->push(["TAHUN {$tahun}"]);

        $lastCol = Coordinate::stringFromColumnIndex($kolomCount);
        $range = "A{$titleRow}:{$lastCol}{$titleRow}";

        $this->styleTracker->addMerge($range);
        $this->styleTracker->addTahunCell($range);

        $this->currentRow++;
        $rows->push($this->emptyRow($kolomCount));
        $this->currentRow++;
    }

    /**
     * Template method — override di subclass untuk entity berbeda.
     * Implementasi default menggunakan Pengangkut.
     */
    protected function addEntityTable(Collection &$rows, int $tahun, int $kolomCount): void
    {
        $startRow = $this->currentRow;

        $headerBuilder = new PengangkutHeaderBuilder($this->pengangkuts, $this->styleTracker, $this->currentRow);
        $headers = $headerBuilder->build();

        $rows->push($headers['header1']);
        $this->currentRow++;
        $rows->push($headers['header2']);
        $this->currentRow++;

        $dataBuilder = new PengangkutDataBuilder(
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
        $lastCol = Coordinate::stringFromColumnIndex($kolomCount);
        $this->styleTracker->addBorderRange("A{$startRow}:{$lastCol}{$endRow}");
    }

    /**
     * @deprecated Gunakan addEntityTable(). Akan dihapus di versi berikutnya.
     */
    protected function addPengangkutTable(Collection &$rows, int $tahun, int $kolomCount): void
    {
        $this->addEntityTable($rows, $tahun, $kolomCount);
    }

    protected function addAllTable(Collection &$rows, int $tahun): void
    {
        $rows->push($this->emptyRow(6));
        $this->currentRow++;

        $allBuilder = new AllTableBuilder(
            $this->styleTracker,
            $this->data,
            $tahun,
            $this->currentRow
        );

        foreach ($allBuilder->build() as $row) {
            $rows->push($row);
            $this->currentRow++;
        }
    }

    protected function addSpacer(Collection &$rows, int $cols): void
    {
        $rows->push($this->emptyRow($cols));
        $this->currentRow++;
        $rows->push($this->emptyRow($cols));
        $this->currentRow++;
    }

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
            ->when($this->filters['mode'] ?? null, function ($q) {
                $q->whereHas('mitra', function ($m) {
                    $mode = $this->filters['mode'];
                    if ($mode === 'bim_rengat') {
                        $m->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                          ->where('nama_mitra', 'ILIKE', '%RENGAT%');
                    } elseif ($mode === 'bim_siak') {
                        $m->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                          ->where('nama_mitra', 'ILIKE', '%SIAK%');
                    } elseif ($mode === 'mul') {
                        $m->where('nama_mitra', 'ILIKE', '%MUTIARA UNGGUL LESTARI%');
                    }
                });
            })
            ->selectRaw("
                DATE_TRUNC('month', tanggal) as bulan,
                pengangkut_id,
                SUM(netto_kebun) as netto_kebun,
                SUM(netto) as netto,
                SUM(susut) as susut
            ")
            ->groupByRaw("DATE_TRUNC('month', tanggal)")
            ->groupBy('pengangkut_id')
            ->get();
    }

    protected function getYearRange(): array
    {
        $start = !empty($this->filters['tanggal_mulai'])
            ? Carbon::parse($this->filters['tanggal_mulai'])->year
            : ($this->filters['tahun_mulai'] ?? now()->year);

        $end = !empty($this->filters['tanggal_akhir'])
            ? Carbon::parse($this->filters['tanggal_akhir'])->year
            : ($this->filters['tahun_akhir'] ?? now()->year);

        return range($start, $end);
    }

    protected function getColumnCount(): int
    {
        return 2 + ($this->pengangkuts->count() * 4);
    }

    protected function emptyRow(int $cols): array
    {
        return array_fill(0, $cols, null);
    }
}