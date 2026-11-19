# Fitur Pengeluaran POS (Expense Tracking)

## Intent & Why

Saat ini dashboard POS menghitung laba bersih hanya dari selisih harga jual dan harga modal (HPP) voucher dan pelanggan bulanan. Biaya operasional nyata — ISP, listrik, pembelian kabel, bensin — tidak tercatat di mana pun. Akibatnya, angka "Laba Bersih" di dashboard terlalu optimis dan tidak mencerminkan keuntungan riil.

Fitur ini menambahkan pencatatan pengeluaran ke sistem POS agar laba bersih yang ditampilkan adalah **laba setelah biaya operasional**, memberikan gambaran keuangan yang sebenarnya.

## Goals

1. Pengguna dapat mencatat pengeluaran harian (ad-hoc, bebas) kapan saja.
2. Pengguna dapat mendaftarkan item pengeluaran bulanan tetap (ISP, listrik) sekali, dan sistem otomatis menggunakannya setiap bulan.
3. Jika nominal pengeluaran bulanan berubah, bulan-bulan sebelumnya tidak terpengaruh.
4. Total pengeluaran (harian + bulanan) bulan berjalan dikurangkan dari laba bersih di dashboard POS.

## Non-Goals

- Sistem kategori pengeluaran yang kompleks (cukup deskripsi teks bebas).
- Approval workflow untuk pengeluaran.
- Integrasi dengan sistem akuntansi eksternal.
- Fitur lampiran/upload bukti pengeluaran (bisa ditambah nanti).

## Anti-Goals

- Pengeluaran bulanan yang retroaktif mengubah bulan-bulan lama ketika nominal diubah.
- Menyembunyikan laba kotor — laba kotor (sebelum pengeluaran) tetap ditampilkan, pengeluaran muncul sebagai pengurang terpisah.

## Desain Database

### Tabel `daily_expenses`

Pencatatan pengeluaran harian ad-hoc. Satu record = satu transaksi pengeluaran.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto increment |
| description | string(255) | Deskripsi pengeluaran (wajib) |
| amount | decimal(12,2) | Nominal pengeluaran (wajib, > 0) |
| expense_date | date | Tanggal pengeluaran |
| created_by | foreignId → users | Siapa yang mencatat (nullable, nullOnDelete) |
| timestamps | | created_at, updated_at |

### Tabel `monthly_expense_templates`

Master item pengeluaran bulanan tetap. Menyimpan nominal "saat ini" yang akan digunakan untuk bulan-bulan mendatang.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto increment |
| name | string(100) | Nama item (ISP, Listrik, Sewa, dll) |
| amount | decimal(12,2) | Nominal saat ini |
| is_active | boolean | Aktif/nonaktif (default true) |
| timestamps | | created_at, updated_at |

### Tabel `monthly_expense_entries`

Snapshot pengeluaran bulanan per bulan. Setiap bulan mendapat satu record per template aktif, dengan nominal yang "dibekukan" saat record dibuat.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto increment |
| template_id | foreignId → monthly_expense_templates | Referensi ke master (nullable, nullOnDelete) |
| name | string(100) | Nama item (disalin dari template, immutable) |
| amount | decimal(12,2) | Nominal bulan ini (disalin dari template saat generate, bisa diedit per bulan) |
| billing_month | date | Bulan berlaku (format YYYY-MM-01) |
| notes | text | Catatan opsional |
| timestamps | | created_at, updated_at |

**Unique constraint:** `(template_id, billing_month)` — satu template hanya boleh punya satu entry per bulan.

### Mekanisme Snapshot Bulanan

Ketika dashboard POS atau halaman pengeluaran diakses untuk bulan tertentu:

1. Cek apakah `monthly_expense_entries` sudah ada untuk bulan itu.
2. Jika belum: ambil semua `monthly_expense_templates` yang `is_active = true`, buat entry untuk masing-masing dengan `amount` dari template saat ini.
3. Jika sudah ada: gunakan nilai yang sudah tersimpan (tidak di-update dari template).

Dengan demikian:
- Bulan A: ISP = 500.000 → entry dibuat dengan 500.000.
- Template diubah ke 600.000.
- Bulan B: Entry baru dibuat dengan 600.000.
- Bulan A tetap 500.000.

Jika pengguna perlu mengoreksi entry bulan tertentu, mereka bisa edit langsung entry tersebut tanpa mempengaruhi template atau bulan lain.

## Desain UI

### 1. Navigasi

Tambahkan menu **"Pengeluaran"** di sidebar navigasi (grup POS & Keuangan) dan tombol navigasi di header halaman POS.

Route: `GET /pos/expenses` → `pos.expenses`

### 2. Halaman Pengeluaran (`pos/expenses.blade.php`)

Halaman tunggal dengan dua tab:

