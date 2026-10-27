<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class LabaRugiSheetExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $pendapatanByCategory;
    protected $bebanLabaRugiByCategory;
    protected $totalPendapatan;
    protected $totalBeban;
    protected $labaBersih;
    protected $start;
    protected $end;

    public function __construct($pendapatanByCategory, $bebanLabaRugiByCategory, $totalPendapatan, $totalBeban, $labaBersih, $start = null, $end = null)
    {
        $this->pendapatanByCategory = $pendapatanByCategory;
        $this->bebanLabaRugiByCategory = $bebanLabaRugiByCategory;
        $this->totalPendapatan = $totalPendapatan;
        $this->totalBeban = $totalBeban;
        $this->labaBersih = $labaBersih;
        $this->start = $start ?? now()->startOfMonth()->toDateString();
        $this->end = $end ?? now()->endOfMonth()->toDateString();
    }

    public function view(): View
    {
        return view('exports.laba_rugi', [
            'pendapatanByCategory'    => $this->pendapatanByCategory,
            'bebanLabaRugiByCategory' => $this->bebanLabaRugiByCategory,
            'totalPendapatan'         => $this->totalPendapatan,
            'totalBeban'              => $this->totalBeban,
            'labaBersih'              => $this->labaBersih,
            'start'                   => $this->start,
            'end'                     => $this->end,
        ]);
    }

    public function title(): string
    {
        return 'Laba Rugi';
    }
}
