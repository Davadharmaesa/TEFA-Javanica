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
        $greenBeans = \App\Models\GreenBeanStock::with('greenBean')
            ->orderBy('tanggal_beli', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'green_bean_id' => $item->green_bean_id,
                    'kode' => 'GBS-' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                    'nama' => $item->greenBean ? $item->greenBean->nama_biji : 'Unknown',
                    'kategori' => $item->greenBean ? ($item->greenBean->asal_kopi ?? 'Unknown') : 'Unknown',
                    'stok' => $item->stok_gram / 1000,
                    'stok_raw' => $item->stok_gram,
                    'harga_beli' => $item->harga_beli,
                    'total_harga' => $item->total_harga > 0 ? (float) $item->total_harga : round(($item->stok_gram / 1000) * $item->harga_beli),
                    'tanggal_beli' => \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y'),
                ];
            });

        $masterGreenBeans = GreenBean::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama_biji,
                'kategori' => $item->asal_kopi ?? 'Unknown',
            ];
        });

        $roastedBeans = RoastedBean::all()->map(function ($item) {
            // Ambil semua roasting batch yang statusnya completed untuk roasted_bean_id ini
            $batches = \App\Models\RoastingBatch::where('roasted_bean_id', $item->id)
                ->where('status_batch', 'completed')
                ->get();

            // Hitung total sisa stok riil dari batch aktif
            $realStokGram = $batches->sum(function($batch) {
                return $batch->sisa_stok !== null ? $batch->sisa_stok : ($batch->berat_matang_bersih * 1000);
            });

            return [
                'id' => $item->id,
                'kode' => 'RB-' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'nama' => $item->nama_kopi,
                'bentuk' => $item->bentuk_akhir,
                'stok' => $realStokGram / 1000, // Dikonversi ke Kg
                'stok_raw' => $realStokGram,     // Dikonversi ke gram murni (misal: 4500 g)
                'satuan_dasar' => $item->satuan_dasar,
                'tanggal_roasting' => $batches->max('created_at'),
                'tanggal_expire' => $batches->max('created_at') ? \Carbon\Carbon::parse($batches->max('created_at'))->addDays(30)->format('Y-m-d') : null,
                'sisa_stok' => $realStokGram,
            ];
        });

        $packagings = Packaging::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'kode' => 'PK-' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'nama' => $item->nama_kemasan,
                'tipe' => $item->tipe,
                'berat' => $item->kapasitas_gram,
                'stok' => $item->stok_pcs,
                'hpp' => $item->harga_satuan,
                'harga' => $item->harga_satuan * 1.5, // Simulasi harga jual
            ];
        });

        $finishedGoods = \App\Models\FinishedGood::with(['roastedBean', 'packaging'])->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'kode' => 'FG-' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'nama' => ($item->roastedBean->nama_kopi ?? 'Unknown') . ' - ' . ($item->packaging->nama_kemasan ?? 'Unknown'),
                'berat' => $item->packaging->kapasitas_gram ?? 0,
                'stok' => $item->stok_pcs,
            ];
        });

        $roastingBatches = \App\Models\RoastingBatch::with('roastedBean')->where('status_batch', 'completed')->orderBy('created_at', 'desc')->get()->map(function ($batch) {
            $sisaGram = $batch->sisa_stok !== null ? (int) $batch->sisa_stok : (int) ($batch->berat_matang_bersih * 1000);
            return [
                'id' => $batch->id,
                'roasted_bean_id' => $batch->roasted_bean_id,
                'kode' => $batch->kode_batch,
                'nama' => $batch->roastedBean ? $batch->roastedBean->nama_kopi : 'Produk',
                'bentuk' => $batch->profil_sangrai,
                'stok' => round($sisaGram / 1000, 2),
                'tanggal_roasting' => \Carbon\Carbon::parse($batch->created_at)->format('d-m-Y H:i'),
                'tanggal_expire' => \Carbon\Carbon::parse($batch->created_at)->addDays(30)->format('d-m-Y'),
                'sisa_stok' => $sisaGram,
            ];
        });

        return view('inventory.index', compact('greenBeans', 'masterGreenBeans', 'roastedBeans', 'packagings', 'roastingBatches', 'finishedGoods'));
    }

    public function storeGreenBean(Request $request)
    {
        $validated = $request->validate([
            'green_bean_id' => 'required|exists:green_bean,id',
            'stok_gram' => 'required|numeric|min:0',
            'tanggal_beli' => 'required|string',
            'total_harga' => 'required|numeric|min:0' // Pastikan required atau menyesuaikan form
        ]);

        $stokKg = $validated['stok_gram'] / 1000;
        $hargaBeli = 0;
        if ($stokKg > 0 && isset($validated['total_harga']) && $validated['total_harga'] > 0) {
            $hargaBeli = round($validated['total_harga'] / $stokKg);
        }

        try {
            $tanggalBeliCarbon = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['tanggal_beli'])->startOfDay();
        } catch (\Exception $e) {
            $tanggalBeliCarbon = \Carbon\Carbon::parse($validated['tanggal_beli'])->startOfDay();
        }

        if ($tanggalBeliCarbon->gt(\Carbon\Carbon::today())) {
            return redirect()->route('inventory.index', ['tab' => 'greenbean'])->withInput()->with('error', 'Tanggal barang masuk tidak boleh melebihi hari ini!');
        }

        $tanggalBeli = $tanggalBeliCarbon->toDateString();

        // SIMPAN JUGA TOTAL_HARGA KE DATABASE
        \App\Models\GreenBeanStock::create([
            'green_bean_id' => $validated['green_bean_id'],
            'stok_gram' => $validated['stok_gram'],
            'harga_beli' => $hargaBeli,
            'total_harga' => $validated['total_harga'], // <-- INI YANG KURANG SEBELUMNYA
            'tanggal_beli' => $tanggalBeli,
        ]);

        return redirect()->route('inventory.index', ['tab' => 'greenbean'])->with('success', 'Stok Green Bean berhasil ditambahkan!');
    }
    
    public function updateGreenBean(Request $request, $id)
    {
        $validated = $request->validate([
            'stok_gram' => 'required|numeric|min:0',
            'tanggal_beli' => 'required|string',
            'total_harga' => 'required|numeric|min:0' // Tambahkan validasi total_harga saat update
        ]);

        $stokKg = $validated['stok_gram'] / 1000;
        $hargaBeli = 0;
        if ($stokKg > 0 && isset($validated['total_harga']) && $validated['total_harga'] > 0) {
            $hargaBeli = round($validated['total_harga'] / $stokKg);
        }

        try {
            $tanggalBeliCarbon = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['tanggal_beli'])->startOfDay();
        } catch (\Exception $e) {
            $tanggalBeliCarbon = \Carbon\Carbon::parse($validated['tanggal_beli'])->startOfDay();
        }

        if ($tanggalBeliCarbon->gt(\Carbon\Carbon::today())) {
            return redirect()->route('inventory.index', ['tab' => 'greenbean'])->withInput()->with('error', 'Tanggal pembelian asli tidak boleh melebihi hari ini!');
        }

        $tanggalBeli = $tanggalBeliCarbon->toDateString();
        
        $stock = \App\Models\GreenBeanStock::findOrFail($id);
        
        // UPDATE JUGA TOTAL_HARGA DAN HARGA_BELI
        $stock->update([
            'stok_gram' => $validated['stok_gram'],
            'harga_beli' => $hargaBeli,
            'total_harga' => $validated['total_harga'], // <-- INI IKUT DI-UPDATE
            'tanggal_beli' => $tanggalBeli,
        ]);
        
        // Trigger WA low stock notification
        try {
            $waService = new \App\Services\WhatsappService();
            $waService->checkAndNotifyLowStock($stock->green_bean_id);
        } catch (\Exception $ex) {
            \Log::warning("Failed to run low stock WA check on update: " . $ex->getMessage());
        }
        
        return redirect()->route('inventory.index', ['tab' => 'greenbean'])->with('success', 'Data Stok Green Bean berhasil diperbarui!');
    }

    public function destroyGreenBean($id)
    {
        $stock = \App\Models\GreenBeanStock::findOrFail($id);
        $greenBeanId = $stock->green_bean_id;
        $stock->delete();

        // Trigger WA low stock notification
        try {
            $waService = new \App\Services\WhatsappService();
            $waService->checkAndNotifyLowStock($greenBeanId);
        } catch (\Exception $ex) {
            \Log::warning("Failed to run low stock WA check on delete: " . $ex->getMessage());
        }

        return redirect()->route('inventory.index', ['tab' => 'greenbean'])->with('success', 'Data Stok Green Bean berhasil dihapus!');
    }

    // --- ROASTED BEAN ---
    public function storeRoastedBean(Request $request)
    {
        $validated = $request->validate(['nama_kopi' => 'required|string|max:255', 'bentuk_akhir' => 'required|string|max:255', 'total_stok' => 'required|numeric|min:0', 'satuan_dasar' => 'required|string|max:50']);
        RoastedBean::create($validated);
        return redirect()->route('inventory.index', ['tab' => 'roastedbean'])->with('success', 'Roasted Bean berhasil ditambahkan!');
    }
    public function updateRoastedBean(Request $request, $id)
    {
        $validated = $request->validate([
            'total_stok' => 'required|numeric|min:0',
        ]);
        
        $batch = \App\Models\RoastingBatch::findOrFail($id);
        $newStokGram = (float) $validated['total_stok'];

        // Update sisa_stok dan berat_matang_bersih pada batch
        $batch->sisa_stok = $newStokGram;
        $batch->berat_matang_bersih = $newStokGram / 1000;
        $batch->save();

        // Sinkronkan total_stok master RoastedBean dengan total sisa batch aktif
        if ($batch->roasted_bean_id) {
            $master = \App\Models\RoastedBean::find($batch->roasted_bean_id);
            if ($master) {
                $totalSisa = \App\Models\RoastingBatch::where('roasted_bean_id', $batch->roasted_bean_id)
                    ->where('status_batch', 'completed')
                    ->sum('sisa_stok');
                $master->total_stok = $totalSisa;
                $master->save();
            }
        }

        return redirect()->route('inventory.index', ['tab' => 'roastedbean'])->with('success', 'Stok Batch Roasting berhasil diperbarui!');
    }
    
    public function destroyRoastedBean($id)
    {
        $batch = \App\Models\RoastingBatch::findOrFail($id);
        $rbId = $batch->roasted_bean_id;
        $batch->delete();

        if ($rbId) {
            $master = \App\Models\RoastedBean::find($rbId);
            if ($master) {
                $master->total_stok = \App\Models\RoastingBatch::where('roasted_bean_id', $rbId)
                    ->where('status_batch', 'completed')
                    ->sum('sisa_stok');
                $master->save();
            }
        }
        
        return redirect()->route('inventory.index', ['tab' => 'roastedbean'])->with('success', 'Batch Roasting berhasil dihapus!');
    }

    // --- PACKAGING ---
    public function storePackaging(Request $request)
    {
        $validated = $request->validate(['nama_kemasan' => 'required|string|max:255', 'tipe' => 'required|string|max:255', 'stok_pcs' => 'required|integer|min:0', 'harga_satuan' => 'required|numeric|min:0']);
        Packaging::create($validated);
        return redirect()->route('inventory.index', ['tab' => 'produk'])->with('success', 'Produk Kemasan berhasil ditambahkan!');
    }
    public function updatePackaging(Request $request, $id)
    {
        $validated = $request->validate(['nama_kemasan' => 'required|string|max:255', 'tipe' => 'required|string|max:255', 'stok_pcs' => 'required|integer|min:0', 'harga_satuan' => 'required|numeric|min:0']);
        Packaging::findOrFail($id)->update($validated);
        return redirect()->route('inventory.index', ['tab' => 'produk'])->with('success', 'Produk Kemasan berhasil diperbarui!');
    }
    public function destroyPackaging($id)
    {
        Packaging::findOrFail($id)->delete();
        return redirect()->route('inventory.index', ['tab' => 'produk'])->with('success', 'Produk Kemasan berhasil dihapus!');
    }
}
