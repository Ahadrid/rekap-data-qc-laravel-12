<?php

namespace App\Query;

use App\Models\RekapData;

class SupplierLuarQueryExport
{
    public static function build(array $filters)
    {
        return RekapData::query()
            ->with(['mitra', 'produk'])

            // WAJIB: supplier luar saja
            ->whereHas('mitra', function ($q) {
                $q->where('tipe_mitra', 'suplier_luar');
            })

            // CPO / PK
            ->where('produk_id', $filters['produk_id'])

            // Per mitra (per sheet)
            ->where('mitra_id', $filters['mitra_id'])

            ->selectRaw("
                DATE_TRUNC('month', tanggal) as bulan,
                SUM(netto_kebun) as netto_kebun,
                SUM(netto) as netto,
                SUM(susut) as susut
            ")
            ->groupByRaw("DATE_TRUNC('month', tanggal)")
            ->orderByRaw("DATE_TRUNC('month', tanggal)");
    }
}