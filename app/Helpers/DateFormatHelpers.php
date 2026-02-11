<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateFormatHelpers
{
    public static function bulanIndo(int $bulan): string
    {
        return Carbon::create()
            ->locale('id')
            ->month($bulan)
            ->translatedFormat('F');
    }
}