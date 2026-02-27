<?php

namespace App\Models;

use App\Helpers\KodeGenerator;
use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    protected $table = 'mitra';

    protected $fillable = [
        'nama_mitra', 
        'kode_mitra', 
        'tipe_mitra', 
        'is_active'
    ];

    protected static function booted()
    {
        static::creating(function ($mitra) {
            if (empty($mitra->kode_mitra)) {
                $kode = KodeGenerator::fromNama($mitra->nama_mitra);
                $mitra->kode_mitra = KodeGenerator::makeUnique(
                    $kode,
                    self::class,
                    'kode_mitra'
                );
            }
        });
    }

    public function rekapData()
    {
        return $this->hasMany(RekapData::class);
    }
}
