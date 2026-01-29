<?php

namespace App\Filament\Resources\Kendaraans\Pages;

use App\Filament\Resources\Kendaraans\KendaraanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKendaraan extends EditRecord
{
    protected static string $resource = KendaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
