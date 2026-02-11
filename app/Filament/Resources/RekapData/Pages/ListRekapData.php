<?php

namespace App\Filament\Resources\RekapData\Pages;

use App\Filament\Resources\RekapData\RekapDataResource;
use App\Models\RekapData;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ListRekapData extends ListRecords
{
    protected static string $resource = RekapDataResource::class;

    /**
     * 🔥 REPORT BULANAN (JAN–DES)
     */
    public function getTableRecords(): Collection|Paginator
    {
        $mitraFilter = $this->getTableFilterState('mitra_id');
        $produkFilter = $this->getTableFilterState('produk_id');
        $tahunFilter = $this->getTableFilterState('tahun');

        $mitraId = $mitraFilter['value'] ?? null;
        $produkId = $produkFilter['value'] ?? null;
        $tahun   = $tahunFilter['value']?? null;

        if ($tahun === '' || $tahun === null) {
            $tahun = null;
        }


        $data = RekapData::query()
            ->select([
                'bulan',
                DB::raw('SUM(netto_kebun) as netto_kebun'),
                DB::raw('SUM(netto) as netto'),
                DB::raw('SUM(susut) as susut'),
            ])
            ->when($mitraId, fn ($q) => $q->where('mitra_id', $mitraId))
            ->when($produkId, fn ($q) => $q->where('produk_id', $produkId))
            ->when($tahun, fn ($q) => $q->where('tahun', $tahun))
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');


        return collect(range(1, 12))->map(fn ($bulan) => [
            'key'          => $bulan, // 🔥 WAJIB
            'bulan'        => $bulan,
            'tahun'        => $tahun,
            'netto_kebun'  => $data[$bulan]->netto_kebun ?? null,
            'netto'        => $data[$bulan]->netto ?? null,
            'susut'        => $data[$bulan]->susut ?? null,
            'susut_persen' => isset($data[$bulan]) && $data[$bulan]->netto_kebun > 0
                ? round(($data[$bulan]->susut / $data[$bulan]->netto_kebun) * 100, 2)
                : null,
        ]);
    }

    public function getTableRecordKey(Model|array $record): string
    {
        $tahunFilter = $this->getTableFilterState('tahun');
        $tahun = $tahunFilter['value'] ?? now()->year;

        return $record['bulan'] . '-' . $tahun;
    }

    /**
     * 🔥 HEADER ACTIONS
     */
    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
    public function getTableActionApperance(): string
    {
        return 'modal';
    }
}
