<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
// TODO Programmer: Pastikan kalian sudah install package Google2FA ya!
// composer require pragmarx/google2fa-laravel bacon/bacon-qr-code
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class AuthController extends Controller
{
    /**
     * Langkah 1: Pak Deka memasukkan Email & Password
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        // Cek kecocokan password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau Password salah'], 401);
        }

        // ==========================================
        // LOGIKA HYBRID: Cek Role User
        // ==========================================
        if ($user->role === 'mahasiswa') {
            // Jalur VIP Mahasiswa: Langsung Berikan Token (Tanpa MFA)
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login Berhasil (Mahasiswa)!',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ]
            ], 200);
        }

        // Skenario A: Pak Deka belum pernah setup HP (Baru pertama kali login)
        if (empty($user->two_factor_secret)) {
            try {
                // Buatkan Secret Rahasia
                $secret = Google2FA::generateSecretKey();
                
                // Simpan ke database
                $user->update(['two_factor_secret' => $secret]);

                // Bangun Gambar Barcode SVG agar langsung tampil di layar HTML
                $qrCodeInline = Google2FA::getQRCodeInline(
                    config('app.name'),
                    $user->email,
                    $secret
                );

                return response()->json([
                    'message' => 'Setup Google Authenticator Required',
                    'requires_2fa_setup' => true,
                    'secret_key' => $secret,
                    'qr_code_url' => $qrCodeInline,
                    'user_id' => $user->id
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Gagal membuat QR Code: ' . $e->getMessage()
                ], 500);
            }
        }

        // Skenario B: Sudah pernah setup, menagih 6 Angka
        return response()->json([
            'message' => 'Masukkan 6 Digit Angka dari Hape Anda',
            'requires_2fa' => true,
            'user_id' => $user->id
        ], 200);
    }

    /**
     * Langkah 2: Pak Deka Menyerahkan 6 Angka dari Hape (OTP)
     */
    public function verify2fa(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'otp' => 'required|digits:6'
        ]);

        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // Validasi Matematis OTP
        $valid = Google2FA::verifyKey($user->two_factor_secret, $request->otp);

        if ($valid) {
            // Tandai bahwa HP sudah resmi tersinkronisasi jika ini login pertama
            if (is_null($user->two_factor_confirmed_at)) {
                $user->two_factor_confirmed_at = now();
                $user->save();
            }

            // Hancurkan Token Lama agar tidak dobel sesi
            $user->tokens()->delete();

            // Berikan Akses Brankas (Token Sanctum)
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login Berhasil!',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ], 200);
        }

        return response()->json(['message' => 'Kode OTP Salah, jangan ngawur!'], 401);
    }

    /**
     * Langkah 3: Auto-Logout saat Jam 11.30 (Menghancurkan Token)
     */
    public function logout(Request $request)
    {
        // Fitur ini harus diakses dengan Header: Authorization Bearer (Token)
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesi Berakhir. Silakan Pulang atau Istirahat.'], 200);
    }
}
