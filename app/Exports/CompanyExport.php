<?php

namespace App\Exports;

use App\Exports\Sheets\AllSheet;
use App\Exports\Sheets\PengangkutSheet;
use App\Exports\Sheets\RekapSheet;
use App\Models\Pengangkut;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CompanyExport implements WithMultipleSheets
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $sheets = [];

        // 1️⃣ Sheet ALL
        $sheets[] = new AllSheet($this->filters);

        // 2️⃣ Sheet REKAP
        $sheets[] = new RekapSheet($this->filters);

        // 3️⃣ Sheet per PENGANGKUT (dinamis)
        $pengangkuts = Pengangkut::whereHas('rekapData.mitra', function ($q) {
            $this->applyCompanyFilter($q);
        })->get();

        foreach ($pengangkuts as $pengangkut) {
            $sheets[] = new PengangkutSheet(
                $this->filters,
                $pengangkut
            );
        }

        return $sheets;
    }

    protected function applyCompanyFilter($query)
    {
        match ($this->filters['mode']) {
            'bim_rengat' =>
                $query->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                      ->where('nama_mitra', 'ILIKE', '%RENGAT%'),

            'bim_siak' =>
                $query->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                      ->where('nama_mitra', 'ILIKE', '%SIAK%'),

            'mul' =>
                $query->where('nama_mitra', 'ILIKE', '%MUTIARA UNGGUL LESTARI%'),
        };
    }
}
