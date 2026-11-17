# Design Spec: Konsistensi Desain, Warna, dan Mode Gelap/Terang (MikroTik Management)

**Tanggal:** 2026-11-17  
**Status:** Disetujui Pengguna (Revisi: Ikon Sidebar Berwarna & Status Router Sesuai Data Real)  
**Domain:** Frontend UI / UX (Laravel Blade, Tailwind CSS, Alpine.js)

---

## 1. Intent and The Why

Aplikasi web MikroTik Management saat ini memiliki tata letak dan fungsionalitas yang sudah kaya dan terstruktur rapi. Namun, ada masalah keterbacaan (readability) dan inkonsistensi visual saat berganti antara **Mode Terang (Light Mode)** dan **Mode Gelap (Dark Mode)**:
1. Beberapa teks sekunder, label kecil, dan petunjuk (placeholder/hint) menggunakan kelas statis `text-zinc-400` tanpa adaptasi tema. Di mode terang dengan latar putih/terang, warna ini memiliki rasio kontras sangat rendah (~2.4:1), sehingga teks tampak pudar atau nyaris tidak terlihat.
2. Sejumlah kontrol form (`input`, `select`, `textarea`, `checkbox`, `radio`) di beberapa halaman belum memiliki kelas `dark:` yang lengkap, menyebabkan kontrol tetap berwarna putih terang atau teks input tidak terbaca di mode gelap.
3. Sebagian kartu sekunder, header tabel, dan garis batas (border) masih menggunakan warna abu-abu terang tanpa padanan mode gelap yang seragam.
4. Ikon pada sidebar navigasi sebelumnya monoton abu-abu. Pengguna meminta ikon dibuat berwarna tematik agar navigasi lebih intuitif dan segar.
5. Widget status router di footer sidebar sebelumnya berisi data statis hardcoded (`CCR1009-Core`, `v7.16`, `CPU: 8%`, `192.168.88.1`). Pengguna meminta bagian ini diperbaiki agar 100% menampilkan data nyata (real-data) dari database/router MikroTik.

---

## 2. Goals, Non-Goals, and Anti-Goals

### Goals
- **Keterbacaan & Kontras Tinggi (WCAG AA compliant):**
  - Teks normal memiliki kontras minimal 4.5:1.
  - Teks besar/bold memiliki kontras minimal 3:1.
  - Teks utama: `text-zinc-900 dark:text-zinc-100`.
  - Teks sekunder (body keterangan, subjudul): `text-zinc-600 dark:text-zinc-300`.
  - Teks metadata/label halus/timestamp: `text-zinc-500 dark:text-zinc-400`.
- **Elemen Form Konsisten & Terbaca:**
  - Semua `<input>`, `<select>`, `<textarea>` memiliki background, border, dan text yang adaptif:
    - Mode terang: `bg-zinc-50 border-zinc-200 text-zinc-900 placeholder:text-zinc-400 focus:bg-white focus:border-zinc-400`.
    - Mode gelap: `dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 dark:placeholder:text-zinc-500 dark:focus:bg-zinc-850 dark:focus:border-zinc-500`.
  - Checkbox & Radio:
    - Memiliki `border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-emerald-600 dark:text-emerald-500 focus:ring-emerald-500`.
- **Ikon Sidebar Berwarna & Tematik:**
  - Tiap modul di sidebar memiliki warna ikon fungsional yang harmonis:
    - Dashboard: `text-indigo-500 dark:text-indigo-400`
    - Online Users: `text-emerald-500 dark:text-emerald-400`
    - Live Devices: `text-sky-500 dark:text-sky-400`
    - Analisis Trafik: `text-rose-500 dark:text-rose-400`
    - Historical Usage: `text-violet-500 dark:text-violet-400`
    - Paket Hotspot: `text-amber-500 dark:text-amber-400`
    - Kelola User & Voucher: `text-teal-500 dark:text-teal-400`
    - Generate Voucher: `text-emerald-500 dark:text-emerald-400`
    - Kasir POS: `text-emerald-600 dark:text-emerald-400`
    - Rekap Penjualan: `text-amber-600 dark:text-amber-400`
    - Tagihan Bulanan: `text-blue-500 dark:text-blue-400`
    - Profil & Aplikasi: `text-indigo-500 dark:text-indigo-400`
    - Router Settings: `text-cyan-500 dark:text-cyan-400`
    - Template Voucher: `text-purple-500 dark:text-purple-400`
    - Pemeliharaan: `text-rose-500 dark:text-rose-400`
    - Audit Log: `text-zinc-500 dark:text-zinc-400`
