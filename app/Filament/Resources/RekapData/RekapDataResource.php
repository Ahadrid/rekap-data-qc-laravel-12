<?php

namespace App\Filament\Resources\RekapData;

use App\Filament\Resources\RekapData\Pages\CreateRekapData;
use App\Filament\Resources\RekapData\Pages\EditRekapData;
use App\Filament\Resources\RekapData\Pages\ListRekapData;
use App\Filament\Resources\RekapData\Pages\ViewRekapData;
use App\Filament\Resources\RekapData\Schemas\RekapDataForm;
use App\Filament\Resources\RekapData\Schemas\RekapDataInfolist;
use App\Filament\Resources\RekapData\Tables\RekapDataTable;
use App\Models\RekapData;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RekapDataResource extends Resource
{
    protected static ?string $model = RekapData::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderOpen;

    protected static string | UnitEnum | null $navigationGroup = 'Data Rekap';

    protected static ?string $recordTitleAttribute = 'Rekap Data';

    protected static ?string $pluralModelLabel = 'Rekap Data';

    protected static ?int $navigationSort = 10;


    public static function form(Schema $schema): Schema
    {
        return RekapDataForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RekapDataInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RekapDataTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRekapData::route('/'),
            // 'create' => CreateRekapData::route('/create'),
            // 'view' => ViewRekapData::route('/{record}'),
            // 'edit' => EditRekapData::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
