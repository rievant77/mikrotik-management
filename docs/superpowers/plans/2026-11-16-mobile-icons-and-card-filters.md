# Mobile Sidebar Icons & Interactive List Card Filters Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan icon pada mobile sidebar menu dan menjadikan kartu ringkasan KPI pada Online Users, Sessions (Live Devices), dan Daftar Voucher sebagai filter interaktif berbasis status/kategori saat diklik.

**Architecture:**
- **Mobile Sidebar Navigation**: Menambahkan SVG icons (Heroicons outline 24x24) pada setiap item menu mobile di `resources/views/components/layouts/app.blade.php`, meniru kelengkapan dan grouping desktop sidebar dengan styling antislop yang konsisten.
- **Online Users**: Mengubah 5 kartu metrik di `resources/views/users/index.blade.php` menjadi button interaktif Alpine.js yang memicu perubahan `categoryFilter` dan visual active ring/badge.
- **Sessions / Live Devices**: Mengubah 6 kartu metrik di `resources/views/devices/index.blade.php` menjadi button interaktif Alpine.js yang memicu `statusFilter` ('all', 'online', 'bypassed', 'standby', 'blocked', 'offline').
- **Daftar Voucher**: Mengubah 4 kartu ringkasan di `app/Http/Controllers/VoucherController.php` dan `resources/views/vouchers/index.blade.php` agar berfokus pada 4 status (Semua Voucher, Sedang Online, Voucher Aktif, Non-Aktif/Expired) dan dapat diklik untuk memfilter daftar voucher.

**Tech Stack:** Laravel 12, Blade, Alpine.js, Tailwind CSS v4.

## Global Constraints
- Format tanggal tetap bahasa Indonesia dan format angka perseribuan (`formatNumber()`, `@formatNumber`).
- Halaman Hotspot Users diabaikan terlebih dahulu sesuai instruksi pengguna.
- Tidak menjalankan perintah git (`git status`, `git commit`, `git worktree`, dll.) sesuai batasan instruksi pengguna.
- Mematuhi prinsip antislop-ui: tanpa gradient generic, tanpa emoji dekoratif, radius konsisten (`rounded-xl`), elevation bersih dengan ring/border state.

---

### Task 1: Mobile Sidebar Icons (`resources/views/components/layouts/app.blade.php`)
- [ ] 1.1 Update drawer navigasi mobile dengan struktur menu lengkap dan terorganisir per kelompok (Live Monitoring, Hotspot & Voucher, Kasir & Billing POS, Konfigurasi & Sistem).
- [ ] 1.2 Tambahkan SVG icon pada setiap item menu mobile dengan ukuran proporsional (`w-5 h-5`), warna icon konsisten, dan badge live.
- [ ] 1.3 Pastikan transisi hover, state aktif (`bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900`), dan tap target mobile (`min-h-[44px]`) nyaman digunakan.

### Task 2: Clickable Card Filters on Online Users (`resources/views/users/index.blade.php`)
- [ ] 2.1 Jadikan kartu "Total Online" sebagai button yang mereset `categoryFilter = 'all'`.
- [ ] 2.2 Jadikan kartu "Voucher (Profit)" sebagai toggle filter `categoryFilter = 'voucher'`.
- [ ] 2.3 Jadikan kartu "Bulanan (Profit)" sebagai toggle filter `categoryFilter = 'bulanan'`.
- [ ] 2.4 Jadikan kartu "Pribadi (Non-Profit)" sebagai toggle filter `categoryFilter = 'pribadi'`.
- [ ] 2.5 Jadikan kartu "Trafik Live" sebagai toggle sort rate (`rate_desc`).
- [ ] 2.6 Tambahkan styling interaktif: `cursor-pointer`, hover subtle lift/border, active ring indicator (`ring-2 ring-emerald-500` dsb.), serta badge penanda `(Filter Aktif)`.

### Task 3: Clickable Card Filters on Live Devices / Sessions (`resources/views/devices/index.blade.php`)
- [ ] 3.1 Ubah 6 kartu KPI (Total Perangkat, Online Hotspot, Bypass IP, Standby, Diblokir, Offline / Idle) menjadi container yang dapat diklik (`@click="statusFilter = ..."`).
- [ ] 3.2 Tambahkan visual feedback saat aktif: border warna spesifik / `ring-2` sesuai status, badge teks kecil penanda filter aktif, dan cursor pointer.

### Task 4: Status-Based Clickable Card Filters on Daftar Voucher (`app/Http/Controllers/VoucherController.php` & `resources/views/vouchers/index.blade.php`)
- [ ] 4.1 Di `VoucherController.php`, hitung dan kirimkan metrik status lengkap: `total`, `online`, `active`, dan `inactive`.
- [ ] 4.2 Di `resources/views/vouchers/index.blade.php`, sesuaikan 4 kartu metrik menjadi: Semua Voucher (`status=all`), Sedang Online (`status=online`), Voucher Aktif (`status=active`), dan Non-Aktif / Expired (`status=inactive`).
- [ ] 4.3 Jadikan tiap kartu sebagai link / klik filter yang mengirimkan query string `status` dengan tetap mempertahankan filter search / kategori / profil.
- [ ] 4.4 Berikan penanda visual ring & border aktif pada kartu yang sedang memfilter daftar.

### Task 5: Compilation & Verification
- [ ] 5.1 Jalankan `npm run build` untuk memverifikasi kompilasi frontend assets.
- [ ] 5.2 Jalankan `php "-dxdebug.mode=off" vendor/bin/phpunit tests/Feature/FrontendPagesTest.php` untuk memastikan seluruh route dan fungsi lulus pengujian.
