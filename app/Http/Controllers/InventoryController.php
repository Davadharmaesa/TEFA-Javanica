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
                'tanggal_roasting' => $item->tanggal_roasting,
                'tanggal_expire' => $item->tanggal_expire,
                'sisa_stok' => $item->sisa_stok,
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

        $roastingBatches = \App\Models\RoastingBatch::with('roastedBean')->where('status_batch', 'completed')->orderBy('created_at', 'desc')->get()->map(function ($batch) {
            return [
                'id' => $batch->id,
                'kode' => $batch->kode_batch,
                'nama' => $batch->roastedBean ? $batch->roastedBean->nama_kopi : 'Produk',
                'bentuk' => $batch->profil_sangrai,
                'stok' => $batch->berat_matang_bersih,
                'tanggal_roasting' => \Carbon\Carbon::parse($batch->created_at)->format('d-m-Y H:i'),
                'tanggal_expire' => \Carbon\Carbon::parse($batch->created_at)->addDays(30)->format('d-m-Y'),
                'sisa_stok' => $batch->berat_matang_bersih * 1000, // Simulasi sisa stok sama dengan hasil
            ];
        });

        return view('inventory.index', compact('greenBeans', 'roastedBeans', 'packagings', 'roastingBatches'));
    }

    // --- GREEN BEAN ---
    public function storeGreenBean(Request $request)
    {
        $validated = $request->validate([
            'green_bean_id' => 'required|exists:green_bean,id',
            'stok_gram' => 'required|numeric|min:0',
            'tanggal_beli' => 'required|date'
        ]);

        $bean = GreenBean::findOrFail($validated['green_bean_id']);
        $bean->stok_gram += $validated['stok_gram'];
        $bean->tanggal_beli = $validated['tanggal_beli'];
        $bean->save();

        return redirect()->route('inventory.index')->with('success', 'Stok Green Bean berhasil ditambahkan!');
    }
    
    public function updateGreenBean(Request $request, $id)
    {
        $validated = $request->validate([
            'stok_gram' => 'required|numeric|min:0',
            'tanggal_beli' => 'required|date'
        ]);
        
        GreenBean::findOrFail($id)->update($validated);
        
        return redirect()->route('inventory.index')->with('success', 'Data Stok Green Bean berhasil diperbarui!');
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
        $validated = $request->validate([
            'total_stok' => 'required|numeric|min:0',
        ]);
        
        $batch = \App\Models\RoastingBatch::findOrFail($id);
        
        $oldStokGram = $batch->berat_matang_bersih * 1000;
        $newStokGram = $validated['total_stok'];
        $diffGram = $newStokGram - $oldStokGram;

        $batch->berat_matang_bersih = $newStokGram / 1000;
        $batch->save();

        if ($batch->roasted_bean_id) {
            $master = \App\Models\RoastedBean::find($batch->roasted_bean_id);
            if ($master) {
                $master->total_stok += $diffGram;
                $master->save();
            }
        }

        return redirect()->route('inventory.index')->with('success', 'Batch Roasting berhasil diperbarui!');
    }
    
    public function destroyRoastedBean($id)
    {
        $batch = \App\Models\RoastingBatch::findOrFail($id);
        
        if ($batch->roasted_bean_id) {
            $master = \App\Models\RoastedBean::find($batch->roasted_bean_id);
            if ($master) {
                $master->total_stok -= ($batch->berat_matang_bersih * 1000);
                $master->save();
            }
        }
        $batch->delete();
        
        return redirect()->route('inventory.index')->with('success', 'Batch Roasting berhasil dihapus!');
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
