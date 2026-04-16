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
        // 1. Total Stok Biji Mentah (Ambil SUM dari kolom stok_gram)
        $total_stok_biji_mentah = GreenBean::sum('stok_gram') / 1000;

        // 2. Total Kopi Matang (Ambil SUM dari kolom berat_matang di tabel Roasting)
        $total_kopi_matang = RoastingBatch::sum('berat_matang') / 1000;

        // 3. Penjualan Retail Hari Ini (Hanya transaksi hari ini yang statusnya LUNAS)
        $penjualan_retail = PesananKeluar::whereDate('created_at', Carbon::today())
                            ->where('status_pembayaran', 'lunas')
                            ->sum('total_pembayaran');

        // 4. Stok Menipis (Ambil Green Bean yang stoknya di bawah 5kg)
        $stok_menipis = GreenBean::where('stok_gram', '<', 5000)
            ->get()
            ->map(function ($item) {
                return [
                    'name'   => $item->nama_biji,
                    'type'   => 'Green Bean',
                    'amount' => $item->stok_gram / 1000,
                    'unit'   => 'kg'
                ];
            });

        // 5. Log Aktivitas Terbaru (Ambil 5 batch roasting terakhir)
        $recent_logs = RoastingBatch::with('greenBean') // Pastikan ada relasi greenBean di Model
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'type'        => 'ROASTING',
                    'description' => "Proses sangrai {$log->greenBean->nama_biji} selesai. Hasil: " . ($log->berat_matang / 1000) . "kg",
                    'time'        => $log->created_at->diffForHumans(),
                    'user'        => $log->user->name ?? 'System' // Jika ada relasi user
                ];
            });

        // 6. Rata-Rata Burnoff (Penyusutan)
        $total_raw_roasted = RoastingBatch::sum('berat_mentah');
        $total_cooked_roasted = RoastingBatch::sum('berat_matang');
        $avg_burnoff = $total_raw_roasted > 0 
            ? round((($total_raw_roasted - $total_cooked_roasted) / $total_raw_roasted) * 100, 1) 
            : 0;

        return view('dashboard', compact(
            'total_stok_biji_mentah',
            'total_kopi_matang',
            'penjualan_retail',
            'stok_menipis',
            'recent_logs',
            'avg_burnoff'
        ));
    }
}