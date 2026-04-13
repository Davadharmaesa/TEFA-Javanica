<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoastedBean;
use Illuminate\Http\Request;

class RoastedBeanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO untuk Programmer: Buat logika get semua data stok hasil sangrai (Roasted Bean)
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // TODO: Validasi dan tambah data roasted bean baru (jika pak deka memasukkan stok manual)
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // TODO: Ambil 1 detail
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // TODO: Update stok atau nama
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // TODO: Hapus data
    }
}
