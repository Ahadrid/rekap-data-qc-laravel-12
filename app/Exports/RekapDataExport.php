<?php
// app/Exports/RekapDataExport.php
namespace App\Exports;

use App\Query\QueryExport;
use App\Models\Produk;
use App\Exports\Traits\MonthlyCalculation;
use App\Exports\Styles\RekapDataStyles;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithMapping,
    WithHeadings,
    WithColumnFormatting,
    WithStyles,
    WithEvents
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapDataExport implements
    FromQuery,
    WithMapping,
    WithHeadings,
    WithColumnFormatting,
    WithStyles,
    WithEvents
{
    use MonthlyCalculation;

    protected array $filters;
    protected bool $isPK = false;
    protected array $counter = [];

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        if (!empty($filters['produk_id'])) {
            $this->isPK = Produk::where('id', $filters['produk_id'])
                ->where(fn ($q) =>
                    $q->where('nama_produk', 'ILIKE', '%PK%')
                      ->orWhere('kode_produk', 'PK')
                )
                ->exists();
        }
        $this->initializeMonthlyTracking();
    }

    public function query()
    {
        return QueryExport::build($this->filters);
    }

    public function map($row): array
    {
        $produk = $row->produk_id;
        $this->counter[$produk] = ($this->counter[$produk] ?? 0) + 1;

        $this->processMonthChange($row);
        $data = $this->buildDataRow($row);
        $this->accumulateMonthlyData($row);

        return $data;
    }

    protected function buildDataRow($row): array
    {
        $data = [
            $this->counter[$row->produk_id],
            \PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel($row->tanggal),
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

        if (!$this->isPK) {
            $data[] = $row->ffa;
            $data[] = $row->dobi;
        }

        $data[] = $row->keterangan;
        return $data;
    }

    public function headings(): array
    {
        $headings = [
            'No', 'Tanggal', 'Nama Rekanan', 'Nama Pengangkutan',
            'No. Kendaraan', 'Nama Supir',
            'Bruto Kirim', 'Tara Kirim', 'Netto Kebun',
            'Bruto', 'Tara', 'Netto',
            'Susut', 'Susut (%)',
        ];

        if (!$this->isPK) {
            $headings[] = 'FFA';
            $headings[] = 'Dobi';
        }

        $headings[] = 'Catatan';
        return $headings;
    }

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

        if (!$this->isPK) {
            $formats['O'] = '#,##0.00';
            $formats['P'] = '#,##0.00';
        }

        return $formats;
    }

    public function styles(Worksheet $sheet): array
    {
        return RekapDataStyles::headerStyle($sheet);
    }

    public function registerEvents(): array
    {
        return RekapDataStyles::registerEvents($this);
    }

    public function isPK(): bool
    {
        return $this->isPK;
    }
}