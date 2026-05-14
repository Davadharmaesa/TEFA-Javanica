@extends('layouts.app')

@section('title', 'Gudang & Inventory - TEFA Javanica')

@section('content')
<div x-data="gudangData()" class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-card p-6 rounded-2xl shadow-sm border border-border/40">
        <div>
            <h1 class="text-2xl font-heading font-bold text-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                Manajemen Gudang
            </h1>
            <p class="text-sm text-muted-foreground mt-1">Pantau dan kelola stok bahan baku, barang setengah jadi, dan produk siap jual.</p>
        </div>
        <div class="flex gap-2">
            <!-- Add Button Dynamic based on Tab -->
            <button @click="openPackModal()" class="bg-primary text-primary-foreground hover:bg-primary/90 px-4 py-2.5 rounded-xl text-sm font-bold shadow-md transition-all flex items-center gap-2 hover:scale-105" x-show="activeTab === 'roastedbean' || activeTab === 'produk'">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                Proses Kemas
            </button>
            <button @click="openModal('add', activeTab)" class="bg-secondary text-secondary-foreground hover:bg-secondary/90 px-4 py-2.5 rounded-xl text-sm font-bold shadow-md transition-all flex items-center gap-2 hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span x-text="'Tambah Stok ' + getTabName()"></span>
            </button>
        </div>
    </div>

    <!-- Stats Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Stat Card 1 -->
        <div class="bg-card border border-border/40 rounded-2xl p-6 shadow-sm flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-muted-foreground uppercase tracking-wider">Green Bean</p>
                <p class="text-3xl font-heading font-black text-primary">{{ number_format($greenBeans->sum('stok'), 1, ',', '.') }} <span class="text-sm font-medium text-muted-foreground">Kg</span></p>
            </div>
        </div>
        <!-- Stat Card 2 -->
        <div class="bg-card border border-border/40 rounded-2xl p-6 shadow-sm flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-secondary to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-14 h-14 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-muted-foreground uppercase tracking-wider">Roasted Bean</p>
                <p class="text-3xl font-heading font-black text-foreground">{{ number_format($roastedBeans->sum('stok'), 1, ',', '.') }} <span class="text-sm font-medium text-muted-foreground">Kg</span></p>
            </div>
        </div>
        <!-- Stat Card 3 -->
        <div class="bg-card border border-border/40 rounded-2xl p-6 shadow-sm flex items-center gap-4 relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-accent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-14 h-14 rounded-xl bg-accent/10 flex items-center justify-center text-accent shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-muted-foreground uppercase tracking-wider">Produk Kemasan</p>
                <p class="text-3xl font-heading font-black text-foreground">{{ number_format($packagings->sum('stok'), 0, ',', '.') }} <span class="text-sm font-medium text-muted-foreground">Pcs</span></p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex overflow-x-auto hide-scrollbar gap-2 p-1 bg-muted/30 rounded-xl border border-border/50">
        <button @click="activeTab = 'greenbean'" 
            class="flex-1 min-w-[150px] py-3 px-4 rounded-lg font-bold text-sm transition-all duration-300 flex justify-center items-center gap-2"
            :class="activeTab === 'greenbean' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg>
            Green Bean
        </button>
        <button @click="activeTab = 'roastedbean'" 
            class="flex-1 min-w-[150px] py-3 px-4 rounded-lg font-bold text-sm transition-all duration-300 flex justify-center items-center gap-2"
            :class="activeTab === 'roastedbean' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
            Roasted Bean
        </button>
        <button @click="activeTab = 'produk'" 
            class="flex-1 min-w-[150px] py-3 px-4 rounded-lg font-bold text-sm transition-all duration-300 flex justify-center items-center gap-2"
            :class="activeTab === 'produk' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            Produk Kemasan
        </button>
    </div>

    <!-- Tab Content: Green Bean -->
    <div x-show="activeTab === 'greenbean'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4">ID / Kode</th>
                        <th class="p-4">Nama Biji Mentah</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4 text-right">Stok Total</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    <template x-for="item in dataGreenBean" :key="item.id">
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4">
                                <span class="text-sm font-mono text-muted-foreground" x-text="item.kode"></span>
                            </td>
                            <td class="p-4">
                                <p class="font-bold text-primary" x-text="item.nama"></p>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 bg-accent/10 text-accent text-[10px] font-black rounded-full uppercase tracking-widest" x-text="item.kategori"></span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold" :class="item.stok < 10 ? 'text-red-500' : 'text-primary'">
                                <span x-text="item.stok"></span> Kg
                            </td>
                            <td class="p-4 text-center">
                                <template x-if="item.stok > 5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-green-500/10 text-green-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Aman
                                    </span>
                                </template>
                                <template x-if="item.stok > 1 && item.stok <= 5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-500/10 text-yellow-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-600"></span> Menipis
                                    </span>
                                </template>
                                <template x-if="item.stok <= 1">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-red-500/10 text-red-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Kritis
                                    </span>
                                </template>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openModal('edit', 'greenbean', item)" class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <form :action="'/inventory/green-bean/' + item.id" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab Content: Roasted Bean -->
    <div x-show="activeTab === 'roastedbean'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4">ID / Kode</th>
                        <th class="p-4">Nama Produk Kopi Matang</th>
                        <th class="p-4">Level Roast</th>
                        <th class="p-4">Tgl & Expire</th>
                        <th class="p-4 text-right">Stok (Kg)</th>
                        <th class="p-4 text-right">Sisa (Gram)</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    <template x-for="item in dataRoastedBean" :key="item.id">
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4">
                                <span class="text-sm font-mono text-muted-foreground" x-text="item.kode"></span>
                            </td>
                            <td class="p-4">
                                <p class="font-bold text-primary" x-text="item.nama"></p>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 bg-primary/10 text-primary text-[10px] font-black rounded-full uppercase tracking-widest" x-text="item.bentuk"></span>
                            </td>
                            <td class="p-4">
                                <p class="text-xs font-bold text-muted-foreground">Roast: <span x-text="item.tanggal_roasting ? item.tanggal_roasting : '-'"></span></p>
                                <p class="text-xs font-bold text-red-500">Exp: <span x-text="item.tanggal_expire ? item.tanggal_expire : '-'"></span></p>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-primary">
                                <span x-text="item.stok"></span> Kg
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-yellow-600">
                                <span x-text="item.sisa_stok ? item.sisa_stok : '0'"></span> g
                            </td>
                            <td class="p-4 text-center">
                                <template x-if="item.stok > 5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-green-500/10 text-green-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Aman
                                    </span>
                                </template>
                                <template x-if="item.stok <= 5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-500/10 text-yellow-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-600"></span> Menipis
                                    </span>
                                </template>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openModal('edit', 'roastedbean', item)" class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <form :action="'/inventory/roasted-bean/' + item.id" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab Content: Produk Kemasan -->
    <div x-show="activeTab === 'produk'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4">Nama Produk Kemasan</th>
                        <th class="p-4">Berat</th>
                        <th class="p-4 text-right">Stok (Pcs)</th>
                        <th class="p-4 text-right">HPP</th>
                        <th class="p-4 text-right">Harga Jual</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    <template x-for="item in dataProduk" :key="item.id">
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4">
                                <p class="font-bold text-foreground" x-text="item.nama"></p>
                            </td>
                            <td class="p-4">
                                <span class="text-sm font-mono text-muted-foreground" x-text="item.berat + 'g'"></span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-primary">
                                <span x-text="item.stok"></span>
                            </td>
                            <td class="p-4 text-right text-sm text-muted-foreground">
                                Rp <span x-text="formatRupiah(item.hpp)"></span>
                            </td>
                            <td class="p-4 text-right font-bold text-green-600">
                                Rp <span x-text="formatRupiah(item.harga)"></span>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openModal('edit', 'produk', item)" class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <form :action="'/inventory/packaging/' + item.id" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Riwayat Pengadaan dihapus sesuai permintaan evaluasi -->

    <!-- Modal Tambah/Edit Green Bean -->
    <div x-show="isModalOpen && activeTab === 'greenbean'" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display: none;" x-transition>
        <div @click.away="isModalOpen = false" class="bg-card w-full max-w-lg rounded-2xl shadow-xl border border-border/40 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-heading font-bold text-primary" x-text="isEditMode ? 'Edit Green Bean' : 'Tambah Green Bean'"></h2>
                <button @click="isModalOpen = false" class="text-muted-foreground hover:text-foreground transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form :action="getFormAction()" method="POST">
                @csrf
                <template x-if="isEditMode"><input type="hidden" name="_method" value="PUT"></template>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-1">Nama Biji Kopi Mentah</label>
                        <input type="text" name="nama_biji" x-model="formData.nama" placeholder="Contoh: Arabica Gayo Premium" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-1">Kategori / Asal Kopi</label>
                        <input type="text" name="asal_kopi" x-model="formData.kategori" placeholder="Contoh: Aceh Gayo" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-foreground mb-1">Stok (Gram)</label>
                            <input type="number" name="stok_gram" x-model="formData.stok_raw" placeholder="Contoh: 50000" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-foreground mb-1">Harga Beli Paket (Rp)</label>
                            <input type="number" name="harga_beli" x-model="formData.harga_beli" placeholder="Contoh: 150000" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-foreground mb-1">Tanggal Beli</label>
                            <input type="date" name="tanggal_beli" x-model="formData.tanggal_beli" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl font-bold text-muted-foreground hover:bg-muted/50 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#526B42] text-white rounded-xl font-bold hover:bg-[#3f5232] shadow-md transition-all hover:scale-105" x-text="isEditMode ? 'Simpan Perubahan' : 'Simpan Data'"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah/Edit Roasted Bean -->
    <div x-show="isModalOpen && activeTab === 'roastedbean'" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display: none;" x-transition>
        <div @click.away="isModalOpen = false" class="bg-card w-full max-w-lg rounded-2xl shadow-xl border border-border/40 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-heading font-bold text-primary" x-text="isEditMode ? 'Edit Roasted Bean' : 'Tambah Roasted Bean'"></h2>
                <button @click="isModalOpen = false" class="text-muted-foreground hover:text-foreground transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form :action="getFormAction()" method="POST">
                @csrf
                <template x-if="isEditMode"><input type="hidden" name="_method" value="PUT"></template>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-1">Nama Produk Kopi Matang</label>
                        <input type="text" name="nama_kopi" x-model="formData.nama" placeholder="Contoh: Gayo Espresso Blend" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-1">Bentuk / Level Roast</label>
                        <select name="bentuk_akhir" x-model="formData.bentuk" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                            <option value="Light Roast">Light Roast</option>
                            <option value="Medium Roast">Medium Roast</option>
                            <option value="Medium-Dark Roast">Medium-Dark Roast</option>
                            <option value="Dark Roast">Dark Roast</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-foreground mb-1">Stok (Gram)</label>
                            <input type="number" name="total_stok" x-model="formData.stok_raw" placeholder="Contoh: 15000" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-foreground mb-1">Satuan Dasar</label>
                            <input type="text" name="satuan_dasar" x-model="formData.satuan_dasar" value="gram" readonly class="w-full bg-muted border border-border rounded-lg px-4 py-2.5 focus:outline-none text-muted-foreground transition-all">
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl font-bold text-muted-foreground hover:bg-muted/50 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#526B42] text-white rounded-xl font-bold hover:bg-[#3f5232] shadow-md transition-all hover:scale-105" x-text="isEditMode ? 'Simpan Perubahan' : 'Simpan Data'"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah/Edit Produk Kemasan -->
    <div x-show="isModalOpen && activeTab === 'produk'" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display: none;" x-transition>
        <div @click.away="isModalOpen = false" class="bg-card w-full max-w-lg rounded-2xl shadow-xl border border-border/40 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-heading font-bold text-primary" x-text="isEditMode ? 'Edit Produk Kemasan' : 'Tambah Produk Kemasan'"></h2>
                <button @click="isModalOpen = false" class="text-muted-foreground hover:text-foreground transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form :action="getFormAction()" method="POST">
                @csrf
                <template x-if="isEditMode"><input type="hidden" name="_method" value="PUT"></template>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-1">Nama Produk Kemasan</label>
                        <input type="text" name="nama_kemasan" x-model="formData.nama" placeholder="Contoh: Pouch Kopi Gayo 250g" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-1">Tipe / Kategori</label>
                        <input type="text" name="tipe" x-model="formData.tipe" placeholder="Contoh: Pouch Flat Bottom" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-foreground mb-1">Stok (Pcs)</label>
                            <input type="number" name="stok_pcs" x-model="formData.stok" placeholder="Contoh: 100" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-foreground mb-1">HPP / Harga Beli (Rp)</label>
                            <input type="number" name="harga_satuan" x-model="formData.hpp" placeholder="Contoh: 5000" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl font-bold text-muted-foreground hover:bg-muted/50 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#526B42] text-white rounded-xl font-bold hover:bg-[#3f5232] shadow-md transition-all hover:scale-105" x-text="isEditMode ? 'Simpan Perubahan' : 'Simpan Data'"></button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Proses Pengemasan -->
    <div x-show="isPackModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display: none;" x-transition>
        <div @click.away="isPackModalOpen = false" class="bg-card w-full max-w-lg rounded-2xl shadow-xl border border-border/40 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-heading font-bold text-primary flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    Proses Pengemasan Otomatis
                </h2>
                <button @click="isPackModalOpen = false" class="text-muted-foreground hover:text-foreground transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form @submit.prevent="submitPackaging" class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-foreground mb-1">Pilih Roasted Bean</label>
                    <select x-model="packForm.roasted_bean_id" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                        <option value="">-- Pilih Kopi Matang --</option>
                        <template x-for="item in dataRoastedBean" :key="item.id">
                            <option :value="item.id" x-text="item.nama + ' (Stok: ' + item.stok_raw + 'g)'"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-foreground mb-1">Pilih Kemasan Target</label>
                    <select x-model="packForm.packaging_id" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                        <option value="">-- Pilih Jenis Kemasan --</option>
                        <template x-for="item in dataProduk" :key="item.id">
                            <option :value="item.id" x-text="item.nama"></option>
                        </template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-1">Stok Diambil (Gram)</label>
                        <input type="number" x-model="packForm.jumlah_stok_digunakan_gram" placeholder="Contoh: 2500" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-1">Ukuran Pouch (Gram)</label>
                        <input type="number" x-model="packForm.ukuran_kemasan_gram" placeholder="Contoh: 200" required class="w-full bg-background border border-border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                    </div>
                </div>
                
                <!-- Preview Kalkulasi -->
                <div class="mt-4 p-4 bg-muted/30 border border-border/50 rounded-xl flex justify-between items-center" x-show="packForm.jumlah_stok_digunakan_gram > 0 && packForm.ukuran_kemasan_gram > 0">
                    <div class="text-center">
                        <p class="text-[10px] font-bold text-muted-foreground uppercase">Hasil Kemasan</p>
                        <p class="text-xl font-black text-primary"><span x-text="Math.floor(packForm.jumlah_stok_digunakan_gram / packForm.ukuran_kemasan_gram)"></span> <span class="text-sm">Pcs</span></p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] font-bold text-muted-foreground uppercase">Sisa Residu</p>
                        <p class="text-xl font-black text-yellow-600"><span x-text="packForm.jumlah_stok_digunakan_gram % packForm.ukuran_kemasan_gram"></span> <span class="text-sm">Gram</span></p>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" @click="isPackModalOpen = false" class="px-5 py-2.5 rounded-xl font-bold text-muted-foreground hover:bg-muted/50 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-primary text-primary-foreground rounded-xl font-bold hover:bg-primary/90 shadow-md transition-all hover:scale-105" :disabled="isPackLoading">
                        <span x-show="!isPackLoading">Eksekusi Pengemasan</span>
                        <span x-show="isPackLoading" class="animate-pulse">Memproses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('gudangData', () => ({
            activeTab: 'greenbean',
            isModalOpen: false,
            isEditMode: false,
            editId: null,
            formData: {},
            
            isPackModalOpen: false,
            isPackLoading: false,
            packForm: { roasted_bean_id: '', packaging_id: '', jumlah_stok_digunakan_gram: '', ukuran_kemasan_gram: '' },
            
            // Backend Data
            dataGreenBean: @json($greenBeans),
            dataRoastedBean: @json($roastedBeans),
            dataProduk: @json($packagings),

            getTabName() {
                if(this.activeTab === 'greenbean') return 'Green Bean';
                if(this.activeTab === 'roastedbean') return 'Roasted Bean';
                return 'Produk';
            },

            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            },
            
            openModal(mode, tab, item = null) {
                this.activeTab = tab || this.activeTab;
                this.isEditMode = (mode === 'edit');
                if (this.isEditMode && item) {
                    this.editId = item.id;
                    this.formData = { ...item };
                } else {
                    this.editId = null;
                    this.formData = {};
                }
                this.isModalOpen = true;
            },

            openPackModal() {
                this.isPackModalOpen = true;
                this.packForm = { roasted_bean_id: '', packaging_id: '', jumlah_stok_digunakan_gram: '', ukuran_kemasan_gram: '' };
            },

            async submitPackaging() {
                this.isPackLoading = true;
                try {
                    const res = await fetch('/internal-api/pengemasan/process', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.packForm)
                    });
                    
                    const data = await res.json();
                    
                    if(res.ok) {
                        alert('Berhasil! ' + Math.floor(this.packForm.jumlah_stok_digunakan_gram / this.packForm.ukuran_kemasan_gram) + ' kemasan ditambahkan. Sisa residu tersimpan: ' + data.data.sisa_gram_dicatat + ' gram.');
                        window.location.reload();
                    } else {
                        alert('Gagal: ' + data.message);
                    }
                } catch(e) {
                    alert('Terjadi kesalahan jaringan.');
                } finally {
                    this.isPackLoading = false;
                }
            },

            getFormAction() {
                let base = '';
                if(this.activeTab === 'greenbean') base = '/inventory/green-bean';
                if(this.activeTab === 'roastedbean') base = '/inventory/roasted-bean';
                if(this.activeTab === 'produk') base = '/inventory/packaging';
                
                if(this.isEditMode && this.editId) return base + '/' + this.editId;
                return base;
            }
        }));
    });
</script>
@endpush
@endsection