- **Widget Status Router Footer Sidebar Sesuai Data Nyata:**
  - Mengambil data dari `RouterSetting` aktif (`$appSetting`) dan cache resource live MikroTik (`Cache::get('router:system_resource')`):
    - Nama Board / Router: Menampilkan nama router asli (`$appSetting->name` atau `board-name` router).
    - Status Koneksi: Titik hijau berdenyut saat online/terhubung, merah saat offline/terputus.
    - Versi: Menampilkan versi RouterOS aktual (contoh `v7.x` atau status koneksi).
    - CPU: Menampilkan beban CPU realtime (`CPU: X%`) atau status koneksi.
    - Host IP: Menampilkan alamat IP host router asli dari pengaturan (`$appSetting->host`).
    - Sinkronisasi dinamis: Mendengarkan pembaruan realtime dari polling dasbor via custom event Alpine.js.
- **Komponen Kontainer & Tabel:**
  - Permukaan kartu utama: `bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800`.
  - Permukaan sub-kartu / widget / inset: `bg-zinc-50 dark:bg-zinc-800/50 border-zinc-200/80 dark:border-zinc-700/60`.
  - Header tabel: `bg-zinc-50/75 dark:bg-zinc-800/40 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-800`.
  - Hover baris tabel: `hover:bg-zinc-50 dark:hover:bg-zinc-800/50`.

### Non-Goals
- Tidak merombak tata letak (layout) halaman, arsitektur grid, atau alur navigasi aplikasi.
- Tidak mengubah logika bisnis backend, controller Laravel, route, atau skema database.
- Tidak mengubah format cetak termal voucher (`print-58mm`, `print-80mm`, `print-grid`) yang sudah didesain khusus untuk media cetak fisik hitam-putih.

### Anti-Goals
- Tidak menampilkan data tiruan atau angka fiktif pada komponen status sistem (R-17, R-38).
- Tidak menggunakan skrip patch eksternal (regex replace string pada CSS/Blade saat runtime); seluruh perbaikan ditulis langsung secara bersih pada template Blade dan sumber terkait (R-33).
- Tidak membiarkan ada kontrol atau teks yang "hilang" (putih di atas putih atau hitam di atas hitam).

---

## 3. Constraints

- **Tech Stack:** Laravel 13, Blade Templates, Tailwind CSS v4, Alpine.js 3, ApexCharts.
- **Dark Mode Mechanism:** Dikelola via class `.dark` pada tag `<html>`, dikontrol oleh `window.themeManager` di `resources/js/app.js` dan persisted di `localStorage.getItem('theme')`.
- **Aturan Antislop:**
  - R-02: Bebas dari karakter em dash di teks UI.
  - R-17 & R-38: Data statistik dan telemetry harus dari sumber data riil, bukan angka fiktif.
  - R-25: Memenuhi standar kontras WCAG AA (4.5:1 untuk teks normal).
  - R-29: Palet warna fungsional dan terkoordinasi.
  - R-34: Kedua mode (terang dan gelap) harus bekerja dan diverifikasi 100%.

---

## 4. How It Gets Made (Keputusan Teknis & Token Standar)

