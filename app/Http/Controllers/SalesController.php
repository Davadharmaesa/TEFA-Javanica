<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoastedBean;
use App\Models\PesananKeluar;
use App\Models\RoastedBeanStock;

class SalesController extends Controller
{
    /**
     * Display the Sales Dashboard / Pre-Order List
     */
    public function index()
    {
        // Dummy data for Internal Pre-Orders (Poliwangi Campus)
        $internalOrders = [
            [
                'id' => 'PO-INT-001',
                'customer_name' => 'Bapak Budi (Dosen TI)',
                'item' => 'Arabica Ijen Roasting Medium (200g)',
                'qty' => 5,
                'total_price' => 250000,
                'status' => 'Menunggu Konfirmasi',
                'payment_status' => 'Belum Bayar',
                'target_date' => '2026-05-02',
                'created_at' => '2026-04-29 09:00:00'
            ],
            [
                'id' => 'PO-INT-002',
                'customer_name' => 'Ibu Rina (Keuangan)',
                'item' => 'Robusta Gombengsari Dark (500g)',
                'qty' => 2,
                'total_price' => 120000,
                'status' => 'Diproses',
                'payment_status' => 'Lunas',
                'target_date' => '2026-04-30',
                'created_at' => '2026-04-28 14:30:00'
            ],
        ];

        // Dummy data for External Pre-Orders
        $externalOrders = [
            [
                'id' => 'PO-EXT-001',
                'customer_name' => 'Cafe Senja, Banyuwangi',
                'item' => 'Espresso Blend (1kg)',
                'qty' => 10,
                'total_price' => 1500000,
                'status' => 'Siap Diambil',
                'payment_status' => 'DP (50%)',
                'target_date' => '2026-04-29',
                'created_at' => '2026-04-25 10:15:00'
            ],
            [
                'id' => 'PO-EXT-002',
                'customer_name' => 'Andi Wijaya',
                'item' => 'Arabica Ijen Roasting Light (200g)',
                'qty' => 1,
                'total_price' => 60000,
                'status' => 'Selesai',
                'payment_status' => 'Lunas',
                'target_date' => '2026-04-27',
                'created_at' => '2026-04-26 16:45:00'
            ]
        ];

        // Data untuk Tab Kasir Timbangan Dinamis
        $roastedBeans = RoastedBean::withSum('stocks as stok_tersedia', 'stok_gram')->get();
        $kasirHistory = PesananKeluar::with(['roastedBean', 'user'])
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        return view('sales.index', compact('internalOrders', 'externalOrders', 'roastedBeans', 'kasirHistory'));
    }

    /**
     * Show the form for creating a new Pre-Order
     */
    public function create()
    {
        return view('sales.create');
    }

    /**
     * Store a cashier sale (Kasir Timbangan Dinamis) via internal web route.
     */
    public function storeCashierSale(Request $request)
    {
        $validated = $request->validate([
            'roasted_bean_id' => 'required|exists:roasted_bean,id',
            'jumlah_gram_pesanan' => 'required|integer|min:1',
            'harga_jual_pesanan' => 'required|integer|min:0',
            'nama_pesanan' => 'nullable|string|max:255',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $roastedBean = RoastedBean::findOrFail($validated['roasted_bean_id']);
            $jumlahGram = $validated['jumlah_gram_pesanan'];

            // Validasi ketersediaan stok
            $totalAvailable = RoastedBeanStock::where('roasted_bean_id', $roastedBean->id)->sum('stok_gram');
            if ($totalAvailable < $jumlahGram) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok kopi matang tidak mencukupi. Tersedia: ' . number_format($totalAvailable) . ' gram.'
                ], 400);
            }

            // Kurangi stok FIFO
            $stocks = RoastedBeanStock::where('roasted_bean_id', $roastedBean->id)
                ->where('stok_gram', '>', 0)
                ->orderBy('tanggal_roasting', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $remainingToDeduct = $jumlahGram;
            foreach ($stocks as $stock) {
                if ($remainingToDeduct <= 0) break;
                if ($stock->stok_gram >= $remainingToDeduct) {
                    $stock->stok_gram -= $remainingToDeduct;
                    $stock->save();
                    $remainingToDeduct = 0;
                } else {
                    $remainingToDeduct -= $stock->stok_gram;
                    $stock->stok_gram = 0;
                    $stock->save();
                }
            }

            // Update cache
            $roastedBean->total_stok = max(0, $roastedBean->total_stok - $jumlahGram);
            $roastedBean->save();

            // Catat pesanan
            $pesanan = PesananKeluar::create([
                'user_id' => auth()->id(),
                'roasted_bean_id' => $roastedBean->id,
                'nama_pesanan' => $validated['nama_pesanan'] ?: 'Kasir Timbangan - ' . $roastedBean->nama_kopi,
                'jumlah_gram_pesanan' => $jumlahGram,
                'harga_jual_pesanan' => $validated['harga_jual_pesanan'],
                'tanggal_pesanan' => now()->toDateString(),
                'total_pembayaran' => $jumlahGram * $validated['harga_jual_pesanan'],
                'status_pembayaran' => 'lunas',
                'status_pengiriman' => 'selesai',
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil! Total: Rp ' . number_format($pesanan->total_pembayaran, 0, ',', '.'),
                'data' => $pesanan->load('roastedBean')
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cashier history as JSON for dynamic refresh.
     */
    public function getCashierHistory()
    {
        $history = PesananKeluar::with(['roastedBean', 'user'])
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        return response()->json($history);
    }

    /**
     * Get roasted beans with stock for dynamic refresh.
     */
    public function getRoastedBeansStock()
    {
        $beans = RoastedBean::withSum('stocks as stok_tersedia', 'stok_gram')->get();
        return response()->json($beans);
    }
}

