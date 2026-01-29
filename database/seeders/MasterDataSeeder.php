<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Produk (Mass Insert Oke)
        DB::table('produk')->insert([
            ['nama_produk' => 'Crude Palm Oil', 'kode_produk' => 'CPO', 'is_active' => true, 'created_at' => now()],
            ['nama_produk' => 'Palm Kernel', 'kode_produk' => 'PK', 'is_active' => true, 'created_at' => now()],
        ]);

        // 2. Seed Mitra (Mass Insert Oke)
        DB::table('mitra')->insert([
            ['nama_mitra' => 'PT Berlian Inti Mekar - Siak', 'kode_mitra' => 'PT BIMS','tipe_mitra' => 'perusahaan', 'is_active' => true, 'created_at' => now()],
            ['nama_mitra' => 'PT Berlian Inti Mekar - Rengat', 'kode_mitra' => 'PT BIMR', 'tipe_mitra' => 'perusahaan', 'is_active' => true, 'created_at' => now()],
            ['nama_mitra' => 'PT Mutiara Unggul Lestari', 'kode_mitra' => 'PT MUL', 'tipe_mitra' => 'perusahaan', 'is_active' => true, 'created_at' => now()],
        ]);

        // 3. Seed Pengangkut (Dibuat satu per satu agar dapat ID-nya)
        // $mrtId = DB::table('pengangkut')->insertGetId([
        //     'nama_pengangkut' => 'PT. Mega Raya Trans',
        //     'kode' => 'MRT',
        //     'is_active' => true,
        //     'created_at' => now()
        // ]);

        // $mmtId = DB::table('pengangkut')->insertGetId([
        //     'nama_pengangkut' => 'CV. MMT',
        //     'kode' => 'MMT',
        //     'is_active' => true,
        //     'created_at' => now()
        // ]);

        // 4. Seed Kendaraan (Menggunakan ID yang sudah didapat di atas)
        // DB::table('kendaraan')->insert([
        //     [
        //         'no_pol' => 'BK 1234 AB',
        //         'nama_supir' => 'Budi Sudarsono',
        //         'pengangkut_id' => $mrtId, // PT. Mega Raya Trans
        //         'created_at' => now()
        //     ],
        //     [
        //         'no_pol' => 'BK 5678 CD',
        //         'nama_supir' => 'Andi Wijaya',
        //         'pengangkut_id' => $mmtId, // CV. MMT
        //         'created_at' => now()
        //     ],
        // ]);
    }
}