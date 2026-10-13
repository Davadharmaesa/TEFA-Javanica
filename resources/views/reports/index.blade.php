@extends('layouts.app')

@section('title', 'Laporan Keuangan - TEFA Javanica')

@section('content')
<div x-data="reportData()" class="space-y-6">

    <!-- Header Section -->
    <div class="print:hidden flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-card p-6 rounded-2xl shadow-sm border border-border/40">
        <div>
            <h1 class="text-3xl font-heading font-black text-primary tracking-tight flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Laporan Keuangan
            </h1>
            <p class="text-sm font-sans text-muted-foreground mt-1">Pantau kinerja finansial, profitabilitas, dan arus kas roastery.</p>
        </div>
        <!-- Actions Panel -->
        <div class="flex flex-col xl:flex-row gap-4 mt-4 md:mt-0 items-start xl:items-center bg-muted/20 p-3 xl:pr-2 xl:pl-4 rounded-2xl border border-border/50 shadow-sm w-full xl:w-auto overflow-hidden">
            <!-- Filter Periode (start / end) -->
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col w-full xl:w-auto">
                <label class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1 ml-1">Periode Laporan</label>
                <div class="bg-background border border-border/60 rounded-xl p-2 text-sm font-bold text-primary flex flex-col sm:flex-row items-center gap-2 shadow-sm transition-colors hover:border-primary/50 w-full">
                    <div class="flex items-center justify-between gap-1 w-full">
                        <input type="date" name="start_date" value="{{ $start ?? now()->startOfMonth()->toDateString() }}" class="border-none bg-transparent text-[11px] sm:text-sm p-1 flex-1 text-center min-w-0 font-mono focus:ring-0">
                        <span class="text-muted-foreground shrink-0 text-xs">—</span>
                        <input type="date" name="end_date" value="{{ $end ?? now()->endOfMonth()->toDateString() }}" class="border-none bg-transparent text-[11px] sm:text-sm p-1 flex-1 text-center min-w-0 font-mono focus:ring-0">
                    </div>
                    <button type="submit" class="w-full sm:w-auto bg-primary/10 text-primary px-4 py-2 sm:py-1.5 rounded-md text-xs font-bold shrink-0 hover:bg-primary/20 transition-colors">Terapkan</button>
                </div>
            </form>
            
            <div class="hidden xl:block w-px h-12 bg-border/60 rounded-full shrink-0"></div>
            <div class="block xl:hidden h-px w-full bg-border/60 rounded-full"></div>
            
            <!-- Action Buttons -->
            <div class="flex flex-row gap-2 w-full xl:w-auto">
                <a href="{{ route('reports.export', ['start_date' => $start, 'end_date' => $end]) }}" class="flex-1 xl:flex-none xl:w-auto bg-green-500/10 text-green-600 hover:bg-green-600 hover:text-white border border-green-500/20 px-3 py-2.5 sm:py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 group shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span class="whitespace-nowrap">Ekspor Excel</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Period Summaries -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-card border border-border/40 rounded-2xl p-4">
            <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Green Bean Masuk (Periode)</p>
            <p class="text-xl font-mono text-primary mt-1">{{ number_format(($greenBeanIncoming ?? 0) / 1000, 2, ',', '.') }} Kg</p>
            <p class="text-xs text-muted-foreground mt-0.5">Sisa stok: {{ number_format(($greenBeanRemaining ?? 0) / 1000, 2, ',', '.') }} Kg</p>
        </div>
        <div class="bg-card border border-border/40 rounded-2xl p-4">
            <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Aktivitas Roasting (Periode)</p>
            <p class="text-xl font-mono text-primary mt-1">{{ $roastingCount ?? 0 }} Batch</p>
        </div>
        <div class="bg-card border border-border/40 rounded-2xl p-4">
            <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Transaksi (Periode)</p>
            <div class="mt-1 space-y-0.5">
                <p class="text-sm text-primary">Lunas: <span class="font-mono text-primary">Rp {{ number_format($totalLunas ?? 0, 0, ',', '.') }}</span></p>
                <p class="text-sm text-primary">DP Terbayar: <span class="font-mono text-primary">Rp {{ number_format($totalDP ?? 0, 0, ',', '.') }}</span></p>
                <p class="text-sm text-primary">Sisa Piutang: <span class="font-mono text-primary">Rp {{ number_format($totalSisaPiutang ?? 0, 0, ',', '.') }}</span></p>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="print:hidden grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Pendapatan -->
        <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-green-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Total Inflow (Pendapatan)</p>
                    <p class="text-2xl font-heading font-mono text-primary mt-1">Rp {{ number_format($totalInflow ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center text-green-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                </div>
            </div>
        </div>
        <!-- Pengeluaran -->
        <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-red-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Total Outflow (Pengeluaran)</p>
                    <p class="text-2xl font-heading font-mono text-primary mt-1">Rp {{ number_format($totalOutflow ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center text-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg>
                </div>
            </div>
        </div>
        <!-- Laba Bersih -->
        <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Laba/Rugi Bersih</p>
                    <p class="text-2xl font-heading font-mono text-primary mt-1">Rp {{ number_format($labaBersih ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.3 7H6a2 2 0 0 1 2 2v5"/><path d="M7.9 15.2c.4 1 1.4 1.8 2.5 1.8h7.1c1.5 0 2.7-1.4 2.4-2.9-.2-.8-.8-1.4-1.6-1.6-.5-.1-1.1-.1-1.6.2-1.4.7-3.1.2-3.8-1.2s-.2-3.1 1.2-3.8c.5-.3 1.1-.3 1.6-.2.8.2 1.4.8 1.6 1.6.3 1.5-.9 2.9-2.4 2.9H8.6c-1.1 0-2.1.8-2.5 1.8"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Section Navigation -->
    <div class="print:hidden flex overflow-x-auto hide-scrollbar gap-2 p-1 bg-muted/30 rounded-xl border border-border/50">
        <button @click="activeSection = 'keuangan'" 
            class="flex-1 min-w-0 text-xs px-2 py-2.5 md:min-w-[150px] md:text-sm md:py-3 md:px-4 rounded-lg font-bold transition-all duration-300 flex justify-center items-center gap-1.5"
            :class="activeSection === 'keuangan' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
            Keuangan
        </button>
        <button @click="activeSection = 'greenbean'" 
            class="flex-1 min-w-0 text-xs px-2 py-2.5 md:min-w-[150px] md:text-sm md:py-3 md:px-4 rounded-lg font-bold transition-all duration-300 flex justify-center items-center gap-1.5"
            :class="activeSection === 'greenbean' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><g transform="rotate(-45 12 12)"><rect x="5" y="3" width="14" height="18" rx="7"/><path d="M12 3c-1.5 3 1.5 6 0 9s-1.5 6 0 9"/></g></svg>
            Green Bean
        </button>
        <button @click="activeSection = 'roastedbean'" 
            class="flex-1 min-w-0 text-xs px-2 py-2.5 md:min-w-[150px] md:text-sm md:py-3 md:px-4 rounded-lg font-bold transition-all duration-300 flex justify-center items-center gap-1.5"
            :class="activeSection === 'roastedbean' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg>
            Roasted Bean
        </button>
    </div>



    <!-- Green Bean Report -->
    <div x-show="activeSection === 'greenbean'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden print:border-none print:shadow-none print:rounded-none">
        <div class="p-6 border-b border-primary/40 bg-primary text-primary-foreground">
            <h2 class="text-xl font-heading font-bold text-white">Laporan Green Bean</h2>
            <p class="text-sm text-white/80">Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</p>
        </div>
        <div class="overflow-x-auto hidden md:block print:block">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4">Tanggal Masuk</th>
                        <th class="p-4">Nama Green Bean</th>
                        <th class="p-4">Supplier</th>
                        <th class="p-4 text-right">Qty Masuk</th>
                        <th class="p-4 text-right">Sisa Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    @forelse($greenBeanStockEntries as $stock)
                        <tr class="hover:bg-muted/10 transition-colors">
                            <td class="p-4 text-sm font-mono text-muted-foreground">{{ $stock->tanggal_beli }}</td>
                            <td class="p-4 font-bold text-foreground">{{ $stock->greenBean?->nama_biji ?? 'Tidak diketahui' }}</td>
                            <td class="p-4 text-muted-foreground">{{ $stock->greenBean?->asal_kopi ?? '-' }}</td>
                            <td class="p-4 text-right font-mono font-medium text-foreground">{{ number_format($stock->stok_gram / 1000, 2, ',', '.') }} Kg</td>
                            <td class="p-4 text-right font-mono font-medium text-foreground">{{ number_format($stock->stok_gram / 1000, 2, ',', '.') }} Kg</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-sm text-muted-foreground">Tidak ada data Green Bean untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Mobile Card List for Green Bean Report -->
        <div class="grid grid-cols-1 gap-4 p-4 md:hidden print:hidden bg-muted/10">
            @forelse($greenBeanStockEntries as $stock)
                <div class="bg-card border border-border/40 rounded-xl p-4 space-y-3 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-bold text-foreground text-sm">{{ $stock->greenBean?->nama_biji ?? 'Tidak diketahui' }}</h4>
                            <p class="text-[11px] text-muted-foreground font-mono mt-0.5">{{ $stock->tanggal_beli }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs border-t border-border/20 pt-2">
                        <div>
                            <p class="text-muted-foreground font-semibold">Supplier/Asal</p>
                            <p class="font-medium text-foreground">{{ $stock->greenBean?->asal_kopi ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-muted-foreground font-semibold">Qty Masuk / Sisa</p>
                            <p class="font-mono font-bold text-foreground">
                                {{ number_format($stock->stok_gram / 1000, 2, ',', '.') }} Kg
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-sm text-muted-foreground">Tidak ada data Green Bean untuk periode ini.</div>
            @endforelse
        </div>
    </div>

    <!-- Roasted Bean Report -->
    <div x-show="activeSection === 'roastedbean'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden print:border-none print:shadow-none print:rounded-none">
        <div class="p-6 border-b border-primary/40 bg-primary text-primary-foreground">
            <h2 class="text-xl font-heading font-bold text-white">Laporan Roasted Bean</h2>
            <p class="text-sm text-white/80">Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</p>
        </div>
        <div class="overflow-x-auto hidden md:block print:block">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4">Tanggal Roasting</th>
                        <th class="p-4">Nama Batch</th>
                        <th class="p-4">Roast Profile</th>
                        <th class="p-4 text-right">Hasil Roasting</th>
                        <th class="p-4 text-right">Operator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    @forelse($roastingBatchEntries as $batch)
                        <tr class="hover:bg-muted/10 transition-colors">
                            <td class="p-4 text-sm font-mono text-muted-foreground">{{ $batch->tanggal_roasting }}</td>
                            <td class="p-4 font-bold text-foreground">{{ $batch->kode_batch }}</td>
                            <td class="p-4 text-muted-foreground">{{ $batch->profil_sangrai }}</td>
                            <td class="p-4 text-right font-mono font-medium text-foreground">{{ number_format($batch->berat_matang_bersih, 2, ',', '.') }} Kg</td>
                            <td class="p-4 text-right font-bold text-foreground">{{ $batch->user?->name ?? 'Operator tidak diketahui' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-sm text-muted-foreground">Tidak ada data Roasted Bean untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Mobile Card List for Roasted Bean Report -->
        <div class="grid grid-cols-1 gap-4 p-4 md:hidden print:hidden bg-muted/10">
            @forelse($roastingBatchEntries as $batch)
                <div class="bg-card border border-border/40 rounded-xl p-4 space-y-3 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-bold text-foreground text-sm">{{ $batch->kode_batch }}</h4>
                            <p class="text-[11px] text-muted-foreground font-mono mt-0.5">{{ $batch->tanggal_roasting }}</p>
                        </div>
                        <span class="px-2 py-0.5 bg-secondary/10 text-secondary border border-secondary/20 text-[10px] font-bold rounded-full">
                            {{ $batch->profil_sangrai }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs border-t border-border/20 pt-2">
                        <div>
                            <p class="text-muted-foreground font-semibold">Operator</p>
                            <p class="font-medium text-foreground">{{ $batch->user?->name ?? 'Operator tidak diketahui' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-muted-foreground font-semibold">Hasil Roasting</p>
                            <p class="font-mono font-bold text-foreground">{{ number_format($batch->berat_matang_bersih, 2, ',', '.') }} Kg</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-sm text-muted-foreground">Tidak ada data Roasted Bean untuk periode ini.</div>
            @endforelse
        </div>
    </div>

    <!-- Print Header -->
    <div class="hidden print:block text-center mb-8 pb-4 border-b-2 border-primary">
        <h1 class="text-3xl font-heading font-black text-primary">TEFA Javanica</h1>
        <p class="text-lg font-bold text-foreground mt-1">Laporan Roastery</p>
        <p class="text-sm text-muted-foreground mt-1" x-text="activeSection === 'keuangan' ? (activeTab === 'labarugi' ? 'Laporan Laba Rugi - Periode: {{ $start }} hingga {{ $end }}' : 'Riwayat Arus Kas - Periode: {{ $start }} hingga {{ $end }}') : (activeSection === 'greenbean' ? 'Laporan Green Bean - Periode: {{ $start }} hingga {{ $end }}' : 'Laporan Roasted Bean - Periode: {{ $start }} hingga {{ $end }}')"></p>
    </div>

    <!-- Tab Content: Arus Kas (Inflow & Outflow) -->
    <div x-show="activeSection === 'keuangan'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden print:border-none print:shadow-none print:rounded-none">
        <div class="p-6 border-b border-primary/40 bg-primary text-primary-foreground flex justify-between items-center">
            <div>
                <h2 class="text-xl font-heading font-bold text-white">Laporan Laba Rugi</h2>
                <p class="text-sm text-white/80">Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</p>
            </div>
            <a href="{{ route('reports.export', ['start_date' => $start, 'end_date' => $end]) }}" class="hidden md:flex bg-white/10 text-white hover:bg-white hover:text-primary border border-white/20 px-3 py-2 rounded-lg text-xs font-bold transition-all items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Ekspor Excel
            </a>
        </div>
        <div class="p-6 overflow-x-auto">
            <table class="w-full text-left min-w-[500px]">
                <tbody>
                    <!-- Pendapatan Penjualan -->
                    <tr class="border-b border-border/30">
                        <td class="py-4 font-bold text-foreground text-lg" colspan="2">Pendapatan Penjualan</td>
                    </tr>
                    @forelse($inflowByCategory as $kategori => $total)
                    <tr class="hover:bg-muted/10 border-b border-border/10">
                        <td class="py-2 pl-6 text-muted-foreground">{{ $kategori }}</td>
                        <td class="py-2 text-right font-mono font-medium text-foreground">Rp {{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr class="hover:bg-muted/10 border-b border-border/10">
                        <td class="py-2 pl-6 text-muted-foreground italic" colspan="2">Tidak ada pendapatan di periode ini.</td>
                    </tr>
                    @endforelse
                    <tr class="bg-muted/10 border-b border-border/50">
                        <td class="py-3 pl-6 font-bold text-green-600">Total Pendapatan</td>
                        <td class="py-3 text-right font-mono font-bold text-green-600">Rp {{ number_format($totalInflow, 0, ',', '.') }}</td>
                    </tr>

                    <!-- Beban Operasional -->
                    <tr>
                        <td class="py-4 font-bold text-foreground text-lg" colspan="2">Beban Operasional</td>
                    </tr>
                    @forelse($outflowByCategory as $kategori => $total)
                    <tr class="hover:bg-muted/10 border-b border-border/10">
                        <td class="py-2 pl-6 text-muted-foreground">{{ $kategori }}</td>
                        <td class="py-2 text-right font-mono font-medium text-foreground">Rp {{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr class="hover:bg-muted/10 border-b border-border/10">
                        <td class="py-2 pl-6 text-muted-foreground italic" colspan="2">Tidak ada pengeluaran di periode ini.</td>
                    </tr>
                    @endforelse
                    <tr class="bg-muted/10 border-b border-border/50">
                        <td class="py-3 pl-6 font-bold text-red-500">Total Pengeluaran</td>
                        <td class="py-3 text-right font-mono font-bold text-red-500">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</td>
                    </tr>

                    <!-- Laba Bersih -->
                    <tr class="{{ $labaBersih >= 0 ? 'bg-primary' : 'bg-red-600' }}">
                        <td class="py-3 px-6 font-black text-white text-[15px] uppercase tracking-wider rounded-bl-lg">Laba/Rugi Bersih</td>
                        <td class="py-3 px-6 text-right font-mono font-black text-white text-xl rounded-br-lg">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>



</div>

@push('scripts')
<style>
@media print {
    @page { margin: 1.5cm; }
    body { background-color: white !important; }
    
    /* Menyembunyikan elemen layout (Sidebar & Header global) tanpa merusak anak-anak main */
    aside, header { display: none !important; }
    
    /* Me-reset ukuran container utama agar full lebar kertas dan konten tidak terpotong */
    .flex.h-screen, .flex-1, main { display: block !important; height: auto !important; overflow: visible !important; }
    main { padding: 0 !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; }
    
    /* Memaksa warna tercetak */
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('reportData', () => ({
            activeSection: 'keuangan',
            
            printReport() {
                window.print();
            }
        }));
    });
</script>
@endpush
@endsection
