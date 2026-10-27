<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_fails_when_fields_are_empty(): void
    {
        $response = $this->postJson('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'operator@javanica.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'operator@javanica.com',
            'password' => 'passwordsalah',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Email atau Password Salah!']);
    }

    public function test_operator_or_pic_bypasses_2fa(): void
    {
        $user = User::factory()->create([
            'email' => 'pic@javanica.com',
            'password' => bcrypt('password123'),
            'role' => 'PIC',
        ]);

        $response = $this->postJson('/login', [
            'email' => 'pic@javanica.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'requires_2fa' => false,
                     'redirect_url' => '/dashboard',
                 ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_without_2fa_secret_returns_qr_setup(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@javanica.com',
            'password' => bcrypt('password123'),
            'role' => 'Admin',
            'two_factor_secret' => null,
        ]);

        $response = $this->postJson('/login', [
            'email' => 'admin@javanica.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'requires_2fa_setup',
                     'qr_code_url',
                     'secret_key',
                     'user_id',
                 ]);
    }

    public function test_user_with_2fa_secret_requires_otp(): void
    {
        $secret = Google2FA::generateSecretKey();
        $user = User::factory()->create([
            'email' => 'admin2@javanica.com',
            'password' => bcrypt('password123'),
            'role' => 'Admin',
            'two_factor_secret' => $secret,
        ]);

        $response = $this->postJson('/login', [
            'email' => 'admin2@javanica.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'requires_2fa' => true,
                     'user_id' => $user->id,
                 ]);
    }

    public function test_verify_2fa_success_with_valid_otp(): void
    {
        $secret = Google2FA::generateSecretKey();
        $user = User::factory()->create([
            'two_factor_secret' => $secret,
        ]);

        $validOtp = Google2FA::getCurrentOtp($secret);

        $response = $this->postJson('/verify-2fa', [
            'user_id' => $user->id,
            'otp' => $validOtp,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Login Berhasil!']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_verify_2fa_fails_with_invalid_otp(): void
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

    public function test_verify_2fa_fails_when_user_not_found(): void
    {
        $response = $this->postJson('/verify-2fa', [
            'user_id' => 99999,
            'otp' => '123456',
        ]);

        $response->assertStatus(404)
                 ->assertJson(['message' => 'User tidak ditemukan!']);
    }
}