<?php

namespace App\Models;

use App\Helpers\KodeGenerator;
use Illuminate\Database\Eloquent\Model;

class Pengangkut extends Model
{
    protected $table = 'pengangkut';

    protected $fillable = ['nama_pengangkut', 'kode', 'is_active'];

    protected static function booted()
    {
        static::creating(function ($pengangkut) {
            if (empty($pengangkut->kode)) {
                $kode = KodeGenerator::fromNama($pengangkut->nama_pengangkut);
                $pengangkut->kode = KodeGenerator::makeUnique(
                    $kode,
                    self::class,
                    'kode'
                );
            }
        });
    }   

    public function kendaraan(){
        return $this->hasMany(Kendaraan::class, 'pengangkut_id');
    }
    public function rekapData(){
        return $this->hasMany(RekapData::class, 'pengangkut_id');
    }
}
