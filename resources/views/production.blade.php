@extends('layouts.app')

@section('title', 'Terminal Roasting - TEFA Javanica')

@section('content')
<div class="space-y-6 pb-12" x-data="productionLab()">
    
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
            <h1 class="text-3xl font-heading font-black text-primary tracking-tight">Terminal Roasting</h1>
            <p class="text-muted-foreground font-sans text-sm mt-1">
                Pusat Kendali Mesin Sangrai & Pencatatan Inventaris Barang Jadi.
            </p>
        </div>
        <div class="text-right">
            <p class="text-2xl font-mono font-black text-primary" x-text="currentTime"></p>
            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest" x-text="currentDate"></p>
        </div>
    </div>

    <!-- 3 Top KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- KPI 1 -->
        <div class="bg-card border border-border shadow-sm rounded-xl p-5 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-primary/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            <div class="flex items-center gap-4">
                <div class="p-3 bg-primary/10 text-primary border border-primary/20 rounded-lg z-10 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg>
                </div>
                <div class="z-10">
                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Target Tercapai</p>
                    <h3 class="text-2xl font-black font-mono text-primary leading-none mt-1" x-text="stats.target_tercapai + ' '"><span class="text-xs font-sans text-muted-foreground uppercase">Kg</span></h3>
                </div>
            </div>
        </div>
        <!-- KPI 2 -->
        <div class="bg-card border border-border shadow-sm rounded-xl p-5 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-secondary/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            <div class="flex items-center gap-4">
                <div class="p-3 bg-secondary/10 text-secondary border border-secondary/20 rounded-lg z-10 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                </div>
                <div class="z-10">
                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Rata-rata Susut Mesin</p>
                    <h3 class="text-2xl font-black font-mono text-secondary leading-none mt-1" x-text="stats.rata_susut + ' '"><span class="text-xs font-sans text-muted-foreground uppercase">% Valid</span></h3>
                </div>
            </div>
        </div>
        <!-- KPI 3 -->
        <div class="bg-card border border-border shadow-sm rounded-xl p-5 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-accent/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            <div class="flex items-center gap-4">
                <div class="p-3 bg-accent/10 text-accent border border-accent/20 rounded-lg z-10 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="z-10">
                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Operator Aktif Shift</p>
                    <h3 class="text-xl font-black font-sans text-primary leading-none mt-1">{{ Auth::user()->name }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- LEFT: Control Panel Form (Spans 7 cols) -->
        <div class="lg:col-span-7 bg-card border border-border shadow-lg shadow-black/5 rounded-2xl overflow-hidden flex flex-col">
            <div class="bg-primary px-6 py-4 flex items-center justify-between">
                <h2 class="font-heading font-black text-lg text-primary-foreground tracking-wide flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m18 11.03-8.8 8.8a2 2 0 0 1-2.83 0l-2.83-2.83a2 2 0 0 1 0-2.83l8.8-8.8"/><path d="M18 11.03 21 8l-3-3-3 3 1.03 2.97"/></svg>
                    INPUT BATCH PRODUKSI
                </h2>
                <div class="bg-white/20 text-white text-[10px] uppercase font-bold px-2 py-1 rounded tracking-widest shadow-inner">Terminal UI</div>
            </div>

            <form @submit.prevent="submitBatch" class="p-6 flex-1 flex flex-col gap-6">
                <!-- Data Pokok -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-primary uppercase tracking-widest drop-shadow-sm">Bahan Mentah</label>
                        <select x-model="form.green_bean_id" class="w-full bg-white border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition-all cursor-pointer shadow-[inset_0_2px_4px_rgba(0,0,0,0.03)]" required>
                            <option value=""> TARIK DARI GUDANG </option>
                            <template x-for="item in greenBeans" :key="item.id">
                                <option :value="item.id" x-text="item.nama_biji + ' (' + (item.stok_gram/1000) + ' kg)'"></option>
                            </template>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-primary uppercase tracking-widest drop-shadow-sm">Kode Batch</label>
                        <input type="text" x-model="form.kode_batch" readonly class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-black font-mono text-primary focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.03)] uppercase placeholder:font-sans placeholder:font-medium placeholder:uppercase" placeholder="Otomatis" required />
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-primary uppercase tracking-widest drop-shadow-sm">Durasi (Menit)</label>
                        <input type="number" min="1" x-model="form.durasi_roasting" @wheel="$event.target.blur()" class="w-full bg-white border border-border/60 rounded-xl px-4 py-3 text-sm font-black font-mono text-primary focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.03)] uppercase placeholder:font-sans placeholder:font-medium placeholder:uppercase [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="0" required />
                    </div>
                </div>

                <!-- Custom Selectors (Radio Cards) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-muted/30 p-4 rounded-xl border border-border/80 shadow-inner">
                    <!-- Profil Sangrai -->
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-primary uppercase tracking-widest flex items-center justify-between">
                            Profil Roasting
                            <span class="text-[9px] bg-secondary/10 text-secondary px-1.5 py-0.5 rounded font-black normal-case">Otomatis dari Durasi</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <!-- Light -->
                            <label class="relative cursor-pointer group">
                                <input type="radio" x-model="form.profil_sangrai" value="Light Roast" class="peer sr-only" disabled required>
                                <div class="h-20 bg-background border px-1 border-border rounded-lg flex flex-col items-center justify-center gap-1 group-hover:border-primary/40 group-hover:shadow-[0_0_15px_rgba(200,80,50,0.1)] peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:shadow-[0_0_15px_rgba(200,80,50,0.2)] transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="text-muted-foreground peer-checked:text-primary transition-colors duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                                    <span class="text-[10px] font-bold text-primary/80 peer-checked:text-primary text-center leading-tight transition-colors">Light<br>Roast</span>
                                </div>
                                <div class="absolute -top-1.5 -right-1.5 bg-primary text-white rounded-full p-0.5 opacity-0 scale-0 peer-checked:opacity-100 peer-checked:scale-100 transition-all duration-300 shadow-md"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                            <!-- Medium -->
                            <label class="relative cursor-pointer group">
                                <input type="radio" x-model="form.profil_sangrai" value="Medium Roast" class="peer sr-only" disabled required>
                                <div class="h-20 bg-background border px-1 border-border rounded-lg flex flex-col items-center justify-center gap-1 group-hover:border-primary/40 group-hover:shadow-[0_0_15px_rgba(200,80,50,0.1)] peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:shadow-[0_0_15px_rgba(200,80,50,0.2)] transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="text-muted-foreground peer-checked:text-primary transition-colors duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/></svg>
                                    <span class="text-[10px] font-bold text-primary/80 peer-checked:text-primary text-center leading-tight transition-colors">Medium<br>Roast</span>
                                </div>
                                <div class="absolute -top-1.5 -right-1.5 bg-primary text-white rounded-full p-0.5 opacity-0 scale-0 peer-checked:opacity-100 peer-checked:scale-100 transition-all duration-300 shadow-md"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                            <!-- Dark -->
                            <label class="relative cursor-pointer group">
                                <input type="radio" x-model="form.profil_sangrai" value="Dark Roast" class="peer sr-only" disabled required>
                                <div class="h-20 bg-background border px-1 border-border rounded-lg flex flex-col items-center justify-center gap-1 group-hover:border-primary/40 group-hover:shadow-[0_0_15px_rgba(200,80,50,0.1)] peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:shadow-[0_0_15px_rgba(200,80,50,0.2)] transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="text-muted-foreground peer-checked:text-primary transition-colors duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                                    <span class="text-[10px] font-bold text-primary/80 peer-checked:text-primary text-center leading-tight transition-colors">Dark<br>Roast</span>
                                </div>
                                <div class="absolute -top-1.5 -right-1.5 bg-primary text-white rounded-full p-0.5 opacity-0 scale-0 peer-checked:opacity-100 peer-checked:scale-100 transition-all duration-300 shadow-md"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                        </div>
                    </div>

                    <!-- Target Hasil -->
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-primary uppercase tracking-widest flex items-center justify-between">
                            Target Produk
                            <span class="text-[9px] bg-secondary/10 text-secondary px-1.5 py-0.5 rounded font-black normal-case">Wajib Pilih</span>
                        </label>
                        <select x-model="form.roasted_bean_id" class="w-full h-20 bg-white border border-border/60 rounded-xl px-4 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition-all cursor-pointer shadow-[inset_0_2px_4px_rgba(0,0,0,0.03)]" required>
                            <option value="">-- DAFTAR PRODUK JAVANICA --</option>
                            <template x-for="item in roastedBeans" :key="item.id">
                                <option :value="item.id" x-text="item.nama_kopi"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Live Dashboard Gauge & Weight Inputs -->
                <div class="border border-border/70 rounded-xl p-5 shadow-sm bg-background relative overflow-hidden group">
                    <!-- Tech Grid Background -->
                    <div class="absolute inset-0 opacity-[0.03] group-hover:opacity-[0.05] transition-opacity" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 16px 16px;"></div>
                    
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-primary uppercase tracking-widest bg-muted px-2 py-1 rounded inline-block">1. Gudang (Kg)</label>
                                <input type="number" min="0.01" step="0.01" x-model="form.berat_mentah_gudang" @wheel="$event.target.blur()" class="w-full bg-white rounded-lg border-2 border-border/80 px-3 py-2 text-xl md:text-2xl text-primary font-black font-mono focus:outline-none focus:border-primary transition-all placeholder:text-muted/50 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="0.00" required />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-primary uppercase tracking-widest bg-primary/10 px-2 py-1 rounded inline-block">2. Sortasi (Kg)</label>
                                <input type="number" min="0.01" step="0.01" :max="form.berat_mentah_gudang" x-model="form.berat_mentah_sortasi" @wheel="$event.target.blur()" class="w-full bg-white rounded-lg border-2 border-primary/40 px-3 py-2 text-xl md:text-2xl text-primary font-black font-mono focus:outline-none focus:border-primary transition-all placeholder:text-muted/50 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="0.00" required />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-accent uppercase tracking-widest bg-accent/10 px-2 py-1 rounded inline-block">3. Kotor (Kg)</label>
                                <input type="number" min="0.01" step="0.01" :max="form.berat_mentah_sortasi" x-model="form.berat_matang_kotor" @wheel="$event.target.blur()" class="w-full bg-white rounded-lg border-2 border-accent/40 px-3 py-2 text-xl md:text-2xl text-accent font-black font-mono focus:outline-none focus:border-accent transition-all placeholder:text-muted/50 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="0.00" required />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-secondary uppercase tracking-widest bg-secondary/10 px-2 py-1 rounded inline-block">4. Bersih (Kg)</label>
                                <input type="number" 
                                       min="0.01" 
                                       step="0.01" 
                                       :max="form.berat_matang_kotor" 
                                       x-model="form.berat_matang_bersih" 
                                       @wheel="$event.target.blur()" 
                                       class="w-full bg-white rounded-lg border-2 border-secondary/40 px-3 py-2 text-xl md:text-2xl text-secondary font-black font-mono focus:outline-none focus:border-secondary transition-all placeholder:text-muted/50 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" 
                                       placeholder="0.00" required />
                            </div>
                        </div>

                        <!-- Magic Gauge -->
                        <div class="mt-2 pt-5 border-t border-dashed border-border/60">
                            <div class="flex justify-between items-end mb-3">
                                <label class="text-xs font-bold text-primary flex items-center gap-1.5 drop-shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" :class="isOptimal ? 'text-secondary' : 'text-accent'"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                                    MONITOR PENYUSUTAN
                                </label>
                                <div class="text-right flex flex-col items-end">
                                    <div class="flex items-center gap-2">
                                        <span x-show="form.berat_mentah_sortasi > 0" class="text-[10px] font-black px-2 py-1 rounded uppercase tracking-widest shadow-sm self-center mr-1" :class="isOptimal ? 'bg-secondary text-white' : 'bg-accent text-white'" x-text="isOptimal ? 'Mulus' : 'Peringatan'" title="Status berdasarkan Susut Mesin"></span>
                                        
                                        <!-- Angka Total Saja -->
                                        <div class="flex flex-col items-end">
                                            <span class="text-4xl font-black font-mono tracking-tighter" :class="isOptimal ? 'text-secondary drop-shadow-[0_2px_10px_rgba(20,80,30,0.3)]' : 'text-accent drop-shadow-[0_2px_10px_rgba(200,30,30,0.3)]'">
                                                <span x-text="shrinkageTotal"></span><span class="text-xl opacity-60">%</span>
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-muted-foreground mt-1" x-show="form.berat_mentah_gudang > 0">Susut Keseluruhan (Total)</span>
                                </div>
                            </div>
                            
                            <!-- Progress Bar Container (True Stacked Flex) -->
                            <div class="h-8 w-full bg-muted/80 rounded-full overflow-hidden relative shadow-[inset_0_2px_8px_rgba(0,0,0,0.08)] cursor-help flex">
                                <!-- Optimal Zone Marker (Hanya visual background) -->
                                <div class="absolute top-0 bottom-0 left-[12%] right-[80%] bg-secondary/10 z-0 border-x-2 border-secondary/30 pointer-events-none">
                                    <div class="absolute inset-0" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(0,0,0,0.03) 10px, rgba(0,0,0,0.03) 20px);"></div>
                                </div>
                                
                                <!-- Segmen 1: Susut Awal (Biru) -->
                                <div class="h-full transition-all duration-[800ms] ease-[cubic-bezier(0.2,1,0.2,1)] z-10 bg-blue-500 hover:brightness-110 border-r border-white/20"
                                     :style="`width: ${barPctAwal}%`"
                                     :title="`Susut Sortasi Mentah: ${shrinkageAwal}%`">
                                </div>

                                <!-- Segmen 2: Susut Mesin (Hijau/Merah) -->
                                <div class="h-full transition-all duration-[800ms] ease-[cubic-bezier(0.2,1,0.2,1)] z-10 border-r border-white/20 hover:brightness-110"
                                     :style="`width: ${barPctMesin}%`"
                                     :class="isOptimal ? 'bg-gradient-to-r from-secondary/90 to-secondary' : 'bg-gradient-to-r from-accent/90 to-accent'"
                                     :title="`Susut Sangrai (Mesin): ${shrinkageRoasting}%`">
                                </div>

                                <!-- Segmen 3: Susut Sortasi Akhir (Kuning/Orange) -->
                                <div class="h-full transition-all duration-[800ms] ease-[cubic-bezier(0.2,1,0.2,1)] z-10 bg-amber-400 hover:brightness-110 flex justify-end items-center px-1"
                                     :style="`width: ${barPctAkhir}%`"
                                     :title="`Susut Sortasi Akhir: ${((form.berat_matang_kotor - form.berat_matang_bersih)/form.berat_matang_kotor*100).toFixed(1)}%`">
                                     <div class="w-2 h-2 rounded-full bg-white opacity-90 shadow-[0_0_10px_rgba(255,255,255,1)] animate-pulse" x-show="form.berat_mentah_gudang > 0"></div>
                                </div>
                            </div>
                            <div class="flex justify-between mt-2 px-1 text-[9px] font-mono font-bold text-muted-foreground uppercase opacity-70">
                                <span>0%</span>
                                <span class="text-secondary opacity-100 drop-shadow">12% (Batas Bawah)</span>
                                <span class="text-secondary opacity-100 drop-shadow">20% (Batas Atas)</span>
                                <span class="text-accent opacity-100 drop-shadow">> 25% (Bahaya)</span>
                            </div>
                            <!-- Legend -->
                            <div class="flex gap-4 mt-3 px-1">
                                <div class="flex items-center gap-1.5 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                                    <div class="w-3 h-3 rounded-sm bg-blue-500"></div>
                                    Awal (Mentah)
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                                    <div class="w-3 h-3 rounded-sm" :class="isOptimal ? 'bg-secondary' : 'bg-accent'"></div>
                                    Mesin (Sangrai)
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                                    <div class="w-3 h-3 rounded-sm bg-amber-400"></div>
                                    Akhir (Matang)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-2 mt-auto">
                    <button type="submit" :disabled="loading || !form.green_bean_id || !form.roasted_bean_id || !form.berat_matang_bersih || !form.berat_mentah_sortasi || !form.profil_sangrai || !form.durasi_roasting" class="w-full h-14 bg-primary text-primary-foreground font-black text-sm uppercase tracking-widest rounded-xl hover:bg-primary/90 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-[0_10px_30px_rgb(0,0,0,0.15)] hover:shadow-[0_10px_40px_rgba(200,80,50,0.35)] flex justify-center items-center gap-3 group relative overflow-hidden transform hover:-translate-y-0.5 active:translate-y-0">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent w-full transition-opacity duration-300"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="relative z-10 group-disabled:opacity-50"><line x1="22" x2="11" y1="2" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        <span x-show="!loading" class="relative z-10">Tambahkan Data</span>
                        <span x-show="loading" class="relative z-10 animate-pulse">Menghubungi Pangkalan Data...</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- RIGHT: History Log (Spans 5 cols) -->
        <div class="lg:col-span-5 bg-card border border-border shadow-lg shadow-black/5 rounded-2xl p-0 flex flex-col overflow-hidden">
            <div class="bg-card border-b border-border p-5 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)] flex justify-between items-center z-10 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-32 h-32 bg-primary/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
                <div>
                    <h2 class="text-lg font-heading font-black text-primary tracking-tight">Log Mesin Terkini</h2>
                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-0.5">Histori Operasional Shift</p>
                </div>
                <div class="bg-primary/10 text-primary p-2 rounded-xl shadow-inner border border-primary/20 relative z-10 flex flex-col items-center justify-center">
                    <span class="text-lg font-black leading-none" x-text="history.length"></span>
                    <span class="text-[8px] font-bold uppercase">Batch</span>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar min-h-[500px] max-h-[850px] bg-background">
                <template x-for="batch in history" :key="batch.id">
                    <div class="bg-card border-l-4 border-l-secondary border border-border rounded-xl p-4 shadow-sm group hover:shadow-md transition-all relative overflow-hidden flex gap-4 items-start">
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1.5">
                                <span class="font-mono font-black text-primary text-sm uppercase truncate pr-2 group-hover:text-secondary transition-colors" x-text="batch.kode_batch || ('BATCH-#' + batch.id)"></span>
                                <span class="font-mono text-[9px] font-bold px-1.5 py-0.5 rounded border border-border/50 bg-muted/30 text-muted-foreground flex-shrink-0" x-text="formatTime(batch.created_at)"></span>
                            </div>
                            
                            <p class="font-sans font-black text-primary text-xs mb-1 truncate" x-text="batch.roasted_bean_nama || 'Produk'"></p>
                            <p class="font-sans font-medium text-primary/70 text-[10px] mb-3 truncate" x-text="'Dari: ' + (batch.green_bean?.nama_biji || 'Biji Mentah')"></p>
                            
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <div class="bg-muted/40 border border-border/60 rounded-lg p-2 text-center group-hover:bg-muted/60 transition-colors">
                                    <p class="text-[9px] text-primary/60 font-black uppercase mb-0.5">Profil Sangrai</p>
                                    <p class="text-xs font-bold text-primary/80 truncate" x-text="batch.profil_sangrai || 'Profil'"></p>
                                </div>
                                <div class="bg-secondary/10 border border-secondary/20 rounded-lg p-2 text-center group-hover:bg-secondary/20 transition-colors">
                                    <p class="text-[9px] text-secondary/80 font-black uppercase mb-0.5">Total Susut</p>
                                    <p class="text-xs font-black text-secondary font-mono" x-text="batch.persentase_susut + '%'"></p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-1.5 text-[9px] font-mono font-bold text-primary mt-1">
                                <span class="bg-background px-1.5 py-0.5 border border-border/80 rounded" x-text="'IN: ' + batch.berat_mentah_gudang + 'kg'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted-foreground opacity-50"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                <span class="bg-primary/10 px-1.5 py-0.5 border border-primary/20 rounded" x-text="'SORT: ' + batch.berat_mentah_sortasi + 'kg'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted-foreground opacity-50"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                <span class="bg-secondary/10 px-1.5 py-0.5 border border-secondary/20 text-secondary rounded" x-text="'OUT: ' + batch.berat_matang_bersih + 'kg'"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="history.length === 0">
                    <div class="h-full min-h-[300px] flex flex-col items-center justify-center text-muted py-12 text-center px-4">
                        <div class="w-16 h-16 rounded-full bg-muted/30 flex items-center justify-center mb-4 ring-4 ring-muted/10">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="opacity-40"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <p class="font-sans text-xs uppercase font-black tracking-widest text-muted-foreground">Mesin Istirahat</p>
                        <p class="text-[10px] text-muted-foreground/70 mt-2 max-w-[200px] leading-relaxed font-medium">Belum ada aktivitas sangrai yang terekam. Masukkan batch produksi pertama Anda hari ini.</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div x-show="toast.show" x-cloak 
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-8 scale-95" x-transition:enter-end="opacity-100 transform translate-y-0 scale-100" 
         x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-y-0 scale-100" x-transition:leave-end="opacity-0 transform translate-y-8 scale-95" 
         class="fixed bottom-10 right-10 px-6 py-4 rounded-xl shadow-2xl flex items-center space-x-4 z-[100] border border-white/20"
         :class="toast.type === 'success' ? 'bg-[#526B42] text-white shadow-[#526B42]/40' : (toast.type === 'warning' ? 'bg-[#D97706] text-white shadow-[#D97706]/40' : 'bg-red-600 text-white shadow-red-600/40')">
        <div class="bg-white/20 p-2 rounded-lg">
            <svg x-show="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white drop-shadow-md"><polyline points="20 6 9 17 4 12"/></svg>
            <svg x-show="toast.type === 'warning'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white drop-shadow-md"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <svg x-show="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white drop-shadow-md"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="flex flex-col">
            <span class="font-black text-sm uppercase tracking-widest leading-none drop-shadow-sm" x-text="toast.type === 'success' ? 'TRANSMISI DATA SUKSES' : (toast.type === 'warning' ? 'PERINGATAN' : 'TRANSMISI DATA GAGAL')"></span>
            <span class="text-[10px] font-bold opacity-90 mt-1.5 font-mono" x-text="toast.msg"></span>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function productionLab() {
        return {
            greenBeans: [],
            roastedBeans: [],
            history: [],
            stats: { target_tercapai: 0, rata_susut: 0, total_batch: 0 },
            loading: false,
            form: { green_bean_id: '', roasted_bean_id: '', kode_batch: '', profil_sangrai: '', durasi_roasting: '', berat_mentah_gudang: '', berat_mentah_sortasi: '', berat_matang_kotor: '', berat_matang_bersih: '' },
            toast: { show: false, msg: '', type: 'success' },
            timeObj: new Date(),

            init() { 
                this.fetchData(); 
                this.generateBatchCode();
                setInterval(() => { this.timeObj = new Date(); }, 1000);

                this.$watch('form.durasi_roasting', value => {
                    if (!value) return;
                    if (value < 12) this.form.profil_sangrai = 'Light Roast';
                    else if (value >= 12 && value <= 15) this.form.profil_sangrai = 'Medium Roast';
                    else this.form.profil_sangrai = 'Dark Roast';
                });

                // Validasi Input Cepat
                this.$watch('form.berat_mentah_sortasi', value => {
                    if(value && this.form.berat_mentah_gudang && parseFloat(value) > parseFloat(this.form.berat_mentah_gudang)) {
                        this.form.berat_mentah_sortasi = this.form.berat_mentah_gudang;
                        this.showToast("Berat Sortasi tidak boleh lebih dari Gudang!", "warning");
                    }
                });
                this.$watch('form.berat_matang_kotor', value => {
                    if(value && this.form.berat_mentah_sortasi && parseFloat(value) > parseFloat(this.form.berat_mentah_sortasi)) {
                        this.form.berat_matang_kotor = this.form.berat_mentah_sortasi;
                        this.showToast("Berat Kotor tidak boleh lebih dari Sortasi!", "warning");
                    }
                });
                this.$watch('form.berat_matang_bersih', value => {
                    if(value && this.form.berat_matang_kotor && parseFloat(value) > parseFloat(this.form.berat_matang_kotor)) {
                        this.form.berat_matang_bersih = this.form.berat_matang_kotor;
                        this.showToast("Berat Bersih tidak boleh lebih dari Kotor!", "warning");
                    }
                });
            },

            generateBatchCode() {
                const date = new Date();
                const ddmmyy = String(date.getDate()).padStart(2, '0') + 
                               String(date.getMonth() + 1).padStart(2, '0') + 
                               date.getFullYear().toString().slice(-2);
                
                const prefix = `RST-${ddmmyy}-`;
                let seq = 1;
                
                if (this.history && this.history.length > 0) {
                    // Cari riwayat hari ini
                    const todayBatches = this.history.filter(b => b.kode_batch && b.kode_batch.startsWith(prefix));
                    if (todayBatches.length > 0) {
                        // Ambil angka sequence tertinggi
                        const maxSeq = Math.max(...todayBatches.map(b => {
                            const parts = b.kode_batch.split('-');
                            return parseInt(parts[2]) || 0;
                        }));
                        seq = maxSeq + 1;
                    }
                }

                this.form.kode_batch = `${prefix}${String(seq).padStart(3, '0')}`;
            },

            get currentTime() {
                return this.timeObj.toLocaleTimeString('id-ID', { hour12: false });
            },

            get currentDate() {
                return this.timeObj.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            },

            get shrinkageAwal() {
                if (!this.form.berat_mentah_gudang || !this.form.berat_mentah_sortasi || this.form.berat_mentah_gudang <= 0) return '0.0';
                let p = (((this.form.berat_mentah_gudang - this.form.berat_mentah_sortasi) / this.form.berat_mentah_gudang) * 100);
                if (p < 0) p = 0;
                return p.toFixed(1);
            },

            get shrinkageTotal() {
                if (!this.form.berat_mentah_gudang || !this.form.berat_matang_bersih || this.form.berat_mentah_gudang <= 0) return '0.0';
                let p = (((this.form.berat_mentah_gudang - this.form.berat_matang_bersih) / this.form.berat_mentah_gudang) * 100);
                if (p < 0) p = 0;
                return p.toFixed(1);
            },

            get shrinkageRoasting() {
                if (!this.form.berat_mentah_sortasi || !this.form.berat_matang_kotor || this.form.berat_mentah_sortasi <= 0) return '0.0';
                let p = (((this.form.berat_mentah_sortasi - this.form.berat_matang_kotor) / this.form.berat_mentah_sortasi) * 100);
                if (p < 0) p = 0;
                return p.toFixed(1);
            },

            // Kalkulasi Bar Proporsional (Relatif terhadap Gudang) agar stacked bar sejajar sempurna
            get barPctAwal() {
                if (!this.form.berat_mentah_gudang || this.form.berat_mentah_gudang <= 0) return 0;
                let val = ((this.form.berat_mentah_gudang - this.form.berat_mentah_sortasi) / this.form.berat_mentah_gudang) * 100;
                return Math.max(0, Math.min(val, 100));
            },
            get barPctMesin() {
                if (!this.form.berat_mentah_gudang || this.form.berat_mentah_gudang <= 0) return 0;
                let val = ((this.form.berat_mentah_sortasi - this.form.berat_matang_kotor) / this.form.berat_mentah_gudang) * 100;
                return Math.max(0, Math.min(val, 100));
            },
            get barPctAkhir() {
                if (!this.form.berat_mentah_gudang || this.form.berat_mentah_gudang <= 0) return 0;
                let val = ((this.form.berat_matang_kotor - this.form.berat_matang_bersih) / this.form.berat_mentah_gudang) * 100;
                return Math.max(0, Math.min(val, 100));
            },

            get isOptimal() {
                const s = parseFloat(this.shrinkageRoasting);
                return s >= 12 && s <= 20;
            },

            async fetchData() {
                try {
                    const [gbRes, rbRes, batchRes, statsRes] = await Promise.all([
                        fetch('/internal-api/roasting/green-beans').catch(() => ({ok: false})),
                        fetch('/internal-api/roasting/roasted-beans').catch(() => ({ok: false})),
                        fetch('/internal-api/roasting/batches').catch(() => ({ok: false})),
                        fetch('/internal-api/roasting/stats').catch(() => ({ok: false}))
                    ]);
                    
                    if(gbRes.ok) this.greenBeans = await gbRes.json();
                    if(rbRes.ok) this.roastedBeans = await rbRes.json();
                    if(batchRes.ok) {
                        this.history = await batchRes.json();
                        this.generateBatchCode(); // Re-generate urutan setelah history ditarik dari DB
                    }
                    if(statsRes.ok) this.stats = await statsRes.json();
                } catch(e) {
                    console.warn("Backend API belum siap. Mode Dummy aktif.");
                    // Dummy Injector biar form jalan sementara:
                    this.greenBeans = [
                        {id: 1, nama_biji: "GB Arabika Belawan", stok_gram: 50000},
                        {id: 2, nama_biji: "GB Robusta Dampit", stok_gram: 35000},
                        {id: 3, nama_biji: "GB Arabika Gayo Fullwash", stok_gram: 10000}
                    ];
                    this.roastedBeans = [
                        {id: 101, nama: "Medium Arabika Ijen"},
                        {id: 102, nama: "Arabika Bubuk Tubruk"},
                        {id: 103, nama: "Arabika Bubuk Espresso"},
                        {id: 104, nama: "Robusta Dark Roast Biji"}
                    ];
                }
            },

            async submitBatch() {
                this.loading = true;
                try {
                    const payload = {
                        green_bean_id: this.form.green_bean_id,
                        roasted_bean_id: this.form.roasted_bean_id,
                        kode_batch: this.form.kode_batch,
                        durasi_roasting: this.form.durasi_roasting,
                        profil_sangrai: this.form.profil_sangrai,
                        berat_mentah_gudang: this.form.berat_mentah_gudang,
                        berat_mentah_sortasi: this.form.berat_mentah_sortasi,
                        berat_matang_kotor: this.form.berat_matang_kotor,
                        berat_matang_bersih: this.form.berat_matang_bersih
                    };
                    
                    // Simulate API network delay
                    await new Promise(r => setTimeout(r, 600));
                    
                    const res = await fetch('/internal-api/roasting/batches', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json', 
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    });
                    
                    const data = await res.json();
                    
                    if (!res.ok) {
                        throw new Error(data.message || 'Terjadi kesalahan pada server');
                    }
                    
                    this.fetchData(); // Refresh history dari server
                    this.showToast(`Log ${this.form.kode_batch} berhasil direkam ke Database.`, 'success');
                    this.form = { green_bean_id: '', roasted_bean_id: '', kode_batch: '', profil_sangrai: '', durasi_roasting: '', berat_mentah_gudang: '', berat_mentah_sortasi: '', berat_matang_kotor: '', berat_matang_bersih: '' };
                    this.generateBatchCode();
                } catch (e) {
                    this.showToast(e.message || "Gagal melakukan post", 'error');
                } finally { 

                    this.loading = false; 
                }
            },

            showToast(msg, type = 'success') {
                this.toast = { show: true, msg, type };
                setTimeout(() => this.toast.show = false, 4000);
            },

            formatTime(dateString) { 
                const d = new Date(dateString);
                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();
                const time = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                return `${day}-${month}-${year} ${time}`;
            }
        }
    }
</script>
@endpush
@endsection
