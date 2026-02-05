<?php

namespace App\Services;

class MitraTypeDetector
{
    public static function detect(string $nama): string
    {
        $nama = strtoupper($nama);
        $nama = preg_replace('/[^A-Z ]/', '', $nama);
        $nama = preg_replace('/\s+/', ' ', $nama);

        $internal = [
            'BERLIAN INTI MEKAR',
            'MUTIARA UNGGUL LESTARI',
        ];

        foreach ($internal as $key) {
            if (str_contains($nama, $key)) {
                return 'perusahaan';
            }
        }

        return 'supplier_luar';
    }
}
