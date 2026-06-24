<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GreenBeanStock;
use App\Models\RoastingBatch;
use App\Models\RoastedBean;
use App\Models\Packaging;
use App\Models\FinishedGood;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q', '');
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
            ['title' => 'Catat Penjualan (Pre-Order)', 'url' => '/sales/create', 'desc' => 'Pencatatan order keluar baru', 'role' => 'sales'],
            ['title' => 'Daftar Penjualan', 'url' => '/sales', 'desc' => 'Riwayat transaksi & status pengiriman', 'role' => 'sales'],
            ['title' => 'Laporan Laba Rugi', 'url' => '/reports', 'desc' => 'Grafik keuntungan & margin bersih', 'role' => 'pic'],
            ['title' => 'Kelola Pengguna', 'url' => '/users', 'desc' => 'Manajemen user login & role staff', 'role' => 'pic'],
        ];

        foreach ($pages as $page) {
            if ($page['role'] === 'pic' && $role !== 'pic') {
                continue;
            }
            if ($page['role'] === 'sales' && !in_array($role, ['pic', 'operator'])) {
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
            $greenBeans = GreenBeanStock::with('greenBean')
                ->whereHas('greenBean', function($q) use ($query) {
                    $q->where('nama_biji', 'like', "%{$query}%")
                      ->orWhere('asal_kopi', 'like', "%{$query}%");
                })->get();

            foreach ($greenBeans as $gb) {
                $results[] = [
                    'type' => 'Bahan Baku',
                    'label' => ($gb->greenBean->nama_biji ?? 'Biji Kopi') . ' (' . ($gb->stok_gram / 1000) . ' kg)',
                    'desc' => 'Stok Green Bean asal ' . ($gb->greenBean->asal_kopi ?? 'Unknown') . ' | Beli: ' . $gb->tanggal_beli,
                    'url' => '/inventory'
                ];
            }

            // 3. Data Batch Roasting
            $batches = RoastingBatch::with('roastedBean')
                ->where('kode_batch', 'like', "%{$query}%")
                ->orWhere('profil_sangrai', 'like', "%{$query}%")
                ->orWhereHas('roastedBean', function($q) use ($query) {
                    $q->where('nama_kopi', 'like', "%{$query}%");
                })->get();

            foreach ($batches as $batch) {
                $results[] = [
                    'type' => 'Batch Roasting',
                    'label' => $batch->kode_batch . ' - ' . ($batch->roastedBean->nama_kopi ?? 'Produk'),
                    'desc' => 'Profil: ' . $batch->profil_sangrai . ' | Hasil Bersih: ' . $batch->berat_matang_bersih . ' Kg',
                    'url' => '/production'
                ];
            }

            // 4. Data Kemasan Kosong (Material)
            $packagings = Packaging::where('nama_kemasan', 'like', "%{$query}%")
                ->orWhere('tipe', 'like', "%{$query}%")
                ->get();

            foreach ($packagings as $pkg) {
                $results[] = [
                    'type' => 'Stok Kemasan',
                    'label' => $pkg->nama_kemasan . ' (' . $pkg->stok_pcs . ' pcs)',
                    'desc' => 'Material kemasan tipe ' . $pkg->tipe . ' | HPP: Rp ' . number_format($pkg->harga_satuan, 0, ',', '.'),
                    'url' => '/inventory'
                ];
            }

            // 5. Data Produk Jadi (Finished Goods)
            $finishedGoods = FinishedGood::with(['roastedBean', 'packaging'])
                ->whereHas('roastedBean', function($q) use ($query) {
                    $q->where('nama_kopi', 'like', "%{$query}%");
                })
                ->orWhereHas('packaging', function($q) use ($query) {
                    $q->where('nama_kemasan', 'like', "%{$query}%");
                })->get();

            foreach ($finishedGoods as $fg) {
                $results[] = [
                    'type' => 'Produk Jadi',
                    'label' => ($fg->roastedBean->nama_kopi ?? 'Kopi') . ' - ' . ($fg->packaging->nama_kemasan ?? 'Kemasan') . ' (' . $fg->stok_pcs . ' Pcs)',
                    'desc' => 'Kopi siap jual di rak gudang',
                    'url' => '/inventory'
                ];
            }
        }

        return response()->json($results);
    }
}
