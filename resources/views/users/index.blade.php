@extends('layouts.app')

@section('title', 'Kelola User - TEFA Javanica')

@section('content')
<div x-data="userManagementData()" class="space-y-6 pb-12">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-card p-6 rounded-2xl shadow-sm border border-border/40 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-24 h-24 bg-primary/5 rounded-bl-full pointer-events-none group-hover:scale-110 transition-transform duration-500"></div>
        <div>
            <h1 class="text-2xl font-heading font-bold text-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
                Kelola Pengguna Aplikasi
            </h1>
            <p class="text-sm text-muted-foreground mt-1">Daftarkan, edit, dan atur hak akses pengguna (PIC, Operator, Mahasiswa Magang).</p>
        </div>
        <div>
            <button @click="openAddModal()" class="bg-primary text-primary-foreground hover:bg-primary/90 px-5 py-2.5 rounded-xl text-sm font-bold shadow-md transition-all flex items-center gap-2 hover:scale-105 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah User
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 bg-secondary/10 border border-secondary/20 text-secondary rounded-xl font-bold flex items-center gap-2 shadow-sm animate-fade-in">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-600 rounded-xl font-bold flex items-center gap-2 shadow-sm animate-fade-in">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-600 rounded-xl font-bold space-y-1 shadow-sm">
            <p class="text-sm font-black flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> GAGAL MENYIMPAN DATA:</p>
            <ul class="list-disc pl-6 text-xs font-medium space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Statistics Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Stat Card 1: Total Users -->
        <div class="bg-card border border-border/40 rounded-2xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide leading-tight">Total Pengguna</p>
            <p class="text-2xl font-black font-mono text-primary leading-none mt-1">{{ $totalUsers }}</p>
            <p class="text-[10px] text-muted-foreground mt-0.5">User</p>
        </div>

        <!-- Stat Card 2: PIC -->
        <div class="bg-card border border-border/40 rounded-2xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-red-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-9 h-9 rounded-xl bg-red-500/10 flex items-center justify-center text-red-500 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide leading-tight">Role PIC</p>
            <p class="text-2xl font-black font-mono text-primary leading-none mt-1">{{ $totalPic }}</p>
            <p class="text-[10px] text-muted-foreground mt-0.5">User</p>
        </div>

        <!-- Stat Card 3: Operator -->
        <div class="bg-card border border-border/40 rounded-2xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-secondary to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-9 h-9 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            </div>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide leading-tight">Role Operator</p>
            <p class="text-2xl font-black font-mono text-primary leading-none mt-1">{{ $totalOperator }}</p>
            <p class="text-[10px] text-muted-foreground mt-0.5">User</p>
        </div>

        <!-- Stat Card 4: Mahasiswa Magang -->
        <div class="bg-card border border-border/40 rounded-2xl p-4 shadow-sm relative overflow-hidden group">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-accent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-9 h-9 rounded-xl bg-accent/10 flex items-center justify-center text-accent mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 2.5 3 6 3s6-1 6-3v-5"/></svg>
            </div>
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide leading-tight">Role Magang</p>
            <p class="text-2xl font-black font-mono text-primary leading-none mt-1">{{ $totalMagang }}</p>
            <p class="text-[10px] text-muted-foreground mt-0.5">User</p>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="bg-card border border-border/40 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border/50 bg-primary/5">
            <h2 class="text-lg font-heading font-bold text-primary">Daftar Pengguna Aktif</h2>
            <p class="text-xs text-muted-foreground mt-1">Daftar lengkap pengguna yang berhak login ke sistem TEFA Javanica.</p>
        </div>
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/30 border-b border-border/50 text-xs uppercase tracking-wider text-muted-foreground font-bold">
                        <th class="p-4 pl-6">Nama Pengguna</th>
                        <th class="p-4">Alamat Email</th>
                        <th class="p-4 text-center">Hak Akses / Role</th>
                        <th class="p-4">Terdaftar Sejak</th>
                        <th class="p-4 text-center pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/30">
                    @foreach($users as $user)
                        <tr class="hover:bg-muted/10 transition-colors group">
                            <td class="p-4 pl-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm shrink-0 uppercase border border-primary/20">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-foreground group-hover:text-primary transition-colors">{{ $user->name }}</p>
                                        @if(auth()->id() === $user->id)
                                            <span class="inline-flex text-[9px] font-black uppercase tracking-widest bg-secondary/20 text-secondary px-1.5 py-0.5 rounded leading-none mt-1">Sedang Aktif</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="text-sm font-mono font-medium text-muted-foreground">{{ $user->email }}</span>
                            </td>
                            <td class="p-4 text-center">
                                @if($user->role === 'PIC')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-red-500/10 text-red-600 border border-red-500/20 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> PIC
                                    </span>
                                @elseif($user->role === 'Operator')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-secondary/10 text-secondary border border-secondary/20 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span> OPERATOR
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-accent/10 text-accent border border-accent/20 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-accent"></span> MAGANG
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-sm font-mono font-medium text-muted-foreground">
                                {{ $user->created_at->format('d M Y - H:i') }}
                            </td>
                            <td class="p-4 text-center pr-6">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Edit button -->
                                    <button 
                                        @click="openEditModal({{ json_encode($user) }})" 
                                        class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" 
                                        title="Ubah Profil / Password User"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>

                                    <!-- Delete button -->
                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda benar-benar yakin ingin menghapus user ini? Akun tersebut tidak akan dapat digunakan lagi.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus User">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            </button>
                                        </form>
                                    @else
                                        <button class="p-1.5 text-muted-foreground/30 cursor-not-allowed" title="Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif" disabled>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-50"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Mobile Card List for Users -->
        <div class="grid grid-cols-1 gap-4 p-4 md:hidden bg-muted/10">
            @foreach($users as $user)
                <div class="bg-card border border-border/40 rounded-xl p-4 space-y-3 shadow-sm relative">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm shrink-0 uppercase border border-primary/20">
                            {{ substr($user->name, 0, 2) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="font-bold text-foreground truncate text-sm">{{ $user->name }}</h4>
                                @if($user->role === 'PIC')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-red-500/10 text-red-600 border border-red-500/20">
                                        PIC
                                    </span>
                                @elseif($user->role === 'Operator')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-secondary/10 text-secondary border border-secondary/20">
                                        OPERATOR
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-accent/10 text-accent border border-accent/20">
                                        MAGANG
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs font-mono text-muted-foreground truncate mt-0.5">{{ $user->email }}</p>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center text-xs border-t border-border/20 pt-2.5">
                        <div>
                            <p class="text-[10px] text-muted-foreground font-semibold">Terdaftar Sejak</p>
                            <p class="text-[11px] font-mono text-foreground mt-0.5">{{ $user->created_at->format('d M Y - H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(auth()->id() === $user->id)
                                <span class="inline-flex text-[9px] font-black uppercase tracking-widest bg-secondary/20 text-secondary px-1.5 py-0.5 rounded leading-none mr-1">Aktif</span>
                            @endif
                            <!-- Edit button -->
                            <button 
                                @click="openEditModal({{ json_encode($user) }})" 
                                class="p-1.5 text-[#8A6B4E] hover:text-[#526B42] hover:bg-[#526B42]/10 rounded-md transition-colors" 
                                title="Ubah Profil / Password User"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </button>

                            <!-- Delete button -->
                            @if(auth()->id() !== $user->id)
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda benar-benar yakin ingin menghapus user ini? Akun tersebut tidak akan dapat digunakan lagi.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-[#8B3A3A] hover:text-red-600 hover:bg-red-600/10 rounded-md transition-colors" title="Hapus User">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </form>
                            @else
                                <button class="p-1.5 text-muted-foreground/30 cursor-not-allowed" title="Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-50"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modal Form Tambah/Edit User -->
    <div x-show="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-transition>
        <div @click.away="isModalOpen = false" class="bg-card w-full max-w-lg rounded-2xl shadow-xl border border-border/40 p-6 relative overflow-hidden my-auto">
            <!-- Modal Header -->
            <div class="flex justify-between items-center mb-6 border-b border-border/50 pb-4">
                <h2 class="text-xl font-heading font-bold text-primary flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
                    <span x-text="isEditMode ? 'Edit Pengguna' : 'Tambah Pengguna Baru'"></span>
                </h2>
                <button @click="isModalOpen = false" class="text-muted-foreground hover:text-foreground transition-colors p-1.5 hover:bg-muted/50 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <!-- Modal Form -->
            <form :action="getFormAction()" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEditMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Input Name -->
                <div>
                    <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1.5">Nama Lengkap</label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="formData.name" 
                        required 
                        placeholder="Contoh: Nain Habib" 
                        class="w-full bg-background border border-border/60 rounded-xl px-4 py-2.5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all"
                    >
                </div>

                <!-- Input Email -->
                <div>
                    <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1.5">Alamat Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        x-model="formData.email" 
                        required 
                        placeholder="Contoh: user@javanica.com" 
                        class="w-full bg-background border border-border/60 rounded-xl px-4 py-2.5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all font-mono"
                    >
                </div>

                <!-- Dropdown Role -->
                <div>
                    <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1.5">Pilih Role / Hak Akses</label>
                    <select 
                        name="role" 
                        x-model="formData.role" 
                        required 
                        class="w-full bg-background border border-border/60 rounded-xl px-4 py-2.5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih hak akses...</option>
                        <option value="PIC">PIC (Administrator Utama)</option>
                        <option value="Operator">Operator (Staf Pengolahan)</option>
                        <option value="Mahasiswa Magang">Mahasiswa Magang (Staf Lapangan)</option>
                    </select>
                </div>

                <!-- Input Password -->
                <div>
                    <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1.5">Kata Sandi (Password)</label>
                    <input 
                        type="password" 
                        name="password" 
                        :required="!isEditMode" 
                        placeholder="Minimal 6 karakter" 
                        class="w-full bg-background border border-border/60 rounded-xl px-4 py-2.5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground transition-all font-mono"
                    >
                    <p class="text-[10px] text-muted-foreground mt-1.5 italic" x-show="isEditMode">
                        *Kosongkan kolom sandi jika tidak ingin memperbarui password saat ini.
                    </p>
                </div>

                <!-- Form Action Buttons -->
                <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-border/30">
                    <button type="button" @click="isModalOpen = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-muted-foreground hover:bg-muted/50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-bold hover:bg-primary/90 shadow-md transition-all hover:scale-105 active:scale-95 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('userManagementData', () => ({
            isModalOpen: false,
            isEditMode: false,
            editId: null,
            formData: { name: '', email: '', role: '' },

            openAddModal() {
                this.isEditMode = false;
                this.editId = null;
                this.formData = { name: '', email: '', role: '' };
                this.isModalOpen = true;
            },

            openEditModal(userObj) {
                this.isEditMode = true;
                this.editId = userObj.id;
                this.formData = {
                    name: userObj.name,
                    email: userObj.email,
                    role: userObj.role
                };
                this.isModalOpen = true;
            },

            getFormAction() {
                if (this.isEditMode && this.editId) {
                    return '/users/' + this.editId;
                }
                return '/users';
            }
        }));
    });
</script>
@endpush
@endsection