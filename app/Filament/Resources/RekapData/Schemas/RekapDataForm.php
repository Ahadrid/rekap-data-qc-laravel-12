<?php

namespace App\Filament\Resources\RekapData\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class RekapDataForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tambah Informasi Rekap Data')
                ->schema([
                    Select::make('produk_id')
                        ->label('Nama Produk')
                        ->relationship('produk', 'nama_produk')
                        ->searchable()
                        ->preload()
                        ->required(),
                        
                    Select::make('mitra_id')
                        ->label('Nama Mitra')
                        ->relationship('mitra', 'nama_mitra')
                        ->searchable()
                        ->preload()
                        ->required(),
                    
                    // Filter Kendaraan berdasarkan Pengangkut (Opsional tapi disarankan)
                    Select::make('pengangkut_id')
                        ->label('Nama Pengangkut')
                        ->relationship('pengangkut', 'nama_pengangkut')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),
                    
                    Select::make('kendaraan_id')
                        ->label('No Polisi')
                        ->relationship('kendaraan', 'no_pol')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($state) {
                                // Kita ambil data kendaraan beserta pengangkut (Vendor)-nya
                                $kendaraan = \App\Models\Kendaraan::with('pengangkut')->find($state);
                                
                                // Set otomatis Nama Supir (diambil dari kolom di tabel kendaraan)
                                $set('nama_supir', $kendaraan?->nama_supir);
                                
                                // Set otomatis Nama Pengangkut (Vendor PT/CV dari relasi)
                                $set('nama_pengangkut', $kendaraan?->pengangkut?->nama_pengangkut);
                            } else {
                                $set('nama_supir', null);
                                $set('nama_pengangkut', null);
                            }
                        })
                        ->required(),

                    TextInput::make('nama_supir')
                        ->label('Nama Supir')
                        ->disabled()
                        ->placeholder('Otomatis terisi...')
                        ->dehydrated(false),
                        
                    Select::make('bulan')
                        ->options([
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                        ])
                        ->required(),
                        
                    TextInput::make('tahun')
                        ->numeric()
                        ->default(date('Y'))
                        ->required(),
                ])->columns(2),

            Section::make('Data rekap CPO/PK')
                ->schema([
                    TextInput::make('netto_kebun')
                        ->numeric()
                        ->live(onBlur: true)
                        ->required(),
                    
                    TextInput::make('netto')
                        ->numeric()
                        ->live(onBlur: true)
                        ->required(),

                    TextInput::make('susut')
                        ->numeric()
                        ->live(onBlur: true)
                        ->required(),
                        
                    TextInput::make('persen_susut')
                        ->numeric()
                        ->label('Persen Susut (%)')
                        ->readOnly()
                        ->placeholder('Otomatis terhitung')
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $netto = (float) $get('netto_kebun');
                            $susut = (float) $get('susut');
                            
                            if ($netto > 0) {
                                $hasil = ($susut / $netto) * 100;
                                $set('persen_susut', round($hasil, 2));
                            } else {
                                $set('persen_susut', 0);
                            }
                        }),
                ])->columns(3),
        ]);
    }
}