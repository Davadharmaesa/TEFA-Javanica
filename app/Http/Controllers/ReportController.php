<?php

namespace App\Http\Controllers;

use App\Models\LogPekerja;
use App\Models\GreenBeanStock;
use App\Models\RoastingBatch;
use App\Models\PesananKeluar;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $totalUpahPekerja = LogPekerja::sum('total_upah');

        $start = $request->query('start_date');
        $end = $request->query('end_date');

        // Default to current month if not provided
        if (!$start || !$end) {
            $start = now()->startOfMonth()->toDateString();
            $end = now()->endOfMonth()->toDateString();
        }

        // Green Bean incoming (within period)
        $greenBeanIncoming = GreenBeanStock::whereBetween('tanggal_beli', [$start, $end])->sum('stok_gram');
        // Current remaining stock (total)
        $greenBeanRemaining = GreenBeanStock::sum('stok_gram');

        // Green Bean stock entries for the selected period
        $greenBeanStockEntries = GreenBeanStock::with('greenBean')
            ->whereBetween('tanggal_beli', [$start, $end])
            ->orderBy('tanggal_beli', 'desc')
            ->get();

        // Roasting activity count in period
        $roastingCount = RoastingBatch::whereBetween('tanggal_roasting', [$start, $end])->where('status_batch', 'completed')->count();

        // Roasting batch entries for the selected period
        $roastingBatchEntries = RoastingBatch::with(['user', 'roastedBean'])
            ->whereBetween('tanggal_roasting', [$start, $end])
            ->where('status_batch', 'completed')
            ->orderBy('tanggal_roasting', 'desc')
            ->get();

        // Financials from PesananKeluar
        $transactions = PesananKeluar::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
        $totalLunas = (clone $transactions)->where('status_pembayaran', 'lunas')->sum('total_pembayaran');

        $totalDP = 0;
        if (Schema::hasColumn('pesanan_keluar', 'metode_pembayaran') && Schema::hasColumn('pesanan_keluar', 'nominal_dp')) {
            $totalDP = (clone $transactions)->where('metode_pembayaran', 'dp')->sum('nominal_dp');
        }

        $totalSisaPiutang = 0;
        if (Schema::hasColumn('pesanan_keluar', 'sisa_pembayaran')) {
            $totalSisaPiutang = (clone $transactions)->sum('sisa_pembayaran');
        }

        return view('reports.index', compact(
            'totalUpahPekerja',
            'start',
            'end',
            'greenBeanIncoming',
            'greenBeanRemaining',
            'roastingCount',
            'greenBeanStockEntries',
            'roastingBatchEntries',
            'totalLunas',
            'totalDP',
            'totalSisaPiutang'
        ));
    }
}
