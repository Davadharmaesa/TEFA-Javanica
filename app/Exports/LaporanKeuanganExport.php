<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanKeuanganExport implements WithMultipleSheets
{
    // Laba Rugi
    protected $pendapatanByCategory, $bebanLabaRugiByCategory, $totalPendapatan, $totalBeban, $labaBersih;
    // Arus Kas
    protected $cashInflowByCategory, $cashOutflowByCategory, $totalCashInflow, $totalCashOutflow, $netCashFlow, $totalSisaPiutang;
    // Operasional
    protected $greenBeanStockEntries, $roastingBatchEntries;
    // Periode
    protected $start, $end;

    public function __construct(
        $pendapatanByCategory,
        $bebanLabaRugiByCategory,
        $totalPendapatan,
        $totalBeban,
        $labaBersih,
        $cashInflowByCategory,
        $cashOutflowByCategory,
        $totalCashInflow,
        $totalCashOutflow,
        $netCashFlow,
        $totalSisaPiutang,
        $greenBeanStockEntries,
        $roastingBatchEntries,
        $start = null,
        $end = null
    ) {
        $this->pendapatanByCategory = $pendapatanByCategory;
        $this->bebanLabaRugiByCategory = $bebanLabaRugiByCategory;
        $this->totalPendapatan = $totalPendapatan;
        $this->totalBeban = $totalBeban;
        $this->labaBersih = $labaBersih;

        $this->cashInflowByCategory = $cashInflowByCategory;
        $this->cashOutflowByCategory = $cashOutflowByCategory;
        $this->totalCashInflow = $totalCashInflow;
        $this->totalCashOutflow = $totalCashOutflow;
        $this->netCashFlow = $netCashFlow;
        $this->totalSisaPiutang = $totalSisaPiutang;

        $this->greenBeanStockEntries = $greenBeanStockEntries;
        $this->roastingBatchEntries = $roastingBatchEntries;

        $this->start = $start;
        $this->end = $end;
    }

    public function sheets(): array
    {
        return [
            new LabaRugiSheetExport(
                $this->pendapatanByCategory,
                $this->bebanLabaRugiByCategory,
                $this->totalPendapatan,
                $this->totalBeban,
                $this->labaBersih,
                $this->start,
                $this->end
            ),
            new ArusKasSheetExport(
                $this->cashInflowByCategory,
                $this->cashOutflowByCategory,
                $this->totalCashInflow,
                $this->totalCashOutflow,
                $this->netCashFlow,
                $this->totalSisaPiutang,
                $this->start,
                $this->end
            ),
            new GreenBeanSheetExport($this->greenBeanStockEntries),
            new RoastingSheetExport($this->roastingBatchEntries),
        ];
    }
}