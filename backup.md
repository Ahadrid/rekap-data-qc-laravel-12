<?php

namespace App\Exports;

use App\Query\QueryExport;
use App\Models\Produk;

use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithMapping,
    WithHeadings,
    WithColumnFormatting,
    WithStyles,
    WithEvents
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapDataExport implements
    FromQuery,
    WithMapping,
    WithHeadings,
    WithColumnFormatting,
    WithStyles,
    WithEvents
{
    protected array $filters;
    protected bool $isPK = false;
    protected array $counter = [];

    protected int $currentDataRow = 2;

    protected ?string $currentMonth = null;
    protected int $startMonthRow = 2;
    
    protected array $monthlySum = [
        'netto_kebun' => 0,
        'netto' => 0,
        'susut' => 0,
    ];

    protected array $monthRows = [];

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;

        if (! empty($filters['produk_id'])) {
            $this->isPK = Produk::where('id', $filters['produk_id'])
                ->where(fn ($q) =>
                    $q->where('nama_produk', 'ILIKE', '%PK%')
                      ->orWhere('kode_produk', 'PK')
                )
                ->exists();
        }
    }

    /* =====================================================
     * QUERY
     * ===================================================== */
    public function query()
    {
        return QueryExport::build($this->filters);
    }

    /* =====================================================
     * MAP
     * ===================================================== */
    public function map($row): array
    {
        $produk = $row->produk_id;
        $this->counter[$produk] = ($this->counter[$produk] ?? 0) + 1;

        $month = $row->tanggal->format('Y-m');

        // INIT pertama
        if ($this->currentMonth === null) {
            $this->currentMonth = $month;
        }

        /**
         * JIKA BULAN BERGANTI
         * simpan hasil bulan lama
         */
        if ($this->currentMonth !== $month) {

            $this->monthRows[] = [
                'month' => $this->currentMonth,
                'start' => $this->startMonthRow,
                'end'   => $this->currentDataRow - 1,
                'sum'   => $this->monthlySum,
            ];

            // reset accumulator
            $this->monthlySum = [
                'netto_kebun' => 0,
                'netto' => 0,
                'susut' => 0,
            ];

            $this->currentMonth = $month;
            $this->startMonthRow = $this->currentDataRow;
        }

        // ===============================
        // BUILD DATA ROW (TIDAK DIUBAH)
        // ===============================
        $data = [
            $this->counter[$produk],
            ExcelDate::dateTimeToExcel($row->tanggal),
            $row->mitra?->nama_mitra,
            $row->pengangkut?->nama_pengangkut,
            $row->kendaraan?->no_pol,
            $row->kendaraan?->nama_supir,

            $row->bruto_kirim,
            $row->tara_kirim,
            $row->netto_kebun,

            $row->bruto,
            $row->tara,
            $row->netto,

            $row->susut ?? 0,
            $row->susut_persen,
        ];

        if (! $this->isPK) {
            $data[] = $row->ffa;
            $data[] = $row->dobi;
        }

        $data[] = $row->keterangan;

        // ===============================
        // AKUMULASI SETELAH DATA MASUK
        // ===============================
        $this->monthlySum['netto_kebun'] += $row->netto_kebun;
        $this->monthlySum['netto']       += $row->netto;
        $this->monthlySum['susut']       += $row->susut ?? 0;

        $this->currentDataRow++;

        return $data;
    }


    /* =====================================================
     * HEADINGS
     * ===================================================== */
    public function headings(): array
    {
        $headings = [
            'No', 'Tanggal', 'Nama Rekanan', 'Nama Pengangkutan',
            'No. Kendaraan', 'Nama Supir',

            'Bruto Kirim', 'Tara Kirim', 'Netto Kebun',
            'Bruto', 'Tara', 'Netto',

            'Susut', 'Susut (%)',
        ];

        if (! $this->isPK) {
            $headings[] = 'FFA';
            $headings[] = 'Dobi';
        }

        $headings[] = 'Catatan';

        return $headings;
    }

    /* =====================================================
     * FORMAT
     * ===================================================== */
    public function columnFormats(): array
    {
        $formats = [
            'B' => 'DD-MMM-YYYY',
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => '#,##0',
            'J' => '#,##0',
            'K' => '#,##0',
            'L' => '#,##0',
            'M' => '0;-0;0',
            'N' => '0.00;-0.00;0.00',
        ];

        if (! $this->isPK) {
            $formats['O'] = '#,##0.00';
            $formats['P'] = '#,##0.00';
        }

        return $formats;
    }

    /* =====================================================
     * STYLE
     * ===================================================== */
    public function styles(Worksheet $sheet): array
    {
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->freezePane('A1');

        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical'   => 'center',
                    'wrapText'   => true,
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => 'A9D08E'],
                ],
            ],
        ];
    }

    /* =====================================================
     * EVENTS
     * ===================================================== */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // 1. Kolom berat (fixed)
                foreach (['G','H','I','J','K','L'] as $col) {
                    $sheet->getColumnDimension($col)->setWidth(12);
                }

                foreach (['M','N'] as $col) {
                    $sheet->getColumnDimension($col)->setWidth(10);
                }

                $highestRow    = $sheet->getHighestRow();
                
                // 2. Auto size kolom lainnya
                $highestCol = $sheet->getHighestColumn();
                $fixed = ['G','H','I','J','K','L','M','N'];

                foreach (range('A', $highestCol) as $col) {
                    if (! in_array($col, $fixed)) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }
                }

                //kolom M = susut
                for ($row= 2; $row <= $highestRow; $row++) { 
                    $cell = $sheet->getCell("M{$row}");
                    if ($cell->getValue() === null || $cell->getValue() === '') {
                        $cell->setValueExplicit(0, DataType::TYPE_NUMERIC);
                    }
                }

                //Style tanggal agar jadi align left dengan format 'DD-MMM-YYYY'
                $sheet->getStyle("B2:B{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // 3. Border
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'D9D9D9'],
                            ],
                        ],
                    ]);
                
                $rowOffset = 0;

                // Simpan Bulan Terakhir
                if ($this->currentMonth !== null) {
                    $this->monthRows[] =[
                        'month' => $this->currentMonth,
                        'start' => $this->startMonthRow,
                        'end' => $this->currentDataRow - 1,
                        'sum' => $this->monthlySum,
                    ];
                }

                foreach($this->monthRows as $monthData){
                    $insertAt = $monthData['end'] + 1 + $rowOffset;

                    $sheet->insertNewRowBefore($insertAt, 1);

                    $start = $monthData['start'] + $rowOffset;
                    $end   = $monthData['end'] + $rowOffset;

                    $sheet->setCellValue(
                        "I{$insertAt}",
                        "=SUM(I{$start}:I{$end})"
                    );

                    $sheet->setCellValue(
                        "L{$insertAt}",
                        "=SUM(L{$start}:L{$end})"
                    );

                    $sheet->setCellValue(
                        "M{$insertAt}",
                        "=SUM(M{$start}:M{$end})"
                    );

                    $sheet->setCellValue(
                        "N{$insertAt}",
                        "=IF(I{$insertAt} = 0,0,M{$insertAt}/I{$insertAt}*100)"
                    );

                    $sheet->getStyle("A{$insertAt}:{$highestCol}{$insertAt}")
                        ->applyFromArray([
                            'font' => ['bold' =>true],
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['argb' => 'FFFF00'], // Kuning terang
                            ],
                        ]);

                    $rowOffset++;
                }
            }
        ];
    }
}
