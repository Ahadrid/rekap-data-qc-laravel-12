<?php

namespace App\Exports\Sheets;

use App\Query\SupplierLuarQueryExport;
use App\Models\Mitra;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;

class SupplierLuarSheet implements FromCollection, WithTitle
{
    protected Mitra $mitra;
    protected int $produkId;
    protected array $filters;

    public function __construct(Mitra $mitra, int $produkId, array $filters)
    {
        $this->mitra    = $mitra;
        $this->produkId = $produkId;
        $this->filters  = $filters;
    }

    public function title(): string
    {
        return $this->mitra->kode_mitra;
    }

    public function collection(): Collection
    {
        $data = SupplierLuarQueryExport::build([
            'produk_id' => $this->produkId,
            'mitra_id'  => $this->mitra->id,
        ])->get();

        if ($data->isEmpty()) {
            return collect();
        }

        $rows = collect();

        // Header
        $rows->push([
            'Bulan',
            'Netto Kebun',
            'Netto',
            'Susut',
            'Susut (%)',
        ]);

        foreach ($data as $row) {
        $susutPersen = $row->netto > 0
            ? round(($row->susut / $row->netto) * 100, 2)
            : 0;

        $rows->push([
            Carbon::parse($row->bulan)->locale('id')->translatedFormat('F'), // <-- fix di sini
            $row->netto_kebun,
            $row->netto,
            $row->susut,
            $susutPersen,
        ]);
    }
        return $rows;
    }
}