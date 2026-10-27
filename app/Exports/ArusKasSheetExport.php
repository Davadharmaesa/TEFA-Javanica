<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ArusKasSheetExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $cashInflowByCategory;
    protected $cashOutflowByCategory;
    protected $totalCashInflow;
    protected $totalCashOutflow;
    protected $netCashFlow;
    protected $totalSisaPiutang;
    protected $start;
    protected $end;

    public function __construct(
        $cashInflowByCategory,
        $cashOutflowByCategory,
        $totalCashInflow,
        $totalCashOutflow,
        $netCashFlow,
        $totalSisaPiutang = 0,
        $start = null,
        $end = null
    ) {
        $this->cashInflowByCategory  = $cashInflowByCategory;
        $this->cashOutflowByCategory = $cashOutflowByCategory;
        $this->totalCashInflow       = $totalCashInflow;
        $this->totalCashOutflow      = $totalCashOutflow;
        $this->netCashFlow           = $netCashFlow;
        $this->totalSisaPiutang      = $totalSisaPiutang;
        $this->start                 = $start ?? now()->startOfMonth()->toDateString();
        $this->end                   = $end ?? now()->endOfMonth()->toDateString();
    }

    public function view(): View
    {
        return view('exports.arus_kas', [
            'cashInflowByCategory'  => $this->cashInflowByCategory,
            'cashOutflowByCategory' => $this->cashOutflowByCategory,
            'totalCashInflow'       => $this->totalCashInflow,
            'totalCashOutflow'      => $this->totalCashOutflow,
            'netCashFlow'           => $this->netCashFlow,
            'totalSisaPiutang'      => $this->totalSisaPiutang,
            'start'                 => $this->start,
            'end'                   => $this->end,
        ]);
    }

    public function title(): string
    {
        return 'Arus Kas';
    }
}
