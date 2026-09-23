<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: (() => { try { return localStorage.getItem('theme') === 'dark'; } catch(e) { return false; } })() }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TEFA Javanica')</title>

    <!--
        Anti-FOUC: Script ini HARUS berada di sini (sebelum Tailwind & Alpine)
        agar browser bisa menerapkan state sidebar sebelum merender elemen apapun.
    -->
    <script>
        (function() {
            try {
                var s = localStorage.getItem('sidebarCollapsed');
                var collapsed = (s !== null) ? (s === 'true') : (window.innerWidth < 1024);
                // Simpan ke localStorage agar state konsisten di setiap halaman
                localStorage.setItem('sidebarCollapsed', String(collapsed));
                if (collapsed && window.innerWidth >= 768) {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch(e) {
                if (window.innerWidth >= 768 && window.innerWidth < 1024) {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            }
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Exact HSL values from Javanica2 (Roastery System 2) globals.css */
            --background: 60 9% 96%;
            --foreground: 31 100% 15%; /* Dark Espresso */
            --card: 0 0% 100%;
            --card-foreground: 31 100% 15%;
            --primary: 20 52% 56%;      /* Terracotta (Coklat Muda) - Mapping from Javanica2 */
            --secondary: 96 24% 34%;   /* Leafy Green */
            --success: 105 45% 45%;    /* Brighter Green for Content */
            --accent: 31 38% 53%;      /* Natural Wicker */
            --muted: 31 20% 90%;
            --muted-foreground: 31 40% 40%;
            --border: 31 38% 53%;
            --input: 31 38% 53%;
            --ring: 20 52% 56%;
            --radius: 0.5rem;
        }

        .dark {
            --background: 18 18% 13%; /* #281f1c */
            --foreground: 40 40% 83%; /* #e6dac3 */
            --card: 18 18% 18%;       /* #362a26 */
            --card-foreground: 40 40% 83%;
            --primary: 17 48% 60%;    /* #ca8569 */
            --secondary: 96 24% 34%;  /* Original Leafy Green */
            --success: 105 45% 55%;   /* Sharper Green for Dark Mode */
            --accent: 17 48% 60%;
            --muted: 18 18% 25%;      /* #4a3a34 */
            --muted-foreground: 17 48% 60%;
            --border: 18 18% 25%;     /* #4a3a34 */
            --input: 18 18% 25%;
            --ring: 17 48% 60%;
        }

        [x-cloak] { display: none !important; }
        
        body {
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
        }

        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: hsl(var(--muted)); border-radius: 10px; }

        /* Sembunyikan spinner bawaan browser untuk input type="number" */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }

        /* ====================================================
           Anti-FOUC: State sidebar collapsed sebelum Alpine.js aktif
           Diterapkan via class pada <html> yang di-set di <head>
           ====================================================
        */
        @media (min-width: 768px) {
            html.sidebar-collapsed aside {
                width: 5rem !important; /* Setara w-20 Tailwind */
            }
            html.sidebar-collapsed aside h1,
            html.sidebar-collapsed aside p.text-muted-foreground.font-mono,
            html.sidebar-collapsed aside nav a span,
            html.sidebar-collapsed aside div.flex-1.whitespace-nowrap,
            html.sidebar-collapsed aside form button span {
                display: none !important;
            }
            html.sidebar-collapsed aside nav a,
            html.sidebar-collapsed aside div.p-3.space-y-3 > div,
            html.sidebar-collapsed aside form button {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
                justify-content: center !important;
            }
        }
    </style>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        border: "hsl(var(--border))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        primary: {
                            DEFAULT: "hsl(var(--primary))",
                            foreground: "#ffffff"
                        },
                        secondary: {
                            DEFAULT: "hsl(var(--secondary))",
                            foreground: "#ffffff"
                        },
                        success: {
                            DEFAULT: "hsl(var(--success))",
                            foreground: "#ffffff"
                        },
                        accent: "hsl(var(--accent))",
                        surface: "hsl(var(--card))",
                        card: "hsl(var(--card))",
                        muted: {
                            DEFAULT: "hsl(var(--muted))",
                            foreground: "hsl(var(--muted-foreground))",
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Montserrat', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace']
                    },
                }
            }
        };
    </script>
    <script>
        window.formatRupiahString = function(value) {
            if (value === null || value === undefined || value === '') return '';
            let valStr = value.toString().replace(/\D/g, '');
            if (!valStr) return '';
            return valStr.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        };

        window.parseRupiahString = function(value) {
            if (value === null || value === undefined || value === '') return 0;
            let clean = value.toString().replace(/\D/g, '');
            return clean ? parseInt(clean, 10) : 0;
        };
    </script>
</head>
<body class="antialiased" x-data="{
    isCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
    isMobileOpen: false
}">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Mobile Backdrop -->
        <div 
            x-show="isMobileOpen" 
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="isMobileOpen = false"
            class="fixed inset-0 bg-black/50 z-40 md:hidden"
            x-cloak
        ></div>

        <!-- SIDEBAR -->
        @include('layout.sidebar')

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- HEADER -->
            @include('layout.header')

            <!-- MAIN CONTENT -->
            <main class="flex-1 overflow-y-auto p-6 md:p-8 lg:p-10 custom-scrollbar">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>

    </div>

    @stack('scripts')
</body>
</html>
