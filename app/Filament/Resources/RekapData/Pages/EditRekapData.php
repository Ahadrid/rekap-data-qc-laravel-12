<?php

namespace App\Filament\Resources\RekapData\Pages;

use App\Filament\Resources\RekapData\RekapDataResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRekapData extends EditRecord
{
    protected static string $resource = RekapDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
