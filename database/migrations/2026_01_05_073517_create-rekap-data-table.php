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
        //
        Schema::create('rekap_data', function (Blueprint $table) {
            $table->id();

            $table->string('no_dokumen');
            $table->integer('urutan_produk');

            $table->date('tanggal');
            $table->integer('tahun');
            $table->smallInteger('bulan');

            $table->integer('bruto_kirim')->default(0);
            $table->integer('tara_kirim')->default(0);
            $table->integer('netto_kebun')->default(0);

            $table->integer('bruto')->default(0);
            $table->integer('tara')->default(0);
            $table->integer('netto')->default(0);
            $table->integer('susut')->default(0);

            $table->decimal('susut_persen', 10, 2)->default(0);
            $table->decimal('ffa', 5, 2)->nullable();
            $table->decimal('dobi', 5, 2)->nullable();

            $table->string('keterangan')->nullable();

            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->foreignId('mitra_id')->constrained('mitra')->cascadeOnDelete();
            $table->foreignId('pengangkut_id')->constrained('pengangkut')->cascadeOnDelete();
            $table->foreignId('kendaraan_id')->constrained('kendaraan')->cascadeOnDelete();

            $table->timestamps();

            /** INDEX & KONSISTENSI */
            $table->unique(['produk_id', 'urutan_produk']); // CPO-1 tidak bentrok PK-1
            $table->index(['mitra_id', 'tahun', 'bulan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('rekap_data');
    }
};
