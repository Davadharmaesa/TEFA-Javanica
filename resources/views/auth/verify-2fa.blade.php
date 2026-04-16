@extends('layouts.app') {{-- Atau layout login kamu --}}

@section('content')
<div class="min-h-screen flex items-center justify-center bg-background">
    <div class="max-w-md w-full bg-card p-8 rounded-xl shadow-lg border border-border">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-heading font-bold text-primary uppercase tracking-wider">Verifikasi Keamanan</h2>
            <p class="text-muted-foreground text-sm mt-2 font-sans">
                Buka aplikasi Google Authenticator di HP kamu dan masukkan 6 digit kode yang muncul.
            </p>
        </div>

        <form action="{{ route('verify.2fa') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-primary mb-2">Kode OTP (6 Digit)</label>
                <input 
                    type="text" 
                    name="otp" 
                    maxlength="6" 
                    placeholder="Contoh: 123456"
                    class="w-full px-4 py-3 rounded-md border border-border bg-background text-center text-2xl font-mono tracking-[0.5em] focus:ring-2 focus:ring-primary focus:outline-none transition-all"
                    required
                    autofocus
                >
                @error('otp')
                    <p class="text-red-500 text-xs mt-2 italic">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full py-3 bg-secondary text-white font-bold rounded-md hover:bg-opacity-90 transition-all shadow-md uppercase tracking-widest text-xs">
                Verifikasi Sekarang
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="/login" class="text-xs text-muted-foreground hover:text-primary transition-colors italic">
                Kembali ke halaman Login
            </a>
        </div>
    </div>
</div>
@endsection