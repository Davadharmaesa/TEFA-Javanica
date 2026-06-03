<?php

namespace App\Http\Controllers;

use App\Models\RoastingBatch;
use App\Models\HppCalculation;
use App\Models\Biaya;
use App\Models\Packaging;
use App\Models\GreenBean;
use Illuminate\Http\Request;

class HppController extends Controller
{
    public function index()
    {
        $batches = RoastingBatch::with(['greenBean', 'hppCalculation', 'roastedBean', 'logPekerjas'])
            ->orderBy('created_at', 'desc')
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
                    'harga_bahan_baku' => $batch->biaya_bahan_baku, // Total harga bahan baku dari DB (FIFO)
                    'sudah_dihitung' => $batch->hppCalculation ? true : false,
                    'hpp' => $batch->hppCalculation,
                    'log_pekerja_total_upah' => $batch->logPekerjas->sum('total_upah'),
                    'pekerja_list' => $batch->logPekerjas->pluck('nama_pekerja')->unique()->values()->toArray()
                ];
            });
            
        $packagings = Packaging::all();

        // Ambil master biaya
        $biayaListrik = Biaya::where('nama', 'like', '%Listrik Mesin%')->orWhere('nama', 'like', '%Listrik%')->first() ?? (object)['harga' => 1500]; // per menit
        $biayaPenyusutan = Biaya::where('nama', 'like', '%Penyusutan%')->first() ?? (object)['harga' => 10000]; // per batch
        $biayaGaji = Biaya::where('kategori', 'Tenaga Kerja')->first() ?? (object)['harga' => 15000]; // per batch

        return view('hpp.index', compact('batches', 'packagings', 'biayaListrik', 'biayaPenyusutan', 'biayaGaji'));
    }

    public function calculatePreview(Request $request)
    {
        $batchData = json_decode($request->batch, true);
        $hasil_kemasan_pcs = $request->hasil_kemasan_pcs;
        $pouch_id = $request->pouch_id;
        $sticker_id = $request->sticker_id;

        $batch = RoastingBatch::findOrFail($batchData['id']);

        $biayaListrikTarif = Biaya::where('nama', 'like', '%Listrik Mesin%')
            ->orWhere('nama', 'like', '%Listrik%')
            ->first()->harga ?? 1500;
            
        // Overhead Lainnya: Semua Overhead selain Listrik
        $biayaOverheadLainnyaTarif = Biaya::where('kategori', 'Overhead')
            ->where('nama', 'not like', '%Listrik%')
            ->sum('harga');

        // Durasi Total Kerja Mesin = Masa Pre-heating + Menit First Crack + Detik Dev Tahan / 60
        $durasiTotalKerja = $batch->durasi_pre_heating + $batch->waktu_first_crack + ($batch->waktu_development / 60);
        // Hitung secara dinamis berdasarkan durasi (jam): tarif * (menit / 60)
        $biayaListrikBatch = ($durasiTotalKerja / 60) * $biayaListrikTarif;
        
        $pouch = $pouch_id ? Packaging::find($pouch_id) : null;
        $sticker = $sticker_id ? Packaging::find($sticker_id) : null;

        $pouch_capacity_gram = $pouch ? $pouch->kapasitas_gram : 0;
        $pouch_hpp = $pouch ? $pouch->harga_satuan : 0;
        $sticker_hpp = $sticker ? $sticker->harga_satuan : 0;

        $totalRoastedGram = $batch->berat_matang_bersih * 1000; // in grams
        
        // Hitung batas maksimum pcs hasil kemasan secara fisik & stok
        $maxPcsByCoffee = $pouch_capacity_gram > 0 ? floor($totalRoastedGram / $pouch_capacity_gram) : 0;
        $maxPcsByPouch = $pouch ? $pouch->stok_pcs : 999999;
        $maxPcsBySticker = $sticker ? $sticker->stok_pcs : 999999;
        $maxOutputPcs = min($maxPcsByCoffee, $maxPcsByPouch, $maxPcsBySticker);

        $share = 1.0;
        if ($totalRoastedGram > 0 && $pouch_capacity_gram > 0) {
            $share = min(1.0, ($hasil_kemasan_pcs * $pouch_capacity_gram) / $totalRoastedGram);
        }

        // Allocate costs based on share of batch packaged
        $biayaBahanBaku = $batch->biaya_bahan_baku * $share;
        $biayaListrik = $biayaListrikBatch * $share;
        $biayaPenyusutan = $biayaOverheadLainnyaTarif * $share;
        
        // Tenaga Kerja: Hitung dari log_pekerja jika ada, jika tidak ada fallback ke master Tenaga Kerja sum
        $biayaTenagaKerjaBatch = \App\Models\LogPekerja::where('roasting_batch_id', $batch->id)->sum('total_upah');
        $is_fallback_gaji = false;
        if ($biayaTenagaKerjaBatch <= 0) {
            $is_fallback_gaji = true;
            $biayaTenagaKerjaBatch = Biaya::where('kategori', 'Tenaga Kerja')->sum('harga') ?? 15000;
        }
        $biayaGaji = $biayaTenagaKerjaBatch * $share;
        
        // Kemasan: Pouch + Stiker
        $biayaKemasan = ($pouch_hpp + $sticker_hpp) * $hasil_kemasan_pcs;
        
        $totalHpp = $biayaBahanBaku + $biayaListrik + $biayaPenyusutan + $biayaGaji + $biayaKemasan;

        $pekerjaList = \App\Models\LogPekerja::where('roasting_batch_id', $batch->id)->pluck('nama_pekerja')->unique()->values()->toArray();
        
        $overheadList = Biaya::where('kategori', 'Overhead')
            ->where('nama', 'not like', '%Listrik%')
            ->pluck('nama')
            ->toArray();

        return response()->json([
            'biaya_bahan_baku' => $biayaBahanBaku,
            'biaya_listrik' => $biayaListrik,
            'biaya_penyusutan' => $biayaPenyusutan,
            'biaya_gaji' => $biayaGaji,
            'biaya_kemasan' => $biayaKemasan,
            'total_hpp' => $totalHpp,
            'hpp_per_pcs' => $hasil_kemasan_pcs > 0 ? $totalHpp / $hasil_kemasan_pcs : 0,
            'biaya_tenaga_kerja_batch' => $biayaTenagaKerjaBatch,
            'is_fallback_gaji' => $is_fallback_gaji,
            'pekerja_list' => $pekerjaList,
            'biaya_overhead_custom_batch' => $biayaOverheadLainnyaTarif,
            'overhead_list' => $overheadList,
            'max_output_pcs' => $maxOutputPcs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'roasting_batch_id' => 'required|exists:roasting_batch,id',
            'hasil_kemasan_pcs' => 'required|numeric|min:1',
            'biaya_bahan_baku' => 'required|numeric',
            'biaya_listrik' => 'required|numeric',
            'biaya_penyusutan' => 'required|numeric',
            'biaya_gaji' => 'required|numeric',
            'biaya_kemasan' => 'required|numeric',
            'total_hpp' => 'required|numeric',
        ]);

        HppCalculation::updateOrCreate(
            ['roasting_batch_id' => $request->roasting_batch_id],
            [
                'biaya_bahan_baku' => $request->biaya_bahan_baku,
                'biaya_listrik' => $request->biaya_listrik,
                'biaya_penyusutan' => $request->biaya_penyusutan,
                'biaya_gaji' => $request->biaya_gaji,
                'biaya_kemasan' => $request->biaya_kemasan,
                'hasil_kemasan_pcs' => $request->hasil_kemasan_pcs,
                'total_hpp' => $request->total_hpp,
            ]
        );

        return redirect()->route('hpp.index')->with('success', 'Laporan HPP berhasil disimpan.');
    }
}
