# Dashboard Cards & Mobile Layout Optimization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyesuaikan card dashboard: menghapus "Router Gateway" dari card atas, menambahkan card "Perangkat Terhubung" dan "Tagihan Bulanan", serta menyusun ulang card atas dan card bawah menjadi 2 kolom di mobile agar lebih ringkas dan tidak memenuhi layar.

**Architecture:** Controller menambahkan agregasi real-time untuk total perangkat fisik terhubung (DHCP leases / live sessions) dan status tagihan bulanan (MonthlyInvoice / MonthlyCustomer) untuk rendering SSR dan live endpoint API. Blade view mengadaptasi Tailwind CSS `grid-cols-2 lg:grid-cols-4` pada baris card atas dan card telemetri router bawah dengan penyesuaian padding dan tipografi responsif.

**Tech Stack:** Laravel 13, Blade, Alpine.js 3, Tailwind CSS v4, RouterOS API.

**Spec:** User prompt:
1. Pada bagian card paling atas, hapus "Router Gateway" karena data router sudah ada di card bawah.
2. Card atas disesuaikan menjadi 4 card seimbang: (1) User Online, (2) Perangkat Terhubung, (3) Voucher Hari Ini, (4) Tagihan Bulanan.
3. Desain mobile: posisi card dibagi 2 kolom (`grid-cols-2`) baik card atas maupun card telemetri router bawah agar terlihat rapi dan tidak memenuhi layar.

## Global Constraints
- Tidak menjalankan pengecekan git (`git status`, `git diff`, dsb.) sesuai instruksi user.
- Format tanggal tetap bahasa Indonesia dan angka format numerik / perseribuan (`formatNumber()`, `FormatHelper::formatNumber()`).
- Data real-time Alpine.js tetap sinkron dengan endpoint `/api/live-dashboard`.

---

### Task 1: Controller Data Aggregation for Top Cards
**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`

- [ ] Step 1: Hitung `totalDevicesCount` dan `onlineDevicesCount` di method `index()`.
- [ ] Step 2: Hitung `monthlyUnpaidCount`, `monthlyUnpaidAmount`, dan `monthlyCustomersCount` untuk periode bulan berjalan di method `index()`.
- [ ] Step 3: Teruskan variabel-variabel tersebut ke view `dashboard.index`.
- [ ] Step 4: Perbarui method `liveData()` agar mengembalikan `total_devices`, `online_devices`, `monthly_unpaid_count`, `monthly_unpaid_amount`, `monthly_unpaid_amount_formatted`, dan `monthly_customers_count`.
- [ ] Step 5: Jalankan tes unit/fitur untuk memverifikasi controller tidak error.

---

### Task 2: Blade View Top Cards & Mobile 2-Column Grid
**Files:**
- Modify: `resources/views/dashboard/index.blade.php`

- [ ] Step 1: Ubah container card atas menjadi `grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4`.
- [ ] Step 2: Perbarui Card 1 (User Online) dengan ukuran font dan padding yang proporsional di mobile (`p-3.5 sm:p-5`, `text-xl sm:text-3xl`).
- [ ] Step 3: Implementasikan Card 2 (Perangkat Terhubung / Live Devices) menggantikan slot lama, lengkap dengan icon, total perangkat, status online/standby, dan link ke `/devices`.
- [ ] Step 4: Perbarui Card 3 (Voucher Hari Ini) dengan styling proporsional 2 kolom di mobile.
- [ ] Step 5: Implementasikan Card 4 (Tagihan Bulanan) menggantikan "Router Gateway", dengan data tagihan belum lunas, total piutang, pelanggan aktif, dan link ke `/pos/monthly`.
- [ ] Step 6: Perbarui inisialisasi Alpine.js `dashboardLive()` dan live polling receiver untuk data baru.

---

### Task 3: Blade View Bottom Router Telemetry Mobile 2-Column Grid
**Files:**
- Modify: `resources/views/dashboard/index.blade.php`

- [ ] Step 1: Ubah container card telemetri router (bawah) dari `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4` menjadi `grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-4 sm:mt-5`.
- [ ] Step 2: Sesuaikan padding kartu (`p-3 sm:p-4`) dan ukuran tipografi (judul `text-[11px] sm:text-xs`, angka `text-base sm:text-2xl font-mono`, progress bar `h-1.5 sm:h-2`) agar presisi dalam tata letak 2 kolom di layar HP.
- [ ] Step 3: Pastikan tidak ada horizontal scroll atau elemen bertumpuk di layar resolusi kecil (<400px).

---

### Task 4: Compilation & Verification
**Files:**
- Verification only

- [ ] Step 1: Jalankan `npm run build` untuk mengompilasi asset.
- [ ] Step 2: Jalankan suite tes PHPUnit via `php -d "xdebug.mode=off" artisan test` untuk memastikan seluruh tes tetap lulus tanpa regresi.
