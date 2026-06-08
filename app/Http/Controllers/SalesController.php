<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\PesananKeluar;
use App\Models\RoastedBeanStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    /**
     * Display the Sales Dashboard / Pre-Order List
     */
    public function index()
    {
        // Load actual completed sales from database
        $orders = PesananKeluar::with('roastedBean')->orderBy('created_at', 'desc')->get()->map(function($order) {
            return [
                'id' => 'PO-OUT-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
                'customer_name' => $order->nama_pesanan,
                'item' => $order->roastedBean ? $order->roastedBean->nama_kopi : 'Kopi',
                'qty' => $order->jumlah_gram_pesanan,
                'total_price' => $order->harga_jual_pesanan,
                'status' => $order->status_pengiriman,
                'payment_status' => $order->status_pembayaran,
                'pickup_status' => $order->status_pengambilan ?? 'Belum Diambil',
                'target_date' => $order->tanggal_pesanan,
                'created_at' => $order->created_at->toDateTimeString()
            ];
        });

        $internalOrders = [];
        $externalOrders = [];

        foreach ($orders as $o) {
            if (str_contains(strtolower($o['customer_name']), 'dosen') || str_contains(strtolower($o['customer_name']), 'kampus') || str_contains(strtolower($o['customer_name']), 'internal')) {
                $internalOrders[] = $o;
            } else {
                $externalOrders[] = $o;
            }
        }

        // Fallback dummy data if no actual orders exist yet
        if (count($orders) === 0) {
            $internalOrders = [
                [
                    'id' => 'PO-INT-001',
                    'customer_name' => 'Bapak Budi (Dosen TI)',
                    'item' => 'Arabica Ijen Roasting Medium (200g)',
                    'qty' => 1000,
                    'total_price' => 250000,
                    'status' => 'Menunggu Konfirmasi',
                    'payment_status' => 'Belum Bayar',
                    'target_date' => '2026-05-02',
                    'created_at' => '2026-04-29 09:00:00'
                ]
            ];
            $externalOrders = [
                [
                    'id' => 'PO-EXT-001',
                    'customer_name' => 'Cafe Senja, Banyuwangi',
                    'item' => 'Espresso Blend (1kg)',
                    'qty' => 10000,
                    'total_price' => 1500000,
                    'status' => 'Siap Diambil',
                    'payment_status' => 'DP (50%)',
                    'target_date' => '2026-04-29',
                    'created_at' => '2026-04-25 10:15:00'
                ]
            ];
        }

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
        $finishedGoods = \App\Models\FinishedGood::with(['roastedBean', 'packaging'])->get();
        
        if ($finishedGoods->isEmpty()) {
            $beans = RoastedBean::all();
            $pouches = Packaging::where('tipe', 'like', '%Pouch%')->get();
            if ($pouches->isEmpty()) {
                $pouches = Packaging::all();
            }
            
            $tempGoods = collect();
            foreach ($beans as $bean) {
                foreach ($pouches as $pouch) {
                    // Coba cari HPP terbaru untuk roasted bean ini
                    $latestHpp = \App\Models\HppCalculation::whereHas('roastingBatch', function($q) use ($bean) {
                        $q->where('roasted_bean_id', $bean->id);
                    })->latest()->first();
                    
                    $hppPerPcs = $latestHpp && $latestHpp->hasil_kemasan_pcs > 0 
                        ? round($latestHpp->total_hpp / $latestHpp->hasil_kemasan_pcs) 
                        : 0;
                        
                    if ($hppPerPcs <= 0) {
                        $hppPerPcs = $pouch->harga_satuan * 10;
                    }
                    
                    $suggestedPricePerPcs = $hppPerPcs > 0 ? round($hppPerPcs * 1.5) : 0;

                    $tempGoods->push([
                        'id' => null,
                        'roasted_bean_id' => $bean->id,
                        'nama' => $bean->nama_kopi . ' - ' . $pouch->nama_kemasan,
                        'berat_gram' => $pouch->kapasitas_gram,
                        'stok_pcs' => 0,
                        'hpp_per_pcs' => $hppPerPcs,
                        'harga_jual_per_pcs' => $suggestedPricePerPcs,
                    ]);
                }
            }
            $finishedGoods = $tempGoods;
        } else {
            $finishedGoods = $finishedGoods->map(function($item) {
                $latestHpp = \App\Models\HppCalculation::whereHas('roastingBatch', function($q) use ($item) {
                    $q->where('roasted_bean_id', $item->roasted_bean_id);
                })->latest()->first();
                
                $hppPerPcs = $latestHpp && $latestHpp->hasil_kemasan_pcs > 0 
                    ? round($latestHpp->total_hpp / $latestHpp->hasil_kemasan_pcs) 
                    : 0;
                    
                if ($hppPerPcs <= 0 && $item->packaging) {
                    $hppPerPcs = $item->packaging->harga_satuan * 10;
                }
                    
                $suggestedPricePerPcs = $hppPerPcs > 0 ? round($hppPerPcs * 1.5) : 0;
                
                return [
                    'id' => $item->id,
                    'roasted_bean_id' => $item->roasted_bean_id,
                    'nama' => ($item->roastedBean->nama_kopi ?? 'Kopi') . ' - ' . ($item->packaging->nama_kemasan ?? 'Kemasan'),
                    'berat_gram' => $item->packaging->kapasitas_gram ?? 0,
                    'stok_pcs' => $item->stok_pcs,
                    'hpp_per_pcs' => $hppPerPcs,
                    'harga_jual_per_pcs' => $suggestedPricePerPcs,
                ];
            });
        }

        return view('sales.create', compact('finishedGoods'));
    }

    /**
     * Store the sale using FIFO
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'roasted_bean_id' => 'required|exists:roasted_bean,id',
            'jumlah_gram_pesanan' => 'required|integer|min:1',
            'harga_jual_pesanan' => 'required|integer|min:0',
            'status_pengiriman' => 'required|string',
            'metode_pembayaran' => 'nullable|string|in:lunas,dp',
            'nominal_dp' => 'nullable|integer|min:0',
            'tanggal_bayar_dp' => 'nullable|date',
            'batas_waktu_pembayaran' => 'nullable|date',
        ]);

        $roastedBean = RoastedBean::findOrFail($validated['roasted_bean_id']);
        $requestedGram = $validated['jumlah_gram_pesanan'];

        try {
            DB::beginTransaction();

            // 1. FIFO deduction from RoastingBatch
            $batches = RoastingBatch::where('roasted_bean_id', $roastedBean->id)
                ->where('status_batch', 'completed')
                ->where('berat_matang_bersih', '>', 0)
                ->orderBy('tanggal_roasting', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $remainingToDeductBatches = $requestedGram;

            foreach ($batches as $batch) {
                if ($remainingToDeductBatches <= 0) break;

                $batchStokGram = $batch->berat_matang_bersih * 1000;

                if ($batchStokGram >= $remainingToDeductBatches) {
                    $newBatchStokGram = $batchStokGram - $remainingToDeductBatches;
                    $batch->berat_matang_bersih = $newBatchStokGram / 1000;
                    $batch->save();
                    $remainingToDeductBatches = 0;
                } else {
                    $remainingToDeductBatches -= $batchStokGram;
                    $batch->berat_matang_bersih = 0;
                    $batch->save();
                }
            }

            // 2. FIFO deduction from RoastedBeanStock
            $stocks = RoastedBeanStock::where('roasted_bean_id', $roastedBean->id)
                ->where('stok_gram', '>', 0)
                ->orderBy('tanggal_roasting', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $remainingToDeductStocks = $requestedGram;

            foreach ($stocks as $stock) {
                if ($remainingToDeductStocks <= 0) break;

                if ($stock->stok_gram >= $remainingToDeductStocks) {
                    $stock->stok_gram -= $remainingToDeductStocks;
                    $stock->save();
                    $remainingToDeductStocks = 0;
                } else {
                    $remainingToDeductStocks -= $stock->stok_gram;
                    $stock->stok_gram = 0;
                    $stock->save();
                }
            }

            // Update master roasted bean stock (allow to go negative for backorder)
            $roastedBean->total_stok = $roastedBean->total_stok - $requestedGram;
            $roastedBean->save();

            // Prepare payment
            $total = $validated['harga_jual_pesanan'];
            $metode = $validated['metode_pembayaran'] ?? 'lunas';
            $nominalDp = $validated['nominal_dp'] ?? null;
            $sisa = null;
            if ($metode === 'dp' && $nominalDp) {
                $sisa = max(0, $total - $nominalDp);
                $statusPembayaran = $sisa === 0 ? 'lunas' : 'dp';
            } else {
                $statusPembayaran = 'lunas';
            }

            // Create pesanan keluar record
            PesananKeluar::create([
                'user_id' => Auth::id() ?? 1,
                'roasted_bean_id' => $roastedBean->id,
                'nama_pesanan' => $validated['customer_name'],
                'jumlah_gram_pesanan' => $validated['jumlah_gram_pesanan'],
                'harga_jual_pesanan' => $validated['harga_jual_pesanan'],
                'tanggal_pesanan' => date('Y-m-d'),
                'total_pembayaran' => $total,
                'status_pembayaran' => $statusPembayaran,
                'status_pengiriman' => $validated['status_pengiriman'],
                'metode_pembayaran' => $metode,
                'nominal_dp' => $nominalDp,
                'sisa_pembayaran' => $sisa,
                'tanggal_bayar_dp' => $validated['tanggal_bayar_dp'] ?? null,
                'batas_waktu_pembayaran' => $validated['batas_waktu_pembayaran'] ?? null,
                'status_pengambilan' => 'Belum Diambil',
            ]);

            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Transaksi penjualan berhasil dicatat dengan pemotongan stok FIFO!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
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
                'user_id' => Auth::id() ?? 1,
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

