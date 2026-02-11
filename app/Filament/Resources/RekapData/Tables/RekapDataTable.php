<?php

namespace App\Filament\Resources\RekapData\Tables;

use App\Helpers\DateFormatHelpers;
use App\Models\Mitra;
use App\Models\Produk;
use App\Models\RekapData;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RekapDataTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bulan')
                    ->label('Bulan')
                    ->state(fn (array $record) => DateFormatHelpers::bulanIndo($record['bulan'])),

                TextColumn::make('netto_kebun')
                    ->label('Netto Kebun')
                    ->numeric()
                    ->state(fn (array $record) => $record['netto_kebun']),

                TextColumn::make('netto')
                    ->label('Netto')
                    ->numeric()
                    ->state(fn (array $record) => $record['netto']),

                TextColumn::make('susut')
                    ->label('Susut')
                    ->numeric()
                    ->state(fn (array $record) => $record['susut'])
                    ->color(fn ($state) => $state <= 0 ? 'danger' : 'success'),

                TextColumn::make('susut_persen')
                    ->label('Susut (%)')
                    ->suffix('%')
                    ->state(fn (array $record) => $record['susut_persen'])
                    ->color(fn ($state) => $state <= 0 ? 'danger' : 'success'),

            ])
            ->filters([
                SelectFilter::make('mitra_id')
                    ->label('Mitra')
                    ->options(
                        Mitra::orderBy('nama_mitra')
                            ->pluck('nama_mitra', 'id')
                            ->toArray()
                    )
                    ->placeholder('Semua Mitra')
                    ->multiple(false),

                SelectFilter::make('produk_id')
                    ->label('Produk')
                    ->options(
                        Produk::orderBy('kode_produk')
                            ->pluck('kode_produk', 'id')
                            ->toArray()
                    )
                    ->placeholder('Semua Produk')
                    ->multiple(false),

                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(
                        RekapData::query()
                            ->select('tahun')
                            ->distinct()
                            ->orderBy('tahun', 'desc')
                            ->pluck('tahun', 'tahun')
                            ->toArray()
                    )
                    ->placeholder('Semua Tahun'),
            ])
            ->recordUrl(null)
            ->recordAction(null)
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('tanggal', 'desc');
    }
}
