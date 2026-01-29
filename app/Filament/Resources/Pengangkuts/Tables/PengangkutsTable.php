<?php

namespace App\Filament\Resources\Pengangkuts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PengangkutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
               TextColumn::make('nama_pengangkut')
                    ->label('Nama Pengangkutan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('kode')
                    ->label('Kode Pengangkutan')
                    ->searchable()
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
            ->recordAction(null)
            ->recordUrl(null)
            ->recordActions([
                ViewAction::make()
                    ->label('Detail'),
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
