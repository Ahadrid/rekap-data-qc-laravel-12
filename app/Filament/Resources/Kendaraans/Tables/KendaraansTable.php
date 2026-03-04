<?php

namespace App\Filament\Resources\Kendaraans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KendaraansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_pol')
                    ->label('Nomor Kendaraan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('nama_supir')
                    ->label('Nama Supir')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('pengangkut.nama_pengangkut')
                    ->label('Pengangkut')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
                SelectFilter::make('pengangkut_id')
                    ->label('Pengangkutan')
                    ->relationship('pengangkut', 'kode')
                    ->searchable(),
            ])
            ->deferLoading()
            ->paginated([10,25,50])
            ->defaultPaginationPageOption(10)
            ->recordAction(null)
            ->recordUrl(null)
            ->recordActions([
                ViewAction::make()
                        ->label('Detail')
                        ->modalHeading('Informasi Kendaraan')
                        ->modalWidth('sm')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->schema(fn (Schema $schema) => $schema->components([
                            TextEntry::make('no_pol')
                                        ->label('Nomor Kendaraan')
                                        ->placeholder('-'),

                            TextEntry::make('nama_supir')
                                        ->label('Nama Supir'),
                            
                            TextEntry::make('pengangkut.nama_pengangkut')
                                        ->label('Nama Pengangkutan'),
                ])),
                EditAction::make()
                    ->modalWidth('xl'),
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
