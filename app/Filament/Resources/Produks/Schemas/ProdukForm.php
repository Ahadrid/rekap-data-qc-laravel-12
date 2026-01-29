<?php

namespace App\Filament\Resources\Produks\Schemas;

use App\Models\Produk;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class ProdukForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_produk')
                    ->label('Nama Produk')
                    ->required(),
                TextInput::make('kode_produk')
                    ->label('Kode Produk')
                    ->maxLength(20)
                    ->required()
                    ->afterStateUpdated(function ($state, $set, $record){
                        $exists = Produk::where('kode_produk', $state)->when($record, fn ($q) => $q->where('id', '!=', $record->id)) 
                        ->exists();

                        if ($exists){
                            Notification::make()
                                ->title('Kode Produk sudah digunakan.')
                                ->danger()
                                ->send();
                            $set('kode_produk', null);
                        }
                    }),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
