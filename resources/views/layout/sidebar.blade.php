<aside 
    class="flex flex-col bg-card shadow-md h-full z-20 relative transition-all duration-300 ease-in-out border-r border-border" 
    :class="isCollapsed ? 'w-20' : 'w-64'"
>
    <!-- Toggle Button -->
    <button 
        @click="isCollapsed = !isCollapsed"
        class="absolute -right-3 top-8 bg-secondary text-secondary-foreground rounded-full p-1 shadow-md hover:bg-secondary/90 transition-transform hover:scale-110 z-30"
    >
        <template x-if="isCollapsed">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </template>
        <template x-if="!isCollapsed">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </template>
    </button>

    <div class="flex items-center h-20 transition-all duration-300" :class="isCollapsed ? 'justify-center px-0' : 'px-6'">
        <div class="flex items-center gap-3" :class="isCollapsed ? 'justify-center' : 'w-full'">
            <div class="bg-primary text-white p-2 rounded-lg flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg>
            </div>
            <div class="flex overflow-hidden transition-all duration-300" :class="isCollapsed ? 'w-0 opacity-0' : 'w-full opacity-100'">
                <div class="whitespace-nowrap">
                    <h1 class="text-primary font-heading font-bold uppercase tracking-wider text-sm leading-tight">
                        TEFA Javanica
                    </h1>
                    <p class="text-[10px] text-muted-foreground font-mono">Roastery Div.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-6 space-y-2 overflow-y-auto overflow-x-hidden custom-scrollbar">
        @php
            $role = strtolower(Auth::user()->role ?? 'operator');
            
            // Menu Dasar (Bisa diakses siapapun, termasuk Pak Deka)
            $navItems = [
                ['name' => 'Beranda', 'path' => 'dashboard', 'href' => route('dashboard'), 'icon' => 'home'],
                ['name' => 'Roasting', 'path' => 'production', 'href' => '/production', 'icon' => 'coffee'],
                ['name' => 'Gudang', 'path' => 'inventory', 'href' => '/inventory', 'icon' => 'package'],
            ];

            // Filter Khusus: Jika bukan Operator/Mahasiswa, buka akses menu Akuntansi & Manajerial
            if (!in_array($role, ['operator'])) {
                $navItems = array_merge($navItems, [
                    ['name' => 'Penjualan', 'path' => 'sales', 'href' => '/sales', 'icon' => 'shopping-cart'],
                    ['name' => 'Laporan', 'path' => 'reports', 'href' => '/reports', 'icon' => 'file-text'],
                    ['name' => 'Pengaturan', 'path' => 'settings', 'href' => '/settings', 'icon' => 'settings'],
                ]);
            }
        @endphp

        @foreach($navItems as $item)
            @php $isActive = request()->is($item['path'] . '*'); @endphp
            <a 
                href="{{ $item['href'] }}" 
                class="flex items-center gap-3 py-2.5 rounded-md font-sans text-sm font-medium transition-all group {{ $isActive ? 'bg-secondary text-secondary-foreground shadow-sm' : 'text-primary hover:bg-muted hover:text-secondary' }}"
                :class="isCollapsed ? 'px-2 justify-center' : 'px-3'"
            >
                <div class="flex-shrink-0 {{ $isActive ? 'text-secondary-foreground' : 'text-muted-foreground group-hover:text-secondary' }}">
                    @if($item['icon'] === 'home')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    @elseif($item['icon'] === 'coffee')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg>
                    @elseif($item['icon'] === 'package')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    @elseif($item['icon'] === 'shopping-cart')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    @elseif($item['icon'] === 'file-text')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/></svg>
                    @elseif($item['icon'] === 'settings')
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    @endif
                </div>
                <span class="transition-all duration-300 whitespace-nowrap overflow-hidden" :class="isCollapsed ? 'w-0 opacity-0' : 'w-full opacity-100'">
                    {{ $item['name'] }}
                </span>
            </a>
        @endforeach
    </nav>

    <!-- Footer / User Profile -->
    <div class="p-3 space-y-3 overflow-hidden">
    <div class="flex items-center gap-3 bg-background border border-border rounded-lg transition-all duration-300" :class="isCollapsed ? 'p-2 justify-center' : 'p-3'">
        <div class="p-1.5 bg-secondary/10 text-secondary rounded-full flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div class="flex-1 overflow-hidden transition-all duration-300 whitespace-nowrap" :class="isCollapsed ? 'w-0 opacity-0 hidden' : 'opacity-100'">
            <p class="text-sm font-bold text-primary truncate">{{ Auth::user()->name }}</p>
            <p class="text-xs text-muted-foreground font-mono truncate">{{ ucfirst(Auth::user()->role ?? 'Operator') }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button
            type="submit"
            class="w-full flex items-center justify-center gap-2 py-2 rounded-md text-sm font-bold text-red-600 bg-red-50 border border-red-200 hover:bg-red-600 hover:text-white transition-all shadow-sm"
            :class="isCollapsed ? 'px-2' : 'px-4'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            <span class="transition-all duration-300 whitespace-nowrap overflow-hidden" :class="isCollapsed ? 'w-0 opacity-0' : 'opacity-100'">
                Keluar
            </span>
        </button>
    </form>
</div>
</aside>
