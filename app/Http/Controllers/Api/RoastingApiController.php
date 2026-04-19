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
        // Hanya ambil yang stoknya > 0
        $beans = GreenBean::where('stok_gram', '>', 0)->get();
        return response()->json($beans);
    }

    /**
     * Get recent roasting batches for history log
     */
    public function getBatches()
    {
        $batches = RoastingBatch::with('greenBean')->orderBy('id', 'desc')->take(20)->get();
        return response()->json($batches);
    }

    /**
     * Store new batch and calculate stock logic
     */
    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'green_bean_id' => 'required|exists:green_bean,id',
            'kode_batch' => 'required|string|unique:roasting_batch,kode_batch',
            'profil_sangrai' => 'required|string',
            'bentuk_akhir' => 'required|string',
            'berat_mentah_gram' => 'required|numeric|min:1',
            'berat_siap_jual_gram' => 'required|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();

            $greenBean = GreenBean::findOrFail($validated['green_bean_id']);

            // 1. Validasi Stok Mentah
            if ($greenBean->stok_gram < $validated['berat_mentah_gram']) {
                return response()->json(['message' => 'Stok Green Bean di gudang tidak mencukupi.'], 400);
            }

            // 2. Kurangi Stok Mentah
            $greenBean->stok_gram -= $validated['berat_mentah_gram'];
            $greenBean->save();

            // 3. Tentukan Nama Roasted Bean dari GB Name (Contoh: "GB Arabika Belawan -> Arabika Belawan")
            $namaRoasted = str_replace('GB ', '', $greenBean->nama_biji);

            // 4. Cari Produk Matang (Berdasarkan Nama, Profil, dan Bentuk), Jika tidak ada, Create!
            $roastedBean = RoastedBean::firstOrCreate(
                [
                    'nama_kopi' => $namaRoasted,
                    'level_roast' => $validated['profil_sangrai'],
                    'bentuk_akhir' => $validated['bentuk_akhir'],
                ],
                [
                    'total_stok' => 0,
                    'satuan_dasar' => 'gram'
                ]
            );

            // 5. Tambah Stok Matang
            $roastedBean->total_stok += $validated['berat_siap_jual_gram'];
            $roastedBean->save();

            // 6. Hitung Persentase Susut (Burnoff)
            $beratKgIn = $validated['berat_mentah_gram'] / 1000;
            $beratKgOut = $validated['berat_siap_jual_gram'] / 1000;
            $susut = (($beratKgIn - $beratKgOut) / $beratKgIn) * 100;
            if ($susut < 0) $susut = 0;

            // 7. Simpan Log Roasting (Audit Trail)
            $batch = RoastingBatch::create([
                'kode_batch' => $validated['kode_batch'],
                'user_id' => Auth::id() ?? 1, // Fallback to 1 if testing without auth
                'green_bean_id' => $greenBean->id,
                'roasted_bean_id' => $roastedBean->id,
                'profil_sangrai' => $validated['profil_sangrai'],
                'bentuk_akhir' => $validated['bentuk_akhir'],
                'berat_mentah' => $beratKgIn,
                'berat_matang' => $beratKgOut,
                'persentase_susut' => round($susut, 2),
                'tanggal_roasting' => date('Y-m-d'),
                'status_batch' => 'completed'
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
