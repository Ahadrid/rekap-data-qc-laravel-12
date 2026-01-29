<?php

namespace App\Filament\Resources\RincianRekapData\Pages;

use App\Filament\Resources\RincianRekapData\RincianRekapDataResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRincianRekapData extends EditRecord
{
    protected static string $resource = RincianRekapDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
