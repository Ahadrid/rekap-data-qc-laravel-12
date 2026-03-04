<?php

namespace App\Filament\Resources\RincianRekapData\Tables;

use App\Models\Mitra;
use App\Models\Pengangkut;
use App\Models\Produk;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;  // ✅ TextEntry tetap dari sini
use Filament\Schemas\Components\Section;      // ✅ Section sekarang dari sini (v4)
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RincianRekapDataTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(null)
            ->deferLoading()
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->defaultSort('tanggal', 'desc')
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('produk.kode_produk')
                    ->label('Produk')
                    ->sortable(),

                TextColumn::make('mitra.kode_mitra')
                    ->label('Nama Rekanan'),

                TextColumn::make('pengangkut.kode')
                    ->label('Nama Pengangkutan'),

                TextColumn::make('kendaraan.no_pol')
                    ->label('No. Kendaraan')
                    ->placeholder('-'),

                TextColumn::make('kendaraan.nama_supir')
                    ->label('Nama Supir')
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('produk_id')
                    ->label('Produk')
                    ->options(
                        Produk::orderBy('kode_produk')
                            ->pluck('kode_produk', 'id')
                            ->toArray()
                    )
                    ->placeholder('Semua Produk')
                    ->multiple(true),

                SelectFilter::make('mitra_id')
                    ->label('Mitra')
                    ->options(
                        Mitra::orderBy('nama_mitra')
                            ->pluck('nama_mitra', 'id')
                            ->toArray()
                    )
                    ->placeholder('Semua Mitra')
                    ->searchable()
                    ->multiple(false),

                SelectFilter::make('pengangkut_id')
                    ->label('Pengangkutan')
                    ->options(
                        Pengangkut::orderBy('kode')
                            ->pluck('kode', 'id')
                            ->toArray()
                    )
                    ->placeholder('Semua Pengangkutan')
                    ->searchable()
                    ->multiple(true),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Detail')
                        ->modalHeading(
                            fn($record) => 'Detail Rekap — ' . Carbon::parse($record->tanggal)->locale('id')->translatedFormat('d F Y')
                        )
                        ->modalWidth('2xl')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->schema(fn (Schema $schema) => $schema->components([
                             /**
                             * 🔸 INFORMASI DOKUMEN
                             */
                            Section::make('Informasi Dokumen')
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('no_dokumen')
                                        ->label('No. Dokumen')
                                        ->placeholder('-'),

                                    TextEntry::make('tanggal')
                                        ->label('Tanggal')
                                        ->date('d M Y'),

                                    TextEntry::make('produk.kode_produk')
                                        ->label('Produk'),

                                    TextEntry::make('urutan_produk')
                                        ->label('Urutan Produk')
                                        ->placeholder('-'),
                            ]),

                            /**
                             * 🔸 REKANAN & PENGANGKUTAN
                             */
                            Section::make('Rekanan & Pengangkutan')
                                    ->columns(2)
                                    ->schema([
                                    TextEntry::make('mitra.nama_mitra')
                                            ->label('Nama Rekanan')
                                            ->placeholder('-'),

                                    TextEntry::make('pengangkut.nama_pengangkut')
                                            ->label('Nama Pengangkutan'),

                                    TextEntry::make('kendaraan.no_pol')
                                            ->label('No. Kendaraan')
                                            ->placeholder('-'),

                                    TextEntry::make('kendaraan.nama_supir')
                                            ->label('Nama Supir')
                                            ->placeholder('-'),
                            ]),

                            /**
                             * 🔸 TIMBANG KEBUN (KIRIM)
                             */
                            Section::make('Timbang Kebun (Kirim)')
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('bruto_kirim')
                                        ->label('Bruto Kirim')
                                        ->numeric(0, ',', '.')
                                        ->placeholder('-'),

                                    TextEntry::make('tara_kirim')
                                        ->label('Tara Kirim')
                                        ->numeric(0, ',', '.')
                                        ->placeholder('-'),

                                    TextEntry::make('netto_kebun')
                                        ->label('Netto Kebun')
                                        ->numeric(0, ',', '.')
                                        ->placeholder('-'),
                            ]),
                            
                            /**
                             * 🔸 TIMBANG PABRIK (TERIMA)
                             */
                            Section::make('Timbang Pabrik (Terima)')
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('bruto')
                                        ->label('Bruto')
                                        ->numeric(0, ',', '.')
                                        ->placeholder('-'),

                                    TextEntry::make('tara')
                                        ->label('Tara')
                                        ->numeric(0, ',', '.')
                                        ->placeholder('-'),

                                    TextEntry::make('netto')
                                        ->label('Netto')
                                        ->numeric(0, ',', '.')
                                        ->placeholder('-'),
                            ]),
                            
                            /**
                             * 🔸 SUSUT & KUALITAS
                             */
                            Section::make('Susut & Kualitas')
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('susut')
                                        ->label('Susut')
                                        ->numeric(0, ',', '.')
                                        ->placeholder('-'),

                                    TextEntry::make('susut_persen')
                                        ->label('Susut (%)')
                                        ->numeric(decimalPlaces: 2)
                                        ->suffix(' %')
                                        ->placeholder('-'),

                                    TextEntry::make('ffa')
                                        ->label('FFA')
                                        ->formatStateUsing(fn($state) => $state !== null 
                                            ? number_format($state, 2, ',', '.') 
                                            : '-')
                                        ->placeholder('-'),

                                    TextEntry::make('dobi')
                                        ->label('DOBI')
                                        ->formatStateUsing(fn($state) => $state !== null 
                                            ? number_format($state, 2, ',', '.') 
                                            : '-')
                                        ->placeholder('-'),
                            ]),

                            /**
                             * 🔸 KETERANGAN
                             */
                            Section::make('Keterangan')
                                ->schema([
                                    TextEntry::make('keterangan')
                                        ->label('')
                                        ->placeholder('Tidak ada keterangan')
                                        ->columnSpanFull(),
                                ])
                                // ->collapsed(fn($record) => blank($record->keterangan)),
                        ])),
                    DeleteAction::make()->label('Hapus'),
                ])
                ->label('Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus yang dipilih'),
                ]),
            ]);
    }
}