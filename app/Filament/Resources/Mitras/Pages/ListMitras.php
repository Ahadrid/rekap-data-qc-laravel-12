<?php

namespace App\Filament\Resources\Mitras\Pages;

use App\Filament\Resources\Mitras\MitraResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListMitras extends ListRecords
{
    protected static string $resource = MitraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::Plus)
                ->label('Tambah Mitra')
                ->color('success'),
        ];
    }

    protected function getTableActionApperance(): string
    {
        return 'modal';
    }
}
