<?php

namespace App\Filament\Resources\Pengangkuts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use ZipStream\CentralDirectoryFileHeader;

class PengangkutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(null)
            ->deferLoading()
            ->paginated([10,25,50])
            ->defaultPaginationPageOption(10)
            ->columns([
               TextColumn::make('nama_pengangkut')
                    ->label('Nama Pengangkutan')
                    ->searchable()
                    ->width('60%')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('kode')
                    ->label('Singkatan')
                    ->searchable()
                    // ->alignCenter()
                    ->width('40%')
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
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
            ->recordActionsColumnLabel('Aksi')
            ->recordActions([
                ViewAction::make()
                        ->label('Detail')
                        ->modalHeading('Informasi Pengangkutan')
                        ->modalWidth('md')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->schema(fn (Schema $schema) => $schema->components([
                            TextEntry::make('nama_pengangkut')
                                        ->label('Nama Pengangkut')
                                        ->placeholder('-'),

                            TextEntry::make('kode')
                                        ->label('Singkatan'),
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
