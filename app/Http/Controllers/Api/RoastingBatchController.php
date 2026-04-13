<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoastingBatch;
use Illuminate\Http\Request;

class RoastingBatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO: Tampilkan riwayat jadwal produksi roasting
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /*
         TODO PENTING: 
         Disini adalah inti sistem. Programmer harus membuat logic:
         1. Validasi input (berapa Green Bean dipakai, menghasilkan berapa Roasted Bean)
         2. Potong stok GreenBean (Pakai model GreenBean)
         3. Tambah stok di RoastedBean (Pakai model RoastedBean)
         4. Hitung persentase susut (Shrinkage)
         5. Simpan Data ke tabel Roasting Batches
        */
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // TODO
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // TODO
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // TODO
    }
}
