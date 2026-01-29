<?php

namespace App\Filament\Resources\RekapData\Pages;

use App\Filament\Resources\RekapData\RekapDataResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRekapData extends ViewRecord
{
    protected static string $resource = RekapDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
