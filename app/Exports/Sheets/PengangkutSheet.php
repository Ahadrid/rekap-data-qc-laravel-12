<?php

namespace App\Exports\Sheets;

use App\Exports\RekapDataExport;
use Maatwebsite\Excel\Concerns\WithTitle;

class PengangkutSheet extends RekapDataExport implements WithTitle
{
    protected $pengangkut;

    public function __construct(array $filters, $pengangkut)
    {
        parent::__construct($filters);
        $this->pengangkut = $pengangkut;
    }

    public function title(): string
    {
        return str($this->pengangkut->kode)->limit(31)->toString();
    }

    public function query()
    {
        return parent::query()
            ->where('pengangkut_id', $this->pengangkut->id);
    }
}
