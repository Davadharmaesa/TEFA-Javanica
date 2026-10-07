<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class KeuanganSheetExport implements FromCollection, WithHeadings, WithTitle
{
    protected $inflowData, $outflowByCategory, $totalInflow, $totalOutflow, $labaBersih;

    public function __construct($inflowData, $outflowByCategory, $totalInflow, $totalOutflow, $labaBersih)
    {
        $this->inflowData = $inflowData;
        $this->outflowByCategory = $outflowByCategory;
        $this->totalInflow = $totalInflow;
        $this->totalOutflow = $totalOutflow;
        $this->labaBersih = $labaBersih;
    }

    public function collection()
    {
        $rows = [];
        
        $rows[] = ['--- PENDAPATAN (INFLOW) ---', ''];
        foreach ($this->inflowData as $item) {
            $rows[] = [$item->kategori ?: 'Lainnya', $item->total];
        }
        $rows[] = ['TOTAL PENDAPATAN', $this->totalInflow];
        $rows[] = ['', ''];

        $rows[] = ['--- PENGELUARAN (OUTFLOW) ---', ''];
        foreach ($this->outflowByCategory as $cat => $amount) {
            $rows[] = [$cat, $amount];
        }
        $rows[] = ['TOTAL PENGELUARAN', $this->totalOutflow];
        $rows[] = ['', ''];

        $rows[] = ['LABA/RUGI BERSIH', $this->labaBersih];

        return collect($rows);
    }

    public function headings(): array
    {
        return ['Keterangan', 'Jumlah (Rp)'];
    }

    public function title(): string
    {
        return 'Laporan Keuangan';
    }
}