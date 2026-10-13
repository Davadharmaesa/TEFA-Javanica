<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GreenBean;
use App\Models\RoastedBean;
use App\Models\RoastingBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RoastingApiController extends Controller
{
    /**
     * Get all green beans for dropdown
     */
    public function getGreenBeans()
    {
        // Hanya ambil master Green Bean yang punya stok aktif di gudang
        $beans = GreenBean::whereHas('stocks', function ($query) {
            $query->where('stok_gram', '>', 0);
        })
        ->withSum('stocks as stok_gram', 'stok_gram')
        ->get();
        return response()->json($beans);
    }

    public function getRoastedBeans()
    {
        $beans = RoastedBean::all();
        return response()->json($beans);
    }

    /**
     * Get recent roasting batches for history log
     */
    public function getBatches()
    {
        $batches = RoastingBatch::with(['greenBean', 'roastedBean'])->orderBy('id', 'desc')->take(10)->get();
        return response()->json($batches);
    }

    public function getStats()
    {
        $total_kopi_matang = RoastingBatch::sum('berat_matang_bersih');
        
        $total_raw = RoastingBatch::sum('berat_mentah_gudang');
        $total_cooked = RoastingBatch::sum('berat_matang_bersih');
        $avg_burnoff = $total_raw > 0 ? round((($total_raw - $total_cooked) / $total_raw) * 100, 1) : 0;

        $total_batch = RoastingBatch::count();

        return response()->json([
            'target_tercapai' => $total_kopi_matang,
            'rata_susut' => $avg_burnoff,
            'total_batch' => $total_batch
        ]);
    }

    /**
     * Store new batch and calculate stock logic
     */
    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'green_bean_id' => 'required|exists:green_bean,id',
            'roasted_bean_id' => 'required|exists:roasted_bean,id',
            'kode_batch' => 'required|string|unique:roasting_batch,kode_batch',
            'profil_sangrai' => 'required|string',
            'waktu_first_crack' => 'required|numeric|min:0.1',
            'waktu_development' => 'required|integer|min:0',
            'durasi_pre_heating' => 'required|numeric|min:0',
            'suhu' => 'required|integer|min:0',
            'berat_mentah_gudang' => 'required|numeric|min:0.1',
            'berat_mentah_sortasi' => 'required|numeric|min:0.1',
            'berat_matang_kotor' => 'required|numeric|min:0.1',
            'berat_matang_bersih' => 'required|numeric|min:0.1',
        ]);

        try {
            DB::beginTransaction();

            $greenBean = GreenBean::findOrFail($validated['green_bean_id']);

            // 1. Validasi Stok Mentah (konversi Kg -> Gram) dari seluruh batch
            $beratMentahGram = $validated['berat_mentah_gudang'] * 1000;
            $totalAvailable = \App\Models\GreenBeanStock::where('green_bean_id', $greenBean->id)->sum('stok_gram');
            if ($totalAvailable < $beratMentahGram) {
                return response()->json(['message' => 'Stok Green Bean di gudang tidak mencukupi.'], 400);
            }

            // 2. Kurangi Stok Mentah menggunakan FIFO & Hitung Biaya Bahan Baku
            $stocks = \App\Models\GreenBeanStock::where('green_bean_id', $greenBean->id)
                ->where('stok_gram', '>', 0)
                ->orderBy('tanggal_beli', 'asc')
                ->orderBy('id', 'asc')
                ->get();



            $biayaBahanBaku = 0;
            $remainingToDeduct = $beratMentahGram;
            foreach ($stocks as $stock) {
                if ($remainingToDeduct <= 0) break;
                
                $hargaPerGram = $stock->harga_beli / 1000;
                
                if ($stock->stok_gram >= $remainingToDeduct) {
                    $biayaBahanBaku += $remainingToDeduct * $hargaPerGram;
                    $stock->stok_gram -= $remainingToDeduct;
                    $stock->save();
                    $remainingToDeduct = 0;
                } else {
                    $biayaBahanBaku += $stock->stok_gram * $hargaPerGram;
                    $remainingToDeduct -= $stock->stok_gram;
                    $stock->stok_gram = 0;
                    $stock->save();
                }
            }

            // Trigger WA low stock notification (non-blocking / error handled)
            try {
                $waService = new \App\Services\WhatsappService();
                $waService->checkAndNotifyLowStock($greenBean->id);
            } catch (\Exception $ex) {
                \Log::warning("Failed to run low stock WA check: " . $ex->getMessage());
            }

            // 3. Ambil Roasted Bean
            $roastedBean = RoastedBean::findOrFail($validated['roasted_bean_id']);

            // 4. Tambah Stok Matang (konversi Kg -> Gram) dan Update Tanggal
            $beratMatangGram = $validated['berat_matang_bersih'] * 1000;
            $roastedBean->total_stok += $beratMatangGram;
            $roastedBean->tanggal_roasting = now()->toDateString();
            $roastedBean->tanggal_expire = now()->addDays(30)->toDateString();
            $roastedBean->save();

            // 5. Hitung Persentase Susut Total (Bahan)
            $beratKgInSortasi = $validated['berat_mentah_sortasi'];
            $beratKgOutBersih = $validated['berat_matang_bersih'];
            $susut = (($beratKgInSortasi - $beratKgOutBersih) / $beratKgInSortasi) * 100;
            if ($susut < 0) $susut = 0;

            // 6. Hitung Akumulasi Biaya Mesin (Opsional jika ingin dilacak terpisah, namun biaya_bahan_baku hanya diisi harga biji kopi mentah)
            $durasiRoasting = $validated['waktu_first_crack'] + ($validated['waktu_development'] / 60);
            $durasiTotalKerja = $durasiRoasting + $validated['durasi_pre_heating'];

            // Ambil tarif mesin per jam dari database (Listrik Mesin), default Rp 1.500 jika tidak ditemukan
            $biayaMesinRecord = \App\Models\Biaya::where('nama', 'Listrik Mesin')
                ->orWhere('nama', 'like', '%mesin%')
                ->first();
            $tarifMesinPerJam = $biayaMesinRecord ? $biayaMesinRecord->harga : 1500;
            // Hitung secara dinamis berdasarkan durasi (jam): tarif * (menit / 60)
            $biayaMesinTotal = ($durasiTotalKerja / 60) * $tarifMesinPerJam;

            // 7. Simpan Log Roasting (Audit Trail)
            $batch = RoastingBatch::create([
                'kode_batch' => $validated['kode_batch'],
                'user_id' => Auth::id() ?? 1, // Fallback to 1 if testing without auth
                'green_bean_id' => $greenBean->id,
                'roasted_bean_id' => $roastedBean->id,
                'profil_sangrai' => $validated['profil_sangrai'],
                'durasi_roasting' => round($durasiRoasting, 2),
                'waktu_first_crack' => $validated['waktu_first_crack'],
                'waktu_development' => $validated['waktu_development'],
                'durasi_pre_heating' => $validated['durasi_pre_heating'],
                'suhu' => $validated['suhu'],
                'berat_mentah_gudang' => $validated['berat_mentah_gudang'],
                'berat_mentah_sortasi' => $validated['berat_mentah_sortasi'],
                'berat_matang_kotor' => $validated['berat_matang_kotor'],
                'berat_matang_bersih' => $validated['berat_matang_bersih'],
                'sisa_stok' => $beratMatangGram,
                'persentase_susut' => round($susut, 2),
                'tanggal_roasting' => date('Y-m-d'),
                'status_batch' => 'completed',
                'biaya_bahan_baku' => round($biayaBahanBaku)
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Batch berhasil disimpan dan otomatis mengkalkulasi stok Gudang.',
                'batch' => $batch
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}
