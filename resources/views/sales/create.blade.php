@extends('layouts.app')

@section('title', 'Pre-Order Baru - TEFA Javanica')

@section('content')
<div class="space-y-6 pb-12" x-data="preOrderForm">
    
    <!-- Tombol Kembali -->
    <div>
        <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-muted-foreground hover:text-primary transition-colors bg-card border border-border/40 rounded-xl px-4 py-2 shadow-sm hover:shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali ke Daftar Penjualan
        </a>
    </div>

    <!-- Header Title -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-border pb-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="flex h-3 w-3 relative">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-secondary"></span>
                </span>
                <span class="text-xs font-mono font-bold text-secondary uppercase tracking-widest">Sistem Online</span>
            </div>
            <h1 class="text-3xl font-heading font-black text-primary tracking-tight">Catat Pre-Order Baru</h1>
            <p class="text-muted-foreground font-sans text-sm mt-1">
                Catat pesanan baru produk jadi (Finished Goods) yang sudah siap jual berdasarkan HPP produk.
            </p>
        </div>
        <div class="text-right">
            <p class="text-2xl font-mono font-black text-primary" x-text="currentTime"></p>
            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest" x-text="currentDate"></p>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-600 rounded-xl font-bold text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Workspace -->
    <form action="{{ route('sales.store') }}" method="POST" @submit.prevent="submitForm" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf
        
        <!-- KIRI: Detail Pesanan (Spans 8 cols) -->
        <div class="lg:col-span-8 bg-card border border-border shadow-sm rounded-2xl p-6 space-y-6">
            <h3 class="text-sm font-bold text-primary uppercase tracking-widest flex items-center gap-2 border-b border-border/30 pb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                Detail Produk & Pelanggan
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Pelanggan -->
                <div class="space-y-1.5 md:col-span-2">
                    <label for="customer_name" class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Nama Pelanggan</label>
                    <input type="text" name="customer_name" id="customer_name" required value="{{ old('customer_name') }}" placeholder="Contoh: Cafe Senja / Bapak Budi (Dosen)" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all">
                </div>

                <!-- Tipe Pesanan -->
                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Tipe Pesanan</label>
                    <div class="grid grid-cols-2 gap-1 bg-muted/40 p-1 rounded-xl border border-border/50 max-w-xs">
                        <button type="button" @click="tipePesanan = 'internal'" :class="tipePesanan === 'internal' ? 'bg-[#526B42] text-white shadow-sm' : 'text-muted-foreground hover:bg-muted/30'" class="py-2.5 text-xs font-black rounded-lg transition-all uppercase tracking-wider flex items-center justify-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            Internal
                        </button>
                        <button type="button" @click="tipePesanan = 'eksternal'" :class="tipePesanan === 'eksternal' ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:bg-muted/30'" class="py-2.5 text-xs font-black rounded-lg transition-all uppercase tracking-wider flex items-center justify-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            Eksternal
                        </button>
                    </div>
                    <input type="hidden" name="tipe_pesanan" :value="tipePesanan">
                    <div class="flex items-center gap-2 text-[11px] text-muted-foreground bg-[#526B42]/5 px-3 py-2 rounded-xl border border-[#526B42]/10 max-w-md mt-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-secondary shrink-0"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        <span><strong>Internal</strong>: warga kampus/dosen. <strong>Eksternal</strong>: pelanggan umum/kafe.</span>
                    </div>
                </div>

                <!-- Dropdown Produk Jadi (Finished Goods) -->
                <div class="space-y-1.5">
                    <label for="finished_good_id" class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Produk Jadi (Kopi & Kemasan)</label>
                    <select id="finished_good_id" name="finished_good_id" required x-model="selectedGoodId" @change="updateSelectedGood" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer">
                        <option value="" disabled selected>-- Pilih Produk Jadi --</option>
                        @foreach($finishedGoods as $good)
                            <option value="{{ $good['roasted_bean_id'] }}-{{ $good['berat_gram'] }}" {{ old('finished_good_id') == ($good['roasted_bean_id'].'-'.$good['berat_gram']) ? 'selected' : '' }}>
                                {{ $good['nama'] }} (Stok: {{ $good['stok_pcs'] }} Pcs)
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- QTY Pcs -->
                <div class="space-y-1.5">
                    <label for="jumlah_pcs" class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Jumlah Pesanan (Pcs)</label>
                    <input type="number" min="1" id="jumlah_pcs" name="jumlah_pcs" required x-model.number="quantityPcs" @input="calculatePrices" placeholder="Contoh: 12" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-mono font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all">
                </div>
            </div>

            <!-- Detail Kalkulasi Fisik & HPP -->
            <template x-if="selectedGood">
                <div class="bg-[#526B42]/5 border border-[#526B42]/20 rounded-xl p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Berat per Kemasan:</span>
                        <span class="font-bold text-primary" x-text="selectedGood.berat_gram + ' Gram'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Estimasi Berat Total:</span>
                        <span class="font-bold text-primary" x-text="(quantityPcs * selectedGood.berat_gram).toLocaleString('id-ID') + ' Gram (' + ((quantityPcs * selectedGood.berat_gram)/1000).toFixed(2) + ' Kg)'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">HPP per Pcs (Terhitung):</span>
                        <span class="font-bold text-primary" x-text="'Rp ' + selectedGood.hpp_per_pcs.toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Harga Jual Disarankan (+50%):</span>
                        <span class="font-bold text-secondary" x-text="'Rp ' + selectedGood.harga_jual_per_pcs.toLocaleString('id-ID') + ' / Pcs'"></span>
                    </div>
                </div>
            </template>

            <!-- Status Pengiriman, Status Pengambilan, & Target Selesai -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-1.5">
                    <label for="status_pengiriman" class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Status Pengiriman</label>
                    <select name="status_pengiriman" id="status_pengiriman" required class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer">
                        <option value="Menunggu Konfirmasi" {{ old('status_pengiriman') === 'Menunggu Konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                        <option value="Diproses" {{ old('status_pengiriman') === 'Diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="Siap Diambil" {{ old('status_pengiriman') === 'Siap Diambil' ? 'selected' : '' }}>Siap Diambil</option>
                        <option value="Selesai" {{ old('status_pengiriman', 'Menunggu Konfirmasi') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label for="status_pengambilan" class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Status Pengambilan</label>
                    <select name="status_pengambilan" id="status_pengambilan" required class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer">
                        <option value="Belum Diambil" {{ old('status_pengambilan', 'Belum Diambil') === 'Belum Diambil' ? 'selected' : '' }}>Belum Diambil</option>
                        <option value="Sudah Diambil" {{ old('status_pengambilan') === 'Sudah Diambil' ? 'selected' : '' }}>Sudah Diambil</option>
                    </select>
                </div>
                
                <div class="space-y-1.5">
                    <label for="tanggal_pesanan" class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Target Selesai / Pengambilan</label>
                    <input type="date" name="tanggal_pesanan" id="tanggal_pesanan" required value="{{ old('tanggal_pesanan', now()->toDateString()) }}" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer">
                </div>
            </div>
        </div>

        <!-- KANAN: Panel Pembayaran (Spans 4 cols) -->
        <div class="lg:col-span-4 bg-card border border-border shadow-lg rounded-2xl p-6 flex flex-col justify-between space-y-6">
            <div class="space-y-6">
                <h3 class="text-sm font-bold text-primary uppercase tracking-widest flex items-center gap-2 border-b border-border/30 pb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2" ry="2"/><line x1="12" y1="4" x2="12" y2="20"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
                    Informasi Pembayaran
                </h3>

                <!-- Hidden inputs agar kompatibel dengan Controller di backend -->
                <input type="hidden" name="roasted_bean_id" :value="selectedGood ? selectedGood.roasted_bean_id : ''">
                <input type="hidden" name="jumlah_gram_pesanan" :value="selectedGood ? quantityPcs * selectedGood.berat_gram : ''">
                
                <!-- Pemilih Metode Pembayaran -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-1 bg-muted/40 p-1 rounded-xl border border-border/50">
                        <button type="button" @click="paymentMethod = 'lunas'" :class="paymentMethod === 'lunas' ? 'bg-[#526B42] text-white shadow-sm' : 'text-muted-foreground hover:bg-muted/30'" class="py-2 text-xs font-black rounded-lg transition-all uppercase tracking-wider flex items-center justify-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M12 12h.01"/><path d="M17 12h.01"/><path d="M7 12h.01"/></svg> Lunas</button>
                        <button type="button" @click="paymentMethod = 'dp'" :class="paymentMethod === 'dp' ? 'bg-[#526B42] text-white shadow-sm' : 'text-muted-foreground hover:bg-muted/30'" class="py-2 text-xs font-black rounded-lg transition-all uppercase tracking-wider flex items-center justify-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg> Cicil / DP</button>
                        <button type="button" @click="paymentMethod = 'belum_bayar'" :class="paymentMethod === 'belum_bayar' ? 'bg-[#526B42] text-white shadow-sm' : 'text-muted-foreground hover:bg-muted/30'" class="py-2 text-xs font-black rounded-lg transition-all uppercase tracking-wider flex items-center justify-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> Hutang</button>
                    </div>
                    <input type="hidden" name="metode_pembayaran" :value="paymentMethod">
                </div>

                <!-- Total Harga Jual (harga_jual_pesanan) -->
                <div class="space-y-1.5">
                    <label for="harga_jual_pesanan_display" class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Total Harga Transaksi (Rp)</label>
                    <input type="text" id="harga_jual_pesanan_display" required 
                           :value="window.formatRupiahString(totalPrice)" 
                           @input="totalPrice = window.parseRupiahString($event.target.value)" 
                           placeholder="0" 
                           :class="isSellingBelowHpp ? 'border-red-500 text-red-600 focus:border-red-500 focus:ring-red-500/50' : 'border-border text-primary focus:border-primary focus:ring-primary/50'"
                           class="w-full bg-background border-2 rounded-xl px-4 py-3.5 text-2xl font-mono font-black focus:outline-none focus:ring-2 transition-all">
                    <input type="hidden" name="harga_jual_pesanan" :value="totalPrice || 0">
                    
                    <template x-if="isSellingBelowHpp">
                        <p class="text-xs font-bold text-red-500 mt-1 flex items-center gap-1.5 bg-red-500/10 p-2 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                            Peringatan Jual Rugi: Total HPP/Modal adalah <span x-text="'Rp ' + totalHpp.toLocaleString('id-ID')"></span>!
                        </p>
                    </template>
                </div>

                <!-- Kalkulator Kasir LUNAS -->
                <div x-show="paymentMethod === 'lunas'" x-transition class="space-y-3 bg-primary/5 p-4 rounded-xl border border-primary/20">
                    <div class="space-y-1.5">
                        <label for="uang_diterima_display" class="text-xs font-bold text-primary uppercase tracking-wider">Uang Diterima (Rp)</label>
                        <input type="text" id="uang_diterima_display" 
                               :value="window.formatRupiahString(uangDiterima)" 
                               @input="uangDiterima = window.parseRupiahString($event.target.value)" 
                               placeholder="0" 
                               class="w-full bg-background border-2 border-primary/50 rounded-xl px-4 py-3 text-xl font-mono font-black focus:outline-none focus:ring-2 focus:ring-primary/50 text-primary transition-all">
                    </div>
                    
                    <div class="flex justify-between items-center pt-2 border-t border-primary/20">
                        <span class="text-sm font-bold text-primary uppercase tracking-wider">Kembalian:</span>
                        <span class="text-2xl font-mono font-black" :class="kembalian < 0 ? 'text-red-500' : 'text-green-600'" x-text="'Rp ' + Math.max(0, kembalian).toLocaleString('id-ID')"></span>
                    </div>
                    <template x-if="kembalian < 0">
                        <p class="text-xs font-bold text-red-500 mt-1 flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            Uang yang diterima kurang <span x-text="'Rp ' + Math.abs(kembalian).toLocaleString('id-ID')"></span>!
                        </p>
                    </template>
                </div>

                <!-- Nominal DP (Hanya jika Cicil / DP) -->
                <div x-show="paymentMethod === 'dp'" x-transition class="space-y-3 bg-secondary/5 p-4 rounded-xl border border-secondary/20" style="display: none;">
                    <div class="space-y-1.5">
                        <label for="nominal_dp_display" class="text-xs font-bold text-secondary uppercase tracking-wider">Nominal DP / Cicilan (Rp)</label>
                        <input type="text" id="nominal_dp_display" 
                               :value="window.formatRupiahString(nominalDp)" 
                               @input="nominalDp = window.parseRupiahString($event.target.value)" 
                               placeholder="0" 
                               class="w-full bg-background border-2 border-secondary/50 rounded-xl px-4 py-3 text-xl font-mono font-black focus:outline-none focus:ring-2 focus:ring-secondary/50 text-secondary transition-all">
                        <input type="hidden" name="nominal_dp" :value="nominalDp || 0">
                    </div>
                    
                    <div class="flex justify-between items-center pt-2 border-t border-secondary/20">
                        <span class="text-sm font-bold text-secondary uppercase tracking-wider">Sisa Hutang:</span>
                        <span class="text-2xl font-mono font-black text-secondary" x-text="'Rp ' + Math.max(0, sisaHutang).toLocaleString('id-ID')"></span>
                    </div>
                    <template x-if="nominalDp >= totalPrice && totalPrice > 0">
                        <p class="text-xs font-bold text-orange-500 mt-1">DP mencapai total harga. Sebaiknya gunakan metode "Lunas".</p>
                    </template>
                </div>

                <!-- Sisa Pembayaran Info (Jika Belum Bayar) -->
                <div x-show="paymentMethod === 'belum_bayar'" x-transition class="space-y-1.5" style="display: none;">
                    <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Total Hutang (Rp)</label>
                    <div class="w-full bg-muted/20 border border-border/60 rounded-xl px-4 py-3 text-sm font-mono font-bold text-muted-foreground" x-text="'Rp ' + (totalPrice || 0).toLocaleString('id-ID')"></div>
                </div>

                <!-- Batas Waktu Pembayaran (Untuk DP atau Hutang) -->
                <div x-show="paymentMethod === 'belum_bayar' || paymentMethod === 'dp'" x-transition class="space-y-1.5" style="display: none;">
                    <label for="batas_waktu_pembayaran" class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Jatuh Tempo Pelunasan</label>
                    <input type="date" name="batas_waktu_pembayaran" id="batas_waktu_pembayaran" :required="paymentMethod === 'belum_bayar' || paymentMethod === 'dp'" value="{{ old('batas_waktu_pembayaran') }}" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer">
                </div>
            </div>

            <div class="pt-6 border-t border-border/30">
                <button type="submit" class="w-full bg-primary text-primary-foreground hover:bg-primary/90 py-4 rounded-xl font-black uppercase tracking-widest text-sm shadow-md transition-all flex justify-center items-center gap-2 hover:-translate-y-0.5 active:translate-y-0">
                    Proses Transaksi
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('preOrderForm', () => ({
            finishedGoods: @json($finishedGoods),
            selectedGoodId: '{{ old('finished_good_id') }}',
            selectedGood: null,
            quantityPcs: {{ old('jumlah_pcs') !== null ? old('jumlah_pcs') : 'null' }},
            totalPrice: null,
            timeObj: new Date(),
            paymentMethod: '{{ old('metode_pembayaran', 'lunas') }}',
            tipePesanan: '{{ old('tipe_pesanan', request('tab') === 'internal' ? 'internal' : 'eksternal') }}',

            uangDiterima: null,
            nominalDp: null,

            init() {
                this.updateSelectedGood();
                setInterval(() => { this.timeObj = new Date(); }, 1000);
            },

            get currentTime() {
                return this.timeObj.toLocaleTimeString('id-ID', { hour12: false });
            },

            get currentDate() {
                return this.timeObj.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            },

            get totalHpp() {
                if (this.selectedGood && this.quantityPcs) {
                    return this.quantityPcs * this.selectedGood.hpp_per_pcs;
                }
                return 0;
            },

            get isSellingBelowHpp() {
                if (this.totalPrice !== null && this.totalPrice !== '' && this.totalHpp) {
                    return parseInt(this.totalPrice) < parseInt(this.totalHpp);
                }
                return false;
            },
            
            get kembalian() {
                if (this.totalPrice !== null && this.uangDiterima !== null) {
                    return parseInt(this.uangDiterima || 0) - parseInt(this.totalPrice || 0);
                }
                return 0;
            },

            get sisaHutang() {
                if (this.totalPrice !== null && this.nominalDp !== null) {
                    return parseInt(this.totalPrice || 0) - parseInt(this.nominalDp || 0);
                }
                return 0;
            },

            submitForm(e) {
                if (this.paymentMethod === 'lunas') {
                    if (this.uangDiterima === null || this.uangDiterima === '' || this.uangDiterima == 0) {
                        alert('Silakan masukkan nominal Uang Diterima dari pelanggan!');
                        return;
                    }
                    if (this.kembalian < 0) {
                        alert('Gagal memproses transaksi: Uang yang diterima kurang Rp ' + Math.abs(this.kembalian).toLocaleString('id-ID') + '!');
                        return;
                    }
                } else if (this.paymentMethod === 'dp') {
                    if (this.nominalDp === null || this.nominalDp === '' || this.nominalDp == 0) {
                        alert('Silakan masukkan nominal Uang DP/Cicilan dari pelanggan!');
                        return;
                    }
                }

                if (this.isSellingBelowHpp) {
                    const confirmMsg = 'PERINGATAN KERUGIAN!\n\n' +
                                     'Total Harga Jual (Rp ' + parseInt(this.totalPrice).toLocaleString('id-ID') + ') lebih rendah dari Total Modal/HPP (Rp ' + parseInt(this.totalHpp).toLocaleString('id-ID') + ').\n\n' +
                                     'Apakah Anda YAKIN ingin tetap memproses transaksi ini dan menjual rugi?';
                    
                    if (confirm(confirmMsg)) {
                        e.target.submit();
                    }
                } else {
                    e.target.submit();
                }
            },

            get sisaPembayaran() {
                if (this.paymentMethod === 'belum_bayar' && this.totalPrice) {
                    return this.totalPrice;
                }
                return 0;
            },

            updateSelectedGood() {
                if (this.selectedGoodId) {
                    const [beanId, berat] = this.selectedGoodId.split('-');
                    this.selectedGood = this.finishedGoods.find(g => g.roasted_bean_id == beanId && g.berat_gram == berat);
                } else {
                    this.selectedGood = null;
                }
                this.calculatePrices();
            },

            calculatePrices() {
                if (this.selectedGood && this.quantityPcs) {
                    this.totalPrice = this.quantityPcs * this.selectedGood.harga_jual_per_pcs;
                } else {
                    this.totalPrice = null;
                }
            }
        }));
    });
</script>
@endpush
@endsection