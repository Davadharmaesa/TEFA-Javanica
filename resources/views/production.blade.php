@extends('layouts.app')

@section('title', 'Lab Roasting - TEFA Javanica')

@section('content')
<div class="space-y-6" x-data="productionLab()">
    <div class="flex flex-col gap-2">
        <h1 class="text-3xl font-heading font-bold text-primary">Lab Roasting (Sangrai)</h1>
        <p class="text-muted-foreground font-sans">
            Catat hasil sangrai, pantau persentase penyusutan (burnoff), dan kelola batch produksi.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Roasting form block -->
        <div class="bg-card border border-border shadow-sm rounded-xl p-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border">
                <div class="p-2 bg-primary text-primary-foreground rounded-md shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                </div>
                <h2 class="font-heading font-bold text-lg text-primary">Batch Sangrai Baru</h2>
            </div>

            <form @submit.prevent="submitBatch" class="space-y-5">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-primary">Pilih Biji Mentah (Green Bean)</label>
                    <select
                        x-model="form.green_bean_id"
                        class="w-full bg-background border border-border rounded-md px-4 py-2 text-sm text-primary font-sans focus:outline-none focus:ring-1 focus:ring-primary transition-all cursor-pointer"
                    >
                        <option value="">-- Pilih Biji --</option>
                        <template x-for="item in greenBeans" :key="item.id">
                            <option :value="item.id" x-text="item.nama_kopi + ' (Tersedia: ' + (item.stok_gram/1000) + ' kg)'"></option>
                        </template>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-primary">Berat Mentah Masuk (Kg)</label>
                        <input
                            type="number" step="0.1" 
                            x-model.number="form.berat_mentah_kg"
                            class="w-full bg-background border border-border rounded-md px-4 py-2 text-sm text-primary font-mono focus:outline-none focus:ring-1 focus:ring-primary transition-all"
                            placeholder="Contoh: 10"
                            required
                        />
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-primary">Hasil Matang Keluar (Kg)</label>
                        <input
                            type="number" step="0.1" 
                            x-model.number="form.berat_akhir_kg"
                            class="w-full bg-background border border-border rounded-md px-4 py-2 text-sm text-primary font-mono focus:outline-none focus:ring-1 focus:ring-primary transition-all"
                            placeholder="Contoh: 8.2"
                            required
                        />
                    </div>
                </div>

                <!-- Optional Temperature Fields -->
                <div class="grid grid-cols-2 gap-4 border-t border-border pt-4 mt-2">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-muted-foreground italic">Suhu Masuk (°C) - Opsional</label>
                        <input
                            type="number"
                            x-model.number="form.suhu_masuk"
                            class="w-full bg-background border border-border rounded-md px-4 py-2 text-sm text-primary font-mono focus:outline-none focus:ring-1 focus:ring-primary transition-all"
                            placeholder="Contoh: 200"
                        />
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-muted-foreground italic">Suhu Keluar (°C) - Opsional</label>
                        <input
                            type="number"
                            x-model.number="form.suhu_keluar"
                            class="w-full bg-background border border-border rounded-md px-4 py-2 text-sm text-primary font-mono focus:outline-none focus:ring-1 focus:ring-primary transition-all"
                            placeholder="Contoh: 215"
                        />
                    </div>
                </div>

                <!-- Auto Calculation Display -->
                <div class="mt-6 p-4 bg-background border border-border rounded-lg relative overflow-hidden group">
                    <div class="absolute inset-y-0 left-0 w-1 bg-muted group-hover:bg-primary transition-all"></div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-muted-foreground uppercase tracking-tighter">Kalkulasi Penyusutan (Burnoff)</span>
                    </div>
                    <div class="flex items-end justify-between">
                        <span class="text-3xl font-mono font-bold text-primary">
                            <span x-text="shrinkagePercent"></span> <span class="text-lg text-muted-foreground">%</span>
                        </span>
                        <template x-if="shrinkagePercent > 0">
                            <span 
                                class="text-[10px] font-bold px-2 py-1 rounded-md mb-1 uppercase tracking-tight"
                                :class="isOptimal ? 'bg-secondary/10 text-secondary' : 'bg-accent/10 text-accent'"
                                x-text="isOptimal ? 'Batas Optimal' : 'Peringatan: Tinggi/Rendah'"
                            ></span>
                        </template>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="loading || !form.green_bean_id || !form.berat_akhir_kg"
                    class="w-full py-3 mt-4 bg-secondary text-secondary-foreground font-bold rounded-md hover:bg-opacity-90 disabled:opacity-50 transition-all flex justify-center items-center gap-2 shadow-sm uppercase tracking-widest text-xs"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg>
                    <span x-show="!loading">Simpan Batch Sangrai</span>
                    <span x-show="loading" class="animate-pulse">Menyimpan...</span>
                </button>
            </form>
        </div>

        <!-- History Block -->
        <div class="bg-card border border-border shadow-sm rounded-xl p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-border">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-background border border-border text-primary rounded-md shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>
                    </div>
                    <h2 class="font-heading font-bold text-lg text-primary uppercase tracking-tighter">Produksi Terakhir</h2>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto space-y-4 custom-scrollbar max-h-[600px]">
                <template x-for="batch in history" :key="batch.id">
                    <div class="p-4 bg-background border border-border rounded-lg relative hover:shadow-md transition-all group">
                        <div class="absolute inset-y-0 left-0 w-1 bg-secondary opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-mono font-bold text-secondary text-sm" x-text="'BATCH-#' + batch.id"></span>
                            <span class="font-mono text-[10px] font-bold text-muted-foreground uppercase" x-text="formatTime(batch.created_at)"></span>
                        </div>
                        <p class="font-sans font-bold text-primary mb-3 text-sm" x-text="batch.green_bean.nama_kopi"></p>
                        <div class="flex justify-between items-end text-xs font-sans">
                            <div class="text-primary font-medium uppercase tracking-tighter">
                                In: <span class="font-mono" x-text="(batch.berat_mentah/1000) + 'kg'"></span> → Out: <span class="font-mono font-bold text-primary" x-text="(batch.berat_akhir/1000) + 'kg'"></span>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-accent font-mono text-xs font-bold uppercase tracking-wider" x-text="batch.shrinkage + '% susut'"></span>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="history.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-muted-foreground py-12 opacity-20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-4 text-primary"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/></svg>
                        <p class="font-sans text-[10px] uppercase font-bold tracking-widest italic">Belum ada aktivitas produksi terekam.</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div x-show="toast.show" x-cloak x-transition.duration.500ms class="fixed bottom-10 right-10 bg-secondary text-secondary-foreground px-6 py-4 rounded-xl shadow-2xl flex items-center space-x-3 z-[100] border border-white/10">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        <span class="font-bold text-sm uppercase tracking-widest" x-text="toast.msg"></span>
    </div>
