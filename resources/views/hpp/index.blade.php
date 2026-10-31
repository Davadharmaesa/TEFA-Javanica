@extends('layouts.app')

@section('title', 'Kalkulator HPP - TEFA Javanica')

@section('content')
<div class="space-y-6 pb-12" x-data="hppCalculator()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-border pb-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="flex h-3 w-3 relative">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-secondary"></span>
                </span>
                <span class="text-xs font-mono font-bold text-secondary uppercase tracking-widest">Sistem Online</span>
            </div>
            <h1 class="text-3xl font-heading font-black text-primary tracking-tight">Kalkulator HPP Otomatis</h1>
            <p class="text-muted-foreground font-sans text-sm mt-1">
                Hitung Harga Pokok Produksi secara dinamis berdasarkan durasi mesin dan output kemasan.
            </p>
        </div>
        <div class="text-right">
            <p class="text-2xl font-mono font-black text-primary" x-text="currentTime"></p>
            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest" x-text="currentDate"></p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 text-green-600 rounded-xl font-bold">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-600 rounded-xl font-bold">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-600 rounded-xl font-bold">
            <p class="font-black mb-1">Gagal menyimpan Laporan HPP:</p>
            <ul class="list-disc list-inside text-sm font-normal space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form Hitung (Kiri) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-card border border-border shadow-sm rounded-2xl overflow-hidden flex flex-col">
                <div class="bg-primary px-5 py-4">
                    <h2 class="font-heading font-bold text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="16" y1="14" x2="16" y2="18"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/><path d="M12 14h.01"/><path d="M8 14h.01"/><path d="M12 18h.01"/><path d="M8 18h.01"/>
                        </svg>
                    Parameter Produksi
                    </h2>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-muted-foreground mb-1">Pilih Batch Roasting</label>
                        <select 
                        x-model="form.selectedBatch" 
                        @change="fetchPreview()" 
                        class="w-full bg-background border border-border rounded-xl px-4 py-3 font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                            <option value=""> Pilih Batch </option>
                            <template x-for="batch in batches" :key="batch.id">
                                <option :value="JSON.stringify(batch)" x-text="batch.kode_batch + ' | ' + batch.green_bean_nama + ' | Berat: ' + batch.berat_matang_bersih + ' kg'"></option>
                            </template>
                        </select>
                        <p class="text-xs text-muted-foreground mt-1" x-show="form.selectedBatch">
                            Biaya Bahan Baku: <span class="font-bold text-foreground" x-text="formatRupiah(JSON.parse(form.selectedBatch).harga_bahan_baku)"></span>
                        </p>
                    </div>

                    <div x-show="form.selectedBatch" class="p-4 bg-muted/40 border border-border/60 rounded-xl space-y-2">
                        <p class="text-xs font-bold text-primary uppercase tracking-wider">Durasi Kerja Mesin</p>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="bg-background p-2 rounded-lg border border-border/50">
                                <p class="text-[9px] font-bold text-muted-foreground uppercase">Pre-heat</p>
                                <p class="text-xs font-black text-primary font-mono" x-text="JSON.parse(form.selectedBatch).durasi_pre_heating + 'm'"></p>
                            </div>
                            <div class="bg-background p-2 rounded-lg border border-border/50">
                                <p class="text-[9px] font-bold text-muted-foreground uppercase">First Crack</p>
                                <p class="text-xs font-black text-primary font-mono" x-text="JSON.parse(form.selectedBatch).waktu_first_crack + 'm'"></p>
                            </div>
                            <div class="bg-background p-2 rounded-lg border border-border/50">
                                <p class="text-[9px] font-bold text-muted-foreground uppercase">Dev Tahan</p>
                                <p class="text-xs font-black text-primary font-mono" x-text="JSON.parse(form.selectedBatch).waktu_development + 's'"></p>
                            </div>
                        </div>
                        <p class="text-[10px] text-muted-foreground mt-1">Tarif Mesin: Rp{{ number_format($biayaListrik->harga, 0, ',', '.') }} / Menit</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-muted-foreground mb-1">Pilih Kemasan</label>
                        <select 
                        x-model="form.selectedPouch" 
                        @change="onPouchChange($event)" 
                        class="w-full bg-background border border-border rounded-xl px-4 py-3 font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                            <option value=""> Pilih Pouch </option>
                            @foreach($packagings->filter(fn($p) => $p->kapasitas_gram > 0) as $pack)
                                <option value="{{ $pack->id }}" data-hpp="{{ $pack->harga_satuan }}" data-capacity="{{ $pack->kapasitas_gram }}" data-stock="{{ $pack->stok_pcs }}">{{ $pack->nama_kemasan }} (Rp{{ number_format($pack->harga_satuan, 0, ',', '.') }}/pcs)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-muted-foreground mb-1">Pilih Label / Stiker</label>
                        <select 
                        x-model="form.selectedSticker" 
                        @change="onStickerChange($event)" 
                        class="w-full bg-background border border-border rounded-xl px-4 py-3 font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                            <option value=""> Pilih Stiker </option>
                            @foreach($packagings->filter(fn($p) => $p->kapasitas_gram == 0) as $pack)
                                <option value="{{ $pack->id }}" data-hpp="{{ $pack->harga_satuan }}" data-stock="{{ $pack->stok_pcs }}">{{ $pack->nama_kemasan }} (Rp{{ number_format($pack->harga_satuan, 0, ',', '.') }}/pcs)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-muted-foreground mb-1">Total Output (Pcs)</label>
                        <input type="number"
                               readonly
                               x-model="form.hasil_kemasan_pcs" 
                               class="w-full bg-muted border border-border rounded-xl px-4 py-3 text-primary font-mono focus:outline-none cursor-not-allowed font-bold" 
                               placeholder="Auto">
                        <p class="text-[10px] text-muted-foreground mt-1" x-show="form.selectedBatch && form.selectedPouch && getMaxPcs() > 0">
                            Maksimum output dari kapasitas batch kopi: <span class="font-bold text-secondary" x-text="getMaxPcs() + ' Pcs'"></span>
                        </p>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-bold text-muted-foreground">Harga Jual Final per Pcs (Rp) <span class="text-red-500">*</span></label>
                            <button type="button" 
                                    x-show="preview.hpp_per_pcs > 0"
                                    @click="form.harga_jual_final = Math.ceil((preview.hpp_per_pcs * 1.5) / 500) * 500" 
                                    class="text-xs text-primary underline font-bold hover:text-primary/80 transition-colors">
                                Gunakan Saran (+50%)
                            </button>
                        </div>
                        <input type="number"
                               min="1"
                               x-model="form.harga_jual_final" 
                               class="w-full bg-background border border-border rounded-xl px-4 py-3 text-sm text-primary font-mono focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all" 
                               placeholder="Contoh: 20000">
                        <p class="text-[10px] text-muted-foreground mt-1">
                            Wajib diisi. Harga satuan per pcs ini yang akan dikunci dan digunakan kasir pada menu Penjualan.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Preview Kalkulasi (Kanan) -->
        <div class="lg:col-span-7">
            <div class="bg-card border border-border shadow-md rounded-2xl overflow-hidden h-full flex flex-col relative group">
                <div class="absolute right-0 top-0 w-32 h-32 bg-primary/5 rounded-bl-full pointer-events-none group-hover:scale-110 transition-transform duration-500"></div>
                <div class="p-6 flex-1 flex flex-col justify-between z-10">
                    <div>
                        <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-secondary animate-pulse" x-show="loading"></span>
                            <span class="w-2 h-2 rounded-full bg-primary" x-show="!loading"></span>
                            Rincian HPP 
                        </h3>
                        
                        <div class="space-y-4">
                            <!-- Bahan Baku -->
                            <div class="flex justify-between items-center border-b border-border/50 pb-2">
                                <div>
                                    <p class="text-sm font-bold text-foreground">Bahan Baku (Green Bean)</p>
                                    <p class="text-[10px] text-muted-foreground" x-text="'Alokasi ' + (preview.share_percentage || 0) + '% dari total bahan baku roasting'"></p>
                                </div>
                                <p class="font-mono font-bold text-primary" x-text="formatRupiah(preview.biaya_bahan_baku)"></p>
                            </div>
                            
                            <!-- Listrik & Operasional Mesin -->
                            <div class="flex justify-between items-center border-b border-border/50 pb-2">
                                <div>
                                    <p class="text-sm font-bold text-foreground">Biaya Operasional Mesin</p>
                                    <p class="text-[10px] text-muted-foreground" x-text="'Alokasi ' + (preview.share_percentage || 0) + '% dari (Durasi × Tarif per Menit) + Pre-heating Rp1.000'"></p>
                                </div>
                                <p class="font-mono font-bold text-primary" x-text="formatRupiah(preview.biaya_listrik)"></p>
                            </div>

                            <!-- Penyusutan -->
                            <div class="flex justify-between items-center border-b border-border/50 pb-2">
                                <div>
                                    <p class="text-sm font-bold text-foreground">Overhead Lainnya & Penyusutan</p>
                                    <p class="text-[10px] text-muted-foreground" x-text="'Alokasi ' + (preview.share_percentage || 0) + '% dari Biaya Overhead (' + formatRupiah(preview.biaya_overhead_custom_batch || 0) + '/batch)'"></p>
                                    <template x-if="preview.overhead_list && preview.overhead_list.length > 0">
                                        <p class="text-[9px] text-secondary font-bold mt-0.5">Komponen: <span x-text="preview.overhead_list.join(', ')"></span></p>
                                    </template>
                                </div>
                                <p class="font-mono font-bold text-primary" x-text="formatRupiah(preview.biaya_penyusutan)"></p>
                            </div>

                            <!-- Gaji -->
                            <div class="flex justify-between items-center border-b border-border/50 pb-2">
                                <div>
                                    <p class="text-sm font-bold text-foreground">Tenaga Kerja (Gaji & Roasting)</p>
                                    <p class="text-[10px] text-muted-foreground" x-text="'Alokasi ' + (preview.share_percentage || 0) + '% dari ' + (preview.is_fallback_gaji ? 'Upah Standar Master (' + formatRupiah(preview.biaya_tenaga_kerja_batch) + ')' : 'Upah Log Kerja (' + formatRupiah(preview.biaya_tenaga_kerja_batch) + ')')"></p>
                                    <template x-if="preview.pekerja_list && preview.pekerja_list.length > 0">
                                        <p class="text-[9px] text-secondary font-bold mt-0.5">Pekerja Terlibat: <span x-text="preview.pekerja_list.join(', ')"></span></p>
                                    </template>
                                </div>
                                <p class="font-mono font-bold text-primary" x-text="formatRupiah(preview.biaya_gaji)"></p>
                            </div>

                            <!-- Kemasan -->
                            <div class="flex justify-between items-center border-b border-border/50 pb-2">
                                <div>
                                    <p class="text-sm font-bold text-foreground">Modal Kemasan (Pouch + Stiker)</p>
                                    <p class="text-[10px] text-muted-foreground" x-text="'Pcs × (Pouch: ' + formatRupiah(form.pouch_hpp) + ' + Stiker: ' + formatRupiah(form.sticker_hpp) + ')'"></p>
                                </div>
                                <p class="font-mono font-bold text-primary" x-text="formatRupiah(preview.biaya_kemasan)"></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-dashed border-border flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
                        <div>
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">HPP Per Pcs Target</p>
                            <p class="text-3xl font-black font-mono text-secondary"><span x-text="formatRupiah(preview.hpp_per_pcs)"></span></p>
                        </div>
                        <div x-show="preview.hpp_per_pcs > 0" class="text-left">
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">Saran Harga Jual (Margin 50%)</p>
                            <p class="text-3xl font-black font-mono text-primary"><span x-text="formatRupiah(Math.ceil((preview.hpp_per_pcs * 1.5) / 500) * 500)"></span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Total HPP Keseluruhan</p>
                            <p class="text-lg font-black font-mono text-primary"><span x-text="formatRupiah(preview.total_hpp)"></span></p>
                        </div>
                    </div>
                </div>

                <!-- Submit Action -->
                <form action="{{ route('hpp.store') }}" method="POST" class="bg-muted/30 p-4 border-t border-border flex justify-end">
                    @csrf
                    <!-- Hidden inputs derived from preview -->
                    <input type="hidden" name="roasting_batch_id" :value="form.selectedBatch ? JSON.parse(form.selectedBatch).id : ''">
                    <input type="hidden" name="hasil_kemasan_pcs" :value="form.hasil_kemasan_pcs">
                    <input type="hidden" name="biaya_bahan_baku" :value="preview.biaya_bahan_baku">
                    <input type="hidden" name="biaya_listrik" :value="preview.biaya_listrik">
                    <input type="hidden" name="biaya_penyusutan" :value="preview.biaya_penyusutan">
                    <input type="hidden" name="biaya_gaji" :value="preview.biaya_gaji">
                    <input type="hidden" name="biaya_kemasan" :value="preview.biaya_kemasan">
                    <input type="hidden" name="total_hpp" :value="preview.total_hpp">
                    <input type="hidden" name="harga_jual_final" :value="form.harga_jual_final">
                    <input type="hidden" name="packaging_id" :value="form.selectedPouch">
                    
                    <button type="submit" 
                        class="px-6 py-3 bg-secondary text-white font-bold rounded-xl shadow-md hover:bg-secondary/90 transition-all hover:scale-105 disabled:opacity-50 disabled:hover:scale-100 disabled:cursor-not-allowed flex items-center gap-2"
                        :disabled="!canSubmit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan Laporan HPP
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- History Table -->
    <div class="mt-8 bg-card border border-border shadow-sm rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-border/50 flex justify-between items-center bg-muted/20">
            <h2 class="font-heading font-bold text-lg text-primary">Riwayat Kalkulasi HPP</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4">Kode Batch</th>
                        <th class="p-4">Tanggal Roast</th>
                        <th class="p-4">Hasil (Pcs)</th>
                        <th class="p-4 text-right">Total HPP</th>
                        <th class="p-4 text-right">HPP / Pcs</th>
                        <th class="p-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    <template x-for="batch in paginatedHppBatches" :key="batch.id">
                        <tr class="hover:bg-muted/10 transition-colors">
                            <td class="p-4 text-sm font-mono font-bold text-foreground" x-text="batch.kode_batch"></td>
                            <td class="p-4 text-xs font-mono text-muted-foreground" x-text="new Date(batch.hpp.created_at).toLocaleDateString('id-ID')"></td>
                            <td class="p-4 text-sm font-bold text-primary" x-text="batch.hpp.hasil_kemasan_pcs + ' Pcs'"></td>
                            <td class="p-4 text-right font-mono font-bold text-muted-foreground" x-text="formatRupiah(batch.hpp.total_hpp)"></td>
                            <td class="p-4 text-right font-mono font-bold text-secondary" x-text="formatRupiah(batch.hpp.total_hpp / batch.hpp.hasil_kemasan_pcs)"></td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-1 bg-green-500/10 text-green-600 text-[10px] font-black rounded-full uppercase tracking-widest border border-green-500/20">Tersimpan</span>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredHppBatches.length === 0">
                        <td colspan="6" class="p-8 text-center text-muted-foreground font-medium text-sm">Belum ada riwayat perhitungan HPP yang tersimpan.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="px-6 py-4 border-t border-border/30 bg-muted/10 flex flex-col sm:flex-row justify-between items-center gap-4">
            <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider">
                Menampilkan <span x-text="filteredHppBatches.length > 0 ? (hppPage - 1) * hppLimit + 1 : 0"></span> - <span x-text="Math.min(hppPage * hppLimit, filteredHppBatches.length)"></span> dari <span x-text="filteredHppBatches.length"></span> data
            </span>
            <div class="flex items-center gap-1.5">
                <button 
                    type="button" 
                    @click="hppPage = Math.max(1, hppPage - 1)" 
                    :disabled="hppPage === 1"
                    class="px-3 py-1.5 text-xs font-bold text-primary hover:text-white hover:bg-primary bg-card disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-card disabled:hover:text-primary rounded-lg border border-border/60 transition-all shadow-sm select-none"
                >Sebelumnya</button>

                <span class="text-xs font-mono font-black text-primary px-3">Halaman <span x-text="hppPage"></span> dari <span x-text="totalHppPages"></span></span>

                <button 
                    type="button" 
                    @click="hppPage = Math.min(totalHppPages, hppPage + 1)" 
                    :disabled="hppPage === totalHppPages"
                    class="px-3 py-1.5 text-xs font-bold text-primary hover:text-white hover:bg-primary bg-card disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-card disabled:hover:text-primary rounded-lg border border-border/60 transition-all shadow-sm select-none"
                >Selanjutnya</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('hppCalculator', () => ({
            // Ubah jadi hppHistory agar jelas menampung data riwayat
            hppHistory: @json($hppHistory),
            batches: @json($batches ?? []), // Data batch untuk form input di atas
            loading: false,

            // Pagination States
            hppPage: 1,
            hppLimit: 10,
            
            get filteredHppBatches() {
                // Mapping data riwayat hpp agar strukturnya sesuai dengan yang dipanggil tabel HTML
                return this.hppHistory.map(item => {
                    return {
                        id: item.id,
                        kode_batch: item.kode_batch || (item.roasting_batch ? item.roasting_batch.kode_batch : 'BATCH-' + item.roasting_batch_id),
                        hpp: {
                            created_at: item.created_at,
                            hasil_kemasan_pcs: item.hasil_kemasan_pcs,
                            total_hpp: item.total_hpp
                        }
                    };
                });
            },
            get totalHppPages() {
                return Math.ceil(this.filteredHppBatches.length / this.hppLimit) || 1;
            },
            get paginatedHppBatches() {
                this.hppPage = Math.min(Math.max(1, this.hppPage), this.totalHppPages);
                const start = (this.hppPage - 1) * this.hppLimit;
                return this.filteredHppBatches.slice(start, start + this.hppLimit);
            },
            
            // ... biarkan sisa fungsi form, preview, onPouchChange, dll tetap sama ...
            form: {
                selectedBatch: '',
                selectedPouch: '',
                selectedSticker: '',
                hasil_kemasan_pcs: '',
                pouch_capacity_gram: 0,
                pouch_hpp: 0,
                pouch_stock: 999999,
                sticker_hpp: 0,
                sticker_stock: 999999,
                harga_jual_final: ''
            },
            preview: {
                biaya_bahan_baku: 0,
                biaya_listrik: 0,
                biaya_penyusutan: 0,
                biaya_gaji: 0,
                biaya_kemasan: 0,
                total_hpp: 0,
                hpp_per_pcs: 0,
                pekerja_list: [],
                biaya_tenaga_kerja_batch: 0,
                is_fallback_gaji: false,
                max_output_pcs: 999999
            },
            timeObj: new Date(),

            init() {
                setInterval(() => { this.timeObj = new Date(); }, 1000);
            },

            get currentTime() {
                return this.timeObj.toLocaleTimeString('id-ID', { hour12: false });
            },

            get currentDate() {
                return this.timeObj.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            },

            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
            },

            getMaxPcs() {
                if (!this.form.selectedBatch || !this.form.pouch_capacity_gram || this.form.pouch_capacity_gram <= 0) return 0;
                try {
                    const batchObj = JSON.parse(this.form.selectedBatch);
                    const totalRoastedGram = batchObj.berat_matang_bersih * 1000;
                    
                    return Math.floor(totalRoastedGram / this.form.pouch_capacity_gram);
                } catch (e) {
                    return 0;
                }
            },

            get canSubmit() {
                const max = this.getMaxPcs();
                const inputVal = parseFloat(this.form.hasil_kemasan_pcs) || 0;
                const finalPrice = parseFloat(this.form.harga_jual_final) || 0;
                return this.form.selectedBatch !== '' && 
                       this.form.selectedPouch !== '' &&
                       inputVal > 0 && 
                       (max === 0 || inputVal <= max) &&
                       (this.preview.max_output_pcs === undefined || inputVal <= this.preview.max_output_pcs) &&
                       this.preview.total_hpp > 0 &&
                       finalPrice > 0 &&
                       !this.loading;
            },

            onPouchChange(event) {
                const selectEl = event.target;
                const selectedOpt = selectEl.options[selectEl.selectedIndex];
                if (selectedOpt && selectedOpt.value !== "") {
                    this.form.pouch_hpp = parseFloat(selectedOpt.getAttribute('data-hpp') || 0);
                    this.form.pouch_capacity_gram = parseFloat(selectedOpt.getAttribute('data-capacity') || 0);
                    this.form.pouch_stock = parseFloat(selectedOpt.getAttribute('data-stock') || 0);
                } else {
                    this.form.pouch_hpp = 0;
                    this.form.pouch_capacity_gram = 0;
                    this.form.pouch_stock = 999999;
                }
                this.form.hasil_kemasan_pcs = this.getMaxPcs();
                this.fetchPreview();
            },

            onStickerChange(event) {
                const selectEl = event.target;
                const selectedOpt = selectEl.options[selectEl.selectedIndex];
                if (selectedOpt && selectedOpt.value !== "") {
                    this.form.sticker_hpp = parseFloat(selectedOpt.getAttribute('data-hpp') || 0);
                    this.form.sticker_stock = parseFloat(selectedOpt.getAttribute('data-stock') || 0);
                } else {
                    this.form.sticker_hpp = 0;
                    this.form.sticker_stock = 999999;
                }
                this.fetchPreview();
            },

            async fetchPreview() {
                if (!this.form.selectedBatch || !this.form.selectedPouch || !this.form.hasil_kemasan_pcs) {
                    this.preview = { biaya_bahan_baku: 0, biaya_listrik: 0, biaya_penyusutan: 0, biaya_gaji: 0, biaya_kemasan: 0, total_hpp: 0, hpp_per_pcs: 0, pekerja_list: [], biaya_tenaga_kerja_batch: 0, is_fallback_gaji: false, max_output_pcs: 999999 };
                    return;
                }

                this.loading = true;
                try {
                    const res = await fetch('/internal-api/hpp-calculator/preview', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            batch: this.form.selectedBatch,
                            hasil_kemasan_pcs: this.form.hasil_kemasan_pcs,
                            pouch_id: this.form.selectedPouch,
                            sticker_id: this.form.selectedSticker || null
                        })
                    });
                    
                    if(res.ok) {
                        this.preview = await res.json();
                        if (!this.form.harga_jual_final && this.preview.hpp_per_pcs > 0) {
                            this.form.harga_jual_final = Math.ceil((this.preview.hpp_per_pcs * 1.5) / 500) * 500;
                        }
                    }
                } catch(e) {
                    console.error('Failed to fetch preview', e);
                } finally {
                    this.loading = false;
                }
            }
        }));
    });
</script>
@endpush
@endsection