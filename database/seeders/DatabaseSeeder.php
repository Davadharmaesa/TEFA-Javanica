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
            'nama_biji' => 'GB Arabika Kintamani',
            'stok_gram' => 50000, // 50 Kg
            'asal_kopi' => 'Bali',
            'harga_jual' => 120000
        ]);

        \App\Models\GreenBean::create([
            'nama_biji' => 'GB Robusta Dampit',
            'stok_gram' => 100000, // 100 Kg
            'asal_kopi' => 'Malang',
            'harga_jual' => 45000
        ]);
        
        \App\Models\GreenBean::create([
            'nama_biji' => 'GB Arabika Gayo',
            'stok_gram' => 25000, // 25 Kg
            'asal_kopi' => 'Aceh',
            'harga_jual' => 150000
        ]);
    }
}
