<?php

namespace App\Services;

class RekapDataCalculator
{
    public static function calculate(array $row): array
    {
        $nettoKebun = (float) ($row['netto_unload'] ?? 0);
        $netto      = (float) ($row['berat_bersih'] ?? 0);
        $susut      = $netto - $nettoKebun;

        $susutPersen = $nettoKebun > 0
            ? round(($susut / $nettoKebun) * 100, 2)
            : 0;

        $ffa  = (float) ($row['ffa_val'] ?? 0);
        $dobi = (float) ($row['dobi_val'] ?? 0);

        if ($ffa > 999)  $ffa  /= 1000;
        if ($dobi > 999) $dobi /= 1000;

        return compact(
            'nettoKebun',
            'netto',
            'susut',
            'susutPersen',
            'ffa',
            'dobi'
        );
    }
}
