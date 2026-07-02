# Sistem Perhitungan Costing dan Laporan Keuangan TEFA Javanica

## Deskripsi Proyek
Sistem Perhitungan Costing dan Laporan Keuangan TEFA Javanica adalah aplikasi yang dikembangkan untuk membantu pengelolaan biaya produksi (*costing*) dan penyusunan laporan keuangan pada unit usaha Roastery. Sistem ini bertujuan meningkatkan efisiensi pengelolaan data, mempermudah proses perhitungan biaya, serta menyediakan informasi keuangan yang lebih terstruktur dan akurat.

## Quick Start

Untuk segera menjalankan aplikasi:

```bash
# 1. Clone & install
git clone <repo-url> && cd TEFA-Javanica
composer install && npm install

# 2. Setup environment
cp .env.example .env && php artisan key:generate

# 3. Setup database (pastikan database tefa_roastery_db sudah ada)
php artisan migrate --seed

# 4. Jalankan aplikasi
php artisan serve  # Terminal 1
npm run dev        # Terminal 2
```

Akses: http://localhost:8000 dengan akun demo.

## Fitur Utama
- Autentikasi pengguna (termasuk fitur *two-factor authentication*).
- Manajemen Data: CRUD GreenBean, RoastedBean, Packaging, dan Biaya.
- Manajemen stok dan *roasting batch*.
- Perhitungan HPP (*cost calculation*) otomatis.
- Integrasi layanan WhatsApp 
- API endpoint untuk kebutuhan integrasi frontend.

## Tech Stack
- **Framework:** Laravel 11.x
- **Language:** PHP 8.3
- **Database:** MySQL / MariaDB (Eloquent ORM)
- **Frontend Assets:** Vite + Node.js
- **Testing:** PHPUnit

## Prasyarat
- PHP >= 8.3
- Composer
- Node.js & npm / yarn
- MySQL / MariaDB
- Ekstensi PHP: `pdo`, `mbstring`, `openssl`, `fileinfo`, `xml`

## Panduan Instalasi & Menjalankan (Development)

1. **Clone repository:**

```bash
git clone <repo-url>
cd TEFA-Javanica
```

2. **Install dependencies:**

```bash
composer install
npm install
```

3. **Konfigurasi Environment:**

```bash
copy .env.example .env      
# atau
cp .env.example .env        

php artisan key:generate
```

4. **Migrasi Database:**

```bash
php artisan migrate --seed
```

5. **Jalankan server:**

```bash
php artisan serve
npm run dev
```

## Menjalankan Test

```bash
php artisan test
```

## Struktur Folder

- `app/` — Models, Controllers, Services, Middleware
- `routes/` — `api.php`, `web.php`
- `database/` — migrations & seeders
- `resources/` — views, css, js
- `public/` — entrypoint `index.php`
- `tests/` — unit & feature tests

### Akun Demo (Development Only)

- **PIC:** `buastri@tefa.com` / `kopi123`
- **Operator:** `pakdeka@tefa.com` / `kopi123`

### Contoh Struktur `.env`

```ini
APP_NAME=Javanica
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tefa_roastery_db
DB_USERNAME=root
DB_PASSWORD=

WA_GATEWAY_URL=http://wa-gateway.local
MAIL_MAILER=smtp
```

## Integrasi WhatsApp

Sistem ini dilengkapi dengan integrasi WhatsApp untuk notifikasi pesanan dan laporan otomatis.

### File-file WhatsApp:
- `wa-gateway.js` — Gateway server untuk WhatsApp (menggunakan Baileys)
- `wa-mock.cjs` — Mock/testing untuk WhatsApp service (development only)
- `app/Services/WhatsappService.php` — Service PHP yang mengintegrasikan WhatsApp

### Setup WhatsApp Gateway:
1. Pastikan Node.js sudah terinstall
2. Install dependencies WhatsApp:
   ```bash
   npm install
   ```
3. Jalankan wa-gateway (jika diperlukan):
   ```bash
   node wa-gateway.js
   ```

## Fitur Detail

### 1. Autentikasi & Keamanan
- Two-Factor Authentication (2FA) menggunakan Google Authenticator
- API Token via Sanctum
- Role-based access control

### 2. Manajemen Data Produksi
- **Green Bean (Biji Hijau):** Manajemen biji kopi mentah dan stoknya
- **Roasted Bean (Biji Roasted):** Tracking biji setelah di-roast
- **Roasting Batch:** Pengelompokan proses roasting
- **Packaging:** Manajemen produk packaging kopi

### 3. Perhitungan Biaya (HPP - Harga Pokok Produksi)
- Kalkulasi otomatis biaya produksi per unit
- Tracking biaya operasional dan bahan baku
- Report keuangan terstruktur

### 4. Pesanan Keluar
- Sistem pencatatan pesanan/order yang keluar
- Integrasi WhatsApp untuk notifikasi pesanan

## Deployment

Untuk production:

```bash
# Update .env
APP_ENV=production
APP_DEBUG=false

# Optimasi & build
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache && php artisan route:cache

# Setup web server ke folder public/
```

## Kontributor / Anggota Kelompok
- Deva David Dharmaesa - 362458302024
- M. Habibulloh - 362458302117 
- Moh. Zulqornain Rofiqi - 362458302086 
- Viona Dhini Olivia - 362458302031 
- Intan Nur Annisa - 362458302083