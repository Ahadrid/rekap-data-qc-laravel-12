<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    protected $table = 'kendaraan';

    protected $fillable = [
        'no_pol',
        'nama_supir',
        'pengangkut_id',
    ];

    public function pengangkut(){
        return $this->belongsTo(Pengangkut::class, 'pengangkut_id');
    }
}
