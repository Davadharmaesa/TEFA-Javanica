# 🚀 PANDUAN SETUP LARAGON UNTUK TIM KODING
> "Baca ini sebelum ngoding, biar nggak hancur beda versi!" 

## 1. Standar Spesifikasi (WAJIB SAMA)
Karena kita pakai **Laragon**, pastikan semua anggota tim punya spek lingkungan yang **SAMA PERSIS** biar nggak terjadi *Vibe Check Failed* atau *Error 500*:

- **PHP Version :** Minimal **PHP 8.2** (Atau samakan dengan versi PHP di Laragon milik PM).
- **Database** : MySQL 8+ atau MariaDB bawaan Laragon.
- **Node.js** : Versi 18 LTS atau 20 LTS (Untuk frontend).

## 2. Langkah-Langkah Clone & Setup Backend
Jangan asal run, ikuti urutan mantra ini dari awal sampai akhir:

1. **Buka Terminal Laragon** lalu pindah ke folder `C:\laragon\www`
2. **Clone repo ini** dari github.
3. Masuk ke folder repo: `cd TEFA-Javanica-Roastery`
4. Jalankan maut ini: **`composer install`** 
   *(INGAT: Jangan pernah jalankan `composer update`, biarkan file `composer.lock` yang mengontrol keharmonisan versi kita).*
5. Salin variabel environment: **`cp .env.example .env`**
6. Buat Kunci Rahasia: **`php artisan key:generate`**
7. Buka HeidiSQL / phpMyAdmin bawaan Laragon, buat database kosong bernama: `tefa_roastery_db`
8. Buka file `.env` di VS Code, cocokkan nama databasenya:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=tefa_roastery_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
9. **Tarik Struktur Tabel**: `php artisan migrate`

## 3. Menyalakan Mesin
- Nyalakan Server API: **`php artisan serve`**
- Nyalakan Server Frontend (kalau digabung/praktek): `npm run dev`

---
*Dibuat oleh Tech Lead (PM)*
