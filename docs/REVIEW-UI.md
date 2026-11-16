# Review Awal: mikrotik-management

Dibuat oleh: Manajer (bot). Tanggal: 2026-11-11.
Tujuan: bekal pemahaman untuk Manajer dan bot Back-end / Front-end / Tester / Reviewer.

## 1. Aplikasi ini apa

Aplikasi web **manajemen router MikroTik** berbasis Laravel. Mengelola hotspot,
voucher, perangkat yang terhubung, lalu lintas jaringan, dan kasir (POS) untuk
penjualan voucher serta pelanggan bulanan.

Terhubung ke router lewat paket `evilfreelancer/routeros-api-php` (RouterOS API).

Fitur utama (dari `routes/web.php`):
- **Login / Logout** — autentikasi sesi.
- **Dashboard** — ringkasan + data live (polling `/api/dashboard/live`).
- **Users** — daftar user hotspot, detail, data live, disconnect, reset FUP.
- **Devices** — perangkat terhubung: make-static, IP binding, kick, komentar, aktivitas web.
- **Traffic Analytics** — pantau lalu lintas, deploy rule, reset counter.
- **Hotspot** — kelola profil & user hotspot (sinkron dari router), generate voucher.
- **Vouchers** — inventori voucher + cetak (58mm, 80mm, grid).
- **POS / Kasir** — voucher, langganan bulanan, invoice, pembayaran, shift kasir.
- **Settings** — koneksi router, profil, branding, template, maintenance.
- **Audit Logs** — catatan aktivitas.

## 2. Stack UI

| Bagian        | Teknologi |
|---------------|-----------|
| Framework     | Laravel 13, PHP 8.3 |
| Tampilan      | Blade (server-side) |
| Interaktivitas| Alpine.js 3 |
| CSS           | Tailwind CSS v4 (+ forms, typography) |
| Grafik        | ApexCharts |
| Ikon          | lucide-vue-next |
| Build         | Vite |

Catatan dari `resources/js/app.js`:
- Ada **tema terang/gelap** (`themeManager`), tersimpan di localStorage, ikut `prefers-color-scheme`.
- Ada helper toast, format byte, format Rupiah (`id-ID`), dan directive `x-money`
  untuk input angka ribuan. Jadi aplikasi ini memang ditujukan untuk pasar Indonesia.
- `resources/js` hanya berisi `app.js`; tidak ada komponen Vue/React. Tampilan
  sepenuhnya Blade + Alpine.

Struktur `resources/views/`: `auth, dashboard, users, devices, traffic, hotspot,
vouchers, pos, settings, audit, layouts, components`.

## 3. Temuan tampilan

Catatan: ini review awal dari struktur dan konfigurasi. Isi tiap file Blade
(`layouts/`, `components/`, tiap halaman) BELUM dibaca satu per satu. Temuan di
bawah adalah titik yang perlu dicek Front-end, bukan vonis akhir.

Yang perlu ditinjau lebih dalam:
1. **Dark mode menyeluruh.** Tema gelap ada di JS. Perlu dicek tiap view sudah
   punya kelas `dark:` yang lengkap — bagian yang terlewat akan terbaca buruk di mode gelap.
2. **Halaman cetak voucher (58mm / 80mm / grid).** Layout cetak termal rawan
   masalah: lebar kertas, margin, page-break. Perlu dites render cetaknya.
3. **Konsistensi komponen.** Banyak halaman sejenis (index + live data + modal
   aksi). Perlu dipastikan pola tabel, modal, dan toast dipakai seragam lewat
   `components/`, bukan disalin-tempel per halaman.

## 4. Langkah lanjut yang disarankan

- Front-end: baca `layouts/` dan `components/` dulu, lalu audit `dark:` dan
  responsif di tiap halaman.
- Tester: uji alur cetak voucher dan polling data live.
- Belum dijalankan: `npm install` / `npm run build` dan migrasi DB belum dicek di sini.
