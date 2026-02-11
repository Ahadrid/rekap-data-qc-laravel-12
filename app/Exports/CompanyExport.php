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

        // 3️⃣ Sheet per PENGANGKUT (HANYA yg relevan)
        $pengangkuts = Pengangkut::whereHas('rekapData', function ($q) {

            // 🔒 FILTER PRODUK (PK / CPO)
            if (!empty($this->filters['produk_id'])) {
                $q->where('produk_id', $this->filters['produk_id']);
            }

            // 🔒 FILTER MITRA / COMPANY
            $q->whereHas('mitra', function ($m) {
                match ($this->filters['mode']) {
                    'bim_rengat' =>
                        $m->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                          ->where('nama_mitra', 'ILIKE', '%RENGAT%'),

                    'bim_siak' =>
                        $m->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                          ->where('nama_mitra', 'ILIKE', '%SIAK%'),

                    'mul' =>
                        $m->where('nama_mitra', 'ILIKE', '%MUTIARA UNGGUL LESTARI%'),
                };
            });
        })
        ->orderBy('kode')
        ->get();

        foreach ($pengangkuts as $pengangkut) {
            $sheets[] = new PengangkutSheet(
                $this->filters,
                $pengangkut
            );
        }

        return $sheets;
    }
}
