<?php

namespace App\Exports\Sheets;

use App\Exports\RekapDataExport;
use App\Models\Mitra;
use App\Query\SupplierLuarQueryExport;
use Maatwebsite\Excel\Concerns\WithTitle;

class SupplierLuarSheet extends RekapDataExport implements WithTitle
{
    protected Mitra $mitra;

    public function __construct(array $filters, Mitra $mitra)
    {
        $filters['mitra_id'] = $mitra->id;
        parent::__construct($filters);
        $this->mitra = $mitra;
    }

    public function query()
    {
        return SupplierLuarQueryExport::build($this->filters);
    }

    public function title(): string
    {
        return str($this->mitra->kode_mitra)
            ->limit(31, '')
            ->toString();
    }
}