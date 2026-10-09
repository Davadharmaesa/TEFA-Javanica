<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- DATA USER MASTER ---
        $operator = User::create([
            'name' => 'Pak Deka',
            'email' => 'pakdeka@tefa.com',
            'password' => Hash::make('kopi123'),
            'role' => 'Operator',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Habib',
            'email' => 'habib@mahasiswa.com',
            'password' => Hash::make('password'),
            'role' => 'Mahasiswa Magang',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Nain',
            'email' => 'nain@mahasiswa.com',
            'password' => Hash::make('password'),
            'role' => 'Mahasiswa Magang',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Bu Astri',
            'email' => 'buastri@tefa.com',
            'password' => Hash::make('kopi123'),
            'role' => 'PIC',
            'email_verified_at' => now(),
        ]); 

        // --- STOK GUDANG MENTAH (Seeder) ---
        $greenBean = \App\Models\GreenBean::create([
            'nama_biji' => 'GB Arabika Belawan',
            'asal_kopi' => 'Bali',
        ]);

        \App\Models\GreenBeanStock::create([
            'green_bean_id' => $greenBean->id,
            'stok_gram' => 40000, // 50 Kg - 10 Kg (used in BATCH-001) = 40 Kg remaining
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
            'satuan' => 'Batch',
            'harga' => 1500,
        ]);
        \App\Models\Biaya::create([
            'nama' => 'Listrik Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Kwh',
            'harga' => 1500,
        ]);
        \App\Models\Biaya::create([
            'nama' => 'Penyusutan Mesin',
            'kategori' => 'Overhead',
            'satuan' => 'Batch',
            'harga' => 2000,
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
            'nama_kemasan' => 'Pouch Standard 100g',
            'kapasitas_gram' => 100,
            'harga_satuan' => 1000,
            'stok_pcs' => 100,
        ]);
        \App\Models\Packaging::create([
            'tipe' => 'Pouch Alumunium',
            'nama_kemasan' => 'Pouch Hitam Doff 200g',
            'kapasitas_gram' => 200,
            'harga_satuan' => 1500,
            'stok_pcs' => 100,
        ]);
        \App\Models\Packaging::create([
            'tipe' => 'Pouch Alumunium',
            'nama_kemasan' => 'Pouch Standard 500g',
            'kapasitas_gram' => 500,
            'harga_satuan' => 2500,
            'stok_pcs' => 100,
        ]);
        \App\Models\Packaging::create([
            'tipe' => 'Pouch Alumunium',
            'nama_kemasan' => 'Pouch Standard 1kg',
            'kapasitas_gram' => 1000,
            'harga_satuan' => 4000,
            'stok_pcs' => 100,
        ]);
        \App\Models\Packaging::create([
            'tipe' => 'Stiker',
            'nama_kemasan' => 'Stiker Label Arabika Ijen',
            'kapasitas_gram' => 0,
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
            'waktu_first_crack' => 12.00,
            'waktu_development' => 180,
            'durasi_pre_heating' => 5,
            'berat_mentah_gudang' => 10.00, // 10 Kg
            'berat_mentah_sortasi' => 9.80,
            'berat_matang_kotor' => 8.50,
            'berat_matang_bersih' => 8.20,
            'persentase_susut' => 16.33,
            'tanggal_roasting' => now()->toDateString(),
            'status_batch' => 'completed',
            'biaya_bahan_baku' => 1200000 // 10 Kg * 120,000/Kg
        ]);
    }
}