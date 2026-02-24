<?php

namespace App\Query;

use App\Models\RekapData;

class SupplierLuarQueryExport
{
    public static function build(array $filters)
    {
        return RekapData::query()
            ->with(['mitra', 'pengangkut', 'kendaraan', 'produk'])
            ->whereHas('mitra', fn ($q) =>
                $q->where('tipe_mitra', 'suplier_luar')
            )
            ->when(
                !empty($filters['mitra_id']),
                fn ($q) => $q->where('mitra_id', $filters['mitra_id'])
            )
            ->when(
                !empty($filters['produk_id']),
                fn ($q) => $q->where('produk_id', $filters['produk_id'])
            )

            //Filter rentang tanggal awal dan akhir
            ->when(
                !empty($filters['tanggal_mulai']) && !empty($filters['tanggal_akhir']),
                fn($q) => $q->whereBetween('tanggal', [
                    $filters['tanggal_mulai'],
                    $filters['tanggal_akhir'],
                ])
            )
            ->orderBy('tanggal');
    }
}