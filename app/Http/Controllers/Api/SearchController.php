<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GreenBeanStock;
use App\Models\RoastingBatch;
use App\Models\RoastedBean;
use App\Models\RoastedBeanStock;
use App\Models\Packaging;
use App\Models\FinishedGood;
use App\Models\PesananKeluar;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->query('q', ''));
        $user = Auth::user();
        $role = strtolower($user->role ?? 'operator');

        $results = [];

        $pages = [
            ['title' => 'Beranda', 'url' => '/dashboard', 'desc' => 'Kembali ke ringkasan utama sistem', 'role' => 'all'],
            ['title' => 'Roasting & Produksi', 'url' => '/production', 'desc' => 'Mulai batch sangrai & proses kemas baru', 'role' => 'all'],
            ['title' => 'Log Kerja Harian', 'url' => '/log-kerja', 'desc' => 'Catat kontribusi pekerja roasting', 'role' => 'all'],
            ['title' => 'Gudang & Inventaris', 'url' => '/inventory', 'desc' => 'Lihat ketersediaan bahan baku & produk jadi', 'role' => 'all'],
            ['title' => 'Kalkulator HPP', 'url' => '/hpp-calculator', 'desc' => 'Hitung Harga Pokok Produksi produk', 'role' => 'pic'],
            ['title' => 'Master Data', 'url' => '/master', 'desc' => 'Kelola harga bahan dasar & biaya operasional', 'role' => 'pic'],
            ['title' => 'Catat Penjualan (Pre-Order)', 'url' => '/sales/create', 'desc' => 'Pencatatan pesanan keluar baru', 'role' => 'all'],
            ['title' => 'Daftar Penjualan', 'url' => '/sales', 'desc' => 'Riwayat transaksi & status pengiriman', 'role' => 'all'],
            ['title' => 'Laporan Laba Rugi', 'url' => '/reports', 'desc' => 'Grafik keuntungan & margin laba bersih', 'role' => 'pic'],
            ['title' => 'Laporan Arus Kas', 'url' => '/reports', 'desc' => 'Arus kas masuk & kas keluar operasional', 'role' => 'pic'],
            ['title' => 'Ekspor Laporan Keuangan', 'url' => '/reports/export', 'desc' => 'Unduh laporan laba rugi & arus kas periode aktif (Excel)', 'role' => 'pic'],
            ['title' => 'Kelola Pengguna', 'url' => '/users', 'desc' => 'Manajemen user login & hak akses staf', 'role' => 'pic'],
        ];

        foreach ($pages as $page) {
            if ($page['role'] === 'pic' && $role !== 'pic') {
                continue;
            }
            if (empty($query) || str_contains(strtolower($page['title']), strtolower($query)) || str_contains(strtolower($page['desc']), strtolower($query))) {
                $results[] = [
                    'type' => 'Halaman',
                    'label' => $page['title'],
                    'desc' => $page['desc'],
                    'url' => $page['url']
                ];
            }
        }

        if (!empty($query)) {
            // 2. Data Stok Biji Mentah (Green Bean)
            try {
                $greenBeans = GreenBeanStock::with('greenBean')
                    ->whereHas('greenBean', function($q) use ($query) {
                        $q->where('nama_biji', 'like', "%{$query}%")
                          ->orWhere('asal_kopi', 'like', "%{$query}%");
                    })
                    ->take(5)
                    ->get();

                foreach ($greenBeans as $gb) {
                    $results[] = [
                        'type' => 'Bahan Baku',
                        'label' => ($gb->greenBean->nama_biji ?? 'Biji Kopi') . ' (' . ($gb->stok_gram / 1000) . ' kg)',
                        'desc' => 'Stok Green Bean asal ' . ($gb->greenBean->asal_kopi ?? 'Unknown') . ($gb->tanggal_beli ? ' | Beli: ' . $gb->tanggal_beli : ''),
                        'url' => '/inventory'
                    ];
                }
            } catch (\Exception $e) {}

            // 3. Data Roasted Bean (Curah)
            try {
                $roastedBeans = RoastedBeanStock::with('roastedBean')
                    ->whereHas('roastedBean', function($q) use ($query) {
                        $q->where('nama_kopi', 'like', "%{$query}%");
                    })
                    ->take(4)
                    ->get();

                foreach ($roastedBeans as $rb) {
                    $results[] = [
                        'type' => 'Bahan Baku',
                        'label' => ($rb->roastedBean->nama_kopi ?? 'Roasted Bean') . ' (' . ($rb->stok_gram / 1000) . ' kg)',
                        'desc' => 'Stok biji kopi sangrai curah di gudang',
                        'url' => '/inventory'
                    ];
                }
            } catch (\Exception $e) {}

            // 4. Data Batch Roasting
            try {
                $batches = RoastingBatch::with('roastedBean')
                    ->where('kode_batch', 'like', "%{$query}%")
                    ->orWhere('profil_sangrai', 'like', "%{$query}%")
                    ->orWhereHas('roastedBean', function($q) use ($query) {
                        $q->where('nama_kopi', 'like', "%{$query}%");
                    })
                    ->latest()
                    ->take(5)
                    ->get();

                foreach ($batches as $batch) {
                    $results[] = [
                        'type' => 'Batch Roasting',
                        'label' => $batch->kode_batch . ' - ' . ($batch->roastedBean->nama_kopi ?? 'Produk'),
                        'desc' => 'Profil: ' . ($batch->profil_sangrai ?? '-') . ' | Hasil: ' . ($batch->berat_matang_bersih ?? 0) . ' Kg',
                        'url' => '/production'
                    ];
                }
            } catch (\Exception $e) {}

            // 5. Data Kemasan Kosong (Packaging)
            try {
                $packagings = Packaging::where('nama_kemasan', 'like', "%{$query}%")
                    ->orWhere('tipe', 'like', "%{$query}%")
                    ->take(5)
                    ->get();

                foreach ($packagings as $pkg) {
                    $results[] = [
                        'type' => 'Stok Kemasan',
                        'label' => $pkg->nama_kemasan . ' (' . number_format($pkg->stok_pcs, 0, ',', '.') . ' pcs)',
                        'desc' => 'Kemasan tipe ' . ($pkg->tipe ?? '-') . ' | HPP: Rp ' . number_format($pkg->harga_satuan ?? 0, 0, ',', '.'),
                        'url' => '/inventory'
                    ];
                }
            } catch (\Exception $e) {}

            // 6. Data Produk Jadi (Finished Goods)
            try {
                $finishedGoods = FinishedGood::with(['roastedBean', 'packaging'])
                    ->whereHas('roastedBean', function($q) use ($query) {
                        $q->where('nama_kopi', 'like', "%{$query}%");
                    })
                    ->orWhereHas('packaging', function($q) use ($query) {
                        $q->where('nama_kemasan', 'like', "%{$query}%");
                    })
                    ->take(5)
                    ->get();

                foreach ($finishedGoods as $fg) {
                    $results[] = [
                        'type' => 'Produk Jadi',
                        'label' => ($fg->roastedBean->nama_kopi ?? 'Kopi') . ' - ' . ($fg->packaging->nama_kemasan ?? 'Kemasan') . ' (' . number_format($fg->stok_pcs, 0, ',', '.') . ' Pcs)',
                        'desc' => 'Produk siap jual di rak gudang inventaris',
                        'url' => '/inventory'
                    ];
                }
            } catch (\Exception $e) {}

            // 7. Data Penjualan (Pesanan Keluar)
            try {
                $orders = PesananKeluar::with(['roastedBean', 'packaging'])
                    ->where('nama_pesanan', 'like', "%{$query}%")
                    ->orWhere('status_pembayaran', 'like', "%{$query}%")
                    ->orWhere('status_pengiriman', 'like', "%{$query}%")
                    ->orWhere('metode_pembayaran', 'like', "%{$query}%")
                    ->orWhereHas('roastedBean', function($q) use ($query) {
                        $q->where('nama_kopi', 'like', "%{$query}%");
                    })
                    ->latest()
                    ->take(5)
                    ->get();

                foreach ($orders as $order) {
                    $results[] = [
                        'type' => 'Penjualan',
                        'label' => $order->nama_pesanan . ' (Rp ' . number_format($order->total_pembayaran ?? 0, 0, ',', '.') . ')',
                        'desc' => 'Status: ' . ($order->status_pembayaran ?? '-') . ' | Pengiriman: ' . ($order->status_pengiriman ?? '-') . ($order->roastedBean ? ' | ' . $order->roastedBean->nama_kopi : ''),
                        'url' => '/sales'
                    ];
                }
            } catch (\Exception $e) {}

            // 8. Data Pengguna (Hanya untuk PIC)
            if ($role === 'pic') {
                try {
                    $users = User::where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('role', 'like', "%{$query}%")
                        ->take(4)
                        ->get();

                    foreach ($users as $u) {
                        $results[] = [
                            'type' => 'Pengguna',
                            'label' => $u->name . ' (' . ucfirst($u->role ?? 'Operator') . ')',
                            'desc' => 'Email: ' . $u->email,
                            'url' => '/users'
                        ];
                    }
                } catch (\Exception $e) {}
            }
        }

        return response()->json($results);
    }
}
