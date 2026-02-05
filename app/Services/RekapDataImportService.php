<?php

namespace App\Services;

use App\Actions\ResolveRekapMasterData;
use App\Models\RekapData;
use App\Helpers\KodeGenerator;

class RekapDataImportService
{
    public static function isDuplicate(array $keys): bool
    {
        return RekapData::where($keys)->exists();
    }

    public static function generateKode($produk): array
    {
        return KodeGenerator::generateRekapData($produk);
    }
}
