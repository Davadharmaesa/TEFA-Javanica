<header class="h-20 bg-card shadow-md flex items-center justify-between px-4 sm:px-6 md:px-8 z-10 relative">
    <div class="flex items-center gap-4 flex-1">
        <!-- Sidebar Toggle Menu Button (Mobile only) -->
        <button 
            @click="isMobileOpen = !isMobileOpen"
            class="md:hidden p-2 -ml-2 text-muted-foreground hover:text-primary hover:bg-muted/50 rounded-lg transition-colors focus:outline-none flex-shrink-0"
            aria-label="Toggle Sidebar"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" x2="20" y1="12" y2="12"></line>
                <line x1="4" x2="20" y1="6" y2="6"></line>
                <line x1="4" x2="20" y1="18" y2="18"></line>
            </svg>
        </button>

        <div class="relative w-96 hidden md:block group" x-data="{
            searchQuery: '',
            searchResults: [],
            showDropdown: false,
            loading: false,
            async performSearch() {
                if (this.searchQuery.trim().length === 0) {
                    this.searchResults = [];
                    await this.fetchDefaults();
                    return;
                }
                this.loading = true;
                try {
                    let res = await fetch('/internal-api/search?q=' + encodeURIComponent(this.searchQuery));
                    if (res.ok) {
                        this.searchResults = await res.json();
                    }
                } catch (e) {
                    console.error('Search failed', e);
                } finally {
                    this.loading = false;
                }
            },
            async fetchDefaults() {
                this.loading = true;
                try {
                    let res = await fetch('/internal-api/search');
                    if (res.ok) {
                        this.searchResults = await res.json();
                    }
                } catch (e) {
                    console.error('Failed to load defaults', e);
                } finally {
                    this.loading = false;
                }
            }
        }" @click.away="showDropdown = false">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </span>
            <input
                type="text"
                placeholder="Cari data stok, batch, atau laporan..."
                class="w-full bg-background border border-border rounded-md pl-10 pr-4 py-2 text-sm text-primary font-sans focus:outline-none focus:ring-1 focus:ring-primary transition-all"
                x-model="searchQuery"
                @focus="showDropdown = true; performSearch()"
                @input.debounce.300ms="performSearch()"
            >
        </div>

    </div>

    <div class="flex items-center gap-6">
        <!-- Theme Toggle -->
        <button @click="darkMode = !darkMode; try { localStorage.setItem('theme', darkMode ? 'dark' : 'light') } catch(e) {}" class="text-muted-foreground hover:text-primary transition-colors">
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            <svg x-show="darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 3v1"/><path d="M12 20v1"/><path d="M3 12h1"/><path d="M20 12h1"/><path d="m18.364 5.636-.707.707"/><path d="m6.343 17.657-.707.707"/><path d="m5.636 5.636.707.707"/><path d="m17.657 17.657.707.707"/></svg>
        </button>

        <!-- Notifications -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="relative text-muted-foreground hover:text-primary transition-colors focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                @if(isset($system_notifications) && count($system_notifications) > 0)
                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-accent rounded-full border-2 border-surface"></span>
                @endif
            </button>

            <!-- Notification Dropdown -->
            <div x-show="open" @click.away="open = false" x-cloak 
                 class="absolute right-0 mt-4 w-80 bg-card border border-border/50 rounded-2xl shadow-xl z-50 overflow-hidden"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 style="display: none;">
                
                <div class="px-4 py-3 border-b border-border/50 bg-muted/10 flex justify-between items-center">
                    <h3 class="font-bold text-sm text-primary">Notifikasi Sistem</h3>
                    @if(isset($system_notifications) && count($system_notifications) > 0)
                        <span class="text-[10px] bg-accent/20 text-accent font-bold px-2 py-0.5 rounded-full">{{ count($system_notifications) }} Baru</span>
                    @endif
                </div>

                <div class="max-h-[300px] overflow-y-auto divide-y divide-border/30 custom-scrollbar">
                    @if(isset($system_notifications) && count($system_notifications) > 0)
                        @foreach($system_notifications as $notif)
                        <div class="px-4 py-3 hover:bg-muted/30 transition-colors cursor-pointer group">
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-full bg-{{ $notif['color'] }}/10 text-{{ $notif['color'] }} flex items-center justify-center shrink-0 mt-0.5 group-hover:bg-{{ $notif['color'] }} group-hover:text-white transition-colors">
                                    {!! $notif['icon'] !!}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-foreground">{{ $notif['title'] }}</p>
                                    <p class="text-[11px] text-muted-foreground mt-0.5 leading-snug">{{ $notif['message'] }}</p>
                                    <p class="text-[9px] text-muted-foreground/70 mt-1 font-mono">{{ $notif['time'] }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="px-4 py-6 text-center">
                            <p class="text-xs text-muted-foreground">Tidak ada notifikasi baru.</p>
                        </div>
                    @endif
                </div>
                
                @if(isset($system_notifications) && count($system_notifications) > 0)
                <div class="px-4 py-2 border-t border-border/50 text-center bg-muted/5">
                    <a href="#" @click.prevent="open = false" class="text-[11px] font-bold text-primary hover:underline">Tandai semua sudah dibaca</a>
                </div>
                @endif
            </div>
        </div>

        <div class="w-px h-6 bg-border"></div>

        <!-- User Profile -->
        <div class="flex items-center gap-3 cursor-pointer group">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-sans font-medium text-primary group-hover:text-primary transition-colors">{{ Auth::user()->name ?? 'Pak Deka' }}</p>
                <p class="text-xs text-muted-foreground font-mono">Role: {{ ucfirst(Auth::user()->role ?? 'Operator') }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center text-white shadow-sm transition-transform group-hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
        </div>
    </div>
</header>

