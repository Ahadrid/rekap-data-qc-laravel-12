<?php

namespace App\Imports;

use App\Models\RekapData;
use App\Services\{
    RekapDataCalculator,
    RekapDataImportService
};
use App\Actions\ResolveRekapMasterData;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\{
    ToModel, WithHeadingRow, WithStartRow
};
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class RekapDataImport implements ToModel, WithHeadingRow, WithStartRow
{
    public int $inserted = 0;
    public int $skipped  = 0;

    public function startRow(): int
    {
        return 4;
    }

    public function model(array $row)
    {
        // Skip jika field wajib kosong
        if (empty($row['mitra']) || empty($row['tanggal'])) {
            $this->skipped++;
            return null;
        }

        // ✅ Skip jika tidak ada nama pengangkutan (transporter_name kosong)
        if (empty(trim($row['transporter_name'] ?? ''))) {
            $this->skipped++;
            return null;
        }

        try {
            $tanggal = is_numeric($row['tanggal'])
                ? Carbon::instance(ExcelDate::excelToDateTimeObject($row['tanggal']))
                : Carbon::parse($row['tanggal']);
        } catch (\Throwable) {
            $this->skipped++;
            return null;
        }

        if (empty($row['mobil_pengangkut'])) {
            $this->skipped++;
            return null;
        }

        $master = ResolveRekapMasterData::resolve($row);
        $calc   = RekapDataCalculator::calculate($row);

        // Cek Duplikasi
        $exists = RekapDataImportService::isDuplicate([
            'tanggal'      => $tanggal->toDateString(),
            'mitra_id'     => $master['mitra']->id,
            'produk_id'    => $master['produk']->id,
            'kendaraan_id' => $master['kendaraan']->id,
            'bruto_kirim'  => (float) ($row['arrival_unload'] ?? 0),
            'tara_kirim'   => (float) ($row['departure_unload'] ?? 0),
            'netto_kebun'  => (float) ($row['netto_unload'] ?? 0),
            'bruto'        => (float) ($row['berat_masuk'] ?? 0),
            'tara'         => (float) ($row['berat_keluar'] ?? 0),
            'netto'        => (float) ($row['berat_bersih'] ?? 0),
        ]);

        if ($exists) {
            $this->skipped++;
            return null;
        }

        $kode = RekapDataImportService::generateKode($master['produk']);
        $this->inserted++;

        return new RekapData([
            'no_dokumen'    => $kode['no_dokumen'],
            'urutan_produk' => $kode['urutan_produk'],
            'tanggal'       => $tanggal->toDateString(),
            'tahun'         => $tanggal->year,
            'bulan'         => $tanggal->month,
            'bruto_kirim'   => (float) ($row['arrival_unload'] ?? 0),
            'tara_kirim'    => (float) ($row['departure_unload'] ?? 0),
            'netto_kebun'   => $calc['nettoKebun'],
            'bruto'         => (float) ($row['berat_masuk'] ?? 0),
            'tara'          => (float) ($row['berat_keluar'] ?? 0),
            'netto'         => $calc['netto'],
            'susut'         => $calc['susut'],
            'susut_persen'  => $calc['susutPersen'],
            'ffa'           => $calc['ffa'],
            'dobi'          => $calc['dobi'],
            'keterangan'    => $row['remark'] ?? null,
            'produk_id'     => $master['produk']->id,
            'mitra_id'      => $master['mitra']->id,
            'pengangkut_id' => $master['pengangkut']->id,
            'kendaraan_id'  => $master['kendaraan']->id,
        ]);
    }
}
