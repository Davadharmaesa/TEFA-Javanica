<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GreenBean;
use App\Models\RoastingBatch;

class ProductionController extends Controller
{
    /**
     * Menampilkan Dashboard Lab Produksi
     */
    public function index()
    {
        return view('production');
    }
}
