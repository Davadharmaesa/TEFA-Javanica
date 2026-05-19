@extends('layouts.app')

@section('title', 'Kalkulator HPP - TEFA Javanica')

@section('content')
<div class="space-y-6 pb-12" x-data="hppCalculator()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-border pb-4">
        <div>
            <h1 class="text-3xl font-heading font-black text-primary tracking-tight">Kalkulator HPP Otomatis</h1>
            <p class="text-muted-foreground font-sans text-sm mt-1">
                Hitung Harga Pokok Produksi secara dinamis berdasarkan durasi mesin dan output kemasan.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/20 text-green-600 rounded-xl font-bold">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form Hitung (Kiri) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-card border border-border shadow-sm rounded-2xl overflow-hidden flex flex-col">
                <div class="bg-primary px-5 py-4">
                    <h2 class="font-heading font-bold text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg>
                        Parameter Produksi
                    </h2>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-primary mb-1">Pilih Batch Roasting (Selesai)</label>
                        <select x-model="form.selectedBatch" @change="fetchPreview()" class="w-full bg-background border border-border rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                            <option value="">-- Pilih Batch --</option>
                            <template x-for="batch in batches" :key="batch.id">
                                <option :value="JSON.stringify(batch)" x-text="batch.kode_batch + ' | ' + batch.green_bean_nama + ' | ' + batch.durasi_roasting + ' Menit'"></option>
                            </template>
                        </select>
                        <p class="text-xs text-muted-foreground mt-1" x-show="form.selectedBatch">
                            Biaya Bahan Baku: <span class="font-bold text-foreground" x-text="formatRupiah(JSON.parse(form.selectedBatch).harga_bahan_baku)"></span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-primary mb-1">Daya Mesin (Kilowatt)</label>
                        <input type="number" step="0.1" x-model="form.daya_mesin_kw" @wheel="$event.target.blur()" @input.debounce.500ms="fetchPreview()" class="w-full bg-background border border-border rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="Contoh: 1.5">
                        <p class="text-[10px] text-muted-foreground mt-1">Tarif Listrik: Rp{{ $biayaListrik->harga }} / kWh</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-primary mb-1">Jenis Kemasan Target</label>
                        <select x-model="form.packaging_hpp" @change="fetchPreview()" class="w-full bg-background border border-border rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all">
                            <option value="0">-- Pilih Kemasan --</option>
                            @foreach($packagings as $pack)
                                <option value="{{ $pack->harga_satuan }}">{{ $pack->nama_kemasan }} (Rp{{ number_format($pack->harga_satuan, 0, ',', '.') }}/pcs)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-primary mb-1">Total Output (Pcs)</label>
                        <input type="number"
                               min="0.01"
                               step="0.01"
                               x-model="form.hasil_kemasan_pcs" 
                               @wheel="$event.target.blur()" 
                               @input.debounce.500ms="fetchPreview()" 
                               class="w-full bg-background border border-border rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" 
                               placeholder="Contoh: 25">
                        <p class="text-[10px] text-muted-foreground mt-1">Gaji Operator & Packing: Rp{{ $biayaGaji->harga }} / Pcs</p>
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
                            Rincian HPP Dinamis
                        </h3>
                        
                        <div class="space-y-4">
                            <!-- Bahan Baku -->
                            <div class="flex justify-between items-center border-b border-border/50 pb-2">
                                <div>
                                    <p class="text-sm font-bold text-foreground">Bahan Baku (Green Bean)</p>
                                    <p class="text-[10px] text-muted-foreground">Harga per kg × Berat terpakai</p>
                                </div>
                                <p class="font-mono font-bold text-primary" x-text="formatRupiah(preview.biaya_bahan_baku)"></p>
                            </div>
                            
                            <!-- Listrik -->
                            <div class="flex justify-between items-center border-b border-border/50 pb-2">
                                <div>
                                    <p class="text-sm font-bold text-foreground">Biaya Listrik (Energi)</p>
                                    <p class="text-[10px] text-muted-foreground">Durasi(jam) × Daya Mesin(kW) × Tarif(kWh)</p>
                                </div>
                                <p class="font-mono font-bold text-primary" x-text="formatRupiah(preview.biaya_listrik)"></p>
                            </div>

                            <!-- Penyusutan -->
                            <div class="flex justify-between items-center border-b border-border/50 pb-2">
                                <div>
                                    <p class="text-sm font-bold text-foreground">Penyusutan Mesin</p>
                                    <p class="text-[10px] text-muted-foreground">Biaya tetap per siklus (batch)</p>
                                </div>
                                <p class="font-mono font-bold text-primary" x-text="formatRupiah(preview.biaya_penyusutan)"></p>
                            </div>

                            <!-- Gaji -->
                            <div class="flex justify-between items-center border-b border-border/50 pb-2">
                                <div>
                                    <p class="text-sm font-bold text-foreground">Tenaga Kerja (Gaji + Packing)</p>
                                    <p class="text-[10px] text-muted-foreground">Total Pcs × Tarif per Pcs</p>
                                </div>
                                <p class="font-mono font-bold text-primary" x-text="formatRupiah(preview.biaya_gaji)"></p>
                            </div>

                            <!-- Kemasan -->
                            <div class="flex justify-between items-center border-b border-border/50 pb-2">
                                <div>
                                    <p class="text-sm font-bold text-foreground">Modal Kemasan (Packaging)</p>
                                    <p class="text-[10px] text-muted-foreground">Total Pcs × HPP Kemasan</p>
                                </div>
                                <p class="font-mono font-bold text-primary" x-text="formatRupiah(preview.biaya_kemasan)"></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-dashed border-border flex justify-between items-end">
                        <div>
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">HPP Per Pcs Target</p>
                            <p class="text-3xl font-black font-mono text-secondary"><span x-text="formatRupiah(preview.hpp_per_pcs)"></span></p>
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
                    <template x-for="batch in batches.filter(b => b.sudah_dihitung)" :key="batch.id">
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
                    <tr x-show="batches.filter(b => b.sudah_dihitung).length === 0">
                        <td colspan="6" class="p-8 text-center text-muted-foreground font-medium text-sm">Belum ada riwayat perhitungan HPP yang tersimpan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('hppCalculator', () => ({
            batches: @json($batches),
            loading: false,
            form: {
                selectedBatch: '',
                daya_mesin_kw: 1.5, // Default 1.5 kW
                packaging_hpp: 0,
                hasil_kemasan_pcs: ''
            },
            preview: {
                biaya_bahan_baku: 0,
                biaya_listrik: 0,
                biaya_penyusutan: 0,
                biaya_gaji: 0,
                biaya_kemasan: 0,
                total_hpp: 0,
                hpp_per_pcs: 0
            },

            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
            },

            get canSubmit() {
                return this.form.selectedBatch !== '' && 
                       this.form.hasil_kemasan_pcs > 0 && 
                       this.preview.total_hpp > 0 &&
                       !this.loading;
            },

            async fetchPreview() {
                if (!this.form.selectedBatch || !this.form.hasil_kemasan_pcs || !this.form.daya_mesin_kw) {
                    this.preview = { biaya_bahan_baku: 0, biaya_listrik: 0, biaya_penyusutan: 0, biaya_gaji: 0, biaya_kemasan: 0, total_hpp: 0, hpp_per_pcs: 0 };
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
                            daya_mesin_kw: this.form.daya_mesin_kw,
                            packaging_hpp: this.form.packaging_hpp
                        })
                    });
                    
                    if(res.ok) {
                        this.preview = await res.json();
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
