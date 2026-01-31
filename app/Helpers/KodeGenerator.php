<?php

namespace App\Helpers;

use App\Models\Produk;
use App\Models\RekapData;

class KodeGenerator
{
    public static function fromNama(string $nama): string
    {
        $nama = strtoupper(trim(preg_replace('/[^A-Z0-9\s]/', '', $nama)));
        $parts = array_values(array_filter(explode(' ', $nama)));

        if (count($parts) <= 1) {
            return $parts[0] ?? '';
        }

        $badanUsaha = ['PT'];

        $kode = [];
        $start = 0;

        if (in_array($parts[0], $badanUsaha)) {
            $kode[] = $parts[0];
            $start = 1;
        }

        for ($i = $start; $i < count($parts); $i++) {
            $kode[] = substr($parts[$i], 0, 1);
        }

        return implode(' ', [
            $kode[0] ?? '',
            implode('', array_slice($kode, 1)),
        ]);
    }

    public static function fromNamaPengangkut (string $nama): string
    {
        $nama = strtoupper(trim(preg_replace('/[^A-Z0-9\s]/', '', $nama)));
        $parts = array_values(array_filter(explode(' ', $nama)));

        if (empty($parts)) {
            return '';
        }

        $badanUsaha = ['PT', 'CV', 'UD'];

        if (in_array($parts[0], $badanUsaha)) {
            array_shift($parts);
        }

        if (empty($parts)) {
            return '';
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        return implode('', array_map(
            fn($w) => substr($w, 0, 1),
            $parts
        ));
    }

    // untuk generate otomatis nama produk jika belum ada di database
    public static function fromNamaProduk (string $nama): string
    {
        $nama = strtoupper(trim(preg_replace('/[^A-Z0-9\s]/', '', $nama)));
        $parts = array_values(array_filter(explode(' ', $nama)));

        if (empty($parts)) {
            return '';
        }

        // Ambil inisial dari setiap kata dari huruf pertama.
        return implode('', array_map(fn ($w) => substr($w, 0, 1), $parts));
    }

    public static function makeUnique(string $kode, string $model, string $column): string
    {
        // Ambil semua kode yang mirip (PT MASS, PT MASS1, PT MASS2, dst)
        $existing = $model::where($column, 'like', $kode . '%')
            ->pluck($column)
            ->toArray();

        if (empty($existing)) {
            return $kode;
        }
        // Ambil angka terbesar di belakang
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
        $lastUrutan = RekapData::where('produk_id', $produk->id)
            ->max('urutan_produk');
        
        $urutan = ($lastUrutan ?? 0) + 1;

        $prefix = strtoupper(
            $produk->kode
            ?? preg_replace('/\s+/', '', $produk->kode_produk)
        );

        return [
            'no_dokumen' => "{$prefix}-{$urutan}",
            'urutan_produk' => $urutan,
        ];
    }
}
