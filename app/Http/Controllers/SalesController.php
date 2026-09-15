<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use App\Models\PesananKeluar;
use App\Models\RoastedBeanStock;
use App\Models\Packaging;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    /**
     * Display the Sales Dashboard / Pre-Order List
     */
    public function index()
    {
        $mapOrder = function($order) {
            $itemName = $order->roastedBean ? $order->roastedBean->nama_kopi : 'Kopi';
            if ($order->packaging) {
                $pkgName = $order->packaging->nama_kemasan;
                $capacityStr = $order->packaging->kapasitas_gram . 'g';
                $capacityStrAlt = $order->packaging->kapasitas_gram . ' g';
                if (stripos($pkgName, $capacityStr) !== false || stripos($pkgName, $capacityStrAlt) !== false) {
                    $itemName .= ' - ' . $pkgName;
                } else {
                    $itemName .= ' - ' . $pkgName . ' (' . $capacityStr . ')';
                }
            }

            // Normalisasi status pembayaran ke label berformat cantik
            $payStatus = strtolower($order->status_pembayaran);
            if ($payStatus === 'lunas') {
                $paymentStatusDisplay = 'Lunas';
            } elseif ($payStatus === 'dp') {
                $paymentStatusDisplay = 'DP';
                if ($order->nominal_dp) {
                    $paymentStatusDisplay .= ' (Rp ' . number_format($order->nominal_dp, 0, ',', '.') . ')';
                }
            } else {
                $paymentStatusDisplay = 'Belum Bayar';
            }

            return [
                'id' => 'PO-OUT-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
                'raw_id' => $order->id,
                'customer_name' => $order->nama_pesanan,
                'item' => $itemName,
                'qty' => $order->jumlah_gram_pesanan,
                'qty_pcs' => $order->jumlah_pcs,
                'total_price' => $order->harga_jual_pesanan,
                'status' => $order->status_pengiriman,
                'payment_status' => $paymentStatusDisplay,
                'pickup_status' => $order->status_pengambilan ?? 'Belum Diambil',
                'target_date' => $order->tanggal_pesanan,
                'tipe_pesanan' => $order->tipe_pesanan ?? 'eksternal',
                'created_at' => $order->created_at->toDateTimeString()
            ];
        };

        // Query separately to paginate in database level
        $internalQuery = PesananKeluar::with(['roastedBean', 'packaging'])
            ->where('tipe_pesanan', 'internal')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page_internal');

        $externalQuery = PesananKeluar::with(['roastedBean', 'packaging'])
            ->where(function($query) {
                $query->where('tipe_pesanan', 'eksternal')
                      ->orWhereNull('tipe_pesanan');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page_external');

        // Transform results and append cross parameters to keep page state
        $internalOrders = $internalQuery->through($mapOrder)->appends([
            'tab' => 'internal',
            'page_external' => request('page_external')
        ]);

        $externalOrders = $externalQuery->through($mapOrder)->appends([
            'tab' => 'external',
            'page_internal' => request('page_internal')
        ]);

        // Fallback dummy data if no actual orders exist yet
        if ($internalQuery->total() === 0 && $externalQuery->total() === 0) {
            $internalOrders = new \Illuminate\Pagination\LengthAwarePaginator([
                [
                    'id' => 'PO-INT-001',
                    'raw_id' => 1,
                    'customer_name' => 'Bapak Budi (Dosen TI)',
                    'item' => 'Arabica Ijen Roasting Medium (200g)',
                    'qty' => 1000,
                    'qty_pcs' => 5,
                    'total_price' => 250000,
                    'status' => 'Menunggu Konfirmasi',
                    'payment_status' => 'Belum Bayar',
                    'pickup_status' => 'Belum Diambil',
                    'target_date' => '2026-05-02',
                    'tipe_pesanan' => 'internal',
                    'created_at' => '2026-04-29 09:00:00'
                ]
            ], 1, 10, 1, [
                'path' => request()->url(),
                'query' => request()->query()
            ]);

            $externalOrders = new \Illuminate\Pagination\LengthAwarePaginator([
                [
                    'id' => 'PO-EXT-001',
                    'raw_id' => 2,
                    'customer_name' => 'Cafe Senja, Banyuwangi',
                    'item' => 'Espresso Blend (1kg)',
                    'qty' => 10000,
                    'qty_pcs' => 10,
                    'total_price' => 1500000,
                    'status' => 'Siap Diambil',
                    'payment_status' => 'DP (Rp 500.000)',
                    'pickup_status' => 'Belum Diambil',
                    'target_date' => '2026-04-29',
                    'tipe_pesanan' => 'eksternal',
                    'created_at' => '2026-04-25 10:15:00'
                ]
            ], 1, 10, 1, [
                'path' => request()->url(),
                'query' => request()->query()
            ]);
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
            'finished_good_id' => 'nullable|string',
            'jumlah_pcs' => 'nullable|integer|min:1',
            'tanggal_pesanan' => 'nullable|date',
            'metode_pembayaran' => 'nullable|string|in:lunas,dp,belum_bayar',
            'nominal_dp' => 'nullable|integer|min:0',
            'tanggal_bayar_dp' => 'nullable|date',
            'batas_waktu_pembayaran' => 'nullable|date',
            'status_pengambilan' => 'nullable|string|in:Belum Diambil,Sudah Diambil',
            'tipe_pesanan'       => 'nullable|string|in:internal,eksternal',
        ]);

        $roastedBean = RoastedBean::findOrFail($validated['roasted_bean_id']);
        $requestedGram = $validated['jumlah_gram_pesanan'];

        // Resolve packaging_id from finished_good_id
        $packagingId = null;
        $jumlahPcs = $validated['jumlah_pcs'] ?? null;

        if (!empty($validated['finished_good_id'])) {
            $parts = explode('-', $validated['finished_good_id']);
            $roastedBeanId = $parts[0] ?? null;
            $beratGram = $parts[1] ?? null;

            if ($roastedBeanId && $beratGram) {
                $finishedGood = \App\Models\FinishedGood::where('roasted_bean_id', $roastedBeanId)
                    ->whereHas('packaging', function($query) use ($beratGram) {
                        $query->where('kapasitas_gram', $beratGram);
                    })->first();
                
                if ($finishedGood) {
                    $packagingId = $finishedGood->packaging_id;
                    if ($jumlahPcs > 0) {
                        $finishedGood->stok_pcs = max(0, $finishedGood->stok_pcs - $jumlahPcs);
                        $finishedGood->save();
                    }
                } else {
                    $pouch = \App\Models\Packaging::where('kapasitas_gram', $beratGram)->first();
                    if ($pouch) {
                        $packagingId = $pouch->id;
                    }
                }
            }
        }

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
            } elseif ($metode === 'belum_bayar') {
                $sisa = $total;
                $statusPembayaran = 'belum_bayar';
                $nominalDp = 0;
            } else {
                $statusPembayaran = 'lunas';
                $sisa = 0;
                $nominalDp = 0;
            }

            // Create pesanan keluar record
            PesananKeluar::create([
                'user_id' => Auth::id() ?? 1,
                'roasted_bean_id' => $roastedBean->id,
                'packaging_id' => $packagingId,
                'nama_pesanan' => $validated['customer_name'],
                'jumlah_gram_pesanan' => $validated['jumlah_gram_pesanan'],
                'jumlah_pcs' => $jumlahPcs,
                'harga_jual_pesanan' => $validated['harga_jual_pesanan'],
                'tanggal_pesanan' => $validated['tanggal_pesanan'] ?? date('Y-m-d'),
                'total_pembayaran' => $total,
                'status_pembayaran' => $statusPembayaran,
                'status_pengiriman' => $validated['status_pengiriman'],
                'metode_pembayaran' => $metode,
                'nominal_dp' => $nominalDp,
                'sisa_pembayaran' => $sisa,
                'tanggal_bayar_dp' => $validated['tanggal_bayar_dp'] ?? ($metode === 'dp' ? date('Y-m-d') : null),
                'batas_waktu_pembayaran' => $validated['batas_waktu_pembayaran'] ?? null,
                'status_pengambilan' => $validated['status_pengambilan'] ?? 'Belum Diambil',
                'tipe_pesanan'       => $validated['tipe_pesanan'] ?? 'eksternal',
            ]);

            DB::commit();

            $tab = ($validated['tipe_pesanan'] ?? 'eksternal') === 'internal' ? 'internal' : 'external';
            return redirect()->route('sales.index', ['tab' => $tab])->with('success', 'Transaksi penjualan berhasil dicatat dengan pemotongan stok FIFO!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show the form for editing the specified Pre-Order
     */
    public function edit($id)
    {
        $order = PesananKeluar::findOrFail($id);
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

        return view('sales.edit', compact('order', 'finishedGoods'));
    }

    /**
     * Update the specified Pre-Order in storage
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'roasted_bean_id' => 'required|exists:roasted_bean,id',
            'jumlah_gram_pesanan' => 'required|integer|min:1',
            'harga_jual_pesanan' => 'required|integer|min:0',
            'status_pengiriman' => 'required|string',
            'finished_good_id' => 'nullable|string',
            'jumlah_pcs' => 'nullable|integer|min:1',
            'tanggal_pesanan' => 'nullable|date',
            'metode_pembayaran' => 'nullable|string|in:lunas,dp,belum_bayar',
            'nominal_dp' => 'nullable|integer|min:0',
            'tanggal_bayar_dp' => 'nullable|date',
            'batas_waktu_pembayaran' => 'nullable|date',
            'status_pengambilan' => 'nullable|string|in:Belum Diambil,Sudah Diambil',
            'tipe_pesanan'       => 'nullable|string|in:internal,eksternal',
        ]);

        try {
            DB::beginTransaction();

            $order = PesananKeluar::findOrFail($id);

            // SECURITY: Prevent cashier from modifying Lunas/DP orders
            if (in_array(strtolower($order->status_pembayaran), ['lunas', 'dp'])) {
                if (!Auth::check() || Auth::user()->role !== 'PIC') {
                    return back()->withErrors(['error' => 'Akses Ditolak. Transaksi yang sudah dibayar (Lunas/DP) tidak boleh diubah oleh Kasir. Silakan hubungi PIC/Manajer.'])->withInput();
                }
            }

            // ============================================
            // 1. REVERT OLD STOCK DEDUCTION
            // ============================================
            $oldRoastedBean = RoastedBean::find($order->roasted_bean_id);
            if ($oldRoastedBean) {
                $oldRoastedBean->total_stok += $order->jumlah_gram_pesanan;
                $oldRoastedBean->save();
            }

            $oldStock = RoastedBeanStock::where('roasted_bean_id', $order->roasted_bean_id)
                ->latest('tanggal_roasting')
                ->first();
            if ($oldStock) {
                $oldStock->stok_gram += $order->jumlah_gram_pesanan;
                $oldStock->save();
            }

            $oldBatch = RoastingBatch::where('roasted_bean_id', $order->roasted_bean_id)
                ->where('status_batch', 'completed')
                ->latest('tanggal_roasting')
                ->first();
            if ($oldBatch) {
                $oldBatch->berat_matang_bersih += ($order->jumlah_gram_pesanan / 1000);
                $oldBatch->save();
            }

            if ($order->packaging_id) {
                $oldFinishedGood = \App\Models\FinishedGood::where('roasted_bean_id', $order->roasted_bean_id)
                    ->where('packaging_id', $order->packaging_id)
                    ->first();
                if ($oldFinishedGood) {
                    $oldFinishedGood->stok_pcs += $order->jumlah_pcs;
                    $oldFinishedGood->save();
                }
            }

            // ============================================
            // 2. APPLY NEW STOCK DEDUCTION (FIFO)
            // ============================================
            $roastedBean = RoastedBean::findOrFail($validated['roasted_bean_id']);
            $requestedGram = $validated['jumlah_gram_pesanan'];

            // Deduct RoastingBatch
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

            // Deduct RoastedBeanStock
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

            // Update master roasted bean stock
            $roastedBean->total_stok = $roastedBean->total_stok - $requestedGram;
            $roastedBean->save();

            // Resolve packaging_id
            $packagingId = null;
            $jumlahPcs = $validated['jumlah_pcs'] ?? null;
            if (!empty($validated['finished_good_id'])) {
                $parts = explode('-', $validated['finished_good_id']);
                $roastedBeanId = $parts[0] ?? null;
                $beratGram = $parts[1] ?? null;

                if ($roastedBeanId && $beratGram) {
                    $finishedGood = \App\Models\FinishedGood::where('roasted_bean_id', $roastedBeanId)
                        ->whereHas('packaging', function($query) use ($beratGram) {
                            $query->where('kapasitas_gram', $beratGram);
                        })->first();
                    
                    if ($finishedGood) {
                        $packagingId = $finishedGood->packaging_id;
                        if ($jumlahPcs > 0) {
                            $finishedGood->stok_pcs = max(0, $finishedGood->stok_pcs - $jumlahPcs);
                            $finishedGood->save();
                        }
                    } else {
                        $pouch = \App\Models\Packaging::where('kapasitas_gram', $beratGram)->first();
                        if ($pouch) {
                            $packagingId = $pouch->id;
                        }
                    }
                }
            }

            // Prepare payment
            $total = $validated['harga_jual_pesanan'];
            $metode = $validated['metode_pembayaran'] ?? 'lunas';
            $nominalDp = $validated['nominal_dp'] ?? null;
            $sisa = null;
            if ($metode === 'dp' && $nominalDp) {
                $sisa = max(0, $total - $nominalDp);
                $statusPembayaran = $sisa === 0 ? 'lunas' : 'dp';
            } elseif ($metode === 'belum_bayar') {
                $sisa = $total;
                $statusPembayaran = 'belum_bayar';
                $nominalDp = 0;
            } else {
                $statusPembayaran = 'lunas';
                $sisa = 0;
                $nominalDp = 0;
            }

            // Update pesanan keluar record
            $order->update([
                'roasted_bean_id' => $roastedBean->id,
                'packaging_id' => $packagingId,
                'nama_pesanan' => $validated['customer_name'],
                'jumlah_gram_pesanan' => $validated['jumlah_gram_pesanan'],
                'jumlah_pcs' => $jumlahPcs,
                'harga_jual_pesanan' => $validated['harga_jual_pesanan'],
                'tanggal_pesanan' => $validated['tanggal_pesanan'] ?? date('Y-m-d'),
                'total_pembayaran' => $total,
                'status_pembayaran' => $statusPembayaran,
                'status_pengiriman' => $validated['status_pengiriman'],
                'metode_pembayaran' => $metode,
                'nominal_dp' => $nominalDp,
                'sisa_pembayaran' => $sisa,
                'tanggal_bayar_dp' => $validated['tanggal_bayar_dp'] ?? ($metode === 'dp' ? date('Y-m-d') : null),
                'batas_waktu_pembayaran' => $validated['batas_waktu_pembayaran'] ?? null,
                'status_pengambilan' => $validated['status_pengambilan'] ?? 'Belum Diambil',
                'tipe_pesanan'       => $validated['tipe_pesanan'] ?? 'eksternal',
            ]);

            DB::commit();

            $tab = ($validated['tipe_pesanan'] ?? 'eksternal') === 'internal' ? 'internal' : 'external';
            return redirect()->route('sales.index', ['tab' => $tab])->with('success', 'Transaksi penjualan berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui transaksi: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Cancel / Delete the specified Pre-Order and restore stock
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $order = PesananKeluar::findOrFail($id);

            // Revert Stock Deduction
            $roastedBean = RoastedBean::find($order->roasted_bean_id);
            if ($roastedBean) {
                $roastedBean->total_stok += $order->jumlah_gram_pesanan;
                $roastedBean->save();
            }

            // Revert RoastedBeanStock
            $latestStock = RoastedBeanStock::where('roasted_bean_id', $order->roasted_bean_id)
                ->latest('tanggal_roasting')
                ->first();
            if ($latestStock) {
                $latestStock->stok_gram += $order->jumlah_gram_pesanan;
                $latestStock->save();
            } else {
                RoastedBeanStock::create([
                    'roasted_bean_id' => $order->roasted_bean_id,
                    'stok_gram' => $order->jumlah_gram_pesanan,
                    'harga_roasting' => 0,
                    'tanggal_roasting' => now()->toDateString(),
                ]);
            }

            // Revert RoastingBatch
            $latestBatch = RoastingBatch::where('roasted_bean_id', $order->roasted_bean_id)
                ->where('status_batch', 'completed')
                ->latest('tanggal_roasting')
                ->first();
            if ($latestBatch) {
                $latestBatch->berat_matang_bersih += ($order->jumlah_gram_pesanan / 1000);
                $latestBatch->save();
            }

            // Revert FinishedGood Stock
            if ($order->packaging_id) {
                $finishedGood = \App\Models\FinishedGood::where('roasted_bean_id', $order->roasted_bean_id)
                    ->where('packaging_id', $order->packaging_id)
                    ->first();
                if ($finishedGood) {
                    $finishedGood->stok_pcs += $order->jumlah_pcs;
                    $finishedGood->save();
                }
            }

            // Delete order
            $order->delete();

            DB::commit();

            $tab = ($order->tipe_pesanan ?? 'eksternal') === 'internal' ? 'internal' : 'external';
            return redirect()->route('sales.index', ['tab' => $tab])->with('success', 'Transaksi penjualan berhasil dibatalkan dan stok dikembalikan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan transaksi: ' . $e->getMessage()]);
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

