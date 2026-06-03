@extends('layouts.app')

@section('title', 'Kasir / Transaksi Baru - TEFA Javanica')

@section('content')
<div class="space-y-6 pb-12" x-data="posSystem()">
    
    <!-- Header Title -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-border pb-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="flex h-3 w-3 relative">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-secondary"></span>
                </span>
                <span class="text-xs font-mono font-bold text-secondary uppercase tracking-widest">Sistem Kasir Aktif</span>
            </div>
            <h1 class="text-3xl font-heading font-black text-primary tracking-tight">Terminal Penjualan</h1>
            <p class="text-muted-foreground font-sans text-sm mt-1">
                Catat pesanan pelanggan dan kalkulasi pembayaran secara otomatis.
            </p>
        </div>
    </div>

    <!-- Main POS Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- KIRI: Pemilihan Produk & Keranjang (Spans 8 cols) -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Tambah Item Section -->
            <div class="bg-card border border-border shadow-sm rounded-2xl p-5 relative overflow-hidden group">
                <h3 class="text-sm font-bold text-primary uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    Pilih Produk
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    <!-- Dropdown Produk -->
                    <div class="md:col-span-7 space-y-1.5">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Produk Javanica</label>
                        <select x-model="selectedProduct" class="w-full bg-background border border-border/60 rounded-xl px-4 py-2.5 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer">
                            <option value="" disabled selected>-- Pilih Produk Jadi --</option>
                            <template x-for="product in availableProducts" :key="product.id">
                                <option :value="product.id" x-text="product.nama_kopi + ' - Rp ' + formatRupiah(product.harga_jual)"></option>
                            </template>
                        </select>
                    </div>
                    
                    <!-- QTY -->
                    <div class="md:col-span-3 space-y-1.5">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Jumlah (Qty)</label>
                        <input type="number" min="1" x-model="qty" placeholder="1" class="w-full bg-background border border-border/60 rounded-xl px-4 py-2.5 text-sm font-mono font-black text-center text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    </div>

                    <!-- Tombol Tambah -->
                    <div class="md:col-span-2">
                        <button @click="addToCart" :disabled="!selectedProduct || !qty" class="w-full bg-primary text-primary-foreground py-2.5 rounded-xl font-bold hover:bg-primary/90 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-md">
                            Tambah
                        </button>
                    </div>
                </div>
            </div>

            <!-- Keranjang / Cart Table -->
            <div class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden min-h-[300px] flex flex-col">
                <div class="px-5 py-3 border-b border-border/50 bg-primary/5">
                    <h3 class="text-sm font-bold text-primary uppercase tracking-widest">Keranjang Pesanan</h3>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-muted/10 border-b border-border/50 text-[10px] uppercase tracking-wider text-muted-foreground font-bold">
                                <th class="p-3 pl-5">Nama Produk</th>
                                <th class="p-3 text-center">Qty</th>
                                <th class="p-3 text-right">Harga Satuan</th>
                                <th class="p-3 text-right">Subtotal</th>
                                <th class="p-3 text-center pr-5">Hapus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <template x-for="(item, index) in cart" :key="index">
                                <tr class="hover:bg-muted/10 transition-colors">
                                    <td class="p-3 pl-5 font-bold text-sm text-foreground" x-text="item.name"></td>
                                    <td class="p-3 text-center font-mono font-bold text-primary text-sm" x-text="item.qty"></td>
                                    <td class="p-3 text-right font-mono text-muted-foreground text-xs" x-text="'Rp ' + formatRupiah(item.price)"></td>
                                    <td class="p-3 text-right font-mono font-black text-primary text-sm" x-text="'Rp ' + formatRupiah(item.subtotal)"></td>
                                    <td class="p-3 text-center pr-5">
                                        <button @click="removeFromCart(index)" class="p-1 text-red-500/70 hover:text-red-600 hover:bg-red-600/10 rounded transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="cart.length === 0">
                                <td colspan="5" class="p-8 text-center text-muted-foreground font-medium italic text-xs">Keranjang masih kosong. Tambahkan produk di atas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- KANAN: Panel Pembayaran (Spans 4 cols) -->
        <div class="lg:col-span-4">
            <form @submit.prevent="submitTransaction" class="bg-card border border-border shadow-lg shadow-black/5 rounded-2xl flex flex-col h-full overflow-hidden sticky top-6">
                <div class="bg-primary px-5 py-4">
                    <h2 class="font-heading font-black text-lg text-primary-foreground tracking-wide flex items-center gap-2">
                        PEMBAYARAN
                    </h2>
                </div>

                <div class="p-5 flex-1 flex flex-col space-y-5">
                    
                    <!-- Nama Pelanggan -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Nama Pelanggan (Opsional)</label>
                        <input type="text" x-model="customerName" placeholder="Umum" class="w-full bg-background border border-border/60 rounded-xl px-4 py-2.5 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all">
                    </div>

                    <hr class="border-dashed border-border">

                    <!-- Total Tagihan -->
                    <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 text-center">
                        <p class="text-[10px] font-bold text-primary uppercase tracking-widest mb-1">Total Tagihan</p>
                        <p class="text-3xl font-black font-mono text-primary truncate" x-text="'Rp ' + formatRupiah(grandTotal)"></p>
                    </div>

                    <!-- Input Pembayaran/DP -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest flex justify-between">
                            <span>Nominal Bayar / DP</span>
                            <span class="text-accent" x-show="isDP">Status: DP</span>
                            <span class="text-secondary" x-show="isLunas">Status: LUNAS</span>
                        </label>
                        <input type="number" min="0" x-model="amountPaid" placeholder="Ketik nominal..." required class="w-full bg-background border-2 border-border/80 rounded-xl px-4 py-3 text-xl font-mono font-black text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none placeholder:text-muted/50 placeholder:text-base placeholder:font-sans">
                    </div>

                    <!-- Kembalian / Sisa Tagihan -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-secondary/10 border border-secondary/20 rounded-lg p-3 text-center transition-opacity" :class="isLunas ? 'opacity-100' : 'opacity-30'">
                            <p class="text-[9px] font-bold text-secondary uppercase tracking-widest mb-1">Kembalian</p>
                            <p class="text-sm font-black font-mono text-secondary truncate" x-text="'Rp ' + formatRupiah(kembalian)"></p>
                        </div>
                        <div class="bg-accent/10 border border-accent/20 rounded-lg p-3 text-center transition-opacity" :class="isDP ? 'opacity-100' : 'opacity-30'">
                            <p class="text-[9px] font-bold text-accent uppercase tracking-widest mb-1">Sisa Hutang</p>
                            <p class="text-sm font-black font-mono text-accent truncate" x-text="'Rp ' + formatRupiah(sisaHutang)"></p>
                        </div>
                    </div>

                    <div class="pt-4 mt-auto">
                        <button type="submit" :disabled="loading || cart.length === 0 || !amountPaid" class="w-full bg-primary text-primary-foreground hover:bg-primary/90 py-4 rounded-xl font-black uppercase tracking-widest text-sm shadow-[0_10px_20px_rgba(200,80,50,0.15)] transition-all flex justify-center items-center gap-2 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">Proses Transaksi</span>
                            <span x-show="loading" class="animate-pulse">Memproses...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('posSystem', () => ({
            // Variabel produk ini diinject dari backend Laravel kamu
            availableProducts: @json($products ?? []),
            
            selectedProduct: '',
            qty: '',
            cart: [],
            customerName: '',
            amountPaid: '', // Menggunakan string kosong agar placeholder terlihat dan tidak ada nilai '0' default
            loading: false,

            // Hitung otomatis (Computed Properties)
            get grandTotal() {
                return this.cart.reduce((total, item) => total + item.subtotal, 0);
            },

            get nominalBayar() {
                return parseFloat(this.amountPaid) || 0;
            },

            get isLunas() {
                return this.nominalBayar >= this.grandTotal && this.grandTotal > 0;
            },

            get isDP() {
                return this.nominalBayar > 0 && this.nominalBayar < this.grandTotal;
            },

            get kembalian() {
                return this.isLunas ? this.nominalBayar - this.grandTotal : 0;
            },

            get sisaHutang() {
                return this.isDP ? this.grandTotal - this.nominalBayar : 0;
            },

            // Aksi interaktif
            addToCart() {
                if (!this.selectedProduct || !this.qty) return;
                
                const product = this.availableProducts.find(p => p.id == this.selectedProduct);
                const quantity = parseInt(this.qty);
                
                const existingIndex = this.cart.findIndex(item => item.product_id == product.id);
                
                if (existingIndex !== -1) {
                    this.cart[existingIndex].qty += quantity;
                    this.cart[existingIndex].subtotal = this.cart[existingIndex].qty * this.cart[existingIndex].price;
                } else {
                    this.cart.push({
                        product_id: product.id,
                        name: product.nama_kopi,
                        price: product.harga_jual,
                        qty: quantity,
                        subtotal: product.harga_jual * quantity
                    });
                }
                
                this.selectedProduct = '';
                this.qty = '';
            },

            removeFromCart(index) {
                this.cart.splice(index, 1);
            },

            // Fungsi submit ke database Laravel
            async submitTransaction() {
                if (this.cart.length === 0 || this.nominalBayar <= 0) return;
                
                this.loading = true;
                
                const payload = {
                    customer_name: this.customerName,
                    items: this.cart,
                    total_amount: this.grandTotal,
                    amount_paid: this.nominalBayar,
                    payment_status: this.isLunas ? 'LUNAS' : 'DP'
                };

                try {
                    const res = await fetch('/sales', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    });

                    if (res.ok) {
                        window.location.href = '/sales'; // Kembali ke halaman index setelah sukses
                    } else {
                        alert('Gagal memproses transaksi. Silakan coba lagi.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan jaringan.');
                } finally {
                    this.loading = false;
                }
            },

            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }
        }));
    });
</script>
@endpush
@endsection