<?php
namespace App\Helpers;
use App\Models\Produk;
use App\Models\RekapData;

class KodeGenerator
{
    private const BADAN_USAHA = ['PT', 'CV'];

    /**
     * Normalisasi nama: uppercase, ganti tanda baca jadi spasi,
     * pindahkan badan usaha suffix ke prefix.
     */
    private static function normalizeNama(string $nama): array
    {
        $nama = strtoupper(trim($nama));
        // Ganti tanda baca (titik, koma, dll) jadi spasi — bukan dihapus
        $nama = preg_replace('/[^A-Z0-9\s]/', ' ', $nama);
        $parts = array_values(array_filter(explode(' ', $nama), fn($w) => $w !== ''));

        // Normalisasi posisi badan usaha: suffix → prefix
        // Contoh: ['HUTHAMA', 'CAHAYA', 'PT'] → ['PT', 'HUTHAMA', 'CAHAYA']
        if (!empty($parts) && in_array(end($parts), self::BADAN_USAHA)) {
            $bu = array_pop($parts);
            array_unshift($parts, $bu);
        }

        return $parts;
    }

    /**
     * Ekstrak kata inti (tanpa badan usaha) dari nama yang sudah dinormalisasi.
     * Contoh: ['PT', 'HUTHAMA', 'CAHAYA', 'ALOPIAS'] → ['HUTHAMA', 'CAHAYA', 'ALOPIAS']
     */
    private static function getCoreWords(string $nama): array
    {
        $parts = self::normalizeNama($nama);
        return array_values(
            array_filter($parts, fn($w) => !in_array($w, self::BADAN_USAHA))
        );
    }

    /**
     * Hitung similarity antara dua array kata menggunakan perbandingan
     * per-kata dengan similar_text. Cocok untuk deteksi typo seperti
     * HUTHAMA vs HUTAMA.
     *
     * @return float 0.0 – 1.0
     */
    private static function nameSimilarity(array $wordsA, array $wordsB): float
    {
        if (empty($wordsA) || empty($wordsB)) {
            return 0.0;
        }

        // Jumlah kata harus sama atau hampir sama
        if (abs(count($wordsA) - count($wordsB)) > 1) {
            return 0.0;
        }

        $count = max(count($wordsA), count($wordsB));
        $totalScore = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $a = $wordsA[$i] ?? '';
            $b = $wordsB[$i] ?? '';
            if ($a === '' || $b === '') {
                // kata ekstra di salah satu → kontribusi 0
                continue;
            }
            similar_text($a, $b, $percent);
            $totalScore += $percent / 100;
        }

        return $totalScore / $count;
    }

    /**
     * Cari kode yang sudah ada di DB untuk nama yang mirip (fuzzy match).
     * Jika ditemukan, kembalikan kode tersebut daripada membuat kode baru.
     *
     * @param class-string<\Illuminate\Database\Eloquent\Model> $model
     * @param float $threshold Ambang batas similaritas (0.0–1.0), default 0.85
     */
    public static function findSimilarKode(
        string $nama,
        string $model,
        string $kodeColumn,
        string $namaColumn,
        float $threshold = 0.85
    ): ?string {
        $coreWords = self::getCoreWords($nama);

        if (empty($coreWords)) {
            return null;
        }

        // Ambil semua data dari DB — pertimbangkan batasi dengan where jika data besar
        $allRecords = $model::select([$kodeColumn, $namaColumn])->get();

        foreach ($allRecords as $record) {
            $existingCore = self::getCoreWords($record->{$namaColumn});
            $similarity = self::nameSimilarity($coreWords, $existingCore);

            if ($similarity >= $threshold) {
                return $record->{$kodeColumn};
            }
        }

        return null;
    }

    public static function fromNamaPengangkut(string $nama): string
    {
        $parts = self::normalizeNama($nama);
        if (empty($parts)) return '';

        // Buang badan usaha dari depan
        if (in_array($parts[0], self::BADAN_USAHA)) {
            array_shift($parts);
        }

        if (empty($parts)) return '';
        if (count($parts) === 1) return $parts[0];

        return implode('', array_map(fn($w) => substr($w, 0, 1), $parts));
    }

    public static function fromNama(string $nama): string
    {
        $parts = self::normalizeNama($nama);
        if (count($parts) <= 1) return $parts[0] ?? '';

        $kode = [];
        $start = 0;

        if (in_array($parts[0], self::BADAN_USAHA)) {
            $kode[] = $parts[0];
            $start = 1;
        }

        for ($i = $start; $i < count($parts); $i++) {
            $kode[] = substr($parts[$i], 0, 1);
        }

        return implode(' ', [$kode[0] ?? '', implode('', array_slice($kode, 1))]);
    }

    public static function fromNamaProduk(string $nama): string
    {
        $parts = self::normalizeNama($nama);
        if (empty($parts)) return '';
        return implode('', array_map(fn($w) => substr($w, 0, 1), $parts));
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $model
     */
    public static function makeUnique(string $kode, string $model, string $column): string
    {
        $existing = $model::where($column, 'like', $kode . '%')
            ->pluck($column)
            ->toArray();

        if (empty($existing)) return $kode;

        $max = 0;
        foreach ($existing as $item) {
            if ($item === $kode) {
                $max = max($max, 0);
                continue;
            }
            if (preg_match('/^' . preg_quote($kode, '/') . '(\d+)$/', $item, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $kode . ($max + 1);
    }

    public static function generateRekapData(Produk $produk): array
    {
        $lastUrutan = RekapData::where('produk_id', $produk->id)->max('urutan_produk');
        $urutan = ($lastUrutan ?? 0) + 1;
        $prefix = strtoupper($produk->kode ?? preg_replace('/\s+/', '', $produk->kode_produk));

        return [
            'no_dokumen' => "{$prefix}-{$urutan}",
            'urutan_produk' => $urutan,
        ];
    }
}