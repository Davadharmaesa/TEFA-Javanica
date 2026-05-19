@extends('layouts.app')

@section('title', 'Modul Penjualan - TEFA Javanica')

@section('content')
<div x-data="{ activeTab: 'internal' }" class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-card p-6 rounded-2xl shadow-sm border border-border/40">
        <div>
            <h1 class="text-2xl font-heading font-bold text-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                Modul Penjualan
            </h1>
            <p class="text-sm text-muted-foreground mt-1">Manajemen pemesanan (Pre-Order) Internal & Eksternal.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('sales.create') }}" class="bg-secondary text-secondary-foreground hover:bg-secondary/90 px-4 py-2.5 rounded-xl text-sm font-bold shadow-md transition-all flex items-center gap-2 hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Buat Pre-Order
            </a>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex overflow-x-auto hide-scrollbar gap-2 p-1 bg-muted/30 rounded-xl border border-border/50 max-w-xl">
        <button 
            @click="activeTab = 'internal'" 
            class="flex-1 min-w-[150px] py-3 px-4 rounded-lg font-bold text-sm transition-all duration-300 flex justify-center items-center gap-2"
            :class="activeTab === 'internal' ? 'bg-card text-primary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Internal (Kampus)
            <span class="ml-2 inline-flex items-center justify-center rounded-full px-2.5 py-0.5 text-[10px] font-black"
                :class="activeTab === 'internal' ? 'bg-primary/20 text-primary' : 'bg-muted-foreground/20 text-muted-foreground'">
                {{ count($internalOrders) }}
            </span>
        </button>
        <button 
            @click="activeTab = 'external'" 
            class="flex-1 min-w-[150px] py-3 px-4 rounded-lg font-bold text-sm transition-all duration-300 flex justify-center items-center gap-2"
            :class="activeTab === 'external' ? 'bg-card text-secondary shadow-sm ring-1 ring-border/50' : 'text-muted-foreground hover:bg-muted/50'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Eksternal (Publik)
            <span class="ml-2 inline-flex items-center justify-center rounded-full px-2.5 py-0.5 text-[10px] font-black"
                :class="activeTab === 'external' ? 'bg-secondary/20 text-secondary' : 'bg-muted-foreground/20 text-muted-foreground'">
                {{ count($externalOrders) }}
            </span>
        </button>
    </div>

    <!-- Tab Content: Internal -->
    <div x-show="activeTab === 'internal'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border text-xs uppercase tracking-wider text-foreground font-bold">
                        <th class="p-4">ID Pesanan</th>
                        <th class="p-4">Pelanggan</th>
                        <th class="p-4">Item & Qty</th>
                        <th class="p-4 text-right">Total Harga</th>
                        <th class="p-4">Target Selesai</th>
                        <th class="p-4 text-center">Status Bayar</th>
                        <th class="p-4 text-center">Status Pesanan</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                        @forelse($internalOrders as $order)
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4 font-mono text-sm font-bold text-primary">{{ $order['id'] }}</td>
                            <td class="p-4 font-bold text-primary">{{ $order['customer_name'] }}</td>
                            <td class="p-4">
                                <div class="font-bold text-primary">{{ $order['item'] }}</div>
                                <div class="text-xs text-muted-foreground mt-0.5 font-mono">{{ $order['qty'] }} pcs</div>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-primary">Rp {{ number_format($order['total_price'], 0, ',', '.') }}</td>
                            <td class="p-4 text-sm font-mono font-bold text-primary">
                                {{ \Carbon\Carbon::parse($order['target_date'])->format('d M Y') }}
                            </td>
                            <td class="p-4 text-center">
                                @if($order['payment_status'] == 'Lunas')
                                    <span class="inline-flex items-center justify-center rounded-full bg-secondary/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-secondary">Lunas</span>
                                @elseif(str_contains($order['payment_status'], 'DP'))
                                    <span class="inline-flex items-center justify-center rounded-full bg-accent/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-accent">{{ $order['payment_status'] }}</span>
                                @else
                                    <span class="inline-flex items-center justify-center rounded-full bg-red-500/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-red-600">{{ $order['payment_status'] }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($order['status'] == 'Menunggu Konfirmasi')
                                    <span class="inline-flex items-center justify-center rounded-full bg-muted-foreground/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $order['status'] }}</span>
                                @elseif($order['status'] == 'Diproses')
                                    <span class="inline-flex items-center justify-center rounded-full bg-blue-500/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-blue-600">{{ $order['status'] }}</span>
                                @elseif($order['status'] == 'Siap Diambil')
                                    <span class="inline-flex items-center justify-center rounded-full bg-primary/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-primary">{{ $order['status'] }}</span>
                                @else
                                    <span class="inline-flex items-center justify-center rounded-full bg-secondary/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-secondary">{{ $order['status'] }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-1">
                                    <button class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Edit Pesanan">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus Pesanan">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-muted-foreground font-medium">Belum ada pesanan pre-order internal.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    <!-- Tab Content: External -->
    <div x-show="activeTab === 'external'" x-transition.opacity.duration.300ms class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-border text-xs uppercase tracking-wider text-foreground font-bold">
                        <th class="p-4">ID Pesanan</th>
                        <th class="p-4">Pelanggan</th>
                        <th class="p-4">Item & Qty</th>
                        <th class="p-4 text-right">Total Harga</th>
                        <th class="p-4">Target Selesai</th>
                        <th class="p-4 text-center">Status Bayar</th>
                        <th class="p-4 text-center">Status Pesanan</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                        @forelse($externalOrders as $order)
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4 font-mono text-sm font-bold text-primary">{{ $order['id'] }}</td>
                            <td class="p-4 font-bold text-primary">{{ $order['customer_name'] }}</td>
                            <td class="p-4">
                                <div class="font-bold text-primary">{{ $order['item'] }}</div>
                                <div class="text-xs text-muted-foreground mt-0.5 font-mono">{{ $order['qty'] }} pcs</div>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-primary">Rp {{ number_format($order['total_price'], 0, ',', '.') }}</td>
                            <td class="p-4 text-sm font-mono font-bold text-primary">
                                {{ \Carbon\Carbon::parse($order['target_date'])->format('d M Y') }}
                            </td>
                            <td class="p-4 text-center">
                                @if($order['payment_status'] == 'Lunas')
                                    <span class="inline-flex items-center justify-center rounded-full bg-secondary/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-secondary">Lunas</span>
                                @elseif(str_contains($order['payment_status'], 'DP'))
                                    <span class="inline-flex items-center justify-center rounded-full bg-accent/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-accent">{{ $order['payment_status'] }}</span>
                                @else
                                    <span class="inline-flex items-center justify-center rounded-full bg-red-500/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-red-600">{{ $order['payment_status'] }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($order['status'] == 'Menunggu Konfirmasi')
                                    <span class="inline-flex items-center justify-center rounded-full bg-muted-foreground/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $order['status'] }}</span>
                                @elseif($order['status'] == 'Diproses')
                                    <span class="inline-flex items-center justify-center rounded-full bg-blue-500/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-blue-600">{{ $order['status'] }}</span>
                                @elseif($order['status'] == 'Siap Diambil')
                                    <span class="inline-flex items-center justify-center rounded-full bg-primary/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-primary">{{ $order['status'] }}</span>
                                @else
                                    <span class="inline-flex items-center justify-center rounded-full bg-secondary/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-secondary">{{ $order['status'] }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-1">
                                    <button class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Edit Pesanan">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus Pesanan">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-muted-foreground font-medium">Belum ada pesanan pre-order eksternal.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
</div>
@endsection
