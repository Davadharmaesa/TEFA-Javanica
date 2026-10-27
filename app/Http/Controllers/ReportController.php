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
        // 1. DATA LAPORAN LABA RUGI (PROFIT & LOSS)
        // ==========================================
        $inflowData = (clone $transactions)->where('pesanan_keluar.status_pembayaran', 'lunas')
            ->join('roasted_bean', 'pesanan_keluar.roasted_bean_id', '=', 'roasted_bean.id')
            ->selectRaw('roasted_bean.bentuk_akhir as kategori, SUM(pesanan_keluar.total_pembayaran) as total')
            ->groupBy('roasted_bean.bentuk_akhir')
            ->get();

        $pendapatanByCategory = [];
        $totalPendapatan = 0;
        foreach ($inflowData as $item) {
            $cat = $item->kategori ?: 'Penjualan Kopi';
            $pendapatanByCategory[$cat] = (float) $item->total;
            $totalPendapatan += (float) $item->total;
        }

        // Beban Pokok & Beban Operasional (Laba Rugi)
        $bebanLabaRugiByCategory = [];
        $hpps = \App\Models\HppCalculation::whereHas('roastingBatch', function ($q) use ($start, $end) {
            $q->whereBetween('tanggal_roasting', [$start, $end]);
        })->get();

        $roastingBahanBaku = RoastingBatch::whereBetween('tanggal_roasting', [$start, $end])
            ->where('status_batch', 'completed')
            ->sum('biaya_bahan_baku');
        $biayaBahanBakuTerpakai = $hpps->sum('biaya_bahan_baku') > 0 ? (float) $hpps->sum('biaya_bahan_baku') : (float) $roastingBahanBaku;
        if ($biayaBahanBakuTerpakai > 0) {
            $bebanLabaRugiByCategory['Bahan Baku Terpakai (HPP Sangrai)'] = $biayaBahanBakuTerpakai;
        }

        $totalUpah = (float) LogPekerja::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])->sum('total_upah');
        if ($totalUpah > 0) {
            $bebanLabaRugiByCategory['Beban Upah Tenaga Kerja'] = $totalUpah;
        }

        $totalListrik = (float) $hpps->sum('biaya_listrik');
        if ($totalListrik > 0) {
            $bebanLabaRugiByCategory['Beban Listrik Mesin Roasting'] = $totalListrik;
        }

        $totalPenyusutan = (float) $hpps->sum('biaya_penyusutan');
        if ($totalPenyusutan > 0) {
            $bebanLabaRugiByCategory['Beban Penyusutan Mesin'] = $totalPenyusutan;
        }

        $totalKemasan = (float) $hpps->sum('biaya_kemasan');
        if ($totalKemasan > 0) {
            $bebanLabaRugiByCategory['Beban Kemasan Terpakai'] = $totalKemasan;
        }

        $totalBebanLabaRugi = array_sum($bebanLabaRugiByCategory);
        $labaBersih = $totalPendapatan - $totalBebanLabaRugi;

        // ==========================================
        // 2. DATA LAPORAN ARUS KAS (CASH FLOW)
        // ==========================================
        // Kas Masuk (Cash Inflows)
        $cashInflowByCategory = [];
        if ($totalLunas > 0) {
            $cashInflowByCategory['Penerimaan Penjualan (Lunas)'] = (float) $totalLunas;
        }
        if ($totalDP > 0) {
            $cashInflowByCategory['Penerimaan Uang Muka (DP)'] = (float) $totalDP;
        }
        $totalCashInflow = (float) $totalLunas + (float) $totalDP;

        // Kas Keluar (Cash Outflows)
        $cashOutflowByCategory = [];
        $greenBeanPurchases = GreenBeanStock::whereBetween('tanggal_beli', [$start, $end])->get();
        $totalBelanjaGB = 0;
        foreach ($greenBeanPurchases as $gb) {
            $totalBelanjaGB += ($gb->total_harga > 0) ? (float) $gb->total_harga : (($gb->stok_gram / 1000) * $gb->harga_beli);
        }
        if ($totalBelanjaGB > 0) {
            $cashOutflowByCategory['Pengadaan Bahan Baku (Green Bean)'] = (float) $totalBelanjaGB;
        }
        if ($totalUpah > 0) {
            $cashOutflowByCategory['Pembayaran Upah Tenaga Kerja'] = (float) $totalUpah;
        }

        $totalCashOutflow = array_sum($cashOutflowByCategory);
        $netCashFlow = $totalCashInflow - $totalCashOutflow;

        // Backward compatibility
        $inflowByCategory = $pendapatanByCategory;
        $outflowByCategory = $cashOutflowByCategory;
        $totalInflow = $totalPendapatan;
        $totalOutflow = $totalCashOutflow;

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
            // Laba Rugi
            'pendapatanByCategory',
            'totalPendapatan',
            'bebanLabaRugiByCategory',
            'totalBebanLabaRugi',
            'labaBersih',
            // Arus Kas
            'cashInflowByCategory',
            'totalCashInflow',
            'cashOutflowByCategory',
            'totalCashOutflow',
            'netCashFlow',
            // Legacy aliases
            'inflowByCategory',
            'outflowByCategory',
            'totalInflow',
            'totalOutflow'
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

        // 3. Data Laba Rugi
        $inflowData = PesananKeluar::whereBetween('pesanan_keluar.created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->where('status_pembayaran', 'lunas')
            ->join('roasted_bean', 'pesanan_keluar.roasted_bean_id', '=', 'roasted_bean.id')
            ->selectRaw('roasted_bean.bentuk_akhir as kategori, SUM(pesanan_keluar.total_pembayaran) as total')
            ->groupBy('roasted_bean.bentuk_akhir')
            ->get();

        $pendapatanByCategory = [];
        $totalPendapatan = 0;
        foreach ($inflowData as $item) {
            $cat = $item->kategori ?: 'Penjualan Kopi';
            $pendapatanByCategory[$cat] = (float) $item->total;
            $totalPendapatan += (float) $item->total;
        }

        $hpps = \App\Models\HppCalculation::whereHas('roastingBatch', function ($q) use ($start, $end) {
            $q->whereBetween('tanggal_roasting', [$start, $end]);
        })->get();

        $bebanLabaRugiByCategory = [];
        $roastingBahanBaku = RoastingBatch::whereBetween('tanggal_roasting', [$start, $end])
            ->where('status_batch', 'completed')
            ->sum('biaya_bahan_baku');
        $biayaBahanBakuTerpakai = $hpps->sum('biaya_bahan_baku') > 0 ? (float) $hpps->sum('biaya_bahan_baku') : (float) $roastingBahanBaku;
        if ($biayaBahanBakuTerpakai > 0) {
            $bebanLabaRugiByCategory['Bahan Baku Terpakai (HPP Sangrai)'] = $biayaBahanBakuTerpakai;
        }

        $totalUpah = (float) LogPekerja::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])->sum('total_upah');
        if ($totalUpah > 0) {
            $bebanLabaRugiByCategory['Beban Upah Tenaga Kerja'] = $totalUpah;
        }

        $totalListrik = (float) $hpps->sum('biaya_listrik');
        if ($totalListrik > 0) $bebanLabaRugiByCategory['Beban Listrik Mesin Roasting'] = $totalListrik;

        $totalPenyusutan = (float) $hpps->sum('biaya_penyusutan');
        if ($totalPenyusutan > 0) $bebanLabaRugiByCategory['Beban Penyusutan Mesin'] = $totalPenyusutan;

        $totalKemasan = (float) $hpps->sum('biaya_kemasan');
        if ($totalKemasan > 0) $bebanLabaRugiByCategory['Beban Kemasan Terpakai'] = $totalKemasan;

        $totalBeban = array_sum($bebanLabaRugiByCategory);
        $labaBersih = $totalPendapatan - $totalBeban;

        // 4. Data Arus Kas
        $transactions = PesananKeluar::whereBetween('pesanan_keluar.created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
        $totalLunas = (float) (clone $transactions)->where('pesanan_keluar.status_pembayaran', 'lunas')->sum('pesanan_keluar.total_pembayaran');
        $totalDP = 0;
        if (Schema::hasColumn('pesanan_keluar', 'metode_pembayaran') && Schema::hasColumn('pesanan_keluar', 'nominal_dp')) {
            $totalDP = (float) (clone $transactions)->where('metode_pembayaran', 'dp')->sum('nominal_dp');
        }
        $totalSisaPiutang = 0;
        if (Schema::hasColumn('pesanan_keluar', 'sisa_pembayaran')) {
            $totalSisaPiutang = (float) (clone $transactions)->sum('sisa_pembayaran');
        }

        $cashInflowByCategory = [];
        if ($totalLunas > 0) $cashInflowByCategory['Penerimaan Penjualan (Lunas)'] = $totalLunas;
        if ($totalDP > 0) $cashInflowByCategory['Penerimaan Uang Muka (DP)'] = $totalDP;
        $totalCashInflow = $totalLunas + $totalDP;

        $cashOutflowByCategory = [];
        $greenBeanPurchases = GreenBeanStock::whereBetween('tanggal_beli', [$start, $end])->get();
        $totalBelanjaGB = 0;
        foreach ($greenBeanPurchases as $gb) {
            $totalBelanjaGB += ($gb->total_harga > 0) ? (float) $gb->total_harga : (($gb->stok_gram / 1000) * $gb->harga_beli);
        }
        if ($totalBelanjaGB > 0) $cashOutflowByCategory['Pengadaan Bahan Baku (Green Bean)'] = (float) $totalBelanjaGB;
        if ($totalUpah > 0) $cashOutflowByCategory['Pembayaran Upah Tenaga Kerja'] = (float) $totalUpah;

        $totalCashOutflow = array_sum($cashOutflowByCategory);
        $netCashFlow = $totalCashInflow - $totalCashOutflow;

        $filename = "Laporan_Keuangan_Lengkap_{$start}_{$end}.xlsx";

        return Excel::download(
            new LaporanKeuanganExport(
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
                $start,
                $end
            ),
            $filename
        );
    }
}
