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

        $operator = User::factory()->create([
            'name' => 'Pak Deka',
            'email' => 'pakdeka@tefa.com',
            'password' => bcrypt('kopi123'),
            'role' =>  'Operator',
        ]);

        User::factory()->create([
            'name' => 'Habib',
            'email' => 'habib@mahasiswa.com',
            'password' => bcrypt('password'),
            'role' =>  'Mahasiswa Magang',
        ]);

        User::factory()->create([
            'name' => 'Nain',
            'email' => 'nain@mahasiswa.com',
            'password' => bcrypt('password'),
            'role' =>  'Mahasiswa Magang',
        ]);

        User::factory()->create([
            'name' => 'Bu Astri',
            'email' => 'buastri@tefa.com',
            'password' => bcrypt('kopi123'),
            'role' =>  'PIC',
        ]); 

        // --- STOK GUDANG MENTAH (Seeder) ---
        $greenBean = \App\Models\GreenBean::create([
            'nama_biji' => 'GB Arabika Belawan',
            'stok_gram' => 50000, // 50 Kg
            'asal_kopi' => 'Bali',
            'harga_beli' => 120000,
            'tanggal_beli' => now()->toDateString()
        ]);

        // --- PRODUK JADI ROASTED BEAN (Seeder) ---
        $roastedBean = \App\Models\RoastedBean::create([
            'nama_kopi' => 'GB Arabika Belawan',
            'bentuk_akhir' => 'Biji Utuh',
            'total_stok' => 8200, // 8.2 Kg dari BATCH-001 (tidak ada stok siluman awal)
        ]);

        // --- BIAYA (Seeder) ---
        \App\Models\Biaya::create([
            'nama' => 'Gas Elpiji 3Kg',
            'kategori' => 'Overhead',
            'satuan' => 'Tabung',
            'harga' => 20000,
        ]);
        \App\Models\Biaya::create([
            'nama' => 'Listrik Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 1500,
        ]);
        \App\Models\Biaya::create([
            'nama' => 'Upah Roaster',
            'kategori' => 'Tenaga Kerja',
            'satuan' => 'Batch',
            'harga' => 15000,
        ]);

        // --- KEMASAN / PACKAGING (Seeder) ---
        \App\Models\Packaging::create([
            'tipe' => 'Pouch Alumunium',
            'nama_kemasan' => 'Pouch Hitam Doff 200g',
            'harga_satuan' => 1500,
            'stok_pcs' => 100,
        ]);
        \App\Models\Packaging::create([
            'tipe' => 'Stiker',
            'nama_kemasan' => 'Stiker Label Arabika Ijen',
            'harga_satuan' => 500,
            'stok_pcs' => 100,
        ]);

        // --- BATCH SANGRAI SELESAI (Seeder) ---
        \App\Models\RoastingBatch::create([
            'kode_batch' => 'BATCH-001',
            'user_id' => $operator->id,
            'green_bean_id' => $greenBean->id,
            'roasted_bean_id' => $roastedBean->id,
            'profil_sangrai' => 'Medium Roast',
            'durasi_roasting' => 15, // 15 menit
            'berat_mentah_gudang' => 10.00, // 10 Kg
            'berat_mentah_sortasi' => 9.80,
            'berat_matang_kotor' => 8.50,
            'berat_matang_bersih' => 8.20,
            'persentase_susut' => 18.00,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed'
        ]);
    }
}
