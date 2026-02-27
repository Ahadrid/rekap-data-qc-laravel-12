<?php

namespace App\Filament\Resources\RincianRekapData\Tables;

use App\Models\Mitra;
use App\Models\Pengangkut;
use App\Models\Produk;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RincianRekapDataTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(null)
            ->deferLoading()
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->defaultSort('tanggal', 'desc')
            ->columns([
                /**
                 * 🔹 TANGGAL
                 */
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                /**
                 * 🔹 MASTER DATA
                 */
                TextColumn::make('produk.kode_produk')
                    ->label('Produk')
                    ->sortable(),

                TextColumn::make('mitra.kode_mitra')
                    ->label('Nama Rekanan'),

                TextColumn::make('pengangkut.kode')
                    ->label('Nama Pengangkutan'),

                TextColumn::make('kendaraan.no_pol')
                    ->label('No. Kendaraan')
                    ->placeholder('-'),

                TextColumn::make('kendaraan.nama_supir')
                    ->label('Nama Supir')
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('produk_id')
                    ->label('Produk')
                    ->options(
                        Produk::orderBy('kode_produk')
                            ->pluck('kode_produk', 'id')
                            ->toArray()
                    )
                    ->placeholder('Semua Produk')
                    ->multiple(true),

                SelectFilter::make('mitra_id')
                    ->label('Mitra')
                    ->options(
                        Mitra::orderBy('nama_mitra')
                            ->pluck('nama_mitra', 'id')
                            ->toArray()
                    )
                    ->placeholder('Semua Mitra')
                    ->searchable()
                    ->multiple(false),

                SelectFilter::make('pengangkut_id')
                    ->label('Pengangkutan')
                    ->options(
                        Pengangkut::orderBy('kode')
                            ->pluck('kode', 'id')
                            ->toArray()
                    )
                    ->placeholder('Semua Pengangkutan')
                    ->searchable()
                    ->multiple(false),

            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->label('Detail'),
                    DeleteAction::make()->label('Hapus'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus yang dipilih'),
                ]),
            ]);
    }
}