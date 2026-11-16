# Merge Vouchers & Hotspot Users Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menggabungkan halaman Kelola User Hotspot (`/hotspot/users`) dan Daftar Voucher (`/vouchers`) menjadi satu halaman terpadu "Kelola User & Voucher" yang efisien, tanpa duplikasi data atau menu di sidebar.

**Architecture:**
- **Routing**: `route('vouchers.index')` dan `route('hotspot.users')` keduanya diarahkan ke `VoucherController@index` sehingga tidak ada link/test yang rusak. API endpoints untuk single user CRUD (`hotspot.users.store`, `update`, `toggle`, `destroy`) tetap dipertahankan.
- **Unified Blade View (`resources/views/vouchers/index.blade.php`)**:
  - Judul: "Kelola User & Voucher Hotspot".
  - Actions atas: "Cetak Grid Terfilter", "+ Tambah User", dan "+ Generate Voucher Massal".
  - 5 Kartu Status KPI: Total Voucher & User (All), Sedang Online, Voucher/User Aktif, Non-Aktif / Expired, dan Nilai Inventory.
  - Tabel Desktop & Mobile Cards: Dilengkapi aksi Cetak, Salin, Edit User modal, Toggle Status Aktif/Nonaktif, dan Hapus User.
  - Modal form terintegrasi untuk Tambah / Edit user hotspot tunggal.
- **Sidebar Desktop & Mobile**: Menggabungkan menu yang sebelumnya terpisah menjadi 1 menu: "Kelola User & Voucher" di bawah grup Hotspot & Voucher.

**Tech Stack:** Laravel 12, Blade, Alpine.js, Tailwind CSS v4, RouterOS API.

## Global Constraints
- Format tanggal tetap bahasa Indonesia dan format angka perseribuan (`formatNumber()`, `@formatNumber`).
- Tidak menjalankan perintah git (`git status`, `git commit`, `git worktree`, dll.) sesuai batasan instruksi pengguna.
- Mematuhi prinsip antislop-ui: tanpa gradient generic, tanpa emoji dekoratif, radius konsisten (`rounded-xl`), elevation bersih dengan ring/border state.

---

### Task 1: Update Routes (`routes/web.php`)
- [ ] 1.1 Arahkan `Route::get('/hotspot/users')` ke `[VoucherController::class, 'index']`.
- [ ] 1.2 Pertahankan routes CRUD single user: `hotspot.users.store`, `hotspot.users.update`, `hotspot.users.toggle`, `hotspot.users.destroy`, dan `hotspot.users.sync`.

### Task 2: Integrate Single User CRUD into Unified View (`resources/views/vouchers/index.blade.php`)
- [ ] 2.1 Update page header title menjadi "Kelola User & Voucher Hotspot" dan tambahkan button "+ Tambah User".
- [ ] 2.2 Tambahkan aksi Edit, Toggle Status, dan Hapus pada tabel desktop dan card mobile.
- [ ] 2.3 Tambahkan modal form `hotspot-user-modal` dan logic Alpine.js: `openCreateModal()`, `editUser()`, `saveUser()`, `toggleStatus()`, dan `deleteUser()`.

### Task 3: Streamline Desktop & Mobile Navigation (`resources/views/components/layouts/app.blade.php`)
- [ ] 3.1 Pada Desktop Sidebar (Group 2: Hotspot & Voucher), satukan menu "Kelola User Hotspot" dan "Daftar Voucher" menjadi satu menu "Kelola User & Voucher" (`route('vouchers.index')`).
- [ ] 3.2 Pada Mobile Drawer (Group 2: Hotspot & Voucher), satukan menjadi satu menu "Kelola User & Voucher" dengan icon yang tepat.

### Task 4: Compilation & Verification
- [ ] 4.1 Jalankan `npm run build` untuk memverifikasi assets.
- [ ] 4.2 Jalankan `php "-dxdebug.mode=off" vendor/bin/phpunit tests/Feature/FrontendPagesTest.php` untuk memverifikasi seluruh test route, CRUD element assertions, dan filter assertions lulus tanpa error.
