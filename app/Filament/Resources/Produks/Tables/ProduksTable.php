<?php

namespace App\Filament\Resources\Produks\Tables;

use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProduksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->paginated([10,25,50])
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('nama_produk')
                    ->label('Nama Produk')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('kode_produk')
                    ->label('Singkatan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->recordActions([
                ViewAction::make()
                        ->label('Detail')
                        ->modalHeading('Informasi Produk')
                        ->modalWidth('sm')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->schema(fn (Schema $schema) => $schema->components([
                            TextEntry::make('nama_produk')
                                        ->label('Nama Produk')
                                        ->placeholder('-'),

                            TextEntry::make('kode_produk')
                                        ->label('Singkatan'),
                ])),
                EditAction::make(),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus yang dipilih'),
                ]),
            ]);
    }
}
