<?php

namespace App\Http\Controllers;

use App\Models\RoastingBatch;
use App\Models\HppCalculation;
use App\Models\Biaya;
use App\Models\Packaging;
use App\Models\GreenBean;
use App\Models\PesananKeluar;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HppController extends Controller
{
    public function index()
    {
        // Hanya ambil batch yang completed dan BELUM PERNAH dihitung HPP-nya
        $batches = RoastingBatch::with(['greenBean', 'logPekerjas'])
            ->where('status_batch', 'completed')
            ->whereDoesntHave('hppCalculation')
            ->orderBy('tanggal_roasting', 'desc')
            ->get()
            ->map(function ($batch) {
                $gb = $batch->greenBean;
                return [
                    'id' => $batch->id,
                    'kode_batch' => $batch->kode_batch,
                    'green_bean_nama' => $gb ? $gb->nama_biji : 'Unknown',
                    'durasi_roasting' => $batch->durasi_roasting,
                    'durasi_pre_heating' => $batch->durasi_pre_heating,
                    'waktu_first_crack' => $batch->waktu_first_crack,
                    'waktu_development' => $batch->waktu_development,
                    'berat_mentah_gudang' => $batch->berat_mentah_gudang, // kg
                    'berat_matang_bersih' => $batch->berat_matang_bersih, // kg
                    'harga_bahan_baku' => $batch->biaya_bahan_baku,
                    'log_pekerja_total_upah' => $batch->logPekerjas->sum('total_upah'),
                    'pekerja_list' => $batch->logPekerjas->pluck('nama_pekerja')->unique()->values()->toArray()
                ];
            });
            
        // Ambil riwayat kalkulasi HPP yang sudah tersimpan
        $hppHistory = HppCalculation::with(['roastingBatch.greenBean', 'packaging'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($hpp) {
                $batch = $hpp->roastingBatch;
                $gb = $batch?->greenBean;
                $pkg = $hpp->packaging;

                // Cek apakah produk dari batch ini sudah pernah memiliki pesanan keluar
                $hasSales = false;
                if ($batch && $pkg) {
                    $hasSales = PesananKeluar::where('roasted_bean_id', $batch->roasted_bean_id)
                        ->where('packaging_id', $pkg->id)
                        ->exists();
                }

                return [
                    'id' => $hpp->id,
                    'roasting_batch_id' => $hpp->roasting_batch_id,
                    'kode_batch' => $batch ? $batch->kode_batch : 'N/A',
                    'created_at' => $hpp->created_at ? $hpp->created_at->toIso8601String() : now()->toIso8601String(),
                    'produk_nama' => ($gb ? $gb->nama_biji : 'Kopi') . ($pkg ? ' - ' . $pkg->nama_kemasan : ''),
                    'kemasan_nama' => $pkg ? $pkg->nama_kemasan : '-',
                    'hasil_kemasan_pcs' => $hpp->hasil_kemasan_pcs,
                    'total_hpp' => (float) $hpp->total_hpp,
                    'hpp_per_pcs' => $hpp->hasil_kemasan_pcs > 0 ? (float) ($hpp->total_hpp / $hpp->hasil_kemasan_pcs) : 0,
                    'harga_jual_final' => (float) $hpp->harga_jual_final,
                    'has_sales' => $hasSales,
                ];
            });

        $packagings = Packaging::all();

        // Ambil master biaya
        $biayaListrik = Biaya::where('nama', 'like', '%Listrik Mesin%')->orWhere('nama', 'like', '%Listrik%')->first() ?? (object)['harga' => 1500]; // per menit
        $biayaPenyusutan = Biaya::where('nama', 'like', '%Penyusutan%')->first() ?? (object)['harga' => 10000]; // per batch
        $biayaGaji = Biaya::where('kategori', 'Tenaga Kerja')->first() ?? (object)['harga' => 15000]; // per batch

        return view('hpp.index', compact('batches', 'hppHistory', 'packagings', 'biayaListrik', 'biayaPenyusutan', 'biayaGaji'));
    }

    public function calculatePreview(Request $request)
    {
        $batchData = json_decode($request->batch, true);
        $hasil_kemasan_pcs = $request->hasil_kemasan_pcs;
        $pouch_id = $request->pouch_id;
        $sticker_id = $request->sticker_id;

        $batch = RoastingBatch::findOrFail($batchData['id']);
        $pouch = $pouch_id ? Packaging::find($pouch_id) : null;
        $sticker = $sticker_id ? Packaging::find($sticker_id) : null;

        $hpp = $this->calculateHppData($batch, (int) $hasil_kemasan_pcs, $pouch, $sticker);
        $pekerjaList = \App\Models\LogPekerja::where('roasting_batch_id', $batch->id)
            ->pluck('nama_pekerja')->unique()->values()->toArray();
        $overheadList = Biaya::where('kategori', 'Overhead')
            ->where('nama', 'not like', '%Listrik%')
            ->pluck('nama')->toArray();

        return response()->json([
            'biaya_bahan_baku' => $hpp['biaya_bahan_baku'],
            'biaya_listrik' => $hpp['biaya_listrik'],
            'biaya_penyusutan' => $hpp['biaya_penyusutan'],
            'biaya_gaji' => $hpp['biaya_gaji'],
            'biaya_kemasan' => $hpp['biaya_kemasan'],
            'total_hpp' => $hpp['total_hpp'],
            'hpp_per_pcs' => $hpp['hpp_per_pcs'],
            'biaya_tenaga_kerja_batch' => $hpp['biaya_tenaga_kerja_batch'],
            'is_fallback_gaji' => $hpp['is_fallback_gaji'],
            'pekerja_list' => $pekerjaList,
            'biaya_overhead_custom_batch' => $hpp['biaya_overhead_custom_batch'],
            'overhead_list' => $overheadList,
            'max_output_pcs' => $hpp['max_output_pcs'],
            'share_percentage' => $hpp['share_percentage'],
        ]);
    }

    private function calculateHppData(
        RoastingBatch $batch,
        int $hasilKemasanPcs,
        ?Packaging $pouch,
        ?Packaging $sticker
    ): array {
        $biayaListrikTarif = Biaya::where('nama', 'like', '%Listrik Mesin%')
            ->orWhere('nama', 'like', '%Listrik%')
            ->value('harga') ?? 1500;
        $biayaOverheadLainnyaTarif = Biaya::where('kategori', 'Overhead')
            ->where('nama', 'not like', '%Listrik%')
            ->sum('harga');

        $durasiTotalKerja = $batch->durasi_pre_heating
            + $batch->waktu_first_crack
            + ($batch->waktu_development / 60);
        $biayaTetapPreHeating = 1000;
        $biayaListrikBatch = ($durasiTotalKerja / 60) * 0.75 * $biayaListrikTarif;

        $kapasitasPouchGram = $pouch?->kapasitas_gram ?? 0;
        $hargaPouch = $pouch?->harga_satuan ?? 0;
        $hargaStiker = $sticker?->harga_satuan ?? 0;
        $totalRoastedGram = $batch->berat_matang_bersih * 1000;
        $maxOutputPcs = $kapasitasPouchGram > 0
            ? (int) floor($totalRoastedGram / $kapasitasPouchGram)
            : 0;

        if ($maxOutputPcs > 0 && $hasilKemasanPcs > $maxOutputPcs) {
            throw ValidationException::withMessages([
                'hasil_kemasan_pcs' => ['Jumlah kemasan melebihi kapasitas kopi dalam batch.'],
            ]);
        }

        $share = 1.0;
        if ($totalRoastedGram > 0 && $kapasitasPouchGram > 0) {
            $share = min(1.0, ($hasilKemasanPcs * $kapasitasPouchGram) / $totalRoastedGram);
        }

        $biayaBahanBaku = $batch->biaya_bahan_baku * $share;
        $biayaListrik = ($biayaListrikBatch * $share) + $biayaTetapPreHeating;
        $biayaPenyusutan = $biayaOverheadLainnyaTarif * $share;
        $biayaTenagaKerjaBatch = \App\Models\LogPekerja::where('roasting_batch_id', $batch->id)
            ->sum('total_upah');
        $isFallbackGaji = $biayaTenagaKerjaBatch <= 0;
        if ($isFallbackGaji) {
            $biayaTenagaKerjaBatch = Biaya::where('kategori', 'Tenaga Kerja')->sum('harga') ?? 15000;
        }
        $biayaGaji = $biayaTenagaKerjaBatch * $share;
        $biayaKemasan = ($hargaPouch + $hargaStiker) * $hasilKemasanPcs;
        $totalHpp = $biayaBahanBaku + $biayaListrik + $biayaPenyusutan + $biayaGaji + $biayaKemasan;

        return [
            'biaya_bahan_baku' => $biayaBahanBaku,
            'biaya_listrik' => $biayaListrik,
            'biaya_penyusutan' => $biayaPenyusutan,
            'biaya_gaji' => $biayaGaji,
            'biaya_kemasan' => $biayaKemasan,
            'total_hpp' => $totalHpp,
            'hpp_per_pcs' => $hasilKemasanPcs > 0 ? $totalHpp / $hasilKemasanPcs : 0,
            'biaya_tenaga_kerja_batch' => $biayaTenagaKerjaBatch,
            'is_fallback_gaji' => $isFallbackGaji,
            'biaya_overhead_custom_batch' => $biayaOverheadLainnyaTarif,
            'max_output_pcs' => $maxOutputPcs,
            'share_percentage' => round($share * 100, 2),
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'roasting_batch_id' => 'required|exists:roasting_batch,id',
            'hasil_kemasan_pcs' => 'required|integer|min:1',
            'harga_jual_final' => 'required|numeric',
            'packaging_id' => 'required|exists:packagings,id',
            'sticker_id' => 'nullable|exists:packagings,id',
        ]);

        $batch = RoastingBatch::findOrFail($validated['roasting_batch_id']);
        $pouch = Packaging::findOrFail($validated['packaging_id']);
        $sticker = isset($validated['sticker_id'])
            ? Packaging::findOrFail($validated['sticker_id'])
            : null;
        $hpp = $this->calculateHppData(
            $batch,
            (int) $validated['hasil_kemasan_pcs'],
            $pouch,
            $sticker
        );

        if ($validated['harga_jual_final'] < $hpp['hpp_per_pcs']) {
            throw ValidationException::withMessages([
                'harga_jual_final' => ['Harga jual tidak boleh di bawah HPP per pcs.'],
            ]);
        }

        // Prevent duplicate calculation for the SAME batch and SAME packaging
        if (HppCalculation::where('roasting_batch_id', $validated['roasting_batch_id'])
            ->where('packaging_id', $validated['packaging_id'])->exists()) {
            return back()->with('error', 'Batch ini sudah dihitung HPP-nya untuk ukuran kemasan tersebut.');
        }

        HppCalculation::create([
            'roasting_batch_id' => $validated['roasting_batch_id'],
            'hasil_kemasan_pcs' => $validated['hasil_kemasan_pcs'],
            'biaya_bahan_baku' => $hpp['biaya_bahan_baku'],
            'biaya_listrik' => $hpp['biaya_listrik'],
            'biaya_penyusutan' => $hpp['biaya_penyusutan'],
            'biaya_gaji' => $hpp['biaya_gaji'],
            'biaya_kemasan' => $hpp['biaya_kemasan'],
            'total_hpp' => $hpp['total_hpp'],
            'harga_jual_final' => $validated['harga_jual_final'],
            'packaging_id' => $validated['packaging_id'],
        ]);

        return redirect()->route('hpp.index')->with('success', 'Laporan HPP berhasil disimpan.');
    }
}
