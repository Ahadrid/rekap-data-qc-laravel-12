<?php

namespace App\Filament\Resources\RincianRekapData\Schemas;

use App\Models\Kendaraan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class RincianRekapDataForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            /* =====================================================
             * INFORMASI UMUM
             * ===================================================== */
            Section::make('Informasi Rekap Data')
                ->schema([
                    DatePicker::make('tanggal')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($state) {
                                $set('tahun', date('Y', strtotime($state)));
                                $set('bulan', (int) date('n', strtotime($state)));
                            }
                        }),

                    TextInput::make('tahun')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),

                    Select::make('bulan')
                        ->options([
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                            4 => 'April', 5 => 'Mei', 6 => 'Juni',
                            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                        ])
                        ->disabled()
                        ->dehydrated(),

                    Select::make('produk_id')
                        ->label('Produk')
                        ->relationship('produk', 'nama_produk')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('mitra_id')
                        ->label('Mitra')
                        ->relationship('mitra', 'nama_mitra')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('pengangkut_id')
                        ->label('Pengangkut')
                        ->relationship('pengangkut', 'nama_pengangkut')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('kendaraan_id')
                        ->label('No Polisi')
                        ->relationship('kendaraan', 'no_pol')
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->columns(2),

            /* =====================================================
             * DATA TIMBANGAN
             * ===================================================== */
            Section::make('Data Timbangan')
                ->schema([
                    TextInput::make('bruto_kirim')->numeric()->required(),
                    TextInput::make('tara_kirim')->numeric()->required(),
                    TextInput::make('netto_kebun')->numeric()->required(),

                    TextInput::make('bruto')->numeric()->required(),
                    TextInput::make('tara')->numeric()->required(),

                    TextInput::make('netto')
                        ->numeric()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $nettoKebun = (float) $get('netto_kebun');
                            $netto      = (float) $get('netto');

                            $susut = $netto - $nettoKebun;
                            $set('susut', $susut);

                            $set(
                                'susut_persen',
                                $nettoKebun > 0 ? round(($susut / $nettoKebun) * 100, 4) : 0
                            );
                        }),

                    TextInput::make('susut')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('susut_persen')
                        ->numeric()
                        ->label('Susut (%)')
                        ->disabled()
                        ->dehydrated(),
                ])
                ->columns(3),

            /* =====================================================
             * DATA KUALITAS
             * ===================================================== */
            Section::make('Data Kualitas')
                ->schema([
                    TextInput::make('ffa')->numeric()->required(),
                    TextInput::make('dobi')->numeric()->required(),
                    TextInput::make('keterangan')->maxLength(255),
                ])
                ->columns(3),
        ]);
    }
}
