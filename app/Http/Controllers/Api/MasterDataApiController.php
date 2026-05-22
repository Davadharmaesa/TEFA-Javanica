<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GreenBean;
use App\Models\RoastedBean;
use App\Models\Biaya;
use App\Models\Packaging;

class MasterDataApiController extends Controller
{
    // --- PACKAGING / KEMASAN ---
    public function getPackagings()
    {
        return response()->json(Packaging::all());
    }

    public function storePackaging(Request $request)
    {
        $validated = $request->validate([
            'tipe' => 'required|string|max:255',
            'nama_kemasan' => 'required|string|max:255',
            'kapasitas_gram' => 'required|integer|min:0',
            'harga_satuan' => 'required|integer|min:0',
        ]);
        // Stok default 0 untuk master data baru
        $validated['stok_pcs'] = 0;

        $packaging = Packaging::create($validated);
        return response()->json(['message' => 'Kemasan berhasil ditambahkan', 'data' => $packaging]);
    }

    public function updatePackaging(Request $request, $id)
    {
        $validated = $request->validate([
            'tipe' => 'required|string|max:255',
            'nama_kemasan' => 'required|string|max:255',
            'kapasitas_gram' => 'required|integer|min:0',
            'harga_satuan' => 'required|integer|min:0',
        ]);
        $packaging = Packaging::findOrFail($id);
        $packaging->update($validated);
        return response()->json(['message' => 'Kemasan berhasil diperbarui', 'data' => $packaging]);
    }

    public function destroyPackaging($id)
    {
        try {
            $packaging = Packaging::findOrFail($id);
            $packaging->delete();
            return response()->json(['message' => 'Kemasan berhasil dihapus']);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['message' => 'Kemasan tidak dapat dihapus karena sudah digunakan dalam proses pengemasan!'], 400);
        }
    }

    // --- BIAYA ---
    public function getBiaya()
    {
        return response()->json(Biaya::all());
    }

    public function storeBiaya(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'satuan' => 'required|string|max:255',
            'harga' => 'required|integer|min:0',
        ]);
        $biaya = Biaya::create($validated);
        return response()->json(['message' => 'Biaya berhasil ditambahkan', 'data' => $biaya]);
    }

    public function updateBiaya(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'satuan' => 'required|string|max:255',
            'harga' => 'required|integer|min:0',
        ]);
        $biaya = Biaya::findOrFail($id);
        $biaya->update($validated);
        return response()->json(['message' => 'Biaya berhasil diperbarui', 'data' => $biaya]);
    }

    public function destroyBiaya($id)
    {
        try {
            $biaya = Biaya::findOrFail($id);
            $biaya->delete();
            return response()->json(['message' => 'Biaya berhasil dihapus']);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['message' => 'Komponen Biaya tidak dapat dihapus karena sudah terhubung dengan data keuangan atau kalkulasi HPP!'], 400);
        }
    }

    // --- GREEN BEAN ---
    public function getGreenBeans()
    {
        $beans = GreenBean::withSum('stocks as stok_gram', 'stok_gram')->get()->map(function ($item) {
            $item->stok_gram = $item->stok_gram ?? 0;
            return $item;
        });
        return response()->json($beans);
    }

    public function storeGreenBean(Request $request)
    {
        $validated = $request->validate([
            'nama_biji' => 'required|string|max:255',
            'asal_kopi' => 'nullable|string|max:255',
        ]);

        $bean = GreenBean::create([
            'nama_biji' => $validated['nama_biji'],
            'asal_kopi' => $validated['asal_kopi'],
        ]);

        $bean->stok_gram = 0; // for JSON response compatibility

        return response()->json(['message' => 'Data Green Bean berhasil ditambahkan', 'data' => $bean]);
    }

    public function updateGreenBean(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_biji' => 'required|string|max:255',
            'asal_kopi' => 'nullable|string|max:255',
        ]);

        $bean = GreenBean::findOrFail($id);
        $bean->update([
            'nama_biji' => $validated['nama_biji'],
            'asal_kopi' => $validated['asal_kopi'],
        ]);

        $bean->stok_gram = $bean->stocks()->sum('stok_gram'); // for JSON response compatibility

        return response()->json(['message' => 'Data Green Bean berhasil diperbarui', 'data' => $bean]);
    }

    public function destroyGreenBean($id)
    {
        try {
            $bean = GreenBean::findOrFail($id);
            $bean->delete();
            return response()->json(['message' => 'Data Green Bean berhasil dihapus']);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['message' => 'Data Green Bean tidak dapat dihapus karena sudah digunakan dalam catatan produksi (Roasting Batch) atau gudang!'], 400);
        }
    }

    // --- ROASTED BEAN ---
    public function getRoastedBeans()
    {
        $beans = RoastedBean::all();
        return response()->json($beans);
    }

    public function storeRoastedBean(Request $request)
    {
        $validated = $request->validate([
            'nama_kopi' => 'required|string|max:255',
            'bentuk_akhir' => 'required|string|max:255',
        ]);

        $bean = RoastedBean::create([
            'nama_kopi' => $validated['nama_kopi'],
            'bentuk_akhir' => $validated['bentuk_akhir'],
            'total_stok' => 0, // Default 0 for new roasted beans
        ]);

        return response()->json(['message' => 'Data Roasted Bean berhasil ditambahkan', 'data' => $bean]);
    }

    public function updateRoastedBean(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_kopi' => 'required|string|max:255',
            'bentuk_akhir' => 'required|string|max:255',
        ]);

        $bean = RoastedBean::findOrFail($id);
        $bean->update([
            'nama_kopi' => $validated['nama_kopi'],
            'bentuk_akhir' => $validated['bentuk_akhir'],
        ]);

        return response()->json(['message' => 'Data Roasted Bean berhasil diperbarui', 'data' => $bean]);
    }

    public function destroyRoastedBean($id)
    {
        try {
            $bean = RoastedBean::findOrFail($id);
            $bean->delete();
            return response()->json(['message' => 'Data Roasted Bean berhasil dihapus']);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['message' => 'Data Roasted Bean tidak dapat dihapus karena sudah terhubung dengan data produksi (Roasting Batch) atau produk kemasan!'], 400);
        }
    }
}
