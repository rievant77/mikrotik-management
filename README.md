# 🚀 MikroTik Hotspot, Bandwidth & POS Management System

Aplikasi manajemen jaringan **MikroTik Hotspot**, kontrol **Bandwidth & FUP (Fair Usage Policy)**, **Point of Sale (POS) Kasir Voucher**, dan **Billing Tagihan Pelanggan Bulanan** berbasis modern web (Laravel 12, Tailwind CSS & Alpine.js).

---

## 🌟 Fitur-Fitur Unggulan

1. **📊 Executive Dashboard & Real-Time Traffic**:
   - Monitoring traffic upload/download live secara real-time.
   - Grafik riwayat penggunaan bandwidth harian, mingguan, dan bulanan.
   - Status router (CPU Load, RAM, Uptime, Suhu, dan Voltase).
   - Daftar 5 konsumen bandwidth tertinggi (*Top Bandwidth Consumers*).

2. **👥 Manajemen Pengguna Hotspot & Perangkat**:
   - Pantau pengguna aktif secara real-time (*Online Users*).
   - Fitur pemutus koneksi (*Kick/Disconnect user*) langsung ke MikroTik.
   - Live Devices & DHCP Leases (Monitoring IP, MAC Address, dan Hostname).

3. **🎫 Pembuatan & Cetak Voucher Massal**:
   - Generate voucher massal dengan kombinasi karakter huruf/angka yang fleksibel.
   - Dukungan **Barcode & QR Code** untuk kemudahan login pelanggan (Scan to Login).
   - Cetak langsung ke printer thermal (*Thermal Print Format 58mm & 80mm*) dan layout kertas A4.
   - Kustomisasi template voucher (Logo kustom, slogan, header, dan footer).

4. **⚡ FUP (Fair Usage Policy) & Bandwidth Limiting**:
   - Batas kuota harian/bulanan dengan penurunan kecepatan otomatis saat kuota FUP habis.
   - Pemulihan kecepatan (*Restore Rate Limit*) otomatis melalui scheduler atau reset manual.

5. **💰 POS Kasir, Omzet & Analisis Laba Bersih**:
   - Kasir penjualan voucher hotspot dengan pencatatan shift kasir.
   - Perhitungan **Omzet (Gross Revenue)**, **Harga Modal (Cost Price)**, dan **Laba Bersih (Net Profit)**.
   - Dashboard analitik finansial dengan grafik tren arus kas (ApexCharts).

6. **🧾 Tagihan Bulanan (*Monthly Recurring Billing*) & Cicilan**:
   - Manajemen pelanggan tetap/rumahan dengan siklus tagihan bulanan otomatis.
   - Pembuatan invoice massal (*Batch Invoice Generation*).
   - Pencatatan pembayaran lunas maupun **cicilan bertahap (*partial installments*)**.
   - Cetak struk/invoice tagihan bulanan.

7. **🎨 Kustomisasi Profil Admin, Identitas & Icon Aplikasi**:
   - Ganti Nama Admin, Email, dan Foto Profil (*Avatar*).
   - Ganti Kata Sandi (*Password*) dengan verifikasi keamanan password lama.
   - Kustomisasi Nama Aplikasi (*App Name*), Tagline, dan Kontak WhatsApp Support.
   - Upload Logo/Ikon Aplikasi dan Favicon Browser kustom.

8. **🔍 Pencarian Cepat Global / Command Palette (`Ctrl + K`)**:
   - Cari data user hotspot, alamat IP, MAC address, invoice, atau menu navigasi secara instan dari halaman mana saja.

9. **🛠️ Pemeliharaan & Reset Data Sistem**:
   - Reset modular data penjualan, user hotspot di MikroTik, counter internet, atau log audit dengan modal konfirmasi keamanan.

---

## 🛠️ Persyaratan Sistem (*Prerequisites*)

Sebelum menjalankan aplikasi, pastikan server atau komputer Anda telah terpasang:

- **PHP**: Versi `>= 8.2` (dengan ekstensi: `pdo`, `sqlite` / `pdo_mysql`, `curl`, `mbstring`, `gd`, `openssl`, `tokenizer`, `xml`)
- **Composer**: Dependency Manager untuk PHP
- **Node.js & NPM**: Versi `>= 18.x` (untuk kompilasi Vite & Tailwind CSS)
- **MikroTik RouterOS**: Versi `6.x` atau `7.x` dengan service **API** aktif (`port 8728` atau `8729` untuk SSL).

---

## 🚀 Panduan Instalasi Cepat (*Quick Start*)

### 1. Clone & Masuk ke Direktori Proyek
```bash
cd /var/www/html/mikrotik-management
```

### 2. Konfigurasi File Environment (`.env`)
Salin file contoh konfigurasi dan sesuaikan pengaturan database serta aplikasi:
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Install Dependencies PHP & JavaScript
```bash
composer install --no-dev --optimize-autoloader
npm install
```

