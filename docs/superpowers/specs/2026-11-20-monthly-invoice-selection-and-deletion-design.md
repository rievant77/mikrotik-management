# Desain Fitur Seleksi Pelanggan Generate Tagihan Bulanan & Hapus Tagihan (Single/Batch)

**Tanggal:** 2026-11-20  
**Status:** Approved  
**Penulis:** Antigravity & User  

---

## 1. Intent & The Why

Pada sistem POS Pelanggan Bulanan (`/pos/monthly`), saat ini aksi "Generate Tagihan" membuat tagihan secara otomatis untuk seluruh pelanggan aktif tanpa opsi memilih sebagian pelanggan. Selain itu, belum tersedia mekanisme untuk menghapus tagihan yang salah digenerate atau ingin dibatalkan, baik secara individual per tagihan maupun secara massal (batch).

Fitur ini bertujuan untuk:
1. Memberikan fleksibilitas kepada pengelola/kasir untuk memilih pelanggan mana saja yang akan digenerate tagihannya pada periode bulan tertentu (misal: penagihan bertahap atau pengecualian pelanggan tertentu).
2. Memungkinkan penghapusan tagihan hasil generate (satu-persatu atau sekaligus secara batch) dengan aman, di mana riwayat pembayaran yang menempel pada tagihan tersebut akan otomatis ikut terhapus bersih (cascade delete).

---

## 2. Goals, Non-Goals, Anti-Goals

### Goals
- Menyediakan antarmuka pemilihan pelanggan (checkbox, search filter, select all) di dalam modal Generate Tagihan Bulanan.
- Menyediakan tombol hapus satu-persatu pada setiap baris data tagihan dengan dialog konfirmasi.
- Menyediakan fitur pemilihan multi-baris (checkbox header & row) pada tabel tagihan dengan bilah aksi batch untuk menghapus banyak tagihan sekaligus.
- Menjamin integritas data: saat tagihan dihapus, semua data pembayaran (`MonthlyPayment`) yang berelasi dengan tagihan tersebut ikut terhapus dari database, serta kas pada shift kasir aktif disesuaikan ulang (recalculate shift expected cash).
- Mencatat audit log untuk setiap penghapusan tagihan (baik single maupun batch).

### Non-Goals
- Mengubah skema tabel database (kolom database sudah mencukupi).
- Mengubah alur pembayaran cicilan atau pelunasan tagihan yang sudah ada.

### Anti-Goals
- Tidak meninggalkan data pembayaran "yatim" (*orphaned payment records*) saat tagihan dihapus.
- Tidak membebani pengguna dengan UI yang lambat atau tidak responsif saat daftar pelanggan banyak (pencarian di modal menggunakan filter reaktif Alpine.js).

---

## 3. Constraints

- Framework: Laravel 11 / PHP 8.2+
- Frontend: Blade + Tailwind CSS + Alpine.js (tanpa reload halaman untuk aksi ajax, reactive feedback via toast/notifikasi)
- UI/UX Anti-Slop: Kontras jelas, penataan tombol rapi tanpa duplikasi ikon, konfirmasi modal sebelum aksi destruktif.
- Testing: Semua test existing (FrontendPagesTest, PosAccountingTest, PosExpenseControllerTest, dll) harus tetap pass (100% green).

---

## 4. User Decisions & Design Details

1. **Pemilihan Pelanggan pada Generate Modal:**
   - Disediakan checkbox "Pilih Semua" (default tercentang untuk seluruh pelanggan aktif).
   - Input pencarian cepat nama/kontak pelanggan.
   - List pelanggan interaktif dengan scrollbar rapi, menampilkan nama pelanggan, nama paket, dan nominal tagihan.
   - Counter dinamis: "X dari Y pelanggan dipilih (Estimasi: Rp Z)".
   - Request API mengirim `customer_ids: [1, 2, ...]` ke backend.

2. **Penghapusan Tagihan Satuan:**
   - Tombol hapus di kolom aksi tabel tagihan (ikon tong sampah warna rose/merah).
   - Dialog konfirmasi konfirmasi: mengingatkan bahwa data tagihan dan catatan pembayaran terkait akan dihapus permanen.
   - Request dikirim via `DELETE /pos/monthly/invoices/{invoice}`.

3. **Penghapusan Tagihan Batch (Massal):**
   - Checkbox di setiap baris tabel tagihan dan checkbox "Pilih Semua" di header tabel.
   - Bilah aksi (*batch action bar*) mengapung / tampil di atas tabel ketika minimal 1 baris dicentang.
   - Menampilkan jumlah tagihan yang dicentang dan tombol aksi "Hapus X Tagihan Terpilih".
   - Request dikirim via `POST /pos/monthly/invoices/batch-delete` dengan payload `{ invoice_ids: [...] }`.

4. **Penghapusan Catatan Pembayaran:**
   - Sesuai keputusan user, saat tagihan dihapus, catatan pembayaran yang terkait dengannya ikut terhapus (`$invoice->payments()->delete()`).
   - Jika pembayaran yang dihapus bertransaksi secara tunai pada shift kasir, saldo kas shift kasir otomatis dihitung ulang (`recalculateShiftExpectedCash`).
