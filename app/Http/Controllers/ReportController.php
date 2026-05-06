<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogPekerja;

class ReportController extends Controller
{
    public function index()
    {
        $totalUpahPekerja = LogPekerja::sum('total_upah');
        return view('reports.index', compact('totalUpahPekerja'));
    }
}
