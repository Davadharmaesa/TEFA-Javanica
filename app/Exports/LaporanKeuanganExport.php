<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanKeuanganExport implements WithMultipleSheets
{
    protected $inflowData, $outflowByCategory, $totalInflow, $totalOutflow, $labaBersih;
    protected $greenBeanStockEntries, $roastingBatchEntries;

    public function __construct($inflowData, $outflowByCategory, $totalInflow, $totalOutflow, $labaBersih, $greenBeanStockEntries, $roastingBatchEntries)
    {
        $this->inflowData = $inflowData;
        $this->outflowByCategory = $outflowByCategory;
        $this->totalInflow = $totalInflow;
        $this->totalOutflow = $totalOutflow;
        $this->labaBersih = $labaBersih;
        $this->greenBeanStockEntries = $greenBeanStockEntries;
        $this->roastingBatchEntries = $roastingBatchEntries;
    }

    public function sheets(): array
    {
        return [
            new KeuanganSheetExport($this->inflowData, $this->outflowByCategory, $this->totalInflow, $this->totalOutflow, $this->labaBersih),
            new GreenBeanSheetExport($this->greenBeanStockEntries),
            new RoastingSheetExport($this->roastingBatchEntries),
        ];
    }
}