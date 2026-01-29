<?php

namespace App\Exports\Sheets;

use App\Models\RekapData;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekapSheet implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithTitle
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Rekap';
    }

    public function query()
    {
        return RekapData::query()
            ->join('mitra', 'rekap_data.mitra_id', '=', 'mitra.id')
            ->selectRaw('
                mitra.id as mitra_id,
                mitra.nama_mitra,
                SUM(rekap_data.netto_kebun) as netto_kebun,
                SUM(rekap_data.netto) as netto,
                SUM(rekap_data.susut) as susut
            ')
            ->when($this->filters['mode'] ?? null, function ($q) {
                // 🔁 samakan filter perusahaan dengan sheet ALL
                // contoh:
                // if ($this->filters['mode'] === 'bim_rengat') {
                //     $q->where('mitra.nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                //       ->where('mitra.nama_mitra', 'ILIKE', '%RENGAT%');
                // }
            })
            ->groupBy('mitra.id', 'mitra.nama_mitra')
            ->orderBy('mitra.nama_mitra');
    }

    public function map($row): array
    {
        $susutPersen = $row->netto_kebun > 0
            ? ($row->susut / $row->netto_kebun) * 100
            : 0;

        return [
            $row->nama_mitra,
            $row->netto_kebun,
            $row->netto,
            $row->susut,
            round($susutPersen, 2),
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Rekanan',
            'Netto Kebun',
            'Netto',
            'Susut',
            'Susut (%)',
        ];
    }
}
