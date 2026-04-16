<div class="space-y-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-3xl font-heading font-bold text-primary">Beranda Eksekutif</h1>
        <p class="text-muted-foreground font-sans">
            Selamat datang, {{ Auth::user()->name }}. Berikut adalah ringkasan performa finansial dan operasional.
        </p>
    </div>

    <!-- Top Metrics Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1 -->
        <div class="bg-card rounded-xl p-6 border border-border shadow-sm flex flex-col justify-between group transition-all hover:shadow-md">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-primary/80 font-bold uppercase tracking-tighter text-xs mb-1">Total Stok Biji Mentah</h3>
                    <div class="flex items-baseline gap-1.5 text-primary">
                        <span class="text-4xl font-mono font-extrabold">{{ $total_stok_biji_mentah }}</span>
                        <span class="text-lg font-bold font-sans">kg</span>
                    </div>
                </div>
                <div class="p-2 border border-border text-primary rounded-md shadow-sm bg-background group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>
            <div class="flex items-center text-[11px] font-bold uppercase tracking-widest text-secondary mt-2">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                <span>Real-time <span class="text-muted-foreground">dari rak gudang</span></span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-card rounded-xl p-6 border border-border shadow-sm flex flex-col justify-between group transition-all hover:shadow-md">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-primary/80 font-bold uppercase tracking-tighter text-xs mb-1">Total Kopi Matang Siap Jual</h3>
                    <div class="flex items-baseline gap-1.5 text-primary">
                        <span class="text-4xl font-mono font-extrabold">{{ $total_kopi_matang }}</span>
                        <span class="text-lg font-bold font-sans">kg</span>
                    </div>
                </div>
                <div class="p-2 border border-border text-primary rounded-md shadow-sm bg-background group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
            </div>
            <div class="flex items-center text-[11px] font-bold uppercase tracking-widest text-secondary mt-2">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                <span>Sisa Total Produksi</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-card rounded-xl p-6 border border-border shadow-sm flex flex-col justify-between group transition-all hover:shadow-md">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-primary/80 font-bold uppercase tracking-tighter text-xs mb-1">Penjualan Retail Hari Ini</h3>
                    <div class="flex items-baseline gap-1.5 text-primary">
                        <span class="text-2xl font-mono font-extrabold mt-1">Rp</span>
                        <span class="text-4xl font-mono font-extrabold">{{ number_format($penjualan_retail, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="p-2 bg-primary text-primary-foreground rounded-md shadow-sm border border-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
            <div class="flex items-center text-[11px] font-bold uppercase tracking-widest text-accent mt-2">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Khusus Lunas <span class="text-muted-foreground">dari mesin Kasir</span></span>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left Side: Warning & Actions (Takes 2 columns) -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            <!-- Warning Box -->
            <div class="bg-red-50 border border-red-200 rounded-xl p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-4 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <h3 class="text-xl font-heading font-bold">Perhatian Dibutuhkan</h3>
                </div>
                
                <h4 class="text-red-700 font-bold mb-4 text-sm">Stok Menipis (&lt; 5kg)</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($stok_menipis as $item)
                        <div class="bg-white border border-red-100 rounded-lg p-4 flex justify-between items-center text-red-800 shadow-sm">
                            <div>
                                <p class="font-bold text-[15px] font-sans">{{ $item['name'] }}</p>
                                <p class="text-xs font-bold uppercase tracking-widest text-red-700/60">{{ $item['type'] }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xl font-mono font-extrabold text-red-600">{{ $item['amount'] }}</span>
                                <span class="text-sm font-mono font-bold text-red-600 ml-0.5">{{ $item['unit'] }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full bg-white border border-red-100 rounded-lg p-4 text-center text-red-700 shadow-sm opacity-70">
                            Stok bahan baku terpantau aman.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="/reports" class="bg-card hover:bg-muted border border-border rounded-xl p-5 flex flex-col items-center justify-center gap-3 transition shadow-sm text-primary group">
                    <div class="p-2 border border-border text-primary rounded-md bg-background group-hover:bg-primary group-hover:text-primary-foreground transition-colors shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                    </div>
                    <span class="text-xs font-bold font-sans uppercase tracking-tighter text-center">Lihat Laba Rugi</span>
                </a>
                <a href="/pos" class="bg-card hover:bg-muted border border-border rounded-xl p-5 flex flex-col items-center justify-center gap-3 transition shadow-sm text-primary group">
                    <div class="p-2 border border-border text-primary rounded-md bg-background group-hover:bg-primary group-hover:text-primary-foreground transition-colors shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <span class="text-xs font-bold font-sans uppercase tracking-tighter text-center">Buka Mesin Kasir</span>
                </a>
                <a href="/settings" class="bg-card hover:bg-muted border border-border rounded-xl p-5 flex flex-col items-center justify-center gap-3 transition shadow-sm text-primary group">
                    <div class="p-2 border border-border text-primary rounded-md bg-background group-hover:bg-primary group-hover:text-primary-foreground transition-colors shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                    </div>
                    <span class="text-xs font-bold font-sans uppercase tracking-tighter text-center">Sistem Pegawai</span>
                </a>
                <a href="/reports" class="bg-card hover:bg-muted border border-border rounded-xl p-5 flex flex-col items-center justify-center gap-3 transition shadow-sm text-destructive hover:text-white hover:bg-destructive group">
                    <div class="p-2 border border-destructive text-destructive rounded-md bg-background group-hover:bg-destructive group-hover:text-white transition-colors shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <span class="text-xs font-bold font-sans uppercase tracking-tighter text-center">Tutup Buku Periode</span>
                </a>
            </div>
        </div>

        <!-- Right Side: Logs -->
        <div class="bg-card border border-border rounded-xl shadow-sm p-6 lg:h-full flex flex-col">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-border">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-background border border-border text-primary rounded-md shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <h2 class="font-heading font-bold text-lg text-primary uppercase tracking-tighter">Log Aktivitas Terbaru</h2>
                </div>
            </div>
            
            <div class="flex-1 space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-border before:to-transparent">
                @foreach($recent_logs as $log)
                    <div class="relative flex items-start gap-4">
                        <div class="w-10 h-10 bg-card border border-border rounded-full flex items-center justify-center text-primary shrink-0 z-10 shadow-sm relative left-0.5 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div class="pt-0.5 pl-1 pb-1 flex-1">
                            <p class="text-sm font-bold text-primary leading-tight mb-1">{{ $log['type'] }}</p>
                            <p class="text-[13px] text-muted-foreground leading-snug mb-2 font-sans">{{ $log['description'] }}</p>
                            <p class="font-mono text-[10px] uppercase font-bold text-muted-foreground">{{ $log['time'] }} oleh {{ $log['user'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <a href="/inventory" class="w-full mt-4 py-2 text-center text-xs font-bold uppercase tracking-widest text-primary border border-border hover:bg-muted rounded-md transition-all">Lihat Seluruh Log</a>
        </div>

    </div>
</div>
