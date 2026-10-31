<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Console\View\Components\Secret;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // tc-001
    // public function test_tc_auth_001_login_sukses_role_pic() : void {
    //     $secret = Google2FA::generateSecretKey();
    //     $user = User::factory()->create([
    //         'email' => 'buastri@tefa.com',
    //         'password' => bcrypt('kopi123'),
    //         'role' => 'pic',
    //         'two_factor_secret' => $secret,
    //     ]);

    //     $response = $this->post('/login', [
    //         'email' => 'buastri@tefa.com',
    //         'password' => 'kopi123',
    //     ]);

    //     $response->assertStatus(200)
    //              ->assertJson([
    //                 'requires_2fa' => true,
    //                 'user_id' => $user->id,
    //              ]);
    // }

    public function test_tc_auth_002_login_gagal_role_pic(): void
    {
        $user = User::factory()->create([
            'email' => 'buastri@tefa.com',
            'password' => bcrypt('kopi123'),
            'role' => 'PIC',
        ]);

        $response = $this->postJson('/login', [
            'email' => 'buastri@tefa.com',
            'password' => 'salah',
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'message' => 'Email atau Password Salah!'
                 ]);
    }

    public function test_tc_auth_002_login_sukses_role_operator(): void
    {
        $user = User::factory()->create([
            'email' => 'pakdeka@tefa.com',
            'password' => bcrypt('kopi123'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'pakdeka@tefa.com',
            'password' => 'kopi123',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                    'redirect_url' => '/dashboard']);
    }

    public function test_tc_auth_004_login_gagal_role_operator(): void
    {
        $user = User::factory()->create([
            'email' => 'pakdeka@tefa.com',
            'password' => bcrypt('kopi123'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'pakdeka@tefa.com',
            'password' => 'passwordsalah',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Email atau Password Salah!']);
    }

    public function test_tc_auth_005_login_gagal_mfa_salah(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => Google2FA::generateSecretKey(),
        ]);

        $response = $this->postJson('/verify-2fa', [
            'user_id' => $user->id,
            'otp' => '000000',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Kode OTP Salah atau Kadaluarsa!']);
    }

    // TC-AUTH-005    
    public function test_tc_auth_006_otorisasi_operator_akses_url_pic(): void
    {
        $user = User::factory()->create([
            'role' => 'Operator',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $this->assertAuthenticatedAs($user);
    }
}