<?php

namespace App\Filament\Resources\Kendaraans\Pages;

use App\Filament\Resources\Kendaraans\KendaraanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListKendaraans extends ListRecords
{
    protected static string $resource = KendaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->color('success')
                ->label('Tambah Kendaraan')
                ->icon(Heroicon::Plus),
        ];
    }

    protected function getTableActionApperance(): string
    {
        return 'modal';
    }
}
