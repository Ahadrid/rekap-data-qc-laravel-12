<?php

namespace App\Filament\Resources\Users\Tables;

use Dom\Text;
use Dotenv\Util\Str;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable(),
                TextColumn::make('email'),
                
                TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state):string => match($state){
                        'superadmin' => 'primary',
                        'admin' => 'danger',
                        'qc' => 'warning',
                        'staff' => 'info',
                        default => 'gray'
                    }),
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
            ->deferLoading()
            ->paginated([10,25,50])
            ->defaultPaginationPageOption(10)
            ->recordAction(null)
            ->recordUrl(null)
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
