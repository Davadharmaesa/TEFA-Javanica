<?php

namespace Tests\Unit;

use App\Models\User;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use Tests\TestCase;

class AuthWhiteboxTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Memastikan skema database siap digunakan
        $this->artisan('migrate');
    }

    // =========================================================================
    // WB-AUTH-01: login() — Basis Paths P1 s.d. P6 (Cyclomatic Complexity V(G)=6)
    // =========================================================================

    /**
     * TC-WB-AUTH-001 (Path 1: N1 -> N2)
     * Menguji decision D1 bernilai True: input kosong / tidak valid melempar ValidationException (HTTP 422).
     */
    public function test_login_path_1_empty_input(): void
    {
        $controller = new AuthController();
        $request = Request::create('/login', 'POST', [
            'email' => '',
            'password' => '',
        ]);

        $this->expectException(ValidationException::class);
        $controller->login($request);
    }

    /**
     * TC-WB-AUTH-002 (Path 2: N1 -> N3 -> N4 -> N6)
     * Menguji decision D2 bernilai True: email tidak terdaftar di database (!$user).
     */
    public function test_login_path_2_user_not_found(): void
    {
        $controller = new AuthController();
        $request = Request::create('/login', 'POST', [
            'email' => 'ghost@tefa.com',
            'password' => '123456',
        ]);

        $response = $controller->login($request);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('Email atau Password Salah', $response->getContent());
    }

    /**
     * TC-WB-AUTH-003 (Path 3: N1 -> N3 -> N4 -> N5 -> N6)
     * Menguji decision D3 bernilai True: user ditemukan, tetapi password salah (!Hash::check).
     */
    public function test_login_path_3_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'pic.test@tefa.com',
            'password' => bcrypt('password_benar'),
            'role' => 'PIC',
        ]);

        $controller = new AuthController();
        $request = Request::create('/login', 'POST', [
            'email' => 'pic.test@tefa.com',
            'password' => 'password_salah',
        ]);

        $response = $controller->login($request);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('Email atau Password Salah', $response->getContent());
    }

    /**
     * TC-WB-AUTH-004 (Path 4: N1 -> N3 -> N4 -> N5 -> N7 -> N8 -> N9)
     * Menguji decision D4 bernilai True: role Operator atau PIC melakukan bypass 2FA langsung ke dashboard.
     */
    public function test_login_path_4_role_pic_bypass_2fa(): void
    {
        User::factory()->create([
            'email' => 'operator.valid@tefa.com',
            'password' => bcrypt('kopi123'),
            'role' => 'Operator',
        ]);

        $controller = new AuthController();
        $request = Request::create('/login', 'POST', [
            'email' => 'operator.valid@tefa.com',
            'password' => 'kopi123',
        ]);

        $response = $controller->login($request);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertFalse($data['requires_2fa']);
        $this->assertEquals('/dashboard', $data['redirect_url']);
    }

    /**
     * TC-WB-AUTH-005 (Path 5: N1 -> N3 -> N4 -> N5 -> N7 -> N8 -> N10 -> N11)
     * Menguji decision D5 bernilai True: role umum belum pernah setup 2FA (two_factor_secret kosong).
     */
    public function test_login_path_5_setup_2fa_first_time(): void
    {
        // Paksa mode production agar bypass isLocal() tidak aktif
        $this->app->detectEnvironment(fn() => 'production');

        $user = User::factory()->create([
            'email' => 'guest@tefa.com',
            'password' => bcrypt('kopi123'),
            'role' => 'Guest',
            'two_factor_secret' => null,
        ]);

        $controller = new AuthController();
        $request = Request::create('/login', 'POST', [
            'email' => 'guest@tefa.com',
            'password' => 'kopi123',
        ]);

        $response = $controller->login($request);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($data['requires_2fa_setup']);
        $this->assertNotEmpty($data['secret_key']);
        $this->assertEquals($user->id, $data['user_id']);
    }

    /**
     * TC-WB-AUTH-006 (Path 6: N1 -> N3 -> N4 -> N5 -> N7 -> N8 -> N10 -> N12)
     * Menguji decision D5 bernilai False: role umum sudah punya two_factor_secret (meminta 6 digit OTP).
     */
    public function test_login_path_6_prompt_otp(): void
    {
        $this->app->detectEnvironment(fn() => 'production');

        $user = User::factory()->create([
            'email' => 'guest.mfa@tefa.com',
            'password' => bcrypt('kopi123'),
            'role' => 'Guest',
            'two_factor_secret' => 'DUMMYSECRETKEY123',
        ]);

        $controller = new AuthController();
        $request = Request::create('/login', 'POST', [
            'email' => 'guest.mfa@tefa.com',
            'password' => 'kopi123',
        ]);

        $response = $controller->login($request);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($data['requires_2fa']);
        $this->assertEquals($user->id, $data['user_id']);
    }

    // =========================================================================
    // WB-AUTH-02: verify2fa() — Basis Paths P1 s.d. P4 (Cyclomatic Complexity V(G)=4)
    // =========================================================================

    /**
     * TC-WB-AUTH-007 (Path 1: N1 -> N2)
     * Menguji decision D1 bernilai True: input OTP tidak 6 digit atau user_id kosong.
     */
    public function test_verify2fa_path_1_validation_error(): void
    {
        $controller = new AuthController();
        $request = Request::create('/verify-2fa', 'POST', [
            'otp' => '123', // Bukan 6 digit
            'user_id' => '',
        ]);

        $this->expectException(ValidationException::class);
        $controller->verify2fa($request);
    }

    /**
     * TC-WB-AUTH-008 (Path 2: N1 -> N3 -> N4 -> N5)
     * Menguji decision D2 bernilai True: user_id tidak ditemukan di database (HTTP 404).
     */
    public function test_verify2fa_path_2_user_not_found(): void
    {
        $controller = new AuthController();
        $request = Request::create('/verify-2fa', 'POST', [
            'otp' => '123456',
            'user_id' => 999999, // ID tidak ada
        ]);

        $response = $controller->verify2fa($request);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('User tidak ditemukan', $response->getContent());
    }

    /**
     * TC-WB-AUTH-009 (Path 3: N1 -> N3 -> N4 -> N6 -> N7 -> N9)
     * Menguji decision D3 bernilai False: user ada, tetapi kode OTP salah / kadaluarsa (HTTP 422).
     */
    public function test_verify2fa_path_3_invalid_otp(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => Google2FA::generateSecretKey(),
        ]);

        $controller = new AuthController();
        $request = Request::create('/verify-2fa', 'POST', [
            'otp' => '000000', // OTP salah
            'user_id' => $user->id,
        ]);

        $response = $controller->verify2fa($request);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('Kode OTP Salah atau Kadaluarsa', $response->getContent());
    }

    /**
     * TC-WB-AUTH-010 (Path 4: N1 -> N3 -> N4 -> N6 -> N7 -> N8)
     * Menguji decision D3 bernilai True: user ada dan kode OTP cocok (HTTP 200 Login Berhasil).
     */
    public function test_verify2fa_path_4_valid_otp_success(): void
    {
        $secret = Google2FA::generateSecretKey();
        $validOtp = Google2FA::getCurrentOtp($secret);

        $user = User::factory()->create([
            'two_factor_secret' => $secret,
        ]);

        $controller = new AuthController();
        $request = Request::create('/verify-2fa', 'POST', [
            'otp' => $validOtp,
            'user_id' => $user->id,
        ]);

        $response = $controller->verify2fa($request);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Login Berhasil!', $data['message']);
        $this->assertAuthenticatedAs($user);
    }
}

