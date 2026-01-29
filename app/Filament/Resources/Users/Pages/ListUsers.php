<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UsersResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListUsers extends ListRecords
{
    protected static string $resource = UsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
             ->icon(Heroicon::Plus)
             ->label('Tambah User')
             ->color('success'),
        ];
    }
}
