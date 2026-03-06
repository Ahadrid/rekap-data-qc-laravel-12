<?php
// App/Exports/Sheets/RekapSupplierLuarSheet.php
namespace App\Exports\Sheets;

use App\Exports\Styles\RekapSheetStyler;
use App\Exports\Styles\StyleTracker;
use App\Models\Mitra;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class RekapSupplierLuarSheet implements FromCollection, WithTitle, WithEvents
{
    protected array $filters;
    protected Collection $mitras;
    protected StyleTracker $styleTracker;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
        $this->mitras = $this->loadMitras();
        $this->styleTracker = new StyleTracker();
    }

    public function title(): string
    {
        return 'REKAP';
    }

    public function collection()
    {
        // ✅ Pass $mitras ke builder — sama seperti RekapSheet pass $pengangkuts
        $builder = new RekapSupplierLuarSheetBuilder(
            $this->mitras,
            $this->styleTracker,
            $this->filters
        );

        return $builder->build();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $styler = new RekapSheetStyler(
                    $event->sheet->getDelegate(),
                    $this->styleTracker
                );
                $styler->apply();
            },
        ];
    }

    protected function loadMitras(): Collection
    {
        return Mitra::where('tipe_mitra', 'suplier_luar')
            ->whereHas('rekapData', function ($q) {
                if (!empty($this->filters['produk_id'])) {
                    $q->where('produk_id', $this->filters['produk_id']);
                }
                if (!empty($this->filters['tanggal_mulai']) && !empty($this->filters['tanggal_akhir'])) {
                    $q->whereBetween('tanggal', [
                        $this->filters['tanggal_mulai'],
                        $this->filters['tanggal_akhir'],
                    ]);
                }
            })
            ->orderBy('nama_mitra')
            ->get();
    }
}