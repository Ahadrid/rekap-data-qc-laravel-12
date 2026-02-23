<?php

namespace App\Exports;

use App\Exports\Sheets\SupplierLuarSheet;
use App\Models\Mitra;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SupplierLuarExport implements WithMultipleSheets
{
    protected int $produkId;
    protected array $filters;

    public function __construct(int $produkId, array $filters = [])
    {
        $this->produkId = $produkId;
        $this->filters  = $filters;
    }

    public function sheets(): array
    {
        $sheets = [];

        $mitras = Mitra::where('tipe_mitra', 'suplier_luar')
            ->whereHas('rekapData', function ($q) {
                $q->where('produk_id', $this->produkId);
            })
            ->orderBy('nama_mitra')
            ->get();

        foreach ($mitras as $mitra) {
            $sheets[] = new SupplierLuarSheet(
                $mitra,
                $this->produkId,
                $this->filters
            );
        }

        return $sheets;
    }
}