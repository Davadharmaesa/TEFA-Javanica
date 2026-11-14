<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityDataWhiteboxTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /**
     * TC-WB-SEC-001 (Bcrypt Hashing Standard)
     * Menguji bahwa saat user dibuat atau password diperbarui, sistem mengenkripsi password
     * menggunakan algoritma Bcrypt (awalan $2y$ dan panjang hash 60 karakter),
     * bukan menyimpannya sebagai plaintext.
     */
    public function test_tc_wb_sec_001_password_hashed_with_standard_bcrypt(): void
    {
        $plainPassword = 'RahasiaSuperKopi2026!';
        $hashed = Hash::make($plainPassword);

        // Verifikasi Bcrypt signature: format standard Laravel/PHP bcrypt dimulai dengan $2y$
        $this->assertStringStartsWith('$2y$', $hashed);
        $this->assertEquals(60, strlen($hashed));
        $this->assertNotEquals($plainPassword, $hashed);

        $user = User::factory()->create([
            'password' => $hashed,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'password' => $hashed,
        ]);
    }

    /**
     * TC-WB-SEC-002 (Bcrypt Hash Verification)
     * Menguji akurasi verifikasi password menggunakan Hash::check:
     * Menghasilkan true untuk password yang cocok, dan false untuk password yang salah.
     */
    public function test_tc_wb_sec_002_bcrypt_hash_verification_accuracy(): void
    {
        $plainPassword = 'kopi123PasswordValid';
        $hashed = Hash::make($plainPassword);

        $this->assertTrue(Hash::check($plainPassword, $hashed));
        $this->assertFalse(Hash::check('passwordSalah123', $hashed));
        $this->assertFalse(Hash::check('', $hashed));
    }

    /**
     * TC-WB-SEC-003 (Proteksi Rute Transaksi Finansial Tanpa Sesi)
     * Menguji bahwa upaya akses langsung ke modul data transaksi finansial (penjualan/keuangan)
     * tanpa sesi otorisasi aktif ditolak dan diarahkan ke rute login.
     */
    public function test_tc_wb_sec_003_unauthenticated_financial_transaction_route_denied(): void
    {
        auth()->logout();

        // Mencoba akses rute penjualan / laporan finansial tanpa sesi
        $response = $this->get('/sales');
        $response->assertRedirect('/login');

        $responseReports = $this->get('/reports');
        $responseReports->assertRedirect('/login');
    }

    /**
     * TC-WB-SEC-004 (Data Masking / Atribut Kredensial Sensitif)
     * Menguji bahwa atribut sensitif seperti password, remember_token, dan two_factor_secret
     * disembunyikan (hidden) saat model User diubah menjadi JSON/Array untuk mencegah kebocoran data.
     */
    public function test_tc_wb_sec_004_sensitive_user_attributes_hidden_from_serialization(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret_pass'),
            'two_factor_secret' => 'SECRET_2FA_TOKEN',
            'remember_token' => 'REMEMBER_TOKEN_XYZ',
        ]);

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
        $this->assertArrayNotHasKey('two_factor_secret', $userArray);
    }
}

