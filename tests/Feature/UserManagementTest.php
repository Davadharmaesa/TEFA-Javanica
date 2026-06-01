<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji apakah PIC dapat mengakses halaman kelola user.
     */
    public function test_pic_can_access_user_management(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $response = $this->actingAs($pic)->get('/users');

        $response->assertStatus(200);
        $response->assertSee('Kelola Pengguna Aplikasi');
    }

    /**
     * Uji apakah Operator ditolak saat mencoba mengakses halaman kelola user.
     */
    public function test_operator_cannot_access_user_management(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($operator)->get('/users');

        $response->assertStatus(403);
    }

    /**
     * Uji apakah PIC dapat menambahkan user baru.
     */
    public function test_pic_can_create_new_user(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $response = $this->actingAs($pic)->post('/users', [
            'name' => 'Adhi Nugraha',
            'email' => 'adhi@tefa.com',
            'role' => 'Operator',
            'password' => 'operator123',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'name' => 'Adhi Nugraha',
            'email' => 'adhi@tefa.com',
            'role' => 'Operator',
        ]);
    }

    /**
     * Uji apakah PIC dapat mengedit data user lain.
     */
    public function test_pic_can_update_user(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@tefa.com',
            'role' => 'Mahasiswa Magang',
        ]);

        $response = $this->actingAs($pic)->put("/users/{$user->id}", [
            'name' => 'Budi Santoso Harjo',
            'email' => 'budi.new@tefa.com',
            'role' => 'Operator',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Budi Santoso Harjo',
            'email' => 'budi.new@tefa.com',
            'role' => 'Operator',
        ]);
    }

    /**
     * Uji apakah PIC dapat menghapus user lain.
     */
    public function test_pic_can_delete_user(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $user = User::factory()->create([
            'name' => 'Rian Hidayat',
            'email' => 'rian@tefa.com',
            'role' => 'Operator',
        ]);

        $response = $this->actingAs($pic)->delete("/users/{$user->id}");

        $response->assertRedirect('/users');
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Uji apakah PIC dilarang menghapus akunnya sendiri yang sedang aktif.
     */
    public function test_pic_cannot_delete_themself(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);

        $response = $this->actingAs($pic)->delete("/users/{$pic->id}");

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'id' => $pic->id,
        ]);
    }
}
