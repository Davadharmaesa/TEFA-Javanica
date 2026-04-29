<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalesController extends Controller
{
    /**
     * Display the Sales Dashboard / Pre-Order List
     */
    public function index()
    {
        // Dummy data for Internal Pre-Orders (Poliwangi Campus)
        $internalOrders = [
            [
                'id' => 'PO-INT-001',
                'customer_name' => 'Bapak Budi (Dosen TI)',
                'item' => 'Arabica Ijen Roasting Medium (200g)',
                'qty' => 5,
                'total_price' => 250000,
                'status' => 'Menunggu Konfirmasi',
                'payment_status' => 'Belum Bayar',
                'target_date' => '2026-05-02',
                'created_at' => '2026-04-29 09:00:00'
            ],
            [
                'id' => 'PO-INT-002',
                'customer_name' => 'Ibu Rina (Keuangan)',
                'item' => 'Robusta Gombengsari Dark (500g)',
                'qty' => 2,
                'total_price' => 120000,
                'status' => 'Diproses',
                'payment_status' => 'Lunas',
                'target_date' => '2026-04-30',
                'created_at' => '2026-04-28 14:30:00'
            ],
        ];

        // Dummy data for External Pre-Orders
        $externalOrders = [
            [
                'id' => 'PO-EXT-001',
                'customer_name' => 'Cafe Senja, Banyuwangi',
                'item' => 'Espresso Blend (1kg)',
                'qty' => 10,
                'total_price' => 1500000,
                'status' => 'Siap Diambil',
                'payment_status' => 'DP (50%)',
                'target_date' => '2026-04-29',
                'created_at' => '2026-04-25 10:15:00'
            ],
            [
                'id' => 'PO-EXT-002',
                'customer_name' => 'Andi Wijaya',
                'item' => 'Arabica Ijen Roasting Light (200g)',
                'qty' => 1,
                'total_price' => 60000,
                'status' => 'Selesai',
                'payment_status' => 'Lunas',
                'target_date' => '2026-04-27',
                'created_at' => '2026-04-26 16:45:00'
            ]
        ];

        return view('sales.index', compact('internalOrders', 'externalOrders'));
    }

    /**
     * Show the form for creating a new Pre-Order
     */
    public function create()
    {
        return view('sales.create');
    }
}
