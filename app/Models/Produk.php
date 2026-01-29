<?php

namespace App\Models;

use App\Helpers\KodeGenerator;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';

    protected $fillable = ['nama_produk', 'kode_produk', 'is_active'];

    protected static function booted()
    {
        static::creating(function ($produk){
            if (empty($produk->kode_produk)) {
                $kode = KodeGenerator::fromNamaProduk($produk->nama_produk);
                $produk->kode_produk = KodeGenerator::makeUnique(
                    $kode,
                    self::class,
                    'kode_produk'
                );
            }
        });
    }
}
