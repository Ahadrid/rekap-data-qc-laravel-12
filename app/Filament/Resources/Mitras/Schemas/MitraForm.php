<?php

namespace App\Filament\Resources\Mitras\Schemas;

use App\Models\Mitra;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class MitraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_mitra')
                    ->label('Nama Mitra')
                    ->required(),
                
                TextInput::make('kode_mitra')
                    ->label('Kode Mitra')
                    ->maxLength(20)
                    ->required()
                    ->afterStateUpdated(function ($state, $set, $record){
                        $exists = Mitra::where('kode_mitra', $state)->when($record, fn ($q) => $q->where('id', '!=', $record->id)) 
                        ->exists();

                        if ($exists){
                            Notification::make()
                                ->title('Kode Mitra sudah digunakan.')
                                ->danger()
                                ->send();
                            $set('kode_mitra', null);
                        }
                    }),

                Select::make('tipe_mitra')
                    ->label('Tipe Mitra')
                    ->options([
                        'perusahaan' => 'Perusahaan',
                        'suplier_luar' => 'Supplier Luar'
                    ])
                    ->searchable()
                    ->placeholder('Tipe Mitra')
                    ->required(),

                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
