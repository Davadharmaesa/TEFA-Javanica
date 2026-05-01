<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        // 1. Cek User & Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau Password Salah!'], 422);
        }

        // 2. Login secara Session (Web)
        Auth::login($user);

        // 3. Jika Operator tanpa MFA (VIP), langsung masuk
        // Sesuaikan role-nya di sini
        if (app()->isLocal() || strtolower($user->role) === 'operator' || strtolower($user->role) === 'pic') {

            $user->update(["two_factor_confirmed_at" => now()]);
            return response()->json([
                'requires_2fa' => false,
                'redirect_url' => '/dashboard'
            ], 200);
        }

        // 4. Skenario A: Belum setup 2FA
        if (empty($user->two_factor_secret)) {
            $secret = Google2FA::generateSecretKey();
            $user->update(['two_factor_secret' => $secret]);

            $qrCodeInline = Google2FA::getQRCodeInline(
                config('app.name'),
                $user->email,
                $secret
            );

            // Tampilkan halaman setup (pastikan file view-nya ada)
            return response()->json([
                'requires_2fa_setup' => true,
                'qr_code_url' => $qrCodeInline,
                'secret_key' => $secret,
                'user_id' => $user->id
            ]);
        }

        // 5. Skenario B: Sudah setup, minta kode OTP
        // Tampilkan halaman input 6 angka
        return response()->json([
            'requires_2fa' => true,
            'user_id' => $user->id
        ]);
    }

    public function verify2fa(Request $request)
    {
        // Validasi input
        $request->validate([
            'otp' => 'required|digits:6',
            'user_id' => 'required'
        ]);

        // Ambil user berdasarkan ID yang dikirim Alpine
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan!'], 404);
        }

        // Cek OTP pakai Google2FA
        $valid = Google2FA::verifyKey($user->two_factor_secret, $request->otp);

        if ($valid) {
            // LOGIN KAN SECARA RESMI DI SINI
            Auth::login($user);

            // Tandai sudah dikonfirmasi
            $user->update(["two_factor_confirmed_at" => now()]);

            // KIRIM RESPONS JSON (Ini yang bikin Alpine seneng)
            return response()->json([
                'message' => 'Login Berhasil!',
                'redirect_url' => route('dashboard')
            ], 200);
        }

        // Jika salah
        return response()->json(['message' => 'Kode OTP Salah atau Kadaluarsa!'], 422);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Logout Berhasil  !']);
        }

        return redirect('/login');
    }
}
