<?php

namespace App\Http\Controllers;

use App\Models\LogPekerja;
use App\Models\GreenBeanStock;
use App\Models\RoastingBatch;
use App\Models\PesananKeluar;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Exports\LaporanKeuanganExport;
use Maatwebsite\Excel\Facades\Excel;

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
        $transactions = PesananKeluar::whereBetween('pesanan_keluar.created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
        $totalLunas = (clone $transactions)->where('pesanan_keluar.status_pembayaran', 'lunas')->sum('pesanan_keluar.total_pembayaran');

        $totalDP = 0;
        if (Schema::hasColumn('pesanan_keluar', 'metode_pembayaran') && Schema::hasColumn('pesanan_keluar', 'nominal_dp')) {
            $totalDP = (clone $transactions)->where('metode_pembayaran', 'dp')->sum('nominal_dp');
        }

        $totalSisaPiutang = 0;
        if (Schema::hasColumn('pesanan_keluar', 'sisa_pembayaran')) {
            $totalSisaPiutang = (clone $transactions)->sum('sisa_pembayaran');
        }

        // ==========================================
        // DYNAMIC INFLOW (Pendapatan)
        // ==========================================
        $inflowData = (clone $transactions)->where('pesanan_keluar.status_pembayaran', 'lunas')
            ->join('roasted_bean', 'pesanan_keluar.roasted_bean_id', '=', 'roasted_bean.id')
            ->selectRaw('roasted_bean.bentuk_akhir as kategori, SUM(pesanan_keluar.total_pembayaran) as total')
            ->groupBy('roasted_bean.bentuk_akhir')
            ->get();

        $inflowByCategory = [];
        $totalInflow = 0;
        foreach ($inflowData as $item) {
            $cat = $item->kategori ?: 'Lainnya';
            $inflowByCategory[$cat] = $item->total;
            $totalInflow += $item->total;
        }

        // ==========================================
        // DYNAMIC OUTFLOW (Pengeluaran)
        // ==========================================
        $outflowByCategory = [];
        $totalOutflow = 0;

        // 1. Belanja Green Bean
        $greenBeanPurchases = GreenBeanStock::whereBetween('tanggal_beli', [$start, $end])->get();
        $totalBelanjaGB = 0;
        foreach ($greenBeanPurchases as $gb) {
            $totalBelanjaGB += ($gb->total_harga > 0) ? (float) $gb->total_harga : (($gb->stok_gram / 1000) * $gb->harga_beli);
        }

        if ($totalBelanjaGB > 0) {
            $outflowByCategory['Belanja Bahan Baku (Green Bean)'] = $totalBelanjaGB;
            $totalOutflow += $totalBelanjaGB;
        }

        // 2. Upah Pekerja
        $totalUpah = LogPekerja::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])->sum('total_upah');
        if ($totalUpah > 0) {
            $outflowByCategory['Upah Tenaga Kerja'] = $totalUpah;
            $totalOutflow += $totalUpah;
        }

        // 3. Biaya Operasional (Listrik, Penyusutan, dll dari HPP)
        // For simplicity, we just aggregate HppCalculation within this period
        // Wait, HppCalculation is just calculation. Has it been realized?
        // Roasting activities already happened. We can take total_upah + biaya_listrik + biaya_penyusutan from HPP?
        // Actually, let's just get it from HppCalculation for batches within this period.
        $hpps = \App\Models\HppCalculation::whereHas('roastingBatch', function ($q) use ($start, $end) {
            $q->whereBetween('tanggal_roasting', [$start, $end]);
        })->get();

        $totalListrik = $hpps->sum('biaya_listrik');
        $totalPenyusutan = $hpps->sum('biaya_penyusutan');
        $totalKemasan = $hpps->sum('biaya_kemasan');

        if ($totalListrik > 0) {
            $outflowByCategory['Biaya Listrik Mesin'] = $totalListrik;
            $totalOutflow += $totalListrik;
        }
        if ($totalPenyusutan > 0) {
            $outflowByCategory['Penyusutan Mesin'] = $totalPenyusutan;
            $totalOutflow += $totalPenyusutan;
        }
        if ($totalKemasan > 0) {
            $outflowByCategory['Biaya Kemasan'] = $totalKemasan;
            $totalOutflow += $totalKemasan;
        }

        $labaBersih = $totalInflow - $totalOutflow;

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
            'totalSisaPiutang',
            'inflowByCategory',
            'outflowByCategory',
            'totalInflow',
            'totalOutflow',
            'labaBersih'
        ));
    }

    public function exportExcel(Request $request)
    {
        $start = $request->query('start_date', now()->startOfMonth()->toDateString());
        $end = $request->query('end_date', now()->endOfMonth()->toDateString());

        // 1. Data Green Bean Entries
        $greenBeanStockEntries = GreenBeanStock::with('greenBean')
            ->whereBetween('tanggal_beli', [$start, $end])
            ->orderBy('tanggal_beli', 'desc')
            ->get();

        // 2. Data Roasting Batch Entries
        $roastingBatchEntries = RoastingBatch::with(['user', 'roastedBean'])
            ->whereBetween('tanggal_roasting', [$start, $end])
            ->where('status_batch', 'completed')
            ->orderBy('tanggal_roasting', 'desc')
            ->get();

        // 3. Data Inflow
        $inflowData = PesananKeluar::whereBetween('pesanan_keluar.created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->where('status_pembayaran', 'lunas')
            ->join('roasted_bean', 'pesanan_keluar.roasted_bean_id', '=', 'roasted_bean.id')
            ->selectRaw('roasted_bean.bentuk_akhir as kategori, SUM(pesanan_keluar.total_pembayaran) as total')
            ->groupBy('roasted_bean.bentuk_akhir')
            ->get();

        // 4. Data Outflow
        $outflowByCategory = [];

        $greenBeanPurchases = GreenBeanStock::whereBetween('tanggal_beli', [$start, $end])->get();
        $totalBelanjaGB = 0;
        foreach ($greenBeanPurchases as $gb) {
            $totalBelanjaGB += ($gb->total_harga > 0) ? (float) $gb->total_harga : (($gb->stok_gram / 1000) * $gb->harga_beli);
        }
        if ($totalBelanjaGB > 0) {
            $outflowByCategory['Belanja Bahan Baku (Green Bean)'] = $totalBelanjaGB;
        }

        $totalUpah = LogPekerja::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])->sum('total_upah');
        if ($totalUpah > 0) {
            $outflowByCategory['Upah Tenaga Kerja'] = $totalUpah;
        }

        $hpps = \App\Models\HppCalculation::whereHas('roastingBatch', function ($q) use ($start, $end) {
            $q->whereBetween('tanggal_roasting', [$start, $end]);
        })->get();

        if ($hpps->sum('biaya_listrik') > 0) $outflowByCategory['Biaya Listrik Mesin'] = $hpps->sum('biaya_listrik');
        if ($hpps->sum('biaya_penyusutan') > 0) $outflowByCategory['Penyusutan Mesin'] = $hpps->sum('biaya_penyusutan');
        if ($hpps->sum('biaya_kemasan') > 0) $outflowByCategory['Biaya Kemasan'] = $hpps->sum('biaya_kemasan');

        $totalOutflow = array_sum($outflowByCategory);
        $totalInflow = $inflowData->sum('total');
        $labaBersih = $totalInflow - $totalOutflow;

        $filename = "Laporan_Lengkap_{$start}_{$end}.xlsx";

        return Excel::download(
            new LaporanKeuanganExport(
                $inflowData,
                $outflowByCategory,
                $totalInflow,
                $totalOutflow,
                $labaBersih,
                $greenBeanStockEntries,
                $roastingBatchEntries
            ),
            $filename
        );
    }
}