### 4. Jalankan Migrasi Database & Seeder
```bash
php artisan migrate --force
```

### 5. Compile Aset Frontend
```bash
npm run build
```

### 6. Jalankan Server Web
Jika menggunakan PHP built-in server untuk pengembangan:
```bash
php artisan serve --port=8000
```
Buka browser dan akses: `http://localhost:8000` (Login default: `admin@example.com` / password yang dikonfigurasi).

---

## 🐳 Menjalankan dengan Docker (Tanpa MySQL / SQLite)

Aplikasi ini sudah dilengkapi dengan konfigurasi **Docker** mandiri yang sangat ringan (*Alpine Linux* + *Nginx* + *PHP-FPM* + *Supervisor* + *SQLite* + *Background Scheduler otomatis*).

### 1. Jalankan Container dengan Docker Compose
Cukup jalankan satu perintah berikut di terminal:
```bash
docker compose up -d --build
```

### 2. Akses Aplikasi
Buka browser di:
```text
http://localhost:8085
```

### 3. Keunggulan Setup Docker Ini:
- 💡 **Tanpa Perlu Database MySQL**: Menggunakan SQLite persisten di folder `./database/database.sqlite`.
- ⏰ **Otomatis Menjalankan Scheduler**: Supervisord di dalam container otomatis mengeksekusi `php artisan schedule:run` setiap menit 24/7.
- 💾 **Data Persisten**: Seluruh data database SQLite, foto/logo di `./public/uploads`, dan log di `./storage` tetap tersimpan aman di server host saat container di-restart.

### Perintah Berguna Docker:
```bash
# Melihat log aplikasi secara live
docker compose logs -f

# Menghentikan container
docker compose down

# Masuk ke terminal container
docker compose exec app sh
```

---

## 🌐 Konfigurasi Koneksi Router MikroTik

### 1. Aktifkan Service API di MikroTik RouterOS
Buka terminal **WinBox** atau **SSH MikroTik**, lalu jalankan:
```routeros
/ip service enable api
/ip service set api port=8728
```
*(Opsional: Jika menggunakan API SSL, aktifkan service `api-ssl` pada port `8729`).*

### 2. Hubungkan Melalui Halaman Web Aplikasi
1. Buka menu **Pengaturan $\rightarrow$ Router Settings** (`/settings/router`).
2. Masukkan parameter koneksi:
   - **IP / Host Router**: Contoh `192.168.88.1`
   - **Port API**: `8728`
   - **Username**: Username admin MikroTik (misal: `admin`)
   - **Password**: Password admin MikroTik
3. Klik tombol **"Tes Koneksi"** untuk memverifikasi.
4. Klik **"Simpan & Hubungkan"**, lalu klik **"Sinkronkan Semua Data"**.

---

## ⏰ Panduan Setup Background Task Scheduler

Aplikasi ini menggunakan **Laravel Scheduler** untuk menjalankan proses penting di latar belakang secara 24/7:
- **`mikrotik:collect`** (Setiap Menit): Mengambil data sesi aktif, mencatat login/logout, menghitung delta pemakaian kuota, dan merekam penjualan voucher secara otomatis.
- **`mikrotik:fup-reset --cycle=daily`** (Setiap Hari Pukul 00:00): Me-reset pemakaian kuota FUP harian dan mengembalikan kecepatan bandwidth normal.
- **`mikrotik:fup-reset --cycle=monthly`** (Setiap Tanggal 1 Awal Bulan Pukul 00:00): Me-reset pemakaian kuota FUP bulanan.

---

### Metode 1: Menggunakan Linux Crontab (Direkomendasikan)

Buka konfigurasi crontab di server Linux Anda:
```bash
crontab -e
```

Tambahkan baris berikut di bagian paling bawah (sesuaikan path direktori jika berbeda):
```bash
* * * * * cd /var/www/html/mikrotik-management && php artisan schedule:run >> /dev/null 2>&1
```

Simpan dan keluar. Cron daemon akan otomatis memanggil Laravel Scheduler setiap menit.

---

### Metode 2: Menggunakan Systemd Service & Timer (Khusus Server Produksi)

Jika Anda menggunakan Ubuntu/Debian dan ingin menggunakan systemd:

#### 1. Buat Service Unit
Buat file `/etc/systemd/system/mikrotik-scheduler.service`:
```ini
[Unit]
Description=MikroTik Management Scheduler Runner
After=network.target

[Service]
Type=oneshot
User=www-data
WorkingDirectory=/var/www/html/mikrotik-management
ExecStart=/usr/bin/php /var/www/html/mikrotik-management/artisan schedule:run

[Install]
WantedBy=multi-user.target
```

