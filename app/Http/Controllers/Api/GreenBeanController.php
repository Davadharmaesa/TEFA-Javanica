<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GreenBean;
use Illuminate\Http\Request;

class GreenBeanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO untuk Programmer: Buat logika get semua data green bean
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // TODO untuk Programmer: Buat logika validasi dan simpan green bean baru
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // TODO untuk Programmer: Buat logika untuk memanggil detail 1 green bean
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // TODO untuk Programmer: Buat logika update
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // TODO untuk Programmer: Buat logika delete
    }
}
