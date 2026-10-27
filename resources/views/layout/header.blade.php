<header 
    class="h-20 bg-card shadow-md flex items-center justify-between px-4 sm:px-6 md:px-8 z-30 relative"
    x-data="{
        searchQuery: '',
        searchResults: [],
        showDropdown: false,
        loading: false,
        mobileSearchOpen: false,
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
        },
        clearSearch() {
            this.searchQuery = '';
            this.performSearch();
            this.$refs.searchInput?.focus();
        }
    }"
    @keydown.window.prevent.ctrl.k="$refs.searchInput?.focus(); showDropdown = true; performSearch()"
    @keydown.window.prevent.cmd.k="$refs.searchInput?.focus(); showDropdown = true; performSearch()"
    @keydown.escape="showDropdown = false; mobileSearchOpen = false; $refs.searchInput?.blur()"
>
    <div class="flex items-center gap-3 flex-1 min-w-0">
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

        <!-- Mobile Search Toggle Button -->
        <button 
            @click="mobileSearchOpen = !mobileSearchOpen; if (mobileSearchOpen) { showDropdown = true; performSearch(); $nextTick(() => $refs.mobileSearchInput?.focus()); }"
            class="md:hidden p-2 text-muted-foreground hover:text-primary hover:bg-muted/50 rounded-lg transition-colors focus:outline-none flex-shrink-0"
            aria-label="Pencarian"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </button>

        <!-- Desktop Global Search Component -->
        <div class="relative w-80 lg:w-[420px] hidden md:block group" @click.away="showDropdown = false">
            <!-- Search Input Box -->
            <div class="relative flex items-center">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </span>
                <input
                    x-ref="searchInput"
                    type="text"
                    placeholder="Cari data stok, batch, atau laporan..."
                    class="w-full bg-background border border-border/70 rounded-xl pl-10 pr-20 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/60 font-sans focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm"
                    x-model="searchQuery"
                    @focus="showDropdown = true; performSearch()"
                    @input.debounce.250ms="performSearch()"
                >
                
                <!-- Action Icons -->
                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                    <!-- Loading Spinner -->
                    <div x-show="loading" x-cloak class="animate-spin text-primary">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>

                    <!-- Clear Button -->
                    <button 
                        type="button" 
                        x-show="searchQuery.length > 0 && !loading" 
                        @click="clearSearch()"
                        class="p-0.5 text-muted-foreground hover:text-foreground rounded-full hover:bg-muted/60 transition-colors"
                        title="Hapus pencarian"
                        x-cloak
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>

                    <!-- Shortcut Hint -->
                    <span x-show="searchQuery.length === 0 && !loading" class="hidden lg:inline-flex text-[10px] font-mono px-1.5 py-0.5 rounded border border-border/60 text-muted-foreground/70 bg-muted/40 font-medium select-none">
                        Ctrl K
                    </span>
                </div>
            </div>

            <!-- Dropdown Floating Results -->
            <div 
                x-show="showDropdown" 
                x-cloak 
                class="absolute left-0 mt-2 w-[440px] max-w-[calc(100vw-2rem)] bg-card border border-border/80 rounded-2xl shadow-2xl z-50 overflow-hidden backdrop-blur-md"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-98"
                style="display: none;"
            >
                <!-- Dropdown Header Bar -->
                <div class="px-4 py-2.5 border-b border-border/50 bg-muted/20 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <template x-if="searchQuery.trim().length === 0">
                            <span class="text-xs font-bold text-primary flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                Pintasan Navigasi Cepat
                            </span>
                        </template>
                        <template x-if="searchQuery.trim().length > 0">
                            <span class="text-xs font-bold text-primary flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                Hasil Pencarian
                            </span>
                        </template>
                    </div>
                    <span x-show="searchResults.length > 0" class="text-[10px] bg-primary/10 text-primary font-bold px-2 py-0.5 rounded-full" x-text="searchResults.length + ' Ditemukan'"></span>
                </div>

                <!-- Dropdown Content / Results List -->
                <div class="max-h-[360px] overflow-y-auto divide-y divide-border/20 custom-scrollbar">
                    <!-- Loading State -->
                    <template x-if="loading && searchResults.length === 0">
                        <div class="px-6 py-8 text-center flex flex-col items-center justify-center gap-2">
                            <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-xs text-muted-foreground font-medium">Mencari data sistem...</span>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <template x-if="!loading && searchResults.length === 0">
                        <div class="px-6 py-8 text-center flex flex-col items-center justify-center">
                            <div class="w-10 h-10 rounded-full bg-muted/40 flex items-center justify-center text-muted-foreground mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </div>
                            <p class="text-xs font-bold text-foreground">Tidak ada hasil ditemukan</p>
                            <p class="text-[11px] text-muted-foreground mt-0.5">Coba kata kunci lain seperti jenis kopi, kode batch, atau laporan</p>
                        </div>
                    </template>

                    <!-- Result Items -->
                    <template x-for="(item, idx) in searchResults" :key="idx">
                        <a 
                            :href="item.url" 
                            @click="showDropdown = false"
                            class="flex items-start gap-3 px-4 py-3 hover:bg-muted/40 transition-colors group cursor-pointer block"
                        >
                            <!-- Icon per Tipe -->
                            <div 
                                class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5 transition-transform group-hover:scale-110"
                                :class="{
                                    'bg-primary/10 text-primary': item.type === 'Halaman',
                                    'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400': item.type === 'Bahan Baku',
                                    'bg-amber-500/10 text-amber-600 dark:text-amber-400': item.type === 'Batch Roasting',
                                    'bg-blue-500/10 text-blue-600 dark:text-blue-400': item.type === 'Stok Kemasan',
                                    'bg-purple-500/10 text-purple-600 dark:text-purple-400': item.type === 'Produk Jadi',
                                    'bg-rose-500/10 text-rose-600 dark:text-rose-400': item.type === 'Penjualan',
                                    'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400': item.type === 'Pengguna'
                                }"
                            >
                                <!-- Icon Halaman -->
                                <template x-if="item.type === 'Halaman'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                                </template>
                                <!-- Icon Bahan Baku -->
                                <template x-if="item.type === 'Bahan Baku'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v8"/><path d="m4.93 10.93 1.41 1.41"/><path d="M2 18h2"/><path d="M20 18h2"/><path d="m19.07 10.93-1.41 1.41"/><path d="M22 22H2"/><path d="m8 6 4-4 4 4"/><path d="M16 18a4 4 0 0 0-8 0"/></svg>
                                </template>
                                <!-- Icon Batch Roasting -->
                                <template x-if="item.type === 'Batch Roasting'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                                </template>
                                <!-- Icon Stok Kemasan -->
                                <template x-if="item.type === 'Stok Kemasan'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                </template>
                                <!-- Icon Produk Jadi -->
                                <template x-if="item.type === 'Produk Jadi'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                                </template>
                                <!-- Icon Penjualan -->
                                <template x-if="item.type === 'Penjualan'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                </template>
                                <!-- Icon Pengguna -->
                                <template x-if="item.type === 'Pengguna'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </template>
                            </div>

                            <!-- Text Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-xs font-bold text-foreground truncate group-hover:text-primary transition-colors" x-text="item.label"></p>
                                    <span 
                                        class="text-[9px] font-semibold px-2 py-0.5 rounded-full shrink-0 uppercase tracking-wider"
                                        :class="{
                                            'bg-primary/10 text-primary border border-primary/20': item.type === 'Halaman',
                                            'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20': item.type === 'Bahan Baku',
                                            'bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/20': item.type === 'Batch Roasting',
                                            'bg-blue-500/10 text-blue-700 dark:text-blue-300 border border-blue-500/20': item.type === 'Stok Kemasan',
                                            'bg-purple-500/10 text-purple-700 dark:text-purple-300 border border-purple-500/20': item.type === 'Produk Jadi',
                                            'bg-rose-500/10 text-rose-700 dark:text-rose-300 border border-rose-500/20': item.type === 'Penjualan',
                                            'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-500/20': item.type === 'Pengguna'
                                        }"
                                        x-text="item.type"
                                    ></span>
                                </div>
                                <p class="text-[11px] text-muted-foreground mt-0.5 truncate leading-snug" x-text="item.desc"></p>
                            </div>

                            <div class="text-muted-foreground/40 group-hover:text-primary group-hover:translate-x-0.5 transition-all self-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                            </div>
                        </a>
                    </template>
                </div>

                <!-- Dropdown Footer -->
                <div class="px-4 py-2 border-t border-border/50 text-[10px] text-muted-foreground flex items-center justify-between bg-muted/10">
                    <span class="flex items-center gap-1">
                        <kbd class="font-mono px-1 py-0.5 bg-card border border-border/60 rounded text-[9px]">Esc</kbd> untuk menutup
                    </span>
                    <span class="font-medium text-primary flex items-center gap-1">
                        Pencarian Sistem TEFA
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- Mobile Search Overlay Modal -->
    <div 
        x-show="mobileSearchOpen" 
        x-cloak 
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 md:hidden p-4 flex flex-col justify-start"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="bg-card border border-border/80 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] w-full" @click.away="mobileSearchOpen = false">
            <!-- Mobile Search Bar Header -->
            <div class="p-3 border-b border-border/50 flex items-center gap-2 bg-muted/20">
                <span class="text-muted-foreground pl-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </span>
                <input
                    x-ref="mobileSearchInput"
                    type="text"
                    placeholder="Cari data stok, batch, atau laporan..."
                    class="flex-1 bg-transparent border-none text-sm text-foreground placeholder:text-muted-foreground/60 focus:outline-none focus:ring-0"
                    x-model="searchQuery"
                    @input.debounce.250ms="performSearch()"
                >
                <button 
                    type="button" 
                    @click="mobileSearchOpen = false" 
                    class="p-2 text-muted-foreground hover:text-foreground rounded-lg hover:bg-muted/50 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <!-- Mobile Results List -->
            <div class="overflow-y-auto divide-y divide-border/20 custom-scrollbar flex-1">
                <template x-if="loading && searchResults.length === 0">
                    <div class="px-6 py-8 text-center flex flex-col items-center justify-center gap-2">
                        <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-xs text-muted-foreground font-medium">Mencari data sistem...</span>
                    </div>
                </template>

                <template x-if="!loading && searchResults.length === 0">
                    <div class="px-6 py-8 text-center flex flex-col items-center justify-center">
                        <div class="w-10 h-10 rounded-full bg-muted/40 flex items-center justify-center text-muted-foreground mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <p class="text-xs font-bold text-foreground">Tidak ada hasil ditemukan</p>
                    </div>
                </template>

                <template x-for="(item, idx) in searchResults" :key="idx">
                    <a 
                        :href="item.url" 
                        @click="mobileSearchOpen = false"
                        class="flex items-start gap-3 px-4 py-3 hover:bg-muted/40 transition-colors group cursor-pointer block"
                    >
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-bold text-foreground truncate" x-text="item.label"></p>
                                <span class="text-[9px] font-semibold px-2 py-0.5 rounded-full shrink-0 uppercase tracking-wider bg-primary/10 text-primary border border-primary/20" x-text="item.type"></span>
                            </div>
                            <p class="text-[11px] text-muted-foreground mt-0.5 truncate leading-snug" x-text="item.desc"></p>
                        </div>
                    </a>
                </template>
            </div>
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

