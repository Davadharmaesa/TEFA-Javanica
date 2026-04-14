<!DOCTYPE html>
<html lang="id" class="h-full bg-[#fdfbf9]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TEFA JAVANICA Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Import Google Fonts API (Outfit or Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #332d2b;
        }
    </style>
</head>
<body class="h-full flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-[#f3eee9] flex flex-col shrink-0">
        <!-- Logo Area -->
        <div class="h-20 flex items-center px-6 gap-3 shrink-0">
            <div class="bg-[#CA8569] text-white p-2 rounded-lg flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> <!-- Placeholder for Coffee Cup Icon -->
            </div>
            <div>
                <h1 class="text-sm font-bold text-[#b57357] leading-tight tracking-wide">TEFA JAVANICA</h1>
                <p class="text-xs text-[#b57357]/70 font-medium">Roastery Div.</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <a href="#" class="flex items-center gap-3 px-4 py-3 bg-[#4c6643] text-white rounded-xl font-medium shadow-sm transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Beranda
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-[#b57357] hover:bg-[#fcf7f4] rounded-xl font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                Lab Produksi
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-[#b57357] hover:bg-[#fcf7f4] rounded-xl font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Gudang
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-[#b57357] hover:bg-[#fcf7f4] rounded-xl font-medium transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Input Transaksi
            </a>
        </nav>

        <!-- User & Logout -->
        <div class="p-4 space-y-2 shrink-0">
            <div class="flex items-center gap-3 px-3 py-2 bg-[#fcf7f4] border border-[#f3eee9] rounded-xl">
                <div class="bg-[#CA8569]/30 text-[#CA8569] rounded-full p-1.5 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="flex-1 overflow-hidden">
                    <p class="text-xs font-bold text-[#b57357] truncate">Pak Deka (Roastery...)</p>
                    <p class="text-[10px] text-[#b57357]/80">Operator</p>
                </div>
            </div>
            <a href="#" class="flex px-4 py-2.5 items-center justify-center gap-2 bg-[#FDEAE9] text-[#de5b58] font-semibold text-sm rounded-xl hover:bg-[#fad8d7] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Keluar
            </a>
        </div>
    </aside>

    <!-- Main Section Wrapper -->
    <div class="flex-1 flex flex-col h-full overflow-hidden">
        
        <!-- Topbar -->
        <header class="h-20 bg-white border-b border-[#f3eee9] flex items-center justify-between px-8 shrink-0">
            <!-- Search -->
            <div class="flex-1 min-w-0 max-w-lg">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path></svg>
                    </div>
                    <input type="text" class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl leading-5 bg-[#fdfbf9] placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-[#CA8569] focus:border-[#CA8569] sm:text-sm transition duration-150 ease-in-out" placeholder="Cari data stok, batch, atau laporan...">
                </div>
            </div>

            <!-- Right Actions -->
            <div class="ml-6 flex items-center gap-6 shrink-0">
                <!-- Theme toggle placeholder -->
                <button class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </button>
                <!-- Notification -->
                <button class="text-gray-400 hover:text-gray-500 relative">
                    <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400 ring-2 ring-white"></span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </button>
                <!-- Profile -->
                <div class="flex items-center gap-3 pl-6 border-l border-gray-200">
                    <div class="text-right">
                        <p class="text-sm font-bold text-[#b57357] leading-tight">Budi Coordinator</p>
                        <p class="text-[11px] font-semibold text-gray-500">Role: Admin</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-[#CA8569] text-white flex items-center justify-center font-bold shadow-sm">
                        BC
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto w-full p-8 relative">
            {{ $slot }}
        </main>
        
    </div>
</body>
</html>
