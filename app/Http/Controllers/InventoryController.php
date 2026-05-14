<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\GreenBean;
use App\Models\RoastedBean;
use App\Models\Packaging;

class InventoryController extends Controller
{
    public function index()
    {
        $greenBeans = GreenBean::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'kode' => 'GB-' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'nama' => $item->nama_biji,
                'kategori' => $item->asal_kopi ?? 'Unknown',
                'stok' => $item->stok_gram / 1000,
                'stok_raw' => $item->stok_gram,
                'harga_beli' => $item->harga_beli,
                'tanggal_beli' => $item->tanggal_beli,
            ];
        });

        $roastedBeans = RoastedBean::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'kode' => 'RB-' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'nama' => $item->nama_kopi,
                'bentuk' => $item->bentuk_akhir,
                'stok' => $item->total_stok / 1000,
                'stok_raw' => $item->total_stok,
                'satuan_dasar' => $item->satuan_dasar,
            ];
        });

        $packagings = Packaging::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'kode' => 'PK-' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'nama' => $item->nama_kemasan,
                'tipe' => $item->tipe,
                'berat' => 0, // Nilai default karena belum ada di DB
                'stok' => $item->stok_pcs,
                'hpp' => $item->harga_satuan,
                'harga' => $item->harga_satuan * 1.5, // Simulasi harga jual
            ];
        });

        return view('inventory.index', compact('greenBeans', 'roastedBeans', 'packagings'));
    }

    // --- GREEN BEAN ---
    public function storeGreenBean(Request $request)
    {
        $validated = $request->validate(['nama_biji' => 'required|string|max:255', 'asal_kopi' => 'required|string|max:255', 'stok_gram' => 'required|numeric|min:0', 'harga_beli' => 'required|numeric|min:0', 'tanggal_beli' => 'required|date']);
        GreenBean::create($validated);
        return redirect()->route('inventory.index')->with('success', 'Green Bean berhasil ditambahkan!');
    }
    public function updateGreenBean(Request $request, $id)
    {
        $validated = $request->validate(['nama_biji' => 'required|string|max:255', 'asal_kopi' => 'required|string|max:255', 'stok_gram' => 'required|numeric|min:0', 'harga_beli' => 'required|numeric|min:0', 'tanggal_beli' => 'required|date']);
        GreenBean::findOrFail($id)->update($validated);
        return redirect()->route('inventory.index')->with('success', 'Green Bean berhasil diperbarui!');
    }
    public function destroyGreenBean($id)
    {
        GreenBean::findOrFail($id)->delete();
        return redirect()->route('inventory.index')->with('success', 'Green Bean berhasil dihapus!');
    }

    // --- ROASTED BEAN ---
    public function storeRoastedBean(Request $request)
    {
        $validated = $request->validate(['nama_kopi' => 'required|string|max:255', 'bentuk_akhir' => 'required|string|max:255', 'total_stok' => 'required|numeric|min:0', 'satuan_dasar' => 'required|string|max:50']);
        RoastedBean::create($validated);
        return redirect()->route('inventory.index')->with('success', 'Roasted Bean berhasil ditambahkan!');
    }
    public function updateRoastedBean(Request $request, $id)
    {
        $validated = $request->validate(['nama_kopi' => 'required|string|max:255', 'bentuk_akhir' => 'required|string|max:255', 'total_stok' => 'required|numeric|min:0', 'satuan_dasar' => 'required|string|max:50']);
        RoastedBean::findOrFail($id)->update($validated);
        return redirect()->route('inventory.index')->with('success', 'Roasted Bean berhasil diperbarui!');
    }
    public function destroyRoastedBean($id)
    {
        RoastedBean::findOrFail($id)->delete();
        return redirect()->route('inventory.index')->with('success', 'Roasted Bean berhasil dihapus!');
    }

    // --- PACKAGING ---
    public function storePackaging(Request $request)
    {
        $validated = $request->validate(['nama_kemasan' => 'required|string|max:255', 'tipe' => 'required|string|max:255', 'stok_pcs' => 'required|integer|min:0', 'harga_satuan' => 'required|numeric|min:0']);
        Packaging::create($validated);
        return redirect()->route('inventory.index')->with('success', 'Produk Kemasan berhasil ditambahkan!');
    }
    public function updatePackaging(Request $request, $id)
    {
        $validated = $request->validate(['nama_kemasan' => 'required|string|max:255', 'tipe' => 'required|string|max:255', 'stok_pcs' => 'required|integer|min:0', 'harga_satuan' => 'required|numeric|min:0']);
        Packaging::findOrFail($id)->update($validated);
        return redirect()->route('inventory.index')->with('success', 'Produk Kemasan berhasil diperbarui!');
    }
    public function destroyPackaging($id)
    {
        Packaging::findOrFail($id)->delete();
        return redirect()->route('inventory.index')->with('success', 'Produk Kemasan berhasil dihapus!');
    }
}