**Tab 1: Pengeluaran Harian**
- Tabel daftar pengeluaran harian bulan terpilih (tanggal, deskripsi, nominal).
- Tombol "Tambah Pengeluaran" → modal form (deskripsi, nominal, tanggal).
- Aksi: Edit, Hapus per baris.
- Subtotal pengeluaran harian di bawah tabel.

**Tab 2: Pengeluaran Bulanan Tetap**
- Tabel daftar entry pengeluaran bulanan untuk bulan terpilih (nama, nominal, catatan).
- Tombol "Kelola Template" → modal/inline area untuk CRUD template master.
- Aksi: Edit nominal entry bulan ini (tanpa mengubah template).
- Subtotal pengeluaran bulanan di bawah tabel.
- Tombol "Generate Entries Bulan Ini" jika entries belum ada (atau auto-generate saat halaman pertama kali diakses).

**Footer:**
- Ringkasan total: Harian + Bulanan = **Total Pengeluaran Bulan Ini**.

### 3. Integrasi Dashboard POS (`pos/index.blade.php`)

Modifikasi kartu KPI **"Laba Bersih"** (kartu #2):

- Ubah label dari "Laba Bersih" menjadi "Laba Operasional".
- Angka utama: `totalNetProfit - totalExpenses`.
- Breakdown di bawah:
  - Laba Kotor: (jumlah laba voucher + bulanan seperti sekarang)
  - Pengeluaran: (total harian + bulanan) — warna merah/rose.

Tambahkan data `total_expenses`, `daily_expenses_total`, `monthly_expenses_total` ke return value `getDetailedDashboardAnalytics()`.

## Desain Backend

### Model Baru

- `App\Models\DailyExpense` — fillable: description, amount, expense_date, created_by.
- `App\Models\MonthlyExpenseTemplate` — fillable: name, amount, is_active.
- `App\Models\MonthlyExpenseEntry` — fillable: template_id, name, amount, billing_month, notes.

### Controller

Tambahkan method ke `PosController`:

- `expenses(Request)` — render halaman pengeluaran.
- `storeDailyExpense(Request)` — simpan pengeluaran harian.
- `updateDailyExpense(Request, DailyExpense)` — edit.
- `deleteDailyExpense(DailyExpense)` — hapus.
- `storeExpenseTemplate(Request)` — buat template baru.
- `updateExpenseTemplate(Request, MonthlyExpenseTemplate)` — edit template.
- `deleteExpenseTemplate(MonthlyExpenseTemplate)` — hapus template.
- `updateMonthlyExpenseEntry(Request, MonthlyExpenseEntry)` — edit entry bulan tertentu.

### Service (`PosManagerService`)

Tambahkan method:

- `getExpensesForMonth(string $month)` — ambil daily expenses + monthly entries, auto-generate entries jika belum ada.
- `generateMonthlyExpenseEntries(string $month)` — buat snapshot dari template aktif.
- `getExpenseTotalsForMonth(string $month)` — return total harian + bulanan (untuk dashboard).

Modifikasi `getDetailedDashboardAnalytics()`:
- Panggil `getExpenseTotalsForMonth()`.
- Tambahkan ke return array: `total_expenses`, `daily_expenses_total`, `monthly_expenses_total`.
- Hitung `total_operational_profit = totalNetProfit - totalExpenses`.

### Routes

```
Route::get('/expenses', [PosController::class, 'expenses'])->name('pos.expenses');
Route::post('/expenses/daily', [PosController::class, 'storeDailyExpense']);
Route::put('/expenses/daily/{expense}', [PosController::class, 'updateDailyExpense']);
Route::delete('/expenses/daily/{expense}', [PosController::class, 'deleteDailyExpense']);
Route::post('/expenses/templates', [PosController::class, 'storeExpenseTemplate']);
Route::put('/expenses/templates/{template}', [PosController::class, 'updateExpenseTemplate']);
Route::delete('/expenses/templates/{template}', [PosController::class, 'deleteExpenseTemplate']);
Route::put('/expenses/monthly/{entry}', [PosController::class, 'updateMonthlyExpenseEntry']);
```

## Constraints

- Stack: Laravel + Blade + Alpine.js + Tailwind CSS (sama dengan modul POS lainnya).
- Pola UI mengikuti `pos/monthly.blade.php` sebagai referensi terdekat (tab, modal, tabel).
- Layout menggunakan `x-layouts.app`.
- Semua nominal dalam Rupiah, format menggunakan `FormatHelper::formatRupiah()` dan `window.formatRupiah()`.
- Mendukung mode terang dan gelap.
- Mobile responsive (2 column → 1 column).

## Left to Builder

- Desain visual detail dan spacing pada halaman pengeluaran.
- Pesan konfirmasi/toast notifikasi.
- Paginasi tabel pengeluaran harian (jika diperlukan).
- Apakah chart tren harian di dashboard juga menampilkan garis pengeluaran (opsional).
- Penambahan ke `FrontendPagesTest` dan tes unit expense.
- Penambahan ke `MaintenanceService` untuk pembersihan data expense lama.
