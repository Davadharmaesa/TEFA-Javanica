@extends('layouts.app')

@section('title', 'Buat Pre-Order Baru - TEFA Javanica')

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-3">
        <a href="{{ route('sales.index') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border bg-transparent text-sm font-medium shadow-sm transition-colors hover:bg-muted focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            <span class="sr-only">Kembali</span>
        </a>
        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-heading font-bold text-primary">Buat Pre-Order</h1>
            <p class="text-muted-foreground font-sans">
                Catat pesanan baru untuk Internal atau Eksternal.
            </p>
        </div>
    </div>
</div>

<div class="max-w-2xl">
    <div class="rounded-xl border border-border bg-card text-card-foreground shadow-sm">
        <div class="p-6">
            <form action="#" method="POST" class="space-y-6">
                <!-- Kategori -->
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Kategori Pesanan</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex cursor-pointer rounded-lg border border-border bg-card p-4 shadow-sm hover:bg-muted/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="kategori" value="internal" class="sr-only" checked>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-foreground">Internal (Kampus)</span>
                                <span class="text-xs text-muted-foreground">Pesanan dari civitas akademika Poliwangi</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute right-4 top-1/2 -translate-y-1/2 text-primary opacity-0 transition-opacity peer-checked:opacity-100"><polyline points="20 6 9 17 4 12"/></svg>
                        </label>
                        <label class="relative flex cursor-pointer rounded-lg border border-border bg-card p-4 shadow-sm hover:bg-muted/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="kategori" value="external" class="sr-only">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-foreground">Eksternal (Publik)</span>
                                <span class="text-xs text-muted-foreground">Pesanan dari luar kampus (Cafe, Umum)</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute right-4 top-1/2 -translate-y-1/2 text-primary opacity-0 transition-opacity peer-checked:opacity-100"><polyline points="20 6 9 17 4 12"/></svg>
                        </label>
                    </div>
                </div>

                <!-- Informasi Pelanggan -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold border-b border-border pb-2">Informasi Pelanggan</h3>
                    <div class="space-y-2">
                        <label for="customer_name" class="text-sm font-medium leading-none">Nama Lengkap / Instansi</label>
                        <input type="text" id="customer_name" name="customer_name" placeholder="Contoh: Bapak Budi (Dosen TI) / Cafe Senja" class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                    </div>
                    <div class="space-y-2">
                        <label for="contact" class="text-sm font-medium leading-none">Kontak (WhatsApp / Email)</label>
                        <input type="text" id="contact" name="contact" placeholder="0812xxxxxx" class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                    </div>
                </div>

                <!-- Detail Pesanan -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold border-b border-border pb-2">Detail Pesanan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2 md:col-span-2">
                            <label for="item" class="text-sm font-medium leading-none">Produk</label>
                            <select id="item" name="item" class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <option value="" disabled selected>Pilih Produk...</option>
                                <option value="Arabica Ijen Roasting Light (200g)">Arabica Ijen Roasting Light (200g)</option>
                                <option value="Arabica Ijen Roasting Medium (200g)">Arabica Ijen Roasting Medium (200g)</option>
                                <option value="Robusta Gombengsari Dark (500g)">Robusta Gombengsari Dark (500g)</option>
                                <option value="Espresso Blend (1kg)">Espresso Blend (1kg)</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="qty" class="text-sm font-medium leading-none">Jumlah (Qty)</label>
                            <input type="number" id="qty" name="qty" min="1" value="1" class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="target_date" class="text-sm font-medium leading-none">Target Selesai (Diambil/Kirim)</label>
                            <input type="date" id="target_date" name="target_date" class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        </div>
                        <div class="space-y-2">
                            <label for="payment_status" class="text-sm font-medium leading-none">Status Pembayaran</label>
                            <select id="payment_status" name="payment_status" class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <option value="belum_bayar">Belum Bayar</option>
                                <option value="dp">DP (Uang Muka)</option>
                                <option value="lunas">Lunas</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end border-t border-border gap-2">
                    <a href="{{ route('sales.index') }}" class="inline-flex h-10 items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                        Batal
                    </a>
                    <button type="button" onclick="alert('Ini hanya tampilan UI simulasi. Integrasi backend akan dilakukan di tahap berikutnya.')" class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                        Simpan Pre-Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
