<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoasteryController extends Controller
{
    public function CekSistem() {
        return Response()->json([
            'status' => 'Suksess',
            'pesan' => 'Halo bos, Laravel backend tefa sudah siap njir',
            'kopi_tersedida' => ['Arabica gayo', 'Robusta Dampit']
        ]);
    }
}
