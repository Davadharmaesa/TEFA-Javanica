@extends('layouts.app')

@section('title', 'Master Data - TEFA Javanica')

@section('content')
<div x-data="masterData()" class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-card p-6 rounded-2xl shadow-sm border border-border/40">
        <div>
            <h1 class="text-2xl font-heading font-bold text-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>
                Pusat Master Data
            </h1>
            <p class="text-sm text-muted-foreground mt-1">Kelola data inti untuk kebutuhan produksi dan perhitungan keuangan.</p>
        </div>
        <div class="flex gap-2">
            <!-- Add Button Dynamic based on Tab -->
            <button @click="openModal()" class="bg-secondary text-secondary-foreground hover:bg-secondary/90 px-4 py-2.5 rounded-xl text-sm font-bold shadow-md transition-all flex items-center gap-2 hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span x-text="'Tambah ' + getTabName()"></span>
            </button>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex overflow-x-auto hide-scrollbar gap-2 p-1 bg-muted/30 rounded-xl border border-border/50">
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
    </div>

    <!-- Tab Content: Komponen Biaya -->
    <div x-show="activeTab === 'biaya'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4">Nama Biaya</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4">Satuan</th>
                        <th class="p-4 text-right">Harga Standar</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    <template x-for="item in dataBiaya" :key="item.id">
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4">
                                <p class="font-bold text-primary" x-text="item.nama"></p>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 bg-secondary/10 text-secondary text-[10px] font-black rounded-full uppercase tracking-widest" x-text="item.kategori"></span>
                            </td>
                            <td class="p-4 text-sm font-mono text-muted-foreground" x-text="item.satuan"></td>
                            <td class="p-4 text-right font-mono font-bold text-primary">
                                Rp <span x-text="formatRupiah(item.harga)"></span>
                            </td>
                            <td class="p-4 text-center">
                                <button @click="editBiaya(item)" class="p-1.5 text-muted-foreground hover:text-secondary bg-transparent hover:bg-secondary/10 rounded-md transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab Content: Green Bean -->
    <div x-show="activeTab === 'greenbean'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4">Nama Biji Mentah</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4 text-right">Stok (Kg)</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    <template x-for="item in dataGreenBean" :key="item.id">
                        <tr class="hover:bg-muted/10 transition-colors group">
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
                                <button class="p-1.5 text-muted-foreground hover:text-secondary bg-transparent hover:bg-secondary/10 rounded-md transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </button>
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
                        <th class="p-4">Nama Produk Jadi</th>
                        <th class="p-4">Bentuk</th>
                        <th class="p-4 text-right">Stok (Kg)</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    <template x-for="item in dataRoastedBean" :key="item.id">
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4">
                                <p class="font-bold text-primary" x-text="item.nama"></p>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 bg-primary/10 text-primary text-[10px] font-black rounded-full uppercase tracking-widest" x-text="item.bentuk"></span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-primary">
                                <span x-text="item.stok"></span> Kg
                            </td>
                            <td class="p-4 text-center">
                                <button class="p-1.5 text-muted-foreground hover:text-secondary bg-transparent hover:bg-secondary/10 rounded-md transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Form Tambah/Edit -->
    <div x-show="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="isModalOpen = false" x-transition.opacity></div>
        <div class="relative bg-card w-full max-w-md p-6 rounded-2xl shadow-2xl border border-border/50 m-4" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-8 scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
             x-transition:leave-end="opacity-0 translate-y-8 scale-95">
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-heading font-bold text-primary" x-text="modalTitle"></h2>
                <button @click="isModalOpen = false" class="text-muted-foreground hover:text-red-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <!-- Form Biaya -->
            <div x-show="activeTab === 'biaya'" class="space-y-4">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-primary uppercase tracking-widest">Nama Biaya</label>
                    <input type="text" x-model="formBiaya.nama" class="w-full bg-background border border-border/60 rounded-xl px-4 py-2.5 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition-all" placeholder="Contoh: Gas Elpiji 3Kg" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-primary uppercase tracking-widest">Kategori</label>
                        <select x-model="formBiaya.kategori" class="w-full bg-background border border-border/60 rounded-xl px-4 py-2.5 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition-all">
                            <option value="Bahan Baku">Bahan Baku</option>
                            <option value="Overhead">Overhead</option>
                            <option value="Tenaga Kerja">Tenaga Kerja</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-primary uppercase tracking-widest">Satuan</label>
                        <input type="text" x-model="formBiaya.satuan" class="w-full bg-background border border-border/60 rounded-xl px-4 py-2.5 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition-all" placeholder="Tabung / Shift" />
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-primary uppercase tracking-widest">Harga (Rp)</label>
                    <input type="number" x-model="formBiaya.harga" class="w-full bg-background border border-border/60 rounded-xl px-4 py-2.5 text-xl font-mono font-black text-primary focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition-all" placeholder="0" />
                </div>
            </div>

            <!-- Form Lain (Placeholder) -->
            <div x-show="activeTab !== 'biaya'" class="py-8 text-center text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4 opacity-50"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/></svg>
                <p class="text-sm">Form input untuk <span class="font-bold uppercase" x-text="activeTab"></span> belum tersedia di mockup ini.</p>
            </div>

            <div class="mt-8 flex gap-3">
                <button @click="isModalOpen = false" class="flex-1 bg-muted text-muted-foreground hover:bg-muted/80 py-3 rounded-xl font-bold text-sm transition-colors">
                    Batal
                </button>
                <button @click="saveData()" class="flex-1 bg-primary text-primary-foreground hover:bg-primary/90 py-3 rounded-xl font-bold text-sm transition-colors shadow-md">
                    Simpan Data
                </button>
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
            modalTitle: '',
            
            // Form State
            formBiaya: { id: null, nama: '', kategori: 'Overhead', satuan: '', harga: '' },

            // Mock Data
            dataBiaya: [
                { id: 1, nama: 'Gas Elpiji 3Kg', kategori: 'Overhead', satuan: 'Tabung', harga: 20000 },
                { id: 2, nama: 'Listrik Mesin', kategori: 'Overhead', satuan: 'Kwh', harga: 1500 },
                { id: 3, nama: 'Upah Roaster', kategori: 'Tenaga Kerja', satuan: 'Batch', harga: 15000 },
                { id: 4, nama: 'Penyusutan Mesin', kategori: 'Overhead', satuan: 'Bulan', harga: 500000 },
            ],
            dataGreenBean: [
                { id: 1, nama: 'Arabika Ijen Fullwash', kategori: 'Arabika', stok: 45.5 },
                { id: 2, nama: 'Robusta Dampit', kategori: 'Robusta', stok: 120.0 },
                { id: 3, nama: 'Arabika Gayo Wine', kategori: 'Arabika', stok: 8.0 },
            ],
            dataRoastedBean: [
                { id: 1, nama: 'Medium Arabika Ijen', bentuk: 'Biji Utuh', stok: 12.5 },
                { id: 2, nama: 'Robusta Dark Roast', bentuk: 'Biji Utuh', stok: 34.0 },
                { id: 3, nama: 'House Blend Espresso', bentuk: 'Bubuk Halus', stok: 5.2 },
            ],

            getTabName() {
                if(this.activeTab === 'biaya') return 'Biaya';
                if(this.activeTab === 'greenbean') return 'Green Bean';
                return 'Roasted Bean';
            },

            openModal() {
                this.modalTitle = 'Tambah Master ' + this.getTabName();
                if(this.activeTab === 'biaya') {
                    this.formBiaya = { id: null, nama: '', kategori: 'Overhead', satuan: '', harga: '' };
                }
                this.isModalOpen = true;
            },

            editBiaya(item) {
                this.modalTitle = 'Edit Komponen Biaya';
                this.formBiaya = { ...item };
                this.isModalOpen = true;
            },

            saveData() {
                if(this.activeTab === 'biaya') {
                    if(!this.formBiaya.nama || !this.formBiaya.harga) {
                        alert('Nama dan Harga wajib diisi!');
                        return;
                    }
                    
                    if(this.formBiaya.id) {
                        // Edit
                        let index = this.dataBiaya.findIndex(x => x.id === this.formBiaya.id);
                        if(index !== -1) this.dataBiaya[index] = { ...this.formBiaya };
                    } else {
                        // Add
                        this.dataBiaya.push({
                            id: Date.now(),
                            ...this.formBiaya
                        });
                    }
                } else {
                    alert('Simulasi simpan hanya tersedia untuk Tab Komponen Biaya di mockup ini.');
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
