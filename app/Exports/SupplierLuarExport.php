<?php

namespace App\Exports;

use App\Exports\Sheets\SupplierLuarSheet;
use App\Models\Mitra;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SupplierLuarExport implements WithMultipleSheets
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $sheets = [];

        $mitras = Mitra::query()
            ->where('tipe_mitra', 'suplier_luar')
            ->whereHas('rekapData', function ($q) {
                if (!empty($this->filters['produk_id'])) {
                    $q->where('produk_id', $this->filters['produk_id']);
                }

                if (!empty($this->filters['tanggal_mulai']) && !empty($this->filters['tanggal_akhir'])) {
                    $q->whereBetween('tanggal',[
                        $this->filters['tanggal_mulai'],
                        $this->filters['tanggal_akhir'],
                    ]);
                }
            })
            ->orderBy('nama_mitra')
            ->get();

        foreach ($mitras as $mitra) {
            $sheets[] = new SupplierLuarSheet(
                $this->filters,
                $mitra
            );
        }

        return $sheets;
    }
}