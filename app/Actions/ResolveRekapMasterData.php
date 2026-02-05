<?php

namespace App\Actions;

use App\Models\Mitra;
use App\Models\Produk;
use App\Models\Pengangkut;
use App\Models\Kendaraan;
use App\Services\MitraTypeDetector;

class ResolveRekapMasterData
{
    const DEFAULT_PENGANGKUT = '';

    public static function resolve(array $row): array
    {
        // MITRA
        $namaMitra = trim($row['mitra']);
        $mitra = Mitra::firstOrCreate(
            ['nama_mitra' => $namaMitra],
            ['is_active' => true]
        );

        if (! $mitra->tipe_mitra) {
            $mitra->update([
                'tipe_mitra' => MitraTypeDetector::detect($namaMitra),
            ]);
        }

        // PRODUK
        $rawProduk  = trim($row['produk'] ?? '');
        $namaProduk = trim(preg_replace('/^\[.*?\]\s*/', '', $rawProduk));

        $produk = Produk::firstOrCreate([
            'nama_produk' => $namaProduk,
        ]);

        // PENGANGKUT
        $namaPengangkut = trim($row['transporter_name'] ?? '')
            ?: self::DEFAULT_PENGANGKUT;

        $pengangkut = Pengangkut::firstOrCreate([
            'nama_pengangkut' => $namaPengangkut,
        ]);

        // KENDARAAN
        $kendaraan = Kendaraan::firstOrCreate(
            ['no_pol' => trim($row['mobil_pengangkut'])],
            [
                'nama_supir'    => trim($row['pengemudi'] ?? null),
                'pengangkut_id' => $pengangkut->id,
            ]
        );

        if (! $kendaraan->pengangkut_id) {
            $kendaraan->update(['pengangkut_id' => $pengangkut->id]);
        }

        return compact('mitra', 'produk', 'pengangkut', 'kendaraan');
    }
}
