<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoastedBean;
use App\Models\Packaging;
use App\Models\FinishedGood;
use App\Models\RoastingBatch;
use App\Models\RoastedBeanStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

            // Validasi berat kopi yang digunakan minimal 1 kemasan
            if ($stokDigunakan < $ukuranKemasan) {
                return response()->json(['message' => 'Berat kopi yang digunakan tidak mencukupi untuk membuat minimal 1 kemasan.'], 400);
            }

            // Validasi apakah stok roasted bean cukup
            $batchStockSum = RoastingBatch::where('roasted_bean_id', $roastedBean->id)
                ->where('status_batch', 'completed')
                ->sum('sisa_stok');
            $availableStock = max((float) $roastedBean->total_stok, (float) $batchStockSum);

            if ($availableStock < $stokDigunakan) {
                return response()->json(['message' => 'Stok Roasted Bean tidak mencukupi.'], 400);
            }

            // Hitung kebutuhan kemasan pcs berdasarkan berat kopi yang digunakan
            $requiredPcs = floor($stokDigunakan / $ukuranKemasan);

            // Validasi ketersediaan stok material pouch secara ril
            if ($pouch->stok_pcs < $requiredPcs) {
                return response()->json(['message' => "Stok kemasan kosong (Pouch) tidak mencukupi untuk jumlah kopi yang dikemas. Dibutuhkan minimal {$requiredPcs} pcs kemasan (Tersedia: {$pouch->stok_pcs} pcs)."], 400);
            }

            // Validasi ketersediaan stok material stiker secara ril
            if ($sticker && $sticker->stok_pcs < $requiredPcs) {
                return response()->json(['message' => "Stok stiker tidak mencukupi untuk jumlah kopi yang dikemas. Dibutuhkan minimal {$requiredPcs} pcs stiker (Tersedia: {$sticker->stok_pcs} pcs)."], 400);
            }

            // Output pcs yang dikemas wajib sama dengan kebutuhan riil
            $hasilKemasanPcs = $requiredPcs;

            // 3. Update Stok Roasted Bean & RoastingBatch (FIFO)
            $stokTerkemas = $hasilKemasanPcs * $ukuranKemasan;

            // Kurangi stok riil dari RoastingBatch menggunakan FIFO
            $batches = RoastingBatch::where('roasted_bean_id', $roastedBean->id)
                ->where('status_batch', 'completed')
                ->where(function($q) {
                    $q->where('sisa_stok', '>', 0)
                      ->orWhere(function($sub) {
                          $sub->whereNull('sisa_stok')
                              ->where('berat_matang_bersih', '>', 0);
                      });
                })
                ->orderBy('tanggal_roasting', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $remainingToDeduct = $stokTerkemas;
            foreach ($batches as $batch) {
                if ($remainingToDeduct <= 0) break;

                $batchStock = $batch->sisa_stok !== null ? (int) $batch->sisa_stok : (int) ($batch->berat_matang_bersih * 1000);

                if ($batchStock >= $remainingToDeduct) {
                    $newBatchStock = $batchStock - $remainingToDeduct;
                    $batch->sisa_stok = $newBatchStock;
                    $batch->berat_matang_bersih = round($newBatchStock / 1000, 2);
                    $batch->save();
                    $remainingToDeduct = 0;
                } else {
                    $remainingToDeduct -= $batchStock;
                    $batch->sisa_stok = 0;
                    $batch->berat_matang_bersih = 0;
                    $batch->save();
                }
            }

            // Kurangi juga dari RoastedBeanStock jika tabel ada (FIFO)
            if (Schema::hasTable('roasted_bean_stock')) {
                $stocks = RoastedBeanStock::where('roasted_bean_id', $roastedBean->id)
                    ->where('stok_gram', '>', 0)
                    ->orderBy('tanggal_roasting', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                $remainingToDeductStocks = $stokTerkemas;
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
            }

            // Sinkronkan total_stok master RoastedBean dari sisa seluruh batch aktif
            $totalBatchSisa = RoastingBatch::where('roasted_bean_id', $roastedBean->id)
                ->where('status_batch', 'completed')
                ->sum('sisa_stok');

            $roastedBean->total_stok = $totalBatchSisa;
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

    public function clearResidue(Request $request)
    {
        $request->validate([
            'batch_id' => 'nullable|exists:roasting_batch,id',
            'roasted_bean_id' => 'nullable'
        ]);

        try {
            DB::beginTransaction();

            $clearedGram = 0;

            if ($request->filled('batch_id')) {
                $batch = RoastingBatch::findOrFail($request->batch_id);
                $clearedGram = $batch->sisa_stok;
                $batch->sisa_stok = 0;
                $batch->berat_matang_bersih = 0;
                $batch->save();

                if ($batch->roasted_bean_id) {
                    $roastedBean = RoastedBean::find($batch->roasted_bean_id);
                    if ($roastedBean) {
                        $roastedBean->total_stok = RoastingBatch::where('roasted_bean_id', $batch->roasted_bean_id)
                            ->where('status_batch', 'completed')
                            ->sum('sisa_stok');
                        $roastedBean->sisa_stok = 0;
                        $roastedBean->save();
                    }
                }
            } elseif ($request->filled('roasted_bean_id')) {
                $batch = RoastingBatch::find($request->roasted_bean_id);
                if ($batch) {
                    $clearedGram = $batch->sisa_stok;
                    $batch->sisa_stok = 0;
                    $batch->berat_matang_bersih = 0;
                    $batch->save();

                    $roastedBean = RoastedBean::find($batch->roasted_bean_id);
                    if ($roastedBean) {
                        $roastedBean->total_stok = RoastingBatch::where('roasted_bean_id', $batch->roasted_bean_id)
                            ->where('status_batch', 'completed')
                            ->sum('sisa_stok');
                        $roastedBean->sisa_stok = 0;
                        $roastedBean->save();
                    }
                } else {
                    $roastedBean = RoastedBean::find($request->roasted_bean_id);
                    if ($roastedBean) {
                        $clearedGram = $roastedBean->sisa_stok;
                        $roastedBean->sisa_stok = 0;
                        $roastedBean->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => "Sisa residu sebanyak {$clearedGram}g berhasil dibersihkan (Penyusutan).",
                'data' => [
                    'cleared_gram' => $clearedGram
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
