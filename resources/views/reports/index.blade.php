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
        <div class="flex flex-col sm:flex-row gap-4 mt-4 md:mt-0 items-center bg-muted/20 p-2 sm:pr-2 sm:pl-4 rounded-2xl border border-border/50 shadow-sm">
            <!-- Filter Tanggal -->
            <div class="flex flex-col w-full sm:w-auto">
                <label class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1 ml-1">Periode Laporan</label>
                <div class="bg-background border border-border/60 rounded-xl px-3 py-2 text-sm font-bold text-primary flex items-center gap-2 shadow-sm transition-colors hover:border-primary/50">
                    <svg class="text-primary/70" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                    <select class="bg-transparent border-none focus:ring-0 outline-none cursor-pointer w-full">
                        <option value="this_month">Bulan Ini (Mei 2026)</option>
                        <option value="last_month">Bulan Lalu (Apr 2026)</option>
                        <option value="this_year">Tahun Ini</option>
                    </select>
                </div>
            </div>
            
            <div class="hidden sm:block w-px h-12 bg-border/60 rounded-full"></div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col gap-1.5 w-full sm:w-[150px]">
                <button @click="printReport()" class="w-full bg-secondary/10 text-secondary hover:bg-secondary hover:text-secondary-foreground border border-secondary/20 px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-between group shadow-sm">
                    <span class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                        Cetak PDF
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-hover:opacity-100 transition-transform transform translate-x-[-5px] group-hover:translate-x-0"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
                <button @click="isImportModalOpen = true" class="w-full bg-green-500/10 text-green-600 hover:bg-green-600 hover:text-white border border-green-500/20 px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-between group shadow-sm">
                    <span class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        Import Excel
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-hover:opacity-100 transition-transform transform translate-x-[-5px] group-hover:translate-x-0"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="print:hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Pendapatan -->
        <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm flex items-center gap-3 relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-green-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-500 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Total Pendapatan</p>
                <p class="text-[15px] font-heading font-black text-foreground whitespace-nowrap">Rp 12.500.000</p>
            </div>
        </div>
        <!-- HPP -->
        <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm flex items-center gap-3 relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-orange-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Harga Pokok (HPP)</p>
                <p class="text-[15px] font-heading font-black text-foreground whitespace-nowrap">Rp 4.200.000</p>
            </div>
        </div>
        <!-- Operasional -->
        <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm flex items-center gap-3 relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-red-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center text-red-500 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Biaya Operasional</p>
                <p class="text-[15px] font-heading font-black text-foreground whitespace-nowrap">Rp 2.100.000</p>
            </div>
        </div>
        <!-- Laba Bersih -->
        <div class="bg-card border border-border/40 rounded-2xl p-5 shadow-sm flex items-center gap-3 relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.3 7H6a2 2 0 0 1 2 2v5"/><path d="M7.9 15.2c.4 1 1.4 1.8 2.5 1.8h7.1c1.5 0 2.7-1.4 2.4-2.9-.2-.8-.8-1.4-1.6-1.6-.5-.1-1.1-.1-1.6.2-1.4.7-3.1.2-3.8-1.2s-.2-3.1 1.2-3.8c.5-.3 1.1-.3 1.6-.2.8.2 1.4.8 1.6 1.6.3 1.5-.9 2.9-2.4 2.9H8.6c-1.1 0-2.1.8-2.5 1.8"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Laba Bersih</p>
                <p class="text-[15px] font-heading font-black text-foreground whitespace-nowrap">Rp 6.200.000</p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="print:hidden flex overflow-x-auto hide-scrollbar gap-2 p-1 bg-muted/30 rounded-xl border border-border/50">
        <button @click="activeTab = 'labarugi'" 
            class="flex-1 min-w-[150px] py-3 px-4 rounded-lg font-bold text-sm transition-all duration-300 flex justify-center items-center gap-2"
            :class="activeTab === 'labarugi' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
            Laba / Rugi
        </button>
        <button @click="activeTab = 'aruskas'" 
            class="flex-1 min-w-[150px] py-3 px-4 rounded-lg font-bold text-sm transition-all duration-300 flex justify-center items-center gap-2"
            :class="activeTab === 'aruskas' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8a2 2 0 0 0 2-2v-3.5c0-1.19.68-2.32 1.77-2.8l.64-.28a2.5 2.5 0 0 0 0-4.58l-.64-.28C18.68 7.08 18 5.95 18 4.76V3a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v1.76c0 1.19-.68 2.32-1.77 2.8l-.64.28a2.5 2.5 0 0 0 0 4.58l.64.28c1.09.48 1.77 1.61 1.77 2.8V19a2 2 0 0 0 2 2Z"/></svg>
            Arus Kas (Cash Flow)
        </button>
    </div>

    <!-- Print Header -->
    <div class="hidden print:block text-center mb-8 pb-4 border-b-2 border-primary">
        <h1 class="text-3xl font-heading font-black text-primary">TEFA Javanica</h1>
        <p class="text-lg font-bold text-foreground mt-1">Laporan Keuangan Roastery</p>
        <p class="text-sm text-muted-foreground mt-1" x-text="activeTab === 'labarugi' ? 'Laporan Laba Rugi - Periode: Mei 2026' : 'Riwayat Arus Kas - Periode: Mei 2026'"></p>
    </div>

    <!-- Tab Content: Laba Rugi -->
    <div x-show="activeTab === 'labarugi'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden print:border-none print:shadow-none print:rounded-none">
        <div class="p-6 border-b border-primary/40 bg-primary text-primary-foreground">
            <h2 class="text-xl font-heading font-bold text-white">Laporan Laba Rugi</h2>
            <p class="text-sm text-white/80">Periode: 1 Mei 2026 - 31 Mei 2026</p>
        </div>
        <div class="p-6">
            <table class="w-full text-left">
                <tbody>
                    <!-- Pendapatan -->
                    <tr class="border-b border-border/30">
                        <td class="py-4 font-bold text-foreground text-lg" colspan="2">Pendapatan</td>
                    </tr>
                    <tr class="hover:bg-muted/10">
                        <td class="py-2 pl-6 text-muted-foreground">Penjualan Biji Kopi Matang (Retail)</td>
                        <td class="py-2 text-right font-mono font-medium text-foreground">Rp 9.500.000</td>
                    </tr>
                    <tr class="hover:bg-muted/10 border-b border-border/30">
                        <td class="py-2 pl-6 text-muted-foreground">Penjualan Grosir / Wholesale</td>
                        <td class="py-2 text-right font-mono font-medium text-foreground">Rp 3.000.000</td>
                    </tr>
                    <tr class="bg-muted/10 border-b border-border/50">
                        <td class="py-3 pl-6 font-bold text-green-600">Total Pendapatan</td>
                        <td class="py-3 text-right font-mono font-bold text-green-600">Rp 12.500.000</td>
                    </tr>

                    <!-- HPP -->
                    <tr>
                        <td class="py-4 font-bold text-foreground text-lg" colspan="2">Harga Pokok Penjualan (HPP)</td>
                    </tr>
                    <tr class="hover:bg-muted/10">
                        <td class="py-2 pl-6 text-muted-foreground">Persediaan Awal Green Bean</td>
                        <td class="py-2 text-right font-mono font-medium text-foreground">Rp 2.000.000</td>
                    </tr>
                    <tr class="hover:bg-muted/10">
                        <td class="py-2 pl-6 text-muted-foreground">Pembelian Green Bean (Pengadaan)</td>
                        <td class="py-2 text-right font-mono font-medium text-foreground">Rp 3.500.000</td>
                    </tr>
                    <tr class="hover:bg-muted/10 border-b border-border/30">
                        <td class="py-2 pl-6 text-muted-foreground">Dikurangi: Persediaan Akhir Green Bean</td>
                        <td class="py-2 text-right font-mono font-medium text-foreground">(Rp 1.300.000)</td>
                    </tr>
                    <tr class="bg-muted/10 border-b border-border/50">
                        <td class="py-3 pl-6 font-bold text-orange-500">Total HPP</td>
                        <td class="py-3 text-right font-mono font-bold text-orange-500">(Rp 4.200.000)</td>
                    </tr>

                    <!-- Laba Kotor -->
                    <tr class="bg-muted/30 border-y-2 border-border/60 shadow-sm">
                        <td class="py-3 pl-6 font-bold text-foreground text-[15px] uppercase tracking-wider">Laba Kotor</td>
                        <td class="py-3 px-6 text-right font-mono font-black text-foreground text-lg">Rp 8.300.000</td>
                    </tr>

                    <!-- Biaya Operasional -->
                    <tr>
                        <td class="py-4 font-bold text-foreground text-lg" colspan="2">Biaya Operasional</td>
                    </tr>
                    <tr class="hover:bg-muted/10">
                        <td class="py-2 pl-6 text-muted-foreground">Biaya Upah Roaster (Log Kerja)</td>
                        <td class="py-2 text-right font-mono font-medium text-foreground">Rp 900.000</td>
                    </tr>
                    <tr class="hover:bg-muted/10">
                        <td class="py-2 pl-6 text-muted-foreground">Biaya Kemasan (Packaging)</td>
                        <td class="py-2 text-right font-mono font-medium text-foreground">Rp 450.000</td>
                    </tr>
                    <tr class="hover:bg-muted/10">
                        <td class="py-2 pl-6 text-muted-foreground">Biaya Listrik & Gas (Komponen Biaya)</td>
                        <td class="py-2 text-right font-mono font-medium text-foreground">Rp 750.000</td>
                    </tr>
                    <tr class="bg-muted/10 border-b border-border/50">
                        <td class="py-3 pl-6 font-bold text-red-500">Total Biaya Operasional</td>
                        <td class="py-3 text-right font-mono font-bold text-red-500">(Rp 2.100.000)</td>
                    </tr>

                    <!-- Laba Bersih -->
                    <tr class="bg-primary">
                        <td class="py-3 px-6 font-black text-primary-foreground text-[15px] uppercase tracking-wider rounded-bl-lg">Laba Bersih Operasional</td>
                        <td class="py-3 px-6 text-right font-mono font-black text-primary-foreground text-xl rounded-br-lg">Rp 6.200.000</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab Content: Arus Kas -->
    <div x-show="activeTab === 'aruskas'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden print:border-none print:shadow-none print:rounded-none" style="display: none;">
        <div class="p-6 border-b border-primary/40 bg-primary flex justify-between items-center text-primary-foreground">
            <div>
                <h2 class="text-xl font-heading font-bold text-primary">Riwayat Arus Kas (Buku Kas)</h2>
                <p class="text-sm text-muted-foreground">Mencatat seluruh uang masuk dan keluar.</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-white/80 uppercase tracking-wider">Saldo Akhir Kas</p>
                <p class="text-2xl font-mono font-black text-white">Rp 24.550.000</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Keterangan</th>
                        <th class="p-4">Tipe</th>
                        <th class="p-4 text-right">Nominal</th>
                        <th class="p-4 text-right">Saldo Kas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    <tr class="hover:bg-muted/10 transition-colors">
                        <td class="p-4 text-sm font-mono text-muted-foreground">01 Mei 2026</td>
                        <td class="p-4 font-bold text-foreground">Saldo Awal Bulan</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 bg-muted text-muted-foreground text-[10px] font-black rounded-full uppercase tracking-widest border border-border">Sistem</span>
                        </td>
                        <td class="p-4 text-right font-mono font-medium text-foreground">-</td>
                        <td class="p-4 text-right font-mono font-bold text-primary">Rp 18.350.000</td>
                    </tr>
                    <tr class="hover:bg-muted/10 transition-colors">
                        <td class="p-4 text-sm font-mono text-muted-foreground">02 Mei 2026</td>
                        <td class="p-4">
                            <p class="font-bold text-foreground">Pengadaan Green Bean</p>
                            <p class="text-xs text-muted-foreground">Invoice: PGD-0009 - Robusta Dampit</p>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 bg-red-500/10 text-red-600 text-[10px] font-black rounded-full uppercase tracking-widest border border-red-500/20">Keluar</span>
                        </td>
                        <td class="p-4 text-right font-mono font-bold text-red-500">Rp 3.500.000</td>
                        <td class="p-4 text-right font-mono font-bold text-primary">Rp 14.850.000</td>
                    </tr>
                    <tr class="hover:bg-muted/10 transition-colors">
                        <td class="p-4 text-sm font-mono text-muted-foreground">05 Mei 2026</td>
                        <td class="p-4">
                            <p class="font-bold text-foreground">Penjualan Retail (POS)</p>
                            <p class="text-xs text-muted-foreground">Invoice: INV-202605-001</p>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 bg-green-500/10 text-green-600 text-[10px] font-black rounded-full uppercase tracking-widest border border-green-500/20">Masuk</span>
                        </td>
                        <td class="p-4 text-right font-mono font-bold text-green-600">Rp 1.200.000</td>
                        <td class="p-4 text-right font-mono font-bold text-primary">Rp 16.050.000</td>
                    </tr>
                    <tr class="hover:bg-muted/10 transition-colors">
                        <td class="p-4 text-sm font-mono text-muted-foreground">10 Mei 2026</td>
                        <td class="p-4">
                            <p class="font-bold text-foreground">Pembayaran Upah Roaster</p>
                            <p class="text-xs text-muted-foreground">10 Batch Roasting x Rp 15.000</p>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 bg-red-500/10 text-red-600 text-[10px] font-black rounded-full uppercase tracking-widest border border-red-500/20">Keluar</span>
                        </td>
                        <td class="p-4 text-right font-mono font-bold text-red-500">Rp 150.000</td>
                        <td class="p-4 text-right font-mono font-bold text-primary">Rp 15.900.000</td>
                    </tr>
                    <tr class="hover:bg-muted/10 transition-colors">
                        <td class="p-4 text-sm font-mono text-muted-foreground">15 Mei 2026</td>
                        <td class="p-4">
                            <p class="font-bold text-foreground">Penjualan Grosir</p>
                            <p class="text-xs text-muted-foreground">Invoice: INV-202605-002 (Cafe XYZ)</p>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 bg-green-500/10 text-green-600 text-[10px] font-black rounded-full uppercase tracking-widest border border-green-500/20">Masuk</span>
                        </td>
                        <td class="p-4 text-right font-mono font-bold text-green-600">Rp 8.650.000</td>
                        <td class="p-4 text-right font-mono font-bold text-primary">Rp 24.550.000</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Import Excel -->
    <div x-show="isImportModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-background/80 backdrop-blur-sm transition-opacity" @click="isImportModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-card border border-border/50 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                <div class="px-6 py-5 border-b border-border/50 flex justify-between items-center bg-muted/30">
                    <h3 class="text-xl font-heading font-black text-primary">Import Laporan 2025</h3>
                    <button @click="isImportModalOpen = false" class="text-muted-foreground hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <div class="px-6 py-6 space-y-4">
                    <div class="border-2 border-dashed border-border rounded-xl p-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto text-green-500 mb-3"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h2"/><path d="M8 17h2"/><path d="M14 13h2"/><path d="M14 17h2"/></svg>
                        <p class="text-sm font-bold text-foreground mb-1">Upload File Excel (.xlsx)</p>
                        <p class="text-xs text-muted-foreground mb-4">Sistem akan otomatis membaca Pemasukan & Pengeluaran dari file ini.</p>
                        <button class="bg-secondary/20 text-secondary font-bold px-4 py-2 rounded-lg text-xs">Pilih File</button>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-border/50 bg-muted/10 flex justify-end gap-3">
                    <button type="button" @click="isImportModalOpen = false" class="px-4 py-2 text-sm font-bold text-muted-foreground hover:text-foreground">Tutup (Mockup)</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Input Modal Awal -->
    <div x-show="isModalAwalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-background/80 backdrop-blur-sm transition-opacity" @click="isModalAwalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-card border border-border/50 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-full">
                <div class="px-6 py-5 border-b border-border/50 flex justify-between items-center bg-muted/30">
                    <h3 class="text-xl font-heading font-black text-primary">Set Modal Awal</h3>
                    <button @click="isModalAwalOpen = false" class="text-muted-foreground hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <label class="block text-xs font-bold text-muted-foreground uppercase tracking-widest mb-2">Nominal Modal (Rp)</label>
                    <input type="text" value="10.000.000" class="w-full bg-background border border-border rounded-xl px-4 py-3 text-lg font-mono font-bold focus:ring-2 focus:ring-primary focus:border-primary">
                    <p class="text-[10px] text-muted-foreground mt-2">Dana ini akan masuk ke Saldo Kas Utama.</p>
                </div>
                <div class="px-6 py-4 border-t border-border/50 bg-muted/10 flex justify-end gap-3">
                    <button type="button" @click="isModalAwalOpen = false" class="px-4 py-2 text-sm font-bold text-muted-foreground hover:text-foreground">Batal</button>
                    <button type="button" @click="isModalAwalOpen = false" class="px-6 py-2 bg-primary text-primary-foreground rounded-xl text-sm font-bold">Simpan</button>
                </div>
            </div>
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
            activeTab: 'labarugi',
            isImportModalOpen: false,
            isModalAwalOpen: false,
            
            printReport() {
                window.print();
            }
        }));
    });
</script>
@endpush
@endsection
