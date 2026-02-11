<?php

namespace App\Exports\Sheets;

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

    public function __construct(array $filters)
    {
        $this->filters = $filters;

        // 🔒 hanya pengangkut sesuai MODE
        $this->pengangkuts = Pengangkut::whereHas('rekapData.mitra', function ($q) {
            match ($this->filters['mode'] ?? null) {
                'bim_rengat' =>
                    $q->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                      ->where('nama_mitra', 'ILIKE', '%RENGAT%'),

                'bim_siak' =>
                    $q->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                      ->where('nama_mitra', 'ILIKE', '%SIAK%'),

                'mul' =>
                    $q->where('nama_mitra', 'ILIKE', '%MUTIARA UNGGUL LESTARI%'),
            };
        })
        ->orderBy('kode')
        ->get();
    }

    public function title(): string
    {
        return 'Rekap';
    }

    protected function emptyRow(int $cols): array
    {
        return array_fill(0, $cols, null);
    }

    public function collection()
    {
        $rows = collect();

        // ===============================
        // QUERY DATA (1x SAJA)
        // ===============================
        $data = RekapData::query()
            ->when($this->filters['mode'] ?? null, function ($q) {
                $q->whereHas('mitra', function ($m) {
                    match ($this->filters['mode']) {
                        'bim_rengat' =>
                            $m->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                              ->where('nama_mitra', 'ILIKE', '%RENGAT%'),

                        'bim_siak' =>
                            $m->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                              ->where('nama_mitra', 'ILIKE', '%SIAK%'),

                        'mul' =>
                            $m->where('nama_mitra', 'ILIKE', '%MUTIARA UNGGUL LESTARI%'),
                    };
                });
            })
            ->selectRaw("
                DATE_TRUNC('month', tanggal) as bulan,
                pengangkut_id,
                SUM(netto_kebun) as netto_kebun,
                SUM(netto) as netto,
                SUM(susut) as susut
            ")
            ->groupByRaw("DATE_TRUNC('month', tanggal)")
            ->groupBy('pengangkut_id')
            ->get();

        // ===============================
        // SETUP KOLOM
        // ===============================
        $kolomPengangkut = 2 + ($this->pengangkuts->count() * 4);
        $kolomAll        = 2 + 4;

        $tahunMulai = $this->filters['tahun_mulai'] ?? now()->year;
        $tahunAkhir = $this->filters['tahun_akhir'] ?? now()->year;

        for ($tahun = $tahunMulai; $tahun <= $tahunAkhir; $tahun++) {

            // ===============================
            // JUDUL TAHUN
            // ===============================
            $rows->push(["TAHUN {$tahun}"]);
            $rows->push($this->emptyRow($kolomPengangkut));

            // ===============================
            // HEADER PENGANGKUT (2 BARIS)
            // ===============================
            $header1 = ['No', 'Bulan'];
            $header2 = ['', ''];

            foreach ($this->pengangkuts as $p) {
                $header1[] = $p->kode;
                $header1[] = '';
                $header1[] = '';
                $header1[] = '';

                $header2[] = 'Netto Kebun';
                $header2[] = 'Netto';
                $header2[] = 'Susut';
                $header2[] = 'Susut%';
            }

            $rows->push($header1);
            $rows->push($header2);

            // ===============================
            // DATA BULANAN (PENGANGKUT)
            // ===============================
            $no = 1;

            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $row = [
                    $no++,
                    Carbon::create($tahun, $bulan)->locale('id')->translatedFormat('F'),
                ];

                foreach ($this->pengangkuts as $p) {
                    $item = $data->first(fn ($i) =>
                        $i->pengangkut_id === $p->id &&
                        Carbon::parse($i->bulan)->year === $tahun &&
                        Carbon::parse($i->bulan)->month === $bulan
                    );

                    $nk = $item->netto_kebun ?? 0;
                    $n  = $item->netto ?? 0;
                    $s  = $item->susut ?? 0;

                    $row[] = $nk;
                    $row[] = $n;
                    $row[] = $s;
                    $row[] = $nk > 0 ? $s / $nk : 0;
                }

                $rows->push($row);
            }

            // ===============================
            // JARAK + TABEL ALL
            // ===============================
            $rows->push($this->emptyRow($kolomPengangkut));
            $rows->push(['No', 'Bulan', 'ALL', '', '', '']);
            $rows->push(['', '', 'Netto Kebun', 'Netto', 'Susut', 'Susut%']);

            $no = 1;

            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $items = $data->filter(fn ($i) =>
                    Carbon::parse($i->bulan)->year === $tahun &&
                    Carbon::parse($i->bulan)->month === $bulan
                );

                $nk = $items->sum('netto_kebun');
                $n  = $items->sum('netto');
                $s  = $items->sum('susut');

                $rows->push([
                    $no++,
                    Carbon::create($tahun, $bulan)->translatedFormat('F'),
                    $nk,
                    $n,
                    $s,
                    $nk > 0 ? $s / $nk : 0,
                ]);
            }

            // ===============================
            // SPASI ANTAR TAHUN
            // ===============================
            $rows->push($this->emptyRow($kolomPengangkut));
            $rows->push($this->emptyRow($kolomPengangkut));
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Freeze kolom A & B (No + Bulan)
                // Baris bebas → ALL & pengangkut tetap aman
                $event->sheet->freezePane('C1');
            },
        ];
    }
}