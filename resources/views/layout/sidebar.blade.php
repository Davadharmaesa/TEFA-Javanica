<!-- Backdrop Gelap Mobile -->
<div
    x-show="isMobileOpen"
    x-cloak
    @click="isMobileOpen = false"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-30 md:hidden transition-opacity duration-300"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"></div>

<aside
    x-init="$nextTick(() => { $el.classList.remove('sidebar-pre-collapsed') })"
    class="flex flex-col bg-card shadow-md h-full z-40 fixed inset-y-0 left-0 md:relative md:translate-x-0 transition-all duration-300 ease-in-out border-r border-border w-64 md:flex-shrink-0"
    :class="[
        isMobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
        isCollapsed ? 'md:w-20' : 'md:w-56'
    ]">

    <style>
        /* Mencegah glitch teks meluber saat render pertama kali sebelum Alpine.js aktif */
        aside.sidebar-pre-collapsed {
            width: 5rem !important;
            /* Setara w-20 */
        }

        aside.sidebar-pre-collapsed h1,
        aside.sidebar-pre-collapsed p.text-muted-foreground.font-mono,
        aside.sidebar-pre-collapsed nav a span,
        aside.sidebar-pre-collapsed div.flex-1.whitespace-nowrap,
        aside.sidebar-pre-collapsed form button span {
            display: none !important;
        }

        aside.sidebar-pre-collapsed nav a,
        aside.sidebar-pre-collapsed div.p-3.space-y-3>div,
        aside.sidebar-pre-collapsed form button {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            justify-content: center !important;
        }
    </style>

    <!-- Toggle Button (Desktop/Tablet only) -->
    <button
        @click="isCollapsed = !isCollapsed; try { localStorage.setItem('sidebarCollapsed', String(isCollapsed)) } catch (e) {}"
        class="hidden md:block absolute -right-3 top-8 bg-secondary text-secondary-foreground rounded-full p-1 shadow-md hover:bg-secondary/90 transition-transform hover:scale-110 z-30">
        <template x-if="isCollapsed">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 18 6-6-6-6" />
            </svg>
        </template>
        <template x-if="!isCollapsed">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6" />
            </svg>
        </template>
    </button>

    <div class="flex items-center h-20 transition-all duration-300" :class="isCollapsed ? 'justify-center px-0' : 'px-6'">
        <div class="flex items-center transition-all duration-300" :class="isCollapsed ? 'justify-center gap-0' : 'w-full gap-3 justify-between'">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 flex items-center justify-center w-9 h-9 bg-white/10 rounded-xl border border-border/40 shadow-sm overflow-hidden">
                    <img src="{{ asset('images/favicon.png') }}" alt="Logo TEFA Javanica" class="w-7 h-7 object-contain">
                </div>
                <div class="flex overflow-hidden transition-all duration-300" :class="isCollapsed ? 'w-0 opacity-0' : 'w-full opacity-100'">
                    <div class="whitespace-nowrap">
                        <h1 class="text-primary font-heading font-bold uppercase tracking-wider text-sm leading-tight">
                            TEFA Javanica
                        </h1>
                        <p class="text-[10px] text-muted-foreground font-mono">Roastery Div</p>
                    </div>
                    <!-- Close Button on Mobile Overlay -->
                    <button
                        @click="isMobileOpen = false"
                        class="md:hidden p-1.5 text-muted-foreground hover:text-primary hover:bg-muted/50 rounded-lg transition-colors focus:outline-none ml-2"
                        aria-label="Close Sidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-6 space-y-2 overflow-y-auto overflow-x-hidden custom-scrollbar">
        @php
        $role = strtolower(Auth::user()->role ?? 'operator');

        // Menu Dasar (Bisa diakses siapapun)
        $navItems = [
        ['name' => 'Beranda', 'path' => 'dashboard', 'href' => route('dashboard'), 'icon' => 'home'],
        ['name' => 'Roasting', 'path' => 'production', 'href' => '/production', 'icon' => 'coffee'],
        ['name' => 'Catatan Kerja', 'path' => 'log-kerja', 'href' => '/log-kerja', 'icon' => 'users'],
        ['name' => 'Gudang', 'path' => 'inventory', 'href' => '/inventory', 'icon' => 'package'],
        ];

        // Penjualan bisa diakses oleh PIC dan Operator (Pak Deka)
        if (in_array($role, ['pic', 'operator'])) {
        $navItems[] = ['name' => 'Penjualan', 'path' => 'sales', 'href' => '/sales', 'icon' => 'shopping-cart'];
        }

        // Menu Akuntansi & Manajerial khusus PIC
        if ($role === 'pic') {
        $navItems = array_merge($navItems, [
        ['name' => 'Kalkulator HPP', 'path' => 'hpp-calculator', 'href' => '/hpp-calculator', 'icon' => 'calculator'],
        ['name' => 'Master Data', 'path' => 'master', 'href' => '/master', 'icon' => 'database'],
        ['name' => 'Laporan', 'path' => 'reports', 'href' => '/reports', 'icon' => 'file-text'],
        ['name' => 'Kelola User', 'path' => 'users', 'href' => '/users', 'icon' => 'users-manage'],
        ]);
        }
        @endphp

        @foreach($navItems as $item)
        @php $isActive = request()->is($item['path'] . '*'); @endphp
        <a
            href="{{ $item['href'] }}"
            @if($isActive) id="active-nav-item" @endif
            class="flex items-center gap-3 py-2.5 rounded-md font-sans text-sm font-medium transition-all group {{ $isActive ? 'bg-secondary text-secondary-foreground shadow-sm' : 'text-primary hover:bg-muted hover:text-secondary' }}"
            :class="isCollapsed ? 'md:px-2 md:justify-center px-3' : 'px-3'">
            <div class="flex-shrink-0 {{ $isActive ? 'text-secondary-foreground' : 'text-muted-foreground group-hover:text-secondary' }}">
                @if($item['icon'] === 'home')
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                @elseif($item['icon'] === 'coffee')
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 2v2" />
                    <path d="M14 2v2" />
                    <path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1" />
                    <path d="M6 2v2" />
                </svg>
                @elseif($item['icon'] === 'users')
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                @elseif($item['icon'] === 'package')
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                    <path d="m3.3 7 8.7 5 8.7-5" />
                    <path d="M12 22V12" />
                </svg>
                @elseif($item['icon'] === 'shopping-cart')
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="8" cy="21" r="1" />
                    <circle cx="19" cy="21" r="1" />
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                </svg>
                @elseif($item['icon'] === 'file-text')
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" x2="8" y1="13" y2="13" />
                    <line x1="16" x2="8" y1="17" y2="17" />
                    <line x1="10" x2="8" y1="9" y2="9" />
                </svg>
                @elseif($item['icon'] === 'database')
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <ellipse cx="12" cy="5" rx="9" ry="3" />
                    <path d="M3 5V19A9 3 0 0 0 21 19V5" />
                    <path d="M3 12A9 3 0 0 0 21 12" />
                </svg>
                @elseif($item['icon'] === 'settings')
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                @elseif($item['icon'] === 'calculator')
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="4" width="16" height="16" rx="2" ry="2" />
                    <rect x="9" y="9" width="6" height="6" />
                    <line x1="9" y1="1" x2="9" y2="4" />
                    <line x1="15" y1="1" x2="15" y2="4" />
                    <line x1="9" y1="20" x2="9" y2="23" />
                    <line x1="15" y1="20" x2="15" y2="23" />
                    <line x1="20" y1="9" x2="23" y2="9" />
                    <line x1="20" y1="14" x2="23" y2="14" />
                    <line x1="1" y1="9" x2="4" y2="9" />
                    <line x1="1" y1="14" x2="4" y2="14" />
                </svg>
                @elseif($item['icon'] === 'users-manage')
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <line x1="19" y1="8" x2="19" y2="14" />
                    <line x1="16" y1="11" x2="22" y2="11" />
                </svg>
                @endif
            </div>
            <span class="transition-all duration-300 whitespace-nowrap overflow-hidden" :class="isCollapsed ? 'md:hidden' : ''">
                {{ $item['name'] }}
            </span>
        </a>
        @endforeach
    </nav>

    <script>
        // Mencegah reset scroll ke atas: men-scroll menu aktif secara tangguh setelah layout rendering browser 100% selesai
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const activeEl = document.getElementById('active-nav-item');
                if (activeEl) {
                    activeEl.scrollIntoView({
                        block: 'nearest',
                        behavior: 'auto'
                    });
                }
            }, 150); // Jeda kecil untuk menjamin browser sudah selesai men-scale kontainer flexbox sidebar
        });
    </script>

    <!-- Footer / User Profile -->
    <div class="p-3 space-y-3 overflow-hidden">
        <div class="flex items-center gap-3 bg-background border border-border rounded-lg transition-all duration-300" :class="isCollapsed ? 'md:p-2 md:justify-center p-3' : 'p-3'">
            <div class="p-1.5 bg-secondary/10 text-secondary rounded-full flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
            </div>
            <div class="flex-1 overflow-hidden transition-all duration-300 whitespace-nowrap" :class="isCollapsed ? 'md:hidden' : ''">
                <p class="text-sm font-bold text-primary truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-muted-foreground font-mono truncate">{{ ucfirst(Auth::user()->role ?? 'Operator') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="w-full flex items-center justify-center gap-2 py-2 rounded-md text-sm font-bold text-red-600 bg-red-50 border border-red-200 hover:bg-red-600 hover:text-white transition-all shadow-sm"
                :class="isCollapsed ? 'md:px-2 px-4' : 'px-4'">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" x2="9" y1="12" y2="12" />
                </svg>
                <span class="transition-all duration-300 whitespace-nowrap overflow-hidden" :class="isCollapsed ? 'md:hidden' : ''">
                    Keluar
                </span>
            </button>
        </form>
    </div>
</aside>