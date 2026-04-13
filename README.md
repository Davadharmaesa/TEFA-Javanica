<div align="center">
  <img src="https://laravel.com/img/logomark.min.svg" width="100" alt="Laravel Logo">
  <h1>☕ TEFA Javanica Roastery API</h1>
  <p>Sistem ERP Backend untuk mengelola Stok Kopi, Produksi Roasting, dan Kasir Timbangan di Lingkungan Kampus.</p>

  [![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net/)
  [![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com/)
  [![Laragon](https://img.shields.io/badge/Environment-Laragon-orange.svg)](https://laragon.org/)
</div>

---

## 👥 Pembagian Tugas Tim (Task Distribution)

Agar pengembangan sistem berjalan lancar dan terarah, berikut adalah pembagian tanggung jawab masing-masing *developer* untuk sprint kali ini:

| Nama / Peran | Fokus Pengerjaan | Modul Target | Status |
| :--- | :--- | :--- | :---: |
| **Pimpinan Proyek & Teman** | Koding Arsitektur & Logika Bisnis | ✅ Algoritma HPP<br>✅ Pemotongan Stok Timbangan<br>✅ Relasi Database Backend | 🚧 *In Progress* |
| **Anggota Tim Design** | UI/UX & Integrasi Frontend | ✅ Tampilan Next.js<br>✅ Routing Halaman Navigasi<br>✅ Mockup Interaktif | 🚧 *In Progress* |

> **Catatan Penting:** Pembuatan kerangka controller dan API Route sudah di-*scaffold* secara otomatis. Tim algoritma bisa fokus langsung ke blok koding `// TODO` di dalam controller yang ada.

---

## 🚀 Panduan Setup Lokal (Wajib Baca)

**⚠️ DILARANG KERAS MENGUBAH VERSI PHP SEMBARANGAN!** Semua anggota tim harus menyamakan spesifikasi di aplikasi Laragon-nya masing-masing.

### Syarat Perangkat (Laragon):
- PHP versi **8.2** (Atau samakan dengan versi yang dipakai Lead Project)
- MySQL **8+** atau MariaDB bawaan Laragon
- Composer yang sudah terintegrasi

### Instalasi & Menjalankan Mesin:
1. Pindah ke direktori web Laragon-mu:
   ```bash
   cd C:\laragon\www
   ```
2. Tarik kode alias *clone repository* ini.
3. Masuk ke dalam folder proyek:
   ```bash
   cd TEFA-Javanica-Roastery
   ```
4. Tarik seluruh *package* pihak ketiga **(JANGAN PERNAH GUNAKAN `composer update`)**:
   ```bash
   composer install
   ```
5. Siapkan konfigurasi rahasia:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
6. Cek HeidiSQL / phpMyAdmin bawaan Laragon, pastikan database bernama `tefa_roastery_db` sudah terbuat. Cek juga pengaturan `.env` mu:
   ```env
   DB_CONNECTION=mysql
   DB_DATABASE=tefa_roastery_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
7. Impor semua tabel database:
   ```bash
   php artisan migrate
   ```
8. Nyalakan mesin server:
   ```bash
   php artisan serve
   ```

*Happy Coding! Silakan tanyakan ke Lead Project jika mengalami server Error 500.*
