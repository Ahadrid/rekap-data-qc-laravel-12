<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rekap_data', function (Blueprint $table) {
            $table->index('tanggal');        // defaultSort
            $table->index('produk_id');      // relasi + searchable
            $table->index('mitra_id');       // relasi + searchable
            $table->index('pengangkut_id');  // relasi
            $table->index('kendaraan_id');   // relasi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_data', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['produk_id']);
            $table->dropIndex(['mitra_id']);
            $table->dropIndex(['pengangkut_id']);
            $table->dropIndex(['kendaraan_id']);
        });
    }
};
