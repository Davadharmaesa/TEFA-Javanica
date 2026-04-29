@extends('layouts.app')

@section('title', 'Modul Penjualan - TEFA Javanica')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex flex-col gap-2">
        <h1 class="text-3xl font-heading font-bold text-primary">Modul Penjualan</h1>
        <p class="text-muted-foreground font-sans">
            Manajemen pemesanan (Pre-Order) Internal & Eksternal.
        </p>
    </div>
    <div>
        <a href="{{ route('sales.create') }}" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-bold uppercase tracking-widest text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Buat Pre-Order
        </a>
    </div>
</div>

<div x-data="{ activeTab: 'internal' }" class="space-y-6">
    <!-- Tabs Navigation -->
    <div class="flex space-x-1 rounded-lg bg-muted p-1 w-full max-w-md">
        <button 
            @click="activeTab = 'internal'" 
            :class="activeTab === 'internal' ? 'bg-card text-primary shadow' : 'text-muted-foreground hover:bg-muted/80'"
            class="flex-1 inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1.5 text-xs font-bold uppercase tracking-widest transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        >
            Internal (Kampus)
            <span class="ml-2 inline-flex items-center justify-center rounded-full bg-primary/20 px-2.5 py-0.5 text-[10px] font-bold text-primary">
                {{ count($internalOrders) }}
            </span>
        </button>
        <button 
            @click="activeTab = 'external'" 
            :class="activeTab === 'external' ? 'bg-card text-secondary shadow' : 'text-muted-foreground hover:bg-muted/80'"
            class="flex-1 inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1.5 text-xs font-bold uppercase tracking-widest transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        >
            Eksternal (Publik)
            <span class="ml-2 inline-flex items-center justify-center rounded-full bg-secondary/20 px-2.5 py-0.5 text-[10px] font-bold text-secondary">
                {{ count($externalOrders) }}
            </span>
        </button>
    </div>

    <!-- Tab Content: Internal -->
    <div x-show="activeTab === 'internal'" x-transition.opacity.duration.300ms class="bg-card border border-border rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border text-xs uppercase tracking-wider text-primary">
                        <th class="p-4">ID Pesanan</th>
                        <th class="p-4">Pelanggan</th>
                        <th class="p-4">Item & Qty</th>
                        <th class="p-4 text-right">Total Harga</th>
                        <th class="p-4">Target Selesai</th>
                        <th class="p-4">Status Bayar</th>
                        <th class="p-4">Status Pesanan</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                        @forelse($internalOrders as $order)
                        <tr class="transition-colors hover:bg-muted/50 text-primary">
                            <td class="p-4 font-mono text-xs font-medium">{{ $order['id'] }}</td>
                            <td class="p-4 font-medium">{{ $order['customer_name'] }}</td>
                            <td class="p-4">
                                <div class="font-medium">{{ $order['item'] }}</div>
                                <div class="text-xs text-primary/80 mt-0.5">{{ $order['qty'] }} pcs</div>
                            </td>
                            <td class="p-4 text-right font-medium">Rp {{ number_format($order['total_price'], 0, ',', '.') }}</td>
                            <td class="p-4">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                    {{ \Carbon\Carbon::parse($order['target_date'])->format('d M Y') }}
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                @if($order['payment_status'] == 'Lunas')
                                    <span class="inline-flex items-center justify-center rounded-full border border-secondary/50 bg-secondary/10 px-2.5 py-0.5 text-xs font-semibold text-secondary">Lunas</span>
                                @elseif(str_contains($order['payment_status'], 'DP'))
                                    <span class="inline-flex items-center justify-center rounded-full border border-accent/50 bg-accent/10 px-2.5 py-0.5 text-xs font-semibold text-accent">{{ $order['payment_status'] }}</span>
                                @else
                                    <span class="inline-flex items-center justify-center rounded-full border border-red-500/50 bg-red-500/10 px-2.5 py-0.5 text-xs font-semibold text-red-600">{{ $order['payment_status'] }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($order['status'] == 'Menunggu Konfirmasi')
                                    <span class="inline-flex items-center justify-center rounded-full border border-muted-foreground/30 bg-muted px-2.5 py-0.5 text-xs font-semibold text-muted-foreground">{{ $order['status'] }}</span>
                                @elseif($order['status'] == 'Diproses')
                                    <span class="inline-flex items-center justify-center rounded-full border border-blue-500/50 bg-blue-500/10 px-2.5 py-0.5 text-xs font-semibold text-blue-600">{{ $order['status'] }}</span>
                                @elseif($order['status'] == 'Siap Diambil')
                                    <span class="inline-flex items-center justify-center rounded-full border border-primary/50 bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">{{ $order['status'] }}</span>
                                @else
                                    <span class="inline-flex items-center justify-center rounded-full border border-secondary/50 bg-secondary/10 px-2.5 py-0.5 text-xs font-semibold text-secondary">{{ $order['status'] }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <button class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors hover:bg-muted hover:text-foreground h-8 w-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-muted-foreground">Belum ada pesanan pre-order internal.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab Content: External -->
    <div x-show="activeTab === 'external'" x-transition.opacity.duration.300ms class="bg-card border border-border rounded-xl shadow-sm overflow-hidden" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border text-xs uppercase tracking-wider text-foreground">
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
                <tbody class="divide-y divide-border">
                        @forelse($externalOrders as $order)
                        <tr class="transition-colors hover:bg-muted/50">
                            <td class="p-4 font-mono text-xs font-medium">{{ $order['id'] }}</td>
                            <td class="p-4 font-medium text-primary">{{ $order['customer_name'] }}</td>
                            <td class="p-4">
                                <div class="text-foreground font-medium">{{ $order['item'] }}</div>
                                <div class="text-xs text-muted-foreground mt-0.5">{{ $order['qty'] }} pcs</div>
                            </td>
                            <td class="p-4 text-right font-medium">Rp {{ number_format($order['total_price'], 0, ',', '.') }}</td>
                            <td class="p-4">
                                <div class="flex items-center text-muted-foreground">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                    {{ \Carbon\Carbon::parse($order['target_date'])->format('d M Y') }}
                                </div>
                            </td>
                            <td class="p-4">
                                @if($order['payment_status'] == 'Lunas')
                                    <span class="inline-flex items-center rounded-full border border-secondary/50 bg-secondary/10 px-2.5 py-0.5 text-xs font-semibold text-secondary">Lunas</span>
                                @elseif(str_contains($order['payment_status'], 'DP'))
                                    <span class="inline-flex items-center rounded-full border border-accent/50 bg-accent/10 px-2.5 py-0.5 text-xs font-semibold text-accent">{{ $order['payment_status'] }}</span>
                                @else
                                    <span class="inline-flex items-center rounded-full border border-red-500/50 bg-red-500/10 px-2.5 py-0.5 text-xs font-semibold text-red-600">{{ $order['payment_status'] }}</span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($order['status'] == 'Menunggu Konfirmasi')
                                    <span class="inline-flex items-center rounded-full border border-muted-foreground/30 bg-muted px-2.5 py-0.5 text-xs font-semibold text-muted-foreground">{{ $order['status'] }}</span>
                                @elseif($order['status'] == 'Diproses')
                                    <span class="inline-flex items-center rounded-full border border-blue-500/50 bg-blue-500/10 px-2.5 py-0.5 text-xs font-semibold text-blue-600">{{ $order['status'] }}</span>
                                @elseif($order['status'] == 'Siap Diambil')
                                    <span class="inline-flex items-center rounded-full border border-primary/50 bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">{{ $order['status'] }}</span>
                                @else
                                    <span class="inline-flex items-center rounded-full border border-secondary/50 bg-secondary/10 px-2.5 py-0.5 text-xs font-semibold text-secondary">{{ $order['status'] }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <button class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors hover:bg-muted hover:text-foreground h-8 w-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-muted-foreground">Belum ada pesanan pre-order eksternal.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
