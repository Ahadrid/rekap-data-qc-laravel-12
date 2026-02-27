<?php

namespace App\Filament\Resources\RincianRekapData;

use App\Filament\Resources\RincianRekapData\Pages\CreateRincianRekapData;
use App\Filament\Resources\RincianRekapData\Pages\EditRincianRekapData;
use App\Filament\Resources\RincianRekapData\Pages\ListRincianRekapData;
use App\Filament\Resources\RincianRekapData\Schemas\RincianRekapDataForm;
use App\Filament\Resources\RincianRekapData\Tables\RincianRekapDataTable;
use App\Models\RekapData;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RincianRekapDataResource extends Resource
{
    protected static ?string $model = RekapData::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static string | UnitEnum | null $navigationGroup = 'Data Rekap';

    public static ?string $pluralModelLabel = 'Rincian Rekap Data';

    public static ?int $navigationSort = 11;

    protected static ?string $recordTitleAttribute = 'Rincian';

    public static function form(Schema $schema): Schema
    {
        return RincianRekapDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RincianRekapDataTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select([
                'id',
                'no_dokumen',
                'urutan_produk',
                'tanggal',
                'tahun', 
                'bulan', 
                'bruto_kirim',
                'tara_kirim',
                'netto_kebun',
                'bruto',
                'tara',
                'netto', 
                'susut', 
                'susut_persen',
                'ffa',
                'dobi',
                'keterangan',
                'produk_id',
                'mitra_id',
                'pengangkut_id',
                'kendaraan_id',
            ])
            ->with([
                'produk:id,kode_produk',
                'mitra:id,kode_mitra',
                'pengangkut:id,kode',
                'kendaraan:id,no_pol,nama_supir',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRincianRekapData::route('/'),
            // 'create' => CreateRincianRekapData::route('/create'),
            // 'edit' => EditRincianRekapData::route('/{record}/edit'),
        ];
    }
}
