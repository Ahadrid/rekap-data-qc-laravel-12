<?php

namespace App\Exports\Sheets;

use App\Exports\RekapDataExport;
use Maatwebsite\Excel\Concerns\WithTitle;

class AllSheet extends RekapDataExport implements WithTitle
{
    public function title(): string
    {
        return 'All';
    }
}
