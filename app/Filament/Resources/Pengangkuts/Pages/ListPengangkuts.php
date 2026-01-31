<?php

namespace App\Filament\Resources\Pengangkuts\Pages;

use App\Filament\Resources\Pengangkuts\PengangkutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPengangkuts extends ListRecords
{
    protected static string $resource = PengangkutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make()
            //     ->icon(Heroicon::Plus)
            //     ->label('Tambah Pengangkut')
            //     ->color('success'),
        ];
    }
}
