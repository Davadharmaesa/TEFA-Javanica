@extends('layouts.app')

@section('title', 'Log Tenaga Kerja - TEFA Javanica')

@section('content')
<div x-data="logKerja()" class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-card p-6 rounded-2xl shadow-sm border border-border/40">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="bg-success/10 text-success p-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h1 class="text-2xl font-heading font-bold text-primary">
                    Terminal Log Pekerja
                </h1>
            </div>
            <p class="text-sm text-muted-foreground">Catat siapa saja yang terlibat dalam batch produksi hari ini untuk keperluan HPP & Honor.</p>
        </div>
        <div class="text-right bg-muted/30 px-4 py-2 rounded-xl border border-border/50">
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Tanggal Hari Ini</p>
            <p class="text-lg font-mono font-black text-primary" x-text="todayDate"></p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Form Input -->
        <div class="lg:col-span-1 space-y-6">
            <form @submit.prevent="submitLog" class="bg-card p-6 rounded-2xl shadow-sm border border-border/40 space-y-5">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-1.5 h-6 bg-success rounded-full"></div>
                    <h2 class="text-lg font-bold text-primary">Form Input Baru</h2>
                </div>

                <!-- Select Batch -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-primary uppercase tracking-widest drop-shadow-sm">Pilih ID Batch</label>
                    <select x-model="form.batch_id" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-success/50 focus:border-success transition-all cursor-pointer shadow-[inset_0_2px_4px_rgba(0,0,0,0.03)]" required>
                        <option value="">-- Pilih Batch Produksi --</option>
                        <template x-for="batch in activeBatches" :key="batch.id">
                            <option :value="batch.id" x-text="batch.kode + ' (' + batch.produk + ')'"></option>
                        </template>
                    </select>
                    <p class="text-[10px] text-muted-foreground">*Hanya batch yang diproses hari ini yang muncul.</p>
                </div>

                <!-- Select Pekerja -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-primary uppercase tracking-widest drop-shadow-sm">Nama Pekerja</label>
                    <select x-model="form.pekerja_id" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-success/50 focus:border-success transition-all cursor-pointer shadow-[inset_0_2px_4px_rgba(0,0,0,0.03)]" required>
                        <option value="">-- Pilih Mahasiswa/Staff --</option>
                        <template x-for="user in pekerjaList" :key="user.id">
                            <option :value="user.id" x-text="user.nama + ' (' + user.role + ')'"></option>
                        </template>
                    </select>
                </div>

                <!-- Select Pekerjaan (Komponen Biaya) -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-primary uppercase tracking-widest drop-shadow-sm">Jenis Pekerjaan</label>
                    <select x-model="form.komponen_id" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-success/50 focus:border-success transition-all cursor-pointer shadow-[inset_0_2px_4px_rgba(0,0,0,0.03)]" required>
                        <option value="">-- Pilih Tugas --</option>
                        <template x-for="tugas in tugasList" :key="tugas.id">
                            <option :value="tugas.id" x-text="tugas.nama + ' (Rp ' + formatRupiah(tugas.tarif) + '/' + tugas.satuan + ')'"></option>
                        </template>
                    </select>
                </div>

                <!-- Input Kuantitas -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-primary uppercase tracking-widest drop-shadow-sm flex justify-between">
                        Jumlah Kerja
                        <span class="text-success bg-success/10 px-1.5 py-0.5 rounded text-[9px]" x-text="selectedSatuan"></span>
                    </label>
                    <input type="number" min="0.1" step="0.1" x-model="form.jumlah" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-xl font-black font-mono text-primary focus:outline-none focus:ring-2 focus:ring-success/50 focus:border-success transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.03)]" placeholder="0" required />
                </div>

                <div class="pt-4">
                    <button type="submit" :disabled="loading || !isFormValid" class="w-full h-14 bg-primary text-primary-foreground font-black text-sm uppercase tracking-widest rounded-xl hover:bg-primary/90 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-md flex justify-center items-center gap-3 group relative overflow-hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span x-text="loading ? 'Menyimpan...' : 'Catat Log Kerja'"></span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Column: Riwayat Hari Ini -->
        <div class="lg:col-span-2">
            <div class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden h-full flex flex-col">
                <div class="p-6 border-b border-border/40 bg-muted/10 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-6 bg-primary rounded-full"></div>
                        <h2 class="text-lg font-bold text-primary">Riwayat Log Hari Ini</h2>
                    </div>
                    <span class="bg-success/10 text-success text-xs font-bold px-3 py-1 rounded-full">
                        <span x-text="history.length"></span> Entri
                    </span>
                </div>
                
                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-muted/30 border-b border-border/50 text-[10px] uppercase tracking-widest text-muted-foreground font-bold">
                                <th class="p-4">Waktu</th>
                                <th class="p-4">ID Batch</th>
                                <th class="p-4">Pekerja</th>
                                <th class="p-4">Tugas</th>
                                <th class="p-4 text-right">Kuantitas</th>
                                <th class="p-4 text-right">Est. Upah</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <template x-if="history.length === 0">
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-muted-foreground">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 opacity-40"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                                        <p class="font-medium">Belum ada pekerja yang dicatat hari ini.</p>
                                    </td>
                                </tr>
                            </template>
                            <template x-for="log in history" :key="log.id">
                                <tr class="hover:bg-muted/10 transition-colors group">
                                    <td class="p-4 text-xs font-mono text-muted-foreground" x-text="log.waktu"></td>
                                    <td class="p-4">
                                        <span class="font-mono font-bold text-primary text-xs bg-muted/50 px-2 py-1 rounded" x-text="log.kode_batch"></span>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-bold text-primary text-sm" x-text="log.nama_pekerja"></p>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2.5 py-1 bg-success/10 text-success text-[10px] font-black rounded-full uppercase tracking-widest" x-text="log.tugas"></span>
                                    </td>
                                    <td class="p-4 text-right font-mono font-bold text-primary text-sm">
                                        <span x-text="log.jumlah"></span> <span class="text-xs text-muted-foreground" x-text="log.satuan"></span>
                                    </td>
                                    <td class="p-4 text-right font-mono font-bold text-success text-sm">
                                        Rp <span x-text="formatRupiah(log.total_upah)"></span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <button @click="deleteLog(log.id)" class="p-1.5 text-muted-foreground hover:text-red-500 bg-transparent hover:bg-red-500/10 rounded-md transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('logKerja', () => ({
            loading: false,
            form: { batch_id: '', pekerja_id: '', komponen_id: '', jumlah: '' },
            history: [],

            activeBatches: [],
            pekerjaList: [],
            tugasList: [],

            async init() {
                await this.fetchData();
            },

            async fetchData() {
                try {
                    const [batchesRes, pekerjaRes, tugasRes, historyRes] = await Promise.all([
                        fetch('/internal-api/log-kerja/batches'),
                        fetch('/internal-api/log-kerja/pekerja'),
                        fetch('/internal-api/log-kerja/tugas'),
                        fetch('/internal-api/log-kerja/history')
                    ]);

                    if (batchesRes.ok) this.activeBatches = await batchesRes.json();
                    if (pekerjaRes.ok) this.pekerjaList = await pekerjaRes.json();
                    if (tugasRes.ok) this.tugasList = await tugasRes.json();
                    if (historyRes.ok) this.history = await historyRes.json();
                } catch (e) {
                    console.error('Error fetching log pekerja data:', e);
                }
            },

            get todayDate() {
                return new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            },

            get selectedSatuan() {
                if(!this.form.komponen_id) return 'Satuan';
                let t = this.tugasList.find(x => x.id === parseInt(this.form.komponen_id));
                return t ? t.satuan : 'Satuan';
            },

            get isFormValid() {
                return this.form.batch_id && this.form.pekerja_id && this.form.komponen_id && this.form.jumlah > 0;
            },

            async submitLog() {
                if (!this.isFormValid) return;
                this.loading = true;
                
                try {
                    const res = await fetch('/internal-api/log-kerja', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.form)
                    });

                    if (res.ok) {
                        await this.fetchData();
                        this.form.pekerja_id = '';
                        this.form.komponen_id = '';
                        this.form.jumlah = '';
                    } else {
                        alert('Gagal mencatat log pekerja.');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan.');
                } finally {
                    this.loading = false;
                }
            },

            async deleteLog(id) {
                if (!confirm('Apakah anda yakin ingin menghapus log ini?')) return;
                try {
                    const res = await fetch(`/internal-api/log-kerja/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });
                    if (res.ok) {
                        await this.fetchData();
                    }
                } catch (e) {
                    alert('Gagal menghapus data.');
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
