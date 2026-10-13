<div class="space-y-6" x-data="adminClock()">
    <!-- Header Section (Sesuai Screenshot) -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-border pb-4">
        <div>
            <!-- Green Dot and SISTEM ONLINE -->
            <div class="flex items-center gap-2 mb-1">
                <span class="flex h-3 w-3 relative">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-secondary"></span>
                </span>
                <span class="text-xs font-mono font-bold text-secondary uppercase tracking-widest text-[#526B42]">Sistem Online</span>
            </div>
            <!-- Title -->
            <h1 class="text-3xl font-heading font-black text-primary tracking-tight">Beranda PIC Eksekutif</h1>
            <!-- Subtitle -->
            <p class="text-muted-foreground font-sans text-sm mt-1">
                Dashboard Pengelolaan Proses Roasting, Kalkulasi Biaya Produksi, dan Stok Barang Jadi.
            </p>
        </div>
        
        <!-- Right side: Realtime Time & Date -->
        <div class="text-left md:text-right">
            <!-- Time -->
            <p class="text-2xl font-mono font-black text-primary" x-text="currentTime">
                {{ \Carbon\Carbon::now()->format('H.i.s') }}
            </p>
            <!-- Date -->
            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest mt-1">
                {{ Str::upper(\Carbon\Carbon::now()->translatedFormat('l, j F Y')) }}
            </p>
        </div>
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
                <div class="p-2 border border-border text-primary rounded-md shadow-sm bg-background group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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
        
        <!-- Left Side: Warning & Actions -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            <!-- Warning Box -->
            @if($has_warning)
                <div class="bg-red-50 dark:bg-[#3d211e] border border-red-200 dark:border-[#612b27] rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-2 mb-4 text-red-600">
                        <h3 class="text-xl font-bold">Perhatian Dibutuhkan</h3>
                    </div>

                    <h4 class="text-red-700 font-bold mb-4 text-sm">
                        Stok Menipis (&lt; 5kg)
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($stok_menipis as $item)
                            <div class="bg-white border border-red-100 rounded-lg p-4 flex justify-between items-center">
                                <div>
                                    <p class="font-bold">{{ $item['name'] }}</p>
                                    <p class="text-xs uppercase">{{ $item['type'] }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-xl font-bold">{{ $item['amount'] }}</span>
                                    <span class="text-sm">{{ $item['unit'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-green-50 dark:bg-[#1f2e1f] border border-green-200 dark:border-[#2e4a2e] rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-2 mb-4 text-green-700">
                        <h3 class="text-xl font-bold">Monitor Bahan Baku</h3>
                    </div>

                    <h4 class="text-green-700 font-bold mb-2 text-sm">
                        Kapasitas Produksi Optimal
                    </h4>

                    <div class="bg-white dark:bg-[#1a241a] border border-green-100 rounded-lg p-4 text-center">
                        <p class="font-bold text-green-800">
                            Seluruh jenis kopi di gudang tersedia melimpah!
                        </p>
                        <p class="text-sm text-green-700 mt-1">
                            Siap dieksekusi oleh mesin roasting tanpa batas cadangan minimum.
                        </p>
                    </div>
                </div>
            @endif

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="/reports" class="bg-card hover:bg-muted border border-border rounded-xl p-5 flex flex-col items-center justify-center gap-3 transition shadow-sm text-primary group">
                    <div class="p-2 border border-border text-primary rounded-md bg-background group-hover:bg-primary group-hover:text-primary-foreground transition-colors shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                    </div>
                    <span class="text-xs font-bold font-sans uppercase tracking-tighter text-center">Lihat Laba Rugi</span>
                </a>
                <a href="/inventory" class="bg-card hover:bg-muted border border-border rounded-xl p-5 flex flex-col items-center justify-center gap-3 transition shadow-sm text-primary group">
                    <div class="p-2 border border-border text-primary rounded-md bg-background group-hover:bg-primary group-hover:text-primary-foreground transition-colors shadow-sm">
                        <!-- Box/Warehouse Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <span class="text-xs font-bold font-sans uppercase tracking-tighter text-center">Kelola Gudang</span>
                </a>
                <a href="/hpp-calculator" class="bg-card hover:bg-muted border border-border rounded-xl p-5 flex flex-col items-center justify-center gap-3 transition shadow-sm text-primary group">
                    <div class="p-2 border border-border text-primary rounded-md bg-background group-hover:bg-primary group-hover:text-primary-foreground transition-colors shadow-sm">
                        <!-- Calculator Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="text-xs font-bold font-sans uppercase tracking-tighter text-center">Kalkulator HPP</span>
                </a>
                <a href="/reports" class="bg-card hover:bg-muted border border-border rounded-xl p-5 flex flex-col items-center justify-center gap-3 transition shadow-sm text-primary group">
                    <div class="p-2 border border-border text-primary rounded-md bg-background group-hover:bg-primary group-hover:text-primary-foreground transition-colors shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <span class="text-xs font-bold font-sans uppercase tracking-tighter text-center">Tutup Buku Periode</span>
                </a>
            </div>
        </div>

        <!-- Right Side: History -->
        <div class="bg-card border border-border rounded-xl shadow-sm p-6 lg:h-full flex flex-col">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-border">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-background border border-border text-primary rounded-md shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <h2 class="font-heading font-bold text-lg text-primary uppercase tracking-tighter">History Aktivitas Terbaru</h2>
                </div>
            </div>
            
            <div class="flex-1 space-y-6 relative">
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
            <a href="/inventory" class="w-full mt-4 py-2 text-center text-xs font-bold uppercase tracking-widest text-primary border border-border hover:bg-muted rounded-md transition-all">Lihat Seluruh History</a>
        </div>

    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('adminClock', () => ({
            currentTime: '',
            init() {
                this.updateTime();
                setInterval(() => this.updateTime(), 1000);
            },
            updateTime() {
                const now = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                this.currentTime = `${pad(now.getHours())}.${pad(now.getMinutes())}.${pad(now.getSeconds())}`;
            }
        }));
    });
</script>
@endpush
