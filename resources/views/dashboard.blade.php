<x-layout>
    <div class="mb-8">
        <h2 class="text-[#ca8569] text-3xl font-bold tracking-tight mb-1">Ringkasan Beranda</h2>
        <p class="text-gray-500 font-medium font-sm">Selamat datang. Berikut adalah data real-time dari pabrik sangrai.</p>
    </div>

    <!-- Top Metrics Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Card 1 -->
        <div class="bg-white rounded-2xl p-6 border border-[#e6dac3] shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-[#7A4E3B]/80 font-semibold text-sm mb-1">Total Stok Biji Mentah</h3>
                    <div class="flex items-baseline gap-1.5 text-[#B26E4F]">
                        <span class="text-4xl font-extrabold">{{ $total_stok_biji_mentah }}</span>
                        <span class="text-lg font-bold">kg</span>
                    </div>
                </div>
                <div class="p-2.5 bg-[#fcf7f4] rounded-xl border border-[#e6dac3] text-[#ca8569]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>
            <div class="flex items-center text-sm font-semibold text-emerald-700/80 mt-2">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                <span>Real-time <span class="text-gray-400 font-medium">dari rak gudang</span></span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-2xl p-6 border border-[#e6dac3] shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-[#7A4E3B]/80 font-semibold text-sm mb-1">Total Kopi Matang Siap Jual</h3>
                    <div class="flex items-baseline gap-1.5 text-[#B26E4F]">
                        <span class="text-4xl font-extrabold">{{ $total_kopi_matang }}</span>
                        <span class="text-lg font-bold">kg</span>
                    </div>
                </div>
                <div class="p-2.5 bg-[#fcf7f4] rounded-xl border border-[#e6dac3] text-[#ca8569]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
            </div>
            <div class="flex items-center text-sm font-semibold text-emerald-700/80 mt-2">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                <span>Tersedia untuk dijual / ditransfer</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-2xl p-6 border border-[#e6dac3] shadow-sm relative overflow-hidden flex flex-col justify-between">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-[#7A4E3B]/80 font-semibold text-sm mb-1">Penjualan Retail Hari Ini</h3>
                    <div class="flex items-baseline gap-1.5 text-[#B26E4F]">
                        <span class="text-2xl font-extrabold mt-1">Rp</span>
                        <span class="text-4xl font-extrabold">{{ number_format($penjualan_retail, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="p-2.5 bg-[#CA8569] rounded-xl text-white shadow-sm shadow-[#ca8569]/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
            <div class="flex items-center text-sm font-semibold text-orange-600/80 mt-2">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Khusus Lunas <span class="text-gray-400 font-medium">dari mesin Kasir</span></span>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left Side: Warning & Actions (Takes 2 columns) -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            <!-- Warning Box -->
            <div class="bg-[#FCEAE9] border border-[#f3d1cf] rounded-2xl p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-4 text-[#CF544D]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <h3 class="text-xl font-bold">Perhatian Dibutuhkan</h3>
                </div>
                
                <h4 class="text-[#CF544D] font-bold mb-4 text-sm">Stok Menipis (&lt; 5kg)</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($stok_menipis as $item)
                        <div class="bg-[#fcf7f4]/60 border border-[#f3d1cf] rounded-xl p-4 flex justify-between items-center text-[#7A4E3B]">
                            <div>
                                <p class="font-bold text-[15px]">{{ $item['name'] }}</p>
                                <p class="text-xs font-semibold text-[#7A4E3B]/60">{{ $item['type'] }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xl font-extrabold text-[#CF544D]">{{ $item['amount'] }}</span>
                                <span class="text-sm font-bold text-[#CF544D] ml-0.5">{{ $item['unit'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button class="bg-white hover:bg-[#fcf7f4] border border-[#e6dac3] rounded-2xl p-5 flex flex-col items-center justify-center gap-3 transition shadow-sm text-[#B57357]">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path></svg>
                    <span class="text-[13px] font-bold">Panggang Baru</span>
                </button>
                <button class="bg-white hover:bg-[#fcf7f4] border border-[#e6dac3] rounded-2xl p-5 flex flex-col items-center justify-center gap-3 transition shadow-sm text-[#B57357]">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    <span class="text-[13px] font-bold">Transfer Internal</span>
                </button>
                <button class="bg-white hover:bg-[#fcf7f4] border border-[#e6dac3] rounded-2xl p-5 flex flex-col items-center justify-center gap-3 transition shadow-sm text-[#B57357]">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span class="text-[13px] font-bold">Buka Kasir</span>
                </button>
                <button class="bg-white hover:bg-[#fcf7f4] border border-[#e6dac3] rounded-2xl p-5 flex flex-col items-center justify-center gap-3 transition shadow-sm text-[#B57357]">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                    <span class="text-[13px] font-bold">Lihat Laba-Rugi</span>
                </button>
            </div>
        </div>

        <!-- Right Side: Logs -->
        <div class="bg-white border border-[#e6dac3] rounded-2xl shadow-sm p-6 lg:h-full">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-[#7A4E3B] font-bold text-lg">Log Aktivitas Terbaru</h3>
                <button class="text-[#B57357] hover:bg-[#fcf7f4] p-1.5 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
            
            <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-[#e6dac3] before:to-transparent">
                @foreach($recent_logs as $log)
                    <div class="relative flex items-start gap-4">
                        <div class="w-10 h-10 bg-[#f9f5f0] border-2 border-white rounded-full flex items-center justify-center text-emerald-600 shrink-0 z-10 shadow-sm relative left-0.5 shadow-sm ring-1 ring-inset ring-[#e6dac3]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <div class="pt-0.5 pl-1 pb-1 flex-1">
                            <p class="text-sm font-bold text-[#B57357] leading-tight mb-1">{{ $log['type'] }}</p>
                            <p class="text-[13px] text-gray-600 leading-snug mb-2">{{ $log['description'] }}</p>
                            <p class="text-[11px] font-semibold text-gray-400">{{ $log['time'] }} oleh {{ $log['user'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-layout>
