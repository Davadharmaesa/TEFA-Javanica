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
        $pesanan = PesananKeluar::with(['roastedBean', 'user'])->orderBy('id', 'desc')->get();
        return response()->json($pesanan);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'roasted_bean_id' => 'required|exists:roasted_bean,id',
            'jumlah_gram_pesanan' => 'required|integer|min:1',
            'harga_jual_pesanan' => 'required|integer|min:0',
            'nama_pesanan' => 'nullable|string',
            'status_pembayaran' => 'nullable|string|in:pending,lunas',
            'status_pengiriman' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $roastedBean = RoastedBean::findOrFail($validated['roasted_bean_id']);
            $jumlahGram = $validated['jumlah_gram_pesanan'];

            // 1. Validasi Ketersediaan Stok Kopi Matang di tabel roasted_bean_stock
            $totalAvailable = RoastedBeanStock::where('roasted_bean_id', $roastedBean->id)->sum('stok_gram');
            if ($totalAvailable < $jumlahGram) {
                return response()->json([
                    'message' => 'Stok kopi matang tidak mencukupi.'
                ], 400);
            }

            // 2. Kurangi Stok dari tabel roasted_bean_stock menggunakan FIFO
            $stocks = RoastedBeanStock::where('roasted_bean_id', $roastedBean->id)
                ->where('stok_gram', '>', 0)
                ->orderBy('tanggal_roasting', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $remainingToDeduct = $jumlahGram;
            foreach ($stocks as $stock) {
                if ($remainingToDeduct <= 0) {
                    break;
                }

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

            // 3. Kurangi total_stok pada tabel cache roasted_bean secara sinkron
            $roastedBean->total_stok = max(0, $roastedBean->total_stok - $jumlahGram);
            $roastedBean->save();

            // 4. Mencatat penjualan di tabel pesanan_keluar
            $namaPesanan = $validated['nama_pesanan'] ?? 'Kasir Timbangan - ' . $roastedBean->nama_kopi;
            $statusPembayaran = $validated['status_pembayaran'] ?? 'lunas';
            $statusPengiriman = $validated['status_pengiriman'] ?? 'selesai';

            $pesanan = PesananKeluar::create([
                'user_id' => Auth::id() ?? 1,
                'roasted_bean_id' => $roastedBean->id,
                'nama_pesanan' => $namaPesanan,
                'jumlah_gram_pesanan' => $jumlahGram,
                'harga_jual_pesanan' => $validated['harga_jual_pesanan'],
                'tanggal_pesanan' => now()->toDateString(),
                'total_pembayaran' => $jumlahGram * $validated['harga_jual_pesanan'],
                'status_pembayaran' => $statusPembayaran,
                'status_pengiriman' => $statusPengiriman,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Transaksi penjualan berhasil disimpan dan stok terpotong.',
                'data' => $pesanan
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // TODO
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // TODO
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // TODO
    }
}
