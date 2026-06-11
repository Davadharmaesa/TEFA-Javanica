<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In - TEFA Javanica</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --background: 60 9% 96%;
            --foreground: 31 100% 15%;
            --card: 0 0% 100%;
            --card-foreground: 31 100% 15%;
            --primary: 20 52% 56%;
            --primary-foreground: 0 0% 100%;
            --secondary: 96 24% 34%;
            --secondary-foreground: 0 0% 100%;
            --accent: 31 38% 53%;
            --accent-foreground: 0 0% 100%;
            --border: 31 38% 53%;
            --input: 31 38% 53%;
            --ring: 20 52% 56%;
            --radius: 0.5rem;
        }

        [x-cloak] {
            display: none !important;
        }

        body {
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
            background-image: radial-gradient(hsl(var(--primary)/0.05) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .slide-in {
            animation: slideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        border: "hsl(var(--border))",
                        input: "hsl(var(--input))",
                        ring: "hsl(var(--ring))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        primary: {
                            DEFAULT: "hsl(var(--primary))",
                            foreground: "hsl(var(--primary-foreground))"
                        },
                        secondary: {
                            DEFAULT: "hsl(var(--secondary))",
                            foreground: "hsl(var(--secondary-foreground))"
                        },
                        accent: {
                            DEFAULT: "hsl(var(--accent))",
                            foreground: "hsl(var(--accent-foreground))"
                        },
                        card: {
                            DEFAULT: "hsl(var(--card))",
                            foreground: "hsl(var(--card-foreground))"
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Montserrat', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>

<body class="min-h-screen flex items-center justify-center p-4 antialiased" x-data="loginComponent()">

    <div class="w-full max-w-md bg-card border-2 border-border/20 shadow-2xl rounded-3xl overflow-hidden transition-all duration-700 relative">
        
        <!-- Decoration -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-secondary/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Header Banner -->
        <div class="bg-primary p-8 text-center relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white transform rotate-12">
                    <rect width="20" height="8" x="2" y="2" rx="2" ry="2" />
                    <rect width="20" height="8" x="2" y="14" rx="2" ry="2" />
                    <line x1="6" x2="6.01" y1="6" y2="6" />
                    <line x1="6" x2="6.01" y1="18" y2="18" />
                </svg>
            </div>

            <div class="inline-flex items-center justify-center p-3.5 bg-white/10 rounded-2xl mb-4 backdrop-blur-md border border-white/20 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                    <path d="M10 2v2" />
                    <path d="M14 2v2" />
                    <path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1" />
                    <path d="M6 2v2" />
                </svg>
            </div>
            <h1 class="text-3xl font-heading font-black text-white mb-1 tracking-tight">TEFA Javanica</h1>
            <p class="text-white/80 font-sans text-xs font-semibold tracking-widest uppercase">Industrial Roastery System</p>
        </div>

        <div class="p-8 relative z-10">
            <!-- Alerts -->
            <template x-if="error">
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 text-red-700 rounded-xl text-sm font-bold flex items-start gap-3 slide-in shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                    <span x-text="error" class="leading-relaxed"></span>
                </div>
            </template>

            <!-- Step 1: Login -->
            <div x-show="step === 'LOGIN'" x-transition.opacity.duration.300ms>
                <form @submit.prevent="submitLogin" class="space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Email Address</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-primary/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="20" height="16" x="2" y="4" rx="2" />
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                </svg>
                            </span>
                            <input type="email" x-model="email" class="w-full pl-12 pr-4 py-3 bg-background border border-border/60 rounded-xl text-sm text-primary font-bold focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)]" placeholder="misal: pakdeka@tefa.com" required autofocus>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Password</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-primary/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                            </span>
                            <input type="password" x-model="password" class="w-full pl-12 pr-4 py-3 bg-background border border-border/60 rounded-xl text-sm text-primary font-mono focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)]" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" :disabled="loading" class="w-full py-3.5 bg-secondary text-secondary-foreground font-black text-sm uppercase tracking-widest rounded-xl hover:bg-secondary/90 disabled:opacity-70 disabled:cursor-not-allowed transition-all shadow-[0_8px_20px_rgba(100,130,80,0.2)] hover:-translate-y-0.5 active:translate-y-0 flex justify-center items-center mt-4">
                        <span x-show="!loading">Sign In</span>
                        <span x-show="loading" x-cloak class="animate-pulse flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Authorizing...
                        </span>
                    </button>
                </form>
            </div>

            <!-- Step 2: MFA -->
            <div x-show="step === 'MFA'" x-cloak class="slide-in">
                <form @submit.prevent="submitMfa" class="space-y-6">
                    <div class="text-center mb-6">
                        <div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center mx-auto mb-3 text-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <h3 class="font-heading font-black text-xl text-primary mb-1">Verifikasi Dua Langkah</h3>
                        <p class="text-xs font-medium text-muted-foreground">
                            Masukkan 6 digit kode dari aplikasi Authenticator Anda.
                        </p>
                    </div>

                    <template x-if="qrUrl">
                        <div class="mb-6 text-center p-5 bg-background border-2 border-dashed border-primary/20 rounded-2xl">
                            <p class="text-[10px] text-primary font-black mb-3 uppercase tracking-widest">Scan Barcode Setup Awal</p>
                            <div x-html="qrUrl" class="mx-auto w-40 h-40 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full border border-border/50 rounded-xl p-3 bg-white shadow-sm"></div>
                            <p class="text-[10px] text-muted-foreground mt-3 font-mono font-bold uppercase tracking-widest bg-muted/50 py-1.5 rounded-md">Secret: <span x-text="secretKey" class="text-primary"></span></p>
                        </div>
                    </template>

                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-primary uppercase tracking-widest text-center block">Kode Autentikasi</label>
                        <!-- REFAKTOR: Hapus default spinner dengan type="text", pakai inputmode numeric -->
                        <input type="text" inputmode="numeric" pattern="[0-9]*" x-ref="otpInput" x-model="otp" maxlength="6" class="w-full text-center tracking-[0.25em] font-mono text-3xl font-black py-4 bg-background border-2 border-border/80 rounded-xl text-primary focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition-all shadow-[inset_0_2px_8px_rgba(0,0,0,0.04)] placeholder:text-muted-foreground/30 placeholder:font-sans placeholder:font-normal placeholder:tracking-normal placeholder:text-lg" placeholder="Ketik 6 Digit Kode" required>
                    </div>

                    <div class="space-y-3 pt-2">
                        <button type="submit" :disabled="loading" class="w-full py-3.5 bg-secondary text-secondary-foreground font-black text-sm uppercase tracking-widest rounded-xl hover:bg-secondary/90 disabled:opacity-70 disabled:cursor-not-allowed transition-all shadow-[0_8px_20px_rgba(100,130,80,0.2)] hover:-translate-y-0.5 active:translate-y-0 flex justify-center items-center">
                            <span x-show="!loading">Verifikasi & Masuk</span>
                            <span x-show="loading" x-cloak class="animate-pulse">Memverifikasi...</span>
                        </button>
                        <button type="button" @click="step = 'LOGIN'" class="w-full py-2.5 text-xs font-bold text-muted-foreground uppercase tracking-widest hover:text-primary transition-colors rounded-xl hover:bg-muted/50">
                            Kembali ke Login
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-10 pt-6 border-t border-border/50 text-center">
                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                    Akses Khusus Internal &bull; Semua Aktivitas Direkam
                </p>
            </div>
        </div>
    </div>

    <script>
        function loginComponent() {
            return {
                step: 'LOGIN',
                email: '',
                password: '',
                otp: '',
                error: '',
                loading: false,
                userId: null,
                qrUrl: null,
                secretKey: null,

                getCsrfToken() {
                    const tag = document.querySelector('meta[name="csrf-token"]');
                    return tag ? tag.getAttribute('content') : '';
                },

                async submitLogin() {
                    this.error = '';
                    this.loading = true;
                    try {
                        const response = await fetch('/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            },
                            body: JSON.stringify({
                                email: this.email,
                                password: this.password
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            if (data.requires_2fa || data.requires_2fa_setup) {
                                this.step = 'MFA';
                                this.userId = data.user_id;
                                if (data.requires_2fa_setup) {
                                    this.qrUrl = data.qr_code_url;
                                    this.secretKey = data.secret_key;
                                }
                                this.$nextTick(() => {
                                    if (this.$refs.otpInput) {
                                        this.$refs.otpInput.focus();
                                    }
                                });
                            } else {
                                window.location.href = '/dashboard';
                            }
                        } else {
                            this.error = data.message || 'Email atau Password salah.';
                        }
                    } catch (e) {
                        console.error('Login Error:', e);
                        this.error = 'Koneksi gagal atau sesi kadaluarsa. Silakan refresh (F5).';
                    } finally {
                        this.loading = false;
                    }
                },

                async submitMfa() {
                    this.error = '';
                    this.loading = true;
                    try {
                        const response = await fetch('/verify-2fa', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken()
                            },
                            body: JSON.stringify({
                                user_id: this.userId,
                                otp: this.otp
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            window.location.href = '/dashboard';
                        } else {
                            this.error = data.message || 'Kode OTP Salah!';
                        }
                    } catch (e) {
                        console.error('MFA Error:', e);
                        this.error = 'Terjadi kesalahan jaringan saat verifikasi.';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>

</html>