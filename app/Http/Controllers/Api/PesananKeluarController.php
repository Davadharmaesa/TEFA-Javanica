<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PesananKeluar;
use App\Models\RoastedBean;
use App\Models\RoastedBeanStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PesananKeluarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pesanan = PesananKeluar::with('roastedBean')->orderBy('id', 'desc')->get();
        return response()->json($pesanan);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'roasted_bean_id' => 'required|exists:roasted_bean,id',
            'nama_pesanan' => 'required|string|max:255',
            'jumlah_gram_pesanan' => 'required|integer|min:1',
            'harga_jual_pesanan' => 'required|integer|min:0',
            'status_pembayaran' => 'nullable|string',
            'status_pengiriman' => 'nullable|string',
            'metode_pembayaran' => 'nullable|string|in:lunas,dp,belum_bayar',
            'nominal_dp' => 'nullable|integer|min:0',
            'tanggal_bayar_dp' => 'nullable|date',
            'batas_waktu_pembayaran' => 'nullable|date',
            'status_pengambilan' => 'nullable|string',
        ]);

        $roastedBean = \App\Models\RoastedBean::findOrFail($validated['roasted_bean_id']);
        $requestedGram = $validated['jumlah_gram_pesanan'];

        if ($roastedBean->total_stok < $requestedGram) {
            return response()->json([
                'message' => 'Stok Roasted Bean di gudang tidak mencukupi (Tersedia: ' . ($roastedBean->total_stok / 1000) . ' kg).'
            ], 400);
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // 1. FIFO deduction from RoastingBatch
            $batches = \App\Models\RoastingBatch::where('roasted_bean_id', $roastedBean->id)
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
            $stocks = \App\Models\RoastedBeanStock::where('roasted_bean_id', $roastedBean->id)
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
            $roastedBean->total_stok = max(0, $roastedBean->total_stok - $requestedGram);
            $roastedBean->save();

            // Prepare payment values
            $total = $validated['harga_jual_pesanan'] * $validated['jumlah_gram_pesanan'];
            $metode = $validated['metode_pembayaran'] ?? ($validated['status_pembayaran'] ?? 'lunas');
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
                $statusPembayaran = $validated['status_pembayaran'] ?? 'lunas';
                $sisa = 0;
                $nominalDp = 0;
            }

            // Create pesanan keluar record
            $pesanan = PesananKeluar::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                'roasted_bean_id' => $roastedBean->id,
                'nama_pesanan' => $validated['nama_pesanan'],
                'jumlah_gram_pesanan' => $validated['jumlah_gram_pesanan'],
                'harga_jual_pesanan' => $validated['harga_jual_pesanan'],
                'tanggal_pesanan' => date('Y-m-d'),
                'total_pembayaran' => $total,
                'status_pembayaran' => $statusPembayaran,
                'status_pengiriman' => $validated['status_pengiriman'] ?? 'selesai',
                'metode_pembayaran' => $metode,
                'nominal_dp' => $nominalDp,
                'sisa_pembayaran' => $sisa,
                'tanggal_bayar_dp' => $validated['tanggal_bayar_dp'] ?? null,
                'batas_waktu_pembayaran' => $validated['batas_waktu_pembayaran'] ?? null,
                'status_pengambilan' => $validated['status_pengambilan'] ?? 'Belum Diambil',
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'message' => 'Transaksi penjualan berhasil dicatat dengan pemotongan stok FIFO.',
                'data' => $pesanan
            ], 201);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pesanan = PesananKeluar::with('roastedBean')->findOrFail($id);
        return response()->json($pesanan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pesanan = PesananKeluar::findOrFail($id);
        $pesanan->update($request->all());
        return response()->json($pesanan);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pesanan = PesananKeluar::findOrFail($id);
        $pesanan->delete();
        return response()->json(['message' => 'Pesanan berhasil dihapus']);
    }
}
