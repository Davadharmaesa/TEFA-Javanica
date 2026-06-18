@extends('layouts.app')

@section('title', 'Catatan Kerja - TEFA Javanica')

@section('content')
<div x-data="logKerja()" class="space-y-6 pb-12">

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
            <h1 class="text-3xl font-heading font-black text-primary tracking-tight">Terminal Catatan Kerja</h1>
            <p class="text-muted-foreground font-sans text-sm mt-1">
                Catat siapa saja yang terlibat dalam batch produksi terbaru untuk keperluan HPP & Honor.
            </p>
        </div>
        <div class="text-right">
            <p class="text-2xl font-mono font-black text-primary" x-text="currentTime"></p>
            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest" x-text="currentDate"></p>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- LEFT: Control Panel Form (Spans 7 cols) -->
        <div class="lg:col-span-7 bg-card border border-border shadow-sm rounded-2xl overflow-hidden flex flex-col h-fit">
            <div class="bg-primary px-6 py-4 flex items-center justify-between">
                <h2 class="font-heading font-black text-lg text-primary-foreground tracking-wide flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span x-text="editMode ? 'UPDATE PEKERJAAN BATCH' : 'INPUT PEKERJAAN BATCH'"></span>
                </h2>
                <div class="bg-white/20 text-white text-[10px] uppercase font-bold px-2.5 py-1 rounded-md tracking-widest shadow-inner">Terminal UI</div>
            </div>

            <form @submit.prevent="submitLog" class="p-6 flex-1 flex flex-col gap-6">
                <!-- Data Pokok -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-primary uppercase tracking-widest drop-shadow-sm">Pilih ID Batch</label>
                        <select x-model="form.batch_id" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer" required>
                            <option value="" disabled selected>-- PILIH BATCH --</option>
                            <template x-for="batch in activeBatches" :key="batch.id">
                                <option :value="batch.id" x-text="batch.kode + ' (' + batch.produk + ')'"></option>
                            </template>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-primary uppercase tracking-widest drop-shadow-sm">Nama Pekerja</label>
                        <input type="text" x-model="form.nama_pekerja" placeholder="Contoh: Budi, Siti" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all" required />
                    </div>
                </div>

                <!-- Custom Selectors -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-muted/20 p-5 rounded-xl border border-border/50 mt-2">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-primary uppercase tracking-widest drop-shadow-sm flex items-center justify-between">
                            Jenis Pekerjaan
                            <span class="text-[9px] bg-secondary/10 text-secondary px-1.5 py-0.5 rounded font-black normal-case">Wajib Pilih</span>
                        </label>
                        <select x-model="form.komponen_id" class="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm font-bold text-primary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all cursor-pointer" required>
                            <option value="" disabled selected>-- PILIH TUGAS --</option>
                            <template x-for="tugas in tugasList" :key="tugas.id">
                                <option :value="tugas.id" x-text="tugas.nama + ' (Rp ' + formatRupiah(tugas.harga || tugas.tarif || 0) + '/' + tugas.satuan + ')'"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-primary uppercase tracking-widest drop-shadow-sm flex items-center justify-between">
                            Jumlah Kerja
                            <span class="text-[9px] bg-primary/10 text-primary px-1.5 py-0.5 rounded font-black normal-case tracking-wider uppercase" x-text="'Satuan: ' + selectedSatuan"></span>
                        </label>
                        <!-- REFAKTOR: Input angka diperbesar, tidak ada default 0, spinner hilang -->
                        <input type="number" min="0.1" step="0.1" x-model="form.jumlah" @wheel="$event.target.blur()" class="w-full bg-background border-2 border-border/80 rounded-xl px-4 py-2.5 text-xl font-mono font-black text-primary text-center focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none placeholder:text-muted/50 placeholder:text-sm placeholder:font-sans" placeholder="Ketik jumlah..." required />
                    </div>
                </div>

                <div class="pt-4 mt-auto flex gap-3">
                    <button type="submit" :disabled="loading || !isFormValid" class="flex-1 h-14 bg-primary text-primary-foreground font-black text-sm uppercase tracking-widest rounded-xl hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-[0_8px_20px_rgba(200,80,50,0.15)] flex justify-center items-center gap-3 hover:-translate-y-0.5 active:translate-y-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span x-show="!loading" x-text="editMode ? 'Update Data Pekerja' : 'Tambahkan Data'"></span>
                        <span x-show="loading" class="animate-pulse">Menyimpan...</span>
                    </button>
                    <button type="button" x-show="editMode" @click="cancelEdit" class="h-14 px-6 bg-muted/50 border border-border text-muted-foreground font-black text-sm uppercase tracking-widest rounded-xl hover:bg-muted transition-all">Batal</button>
                </div>
            </form>
        </div>

        <!-- RIGHT: History Panel (Spans 5 cols) -->
        <div class="lg:col-span-5 bg-card border border-border shadow-sm rounded-2xl p-0 flex flex-col overflow-hidden">
            <div class="bg-primary/5 border-b border-border/50 p-5 flex justify-between items-center z-10 relative overflow-hidden">
                <div>
                    <h2 class="text-lg font-heading font-black text-primary tracking-tight">Catatan Kerja Terkini</h2>
                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-0.5">Histori Operasional Terbaru</p>
                </div>
                <div class="bg-primary/10 text-primary p-2 rounded-xl border border-primary/20 relative z-10 flex flex-col items-center justify-center min-w-[50px]">
                    <span class="text-lg font-black font-mono leading-none" x-text="history.length"></span>
                    <span class="text-[8px] font-bold uppercase tracking-widest mt-1">Entri</span>
                </div>
            </div>

            <!-- List container -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar min-h-[500px] max-h-[850px] bg-background">
                <template x-for="log in history" :key="log.id">
                    <div class="bg-card border-l-4 border-l-secondary border border-border rounded-xl p-4 shadow-sm group hover:bg-muted/10 transition-colors relative overflow-hidden">
                        
                        <!-- Action Buttons -->
                        <div class="absolute top-3 right-3 flex gap-1 md:opacity-0 group-hover:opacity-100 opacity-100 transition-opacity">
                            <button @click="editLog(log)" class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" title="Edit Log">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </button>
                            <button @click="deleteLog(log.id)" class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus Log">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </button>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1.5 pr-16">
                                <span class="font-mono font-black text-primary text-sm uppercase truncate group-hover:text-secondary transition-colors" x-text="log.kode_batch"></span>
                                <span class="font-mono text-[9px] font-bold px-1.5 py-0.5 rounded border border-border/50 bg-muted/30 text-muted-foreground flex-shrink-0" x-text="log.waktu"></span>
                            </div>
                            
                            <p class="font-sans font-black text-primary text-xs mb-1 truncate" x-text="log.nama_pekerja"></p>
                            <p class="font-sans font-medium text-primary/70 text-[10px] mb-3 truncate" x-text="'Role: ' + (log.role_pekerja || 'Pekerja Manual')"></p>
                            
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <div class="bg-muted/40 border border-border/60 rounded-lg p-2 text-center group-hover:bg-muted/60 transition-colors">
                                    <p class="text-[9px] text-primary/60 font-black uppercase mb-0.5">Tugas</p>
                                    <p class="text-xs font-bold text-primary/80 truncate text-ellipsis uppercase" x-text="log.tugas"></p>
                                </div>
                                <div class="bg-secondary/10 border border-secondary/20 rounded-lg p-2 text-center group-hover:bg-secondary/20 transition-colors">
                                    <p class="text-[9px] text-secondary/80 font-black uppercase mb-0.5">Upah Estimasi</p>
                                    <p class="text-xs font-black text-secondary font-mono" x-text="'Rp ' + formatRupiah(log.total_upah)"></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-1.5 text-[10px] font-mono font-bold text-primary">
                                <span class="bg-background px-1.5 py-0.5 border border-border/80 rounded" x-text="'JML: ' + log.jumlah + ' ' + log.satuan"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="history.length === 0">
                    <div class="h-full min-h-[300px] flex flex-col items-center justify-center text-muted py-12 text-center px-4">
                        <div class="w-16 h-16 rounded-full bg-muted/30 flex items-center justify-center mb-4 ring-4 ring-muted/10 text-muted-foreground">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <p class="font-sans text-xs uppercase font-black tracking-widest text-muted-foreground">Catatan Kosong</p>
                        <p class="text-[10px] text-muted-foreground/70 mt-2 max-w-[200px] leading-relaxed font-medium">Belum ada catatan kerja yang terekam.</p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('logKerja', () => ({
            loading: false,
            form: { batch_id: '', nama_pekerja: '', komponen_id: '', jumlah: '' },
            history: [],
            editMode: false,
            editId: null,

            activeBatches: [],
            tugasList: [],
            timeObj: new Date(),

            async init() {
                await this.fetchData();
                setInterval(() => { this.timeObj = new Date(); }, 1000);
            },

            async fetchData() {
                try {
                    const [batchesRes, tugasRes, historyRes] = await Promise.all([
                        fetch('/internal-api/log-kerja/batches'),
                        fetch('/internal-api/log-kerja/tugas'),
                        fetch('/internal-api/log-kerja/history')
                    ]);

                    if (batchesRes.ok) this.activeBatches = await batchesRes.json();
                    if (tugasRes.ok) this.tugasList = await tugasRes.json();
                    if (historyRes.ok) this.history = await historyRes.json();
                } catch (e) {
                    console.error('Error fetching log pekerja data:', e);
                }
            },

            get currentTime() {
                return this.timeObj.toLocaleTimeString('id-ID', { hour12: false });
            },

            get currentDate() {
                return this.timeObj.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            },

            get selectedSatuan() {
                if(!this.form.komponen_id) return 'Satuan';
                let t = this.tugasList.find(x => x.id === parseInt(this.form.komponen_id));
                return t ? t.satuan : 'Satuan';
            },

            get isFormValid() {
                return this.form.batch_id && this.form.nama_pekerja && this.form.komponen_id && this.form.jumlah > 0;
            },

            editLog(log) {
                this.editMode = true;
                this.editId = log.id;
                this.form = {
                    batch_id: log.batch_id,
                    nama_pekerja: log.nama_pekerja,
                    komponen_id: log.komponen_id,
                    jumlah: log.jumlah
                };
            },

            cancelEdit() {
                this.editMode = false;
                this.editId = null;
                this.form = { batch_id: '', nama_pekerja: '', komponen_id: '', jumlah: '' };
            },

            async submitLog() {
                if (!this.isFormValid) return;
                this.loading = true;
                
                try {
                    const url = this.editMode ? `/internal-api/log-kerja/${this.editId}` : '/internal-api/log-kerja';
                    const method = this.editMode ? 'PUT' : 'POST';

                    const res = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.form)
                    });

                    if (res.ok) {
                        await this.fetchData();
                        this.cancelEdit();
                    } else {
                        alert(this.editMode ? 'Gagal memperbarui catatan kerja.' : 'Gagal mencatat catatan kerja.');
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