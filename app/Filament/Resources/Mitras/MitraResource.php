<?php

namespace App\Filament\Resources\Mitras;

use App\Filament\Resources\Mitras\Pages\CreateMitra;
use App\Filament\Resources\Mitras\Pages\EditMitra;
use App\Filament\Resources\Mitras\Pages\ListMitras;
use App\Filament\Resources\Mitras\Schemas\MitraForm;
use App\Filament\Resources\Mitras\Tables\MitrasTable;
use App\Models\Mitra;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MitraResource extends Resource
{
    protected static ?string $model = Mitra::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string | UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'mitra';

    protected static ?string $pluralModelLabel = 'Mitra';

    public static function form(Schema $schema): Schema
    {
        return MitraForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MitrasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Total Mitra';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMitras::route('/'),
            // 'create' => CreateMitra::route('/create'),
            // 'edit' => EditMitra::route('/{record}/edit'),
        ];
    }
}
