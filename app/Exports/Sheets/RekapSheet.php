<?php
namespace App\Exports\Sheets;

use App\Exports\Styles\RekapSheetStyler;
use App\Exports\Styles\StyleTracker;
use App\Models\RekapData;
use App\Models\Pengangkut;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class RekapSheet implements FromCollection, WithTitle, WithEvents
{
    protected array $filters;
    protected Collection $pengangkuts;
    protected StyleTracker $styleTracker;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
        $this->pengangkuts = $this->loadPengangkuts();
        $this->styleTracker = new StyleTracker();
    }

    public function title(): string
    {
        return 'Rekap';
    }

    public function collection()
    {
        $builder = new RekapSheetBuilder(
            $this->pengangkuts,
            $this->styleTracker,
            $this->filters
        );

        return $builder->build();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $styler = new RekapSheetStyler($event->sheet->getDelegate(), $this->styleTracker);
                $styler->apply();
            },
        ];
    }

    protected function loadPengangkuts(): Collection
    {
        return Pengangkut::whereHas('rekapData', function ($q) {
            if (!empty($this->filters['produk_id'])) {
                $q->where('produk_id', $this->filters['produk_id']);
            }
            $q->whereHas('mitra', fn($m) => $this->applyMitraFilter($m));
        })
        ->orderBy('kode')
        ->get();
    }

    protected function applyMitraFilter($query): void
    {
        match ($this->filters['mode'] ?? null) {
            'bim_rengat' => $query->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                ->where('nama_mitra', 'ILIKE', '%RENGAT%'),
            'bim_siak' => $query->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                ->where('nama_mitra', 'ILIKE', '%SIAK%'),
            'mul' => $query->where('nama_mitra', 'ILIKE', '%MUTIARA UNGGUL LESTARI%'),
            default => null,
        };
    }
}