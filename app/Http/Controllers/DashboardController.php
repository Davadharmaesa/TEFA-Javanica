<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Mock data based on the provided image
        $data = [
            'total_stok_biji_mentah' => 49,
            'total_kopi_matang' => 0,
            'penjualan_retail' => 0,
            
            'stok_menipis' => [
                [
                    'name' => 'Arabica Gayo',
                    'type' => 'Green Bean',
                    'amount' => 0,
                    'unit' => 'kg'
                ],
                [
                    'name' => 'Robusta Dampit',
                    'type' => 'Green Bean',
                    'amount' => 0,
                    'unit' => 'kg'
                ],
                [
                    'name' => 'Javanica Espresso Blend',
                    'type' => 'Roasted Bean',
                    'amount' => 0,
                    'unit' => 'kg'
                ],
                [
                    'name' => 'Arabica Filter Premium',
                    'type' => 'Roasted Bean',
                    'amount' => 0,
                    'unit' => 'gram'
                ]
            ],

            'recent_logs' => [
                [
                    'type' => 'INBOUND_RESTOCK',
                    'description' => 'Received 25kg of Arabica Ijen from King Nain. Logged to Expense.',
                    'time' => '12.47.53',
                    'user' => 'Budi Coordinator'
                ],
                [
                    'type' => 'INBOUND_RESTOCK',
                    'description' => 'Received 12kg of 12 from 12. Logged to Expense.',
                    'time' => '23.06.30',
                    'user' => 'Budi Coordinator'
                ],
                [
                    'type' => 'INBOUND_RESTOCK',
                    'description' => 'Received 12kg of Arabica from agus. Logged to Expense.',
                    'time' => '23.09.43',
                    'user' => 'Budi Coordinator'
                ]
            ]
        ];

        return view('dashboard', $data);
    }
}
