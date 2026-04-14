<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TEFA Javanica</title>
    <!-- CSS Jaringan Langsung: Tailwind + Alpine -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        background: '#F5F5F0',
                        primary: '#3C2E28', // Coklat Tua Kopi
                        secondary: '#8B5E34', // Coklat Emas
                        border: '#E2DFD8',
                        muted: '#8A847C'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Outfit', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .slide-in { animation: slideIn 0.4s ease-out forwards; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
</head>
<body class="bg-background min-h-screen flex items-center justify-center p-4 antialiased">

    <!-- Container Pusat Alpine.js -->
    <div x-data="loginComponent()" class="w-full max-w-md bg-white border border-border shadow-xl rounded-2xl overflow-hidden transition-all duration-500">
        
        <!-- Header / Banner Atas -->
        <div class="bg-primary p-8 text-center relative overflow-hidden">
            <!-- Ikon Abstrak di Kanan Atas -->
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <svg xmlns="http://www.w3.org/polaris/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/></svg>
            </div>
            
            <!-- Logo Utama Kopi -->
            <div class="inline-flex items-center justify-center p-3 bg-white/10 rounded-xl mb-4 backdrop-blur-sm border border-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg>
            </div>
            <h1 class="text-2xl font-heading font-bold text-white mb-2">TEFA Javanica</h1>
            <p class="text-white/80 font-sans text-sm">Industrial Roastery Management System</p>
        </div>

        <div class="p-8">
            <!-- Alert Error -->
            <template x-if="error">
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-md text-sm text-center slide-in" x-text="error"></div>
            </template>

            <!-- TAMPILAN 1: FORM LOGIN -->
            <div x-show="step === 'LOGIN'" x-transition.opacity.duration.300ms>
                <form @submit.prevent="submitLogin" class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-primary">Email Address</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </span>
                            <input type="email" x-model="email" class="w-full pl-10 pr-4 py-2 bg-background border border-border rounded-md text-sm text-primary font-sans focus:outline-none focus:ring-1 focus:ring-secondary transition-all" placeholder="misal: buastri@tefa.com" required>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-primary">Password</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input type="password" x-model="password" class="w-full pl-10 pr-4 py-2 bg-background border border-border rounded-md text-sm text-primary font-sans focus:outline-none focus:ring-1 focus:ring-secondary transition-all" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" :disabled="loading" class="w-full py-2.5 bg-secondary text-white font-medium rounded-md hover:bg-secondary/90 transition-colors mt-2 flex justify-center items-center">
                        <span x-show="!loading">Sign In</span>
                        <span x-show="loading" x-cloak>Loading...</span>
                    </button>
                </form>
            </div>

            <!-- TAMPILAN 2: FORM MFA (OTP) -->
            <div x-show="step === 'MFA'" x-cloak class="slide-in">
                <form @submit.prevent="submitMfa" class="space-y-5">
                    <div class="text-center mb-6">
                        <h3 class="font-bold text-primary mb-2">Two-Factor Authentication</h3>
                        <p class="text-sm text-muted">
                            Silakan masukkan 6 digit kode dari aplikasi <b>Google Authenticator</b> Anda.
                        </p>
                    </div>
                    
                    <!-- Alert Jika Gembok Baru Mau Diset (QR Code Mockup) -->
                    <template x-if="qrUrl">
                        <div class="mb-4 text-center">
                            <p class="text-xs text-red-600 font-bold mb-2">SCAN BARCODE INI DENGAN HP ANDA</p>
                            <!-- Nanti saat dicoding asli, URL QR masuk ke tag src image ini -->
                            <div x-html="qrUrl" class="mx-auto w-40 h-40 border-4 border-dashed border-red-500 rounded-xl p-2 flex items-center justify-center bg-white [&>svg]:w-full [&>svg]:h-full"></div>
                            <p class="text-[10px] text-muted mt-2">Secret: <span x-text="secretKey"></span></p>
                        </div>
                    </template>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-primary text-center block">Kode Verifikasi (6 Angka)</label>
                        <input type="text" x-model="otp" maxlength="6" class="w-full text-center tracking-[0.5em] font-mono text-2xl py-3 bg-background border border-border rounded-md text-primary focus:outline-none focus:ring-2 focus:ring-secondary transition-all" placeholder="000000" required>
                    </div>

                    <button type="submit" :disabled="loading" class="w-full py-2.5 bg-primary text-white font-medium rounded-md hover:bg-primary/90 transition-colors mt-2 flex justify-center items-center">
                        <span x-show="!loading">Verify & Login</span>
                        <span x-show="loading" x-cloak>Memproses...</span>
                    </button>
                    <button type="button" @click="step = 'LOGIN'" class="w-full py-2 text-sm text-muted hover:text-primary transition-colors">
                        Kembali ke Layar Awal
                    </button>
                </form>
            </div>

            <div class="mt-8 pt-6 border-t border-border text-center">
                <p class="text-xs text-muted">
                    Authorized Personnel Only. All access is logged.
                </p>
            </div>
        </div>
    </div>

    <!-- Logika Interaksi Super Ringan (Alpine JS) -->
    <script>
        function loginComponent() {
            return {
                step: 'LOGIN', // 'LOGIN' atau 'MFA'
                email: '',
                password: '',
                otp: '',
                error: '',
                loading: false,
                userId: null,
                qrUrl: null, // Untuk menampilkan gambar QR jika baru pertama kali login
                secretKey: null,

                async submitLogin() {
                    this.error = '';
                    this.loading = true;

                    try {
                        const response = await fetch('/api/v1/login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ email: this.email, password: this.password })
                        });

                        const data = await response.json();
                        
                        // Simulasi Multi Role Sementara & Keberhasilan Auth:
                        if (response.ok) {
                            if (data.requires_2fa || data.requires_2fa_setup) {
                                // Lempar ke halaman OTP
                                this.step = 'MFA';
                                this.userId = data.user_id;

                                // Kalau butuh Set-up Pertama kali
                                if (data.requires_2fa_setup) {
                                    this.qrUrl = data.qr_code_url;
                                    this.secretKey = data.secret_key;
                                } else {
                                    this.qrUrl = null;
                                }
                            } else {
                                // Misal untuk Mahasiswa Praktikum (Akses Langsung)
                                window.location.href = '/dashboard';
                            }
                        } else {
                            this.error = data.message || 'Login Gagal, Kredensial tidak cocok.';
                        }
                    } catch (e) {
                        this.error = 'Terjadi kesalahan jaringan ke server API.';
                    } finally {
                        this.loading = false;
                    }
                },

                async submitMfa() {
                    if (this.otp.length !== 6) {
                        this.error = 'Kode wajib 6 Angka!';
                        return;
                    }

                    this.error = '';
                    this.loading = true;

                    try {
                        const response = await fetch('/api/v1/verify-2fa', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ user_id: this.userId, otp: this.otp })
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            // Sukses Total!
                            window.location.href = '/dashboard';
                        } else {
                            this.error = data.message || 'OTP Salah atau Kadaluarsa!';
                        }
                    } catch (e) {
                        this.error = 'Terjadi kesalahan jaringan ke server API.';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
