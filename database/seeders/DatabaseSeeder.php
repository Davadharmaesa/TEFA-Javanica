<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Pak Deka',
            'email' => 'pakdeka@tefa.com',
            'password' => bcrypt('kopi123'),
            'role' =>  'Operator',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Bu Astri',
            'email' => 'buastri@tefa.com',
            'password' => bcrypt('kopi123'),
            'role' =>  'PIC',
        ]); 

        // --- STOK GUDANG MENTAH (Seeder) ---
        \App\Models\GreenBean::create([
            'nama_biji' => 'GB Arabika Belawan',
            'stok_gram' => 50000, // 50 Kg
            'asal_kopi' => 'Bali',
            'harga_jual' => 120000
        ]);

        // --- PRODUK JADI ROASTED BEAN (Seeder) ---
    
        \App\Models\RoastedBean::create([
            'nama_kopi' => 'GB Arabika Belawan',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 5000, // 5 Kg
        ]);
    }
}
