# TEFA Javanica API

## Persyaratan Sistem (Environment)
- PHP **8.3** atau lebih baru
- MySQL / MariaDB

## Panduan Instalasi Lokal (Laragon)

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di mesin lokal teman-teman:

1. **Clone Repository (Buka Terminal di dalam folder C:\laragon\www)**
   ```bash
   git clone <link-repo-ini>
   cd TEFA-Javanica
   ```

2. **Install Dependensi**
   Jalankan composer untuk mengunduh semua package. (Jangan gunakan `update`).
   ```bash
   composer install
   ```

3. **Setup Konfigurasi Environment**
   Salin file `.env.example` menjadi `.env`, lalu buat Application Key.
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

4. **Setup Database**
   - Buka HeidiSQL / phpMyAdmin di Laragon.
   - Buat database baru bernama `tefa_roastery_db`
   - Buka file `.env` dan pastikan konfigurasi databasemu sesuai:
     ```env
     DB_CONNECTION=mysql
     DB_DATABASE=tefa_roastery_db
     DB_USERNAME=root
     DB_PASSWORD=
     ```

5. **Migrasi Database**
   Tarik tabel-tabel database ke dalam MySQL.
   ```bash
   php artisan migrate
   ```

6. **Jalankan Aplikasi**
   Nyalaan server *backend* secara lokal.
   ```bash
   php artisan serve
   ```
   
Selesai! API Backend TEFA Javanica sudah berjalan.
