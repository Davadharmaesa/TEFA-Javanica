<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - TEFA Javanica</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    
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

        [x-cloak] { display: none !important; }
        
        body {
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
        }

        .slide-in { animation: slideIn 0.3s ease-out forwards; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
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
                        primary: { DEFAULT: "hsl(var(--primary))", foreground: "hsl(var(--primary-foreground))" },
                        secondary: { DEFAULT: "hsl(var(--secondary))", foreground: "hsl(var(--secondary-foreground))" },
                        accent: { DEFAULT: "hsl(var(--accent))", foreground: "hsl(var(--accent-foreground))" },
                        card: { DEFAULT: "hsl(var(--card))", foreground: "hsl(var(--card-foreground))" },
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

    <div class="w-full max-w-md bg-card border border-border shadow-xl rounded-2xl overflow-hidden transition-all duration-700">
        
        <!-- Header Banner -->
        <div class="bg-foreground p-8 text-center relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/></svg>
            </div>
            
            <div class="inline-flex items-center justify-center p-3 bg-white/10 rounded-xl mb-4 backdrop-blur-sm border border-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg>
            </div>
            <h1 class="text-2xl font-heading font-bold text-white mb-2 uppercase tracking-wide">TEFA Javanica</h1>
            <p class="text-white/80 font-sans text-sm">Industrial Roastery Management System</p>
        </div>

        <div class="p-8">
            <!-- Alerts -->
            <template x-if="error">
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-md text-sm text-center slide-in" x-text="error"></div>
            </template>

            <!-- Step 1: Login -->
            <div x-show="step === 'LOGIN'" x-transition.opacity.duration.300ms>
                <form @submit.prevent="submitLogin" class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Email Address</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </span>
                            <input type="email" x-model="email" class="w-full pl-10 pr-4 py-2 bg-background border border-border rounded-md text-sm text-foreground font-sans focus:outline-none focus:ring-1 focus:ring-accent transition-all" placeholder="misal: pakdeka@tefa.com" required>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground">Password</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input type="password" x-model="password" class="w-full pl-10 pr-4 py-2 bg-background border border-border rounded-md text-sm text-foreground font-sans focus:outline-none focus:ring-1 focus:ring-accent transition-all" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" :disabled="loading" class="w-full py-2.5 bg-primary text-white font-medium rounded-md hover:bg-opacity-90 transition-all shadow-sm flex justify-center items-center">
                        <span x-show="!loading">Sign In</span>
                        <span x-show="loading" x-cloak class="animate-pulse">Loading...</span>
                    </button>
                </form>
            </div>

            <!-- Step 2: MFA -->
            <div x-show="step === 'MFA'" x-cloak class="slide-in">
                <form @submit.prevent="submitMfa" class="space-y-5">
                    <div class="text-center mb-6">
                        <h3 class="font-bold text-foreground mb-2 italic tracking-tighter uppercase">Two-Factor Authentication</h3>
                        <p class="text-xs text-muted-foreground">
                            Please enter the 6-digit code sent to your authenticator app.
                        </p>
                    </div>
                    
                    <template x-if="qrUrl">
                        <div class="mb-4 text-center p-4 bg-background border-2 border-dashed border-primary/20 rounded-xl">
                            <p class="text-[10px] text-primary font-bold mb-2">SCAN BARCODE INI DENGAN HP ANDA</p>
                            <div x-html="qrUrl" class="mx-auto w-40 h-40 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full border border-border rounded-lg p-2 bg-white"></div>
                            <p class="text-[10px] text-muted-foreground mt-2 font-mono uppercase tracking-widest">Secret: <span x-text="secretKey"></span></p>
                        </div>
                    </template>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-foreground text-center block uppercase tracking-tighter">Authentication Code</label>
                        <input type="text" x-model="otp" maxlength="6" class="w-full text-center tracking-[0.5em] font-mono text-2xl py-3 bg-background border border-border rounded-md text-foreground focus:outline-none focus:ring-2 focus:ring-primary transition-all" placeholder="000000" required>
                    </div>

                    <button type="submit" :disabled="loading" class="w-full py-2.5 bg-primary text-white font-medium rounded-md hover:bg-opacity-90 transition-all shadow-sm mt-2">
                        <span x-show="!loading">Verify & Login</span>
                        <span x-show="loading" x-cloak class="animate-pulse">Verifying...</span>
                    </button>
                    <button type="button" @click="step = 'LOGIN'" class="w-full py-2 text-sm text-muted-foreground hover:text-foreground transition-all">
                        Back to Login
                    </button>
                </form>
            </div>

            <div class="mt-8 pt-6 border-t border-border text-center">
                <p class="text-xs text-muted-foreground">
                    Authorized Personnel Only. All access is logged.
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

                async submitLogin() {
                    this.error = ''; this.loading = true;
                    try {
                        const response = await fetch('/api/v1/login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ email: this.email, password: this.password })
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
                            } else {
                                // Menerima URL tujuan dari API backend (jika ada), 
                                // kalau tidak ada otomatis lempar ke /dashboard
                                window.location.href = data.redirect_url ? data.redirect_url : '/dashboard';
                            }
                        } else {
                            this.error = data.message || 'Email atau Password salah.';
                        }
                    } catch (e) {
                        this.error = 'Terjadi kesalahan jaringan.';
                    } finally {
                        this.loading = false;
                    }
                },

                async submitMfa() {
                    this.error = ''; this.loading = true;
                    try {
                        const response = await fetch('/api/v1/verify-2fa', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ user_id: this.userId, otp: this.otp })
                        });
                        const data = await response.json();
                        if (response.ok) {
                            // Sama seperti di atas, biarkan backend yang menentukan arah url-nya
                            window.location.href = data.redirect_url ? data.redirect_url : '/dashboard';
                        } else {
                            this.error = data.message || 'Kode OTP Salah!';
                        }
                    } catch (e) {
                        this.error = 'Terjadi kesalahan jaringan.';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
