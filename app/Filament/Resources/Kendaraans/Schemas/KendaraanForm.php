<?php

namespace App\Filament\Resources\Kendaraans\Schemas;

use App\Models\Kendaraan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class KendaraanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_supir')
                    ->label('Nama Supir')
                    ->required(),
                TextInput::make('no_pol')
                    ->label('No. Polisi')
                    ->maxLength(15)
                    ->required()
                    ->afterStateUpdated(function ($state, $set, $record){
                        $exists = Kendaraan::where('no_pol', $state)->when($record, fn ($q) => $q->where('id', '!=', $record->id)) 
                        ->exists();

                        if ($exists){
                            Notification::make()
                                ->title('No. Polisi sudah digunakan.')
                                ->danger()
                                ->send();
                            $set('no_pol', null);
                        }
                    }),
                Select::make('pengangkut_id')
                    ->label('Transporter Name')
                    ->relationship('pengangkut', 'nama_pengangkut')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),
            ]);
    }
}
