@extends('layouts.app')

@section('title', 'Laporan Keuangan - TEFA Javanica')

@section('content')
<div x-data="{ activeReport: 'laba_rugi', showDetails: false }" class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-card p-6 rounded-2xl shadow-sm border border-border/40">
        <div>
            <h1 class="text-3xl font-heading font-black text-primary tracking-tight flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Laporan Keuangan
            </h1>
            <p class="text-sm font-sans text-muted-foreground mt-1">Laporan Keuangan TEFA Javanica Roastery.</p>
        </div>
        <!-- Actions Panel -->
        <div class="flex flex-col sm:flex-row gap-3 mt-4 md:mt-0 items-stretch sm:items-center bg-muted/30 p-2.5 sm:pr-2 sm:pl-3 rounded-2xl border border-border/50 shadow-sm w-full md:w-auto">
            <!-- Filter Periode (start / end) -->
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                <div class="bg-background border border-border/60 rounded-xl p-1.5 text-sm font-bold text-primary flex items-center gap-1.5 shadow-sm transition-colors hover:border-primary/50">
                    <input type="date" name="start_date" value="{{ $start ?? now()->startOfMonth()->toDateString() }}" class="border-none bg-transparent text-xs sm:text-sm p-1 text-center font-mono focus:ring-0 text-foreground">
                    <span class="text-muted-foreground shrink-0 text-xs">—</span>
                    <input type="date" name="end_date" value="{{ $end ?? now()->endOfMonth()->toDateString() }}" class="border-none bg-transparent text-xs sm:text-sm p-1 text-center font-mono focus:ring-0 text-foreground">
                </div>
                <button type="submit" class="bg-primary/10 text-primary px-3.5 py-2 rounded-xl text-xs font-bold hover:bg-primary/20 transition-colors">Terapkan</button>
            </form>
            
            <div class="hidden sm:block w-px h-8 bg-border/60 rounded-full shrink-0"></div>
            
            <!-- Tombol Ekspor Excel -->
            <a href="{{ route('reports.export', ['start_date' => $start, 'end_date' => $end]) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span class="whitespace-nowrap">Ekspor Excel </span>
            </a>
        </div>
    </div>

    <!-- Tab Pilihan Laporan (Laba & Rugi vs Arus Kas) -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-muted/20 p-2 rounded-2xl border border-border/40">
        <div class="flex gap-2">
            <button type="button" @click="activeReport = 'laba_rugi'" :class="activeReport === 'laba_rugi' ? 'bg-primary text-primary-foreground shadow-sm' : 'bg-card text-muted-foreground hover:bg-muted'" class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>Laporan Laba & Rugi</span>
            </button>
            <button type="button" @click="activeReport = 'arus_kas'" :class="activeReport === 'arus_kas' ? 'bg-primary text-primary-foreground shadow-sm' : 'bg-card text-muted-foreground hover:bg-muted'" class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                <span>Laporan Arus Kas</span>
            </button>
        </div>

        <span class="text-xs font-mono text-muted-foreground pr-2 font-medium">
            Periode: <strong>{{ \Carbon\Carbon::parse($start)->format('d/m/Y') }}</strong> s.d. <strong>{{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}</strong>
        </span>
    </div>

    <!-- Summary Cards Dinamis -->
    <!-- Mode Laba Rugi Cards -->
    <div x-show="activeReport === 'laba_rugi'" x-transition class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Penjualan Bersih -->
        <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-primary/70"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Penjualan Bersih</p>
                    <p class="text-xl font-heading font-mono text-primary mt-1 font-bold">
                        Rp {{ number_format($penjualanBersih, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
            </div>
            <p class="text-[11px] text-muted-foreground mt-2">Omset penjualan periode ini</p>
        </div>

        <!-- HPP -->
        <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-amber-500/70"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Total HPP</p>
                    <p class="text-xl font-heading font-mono text-amber-600 dark:text-amber-400 mt-1 font-bold">
                        Rp {{ number_format($totalHpp, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.12 6.4-6.05-4.06a2 2 0 0 0-2.17-.05L2.95 8.41a2 2 0 0 0-.95 1.7v5.82a2 2 0 0 0 .88 1.66l6.05 4.07a2 2 0 0 0 2.17.05l9.95-6.12a2 2 0 0 0 .95-1.7V8.06a2 2 0 0 0-.88-1.66Z"/></svg>
                </div>
            </div>
            <p class="text-[11px] text-muted-foreground mt-2">Bahan Baku + Kemasan</p>
        </div>

        <!-- Laba Kotor -->
        <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-blue-500/70"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Laba Kotor</p>
                    <p class="text-xl font-heading font-mono {{ $labaKotor >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-500' }} mt-1 font-bold">
                        {{ $labaKotor < 0 ? '(Rp ' . number_format(abs($labaKotor), 0, ',', '.') . ')' : 'Rp ' . number_format($labaKotor, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                </div>
            </div>
            <p class="text-[11px] text-muted-foreground mt-2">Penjualan Bersih - HPP</p>
        </div>

        <!-- Laba Bersih -->
        <div class="bg-card border border-primary/40 rounded-2xl p-5 shadow-sm relative overflow-hidden bg-gradient-to-br from-primary/10 via-card to-card">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-primary"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider leading-tight {{ $labaBersih >= 0 ? 'text-primary' : 'text-red-500' }}">
                        {{ $labaBersih >= 0 ? 'Laba Bersih (Net)' : 'Rugi Bersih (Net)' }}
                    </p>
                    <p class="text-xl font-heading font-mono mt-1 font-black {{ $labaBersih >= 0 ? 'text-primary' : 'text-red-500' }}">
                        {{ $labaBersih < 0 ? '(Rp ' . number_format(abs($labaBersih), 0, ',', '.') . ')' : 'Rp ' . number_format($labaBersih, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary text-primary-foreground flex items-center justify-center shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <p class="text-[11px] text-primary/80 mt-2 font-medium">Laba Kotor - Beban Operasional</p>
        </div>
    </div>

    <!-- Mode Arus Kas Cards -->
    <div x-show="activeReport === 'arus_kas'" x-transition class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Kas Bersih Operasional -->
        <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-primary/70"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Kas Bersih Operasional</p>
                    <p class="text-xl font-heading font-mono {{ $kasBersihOperasional >= 0 ? 'text-primary' : 'text-red-500' }} mt-1 font-bold">
                        {{ $kasBersihOperasional < 0 ? '(Rp ' . number_format(abs($kasBersihOperasional), 0, ',', '.') . ')' : 'Rp ' . number_format($kasBersihOperasional, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg>
                </div>
            </div>
            <p class="text-[11px] text-muted-foreground mt-2">Kas masuk vs kas keluar riil</p>
        </div>

        <!-- Kenaikan / Penurunan Kas -->
        <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-accent/70"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Perubahan Kas Bersih</p>
                    <p class="text-xl font-heading font-mono {{ $kenaikanKas >= 0 ? 'text-foreground' : 'text-red-500' }} mt-1 font-bold">
                        {{ $kenaikanKas < 0 ? '(Rp ' . number_format(abs($kenaikanKas), 0, ',', '.') . ')' : 'Rp ' . number_format($kenaikanKas, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center text-accent">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                </div>
            </div>
            <p class="text-[11px] text-muted-foreground mt-2">Operasional + Investasi + Pendanaan</p>
        </div>

        <!-- Saldo Kas Awal -->
        <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-muted-foreground/30"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-black text-muted-foreground uppercase tracking-wider leading-tight">Saldo Kas Awal</p>
                    <p class="text-xl font-heading font-mono text-foreground mt-1 font-bold">
                        {{ $saldoKasAwal < 0 ? '(Rp ' . number_format(abs($saldoKasAwal), 0, ',', '.') . ')' : 'Rp ' . number_format($saldoKasAwal, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-muted flex items-center justify-center text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                </div>
            </div>
            <p class="text-[11px] text-muted-foreground mt-2">Sebelum {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }}</p>
        </div>

        <!-- Saldo Kas Akhir -->
        <div class="bg-card border border-primary/40 rounded-2xl p-5 shadow-sm relative overflow-hidden bg-gradient-to-br from-primary/10 via-card to-card">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-primary"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-black text-primary uppercase tracking-wider leading-tight">Saldo Kas Akhir</p>
                    <p class="text-xl font-heading font-mono text-primary mt-1 font-black">
                        {{ $saldoKasAkhir < 0 ? '(Rp ' . number_format(abs($saldoKasAkhir), 0, ',', '.') . ')' : 'Rp ' . number_format($saldoKasAkhir, 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary text-primary-foreground flex items-center justify-center shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                </div>
            </div>
            <p class="text-[11px] text-primary/80 mt-2 font-medium">Per {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}</p>
        </div>
    </div>


    <!-- ========================================================================= -->
    <!-- TAB 1: LAPORAN LABA & RUGI (FORMAT 4 KOLOM PERSIS GAMBAR CONTOH) -->
    <!-- ========================================================================= -->
    <div x-show="activeReport === 'laba_rugi'" x-transition class="bg-card border border-border/50 rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Header Dokumen Formal -->
        <div class="text-center py-6 px-4 border-b border-border/40 bg-muted/20">
            <h2 class="text-lg md:text-xl font-heading font-black uppercase tracking-wider text-primary">Laporan Laba & Rugi</h2>
            <h3 class="text-base md:text-lg font-heading font-bold uppercase tracking-wide text-foreground mt-0.5">TEFA JAVANICA</h3>
            <p class="text-xs md:text-sm font-bold text-muted-foreground mt-0.5">
                Dari {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} Sampai {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}
            </p>
        </div>

        <!-- Tabel 4 Kolom Laba Rugi -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-primary text-primary-foreground">
                        <th class="py-3 px-6 font-bold text-left tracking-wide">Akun & Keterangan</th>
                        <th class="py-3 px-4 font-bold text-right tracking-wide font-mono w-40">Rincian</th>
                        <th class="py-3 px-4 font-bold text-right tracking-wide font-mono w-44">Subtotal</th>
                        <th class="py-3 px-6 font-bold text-right tracking-wide font-mono w-48">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/20">

                    <!-- 1. PENDAPATAN DARI PENJUALAN -->
                    <tr class="bg-primary/10">
                        <td class="py-2.5 px-6 font-bold text-foreground" colspan="4">Pendapatan dari Penjualan</td>
                    </tr>
                    <tr class="hover:bg-muted/15">
                        <td class="py-2 px-6 pl-10 text-foreground">Penjualan</td>
                        <td class="py-2 px-4 text-right font-mono text-muted-foreground"></td>
                        <td class="py-2 px-4 text-right font-mono font-medium text-foreground">{{ number_format($penjualanKotor, 0, ',', '.') }}</td>
                        <td class="py-2 px-6 text-right font-mono"></td>
                    </tr>
                    <tr class="hover:bg-muted/15">
                        <td class="py-2 px-6 pl-10 text-foreground">-Retur dan Pot. Penjualan</td>
                        <td class="py-2 px-4 text-right font-mono text-muted-foreground">{{ number_format($returPenjualan, 0, ',', '.') }}</td>
                        <td class="py-2 px-4 text-right font-mono"></td>
                        <td class="py-2 px-6 text-right font-mono"></td>
                    </tr>
                    <tr class="hover:bg-muted/15">
                        <td class="py-2 px-6 pl-10 text-foreground">Diskon Penjualan</td>
                        <td class="py-2 px-4 text-right font-mono text-muted-foreground">{{ number_format($diskonPenjualan, 0, ',', '.') }}</td>
                        <td class="py-2 px-4 text-right font-mono text-muted-foreground">{{ number_format($potonganPenjualan, 0, ',', '.') }}</td>
                        <td class="py-2 px-6 text-right font-mono"></td>
                    </tr>
                    <tr class="bg-muted/20 font-bold border-t border-border/40">
                        <td class="py-2.5 px-6 font-bold text-foreground">Penjualan Bersih</td>
                        <td class="py-2.5 px-4"></td>
                        <td class="py-2.5 px-4"></td>
                        <td class="py-2.5 px-6 text-right font-mono font-bold text-primary">{{ number_format($penjualanBersih, 0, ',', '.') }}</td>
                    </tr>

                    <!-- 2. HARGA POKOK PENJUALAN -->
                    <tr class="bg-primary/10">
                        <td class="py-2.5 px-6 font-bold text-foreground" colspan="4">Harga Pokok Penjualan</td>
                    </tr>
                    <tr class="hover:bg-muted/15">
                        <td class="py-2 px-6 pl-10 text-foreground">Biaya Bahan Baku (Green Bean)</td>
                        <td class="py-2 px-4 text-right font-mono text-foreground">{{ number_format($hppBahanBaku, 0, ',', '.') }}</td>
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-6"></td>
                    </tr>
                    <tr class="hover:bg-muted/15">
                        <td class="py-2 px-6 pl-10 text-foreground">Biaya Bahan Kemasan (Packaging)</td>
                        <td class="py-2 px-4 text-right font-mono text-foreground">{{ number_format($hppKemasan, 0, ',', '.') }}</td>
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-6"></td>
                    </tr>
                    <tr class="hover:bg-muted/15 border-t border-border/20">
                        <td class="py-2 px-6 pl-6 font-semibold text-foreground">Total Harga Pokok Penjualan</td>
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-6 text-right font-mono font-semibold text-amber-600 dark:text-amber-400">
                            {{ $totalHpp > 0 ? '(' . number_format($totalHpp, 0, ',', '.') . ')' : '0' }}
                        </td>
                    </tr>

                    <!-- 3. LABA KOTOR -->
                    <tr class="bg-muted/30 font-bold border-t-2 border-b-2 border-border/60">
                        <td class="py-2.5 px-6 font-black text-foreground">Laba Kotor</td>
                        <td class="py-2.5 px-4"></td>
                        <td class="py-2.5 px-4"></td>
                        <td class="py-2.5 px-6 text-right font-mono font-black text-foreground">
                            {{ $labaKotor < 0 ? '(' . number_format(abs($labaKotor), 0, ',', '.') . ')' : number_format($labaKotor, 0, ',', '.') }}
                        </td>
                    </tr>

                    <!-- 4. BEBAN OPERASI -->
                    <tr class="bg-primary/10">
                        <td class="py-2.5 px-6 font-bold text-foreground" colspan="4">Beban Operasi :</td>
                    </tr>
                    <!-- Beban Penjualan -->
                    <tr>
                        <td class="py-2 px-6 pl-8 font-semibold text-foreground" colspan="4">Beban Penjualan / Operasional</td>
                    </tr>
                    <tr class="hover:bg-muted/15">
                        <td class="py-2 px-6 pl-12 text-muted-foreground">Biaya Gaji / Upah Tenaga Kerja</td>
                        <td class="py-2 px-4 text-right font-mono text-foreground">{{ number_format($bebanGaji, 0, ',', '.') }}</td>
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-6"></td>
                    </tr>
                    <tr class="hover:bg-muted/15">
                        <td class="py-2 px-6 pl-10 text-foreground font-medium">Total Beban Penjualan</td>
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-4 text-right font-mono font-medium text-foreground">{{ number_format($totalBebanPenjualan, 0, ',', '.') }}</td>
                        <td class="py-2 px-6"></td>
                    </tr>

                    <!-- Beban Administrasi -->
                    <tr>
                        <td class="py-2 px-6 pl-8 font-semibold text-foreground" colspan="4">Beban Administrasi & Umum</td>
                    </tr>
                    <tr class="hover:bg-muted/15">
                        <td class="py-2 px-6 pl-12 text-muted-foreground">Biaya Listrik Mesin Roasting</td>
                        <td class="py-2 px-4 text-right font-mono text-foreground">{{ number_format($bebanListrik, 0, ',', '.') }}</td>
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-6"></td>
                    </tr>
                    <tr class="hover:bg-muted/15">
                        <td class="py-2 px-6 pl-12 text-muted-foreground">Biaya Penyusutan Mesin</td>
                        <td class="py-2 px-4 text-right font-mono text-foreground">{{ number_format($bebanPenyusutan, 0, ',', '.') }}</td>
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-6"></td>
                    </tr>
                    <tr class="hover:bg-muted/15">
                        <td class="py-2 px-6 pl-10 text-foreground font-medium">Total Beban Administrasi</td>
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-4 text-right font-mono font-medium text-foreground">{{ number_format($totalBebanAdministrasi, 0, ',', '.') }}</td>
                        <td class="py-2 px-6"></td>
                    </tr>

                    <!-- Jumlah Beban Operasi -->
                    <tr class="hover:bg-muted/15 border-t border-border/40 font-semibold">
                        <td class="py-2.5 px-6 font-bold text-foreground">Jumlah Beban Operasi</td>
                        <td class="py-2.5 px-4"></td>
                        <td class="py-2.5 px-4"></td>
                        <td class="py-2.5 px-6 text-right font-mono font-bold text-red-500">
                            {{ $jumlahBebanOperasi > 0 ? '(' . number_format($jumlahBebanOperasi, 0, ',', '.') . ')' : '0' }}
                        </td>
                    </tr>

                    <!-- 5. LABA BERSIH OPERASIONAL -->
                    <tr class="bg-muted/20 font-bold border-t border-b border-border/40">
                        <td class="py-2.5 px-6 font-black text-foreground">Laba Bersih Operasional</td>
                        <td class="py-2.5 px-4"></td>
                        <td class="py-2.5 px-4"></td>
                        <td class="py-2.5 px-6 text-right font-mono font-black text-foreground">
                            {{ $labaBersihOperasional < 0 ? '(' . number_format(abs($labaBersihOperasional), 0, ',', '.') . ')' : number_format($labaBersihOperasional, 0, ',', '.') }}
                        </td>
                    </tr>

                    <!-- 6. PENDAPATAN & BEBAN NON OPERASI -->
                    <tr class="bg-primary/10">
                        <td class="py-2.5 px-6 font-bold text-foreground" colspan="4">Pendapatan dan Beban Non Operasi</td>
                    </tr>
                    <tr class="hover:bg-muted/15">
                        <td class="py-2 px-6 pl-10 text-foreground">Pendapatan dan Beban Lainnya</td>
                        <td class="py-2 px-4 text-right font-mono text-muted-foreground">0</td>
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-6"></td>
                    </tr>
                    <tr class="hover:bg-muted/15">
                        <td class="py-2 px-6 pl-10 text-foreground">Jumlah Pendapatan dan Beban Lainnya</td>
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-4 text-right font-mono text-muted-foreground">0</td>
                        <td class="py-2 px-6 text-right font-mono text-muted-foreground">0</td>
                    </tr>

                    <!-- 7. LABA BERSIH AKHIR (DOUBLE UNDERLINE) -->
                    <tr class="bg-primary/10 border-t-2 border-b-4 border-double border-primary/60">
                        <td class="py-3.5 px-6 font-black text-foreground text-base tracking-wide whitespace-nowrap">
                            {{ $labaBersih >= 0 ? 'Laba Bersih' : 'Rugi Bersih' }}
                        </td>
                        <td class="py-3.5 px-4"></td>
                        <td class="py-3.5 px-4"></td>
                        <td class="py-3.5 px-6 text-right font-mono font-black text-xl whitespace-nowrap {{ $labaBersih >= 0 ? 'text-primary' : 'text-red-500' }}">
                            {{ $labaBersih < 0 ? '(Rp ' . number_format(abs($labaBersih), 0, ',', '.') . ')' : 'Rp ' . number_format($labaBersih, 0, ',', '.') }}
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>


    <!-- ========================================================================= -->
    <!-- TAB 2: LAPORAN ARUS KAS (METODE LANGSUNG) -->
    <!-- ========================================================================= -->
    <div x-show="activeReport === 'arus_kas'" x-transition class="bg-card border border-border/50 rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Header Dokumen Formal -->
        <div class="text-center py-6 px-4 border-b border-border/40 bg-muted/20 flex flex-col items-center justify-center relative">
            <h2 class="text-lg md:text-xl font-heading font-black uppercase tracking-wider text-foreground">TEFA JAVANICA</h2>
            <h3 class="text-base md:text-lg font-heading font-bold uppercase tracking-wide text-primary mt-0.5">LAPORAN ARUS KAS</h3>
            <p class="text-xs md:text-sm font-bold text-muted-foreground mt-0.5 uppercase tracking-wide font-mono">
                {{ strtoupper(\Carbon\Carbon::parse($start)->translatedFormat('d/F/Y')) }} - {{ strtoupper(\Carbon\Carbon::parse($end)->translatedFormat('d/F/Y')) }}
            </p>
            <p class="text-[11px] text-muted-foreground font-medium mt-0.5">(Dalam IDR)</p>

            <!-- Toggle Rincian Akun -->
            <button type="button" @click="showDetails = !showDetails" class="mt-3 text-xs text-primary font-bold flex items-center gap-1.5 hover:underline bg-background/80 border border-border/60 px-3 py-1.5 rounded-lg shadow-2xs transition-all">
                <span x-text="showDetails ? 'Sembunyikan Rincian Akun' : 'Lihat Rincian Akun Transaksi'"></span>
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" :class="showDetails ? 'rotate-180' : ''" class="transition-transform"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
        </div>

        <!-- Tabel Arus Kas Utama -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse min-w-[650px]">
                <thead>
                    <tr class="bg-primary text-primary-foreground">
                        <th class="py-3 px-6 font-bold text-left tracking-wide">Akun & Kategori</th>
                        <th class="py-3 px-6 font-bold text-right tracking-wide font-mono">
                            {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/20">

                    <!-- 1. OPERASIONAL -->
                    <tr class="bg-primary/10 border-t border-b border-border/30">
                        <td class="py-2.5 px-6 font-bold text-foreground text-sm tracking-wide" colspan="2">
                            Arus Kas dari Aktivitas Operasional
                        </td>
                    </tr>
                    <tr class="hover:bg-muted/15 transition-colors">
                        <td class="py-2.5 px-6 pl-10 text-foreground">
                            Penerimaan dari pelanggan
                            <div x-show="showDetails" x-transition class="text-xs text-muted-foreground mt-1.5 space-y-1 pl-3 border-l-2 border-primary/50">
                                <div>• Penjualan Kopi Lunas: <span class="font-mono text-foreground font-semibold">Rp {{ number_format($totalLunas, 0, ',', '.') }}</span></div>
                                <div>• Pembayaran Uang Muka (DP): <span class="font-mono text-foreground font-semibold">Rp {{ number_format($totalDP, 0, ',', '.') }}</span></div>
                            </div>
                        </td>
                        <td class="py-2.5 px-6 text-right font-mono font-medium text-foreground align-top">
                            {{ $penerimaanPelanggan > 0 ? number_format($penerimaanPelanggan, 0, ',', '.') : '0' }}
                        </td>
                    </tr>
                    <tr class="hover:bg-muted/15 transition-colors">
                        <td class="py-2 px-6 pl-10 text-foreground">Aset lancar lainnya</td>
                        <td class="py-2 px-6 text-right font-mono font-medium text-foreground">0</td>
                    </tr>
                    <tr class="hover:bg-muted/15 transition-colors">
                        <td class="py-2.5 px-6 pl-10 text-foreground">
                            Pembayaran ke pemasok
                            <div x-show="showDetails" x-transition class="text-xs text-muted-foreground mt-1.5 space-y-1 pl-3 border-l-2 border-primary/50">
                                <div>• Belanja Bahan Baku (Green Bean): <span class="font-mono text-foreground font-semibold">Rp {{ number_format($detailOperasional['pembayaran_pemasok']['rincian']['Belanja Bahan Baku (Green Bean)'], 0, ',', '.') }}</span></div>
                                <div>• Biaya Kemasan (Packaging): <span class="font-mono text-foreground font-semibold">Rp {{ number_format($detailOperasional['pembayaran_pemasok']['rincian']['Biaya Kemasan (Packaging)'], 0, ',', '.') }}</span></div>
                            </div>
                        </td>
                        <td class="py-2.5 px-6 text-right font-mono font-medium text-foreground align-top">
                            {{ $pembayaranPemasok > 0 ? '(' . number_format($pembayaranPemasok, 0, ',', '.') . ')' : '0' }}
                        </td>
                    </tr>
                    <tr class="hover:bg-muted/15 transition-colors">
                        <td class="py-2 px-6 pl-10 text-foreground">Kartu kredit dan liabilitas jangka pendek lainnya</td>
                        <td class="py-2 px-6 text-right font-mono font-medium text-foreground">0</td>
                    </tr>
                    <tr class="hover:bg-muted/15 transition-colors">
                        <td class="py-2 px-6 pl-10 text-foreground">Pendapatan lainnya</td>
                        <td class="py-2 px-6 text-right font-mono font-medium text-foreground">0</td>
                    </tr>
                    <tr class="hover:bg-muted/15 transition-colors">
                        <td class="py-2.5 px-6 pl-10 text-foreground">
                            Pengeluaran operasional
                            <div x-show="showDetails" x-transition class="text-xs text-muted-foreground mt-1.5 space-y-1 pl-3 border-l-2 border-primary/50">
                                <div>• Upah Tenaga Kerja: <span class="font-mono text-foreground font-semibold">Rp {{ number_format($detailOperasional['pengeluaran_operasional']['rincian']['Upah Tenaga Kerja'], 0, ',', '.') }}</span></div>
                                <div>• Beban Listrik Roasting (Riil): <span class="font-mono text-foreground font-semibold">Rp {{ number_format($detailOperasional['pengeluaran_operasional']['rincian']['Biaya Listrik Operasional'], 0, ',', '.') }}</span></div>
                                <div class="text-[10px] text-amber-600 dark:text-amber-400 italic font-sans">*Penyusutan mesin dikeluarkan dari kas keluar (beban non-kas)</div>
                            </div>
                        </td>
                        <td class="py-2.5 px-6 text-right font-mono font-medium text-foreground align-top">
                            {{ $pengeluaranOperasional > 0 ? '(' . number_format($pengeluaranOperasional, 0, ',', '.') . ')' : '0' }}
                        </td>
                    </tr>
                    <tr class="bg-muted/30 font-bold border-t border-b border-border/60">
                        <td class="py-2.5 px-6 font-bold text-foreground">Kas Bersih dari Aktivitas Operasional</td>
                        <td class="py-2.5 px-6 text-right font-mono font-bold {{ $kasBersihOperasional >= 0 ? 'text-foreground' : 'text-red-500' }}">
                            {{ $kasBersihOperasional < 0 ? '(' . number_format(abs($kasBersihOperasional), 0, ',', '.') . ')' : number_format($kasBersihOperasional, 0, ',', '.') }}
                        </td>
                    </tr>

                    <tr class="h-3 bg-background"><td colspan="2"></td></tr>

                    <!-- 2. INVESTASI -->
                    <tr class="bg-primary/10 border-t border-b border-border/30">
                        <td class="py-2.5 px-6 font-bold text-foreground text-sm tracking-wide" colspan="2">
                            Arus Kas dari Aktivitas Investasi
                        </td>
                    </tr>
                    <tr class="hover:bg-muted/15 transition-colors">
                        <td class="py-2 px-6 pl-10 text-foreground">Perolehan/Penjualan aset</td>
                        <td class="py-2 px-6 text-right font-mono font-medium text-foreground">0</td>
                    </tr>
                    <tr class="hover:bg-muted/15 transition-colors">
                        <td class="py-2 px-6 pl-10 text-foreground">Aktivitas investasi lainnya</td>
                        <td class="py-2 px-6 text-right font-mono font-medium text-foreground">0</td>
                    </tr>
                    <tr class="bg-muted/30 font-bold border-t border-b border-border/60">
                        <td class="py-2.5 px-6 font-bold text-foreground">Kas Bersih dari Aktivitas Investasi</td>
                        <td class="py-2.5 px-6 text-right font-mono font-bold text-foreground">0</td>
                    </tr>

                    <tr class="h-3 bg-background"><td colspan="2"></td></tr>

                    <!-- 3. PENDANAAN -->
                    <tr class="bg-primary/10 border-t border-b border-border/30">
                        <td class="py-2.5 px-6 font-bold text-foreground text-sm tracking-wide" colspan="2">
                            Arus Kas dari Aktivitas Pendanaan
                        </td>
                    </tr>
                    <tr class="hover:bg-muted/15 transition-colors">
                        <td class="py-2 px-6 pl-10 text-foreground">Pembayaran/Penerimaan pinjaman</td>
                        <td class="py-2 px-6 text-right font-mono font-medium text-foreground">0</td>
                    </tr>
                    <tr class="hover:bg-muted/15 transition-colors">
                        <td class="py-2 px-6 pl-10 text-foreground">Ekuitas/Modal</td>
                        <td class="py-2 px-6 text-right font-mono font-medium text-foreground">0</td>
                    </tr>
                    <tr class="bg-muted/30 font-bold border-t border-b border-border/60">
                        <td class="py-2.5 px-6 font-bold text-foreground">Kas Bersih dari Aktivitas Pendanaan</td>
                        <td class="py-2.5 px-6 text-right font-mono font-bold text-foreground">0</td>
                    </tr>

                    <tr class="h-3 bg-background"><td colspan="2"></td></tr>

                    <!-- 4. REKONSILIASI KAS -->
                    <tr class="hover:bg-muted/15 transition-colors border-t border-border/40">
                        <td class="py-2.5 px-6 font-medium text-foreground">Kenaikan (penurunan) kas</td>
                        <td class="py-2.5 px-6 text-right font-mono font-semibold text-foreground">
                            {{ $kenaikanKas < 0 ? '(' . number_format(abs($kenaikanKas), 0, ',', '.') . ')' : number_format($kenaikanKas, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="hover:bg-muted/15 transition-colors">
                        <td class="py-2.5 px-6 font-medium text-foreground">Total revaluasi bank</td>
                        <td class="py-2.5 px-6 text-right font-mono font-semibold text-foreground">0</td>
                    </tr>
                    <tr class="hover:bg-muted/15 transition-colors">
                        <td class="py-2.5 px-6 font-medium text-foreground">Saldo kas awal</td>
                        <td class="py-2.5 px-6 text-right font-mono font-semibold text-foreground">
                            {{ $saldoKasAwal < 0 ? '(' . number_format(abs($saldoKasAwal), 0, ',', '.') . ')' : number_format($saldoKasAwal, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="bg-primary/10 border-t-2 border-b-4 border-double border-primary/60">
                        <td class="py-3.5 px-6 font-black text-foreground text-base tracking-wide">
                            Saldo kas akhir
                        </td>
                        <td class="py-3.5 px-6 text-right font-mono font-black text-primary text-xl">
                            {{ $saldoKasAkhir < 0 ? '(Rp ' . number_format(abs($saldoKasAkhir), 0, ',', '.') . ')' : 'Rp ' . number_format($saldoKasAkhir, 0, ',', '.') }}
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
