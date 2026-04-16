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

        User::factory()->create([
            'name' => 'Bu Astri',
            'email' => 'buastri@tefa.com',
            'password' => bcrypt('kopi123'),
            'role' =>  'PIC',
        ]); 
    }
}
