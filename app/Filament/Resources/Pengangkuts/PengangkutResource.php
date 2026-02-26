<?php

namespace App\Filament\Resources\Pengangkuts;

use App\Filament\Resources\Pengangkuts\Pages\CreatePengangkut;
use App\Filament\Resources\Pengangkuts\Pages\EditPengangkut;
use App\Filament\Resources\Pengangkuts\Pages\ListPengangkuts;
use App\Filament\Resources\Pengangkuts\Schemas\PengangkutForm;
use App\Filament\Resources\Pengangkuts\Tables\PengangkutsTable;
use App\Models\Pengangkut;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PengangkutResource extends Resource
{
    protected static ?string $model = Pengangkut::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string | UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'pengangkut';

    protected static ?string $pluralModelLabel = 'Pengangkut';

    public static function form(Schema $schema): Schema
    {
        return PengangkutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengangkutsTable::configure($table);
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
        return 'Total Pengangkut';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengangkuts::route('/'),
            // 'create' => CreatePengangkut::route('/create'),
            // 'edit' => EditPengangkut::route('/{record}/edit'),
        ];
    }
}
