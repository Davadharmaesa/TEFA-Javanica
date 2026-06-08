<header class="h-20 bg-card shadow-md flex items-center justify-between px-8 z-10 relative">
    <div class="flex items-center gap-4 flex-1">
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

            <!-- Dropdown Hasil Pencarian -->
            <div x-show="showDropdown" x-cloak class="absolute left-0 right-0 mt-2 bg-card border border-border rounded-xl shadow-xl z-50 max-h-96 overflow-y-auto custom-scrollbar p-2" style="display: none;">
                <!-- Loading -->
                <div x-show="loading" class="flex items-center justify-center p-6 gap-2 text-xs text-muted-foreground">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Mencari data...</span>
                </div>
                
                <!-- Hasil List -->
                <div x-show="!loading && searchResults.length > 0" class="space-y-1">
                    <template x-for="item in searchResults" :key="item.label + item.url + item.type">
                        <a :href="item.url" class="block p-3 hover:bg-muted/30 rounded-lg transition-colors group">
                            <div class="flex justify-between items-start gap-2">
                                <p class="text-sm font-bold text-primary group-hover:text-secondary transition-colors" x-text="item.label"></p>
                                <span class="text-[9px] font-black font-mono uppercase tracking-widest px-2 py-0.5 rounded flex-shrink-0" 
                                      :class="{
                                          'bg-blue-500/10 text-blue-600': item.type === 'Halaman',
                                          'bg-green-500/10 text-green-600': item.type === 'Bahan Baku',
                                          'bg-yellow-500/10 text-yellow-600': item.type === 'Batch Roasting',
                                          'bg-amber-500/10 text-amber-600': item.type === 'Stok Kemasan',
                                          'bg-purple-500/10 text-purple-600': item.type === 'Produk Jadi'
                                      }"
                                      x-text="item.type"></span>
                            </div>
                            <p class="text-xs text-muted-foreground mt-0.5" x-text="item.desc"></p>
                        </a>
                    </template>
                </div>

                <!-- Kosong -->
                <div x-show="!loading && searchResults.length === 0" class="p-6 text-center text-xs text-muted-foreground">
                    Tidak ditemukan data atau laporan yang cocok.
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-6">
        <!-- Theme Toggle -->
        <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')" class="text-muted-foreground hover:text-primary transition-colors">
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            <svg x-show="darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 3v1"/><path d="M12 20v1"/><path d="M3 12h1"/><path d="M20 12h1"/><path d="m18.364 5.636-.707.707"/><path d="m6.343 17.657-.707.707"/><path d="m5.636 5.636.707.707"/><path d="m17.657 17.657.707.707"/></svg>
        </button>

        <!-- Notifications -->
        <button class="relative text-muted-foreground hover:text-primary transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
            <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-accent rounded-full border-2 border-surface"></span>
        </button>

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
