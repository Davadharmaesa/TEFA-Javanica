@extends('layouts.app')

@section('title', 'Master Data - TEFA Javanica')

@section('content')
<div x-data="masterData()" class="space-y-6 pb-12">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-card p-6 rounded-2xl shadow-sm border border-border/40 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-24 h-24 bg-primary/5 rounded-bl-full pointer-events-none group-hover:scale-110 transition-transform duration-500"></div>
        <div>
            <h1 class="text-2xl font-heading font-bold text-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>
                Pusat Master Data
            </h1>
            <p class="text-sm text-muted-foreground mt-1">Kelola data inti untuk kebutuhan produksi dan perhitungan keuangan.</p>
        </div>
        <div class="flex gap-2">
            <button @click="openModal()" class="bg-primary text-primary-foreground hover:bg-primary/90 px-5 py-2.5 rounded-xl text-sm font-bold shadow-md transition-all flex items-center gap-2 hover:scale-105 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span x-text="'Tambah ' + getTabName()"></span>
            </button>
        </div>
    </div>

    <div class="flex overflow-x-auto hide-scrollbar gap-2 p-1 bg-muted/30 rounded-xl border border-border/50 max-w-4xl">
        <button @click="activeTab = 'biaya'" 
            class="flex-1 min-w-[150px] py-3 px-4 rounded-lg font-bold text-sm transition-all duration-300 flex justify-center items-center gap-2"
            :class="activeTab === 'biaya' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Komponen Biaya
        </button>
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
        <button @click="activeTab = 'packaging'" 
            class="flex-1 min-w-[150px] py-3 px-4 rounded-lg font-bold text-sm transition-all duration-300 flex justify-center items-center gap-2"
            :class="activeTab === 'packaging' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            Kemasan
        </button>
    </div>

    <div x-show="activeTab === 'biaya'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4 pl-6">Nama Biaya</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4">Satuan</th>
                        <th class="p-4 text-right">Harga Standar</th>
                        <th class="p-4 text-center pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    <template x-for="item in dataBiaya" :key="item.id">
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4 pl-6">
                                <p class="font-bold text-primary group-hover:text-secondary transition-colors" x-text="item.nama"></p>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 bg-secondary/10 text-secondary text-[10px] font-black rounded-full uppercase tracking-widest shadow-sm border border-secondary/20" x-text="item.kategori"></span>
                            </td>
                            <td class="p-4 text-sm font-mono text-muted-foreground" x-text="item.satuan"></td>
                            <td class="p-4 text-right font-mono font-bold text-primary">
                                Rp <span x-text="formatRupiah(item.harga)"></span>
                            </td>
                            <td class="p-4 text-center pr-6">
                                <div class="flex justify-center gap-1">
                                    <button @click="editBiaya(item)" class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button @click="openDeleteModal(item)" class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 'greenbean'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4 pl-6">Nama Biji Mentah</th>
                        <th class="p-4">Asal</th>
                        <th class="p-4 text-right">Stok (Kg)</th>
                        <th class="p-4 text-center pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    <template x-for="item in dataGreenBean" :key="item.id">
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4 pl-6">
                                <p class="font-bold text-primary group-hover:text-secondary transition-colors" x-text="item.nama_biji"></p>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 bg-accent/10 text-accent text-[10px] font-black rounded-full uppercase tracking-widest shadow-sm border border-accent/20" x-text="item.asal_kopi"></span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold" :class="(item.stok_gram/1000) < 10 ? 'text-red-500' : 'text-primary'">
                                <span x-text="(item.stok_gram / 1000).toFixed(1)"></span> Kg
                            </td>
                            <td class="p-4 text-center pr-6">
                                <div class="flex justify-center gap-1">
                                    <button @click="editGreenBean(item)" class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button @click="openDeleteModal(item)" class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 'roastedbean'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4 pl-6">Nama Produk Jadi</th>
                        <th class="p-4">Bentuk</th>
                        <th class="p-4 text-right">Stok (Kg)</th>
                        <th class="p-4 text-center pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    <template x-for="item in dataRoastedBean" :key="item.id">
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4 pl-6">
                                <p class="font-bold text-primary group-hover:text-secondary transition-colors" x-text="item.nama_kopi"></p>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 bg-primary/10 text-primary text-[10px] font-black rounded-full uppercase tracking-widest shadow-sm border border-primary/20" x-text="item.bentuk_akhir"></span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-primary">
                                <span x-text="(item.total_stok / 1000).toFixed(1)"></span> Kg
                            </td>
                            <td class="p-4 text-center pr-6">
                                <div class="flex justify-center gap-1">
                                    <button @click="editRoastedBean(item)" class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button @click="openDeleteModal(item)" class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 'packaging'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4 pl-6">Tipe</th>
                        <th class="p-4">Nama Item</th>
                        <th class="p-4">Kapasitas (Gram)</th>
                        <th class="p-4">Satuan</th>
                        <th class="p-4 text-right">Harga Satuan (Rp)</th>
                        <th class="p-4 text-right">Stok</th>
                        <th class="p-4 text-center pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    <template x-for="item in dataPackaging" :key="item.id">
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4 pl-6">
                                <span class="px-2.5 py-1 bg-accent/10 text-accent text-[10px] font-black rounded-full uppercase tracking-widest shadow-sm border border-accent/20" x-text="item.tipe"></span>
                            </td>
                            <td class="p-4">
                                <p class="font-bold text-primary group-hover:text-secondary transition-colors" x-text="item.nama_kemasan"></p>
                            </td>
                            <td class="p-4">
                                <span class="text-sm font-mono text-muted-foreground" x-text="item.tipe === 'Stiker' ? '-' : item.kapasitas_gram + 'g'"></span>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 bg-muted-foreground/10 text-muted-foreground text-[10px] font-bold rounded-md uppercase tracking-wider border border-border" x-text="item.satuan || 'pcs'"></span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-primary">
                                Rp <span x-text="formatRupiah(item.harga_satuan)"></span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold" :class="item.stok_pcs < 50 ? 'text-red-500' : 'text-primary'">
                                <span x-text="item.stok_pcs"></span> <span x-text="item.satuan || 'pcs'" class="text-xs"></span>
                            </td>
                            <td class="p-4 text-center pr-6">
                                <div class="flex justify-center gap-1">
                                    <button @click="editPackaging(item)" class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button @click="openDeleteModal(item)" class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="isModalOpen = false" x-transition.opacity></div>
        <div class="relative bg-card w-full max-w-xl sm:max-w-2xl p-6 sm:p-8 rounded-2xl shadow-2xl border border-border/50 m-2 sm:m-4" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-8 scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
             x-transition:leave-end="opacity-0 translate-y-8 scale-95">
            
            <div class="flex justify-between items-center mb-6 border-b border-border/50 pb-4">
                <h2 class="text-xl font-heading font-black text-primary uppercase tracking-wide" x-text="modalTitle"></h2>
                <button @click="isModalOpen = false" class="text-muted-foreground hover:bg-muted p-1.5 rounded-xl transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div x-show="activeTab === 'biaya'" class="space-y-5">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Nama Biaya</label>
                    <input type="text" x-model="formBiaya.nama" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all" placeholder="Contoh: Gas Elpiji 3Kg" />
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Kategori</label>
                        <select x-model="formBiaya.kategori" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer">
                            <option value="Bahan Baku">Bahan Baku</option>
                            <option value="Overhead">Overhead</option>
                            <option value="Tenaga Kerja">Tenaga Kerja</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Satuan</label>
                        <input type="text" x-model="formBiaya.satuan" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all" placeholder="Contoh: Tabung / Shift" />
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Harga (Rp)</label>
                    <input type="number" min="0" x-model="formBiaya.harga" class="w-full bg-background border-2 border-border/80 rounded-xl px-4 py-3 text-xl font-mono font-black text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none placeholder:text-muted/50 placeholder:text-base placeholder:font-sans" placeholder="Ketik nominal..." />
                </div>
            </div>

            <div x-show="activeTab === 'greenbean'" class="space-y-5">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Nama Biji Mentah</label>
                    <input type="text" x-model="formGreenBean.nama_biji" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all" placeholder="Contoh: Arabika Ijen" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Asal Kopi</label>
                    <input type="text" x-model="formGreenBean.asal_kopi" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all" placeholder="Contoh: Banyuwangi" />
                </div>
                <p class="text-[10px] text-muted-foreground italic mt-2">*Stok dan harga beli dilacak dinamis per tanggal pembelian di menu Gudang.</p>
            </div>

            <div x-show="activeTab === 'roastedbean'" class="space-y-5">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Nama Produk Jadi</label>
                    <input type="text" x-model="formRoastedBean.nama_kopi" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all" placeholder="Contoh: Kopi Arabika Ijen" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Bentuk Akhir</label>
                    <input type="text" x-model="formRoastedBean.bentuk_akhir" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all" placeholder="Contoh: Biji Utuh / Bubuk Kasar" />
                </div>
                <p class="text-[10px] text-muted-foreground italic mt-2">*Stok produk jadi otomatis bertambah dari hasil Produksi Roasting.</p>
            </div>

            <div x-show="activeTab === 'packaging'" class="space-y-5">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Tipe Kemasan</label>
                        <select x-model="formPackaging.tipe" @change="if (formPackaging.tipe === 'Stiker') { formPackaging.kapasitas_gram = ''; } if (formPackaging.tipe !== 'Lainnya') { formPackaging.custom_tipe = ''; }" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer">
                            <template x-for="type in packagingTypeOptions" :key="type">
                                <option :value="type" x-text="type"></option>
                            </template>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Satuan Baru</label>
                        <select x-model="formPackaging.satuan" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer">
                            <option value="pcs">pcs</option>
                            <option value="gram">gram</option>
                            <option value="pack">pack</option>
                        </select>
                    </div>
                </div>
                
                <div x-show="formPackaging.tipe === 'Lainnya'" class="space-y-1.5">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Nama Tipe Kemasan Baru</label>
                    <input type="text" x-model="formPackaging.custom_tipe" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all" placeholder="Contoh: Kantong Kertas" />
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Nama Item</label>
                    <input type="text" x-model="formPackaging.nama_kemasan" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all" placeholder="Contoh: Pouch Hitam 200g" />
                </div>
                
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Kapasitas (Gram)</label>
                        <input type="number" min="0" x-model="formPackaging.kapasitas_gram" :disabled="formPackaging.tipe === 'Stiker'" :placeholder="formPackaging.tipe === 'Stiker' ? '-' : 'Contoh: 200'" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-mono font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all disabled:cursor-not-allowed disabled:bg-muted/50 disabled:text-muted-foreground [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Tambah Stok Awal</label>
                        <input type="number" min="0" x-model="formPackaging.stok_pcs" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-mono font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="Ketik jumlah..." />
                    </div>
                </div>

                <div class="bg-primary/5 border border-primary/20 rounded-xl p-5 space-y-4">
                    <p class="text-[10px] font-bold text-primary uppercase tracking-widest">Kalkulator Pembantu Harga Satuan</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase">Total Pembelian (Rp)</label>
                            <input type="number" min="0" x-model="formPackaging.harga_total_pembelian" class="w-full bg-background border border-border/60 rounded-xl px-4 py-2.5 text-sm font-mono font-medium text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="Total Bayar..." />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase">Jumlah Qty Beli</label>
                            <input type="number" min="1" x-model="formPackaging.jumlah_qty_pembelian" class="w-full bg-background border border-border/60 rounded-xl px-4 py-2.5 text-sm font-mono font-medium text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="Jml Barang..." />
                        </div>
                    </div>
                    <div class="space-y-1.5 pt-3 border-t border-primary/10">
                        <label class="text-[10px] font-bold text-primary uppercase tracking-widest">Harga Satuan Rill (Rp)</label>
                        <input type="number" min="0" x-model="formPackaging.harga_satuan" class="w-full bg-white border border-border/60 rounded-xl px-4 py-3 text-lg font-mono font-black text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none placeholder:text-muted/40 placeholder:text-sm placeholder:font-sans" placeholder="Bisa diketik manual..." />
                        <span class="text-[9px] font-bold text-muted-foreground">*Terisi otomatis jika kalkulator diisi. Tetap bisa diedit manual.</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button @click="isModalOpen = false" class="flex-1 bg-muted text-muted-foreground hover:bg-muted/80 py-3.5 rounded-xl font-black text-sm uppercase tracking-widest transition-colors">
                    Batal
                </button>
                <button @click="saveData()" class="flex-1 bg-primary text-primary-foreground hover:bg-primary/90 py-3.5 rounded-xl font-black text-sm uppercase tracking-widest transition-all shadow-[0_8px_20px_rgba(200,80,50,0.15)] hover:-translate-y-0.5 active:translate-y-0">
                    Simpan Data
                </button>
            </div>
        </div>
    </div>

    <div x-show="isDeleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="isDeleteModalOpen = false"></div>
        <div x-show="isDeleteModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-95" @click.stop class="relative bg-card w-full max-w-lg rounded-2xl border border-border/50 shadow-2xl p-6 z-10">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start justify-between mb-4">
                <div>
                    <h3 class="text-xl font-heading font-bold text-primary">Konfirmasi Hapus</h3>
                    <p class="text-sm text-muted-foreground mt-2">Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <button @click="isDeleteModalOpen = false" class="self-start p-2 rounded-xl text-muted-foreground hover:bg-muted transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="rounded-xl bg-muted/30 border border-border/60 p-4 mb-6">
                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Item yang akan dihapus:</p>
                <p class="text-base font-bold text-primary mt-1" x-text="itemToDelete ? getDeleteLabel(itemToDelete) : '-' "></p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button @click="isDeleteModalOpen = false" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-muted text-muted-foreground hover:bg-muted/80 font-bold transition-colors">Batal</button>
                <button @click="confirmDelete()" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-red-600 text-white hover:bg-red-700 font-bold transition-all shadow-md">Ya, Hapus Item</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('masterData', () => ({
            activeTab: 'biaya',
            isModalOpen: false,
            isDeleteModalOpen: false,
            itemToDelete: null,
            modalTitle: '',
            
            // Inisialisasi angka diubah menjadi string kosong ('') agar placeholder form langsung terlihat
            formBiaya: { id: null, nama: '', kategori: 'Overhead', satuan: '', harga: '' },
            formGreenBean: { id: null, nama_biji: '', asal_kopi: '' },
            formRoastedBean: { id: null, nama_kopi: '', bentuk_akhir: '' },
            formPackaging: { id: null, tipe: 'Pouch Alumunium', custom_tipe: '', nama_kemasan: '', kapasitas_gram: '', satuan: 'pcs', harga_satuan: '', harga_total_pembelian: '', jumlah_qty_pembelian: '', stok_pcs: '' },

            dataBiaya: [
                { id: 1, nama: 'Gas Elpiji 3Kg', kategori: 'Overhead', satuan: 'Tabung', harga: 20000 },
                { id: 2, nama: 'Listrik Mesin', kategori: 'Overhead', satuan: 'Kwh', harga: 1500 },
                { id: 3, nama: 'Upah Roaster', kategori: 'Tenaga Kerja', satuan: 'Batch', harga: 15000 }
            ],
            dataGreenBean: [],
            dataRoastedBean: [],
            dataPackaging: [],

            init() {
                this.fetchData();
                this.$watch('formPackaging.harga_total_pembelian', () => this.calculateHargaSatuan());
                this.$watch('formPackaging.jumlah_qty_pembelian', () => this.calculateHargaSatuan());
            },

            calculateHargaSatuan() {
                const total = Number(this.formPackaging.harga_total_pembelian) || 0;
                const qty = Number(this.formPackaging.jumlah_qty_pembelian) || 0;
                if (qty > 0) {
                    this.formPackaging.harga_satuan = Math.round(total / qty);
                }
            },

            async fetchData() {
                try {
                    const [gbRes, rbRes, biayaRes, pkgRes] = await Promise.all([
                        fetch('/internal-api/master/green-beans'),
                        fetch('/internal-api/master/roasted-beans'),
                        fetch('/internal-api/master/biaya'),
                        fetch('/internal-api/master/packagings')
                    ]);
                    if (gbRes.ok) this.dataGreenBean = await gbRes.json();
                    if (rbRes.ok) this.dataRoastedBean = await rbRes.json();
                    if (biayaRes.ok) this.dataBiaya = await biayaRes.json();
                    if (pkgRes.ok) this.dataPackaging = await pkgRes.json();
                } catch (e) {
                    console.error('Failed to fetch data:', e);
                }
            },

            getTabName() {
                if(this.activeTab === 'biaya') return 'Biaya';
                if(this.activeTab === 'greenbean') return 'Green Bean';
                if(this.activeTab === 'packaging') return 'Kemasan';
                return 'Roasted Bean';
            },

            get packagingTypeOptions() {
                const baseTypes = ['Pouch Alumunium', 'Stiker'];
                const customTypes = [...new Set(this.dataPackaging
                    .map(item => item.tipe)
                    .filter(type => type && !baseTypes.includes(type) && type !== 'Lainnya'))];

                return [...baseTypes, ...customTypes, 'Lainnya'];
            },

            openModal() {
                this.modalTitle = 'Tambah Master ' + this.getTabName();
                if(this.activeTab === 'biaya') this.formBiaya = { id: null, nama: '', kategori: 'Overhead', satuan: '', harga: '' };
                if(this.activeTab === 'greenbean') this.formGreenBean = { id: null, nama_biji: '', asal_kopi: '' };
                if(this.activeTab === 'roastedbean') this.formRoastedBean = { id: null, nama_kopi: '', bentuk_akhir: '' };
                if(this.activeTab === 'packaging') this.formPackaging = { id: null, tipe: 'Pouch Alumunium', custom_tipe: '', nama_kemasan: '', kapasitas_gram: '', satuan: 'pcs', harga_satuan: '', harga_total_pembelian: '', jumlah_qty_pembelian: '', stok_pcs: '' };
                this.isModalOpen = true;
            },

            editBiaya(item) {
                this.modalTitle = 'Edit Komponen Biaya';
                this.formBiaya = { ...item };
                this.isModalOpen = true;
            },

            editGreenBean(item) {
                this.modalTitle = 'Edit Data Kopi Mentah';
                this.formGreenBean = { ...item };
                this.isModalOpen = true;
            },

            editRoastedBean(item) {
                this.modalTitle = 'Edit Produk Jadi';
                this.formRoastedBean = { ...item };
                this.isModalOpen = true;
            },

            editPackaging(item) {
                this.modalTitle = 'Edit Master Kemasan';
                const isCustomType = item.tipe && !['Pouch Alumunium', 'Stiker'].includes(item.tipe);

                this.formPackaging = {
                    id: item.id,
                    tipe: isCustomType ? 'Lainnya' : item.tipe,
                    custom_tipe: isCustomType ? item.tipe : '',
                    nama_kemasan: item.nama_kemasan,
                    kapasitas_gram: item.tipe === 'Stiker' ? '' : item.kapasitas_gram,
                    satuan: item.satuan || 'pcs',
                    harga_satuan: item.harga_satuan,
                    harga_total_pembelian: item.harga_total_pembelian ?? '',
                    jumlah_qty_pembelian: item.jumlah_qty_pembelian ?? '',
                    stok_pcs: item.stok_pcs ?? '',
                };
                this.isModalOpen = true;
            },

            getDeleteLabel(item) {
                if(this.activeTab === 'biaya') return `${item.nama} (${item.kategori})`;
                if(this.activeTab === 'greenbean') return `${item.nama_biji} - ${item.asal_kopi}`;
                if(this.activeTab === 'roastedbean') return `${item.nama_kopi} - ${item.bentuk_akhir}`;
                return `${item.nama_kemasan} (${item.tipe})`;
            },

            openDeleteModal(item) {
                this.itemToDelete = item;
                this.isDeleteModalOpen = true;
            },

            async confirmDelete() {
                if(!this.itemToDelete) return;

                let url = '';
                if(this.activeTab === 'biaya') url = `/internal-api/master/biaya/${this.itemToDelete.id}`;
                if(this.activeTab === 'greenbean') url = `/internal-api/master/green-beans/${this.itemToDelete.id}`;
                if(this.activeTab === 'roastedbean') url = `/internal-api/master/roasted-beans/${this.itemToDelete.id}`;
                if(this.activeTab === 'packaging') url = `/internal-api/master/packagings/${this.itemToDelete.id}`;

                try {
                    const res = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if(res.ok) {
                        this.fetchData();
                        this.isDeleteModalOpen = false;
                        this.itemToDelete = null;
                    } else {
                        alert(data.message || 'Gagal menghapus data.');
                    }
                } catch(e) {
                    alert('Terjadi kesalahan jaringan atau sistem.');
                }
            },

            async saveData() {
                if(this.activeTab === 'greenbean') {
                    const method = this.formGreenBean.id ? 'PUT' : 'POST';
                    const url = this.formGreenBean.id ? `/internal-api/master/green-beans/${this.formGreenBean.id}` : `/internal-api/master/green-beans`;
                    const res = await fetch(url, {
                        method: method,
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify(this.formGreenBean)
                    });
                    if(res.ok) this.fetchData();
                } else if(this.activeTab === 'roastedbean') {
                    const method = this.formRoastedBean.id ? 'PUT' : 'POST';
                    const url = this.formRoastedBean.id ? `/internal-api/master/roasted-beans/${this.formRoastedBean.id}` : `/internal-api/master/roasted-beans`;
                    const res = await fetch(url, {
                        method: method,
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify(this.formRoastedBean)
                    });
                    if(res.ok) this.fetchData();
                } else if(this.activeTab === 'biaya') {
                    const method = this.formBiaya.id ? 'PUT' : 'POST';
                    const url = this.formBiaya.id ? `/internal-api/master/biaya/${this.formBiaya.id}` : `/internal-api/master/biaya`;
                    const res = await fetch(url, {
                        method: method,
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify(this.formBiaya)
                    });
                    if(res.ok) this.fetchData();
                } else if(this.activeTab === 'packaging') {
                    const method = this.formPackaging.id ? 'PUT' : 'POST';
                    const url = this.formPackaging.id ? `/internal-api/master/packagings/${this.formPackaging.id}` : `/internal-api/master/packagings`;
                    const customTipe = this.formPackaging.tipe === 'Lainnya' ? this.formPackaging.custom_tipe.trim() : this.formPackaging.tipe;

                    if (this.formPackaging.tipe === 'Lainnya' && !customTipe) {
                        alert('Nama tipe kemasan wajib diisi ketika memilih Lainnya.');
                        return;
                    }

                    const payload = {
                        ...this.formPackaging,
                        tipe: customTipe,
                        kapasitas_gram: this.formPackaging.tipe === 'Stiker' ? 0 : Number(this.formPackaging.kapasitas_gram || 0),
                        stok_pcs: Number(this.formPackaging.stok_pcs || 0),
                    };
                    delete payload.custom_tipe;

                    const res = await fetch(url, {
                        method: method,
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify(payload)
                    });

                    if(res.ok) {
                        await this.fetchData();
                        this.isModalOpen = false;
                    } else {
                        const data = await res.json().catch(() => ({}));
                        alert(data.message || 'Gagal menyimpan data kemasan.');
                    }

                    return;
                }
                this.isModalOpen = false;
            },

            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }
        }));
    });
</script>
@endpush
@endsection