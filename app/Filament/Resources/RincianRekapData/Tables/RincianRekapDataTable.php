<?php

namespace App\Filament\Resources\RincianRekapData\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RincianRekapDataTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                /**
                 * 🔹 TANGGAL
                 */
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                /**
                 * 🔹 MASTER DATA
                 */
                TextColumn::make('produk.kode_produk')
                    ->label('Produk')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('mitra.nama_mitra')
                    ->label('Mitra')
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('pengangkut.nama_pengangkut')
                    ->label('Nama Pengangkutan')
                    ->searchable()
                    ->wrap()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('kendaraan.no_pol')
                    ->label('No Polisi')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('kendaraan.nama_supir')
                    ->label('Supir')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),

                /**
                 * 🔹 TIMBANGAN
                 */
                TextColumn::make('bruto_kirim')
                    ->label('Bruto Kirim')
                    ->numeric(0, ',', '.')
                    ->toggleable(isToggledHiddenByDefault: false),
                
                TextColumn::make('tara_kirim')
                    ->label('Tara Kirim')
                    ->numeric(0, ',', '.')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('netto_kebun')
                    ->label('Netto Kebun')
                    ->numeric(0, ',', '.')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('bruto')
                    ->label('Bruto')
                    ->numeric(0, ',', '.')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('tara')
                    ->label('Tara')
                    ->numeric(0, ',', '.')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('netto')
                    ->label('Netto')
                    ->numeric(0, ',', '.')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('susut')
                    ->label('Susut')
                    ->numeric(0, ',', '.')
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->toggleable(isToggledHiddenByDefault: false),

                /**
                 * 🔹 SUSUT %
                 * (virtual, tidak perlu kolom database)
                 */
                TextColumn::make('susut_persen')
                    ->label('Susut (%)')
                    ->suffix('%')
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->weight('bold'),
                // TextColumn::make('susut_persen')
                //     ->label('Susut (%)')
                //     ->getStateUsing(fn ($record) =>
                //         $record->netto_kebun > 0
                //             ? round(($record->susut / $record->netto_kebun) * 100, 2)
                //             : 0
                //     )
                //     ->suffix('%')
                //     ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                //     ->weight('bold')
                //     ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('ffa')
                    ->label('FFA')
                    ->wrap()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),
                
                TextColumn::make('dobi')
                    ->label('Dobi')
                    ->wrap()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('keterangan')
                    ->label('Catatan')
                    ->wrap()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                // nanti bisa tambah filter bulan / produk / mitra
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Detail'),
                    EditAction::make(),
                    DeleteAction::make()
                        ->label('Hapus'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus yang dipilih'),
                ]),
            ])
            ->defaultSort('tanggal', 'desc');
    }
}