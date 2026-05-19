<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoastedBean;
use App\Models\Packaging;
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
            'jumlah_stok_digunakan_gram' => 'required|numeric|min:1',
            'ukuran_kemasan_gram' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $roastedBean = RoastedBean::findOrFail($validated['roasted_bean_id']);
            $packaging = Packaging::findOrFail($validated['packaging_id']);

            $stokDigunakan = $validated['jumlah_stok_digunakan_gram'];
            $ukuranKemasan = $validated['ukuran_kemasan_gram'];

            // Validasi apakah stok cukup
            if ($roastedBean->total_stok < $stokDigunakan) {
                return response()->json(['message' => 'Stok Roasted Bean tidak mencukupi.'], 400);
            }

            // 1. Hitung Kemasan & Sisa
            // Contoh: 2500 gram dibagi 200 gram -> hasil 12 pouch, sisa 100 gram
            $hasilKemasanPcs = floor($stokDigunakan / $ukuranKemasan);
            $sisaGram = fmod($stokDigunakan, $ukuranKemasan); // sisa stok (residu)

            // 2. Update Stok Roasted Bean
            // Kurangi stok utama hanya sebesar yang sukses dikemas ke dalam pouch (tidak termasuk sisa residu)
            // Sehingga sisa residu secara otomatis tetap berada di total_stok untuk dapat dikemas di proses berikutnya
            $stokTerkemas = $hasilKemasanPcs * $ukuranKemasan;
            $roastedBean->total_stok -= $stokTerkemas;

            // Perbarui sisa_stok sesuai dengan sisa residu ril yang dihasilkan dari proses terbaru ini
            $roastedBean->sisa_stok = $sisaGram;
            $roastedBean->save();

            // 3. Tambah Stok Kemasan (Pouch / Stiker)
            $packaging->stok_pcs += $hasilKemasanPcs;
            $packaging->save();

            // Opsional: Jika Anda ingin menyimpan log transaksi pengemasan ke tabel baru
            // PackagingProcess::create([...]);

            DB::commit();

            return response()->json([
                'message' => 'Proses pengemasan berhasil.',
                'data' => [
                    'stok_digunakan_gram' => $stokDigunakan,
                    'hasil_kemasan_pcs' => $hasilKemasanPcs,
                    'sisa_gram_dicatat' => $sisaGram,
                    'total_stok_roasted_bean_sekarang' => $roastedBean->total_stok,
                    'sisa_stok_tersimpan' => $roastedBean->sisa_stok,
                    'total_kemasan_sekarang' => $packaging->stok_pcs
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
