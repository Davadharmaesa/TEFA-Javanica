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
        $batches = RoastingBatch::with(['greenBean', 'hppCalculation', 'roastedBean'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($batch) {
                // Harga bahan baku per gram = harga beli paket / stok saat beli (asumsi stok_gram + berat yang sudah dipakai)
                // Karena kita belum melacak stok awal, kita asumsikan harga_beli adalah harga per Kg.
                // Jika tidak, kita bisa ambil harga_beli saja.
                $gb = $batch->greenBean;
                $hargaPerKg = $gb ? ($gb->harga_beli ?? 100000) : 100000; 
                $hargaPerGram = $hargaPerKg / 1000;
                
                return [
                    'id' => $batch->id,
                    'kode_batch' => $batch->kode_batch,
                    'green_bean_nama' => $gb ? $gb->nama_biji : 'Unknown',
                    'durasi_roasting' => $batch->durasi_roasting,
                    'berat_mentah_gudang' => $batch->berat_mentah_gudang, // kg
                    'harga_bahan_baku' => $batch->berat_mentah_gudang * 1000 * $hargaPerGram, // Total harga bahan baku
                    'sudah_dihitung' => $batch->hppCalculation ? true : false,
                    'hpp' => $batch->hppCalculation
                ];
            });
            
        $packagings = Packaging::all();

        // Ambil master biaya
        $biayaListrik = Biaya::where('nama', 'like', '%Listrik%')->first() ?? (object)['harga' => 1500]; // per kWh
        $biayaPenyusutan = Biaya::where('nama', 'like', '%Penyusutan%')->first() ?? (object)['harga' => 10000]; // per batch
        $biayaGaji = Biaya::where('nama', 'like', '%Gaji%')->orWhere('nama', 'like', '%Operator%')->first() ?? (object)['harga' => 500]; // per pcs

        return view('hpp.index', compact('batches', 'packagings', 'biayaListrik', 'biayaPenyusutan', 'biayaGaji'));
    }

    public function calculatePreview(Request $request)
    {
        $batch = collect(json_decode($request->batch));
        $hasil_kemasan_pcs = $request->hasil_kemasan_pcs;
        $daya_mesin_kw = $request->daya_mesin_kw;
        $packaging_hpp = $request->packaging_hpp;

        $biayaListrikTarif = Biaya::where('nama', 'like', '%Listrik%')->first()->harga ?? 1500;
        $biayaPenyusutanTarif = Biaya::where('nama', 'like', '%Penyusutan%')->first()->harga ?? 10000;
        $biayaGajiTarif = Biaya::where('nama', 'like', '%Gaji%')->orWhere('nama', 'like', '%Operator%')->first()->harga ?? 500;

        $durasiJam = $batch['durasi_roasting'] / 60;
        $biayaListrik = $durasiJam * $daya_mesin_kw * $biayaListrikTarif;
        
        $biayaGaji = $biayaGajiTarif * $hasil_kemasan_pcs;
        $biayaKemasan = $packaging_hpp * $hasil_kemasan_pcs;
        $biayaBahanBaku = $batch['harga_bahan_baku'];
        
        $totalHpp = $biayaBahanBaku + $biayaListrik + $biayaPenyusutanTarif + $biayaGaji + $biayaKemasan;

        return response()->json([
            'biaya_bahan_baku' => $biayaBahanBaku,
            'biaya_listrik' => $biayaListrik,
            'biaya_penyusutan' => $biayaPenyusutanTarif,
            'biaya_gaji' => $biayaGaji,
            'biaya_kemasan' => $biayaKemasan,
            'total_hpp' => $totalHpp,
            'hpp_per_pcs' => $hasil_kemasan_pcs > 0 ? $totalHpp / $hasil_kemasan_pcs : 0
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
