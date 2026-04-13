<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PesananKeluar;
use Illuminate\Http\Request;

class PesananKeluarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO: Menampilkan riwayat transaksi kasir
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /* 
        TODO PENTING (FITUR MODE TIMBANGAN API):
        1. Terima data gram yang dibeli (misal 310 gram).
        2. Kurangi stok di tabel roasted_beans (310 / 1000 = 0.31 Kg)
        3. Catat di tabel pesanan_keluars (Rekap penjualan hari ini)
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