</div>

@push('scripts')
<script>
    function productionLab() {
        return {
            greenBeans: [],
            history: [],
            loading: false,
            form: { green_bean_id: '', berat_mentah_kg: '', berat_akhir_kg: '', suhu_masuk: '', suhu_keluar: '' },
            toast: { show: false, msg: '' },

            init() { this.fetchData(); },

            get shrinkagePercent() {
                if (!this.form.berat_mentah_kg || !this.form.berat_akhir_kg) return '0.0';
                return (((this.form.berat_mentah_kg - this.form.berat_akhir_kg) / this.form.berat_mentah_kg) * 100).toFixed(1);
            },

            get isOptimal() {
                const s = parseFloat(this.shrinkagePercent);
                return s >= 12 && s <= 20;
            },

            async fetchData() {
                try {
                    const [gbRes, batchRes] = await Promise.all([
                        fetch('/api/v1/green-beans'),
                        fetch('/api/v1/roasting-batches')
                    ]);
                    this.greenBeans = await gbRes.json();
                    this.history = await batchRes.json();
                } catch(e) {}
            },

            async submitBatch() {
                this.loading = true;
                try {
                    const payload = {
                        green_bean_id: this.form.green_bean_id,
                        berat_mentah_gram: this.form.berat_mentah_kg * 1000,
                        berat_siap_jual_gram: this.form.berat_akhir_kg * 1000,
                        catatan: `In: ${this.form.suhu_masuk}C | Out: ${this.form.suhu_keluar}C`
                    };
                    const res = await fetch('/api/v1/roasting-batches', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    if (res.ok) {
                        this.showToast('Batch Berhasil Disimpan!');
                        this.form = { green_bean_id: '', berat_mentah_kg: '', berat_akhir_kg: '', suhu_masuk: '', suhu_keluar: '' };
                        this.fetchData();
                    }
                } finally { this.loading = false; }
            },

            showToast(msg) {
                this.toast = { show: true, msg };
                setTimeout(() => this.toast.show = false, 4000);
            },

            formatTime(dateString) { return new Date(dateString).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }); }
        }
    }
</script>
@endpush
@endsection