### A. Token Warna Standar
| Kategori Elemen | Mode Terang (Light) | Mode Gelap (Dark) | Catatan |
|---|---|---|---|
| Latar Halaman (Body) | `bg-zinc-100` | `dark:bg-zinc-950` | Sudah terpasang di layout |
| Kartu / Panel Utama | `bg-white` | `dark:bg-zinc-900` | Elevasi 1 |
| Kartu / Inset Sekunder | `bg-zinc-50` | `dark:bg-zinc-800/60` | Elevasi 2 / Telemetri |
| Garis Batas (Border) | `border-zinc-200` | `dark:border-zinc-800` | Standar pemisah |
| Border Halus (Sub-border) | `border-zinc-100` | `dark:border-zinc-800/80` | Pemisah dalam kartu |
| Teks Primer (Heading / Val) | `text-zinc-900` | `dark:text-zinc-100` | Kontras maksimal |
| Teks Sekunder (Body / Subtitle) | `text-zinc-600` | `dark:text-zinc-300` | Sangat terbaca |
| Teks Tersier (Meta / Label) | `text-zinc-500` | `dark:text-zinc-400` | Pengganti `text-zinc-400` telanjang |
| Placeholder Input | `placeholder:text-zinc-400` | `dark:placeholder:text-zinc-500` | Tetap kontras |
| Input Background | `bg-zinc-50` / `bg-white` | `dark:bg-zinc-800` | Adaptif |
| Input Border | `border-zinc-200` / `border-zinc-300`| `dark:border-zinc-700` | Adaptif |

### B. Arsitektur Data Widget Status Sidebar
1. `AppServiceProvider::boot()`: View composer membagikan `$appSetting` serta data telemetry router terkini yang disimpan di Cache (`Cache::get('router:system_resource')`).
2. `CollectorService` & `DashboardController`: Menyimpan snapshot resource `/system/resource/print` ke Cache saat polling berhasil.
3. Blade Component `components/layouts/app.blade.php`: Merender nilai riil awal (Nama Host, IP Host, Status Online, Versi OS, Beban CPU).
4. Alpine Component `sidebarRouterStatus()`: Mengikat data ke state lokal dan bereaksi terhadap event `router-telemetry-updated` dari live polling dasbor.

### C. Lingkup Berkas yang Diselaraskan
1. **Layouts & Global Components:**
   - `resources/views/components/layouts/app.blade.php` (sidebar berwarna, widget status real data, topbar, drawer, bottom nav)
   - `resources/views/layouts/app.blade.php` (sinkronisasi layout)
   - `resources/views/components/card.blade.php`
   - `resources/views/components/stat-card.blade.php`
   - `resources/views/components/modal.blade.php`
   - `resources/views/components/badge.blade.php`
   - `resources/views/components/command-palette.blade.php`
   - `resources/views/components/theme-toggle.blade.php`
2. **Dashboard & Monitoring:**
   - `resources/views/dashboard/index.blade.php`
   - `resources/views/traffic/index.blade.php`
   - `resources/views/devices/index.blade.php`
   - `resources/views/users/index.blade.php`
   - `resources/views/users/show.blade.php`
   - `resources/views/users/historical.blade.php`
3. **Hotspot & Vouchers:**
   - `resources/views/hotspot/profiles.blade.php`
   - `resources/views/hotspot/users.blade.php`
   - `resources/views/hotspot/generate.blade.php`
   - `resources/views/vouchers/index.blade.php`
4. **POS & Billing:**
   - `resources/views/pos/index.blade.php`
   - `resources/views/pos/vouchers.blade.php`
   - `resources/views/pos/monthly.blade.php`
   - `resources/views/pos/shifts.blade.php`
5. **Settings & System:**
   - `resources/views/settings/router.blade.php`
   - `resources/views/settings/profile.blade.php`
   - `resources/views/settings/templates.blade.php`
   - `resources/views/settings/maintenance.blade.php`
   - `resources/views/audit/index.blade.php`
   - `resources/views/auth/login.blade.php`

---

## 5. What's Left to the Builder

- Penggantian token kelas secara presisi di setiap file Blade.
- Memastikan pemetaan data realtime pada widget status router di sidebar berjalan mulus di mode SSR maupun SPA/Alpine.
- Verifikasi visual dan pengujian build Vite (`npm run build`).
