@extends('layouts.app')

@section('title', 'Edit Pre-Order - TEFA Javanica')

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
            <h1 class="text-3xl font-heading font-black text-primary tracking-tight">Edit Pre-Order</h1>
            <p class="text-muted-foreground font-sans text-sm mt-1">
                Ubah rincian data transaksi pre-order pelanggan.
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
    <form action="{{ route('sales.update', $order->id) }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf
        @method('PUT')
        
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
                    <input type="text" name="customer_name" id="customer_name" required value="{{ old('customer_name', $order->nama_pesanan) }}" placeholder="Contoh: Cafe Senja / Bapak Budi (Dosen)" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all">
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
                        <option value="" disabled>-- Pilih Produk Jadi --</option>
                        @foreach($finishedGoods as $good)
                            <option value="{{ $good['roasted_bean_id'] }}-{{ $good['berat_gram'] }}">
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
                        <option value="Menunggu Konfirmasi" {{ old('status_pengiriman', $order->status_pengiriman) === 'Menunggu Konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                        <option value="Diproses" {{ old('status_pengiriman', $order->status_pengiriman) === 'Diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="Siap Diambil" {{ old('status_pengiriman', $order->status_pengiriman) === 'Siap Diambil' ? 'selected' : '' }}>Siap Diambil</option>
                        <option value="Selesai" {{ old('status_pengiriman', $order->status_pengiriman) === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label for="status_pengambilan" class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Status Pengambilan</label>
                    <select name="status_pengambilan" id="status_pengambilan" required class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer">
                        <option value="Belum Diambil" {{ old('status_pengambilan', $order->status_pengambilan) === 'Belum Diambil' ? 'selected' : '' }}>Belum Diambil</option>
                        <option value="Sudah Diambil" {{ old('status_pengambilan', $order->status_pengambilan) === 'Sudah Diambil' ? 'selected' : '' }}>Sudah Diambil</option>
                    </select>
                </div>
                
                <div class="space-y-1.5">
                    <label for="tanggal_pesanan" class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Target Selesai / Pengambilan</label>
                    <input type="date" name="tanggal_pesanan" id="tanggal_pesanan" required value="{{ old('tanggal_pesanan', $order->tanggal_pesanan ? $order->tanggal_pesanan->toDateString() : '') }}" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer">
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
                    <div class="grid grid-cols-2 gap-1 bg-muted/40 p-1 rounded-xl border border-border/50">
                        <button type="button" @click="paymentMethod = 'lunas'" :class="paymentMethod === 'lunas' ? 'bg-[#526B42] text-white shadow-sm' : 'text-muted-foreground hover:bg-muted/30'" class="py-2 text-xs font-black rounded-lg transition-all uppercase tracking-wider">Lunas</button>
                        <button type="button" @click="paymentMethod = 'belum_bayar'" :class="paymentMethod === 'belum_bayar' ? 'bg-[#526B42] text-white shadow-sm' : 'text-muted-foreground hover:bg-muted/30'" class="py-2 text-xs font-black rounded-lg transition-all uppercase tracking-wider">Belum Bayar</button>
                    </div>
                    <input type="hidden" name="metode_pembayaran" :value="paymentMethod">
                </div>

                <!-- Total Harga Jual (harga_jual_pesanan) -->
                <div class="space-y-1.5">
                    <label for="harga_jual_pesanan" class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Total Harga Transaksi (Rp)</label>
                    <input type="number" name="harga_jual_pesanan" id="harga_jual_pesanan" required x-model.number="totalPrice" placeholder="0" class="w-full bg-background border-2 border-border rounded-xl px-4 py-3.5 text-2xl font-mono font-black text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all">
                </div>

                <!-- Sisa Pembayaran (Hanya jika Belum Bayar) -->
                <div x-show="paymentMethod === 'belum_bayar'" x-transition class="space-y-1.5" style="display: none;">
                    <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Sisa Pembayaran (Rp)</label>
                    <div class="w-full bg-muted/20 border border-border/60 rounded-xl px-4 py-3 text-sm font-mono font-bold text-muted-foreground" x-text="'Rp ' + (sisaPembayaran || 0).toLocaleString('id-ID')"></div>
                </div>

                <!-- Batas Waktu Pembayaran (Hanya jika Belum Bayar) -->
                <div x-show="paymentMethod === 'belum_bayar'" x-transition class="space-y-1.5" style="display: none;">
                    <label for="batas_waktu_pembayaran" class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Batas Waktu Pembayaran</label>
                    <input type="date" name="batas_waktu_pembayaran" id="batas_waktu_pembayaran" :required="paymentMethod === 'belum_bayar'" value="{{ old('batas_waktu_pembayaran', $order->batas_waktu_pembayaran ? $order->batas_waktu_pembayaran->toDateString() : '') }}" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer">
                </div>
            </div>

            <div class="pt-6 border-t border-border/30">
                <button type="submit" class="w-full bg-primary text-primary-foreground hover:bg-primary/90 py-4 rounded-xl font-black uppercase tracking-widest text-sm shadow-md transition-all flex justify-center items-center gap-2 hover:-translate-y-0.5 active:translate-y-0">
                    Simpan Perubahan
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
            selectedGoodId: '{{ old('finished_good_id', $order->roasted_bean_id . '-' . ($order->packaging ? $order->packaging->kapasitas_gram : '')) }}',
            selectedGood: null,
            quantityPcs: {{ old('jumlah_pcs', $order->jumlah_pcs) !== null ? old('jumlah_pcs', $order->jumlah_pcs) : 'null' }},
            totalPrice: {{ old('harga_jual_pesanan', $order->harga_jual_pesanan) !== null ? old('harga_jual_pesanan', $order->harga_jual_pesanan) : 'null' }},
            timeObj: new Date(),
            paymentMethod: '{{ old('metode_pembayaran', $order->metode_pembayaran) }}',
            tipePesanan: '{{ old('tipe_pesanan', $order->tipe_pesanan) }}',

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
                // Do not recalculate price on init if old value or existing value exists
                // so we don't overwrite custom prices
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
