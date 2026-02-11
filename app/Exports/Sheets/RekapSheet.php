<?php
namespace App\Exports\Sheets;
use App\Helpers\DateFormatHelpers;
use App\Models\RekapData;
use App\Models\Pengangkut;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class RekapSheet implements FromCollection, WithTitle, WithEvents
{
    protected array $filters;
    protected Collection $pengangkuts;
    protected array $mergeCells = []; // Menyimpan koordinat merge cells
    protected array $borderRanges = []; // Menyimpan koordinat untuk border
    protected array $percentCells = []; // Menyimpan koordinat cell untuk susut %
    protected array $headerCells = []; // Menyimpan koordinat header untuk bold
    protected array $redBorderColumns = []; // Menyimpan koordinat border merah

    public function __construct(array $filters)
    {
        $this->filters = $filters;
        // 🔒 hanya pengangkut sesuai MODE
        $this->pengangkuts = Pengangkut::whereHas('rekapData', function ($q) {
            // filter produk (PK / CPO)
            if (!empty($this->filters['produk_id'])) {
                $q->where('produk_id', $this->filters['produk_id']);
            }
            // filter mitra
            $q->whereHas('mitra', function ($m) {
                match ($this->filters['mode'] ?? null) {
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
            // 🔒 FILTER PRODUK
            ->when(!empty($this->filters['produk_id']), function ($q) {
                $q->where('produk_id', $this->filters['produk_id']);
            })
            // 🔒 FILTER MITRA
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

        $currentRow = 1;

        for ($tahun = $tahunMulai; $tahun <= $tahunAkhir; $tahun++) {
            // ===============================
            // JUDUL TAHUN
            // ===============================
            $rows->push(["TAHUN {$tahun}"]);
            $currentRow++;
            
            $rows->push($this->emptyRow($kolomPengangkut));
            $currentRow++;

            // ===============================
            // HEADER PENGANGKUT (2 BARIS)
            // ===============================
            $pengangkutStartRow = $currentRow;
            
            $header1 = ['No', 'Bulan'];
            $header2 = ['', ''];
            
            // Merge No dan Bulan dengan baris dibawahnya
            $this->mergeCells[] = "A{$currentRow}:A" . ($currentRow + 1);
            $this->mergeCells[] = "B{$currentRow}:B" . ($currentRow + 1);
            
            // Simpan koordinat header No dan Bulan untuk bold
            $this->headerCells[] = "A{$currentRow}:A" . ($currentRow + 1);
            $this->headerCells[] = "B{$currentRow}:B" . ($currentRow + 1);
            
            $colIndex = 3; // Mulai dari kolom C (1=A, 2=B, 3=C)
            foreach ($this->pengangkuts as $p) {
                $header1[] = $p->kode;
                $header1[] = '';
                $header1[] = '';
                $header1[] = '';
                
                $header2[] = 'Netto Kebun';
                $header2[] = 'Netto';
                $header2[] = 'Susut';
                $header2[] = 'Susut%';
                
                // Simpan koordinat untuk merge kode pengangkut
                $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 3);
                $this->mergeCells[] = "{$startCol}{$currentRow}:{$endCol}{$currentRow}";
                
                // Simpan koordinat header pengangkut untuk bold
                $this->headerCells[] = "{$startCol}{$currentRow}:{$endCol}{$currentRow}";
                
                // Simpan koordinat header detail (Netto Kebun, dll) untuk bold
                for ($i = 0; $i < 4; $i++) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + $i);
                    $this->headerCells[] = "{$col}" . ($currentRow + 1);
                }
                
                // Simpan koordinat untuk border merah (kiri tabel pengangkut)
                $this->redBorderColumns[] = [
                    'range' => "{$startCol}" . ($pengangkutStartRow) . ":{$startCol}" . ($pengangkutStartRow + 13), // Header + 12 bulan
                    'side' => 'left'
                ];
                
                // Border merah kanan
                $this->redBorderColumns[] = [
                    'range' => "{$endCol}" . ($pengangkutStartRow) . ":{$endCol}" . ($pengangkutStartRow + 13),
                    'side' => 'right'
                ];
                
                $colIndex += 4;
            }
            
            $rows->push($header1);
            $currentRow++;
            $rows->push($header2);
            $currentRow++;

            // ===============================
            // DATA BULANAN (PENGANGKUT)
            // ===============================
            $no = 1;
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $row = [
                    $no++,
                    Carbon::create($tahun, $bulan)->locale('id')->translatedFormat('F'),
                ];

                $colIdx = 3; // Start dari kolom C
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
                    $row[] = $nk > 0 ? ($s / $nk) * 100 : 0; // Konversi ke persen
                    
                    // Simpan koordinat kolom Susut% untuk format desimal
                    $susutPercentCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 3);
                    $this->percentCells[] = "{$susutPercentCol}{$currentRow}";
                    
                    $colIdx += 4;
                }
                
                $rows->push($row);
                $currentRow++;
            }
            
            // Simpan range untuk border tabel pengangkut
            $pengangkutEndRow = $currentRow - 1;
            $lastPengangkutCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($kolomPengangkut);
            $this->borderRanges[] = "A{$pengangkutStartRow}:{$lastPengangkutCol}{$pengangkutEndRow}";

            // ===============================
            // JARAK + TABEL ALL
            // ===============================
            $rows->push($this->emptyRow($kolomPengangkut));
            $currentRow++;
            
            // Header ALL dengan merge
            $allStartRow = $currentRow;
            $rows->push(['No', 'Bulan', 'ALL', '', '', '']);
            
            // Merge No dan Bulan untuk tabel ALL
            $this->mergeCells[] = "A{$currentRow}:A" . ($currentRow + 1);
            $this->mergeCells[] = "B{$currentRow}:B" . ($currentRow + 1);
            $this->mergeCells[] = "C{$currentRow}:F{$currentRow}"; // Merge ALL
            
            // Simpan koordinat header ALL untuk bold
            $this->headerCells[] = "A{$currentRow}:A" . ($currentRow + 1);
            $this->headerCells[] = "B{$currentRow}:B" . ($currentRow + 1);
            $this->headerCells[] = "C{$currentRow}:F{$currentRow}";
            
            $currentRow++;
            
            $rows->push(['', '', 'Netto Kebun', 'Netto', 'Susut', 'Susut%']);
            
            // Simpan koordinat header detail ALL untuk bold
            $this->headerCells[] = "C{$currentRow}";
            $this->headerCells[] = "D{$currentRow}";
            $this->headerCells[] = "E{$currentRow}";
            $this->headerCells[] = "F{$currentRow}";
            
            // Border merah untuk tabel ALL (kiri dan kanan)
            $this->redBorderColumns[] = [
                'range' => "C{$allStartRow}:C" . ($currentRow + 12), // Header + 12 bulan
                'side' => 'left'
            ];
            $this->redBorderColumns[] = [
                'range' => "F{$allStartRow}:F" . ($currentRow + 12),
                'side' => 'right'
            ];
            
            $currentRow++;
            
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
                    Carbon::create($tahun, $bulan)->locale('id')->translatedFormat('F'),
                    $nk,
                    $n,
                    $s,
                    $nk > 0 ? ($s / $nk) * 100 : 0, // Konversi ke persen
                ]);
                
                // Simpan koordinat kolom Susut% di tabel ALL (kolom F)
                $this->percentCells[] = "F{$currentRow}";
                
                $currentRow++;
            }
            
            // Simpan range untuk border tabel ALL
            $allEndRow = $currentRow - 1;
            $this->borderRanges[] = "A{$allStartRow}:F{$allEndRow}";

            // ===============================
            // SPASI ANTAR TAHUN
            // ===============================
            $rows->push($this->emptyRow($kolomPengangkut));
            $currentRow++;
            $rows->push($this->emptyRow($kolomPengangkut));
            $currentRow++;
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Freeze kolom A & B (No + Bulan)
                $event->sheet->freezePane('C1');
                
                // Apply merge cells
                foreach ($this->mergeCells as $range) {
                    $sheet->mergeCells($range);
                }
                
                // Format angka biasa (tanpa desimal) untuk semua kolom angka
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Loop semua cell untuk format angka biasa
                for ($row = 1; $row <= $highestRow; $row++) {
                    for ($col = 'C'; $col <= $highestColumn; $col++) {
                        $cellValue = $sheet->getCell("{$col}{$row}")->getValue();
                        
                        // Jika cell berisi angka, format dengan pemisah ribuan tanpa desimal
                        if (is_numeric($cellValue) && $cellValue !== null && $cellValue !== '') {
                            $sheet->getStyle("{$col}{$row}")
                                ->getNumberFormat()
                                ->setFormatCode('#,##0');
                        }
                    }
                }
                
                // Format khusus untuk kolom Susut% dengan 2 desimal
                foreach ($this->percentCells as $cell) {
                    $sheet->getStyle($cell)
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }
                
                // Apply border hanya untuk tabel pengangkut dan ALL
                foreach ($this->borderRanges as $range) {
                    $sheet->getStyle($range)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }
                
                // Apply bold untuk semua header
                foreach ($this->headerCells as $range) {
                    $sheet->getStyle($range)
                        ->getFont()
                        ->setBold(true);
                }
                
                // Apply center alignment untuk merge cells (judul pengangkut dan ALL)
                foreach ($this->mergeCells as $range) {
                    $sheet->getStyle($range)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }
                
                // Apply center alignment untuk semua cell kecuali kolom Bulan (B)
                for ($row = 1; $row <= $highestRow; $row++) {
                    for ($col = 'A'; $col <= $highestColumn; $col++) {
                        if ($col !== 'B') { // Skip kolom Bulan
                            $sheet->getStyle("{$col}{$row}")
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }
                    }
                }
                
                // Apply border merah tebal untuk pembatas tabel
                foreach ($this->redBorderColumns as $borderInfo) {
                    $borderStyle = [
                        'borders' => [
                            $borderInfo['side'] => [
                                'borderStyle' => Border::BORDER_THICK,
                                'color' => ['rgb' => 'FF0000'], // Merah
                            ],
                        ],
                    ];
                    $sheet->getStyle($borderInfo['range'])->applyFromArray($borderStyle);
                }
                
                // Set lebar kolom No secara manual (misalnya 5)
                $sheet->getColumnDimension('A')->setWidth(5);
                
                // Auto size untuk kolom selain No (B sampai kolom terakhir)
                foreach (range('B', $highestColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}