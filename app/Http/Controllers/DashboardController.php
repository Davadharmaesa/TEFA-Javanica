<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GreenBean;
use App\Models\RoastingBatch;
use App\Models\PesananKeluar;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Stok Biji Mentah (Ambil SUM dari tabel green_bean_stock)
        $total_stok_biji_mentah = \App\Models\GreenBeanStock::sum('stok_gram') / 1000;

        // 2. Total Kopi Matang (Ambil SUM dari kolom berat_matang_bersih di tabel Roasting)
        $total_kopi_matang = RoastingBatch::sum('berat_matang_bersih');

        // 3. Penjualan Retail Hari Ini (Hanya transaksi hari ini yang statusnya LUNAS)
        $penjualan_retail = PesananKeluar::whereDate('created_at', Carbon::today())
            ->where('status_pembayaran', 'lunas')
            ->sum('total_pembayaran');

        // 4. Stok Menipis (Total stok tiap master green bean < 5 Kg)
        $stok_menipis = GreenBean::withSum('stocks as stok_gram', 'stok_gram')
            ->get()
            ->filter(function ($item) {
                return ($item->stok_gram ?? 0) < 5000;
            })
            ->map(function ($item) {
                return [
                    'name' => $item->nama_biji,
                    'type' => 'Green Bean',
                    'amount' => ($item->stok_gram ?? 0) / 1000,
                    'unit' => 'kg'
                ];
            });

        // FLAG WARNING
        $has_warning = $stok_menipis->isNotEmpty();

        // 5. Log Aktivitas Terbaru (Ambil 5 batch roasting terakhir)
        $recent_logs = RoastingBatch::with('greenBean') // Pastikan ada relasi greenBean di Model
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'type' => 'ROASTING',
                    'description' => "Sangrai {$log->greenBean->nama_biji} (" . $log->profil_sangrai . "). Hasil: " . $log->berat_matang_bersih . "kg",
                    'time' => $log->created_at->diffForHumans(),
                    'user' => $log->user->name ?? 'System' // Jika ada relasi user
                ];
            });

        // 6. Rata-Rata Burnoff (Penyusutan)
        $total_raw_roasted = RoastingBatch::sum('berat_mentah_gudang');
        $total_cooked_roasted = RoastingBatch::sum('berat_matang_bersih');
        $avg_burnoff = $total_raw_roasted > 0
            ? round((($total_raw_roasted - $total_cooked_roasted) / $total_raw_roasted) * 100, 1)
            : 0;

        // 7. Antrean Pesanan MTO
        $antrean_mto = PesananKeluar::whereIn('status_pengiriman', ['Menunggu Konfirmasi', 'Diproses'])
            ->orderBy('tanggal_pesanan', 'asc')
            ->get();

        return view('dashboard', compact(
            'total_stok_biji_mentah',
            'total_kopi_matang',
            'penjualan_retail',
            'stok_menipis',
            'recent_logs',
            'avg_burnoff',
            'has_warning',
            'antrean_mto'
        ));
    }
}