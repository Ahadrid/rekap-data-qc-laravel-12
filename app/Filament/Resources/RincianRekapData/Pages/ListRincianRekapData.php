<?php

namespace App\Filament\Resources\RincianRekapData\Pages;

use App\Exports\CompanyExport;
use App\Filament\Resources\RincianRekapData\RincianRekapDataResource;
use App\Imports\RekapDataImport as Rincian;
use App\Models\Produk;
use App\Models\RekapData;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SupplierLuarExport;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Pagination\CursorPaginator;

class ListRincianRekapData extends ListRecords
{
    protected static string $resource = RincianRekapDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
            Action::make('importExcel')
                ->label('Import Excel')
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn () => 
                    in_array(Auth::user()->role, ['admin', 'superadmin'])
                )
                ->authorize(fn () => 
                    Gate::allows('import-rekap-data')
                )
                ->schema(fn (Schema $schema) => $schema -> components([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->storeFiles(false),
                ]))
                ->action(function (array $data) {

                    Gate::authorize('import-rekap-data');

                    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file */
                    $file = $data['file'];

                    $import = new Rincian();

                    Excel::import(
                        $import,
                        $file->getRealPath()
                    );

                    /* ===============================
                    * NOTIFIKASI NOMOR TERAKHIR
                    * =============================== */
                    $messages = [];

                    foreach (Produk::all() as $produk) {
                        $last = RekapData::where('produk_id', $produk->id)
                            ->max('urutan_produk');

                        if ($last) {
                            $messages[] = "{$produk->kode_produk}-{$last}";
                        }
                    }

                    Notification::make()
                        ->title('Import selesai')
                        ->body(
                            "Nomor terakhir:\n" .
                            implode("\n", $messages) .
                            "\n\nBerhasil: {$import->inserted}\n" .
                            "Duplikat/dilewati: {$import->skipped}"
                        )
                        ->success()
                        ->send();
                }),

            Action::make('export')
                ->label('Export Excel')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray')
                ->schema(fn (Schema $schema) => $schema->components([
                    Select::make('mode')
                        ->label('Jenis Export')
                        ->options([
                            'suplier_luar' => 'Supplier Luar',
                            'bim_rengat'   => 'Berlian Inti Mekar - Rengat',
                            'bim_siak'     => 'Berlian Inti Mekar - Siak',
                            'mul'          => 'Mutiara Unggul Lestari',
                        ])
                        ->required(),

                    Select::make('produk_id')
                        ->label('Produk')
                        ->options(
                            Produk::query()
                                ->orderBy('nama_produk')
                                ->pluck('nama_produk', 'id')
                        )
                        ->searchable()
                        ->placeholder('Semua Produk')
                        ->required(),

                    DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->required()
                        ->default(now()->startOfMonth()),

                    DatePicker::make('tanggal_akhir')
                        ->label('Tanggal Akhir')
                        ->required()
                        ->default(now()->endOfMonth()),
                ]))
                ->action(function (array $data) {

                    $filters = [
                        'mode'      => $data['mode'],
                        'produk_id' => $data['produk_id'] ?? null,
                        'tanggal_mulai' => Carbon::parse($data['tanggal_mulai'])->startOfDay(),
                        'tanggal_akhir' => Carbon::parse($data['tanggal_akhir'])->endOfDay(),
                    ];

                    $namaFile = 'rekap-' . match ($data['mode']) {
                        'suplier_luar' => 'suplier-luar',
                        'bim_rengat'    => 'bim-rengat',
                        'bim_siak'      => 'bim-siak',
                        'mul'           => 'mul',
                    };

                    if (! empty($data['produk_id'])) {
                        $produk = Produk::find($data['produk_id']);
                        $namaFile .= '-' . str($produk->kode_produk)->slug('-')
                                        .'_'
                                        . Carbon::parse($data['tanggal_mulai'])->format('d-M-Y')
                                        . '_sd_'
                                        . Carbon::parse($data['tanggal_akhir'])->format('d-M-Y');
                    }

                    $namaFile .= '.xlsx';

                    /* ===============================
                    * EXPORT PERUSAHAAN (LAMA, AMAN)
                    * =============================== */

                    return Excel::download(
                        match ($data['mode']) {
                            'suplier_luar' => new SupplierLuarExport($filters),
                            default        => new CompanyExport($filters),
                        },
                        $namaFile
                    );
                }),

        ];
    }

    protected function getTableActionApperance(): string
    {
        return 'modal';
    }
}
