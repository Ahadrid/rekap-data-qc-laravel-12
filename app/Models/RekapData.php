<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapData extends Model
{
    protected static function booted()
    {
        static::saving(function ($rekap) {
            if ($rekap->netto_kebun > 0) {
                $rekap->susut_persen = ($rekap->susut / $rekap->netto_kebun) * 100;
            } else {
                $rekap->susut_persen = 0;
            }
        });
    }
    //
    protected $casts = [
        'netto_kebun' => 'double',
        'susut' => 'double',
        'tanggal' => 'date'
    ];

    protected $fillable = [
        'no_dokumen',
        'urutan_produk',
        'tanggal',
        'tahun', 
        'bulan', 
        'bruto_kirim',
        'tara_kirim',
        'netto_kebun',
        'bruto',
        'tara',
        'netto', 
        'susut', 
        'susut_persen',
        'ffa',
        'dobi',
        'keterangan',
        'produk_id',
        'mitra_id',
        'pengangkut_id',
        'kendaraan_id',
    ];
    public function produk(){
        return $this->belongsTo(Produk::class);
    }
    public function mitra(){
        return $this->belongsTo(Mitra::class);
    }
    public function pengangkut(){
        return $this->belongsTo(Pengangkut::class);
    }
    public function kendaraan(){
        return $this->belongsTo(Kendaraan::class);
    }
}