#### 2. Buat Timer Unit (Menjalankan setiap menit)
Buat file `/etc/systemd/system/mikrotik-scheduler.timer`:
```ini
[Unit]
Description=Run MikroTik Management Scheduler Every Minute

[Timer]
OnBootSec=1min
OnUnitActiveSec=1min
Unit=mikrotik-scheduler.service

[Install]
WantedBy=timers.target
```

#### 3. Aktifkan Timer
```bash
sudo systemctl daemon-reload
sudo systemctl enable --now mikrotik-scheduler.timer
```

---

### Daftar Perintah Artisan Mandiri

Anda juga dapat menjalankan perintah scheduler secara manual:

```bash
# Menjalankan pengumpul data traffic & penjualan sekarang
php artisan mikrotik:collect

# Menjalankan reset FUP harian secara manual
php artisan mikrotik:fup-reset --cycle=daily

# Menjalankan reset FUP bulanan secara manual
php artisan mikrotik:fup-reset --cycle=monthly

# Memeriksa daftar jadwal yang sedang aktif
php artisan schedule:list
```

---

## 📖 Panduan Penggunaan Modul Utama

| Modul | Menu / URL | Deskripsi Penggunaan |
|---|---|---|
| **Dashboard** | `/` | Pantau grafik real-time traffic download/upload, status router CPU & memori, dan 5 pemakai bandwidth teratas. |
| **Online Users** | `/users` | Lihat daftar user yang sedang aktif terhubung, IP, MAC address, kuota terpakai, dan tombol *Kick / Putus Koneksi*. |
| **Live Devices** | `/devices` | Pantau seluruh perangkat di jaringan (DHCP Lease, IP Binding, MAC address, hostname). |
| **Analisis Trafik** | `/traffic-analytics` | Klasifikasi pemakaian bandwidth user (Streaming Video, Sosmed & Chat, Game Online, Cloud/Browsing), Donut Chart & Hourly Trend. |
| **Generate Voucher** | `/hotspot/generate` | Buat voucher massal berdasarkan profil paket yang dipilih, panjang kode, awalan (prefix), dan opsi cetak langsung. |
| **Daftar Voucher** | `/vouchers` | Kelola stok voucher, filter berdasarkan status (*unused*, *active*, *expired*), batch ID, dan cetak ulang thermal. |
| **Profil Paket Hotspot** | `/hotspot/profiles` | Atur nama paket, masa aktif (*validity*), batas kecepatan (*rate limit*), batas kuota FUP, harga jual, dan harga modal. |
| **POS Kasir** | `/pos` | Kasir kas penjualan voucher harian, buka/tutup shift kasir, cetak struk kasir, dan analisis omzet vs laba bersih. |
| **Tagihan Bulanan** | `/pos/monthly` | Kelola pelanggan rumahan, buat invoice bulanan massal, catat pembayaran lunas/cicilan bertahap, dan cetak invoice. |
| **Template Voucher** | `/settings/templates` | Kustomisasi tampilan cetak voucher thermal (ukuran 58mm/80mm, QR code, barcode, logo, dan slogan). |
| **Profil & Aplikasi** | `/settings/profile` | Perbarui nama admin, email, ganti kata sandi, avatar, logo aplikasi, favicon browser, dan nomor kontak WhatsApp support. |
| **Pemeliharaan & Reset** | `/settings/maintenance` | Fasilitas reset data sistem (penjualan, user hotspot di MikroTik, counter traffic, atau factory reset) dengan konfirmasi aman. |
| **Audit Log** | `/audit-logs` | Rekaman riwayat aktivitas dan perubahan yang dilakukan admin pada sistem untuk audit keamanan. |

---

## 🔒 Tips Keamanan & Deployment Produksi

1. **Ubah Kredensial Default**: Segera perbarui nama, email, dan kata sandi akun admin di menu **Pengaturan $\rightarrow$ Profil & Aplikasi**.
2. **Amankan Service API MikroTik**: Di MikroTik, batasi `allowed-address` pada IP Service API hanya ke IP server web aplikasi:
   ```routeros
   /ip service set api address=192.168.88.10/32
   ```
3. **Non-aktifkan Debug Mode**: Pada file `.env` di lingkungan produksi, pastikan:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```
4. **Symlink Storage**: Pastikan direktori storage publik telah terhubung:
   ```bash
   php artisan storage:link
   ```

---

## 🧪 Pengujian Otomatis (*Automated Testing*)

Proyek ini dilengkapi dengan unit test dan feature test otomatis untuk menjamin integritas seluruh logika bisnis dan integrasi router:

```bash
php artisan test
```

---

## 📄 Lisensi
Hak Cipta © 2026. Aplikasi ini dikembangkan untuk kebutuhan operasional manajemen MikroTik Hotspot & Billing Jaringan.
