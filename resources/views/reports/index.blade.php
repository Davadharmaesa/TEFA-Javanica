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
            <p class="text-sm font-sans text-muted-foreground mt-1">Pantau kinerja finansial, profitabilitas laba rugi, dan arus kas likuiditas roastery.</p>
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

    <!-- Tabs Navigation (Gaya Segmented Bar seperti di Gudang) -->
    <div class="print:hidden flex overflow-x-auto hide-scrollbar gap-2 p-1 bg-muted/30 rounded-xl border border-border/50">
        <button type="button" @click="activeTab = 'laba_rugi'"
            class="flex-1 min-w-0 md:min-w-[180px] py-2.5 px-3 md:py-3 md:px-4 rounded-lg font-bold text-xs md:text-sm transition-all duration-300 flex justify-center items-center gap-2 cursor-pointer"
            :class="activeTab === 'laba_rugi' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>
            </svg>
            <span class="truncate">Laporan Laba Rugi</span>
        </button>
        <button type="button" @click="activeTab = 'cash_flow'"
            class="flex-1 min-w-0 md:min-w-[180px] py-2.5 px-3 md:py-3 md:px-4 rounded-lg font-bold text-xs md:text-sm transition-all duration-300 flex justify-center items-center gap-2 cursor-pointer"
            :class="activeTab === 'cash_flow' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            <span class="truncate">Laporan Arus Kas (Cash Flow)</span>
        </button>
    </div>

    <!-- Summary Cards (Dinamis Sesuai Tab) -->
    <div class="print:hidden">
        <!-- 1. Summary Cards LABA RUGI -->
        <div x-show="activeTab === 'laba_rugi'" x-transition.opacity.duration.300ms class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Pendapatan -->
            <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-green-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Total Pendapatan Usaha</p>
                        <p class="text-2xl font-heading font-mono text-primary mt-1">Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-muted-foreground mt-1">Hasil dari penjualan produk kopi</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center text-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                    </div>
                </div>
            </div>
            <!-- Beban Operasional -->
            <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-red-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Total Beban Pokok & Operasional</p>
                        <p class="text-2xl font-heading font-mono text-primary mt-1">Rp {{ number_format($totalBebanLabaRugi ?? 0, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-muted-foreground mt-1">HPP bahan, upah, listrik & kemasan</p>
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
                        <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Laba / (Rugi) Bersih</p>
                        <p class="text-2xl font-heading font-mono {{ ($labaBersih ?? 0) >= 0 ? 'text-primary' : 'text-red-600' }} mt-1">Rp {{ number_format($labaBersih ?? 0, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-muted-foreground mt-1">{{ ($labaBersih ?? 0) >= 0 ? 'Profit operasional roastery' : 'Defisit operasional roastery' }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.3 7H6a2 2 0 0 1 2 2v5"/><path d="M7.9 15.2c.4 1 1.4 1.8 2.5 1.8h7.1c1.5 0 2.7-1.4 2.4-2.9-.2-.8-.8-1.4-1.6-1.6-.5-.1-1.1-.1-1.6.2-1.4.7-3.1.2-3.8-1.2s-.2-3.1 1.2-3.8c.5-.3 1.1-.3 1.6-.2.8.2 1.4.8 1.6 1.6.3 1.5-.9 2.9-2.4 2.9H8.6c-1.1 0-2.1.8-2.5 1.8"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Summary Cards ARUS KAS (CASH FLOW) -->
        <div x-show="activeTab === 'cash_flow'" x-transition.opacity.duration.300ms class="grid grid-cols-1 md:grid-cols-3 gap-4" style="display: none;">
            <!-- Kas Masuk -->
            <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-green-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Total Kas Masuk (Inflow)</p>
                        <p class="text-2xl font-heading font-mono text-primary mt-1">Rp {{ number_format($totalCashInflow ?? 0, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-muted-foreground mt-1">Uang riil diterima (Lunas + DP)</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center text-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                </div>
            </div>
            <!-- Kas Keluar -->
            <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-red-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Total Kas Keluar (Outflow)</p>
                        <p class="text-2xl font-heading font-mono text-primary mt-1">Rp {{ number_format($totalCashOutflow ?? 0, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-muted-foreground mt-1">Belanja bahan baku & bayar upah</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center text-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
                    </div>
                </div>
            </div>
            <!-- Net Cash Flow -->
            <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Arus Kas Bersih (Net Cash Flow)</p>
                        <p class="text-2xl font-heading font-mono {{ ($netCashFlow ?? 0) >= 0 ? 'text-primary' : 'text-red-600' }} mt-1">Rp {{ number_format($netCashFlow ?? 0, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-muted-foreground mt-1">{{ ($netCashFlow ?? 0) >= 0 ? 'Surplus saldo kas periode ini' : 'Defisit kas (pengeluaran > penerimaan)' }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Status Piutang (Khusus Tab Arus Kas) -->
    <div x-show="activeTab === 'cash_flow'" x-transition.opacity.duration.300ms class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-muted/20 border border-border/40 rounded-2xl p-4 text-xs font-bold" style="display: none;">
        <div class="flex items-center justify-between p-2 rounded-xl bg-background/60 border border-border/40">
            <span class="text-muted-foreground">Kas Masuk dari Lunas:</span>
            <span class="font-mono text-primary text-sm">Rp {{ number_format($totalLunas ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="flex items-center justify-between p-2 rounded-xl bg-background/60 border border-border/40">
            <span class="text-muted-foreground">Kas Masuk dari Uang Muka (DP):</span>
            <span class="font-mono text-primary text-sm">Rp {{ number_format($totalDP ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="flex items-center justify-between p-2 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-800">
            <span>Sisa Piutang Berjalan (Belum Cair):</span>
            <span class="font-mono text-amber-700 text-sm">Rp {{ number_format($totalSisaPiutang ?? 0, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Print Header -->
    <div class="hidden print:block text-center mb-8 pb-4 border-b-2 border-primary">
        <h1 class="text-3xl font-heading font-black text-primary">TEFA Javanica</h1>
        <p class="text-lg font-bold text-foreground mt-1" x-text="activeTab === 'laba_rugi' ? 'Laporan Laba Rugi' : 'Laporan Arus Kas (Cash Flow)'"></p>
        <p class="text-sm text-muted-foreground mt-1">Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}</p>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 1: TABEL LAPORAN LABA RUGI -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'laba_rugi'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden print:border-none print:shadow-none print:rounded-none">
        <div class="p-6 border-b border-primary/40 bg-primary text-primary-foreground flex justify-between items-center">
            <div>
                <h2 class="text-xl font-heading font-bold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                    Laporan Laba Rugi (Profit & Loss Statement)
                </h2>
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
                    <tr class="border-b border-border/30 bg-muted/20">
                        <td class="py-3 px-4 font-black text-foreground text-base uppercase tracking-wider" colspan="2">1. Pendapatan Penjualan Kopi</td>
                    </tr>
                    @forelse($pendapatanByCategory as $kategori => $total)
                    <tr class="hover:bg-muted/10 border-b border-border/10">
                        <td class="py-2.5 pl-8 text-foreground font-medium">{{ $kategori }}</td>
                        <td class="py-2.5 pr-4 text-right font-mono font-medium text-foreground">Rp {{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr class="hover:bg-muted/10 border-b border-border/10">
                        <td class="py-2.5 pl-8 text-muted-foreground italic" colspan="2">Tidak ada transaksi penjualan di periode ini.</td>
                    </tr>
                    @endforelse
                    <tr class="bg-green-500/10 border-b border-green-500/20">
                        <td class="py-3 pl-8 font-black text-green-700">Subtotal Pendapatan Usaha</td>
                        <td class="py-3 pr-4 text-right font-mono font-black text-green-700 text-base">Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}</td>
                    </tr>

                    <!-- Beban Pokok & Operasional -->
                    <tr class="border-b border-border/30 bg-muted/20">
                        <td class="py-3 px-4 font-black text-foreground text-base uppercase tracking-wider pt-6" colspan="2">2. Beban Pokok Produksi & Operasional</td>
                    </tr>
                    @forelse($bebanLabaRugiByCategory as $kategori => $total)
                    <tr class="hover:bg-muted/10 border-b border-border/10">
                        <td class="py-2.5 pl-8 text-foreground font-medium">{{ $kategori }}</td>
                        <td class="py-2.5 pr-4 text-right font-mono font-medium text-foreground">Rp {{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr class="hover:bg-muted/10 border-b border-border/10">
                        <td class="py-2.5 pl-8 text-muted-foreground italic" colspan="2">Tidak ada beban operasional di periode ini.</td>
                    </tr>
                    @endforelse
                    <tr class="bg-red-500/10 border-b border-red-500/20">
                        <td class="py-3 pl-8 font-black text-red-600">Subtotal Beban Operasional</td>
                        <td class="py-3 pr-4 text-right font-mono font-black text-red-600 text-base">Rp {{ number_format($totalBebanLabaRugi ?? 0, 0, ',', '.') }}</td>
                    </tr>

                    <!-- Laba Bersih -->
                    <tr class="{{ ($labaBersih ?? 0) >= 0 ? 'bg-primary' : 'bg-red-600' }}">
                        <td class="py-4 px-6 font-black text-white text-base uppercase tracking-wider rounded-bl-xl">Laba / (Rugi) Bersih Periode Ini</td>
                        <td class="py-4 px-6 text-right font-mono font-black text-white text-2xl rounded-br-xl">Rp {{ number_format($labaBersih ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 2: TABEL LAPORAN ARUS KAS (CASH FLOW) -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'cash_flow'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden print:border-none print:shadow-none print:rounded-none" style="display: none;">
        <div class="p-6 border-b border-primary/40 bg-primary text-primary-foreground flex justify-between items-center">
            <div>
                <h2 class="text-xl font-heading font-bold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Laporan Arus Kas (Cash Flow Statement)
                </h2>
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
                    <!-- Arus Kas Masuk -->
                    <tr class="border-b border-border/30 bg-muted/20">
                        <td class="py-3 px-4 font-black text-foreground text-base uppercase tracking-wider" colspan="2">1. Arus Kas Masuk (Cash Inflows)</td>
                    </tr>
                    @forelse($cashInflowByCategory as $kategori => $total)
                    <tr class="hover:bg-muted/10 border-b border-border/10">
                        <td class="py-2.5 pl-8 text-foreground font-medium">{{ $kategori }}</td>
                        <td class="py-2.5 pr-4 text-right font-mono font-medium text-foreground">Rp {{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr class="hover:bg-muted/10 border-b border-border/10">
                        <td class="py-2.5 pl-8 text-muted-foreground italic" colspan="2">Tidak ada penerimaan kas riil di periode ini.</td>
                    </tr>
                    @endforelse
                    <tr class="bg-green-500/10 border-b border-green-500/20">
                        <td class="py-3 pl-8 font-black text-green-700">Total Arus Kas Masuk</td>
                        <td class="py-3 pr-4 text-right font-mono font-black text-green-700 text-base">Rp {{ number_format($totalCashInflow ?? 0, 0, ',', '.') }}</td>
                    </tr>

                    <!-- Arus Kas Keluar -->
                    <tr class="border-b border-border/30 bg-muted/20">
                        <td class="py-3 px-4 font-black text-foreground text-base uppercase tracking-wider pt-6" colspan="2">2. Arus Kas Keluar (Cash Outflows)</td>
                    </tr>
                    @forelse($cashOutflowByCategory as $kategori => $total)
                    <tr class="hover:bg-muted/10 border-b border-border/10">
                        <td class="py-2.5 pl-8 text-foreground font-medium">{{ $kategori }}</td>
                        <td class="py-2.5 pr-4 text-right font-mono font-medium text-foreground">Rp {{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr class="hover:bg-muted/10 border-b border-border/10">
                        <td class="py-2.5 pl-8 text-muted-foreground italic" colspan="2">Tidak ada pengeluaran kas riil di periode ini.</td>
                    </tr>
                    @endforelse
                    <tr class="bg-red-500/10 border-b border-red-500/20">
                        <td class="py-3 pl-8 font-black text-red-600">Total Arus Kas Keluar</td>
                        <td class="py-3 pr-4 text-right font-mono font-black text-red-600 text-base">Rp {{ number_format($totalCashOutflow ?? 0, 0, ',', '.') }}</td>
                    </tr>

                    <!-- Net Cash Flow -->
                    <tr class="{{ ($netCashFlow ?? 0) >= 0 ? 'bg-primary' : 'bg-red-600' }}">
                        <td class="py-4 px-6 font-black text-white text-base uppercase tracking-wider rounded-bl-xl">Kenaikan / (Penurunan) Kas Bersih (Net Cash Flow)</td>
                        <td class="py-4 px-6 text-right font-mono font-black text-white text-2xl rounded-br-xl">Rp {{ number_format($netCashFlow ?? 0, 0, ',', '.') }}</td>
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
    aside, header { display: none !important; }
    .flex.h-screen, .flex-1, main { display: block !important; height: auto !important; overflow: visible !important; }
    main { padding: 0 !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('reportData', () => ({
            activeTab: 'laba_rugi',
            
            printReport() {
                window.print();
            }
        }));
    });
</script>
@endpush
@endsection
