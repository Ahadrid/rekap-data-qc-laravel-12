<?php

namespace App\Exports;

use App\Models\Produk;
use App\Models\RekapData;
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
        return RekapData::query()
            ->with(['mitra', 'produk', 'kendaraan', 'pengangkut'])

            // filter produk
            ->when($this->filters['produk_id'] ?? null,
                fn ($q, $v) => $q->where('produk_id', $v)
            )

            // filter MODE EXPORT
            ->when($this->filters['mode'] ?? null, function ($q, $mode) {
                match ($mode) {
                    'suplier_luar' =>
                        $q->whereHas('mitra', fn ($m) =>
                            $m->where('tipe_mitra', 'suplier_luar')
                        ),

                    'bim_rengat' =>
                        $q->whereHas('mitra', fn ($m) =>
                            $m->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                              ->where('nama_mitra', 'ILIKE', '%RENGAT%')
                        ),

                    'bim_siak' =>
                        $q->whereHas('mitra', fn ($m) =>
                            $m->where('nama_mitra', 'ILIKE', '%BERLIAN INTI MEKAR%')
                              ->where('nama_mitra', 'ILIKE', '%SIAK%')
                        ),

                    'mul' =>
                        $q->whereHas('mitra', fn ($m) =>
                            $m->where('nama_mitra', 'ILIKE', '%MUTIARA UNGGUL LESTARI%')
                        ),

                    default => null,
                };
            })

            ->orderBy('tanggal');
    }

    /* =====================================================
     * MAP
     * ===================================================== */
    public function map($row): array
    {
        $produk = $row->produk_id;
        $this->counter[$produk] = ($this->counter[$produk] ?? 0) + 1;

        $data = [
            $this->counter[$produk],
            $row->tanggal,
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
            'B' => NumberFormat::FORMAT_DATE_YYYYMMDD,
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
        $sheet->freezePane('A2');

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
                $highestColumn = $sheet->getHighestColumn();
                
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

                $range = "A1:{$highestColumn}{$highestRow}";

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
            }
        ];
    }
}
