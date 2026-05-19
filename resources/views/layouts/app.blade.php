<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TEFA Javanica')</title>
    
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
        }
    </script>
</head>
<body class="antialiased" x-data="{ isCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }">

    <div class="flex h-screen overflow-hidden">
        
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
