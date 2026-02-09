<?php
// app/Exports/Traits/MonthlyCalculation.php
namespace App\Exports\Traits;

trait MonthlyCalculation
{
    protected int $currentDataRow = 2;
    protected ?string $currentMonth = null;
    protected int $startMonthRow = 2;
    
    protected array $monthlySum = [
        'netto_kebun' => 0,
        'netto' => 0,
        'susut' => 0,
    ];
    
    protected array $monthRows = [];

    protected function initializeMonthlyTracking(): void
    {
        $this->currentDataRow = 2;
        $this->currentMonth = null;
        $this->startMonthRow = 2;
        $this->monthlySum = [
            'netto_kebun' => 0,
            'netto' => 0,
            'susut' => 0,
        ];
        $this->monthRows = [];
    }

    protected function processMonthChange($row): void
    {
        $month = $row->tanggal->format('Y-m');

        if ($this->currentMonth === null) {
            $this->currentMonth = $month;
            return;
        }

        if ($this->currentMonth !== $month) {
            $this->saveMonthData();
            $this->resetMonthlyAccumulator($month);
        }
    }

    protected function saveMonthData(): void
    {
        $this->monthRows[] = [
            'month' => $this->currentMonth,
            'start' => $this->startMonthRow,
            'end'   => $this->currentDataRow - 1,
            'sum'   => $this->monthlySum,
        ];
    }

    protected function resetMonthlyAccumulator(string $newMonth): void
    {
        $this->monthlySum = [
            'netto_kebun' => 0,
            'netto' => 0,
            'susut' => 0,
        ];
        $this->currentMonth = $newMonth;
        $this->startMonthRow = $this->currentDataRow;
    }

    protected function accumulateMonthlyData($row): void
    {
        $this->monthlySum['netto_kebun'] += $row->netto_kebun;
        $this->monthlySum['netto'] += $row->netto;
        $this->monthlySum['susut'] += $row->susut ?? 0;
        $this->currentDataRow++;
    }

    public function finalizeMonthlyData(): void
    {
        if ($this->currentMonth !== null) {
            $this->saveMonthData();
        }
    }

    public function getMonthRows(): array
    {
        return $this->monthRows;
    }
}