<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    // TC-USR-001
    public function test_tc_usr_001_tambah_akun_pegawai_baru(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $response = $this->actingAs($pic)->post('/users', [
            'name' => 'Viona Dhini', 'email' => 'viona@tefa.com', 'role' => 'Operator', 'password' => 'Secret123!',
        ]);
        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', ['email' => 'viona@tefa.com']);
    }

    // TC-USR-002
    public function test_tc_usr_002_pembaruan_role_akun_pegawai(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $user = User::factory()->create(['name' => 'Budi', 'email' => 'budi@tefa.com', 'role' => 'Operator']);

        $response = $this->actingAs($pic)->put("/users/{$user->id}", [
            'name' => 'Budi', 'email' => 'budi@tefa.com', 'role' => 'PIC',
        ]);
        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'PIC']);
    }

    // TC-USR-003
    public function test_tc_usr_003_menghapus_akun_pegawai_lain(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $user = User::factory()->create(['name' => 'Rian', 'email' => 'rian@tefa.com', 'role' => 'Operator']);

        $response = $this->actingAs($pic)->delete("/users/{$user->id}");
        $response->assertRedirect('/users');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    // TC-USR-004
    public function test_tc_usr_004_proteksi_menolak_hapus_akun_sendiri(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $response = $this->actingAs($pic)->delete("/users/{$pic->id}");
        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', ['id' => $pic->id]);
    }

    // TC-USR-005
    public function test_tc_usr_005_uji_validasi_email_duplikat(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        User::factory()->create(['email' => 'viona@tefa.com']);

        $response = $this->actingAs($pic)->post('/users', [
            'name' => 'Duplikat', 'email' => 'viona@tefa.com', 'role' => 'Operator', 'password' => 'secret123',
        ]);
        $response->assertSessionHasErrors('email');
    }

    // TC-USR-006
    public function test_tc_usr_006_validasi_form_tambah_user_kosong(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $response = $this->actingAs($pic)->post('/users', [
            'name' => '', 'email' => '', 'role' => '', 'password' => '',
        ]);
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    // TC-USR-007 (Bagian 1)
    public function test_tc_usr_007_uji_format_email_tidak_valid(): void
    {
        $pic = User::factory()->create(['role' => 'PIC']);
        $response = $this->actingAs($pic)->post('/users', [
            'name' => 'Test Email', 'email' => 'viona-tanpa-at-sign.com', 'role' => 'Operator', 'password' => 'secret123',
        ]);
        $response->assertSessionHasErrors('email');
    }

    // TC-USR-007 (Bagian 2 - Akses Operator)
    public function test_operator_cannot_access_user_management(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);
        $response = $this->actingAs($operator)->get('/users');
        $response->assertStatus(403);
    }
}