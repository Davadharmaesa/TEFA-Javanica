@extends('layouts.app')

@section('title', 'Modul Penjualan - TEFA Javanica')

@section('content')
<div x-data="{ activeTab: '{{ request('tab') === 'eksternal' || request('tab') === 'external' ? 'external' : 'internal' }}', paymentModal: false, selectedOrder: {}, paymentUrl: '', openPayment(order) { this.selectedOrder = order; this.paymentUrl = '{{ url('/sales') }}/' + order.id + '/payment'; this.paymentModal = true; } }" class="space-y-6 pb-12">

    @if(session('success'))
        <div class="p-4 bg-secondary/10 border border-secondary/20 text-secondary rounded-xl font-bold flex items-center gap-2 shadow-sm animate-fade-in">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-700 rounded-xl font-bold shadow-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-card p-6 rounded-2xl shadow-sm border border-border/40 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-24 h-24 bg-primary/5 rounded-bl-full pointer-events-none group-hover:scale-110 transition-transform duration-500"></div>
        <div>
            <h1 class="text-2xl font-heading font-bold text-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                Modul Penjualan
            </h1>
            <p class="text-sm text-muted-foreground mt-1">Manajemen pemesanan (Pre-Order) Internal & Eksternal.</p>
        </div>
        <div class="flex gap-2">
            <a :href="'{{ route('sales.create') }}?tab=' + activeTab" class="bg-primary text-primary-foreground hover:bg-primary/90 px-5 py-2.5 rounded-xl text-sm font-bold shadow-md transition-all flex items-center gap-2 hover:scale-105 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Buat Pre-Order
            </a>
        </div>
    </div>

    <div class="flex overflow-x-auto hide-scrollbar gap-2 p-1 bg-muted/30 rounded-xl border border-border/50 max-w-xl">
        <button 
            @click="activeTab = 'internal'" 
            class="flex-1 min-w-0 md:min-w-[150px] py-2.5 px-2 md:py-3 md:px-4 rounded-lg font-bold text-xs md:text-sm transition-all duration-300 flex justify-center items-center gap-2"
            :class="activeTab === 'internal' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span class="truncate">Internal (Kampus)</span>
            <span class="ml-1 inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-[9px] font-black shrink-0"
                :class="activeTab === 'internal' ? 'bg-primary/10 text-primary' : 'bg-muted-foreground/20 text-muted-foreground'">
                {{ $internalOrders->total() }}
            </span>
        </button>
        <button 
            @click="activeTab = 'external'" 
            class="flex-1 min-w-0 md:min-w-[150px] py-2.5 px-2 md:py-3 md:px-4 rounded-lg font-bold text-xs md:text-sm transition-all duration-300 flex justify-center items-center gap-2"
            :class="activeTab === 'external' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span class="truncate">Eksternal (Publik)</span>
            <span class="ml-1 inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-[9px] font-black shrink-0"
                :class="activeTab === 'external' ? 'bg-primary/10 text-primary' : 'bg-muted-foreground/20 text-muted-foreground'">
                {{ $externalOrders->total() }}
            </span>
        </button>
    </div>

    <div x-show="activeTab === 'internal'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border/50 bg-primary/5 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-heading font-bold text-primary">Daftar Pre-Order Internal</h2>
                <p class="text-xs text-muted-foreground mt-1">Pesanan dari mahasiswa, dosen, atau staf kampus.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left border-collapse min-w-[950px]">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4 pl-6 whitespace-nowrap w-[8%]">ID</th>
                        <th class="p-4 min-w-[110px] whitespace-nowrap w-[12%]">Pelanggan</th>
                        <th class="p-4 min-w-[280px] whitespace-nowrap w-[30%]">Produk & Jumlah</th>
                        <th class="p-4 text-center whitespace-nowrap w-[10%]">Total</th>
                        <th class="p-4 text-center whitespace-nowrap w-[10%]">Target Selesai</th>
                        <th class="p-4 text-center whitespace-nowrap w-[10%]">Status Bayar</th>
                        <th class="p-4 text-center whitespace-nowrap w-[10%]">Status Kirim</th>
                        <th class="p-4 text-center whitespace-nowrap w-[10%]">Status Ambil</th>
                        <th class="p-4 text-center pr-6 whitespace-nowrap w-[10%]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    @forelse($internalOrders ?? [] as $order)
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4 pl-6 font-mono text-sm font-bold text-muted-foreground whitespace-nowrap">{{ $order['id'] }}</td>
                            <td class="p-4 font-bold text-primary min-w-[110px] whitespace-nowrap">{{ $order['customer_name'] }}</td>
                            <td class="p-4 min-w-[280px]">
                                <div class="font-bold text-foreground">{{ $order['item'] }}</div>
                                <div class="text-xs text-muted-foreground mt-0.5 font-mono font-medium">
                                    @if(isset($order['qty_pcs']) && $order['qty_pcs'] > 0)
                                        {{ number_format($order['qty_pcs']) }} Pcs ({{ number_format($order['qty']) }} Gram)
                                    @else
                                        {{ number_format($order['qty']) }} Gram
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                <div class="font-mono font-black text-primary">Rp {{ number_format($order['total_price'], 0, ',', '.') }}</div>
                                <div class="mt-1 text-[10px] font-mono font-bold text-muted-foreground">Dibayar: Rp {{ number_format($order['paid_amount'] ?? 0, 0, ',', '.') }}</div>
                                <div class="text-[10px] font-mono font-bold text-red-600">Sisa: Rp {{ number_format($order['remaining_amount'] ?? $order['total_price'], 0, ',', '.') }}</div>
                            </td>
                            <td class="p-4 text-center text-sm font-mono font-medium text-muted-foreground whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($order['target_date'])->format('d M Y') }}
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                @if(strtolower($order['payment_status']) == 'lunas')
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-secondary/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-secondary">Lunas</span>
                                @elseif(str_contains(strtolower($order['payment_status']), 'dp'))
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-accent/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-accent">{{ $order['payment_status'] }}</span>
                                @else
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-red-500/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-red-600">{{ $order['payment_status'] }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                @if($order['status'] == 'Menunggu Konfirmasi')
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-muted/50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-muted-foreground border border-border">{{ $order['status'] }}</span>
                                @elseif($order['status'] == 'Diproses')
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-blue-500/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-blue-600">{{ $order['status'] }}</span>
                                @elseif($order['status'] == 'Siap Diambil')
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-primary/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-primary">{{ $order['status'] }}</span>
                                @else
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-secondary/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-secondary">{{ $order['status'] }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                @if($order['pickup_status'] == 'Belum Diambil')
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-red-500/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-red-600">Belum Diambil</span>
                                @else
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-green-500/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-green-600">Sudah Diambil</span>
                                @endif
                            </td>
                            <td class="p-4 text-center pr-6 whitespace-nowrap">
                                <div class="flex justify-center gap-1">
                                    @if(($order['remaining_amount'] ?? 0) > 0)
                                        <button type="button" @click="openPayment({ id: {{ $order['raw_id'] }}, customer: @js($order['customer_name']), total: {{ $order['total_price'] }}, paid: {{ $order['paid_amount'] ?? 0 }}, remaining: {{ $order['remaining_amount'] ?? 0 }}, deadline: @js(($order['payment_deadline'] ?? null)?->format('Y-m-d')) })" class="p-1.5 text-[#526B42] hover:text-[#3d5130] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Tambah Pembayaran">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 12h8"></path><path d="M12 8v8"></path></svg>
                                        </button>
                                    @endif
                                    @if(strtolower($order['payment_status']) !== 'lunas' || (Auth::check() && Auth::user()->role === 'PIC'))
                                        <a href="{{ route('sales.edit', $order['raw_id']) }}" class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Edit Pesanan">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                    @endif
                                    <form action="{{ route('sales.destroy', $order['raw_id']) }}" method="POST" class="inline" onsubmit="return confirm('Batalkan pesanan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus Pesanan">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-muted-foreground font-medium italic">Belum ada pesanan pre-order internal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Mobile Card List View -->
        <div class="grid grid-cols-1 gap-4 p-4 md:hidden">
            @forelse($internalOrders ?? [] as $order)
                <div class="bg-background border border-border/50 rounded-xl p-4 space-y-3 shadow-sm relative group">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[10px] font-mono text-muted-foreground block">#{{ $order['id'] }}</span>
                            <h4 class="font-bold text-primary text-sm mt-0.5">{{ $order['customer_name'] }}</h4>
                        </div>
                        @if($order['status'] == 'Menunggu Konfirmasi')
                            <span class="px-2 py-0.5 bg-muted/50 text-muted-foreground border border-border text-[9px] font-black rounded-full uppercase tracking-wider">{{ $order['status'] }}</span>
                        @elseif($order['status'] == 'Diproses')
                            <span class="px-2 py-0.5 bg-blue-500/10 text-blue-600 text-[9px] font-black rounded-full uppercase tracking-wider">{{ $order['status'] }}</span>
                        @elseif($order['status'] == 'Siap Diambil')
                            <span class="px-2 py-0.5 bg-primary/10 text-primary text-[9px] font-black rounded-full uppercase tracking-wider">{{ $order['status'] }}</span>
                        @else
                            <span class="px-2 py-0.5 bg-secondary/10 text-secondary text-[9px] font-black rounded-full uppercase tracking-wider">{{ $order['status'] }}</span>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 text-xs border-y border-border/20 py-2">
                        <div class="col-span-2">
                            <span class="text-[10px] text-muted-foreground block">Item & Qty</span>
                            <span class="font-bold text-foreground">{{ $order['item'] }}</span>
                            <span class="font-mono text-muted-foreground text-[10px] block">
                                @if(isset($order['qty_pcs']) && $order['qty_pcs'] > 0)
                                    {{ number_format($order['qty_pcs']) }} Pcs ({{ number_format($order['qty']) }} Gram)
                                @else
                                    {{ number_format($order['qty']) }} Gram
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-muted-foreground block">Total Harga</span>
                            <span class="font-mono font-bold text-primary">Rp {{ number_format($order['total_price'], 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-muted-foreground block">Target Selesai</span>
                            <span class="font-mono font-medium text-muted-foreground">{{ \Carbon\Carbon::parse($order['target_date'])->format('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-muted-foreground block">Status Bayar</span>
                            @if(strtolower($order['payment_status']) == 'lunas')
                                <span class="inline-flex rounded-full bg-secondary/10 px-2 py-0.5 text-[9px] font-black uppercase text-secondary">Lunas</span>
                            @elseif(str_contains(strtolower($order['payment_status']), 'dp'))
                                <span class="inline-flex rounded-full bg-accent/10 px-2 py-0.5 text-[9px] font-black uppercase text-accent">{{ $order['payment_status'] }}</span>
                            @else
                                <span class="inline-flex rounded-full bg-red-500/10 px-2 py-0.5 text-[9px] font-black uppercase text-red-600">{{ $order['payment_status'] }}</span>
                            @endif
                        </div>
                        @if(isset($order['pickup_status']))
                        <div>
                            <span class="text-[10px] text-muted-foreground block">Status Pengambilan</span>
                            @if($order['pickup_status'] == 'Belum Diambil')
                                <span class="inline-flex rounded-full bg-red-500/10 px-2 py-0.5 text-[9px] font-black uppercase text-red-600">Belum Diambil</span>
                            @else
                                <span class="inline-flex rounded-full bg-green-500/10 px-2 py-0.5 text-[9px] font-black uppercase text-green-600">Sudah Diambil</span>
                            @endif
                        </div>
                        @endif
                    </div>

                    <div class="flex justify-end gap-2 pt-1">
                        @if(($order['remaining_amount'] ?? 0) > 0)
                            <button type="button" @click="openPayment({ id: {{ $order['raw_id'] }}, customer: @js($order['customer_name']), total: {{ $order['total_price'] }}, paid: {{ $order['paid_amount'] ?? 0 }}, remaining: {{ $order['remaining_amount'] ?? 0 }}, deadline: @js(($order['payment_deadline'] ?? null)?->format('Y-m-d')) })" class="flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-[#526B42] bg-[#526B42]/5 hover:bg-[#526B42]/10 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 12h8"></path><path d="M12 8v8"></path></svg>
                                Bayar
                            </button>
                        @endif
                        <a href="{{ route('sales.edit', $order['raw_id']) }}" class="flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-[#8A6B4E] bg-[#8A6B4E]/5 hover:bg-[#8A6B4E]/10 rounded-lg transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            Edit
                        </a>
                        <form action="{{ route('sales.destroy', $order['raw_id']) }}" method="POST" class="inline" onsubmit="return confirm('Batalkan pesanan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-[#8B3A3A] bg-[#8B3A3A]/5 hover:bg-[#8B3A3A]/10 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                Batal
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="p-6 text-center text-muted-foreground font-medium italic text-xs">Belum ada pesanan pre-order internal.</p>
            @endforelse
        </div>

        <!-- Pagination Controls -->
        <div class="px-6 py-4 border-t border-border/30 bg-muted/10 flex flex-col sm:flex-row justify-between items-center gap-4">
            <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider">
                Menampilkan {{ $internalOrders->firstItem() ?? 0 }} - {{ $internalOrders->lastItem() ?? 0 }} dari {{ $internalOrders->total() }} data
            </span>
            <div class="flex items-center gap-1.5">
                @if ($internalOrders->onFirstPage())
                    <span class="px-3 py-1.5 text-xs font-bold text-muted-foreground bg-muted/50 rounded-lg cursor-not-allowed border border-border/40 select-none">Sebelumnya</span>
                @else
                    <a href="{{ $internalOrders->previousPageUrl() }}" class="px-3 py-1.5 text-xs font-bold text-primary hover:text-white hover:bg-primary bg-card rounded-lg border border-border/60 transition-all shadow-sm">Sebelumnya</a>
                @endif

                <span class="text-xs font-mono font-black text-primary px-3">Halaman {{ $internalOrders->currentPage() }} dari {{ $internalOrders->lastPage() }}</span>

                @if ($internalOrders->hasMorePages())
                    <a href="{{ $internalOrders->nextPageUrl() }}" class="px-3 py-1.5 text-xs font-bold text-primary hover:text-white hover:bg-primary bg-card rounded-lg border border-border/60 transition-all shadow-sm">Selanjutnya</a>
                @else
                    <span class="px-3 py-1.5 text-xs font-bold text-muted-foreground bg-muted/50 rounded-lg cursor-not-allowed border border-border/40 select-none">Selanjutnya</span>
                @endif
            </div>
        </div>
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4 pl-6 whitespace-nowrap w-[8%]">ID</th>
                        <th class="p-4 min-w-[110px] whitespace-nowrap w-[12%]">Pelanggan</th>
                        <th class="p-4 min-w-[280px] whitespace-nowrap w-[30%]">Produk & Jumlah</th>
                        <th class="p-4 text-center whitespace-nowrap w-[10%]">Total</th>
                        <th class="p-4 text-center whitespace-nowrap w-[10%]">Target Selesai</th>
                        <th class="p-4 text-center whitespace-nowrap w-[10%]">Status Bayar</th>
                        <th class="p-4 text-center whitespace-nowrap w-[10%]">Status Kirim</th>
                        <th class="p-4 text-center whitespace-nowrap w-[10%]">Status Ambil</th>
                        <th class="p-4 text-center pr-6 whitespace-nowrap w-[10%]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    @forelse($externalOrders ?? [] as $order)
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4 pl-6 font-mono text-sm font-bold text-muted-foreground whitespace-nowrap">{{ $order['id'] }}</td>
                            <td class="p-4 font-bold text-primary min-w-[110px] whitespace-nowrap">{{ $order['customer_name'] }}</td>
                            <td class="p-4 min-w-[280px]">
                                <div class="font-bold text-foreground">{{ $order['item'] }}</div>
                                <div class="text-xs text-muted-foreground mt-0.5 font-mono font-medium">
                                    @if(isset($order['qty_pcs']) && $order['qty_pcs'] > 0)
                                        {{ number_format($order['qty_pcs']) }} Pcs ({{ number_format($order['qty']) }} Gram)
                                    @else
                                        {{ number_format($order['qty']) }} Gram
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                <div class="font-mono font-black text-primary">Rp {{ number_format($order['total_price'], 0, ',', '.') }}</div>
                                <div class="mt-1 text-[10px] font-mono font-bold text-muted-foreground">Dibayar: Rp {{ number_format($order['paid_amount'] ?? 0, 0, ',', '.') }}</div>
                                <div class="text-[10px] font-mono font-bold text-red-600">Sisa: Rp {{ number_format($order['remaining_amount'] ?? $order['total_price'], 0, ',', '.') }}</div>
                            </td>
                            <td class="p-4 text-center text-sm font-mono font-medium text-muted-foreground whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($order['target_date'])->format('d M Y') }}
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                @if(strtolower($order['payment_status']) == 'lunas')
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-secondary/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-secondary">Lunas</span>
                                @elseif(str_contains(strtolower($order['payment_status']), 'dp'))
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-accent/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-accent">{{ $order['payment_status'] }}</span>
                                @else
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-red-500/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-red-600">{{ $order['payment_status'] }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                @if($order['status'] == 'Menunggu Konfirmasi')
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-muted/50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-muted-foreground border border-border">{{ $order['status'] }}</span>
                                @elseif($order['status'] == 'Diproses')
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-blue-500/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-blue-600">{{ $order['status'] }}</span>
                                @elseif($order['status'] == 'Siap Diambil')
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-primary/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-primary">{{ $order['status'] }}</span>
                                @else
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-secondary/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-secondary">{{ $order['status'] }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                @if($order['pickup_status'] == 'Belum Diambil')
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-red-500/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-red-600">Belum Diambil</span>
                                @else
                                    <span class="inline-flex whitespace-nowrap items-center justify-center rounded-full bg-green-500/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-green-600">Sudah Diambil</span>
                                @endif
                            </td>
                            <td class="p-4 text-center pr-6 whitespace-nowrap">
                                <div class="flex justify-center gap-1">
                                    @if(($order['remaining_amount'] ?? 0) > 0)
                                        <button type="button" @click="openPayment({ id: {{ $order['raw_id'] }}, customer: @js($order['customer_name']), total: {{ $order['total_price'] }}, paid: {{ $order['paid_amount'] ?? 0 }}, remaining: {{ $order['remaining_amount'] ?? 0 }}, deadline: @js(($order['payment_deadline'] ?? null)?->format('Y-m-d')) })" class="p-1.5 text-[#526B42] hover:text-[#3d5130] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Tambah Pembayaran">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 12h8"></path><path d="M12 8v8"></path></svg>
                                        </button>
                                    @endif
                                    @if(strtolower($order['payment_status']) !== 'lunas' || (Auth::check() && Auth::user()->role === 'PIC'))
                                        <a href="{{ route('sales.edit', $order['raw_id']) }}" class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Edit Pesanan">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                    @endif
                                    <form action="{{ route('sales.destroy', $order['raw_id']) }}" method="POST" class="inline" onsubmit="return confirm('Batalkan pesanan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus Pesanan">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-muted-foreground font-medium italic">Belum ada pesanan pre-order eksternal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Mobile Card List View -->
        <div class="grid grid-cols-1 gap-4 p-4 md:hidden">
            @forelse($externalOrders ?? [] as $order)
                <div class="bg-background border border-border/50 rounded-xl p-4 space-y-3 shadow-sm relative group">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[10px] font-mono text-muted-foreground block">#{{ $order['id'] }}</span>
                            <h4 class="font-bold text-primary text-sm mt-0.5">{{ $order['customer_name'] }}</h4>
                        </div>
                        @if($order['status'] == 'Menunggu Konfirmasi')
                            <span class="px-2 py-0.5 bg-muted/50 text-muted-foreground border border-border text-[9px] font-black rounded-full uppercase tracking-wider">{{ $order['status'] }}</span>
                        @elseif($order['status'] == 'Diproses')
                            <span class="px-2 py-0.5 bg-blue-500/10 text-blue-600 text-[9px] font-black rounded-full uppercase tracking-wider">{{ $order['status'] }}</span>
                        @elseif($order['status'] == 'Siap Diambil')
                            <span class="px-2 py-0.5 bg-primary/10 text-primary text-[9px] font-black rounded-full uppercase tracking-wider">{{ $order['status'] }}</span>
                        @else
                            <span class="px-2 py-0.5 bg-secondary/10 text-secondary text-[9px] font-black rounded-full uppercase tracking-wider">{{ $order['status'] }}</span>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 text-xs border-y border-border/20 py-2">
                        <div class="col-span-2">
                            <span class="text-[10px] text-muted-foreground block">Item & Qty</span>
                            <span class="font-bold text-foreground">{{ $order['item'] }}</span>
                            <span class="font-mono text-muted-foreground text-[10px] block">
                                @if(isset($order['qty_pcs']) && $order['qty_pcs'] > 0)
                                    {{ number_format($order['qty_pcs']) }} Pcs ({{ number_format($order['qty']) }} Gram)
                                @else
                                    {{ number_format($order['qty']) }} Gram
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-muted-foreground block">Total Harga</span>
                            <span class="font-mono font-bold text-primary">Rp {{ number_format($order['total_price'], 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-muted-foreground block">Target Selesai</span>
                            <span class="font-mono font-medium text-muted-foreground">{{ \Carbon\Carbon::parse($order['target_date'])->format('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-muted-foreground block">Status Bayar</span>
                            @if(strtolower($order['payment_status']) == 'lunas')
                                <span class="inline-flex rounded-full bg-secondary/10 px-2 py-0.5 text-[9px] font-black uppercase text-secondary">Lunas</span>
                            @elseif(str_contains(strtolower($order['payment_status']), 'dp'))
                                <span class="inline-flex rounded-full bg-accent/10 px-2 py-0.5 text-[9px] font-black uppercase text-accent">{{ $order['payment_status'] }}</span>
                            @else
                                <span class="inline-flex rounded-full bg-red-500/10 px-2 py-0.5 text-[9px] font-black uppercase text-red-600">{{ $order['payment_status'] }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-1">
                        @if(($order['remaining_amount'] ?? 0) > 0)
                            <button type="button" @click="openPayment({ id: {{ $order['raw_id'] }}, customer: @js($order['customer_name']), total: {{ $order['total_price'] }}, paid: {{ $order['paid_amount'] ?? 0 }}, remaining: {{ $order['remaining_amount'] ?? 0 }}, deadline: @js(($order['payment_deadline'] ?? null)?->format('Y-m-d')) })" class="flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-[#526B42] bg-[#526B42]/5 hover:bg-[#526B42]/10 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 12h8"></path><path d="M12 8v8"></path></svg>
                                Bayar
                            </button>
                        @endif
                        @if(strtolower($order['payment_status']) !== 'lunas' || (Auth::check() && Auth::user()->role === 'PIC'))
                            <a href="{{ route('sales.edit', $order['raw_id']) }}" class="flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-[#8A6B4E] bg-[#8A6B4E]/5 hover:bg-[#8A6B4E]/10 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                Edit
                            </a>
                        @endif
                        <form action="{{ route('sales.destroy', $order['raw_id']) }}" method="POST" class="inline" onsubmit="return confirm('Batalkan pesanan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-[#8B3A3A] bg-[#8B3A3A]/5 hover:bg-[#8B3A3A]/10 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                Batal
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="p-6 text-center text-muted-foreground font-medium italic text-xs">Belum ada pesanan pre-order eksternal.</p>
            @endforelse
        </div>


        <!-- Pagination Controls -->
        <div class="px-6 py-4 border-t border-border/30 bg-muted/10 flex flex-col sm:flex-row justify-between items-center gap-4">
            <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider">
                Menampilkan {{ $externalOrders->firstItem() ?? 0 }} - {{ $externalOrders->lastItem() ?? 0 }} dari {{ $externalOrders->total() }} data
            </span>
            <div class="flex items-center gap-1.5">
                @if ($externalOrders->onFirstPage())
                    <span class="px-3 py-1.5 text-xs font-bold text-muted-foreground bg-muted/50 rounded-lg cursor-not-allowed border border-border/40 select-none">Sebelumnya</span>
                @else
                    <a href="{{ $externalOrders->previousPageUrl() }}" class="px-3 py-1.5 text-xs font-bold text-primary hover:text-white hover:bg-primary bg-card rounded-lg border border-border/60 transition-all shadow-sm">Sebelumnya</a>
                @endif

                <span class="text-xs font-mono font-black text-primary px-3">Halaman {{ $externalOrders->currentPage() }} dari {{ $externalOrders->lastPage() }}</span>

                @if ($externalOrders->hasMorePages())
                    <a href="{{ $externalOrders->nextPageUrl() }}" class="px-3 py-1.5 text-xs font-bold text-primary hover:text-white hover:bg-primary bg-card rounded-lg border border-border/60 transition-all shadow-sm">Selanjutnya</a>
                @else
                    <span class="px-3 py-1.5 text-xs font-bold text-muted-foreground bg-muted/50 rounded-lg cursor-not-allowed border border-border/40 select-none">Selanjutnya</span>
                @endif
            </div>
        </div>
    </div>

    <div x-show="paymentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="paymentModal = false">
        <div class="w-full max-w-md rounded-2xl bg-card border border-border shadow-2xl p-6" @click.outside="paymentModal = false">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-lg font-heading font-bold text-primary">Tambah Pembayaran</h2>
                    <p class="text-xs text-muted-foreground mt-1" x-text="selectedOrder.customer"></p>
                </div>
                <button type="button" @click="paymentModal = false" class="text-muted-foreground hover:text-foreground" aria-label="Tutup">&times;</button>
            </div>
            <div class="grid grid-cols-2 gap-3 rounded-xl bg-muted/30 p-4 mb-5 text-sm">
                <div><span class="block text-xs text-muted-foreground">Total Transaksi</span><strong x-text="'Rp ' + (selectedOrder.total || 0).toLocaleString('id-ID')"></strong></div>
                <div><span class="block text-xs text-muted-foreground">Sudah Dibayar</span><strong x-text="'Rp ' + (selectedOrder.paid || 0).toLocaleString('id-ID')"></strong></div>
                <div><span class="block text-xs text-muted-foreground">Sisa Pembayaran</span><strong class="text-red-600" x-text="'Rp ' + (selectedOrder.remaining || 0).toLocaleString('id-ID')"></strong></div>
                <div><span class="block text-xs text-muted-foreground">Batas Waktu</span><strong x-text="selectedOrder.deadline || 'Tidak ditentukan'"></strong></div>
            </div>
            <form :action="paymentUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="payment_nominal" class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1.5">Nominal Pembayaran Berikutnya (Rp)</label>
                    <input id="payment_nominal" name="nominal" type="number" min="1" :max="selectedOrder.remaining" required class="w-full rounded-xl border border-border bg-background px-4 py-3 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-primary/40">
                </div>
                <div>
                    <label for="payment_date" class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1.5">Tanggal Pembayaran</label>
                    <input id="payment_date" name="tanggal_bayar" type="date" value="{{ now()->toDateString() }}" required class="w-full rounded-xl border border-border bg-background px-4 py-3 font-bold focus:outline-none focus:ring-2 focus:ring-primary/40">
                </div>
                <button type="submit" class="w-full rounded-xl bg-primary px-4 py-3 font-black text-primary-foreground hover:bg-primary/90">Simpan Pembayaran</button>
            </form>
        </div>

    </div>
</div>
@endsection