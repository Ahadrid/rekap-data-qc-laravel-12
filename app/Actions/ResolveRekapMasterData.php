<?php
namespace App\Actions;

use App\Models\Mitra;
use App\Models\Produk;
use App\Models\Pengangkut;
use App\Models\Kendaraan;
use App\Services\MitraTypeDetector;
use App\Helpers\KodeGenerator;

class ResolveRekapMasterData
{
    public static function resolve(array $row): array
    {
        /* ===============================
         * MITRA
         * =============================== */
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

        /* ===============================
         * PRODUK
         * =============================== */
        $rawProduk  = trim($row['produk'] ?? '');
        $namaProduk = trim(preg_replace('/^\[.*?\]\s*/', '', $rawProduk));
        $produk = Produk::firstOrCreate([
            'nama_produk' => $namaProduk,
        ]);

        /* ===============================
         * PENGANGKUT (dengan fuzzy match)
         * =============================== */
        $namaPengangkut = trim($row['transporter_name']);
        $pengangkut     = self::resolveOrCreatePengangkut($namaPengangkut);

        /* ===============================
         * KENDARAAN
         * =============================== */
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

    /**
     * Cari pengangkut yang namanya mirip (fuzzy match) sebelum membuat baru.
     * Ini mencegah duplikasi akibat variasi penulisan seperti:
     *   "PT HUTHAMA CAHAYA ALOPIAS"
     *   "PT. HUTHAMA CAHAYA ALOPIAS"
     *   "PT HUTAMA CAHAYA ALOPIAS"  ← typo
     */
    private static function resolveOrCreatePengangkut(string $nama): Pengangkut
    {
        // 1. Cari exact match dulu (paling cepat)
        $existing = Pengangkut::where('nama_pengangkut', $nama)->first();
        if ($existing) {
            return $existing;
        }

        // 2. Fuzzy match: cari nama yang mirip di DB
        $kodeMirip = KodeGenerator::findSimilarKode(
            $nama,
            Pengangkut::class,
            'kode', // sesuaikan dengan nama kolom kode di tabel pengangkut
            'nama_pengangkut',
            0.85
        );

        if ($kodeMirip) {
            // Sudah ada pengangkut dengan nama serupa → pakai yang itu
            return Pengangkut::where('kode', $kodeMirip)->first();
        }

        // 3. Benar-benar baru → buat record + generate kode unik
        $kode = KodeGenerator::fromNamaPengangkut($nama);
        $kode = KodeGenerator::makeUnique($kode, Pengangkut::class, 'kode');

        return Pengangkut::create([
            'nama_pengangkut' => $nama,
            'kode' => $kode,
            'is_active'       => true,
        ]);
    }
}