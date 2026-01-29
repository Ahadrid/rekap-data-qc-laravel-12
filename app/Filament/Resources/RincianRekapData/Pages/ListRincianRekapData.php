<?php

namespace App\Filament\Resources\RincianRekapData\Pages;

use App\Exports\CompanyExport;
use App\Exports\RekapDataExport;
use App\Filament\Resources\RincianRekapData\RincianRekapDataResource;
use App\Imports\RekapDataImport as Rincian;
use App\Models\Produk;
use App\Models\RekapData;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

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
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->storeFiles(false), // 🔥 tidak masuk storage
                ])
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
                            "Duplikat dilewati: {$import->skipped}"
                        )
                        ->success()
                        ->send();
                }),

            Action::make('export')
                ->label('Export Excel')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    Select::make('mode')
                        ->label('Jenis Export')
                        ->options([
                            'suplier_luar' => 'Supplier Luar',
                            'bim_rengat'    => 'Berlian Inti Mekar - Rengat',
                            'bim_siak'      => 'Berlian Inti Mekar - Siak',
                            'mul'           => 'Mutiara Unggul Lestari',
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
                        ->nullable(),
                ])
                ->action(function (array $data) {

                    $filters = [
                        'mode'      => $data['mode'],
                        'produk_id' => $data['produk_id'] ?? null,
                    ];

                    $namaFile = 'rekap-' . match ($data['mode']) {
                        'suplier_luar' => 'suplier-luar',
                        'bim_rengat'    => 'bim-rengat',
                        'bim_siak'      => 'bim-siak',
                        'mul'           => 'mul',
                    };

                    if (! empty($data['produk_id'])) {
                        $produk = Produk::find($data['produk_id']);
                        $namaFile .= '-' . str($produk->nama_produk)->slug('-');
                    }

                    $namaFile .= '.xlsx';

                    return Excel::download(
                        new CompanyExport([
                            'produk_id' => $data['produk_id'],
                            'mode'      => $data['mode'], // bim_rengat | bim_siak | mul
                        ]),
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
