<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoastedBean;
use App\Models\Packaging;
use App\Models\FinishedGood;
use Illuminate\Support\Facades\DB;

class PackagingApiController extends Controller
{
    /**
     * Proses Pengemasan Kopi Matang
     */
    public function storePackagingProcess(Request $request)
    {
        $validated = $request->validate([
            'roasted_bean_id' => 'required|exists:roasted_bean,id',
            'packaging_id' => 'required|exists:packagings,id',
            'sticker_id' => 'nullable|exists:packagings,id',
            'jumlah_stok_digunakan_gram' => 'required|numeric|min:1',
            'ukuran_kemasan_gram' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            $roastedBean = RoastedBean::findOrFail($validated['roasted_bean_id']);
            $pouch = Packaging::findOrFail($validated['packaging_id']);
            
            $sticker = null;
            if (!empty($validated['sticker_id'])) {
                $sticker = Packaging::findOrFail($validated['sticker_id']);
            }

            $stokDigunakan = $validated['jumlah_stok_digunakan_gram'];
            
            // Auto-Adjust Ukuran: automatically adjust to target packaging size from DB
            $ukuranKemasan = $pouch->kapasitas_gram;
            if ($ukuranKemasan <= 0) {
                return response()->json(['message' => 'Kemasan utama (Pouch) harus memiliki kapasitas gram yang valid.'], 400);
            }

            // Validasi apakah stok roasted bean cukup
            if ($roastedBean->total_stok < $stokDigunakan) {
                return response()->json(['message' => 'Stok Roasted Bean tidak mencukupi.'], 400);
            }

            // 1. Hitung output maksimum berdasarkan kopi
            $maxPcsByCoffee = floor($stokDigunakan / $ukuranKemasan);

            // 2. Terapkan limitasi stok material di database secara ril
            $maxPcsByPouch = $pouch->stok_pcs;
            $maxPcsBySticker = $sticker ? $sticker->stok_pcs : $maxPcsByCoffee;

            // Output pcs yang dapat dikemas adalah nilai minimum dari ketiganya
            $hasilKemasanPcs = min($maxPcsByCoffee, $maxPcsByPouch, $maxPcsBySticker);

            if ($hasilKemasanPcs <= 0) {
                return response()->json(['message' => 'Stok kemasan kosong (pouch/stiker) tidak mencukupi untuk membuat setidaknya 1 produk kemasan.'], 400);
            }

            // 3. Update Stok Roasted Bean
            // Kurangi stok utama hanya sebesar yang sukses dikemas ke dalam pouch (tidak termasuk sisa residu)
            $stokTerkemas = $hasilKemasanPcs * $ukuranKemasan;
            $roastedBean->total_stok -= $stokTerkemas;

            // Perbarui sisa_stok sesuai dengan sisa residu ril yang dihasilkan
            $sisaGram = $stokDigunakan - $stokTerkemas;
            $roastedBean->sisa_stok = $sisaGram;
            $roastedBean->save();

            // 4. Kurangi Stok Kemasan Kosong (Pouch terpakai)
            $pouch->stok_pcs -= $hasilKemasanPcs;
            $pouch->save();

            // 5. Kurangi Stok Stiker (jika digunakan)
            if ($sticker) {
                $sticker->stok_pcs -= $hasilKemasanPcs;
                $sticker->save();
            }

            // 6. Tambah/Update Stok Kopi Siap Jual (Finished Goods)
            $finishedGood = FinishedGood::firstOrCreate(
                ['roasted_bean_id' => $roastedBean->id, 'packaging_id' => $pouch->id],
                ['stok_pcs' => 0]
            );
            $finishedGood->stok_pcs += $hasilKemasanPcs;
            $finishedGood->save();

            DB::commit();

            return response()->json([
                'message' => 'Proses pengemasan berhasil.',
                'data' => [
                    'stok_digunakan_gram' => $stokDigunakan,
                    'hasil_kemasan_pcs' => $hasilKemasanPcs,
                    'sisa_gram_dicatat' => $sisaGram,
                    'total_stok_roasted_bean_sekarang' => $roastedBean->total_stok,
                    'sisa_stok_tersimpan' => $roastedBean->sisa_stok,
                    'total_kemasan_sekarang' => $pouch->stok_pcs,
                    'total_stiker_sekarang' => $sticker ? $sticker->stok_pcs : null,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}
