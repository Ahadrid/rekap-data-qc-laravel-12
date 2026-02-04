<?php

namespace App\Query;
use App\Models\RekapData;

class QueryExport
{
    public static function build(array $filters)
    {
        return RekapData::query()
            ->with(['mitra', 'produk', 'kendaraan', 'pengangkut'])

            // filter produk
            ->when($filters['produk_id'] ?? null,
                fn ($q, $v) => $q->where('produk_id', $v)
            )

            // filter MODE EXPORT
            ->when($filters['mode'] ?? null, function ($q, $mode) {
                match ($mode) {
                    'suplier_luar' =>
                        $q->whereHas('mitra', fn ($m) =>
                            $m->where('tipe_mitra', 'suplier_luar')
                        ),

                    'bim_rengat' =>
                        $q->whereHas('mitra', fn ($m) =>
                            $m->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                              ->where('nama_mitra', 'ILIKE', '%RENGAT%')
                        ),

                    'bim_siak' =>
                        $q->whereHas('mitra', fn ($m) =>
                            $m->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                              ->where('nama_mitra', 'ILIKE', '%SIAK%')
                        ),

                    'mul' =>
                        $q->whereHas('mitra', fn ($m) =>
                            $m->where('nama_mitra', 'ILIKE', '%MUTIARA UNGGUL LESTARI%')
                        ),

                    default => null,
                };
            })

            ->orderBy('tanggal');

    }

}