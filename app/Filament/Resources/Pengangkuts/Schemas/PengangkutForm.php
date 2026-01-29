<?php

namespace App\Filament\Resources\Pengangkuts\Schemas;

use App\Models\Pengangkut;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class PengangkutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_pengangkut')
                    ->required(),
                TextInput::make('kode')
                    ->label('Kode Pengangkut')
                    ->maxLength(20)
                    ->required()
                    ->afterStateUpdated(function ($state, $set, $record){
                        $exists = Pengangkut::where('kode', $state)->when($record, fn ($q) => $q->where('id', '!=', $record->id)) 
                        ->exists();

                        if ($exists){
                            Notification::make()
                                ->title('Kode Pengangkut sudah digunakan.')
                                ->danger()
                                ->send();
                            $set('kode', null);
                        }
                    }),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
