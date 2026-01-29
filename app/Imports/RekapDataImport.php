<?php

namespace App\Imports;

use App\Models\RekapData;
use App\Models\Mitra;
use App\Models\Produk;
use App\Models\Kendaraan;
use App\Models\Pengangkut;
use App\Helpers\KodeGenerator;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class RekapDataImport implements ToModel, WithHeadingRow, WithStartRow
{
    public int $inserted = 0;
    public int $skipped  = 0;

    const DEFAULT_PENGANGKUT = 'PT. INTAN SEJATI ANDALAN';

    public function startRow(): int
    {
        return 4;
    }

    /**
     * Deteksi tipe mitra
     */
    protected function detectTipeMitra(string $nama): string
    {
        $nama = strtoupper($nama);
        $nama = preg_replace('/[^A-Z ]/', '', $nama); // buang simbol
        $nama = preg_replace('/\s+/', ' ', $nama);    // normalize spasi

        $perusahaanInternal = [
            'Berlian Inti Mekar',
            'Mutiara Unggul Lestari',
        ];

        foreach ($perusahaanInternal as $internal) {
            if (str_contains($nama, $internal)) {
                return 'perusahaan';
            }
        }

        return 'supplier_luar';
    }

    public function model(array $row)
    {
        /* =====================================================
         * 1️⃣ FILTER AWAL
         * ===================================================== */
        if (empty($row['mitra']) || empty($row['tanggal'])) {
            return null;
        }

        /* =====================================================
         * 2️⃣ TANGGAL
         * ===================================================== */
        try {
            $tanggal = is_numeric($row['tanggal'])
                ? Carbon::instance(ExcelDate::excelToDateTimeObject($row['tanggal']))
                : Carbon::parse($row['tanggal']);
        } catch (\Throwable) {
            return null;
        }

        /* =====================================================
         * 3️⃣ MITRA
         * ===================================================== */
        $namaMitra = trim($row['mitra']);

        $mitra = Mitra::firstOrCreate(
            ['nama_mitra' => $namaMitra],
            ['is_active' => true]
        );

        // Set tipe_mitra hanya jika masih kosong
        if (! $mitra->tipe_mitra) {
            $mitra->update([
                'tipe_mitra' => $this->detectTipeMitra($namaMitra),
            ]);
        }

        /* =====================================================
         * 4️⃣ PRODUK
         * ===================================================== */
        $rawProduk  = trim($row['produk'] ?? '');
        $namaProduk = trim(preg_replace('/^\[.*?\]\s*/', '', $rawProduk));

        $produk = Produk::firstOrCreate([
            'nama_produk' => $namaProduk,
        ]);

        /* =====================================================
         * 5️⃣ PENGANGKUT
         * ===================================================== */
        $namaPengangkut = trim($row['transporter_name'] ?? '')
            ?: self::DEFAULT_PENGANGKUT;

        $pengangkut = Pengangkut::firstOrCreate([
            'nama_pengangkut' => $namaPengangkut,
        ]);

        /* =====================================================
         * 6️⃣ KENDARAAN
         * ===================================================== */
        if (empty($row['mobil_pengangkut'])) {
            return null;
        }

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

        /* =====================================================
         * 7️⃣ DUPLICATE GUARD
         * ===================================================== */
        $exists = RekapData::where([
            'tanggal'      => $tanggal->toDateString(),
            'mitra_id'     => $mitra->id,
            'produk_id'    => $produk->id,
            'kendaraan_id' => $kendaraan->id,
            'bruto_kirim'  => (float) ($row['arrival_unload'] ?? 0),
            'tara_kirim'   => (float) ($row['departure_unload'] ?? 0),
        ])->exists();

        if ($exists) {
            $this->skipped++;
            return null;
        }

        /* =====================================================
         * 8️⃣ HITUNG NILAI
         * ===================================================== */
        $nettoKebun = (float) ($row['netto_unload'] ?? 0);
        $netto      = (float) ($row['berat_bersih'] ?? 0);
        $susut      = $netto - $nettoKebun;

        $susutPersen = $nettoKebun > 0
            ? round(($susut / $nettoKebun) * 100, 2)
            : 0;

        $ffa  = (float) ($row['ffa_val'] ?? 0);
        $dobi = (float) ($row['dobi_val'] ?? 0);

        if ($ffa > 999)  $ffa  /= 1000;
        if ($dobi > 999) $dobi /= 1000;

        /* =====================================================
         * 9️⃣ AUTO GENERATE KODE
         * ===================================================== */
        $kode = KodeGenerator::generateRekapData($produk);

        $this->inserted++;

        /* =====================================================
         * 🔟 SIMPAN
         * ===================================================== */
        return new RekapData([
            'no_dokumen'    => $kode['no_dokumen'],
            'urutan_produk' => $kode['urutan_produk'],

            'tanggal' => $tanggal->toDateString(),
            'tahun'   => $tanggal->year,
            'bulan'   => $tanggal->month,

            'bruto_kirim' => (float) ($row['arrival_unload'] ?? 0),
            'tara_kirim'  => (float) ($row['departure_unload'] ?? 0),
            'netto_kebun' => $nettoKebun,

            'bruto' => (float) ($row['berat_masuk'] ?? 0),
            'tara'  => (float) ($row['berat_keluar'] ?? 0),
            'netto' => $netto,

            'susut'        => $susut,
            'susut_persen' => $susutPersen,

            'ffa'        => $ffa,
            'dobi'       => $dobi,
            'keterangan' => $row['remark'] ?? null,

            'produk_id'     => $produk->id,
            'mitra_id'      => $mitra->id,
            'pengangkut_id' => $pengangkut->id,
            'kendaraan_id'  => $kendaraan->id,
        ]);
    }
}
