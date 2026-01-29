<?php

namespace App\Filament\Resources\Pengangkuts\Pages;

use App\Filament\Resources\Pengangkuts\PengangkutResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPengangkut extends EditRecord
{
    protected static string $resource = PengangkutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
