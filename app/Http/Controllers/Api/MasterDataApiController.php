<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GreenBean;
use App\Models\RoastedBean;
use App\Models\Biaya;

class MasterDataApiController extends Controller
{
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
        $biaya = Biaya::findOrFail($id);
        $biaya->delete();
        return response()->json(['message' => 'Biaya berhasil dihapus']);
    }

    // --- GREEN BEAN ---
    public function getGreenBeans()
    {
        $beans = GreenBean::all();
        return response()->json($beans);
    }

    public function storeGreenBean(Request $request)
    {
        $validated = $request->validate([
            'nama_biji' => 'required|string|max:255',
            'asal_kopi' => 'nullable|string|max:255',
            'harga_jual' => 'required|numeric|min:0',
        ]);

        $bean = GreenBean::create([
            'nama_biji' => $validated['nama_biji'],
            'asal_kopi' => $validated['asal_kopi'],
            'harga_jual' => $validated['harga_jual'],
            'stok_gram' => 0, // Default 0 for new beans
        ]);

        return response()->json(['message' => 'Data Green Bean berhasil ditambahkan', 'data' => $bean]);
    }

    public function updateGreenBean(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_biji' => 'required|string|max:255',
            'asal_kopi' => 'nullable|string|max:255',
            'harga_jual' => 'required|numeric|min:0',
        ]);

        $bean = GreenBean::findOrFail($id);
        $bean->update([
            'nama_biji' => $validated['nama_biji'],
            'asal_kopi' => $validated['asal_kopi'],
            'harga_jual' => $validated['harga_jual'],
        ]);

        return response()->json(['message' => 'Data Green Bean berhasil diperbarui', 'data' => $bean]);
    }

    public function destroyGreenBean($id)
    {
        $bean = GreenBean::findOrFail($id);
        $bean->delete();
        return response()->json(['message' => 'Data Green Bean berhasil dihapus']);
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
        $bean = RoastedBean::findOrFail($id);
        $bean->delete();
        return response()->json(['message' => 'Data Roasted Bean berhasil dihapus']);
    }
}
