# Product Requirements Document (PRD)
## Project: MikroTik Hotspot, User, Device & Bandwidth Manager — Laravel

- **Versi Dokumen**: 1.3.0
- **Status**: Revised Draft / Ready for Technical Design
- **Target Rilis**: MVP (Fase 1)
- **Backend**: Laravel 11.x / PHP 8.2+
- **Frontend**: Livewire 3 + Alpine.js atau Inertia.js + Vue 3
- **Realtime**: Laravel Reverb / WebSocket
- **Database**: PostgreSQL (direkomendasikan) / MySQL
- **Router Scope**: Single-router first; arsitektur tidak membutuhkan multi-router untuk MVP

---

## 1. Ringkasan Eksekutif

Aplikasi ini adalah sistem manajemen MikroTik Hotspot berbasis Laravel untuk memonitor user, device/session, bandwidth, pemakaian data, voucher, dan penjualan manual.

Versi ini memprioritaskan **data realtime** dan **historical analytics** secara bersamaan. Laravel bertindak sebagai backend/orchestrator yang mengambil data dari MikroTik melalui RouterOS API, menyimpan snapshot dan hasil accounting ke database, kemudian mengirim perubahan data ke browser melalui WebSocket.

MVP hanya menargetkan **satu router MikroTik utama**. Struktur aplikasi tetap dipisahkan dengan `router_id` pada data inti bila diperlukan untuk ekspansi di masa depan, tetapi seluruh UX, scheduler, collector, dan dashboard MVP dirancang untuk satu router aktif sehingga implementasi lebih sederhana dan stabil.

Aplikasi **tidak menggunakan payment gateway**. Penjualan voucher dicatat sebagai transaksi kasir/manual.

---

## 2. Tujuan Produk

### 2.1 Tujuan Utama

1. Menampilkan user Hotspot yang sedang online secara realtime.
2. Menampilkan device yang sedang terhubung secara realtime, termasuk username, MAC address, IP address, uptime, dan bandwidth.
3. Menampilkan bandwidth user secara realtime dan dapat difilter berdasarkan waktu.
4. Menampilkan chart realtime untuk traffic interface dan penggunaan bandwidth user.
5. Menyimpan histori pemakaian data per session secara akurat tanpa double counting.
6. Menyediakan analitik pemakaian berdasarkan user, device, profile, dan rentang waktu.
7. Mempermudah generate dan cetak voucher.
8. Menyediakan pencatatan penjualan voucher manual dan tutup kasir.

### 2.2 Non-Goals MVP

1. Payment gateway.
2. Integrasi banyak router dalam satu dashboard.
3. Otomasi FUP kompleks.
4. Notifikasi Telegram/WhatsApp.
5. Pengelolaan captive portal custom di luar kebutuhan login Hotspot MikroTik.

---

## 3. Prinsip Arsitektur

### 3.1 Laravel sebagai Backend Utama

Laravel menangani:

- autentikasi dan authorization;
- komunikasi RouterOS API;
- collector/polling data;
- session accounting;
- penyimpanan histori;
- reporting dan filtering;
- queue dan scheduler;
- WebSocket broadcasting melalui Laravel Reverb;
- voucher dan transaksi kasir.

### 3.2 Realtime = Router Polling + WebSocket

WebSocket tidak membaca data langsung dari MikroTik. Alur realtime adalah:

```text
MikroTik
   ↓
Laravel Collector
   ↓
Live Session / Traffic Snapshot
   ↓
Database + Event
   ↓
Laravel Reverb
   ↓
Browser Dashboard
```

Dengan model ini, browser tidak membuka koneksi langsung ke API MikroTik.

### 3.3 Single Router Context

MVP hanya memiliki satu router aktif yang dikonfigurasi pada `router_settings`.

Tidak ada kebutuhan UI untuk:

- switch router;
- multi-router dashboard;
- per-router session selector.

Jika suatu saat perlu multi-router, struktur service dan repository harus dibuat agar dapat dikembangkan tanpa mengubah domain utama.

---

## 4. Persona Pengguna

| Persona | Kebutuhan |
|---|---|
| Owner / Admin | Melihat status jaringan, user online, pemakaian, penjualan, dan laporan. |
| Operator / Kasir | Generate voucher, cetak voucher, melihat user aktif, mencatat transaksi. |
| Teknisi | Memantau device, bandwidth, resource router, dan troubleshooting. |

---

## 5. Functional Requirements

## 5.1 Router Connection

**FR-1.1** Sistem menyediakan satu konfigurasi router MikroTik aktif.

**FR-1.2** Konfigurasi minimal:

- Host/IP;
- API Port, default 8728;
- username API;
- password terenkripsi;
- enable/disable koneksi;
- timeout koneksi.

**FR-1.3** Sistem menyediakan tombol `Test Connection` yang melakukan:

1. TCP/API connection check;
2. API authentication check;
3. pembacaan identity router;
4. pembacaan versi RouterOS.

**FR-1.4** Status router:

- Online;
- Degraded;
- Unreachable;

beserta `last_successful_poll_at` dan `last_error_at`.

**FR-1.5** Jika memungkinkan, gunakan API-SSL pada port 8729. Port 8728 tetap dapat dikonfigurasi bila lingkungan router hanya menyediakan API biasa.

---

## 5.2 Realtime User Monitoring

**FR-2.1** Dashboard menampilkan daftar user Hotspot yang sedang online secara realtime.

Data minimal:

- Username;
- Profile;
- Status;
- IP address;
- jumlah device/session;
- total upload session;
- total download session;
- total bandwidth saat ini;
- uptime;
- last seen.

**FR-2.2** Data online user diperbarui otomatis tanpa refresh halaman.

**FR-2.3** User dapat difilter berdasarkan:

- username;
- profile;
- status;
- IP;
- MAC/device;
- rentang waktu histori.

**FR-2.4** Sistem menampilkan agregasi realtime:

- total user online;
- total device online;
- total upload rate;
- total download rate;
- top bandwidth users.

---

## 5.3 Realtime Device / Session Monitoring

Satu username dapat memiliki lebih dari satu device apabila profile MikroTik mengizinkan shared users.

**FR-3.1** Setiap active session direpresentasikan sebagai device/session record terpisah.

Minimal:

- username;
- MAC address;
- IP address;
- interface bila tersedia;
- session start;
- last seen;
- uptime;
- bytes in;
- bytes out;
- current upload rate;
- current download rate;
- session status.

**FR-3.2** Device harus memiliki identitas session yang stabil selama sesi aktif.

**FR-3.3** Reconnect dianggap sebagai session baru, walaupun username dan MAC sama.

**FR-3.4** Perubahan IP address tidak membuat session baru selama session identifier yang berasal dari collector tetap sama.

**FR-3.5** Jika session hilang dari MikroTik, collector menandainya sebagai `ended` setelah mekanisme grace period, bukan langsung menghapus data.

---

## 5.4 Realtime Bandwidth User

**FR-4.1** Sistem menghitung bandwidth realtime per device/session berdasarkan delta counter bytes terhadap snapshot sebelumnya.

```text
upload_rate = delta_bytes_in / delta_time
 download_rate = delta_bytes_out / delta_time
```

**FR-4.2** Dashboard dapat menampilkan:

- upload rate;
- download rate;
- total rate;
- top users by upload;
- top users by download;
- top devices by bandwidth.

**FR-4.3** Interval live collector default: **2–5 detik**, dapat dikonfigurasi sesuai kapasitas router.

**FR-4.4** Sistem tidak boleh melakukan polling RouterOS satu kali per browser/client. Satu polling collector digunakan bersama oleh seluruh client dashboard.

---

## 5.5 Realtime Traffic Chart

**FR-5.1** Dashboard memiliki chart realtime untuk traffic router/interface.

Data:

- Rx bytes/sec;
- Tx bytes/sec;
- total throughput.

**FR-5.2** Chart diperbarui melalui WebSocket tanpa full page refresh.

**FR-5.3** Rentang live chart yang ditampilkan di browser:

- 1 menit;
- 5 menit;
- 15 menit;
- 30 menit;
- 1 jam.

**FR-5.4** Data live chart disimpan sebagai short-term snapshots agar data dapat digunakan untuk historical analytics dalam periode yang ditentukan.

---

## 5.6 Historical Usage & Time Filtering

Aplikasi harus dapat melihat penggunaan berdasarkan waktu.

**FR-6.1** Filter waktu:

- Hari ini;
- Kemarin;
- 7 hari;
- 30 hari;
- custom date range;
- custom date-time range untuk detail session.

**FR-6.2** Filter tambahan:

- username;
- profile;
- MAC address;
- IP address;
- session status.

**FR-6.3** Hasil laporan minimal menampilkan:

- total upload;
- total download;
- total usage;
- jumlah session;
- total uptime;
- first seen;
- last seen.

**FR-6.4** User dapat membuka detail sebuah username dan melihat seluruh device/session pada rentang waktu yang dipilih.

---

## 5.7 Usage Accounting Engine

### 5.7.1 Prinsip

Sistem **tidak mencatat setiap hasil polling sebagai konsumsi final**. Polling menghasilkan snapshot; accounting menghitung delta dari snapshot sebelumnya.

```text
MikroTik Counter
       ↓
Snapshot N
       ↓
Compare Snapshot N-1
       ↓
Delta Upload/Download
       ↓
Session Usage Accumulator
       ↓
Daily Summary
```

### 5.7.2 Session Identity

Session diidentifikasi menggunakan kombinasi data aktif yang tersedia dari MikroTik dan ID internal aplikasi.

Minimal model:

```text
router/session context
username
mac_address
ip_address
session_started_at
```

Aplikasi membuat `session_id` internal ketika session pertama kali terlihat.

Username + MAC **bukan** primary key session.

### 5.7.3 Counter Handling

Collector harus menangani:

1. Counter bertambah normal → simpan delta.
2. Counter lebih kecil dari snapshot sebelumnya → anggap counter reset/restart dan jangan menghasilkan delta negatif.
3. Router reboot → sesi aktif ditutup setelah grace period dan session baru dibuat jika user kembali online.
4. Polling gagal → tidak mencatat delta; lanjut saat polling berikutnya.
5. Job overlap → locking wajib digunakan agar satu router tidak diproses oleh dua collector bersamaan.

### 5.7.4 Idempotency

Setiap snapshot/poll harus memiliki identifier yang dapat digunakan untuk mencegah double processing.

Contoh:

```text
collector_run_id
session_id
recorded_at
```

Satu `collector_run_id` tidak boleh diproses dua kali untuk session yang sama.

---

## 5.8 Historical Bandwidth Chart

**FR-8.1** Grafik dapat menampilkan:

- bandwidth user per menit;
- upload/download per jam;
- penggunaan per hari;
- perbandingan beberapa user.

**FR-8.2** Grafik dapat difilter berdasarkan waktu.

**FR-8.3** Untuk data besar, dashboard menggunakan agregasi server-side, bukan mengirim seluruh raw snapshot ke browser.

---

## 5.9 Hotspot Profile & Voucher

**FR-9.1** Kelola Hotspot Profile:

- name;
- rate limit Rx/Tx;
- shared users;
- validity;
- data limit;
- price;
- selling price;
- expired behavior.

**FR-9.2** Batch voucher generator mendukung:

- jumlah voucher;
- username=password atau credential terpisah;
- numeric/lowercase/uppercase/mixed;
- prefix;
- time limit;
- data limit;
- validity.

**FR-9.3** Username harus unique pada konteks Hotspot MikroTik.

---

## 5.10 Voucher Lifecycle

Voucher tidak boleh langsung dianggap sebagai transaksi hanya karena di-generate.

State minimal:

```text
GENERATED
   ↓
AVAILABLE
   ↓
SOLD
   ↓
ACTIVATED
   ↓
EXPIRED
```

State tambahan bila diperlukan:

```text
CANCELLED
DISABLED
```

**Generate voucher** hanya membuat inventory. **Sales transaction** tercatat ketika voucher dinyatakan terjual.

---

## 5.11 POS / Cashier — Voucher Usage Based Revenue

POS MVP memiliki dua sumber pendapatan yang berbeda dan **tidak boleh dicampur**:

1. **Voucher Hotspot**: pendapatan dihitung dari voucher yang benar-benar **dipakai/diaktivasi**, bukan saat voucher dibuat.
2. **User Bulanan**: pembayaran bulanan dicatat manual berdasarkan customer/user yang dipilih dan periode tagihan.

### 5.11.1 Voucher Revenue

**FR-11.1** Generate voucher hanya membuat inventory voucher.

**FR-11.2** Voucher dianggap menghasilkan transaksi ketika voucher pertama kali **dipakai/diaktivasi pada MikroTik** atau saat operator melakukan konfirmasi aktivasi secara manual bila mekanisme router tidak dapat mendeteksi event secara langsung.

**FR-11.3** Sistem mencatat minimal:

- voucher/user identifier;
- profile/package;
- harga pokok;
- harga jual;
- profit;
- waktu pertama dipakai/diaktivasi;
- operator/kasir bila tersedia;
- status voucher.

**FR-11.4** Dashboard POS menampilkan:

- jumlah voucher terbuat;
- jumlah voucher tersedia;
- jumlah voucher dipakai;
- jumlah voucher expired;
- omzet voucher terpakai;
- laba voucher terpakai;
- trend voucher dipakai per hari.

**FR-11.5** Perhitungan omzet POS **tidak boleh menggunakan jumlah voucher generated sebagai jumlah penjualan**.

### 5.11.2 User Bulanan

Aplikasi memiliki customer/user yang membayar biaya bulanan secara manual.

**FR-11.6** Admin dapat membuat data user bulanan dengan minimal:

- nama user/customer;
- username hotspot bila user juga memiliki akun MikroTik;
- nomor kontak (opsional);
- alamat/catatan (opsional);
- paket/profile (opsional);
- harga bulanan tetap;
- tanggal jatuh tempo/tagihan;
- status aktif/nonaktif.

**FR-11.7** Harga bulanan disimpan pada data user sehingga setiap user dapat memiliki tarif yang berbeda.

Contoh:

```text
Budi  → Rp150.000 / bulan
Andi  → Rp200.000 / bulan
Citra → Rp125.000 / bulan
```

**FR-11.8** Operator mencatat pembayaran dengan alur:

```text
Pilih Nama User
      ↓
Pilih Periode Tagihan
      ↓
Sistem menampilkan Tarif Bulanan User
      ↓
Input/konfirmasi Tanggal Bayar
      ↓
Simpan Pembayaran
```

Tanggal pembayaran **diinput manual**.

**FR-11.9** Nominal pembayaran default berasal dari `monthly_price` user, tetapi Admin dapat memberikan override bila diperlukan dan alasan override wajib dicatat.

**FR-11.10** Setiap pembayaran menyimpan:

- customer/user;
- periode tagihan (`billing_month`);
- nominal tarif;
- nominal dibayar;
- tanggal bayar;
- metode pembayaran (`cash`, `transfer`, `other`);
- operator;
- catatan.

**FR-11.11** Satu user tidak boleh memiliki dua pembayaran final untuk periode tagihan yang sama, kecuali transaksi sebelumnya dibatalkan/void.

**FR-11.12** Status tagihan minimal:

```text
UNPAID
PAID
PARTIAL
VOID
```

**FR-11.13** Dashboard POS menampilkan:

- total tagihan bulan berjalan;
- total sudah dibayar;
- total belum dibayar;
- total pembayaran sebagian;
- total omzet voucher dipakai;
- total pendapatan gabungan;
- daftar user yang belum membayar;
- daftar pembayaran terbaru.

### 5.11.3 Cashier Shift

**FR-11.14** Sistem tetap memiliki `cashier_shifts` untuk transaksi tunai.

**FR-11.15** Shift menyimpan:

- kasir;
- waktu buka/tutup;
- modal awal;
- total voucher cash;
- total pembayaran bulanan cash;
- expected cash;
- actual cash;
- discrepancy;
- status.

**FR-11.16** Pembayaran transfer tidak dihitung sebagai kas fisik pada `expected_cash`.

---

## 5.12 Audit Log

Semua perubahan penting harus dapat dilacak.

Minimal event:

- login/logout;
- router setting changed;
- voucher generated;
- voucher activated/used;
- voucher disabled;
- voucher deleted;
- monthly customer created/updated;
- monthly price changed;
- monthly payment created;
- monthly payment voided;
- quota reset;
- user/profile changed;
- cashier shift opened/closed.

---

## 6. Collector & Realtime Architecture

### 6.1 Komponen

```text
Laravel Scheduler
      ↓
Collector Job
      ↓
Queue Worker
      ↓
RouterOS API
      ↓
Session Collector
      ├── Active Sessions
      ├── Interface Traffic
      └── Resource Metrics
      ↓
Usage Accounting Service
      ↓
Database
      ↓
Domain Events
      ↓
Laravel Reverb
      ↓
Web Browser
```

### 6.2 Queue Rules

- Collector wajib berjalan di queue/background worker.
- Gunakan distributed lock / `withoutOverlapping` untuk router collector.
- Timeout job harus lebih kecil dari interval scheduling berikutnya.
- Retry menggunakan exponential backoff.
- Failed jobs dicatat.
- Sistem harus menyediakan health status collector.

### 6.3 Live Collector vs Historical Collector

**Live collector (2–5 detik):**

- active sessions;
- device status;
- bandwidth rate;
- interface traffic.

**Historical collector/aggregation:**

- session snapshots;
- usage accumulation;
- hourly/daily summary.

Pendekatan ini menjaga realtime tetap responsif tanpa membuat database dan router bekerja berlebihan.

---

## 7. Database Schema Inti

### 7.1 router_settings

```sql
CREATE TABLE router_settings (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    host VARCHAR(100) NOT NULL,
    api_port INT NOT NULL DEFAULT 8728,
    username VARCHAR(100) NOT NULL,
    password TEXT NOT NULL,
    use_ssl BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    connection_timeout_ms INT DEFAULT 3000,
    last_successful_poll_at TIMESTAMP NULL,
    last_error_at TIMESTAMP NULL,
    last_error_message TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 7.2 hotspot_profiles

```sql
CREATE TABLE hotspot_profiles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    shared_users INT DEFAULT 1,
    rate_limit VARCHAR(100),
    validity VARCHAR(50),
    price DECIMAL(12,2) DEFAULT 0,
    selling_price DECIMAL(12,2) DEFAULT 0,
    data_limit_bytes BIGINT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 7.3 hotspot_users

```sql
CREATE TABLE hotspot_users (
    id BIGSERIAL PRIMARY KEY,
    profile_id BIGINT REFERENCES hotspot_profiles(id) ON DELETE SET NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    comment VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    expired_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 7.4 monthly_customers

Data user bulanan dipisahkan dari `hotspot_users` agar customer yang membayar bulanan dapat dikelola walaupun belum atau tidak memiliki akun hotspot.

```sql
CREATE TABLE monthly_customers (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    hotspot_user_id BIGINT NULL REFERENCES hotspot_users(id) ON DELETE SET NULL,
    contact VARCHAR(100) NULL,
    address TEXT NULL,
    notes TEXT NULL,
    monthly_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    billing_day INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE INDEX idx_monthly_customers_name
ON monthly_customers(name);
```

### 7.5 hotspot_sessions

```sql
CREATE TABLE hotspot_sessions (
    id BIGSERIAL PRIMARY KEY,
    hotspot_user_id BIGINT NULL REFERENCES hotspot_users(id) ON DELETE SET NULL,
    username VARCHAR(100) NOT NULL,
    mac_address VARCHAR(20),
    ip_address VARCHAR(45),
    started_at TIMESTAMP NOT NULL,
    last_seen_at TIMESTAMP NOT NULL,
    ended_at TIMESTAMP NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    last_bytes_in BIGINT DEFAULT 0,
    last_bytes_out BIGINT DEFAULT 0,
    total_bytes_in BIGINT DEFAULT 0,
    total_bytes_out BIGINT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 7.6 usage_snapshots

```sql
CREATE TABLE usage_snapshots (
    id BIGSERIAL PRIMARY KEY,
    session_id BIGINT NOT NULL REFERENCES hotspot_sessions(id) ON DELETE CASCADE,
    recorded_at TIMESTAMP NOT NULL,
    bytes_in BIGINT NOT NULL DEFAULT 0,
    bytes_out BIGINT NOT NULL DEFAULT 0,
    delta_bytes_in BIGINT NOT NULL DEFAULT 0,
    delta_bytes_out BIGINT NOT NULL DEFAULT 0,
    upload_bps BIGINT NOT NULL DEFAULT 0,
    download_bps BIGINT NOT NULL DEFAULT 0,
    uptime_seconds INT DEFAULT 0,
    collector_run_id UUID NOT NULL,
    created_at TIMESTAMP
);

CREATE INDEX idx_usage_snapshots_session_time
ON usage_snapshots(session_id, recorded_at);

CREATE UNIQUE INDEX idx_usage_snapshots_collector_session
ON usage_snapshots(collector_run_id, session_id);
```

### 7.7 daily_user_usage_summaries

```sql
CREATE TABLE daily_user_usage_summaries (
    id BIGSERIAL PRIMARY KEY,
    hotspot_user_id BIGINT NULL REFERENCES hotspot_users(id) ON DELETE SET NULL,
    username VARCHAR(100) NOT NULL,
    usage_date DATE NOT NULL,
    total_bytes_in BIGINT DEFAULT 0,
    total_bytes_out BIGINT DEFAULT 0,
    total_bytes BIGINT DEFAULT 0,
    total_uptime_seconds INT DEFAULT 0,
    session_count INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(username, usage_date)
);
```

### 7.8 voucher_sales

Voucher sales hanya dibuat ketika voucher **dipakai/diaktivasi**, bukan ketika generate.

```sql
CREATE TABLE voucher_sales (
    id BIGSERIAL PRIMARY KEY,
    hotspot_user_id BIGINT NULL REFERENCES hotspot_users(id) ON DELETE SET NULL,
    username VARCHAR(100) NOT NULL,
    profile_name VARCHAR(100) NOT NULL,
    cost_price DECIMAL(12,2) DEFAULT 0,
    selling_price DECIMAL(12,2) DEFAULT 0,
    profit DECIMAL(12,2) DEFAULT 0,
    activated_at TIMESTAMP NOT NULL,
    recorded_by_user_id BIGINT NULL,
    shift_id BIGINT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'completed',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE UNIQUE INDEX idx_voucher_sales_user_once
ON voucher_sales(hotspot_user_id)
WHERE status = 'completed';
```

> Catatan: unique rule di atas mengasumsikan satu voucher hanya menghasilkan satu transaksi pendapatan. Jika satu username dapat dipakai ulang sebagai akun pelanggan, gunakan `voucher_id` terpisah sebagai referensi transaksi agar identitas voucher tetap unik.

### 7.9 monthly_payments

```sql
CREATE TABLE monthly_payments (
    id BIGSERIAL PRIMARY KEY,
    monthly_customer_id BIGINT NOT NULL REFERENCES monthly_customers(id) ON DELETE RESTRICT,
    billing_month DATE NOT NULL,
    monthly_price DECIMAL(12,2) NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    paid_at DATE NOT NULL,
    payment_method VARCHAR(20) NOT NULL DEFAULT 'cash',
    status VARCHAR(20) NOT NULL DEFAULT 'paid',
    recorded_by_user_id BIGINT NULL,
    shift_id BIGINT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE UNIQUE INDEX idx_monthly_payment_period
ON monthly_payments(monthly_customer_id, billing_month)
WHERE status <> 'void';
```

### 7.10 cashier_shifts

```sql
CREATE TABLE cashier_shifts (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    opened_at TIMESTAMP NOT NULL,
    closed_at TIMESTAMP NULL,
    opening_cash DECIMAL(12,2) DEFAULT 0,
    expected_cash DECIMAL(12,2) DEFAULT 0,
    actual_cash DECIMAL(12,2) NULL,
    discrepancy DECIMAL(12,2) NULL,
    status VARCHAR(20) DEFAULT 'open',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 7.11 audit_logs

```sql
CREATE TABLE audit_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100),
    entity_id BIGINT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP
);
```

---

## 8. Data Retention

### 8.1 Live Snapshot

Raw live snapshots dapat disimpan selama **7–30 hari** sesuai kapasitas server.

### 8.2 Daily Summary

Daily summary disimpan jangka panjang.

### 8.3 Aggregation

Data historical chart menggunakan:

- raw/minute-level untuk periode pendek;
- hourly untuk periode menengah;
- daily untuk periode panjang.

### 8.4 Cleanup

Cleanup dijalankan melalui scheduled job Laravel.

Cleanup harus idempotent dan dicatat pada application log.

---

## 9. Error Handling & Edge Cases

| Kasus | Penanganan |
|---|---|
| Router offline | Collector retry, status `Unreachable`, browser tetap menampilkan last known state + timestamp. |
| API timeout | Timeout + exponential backoff. |
| Router reboot | Tutup session setelah grace period; session baru dibuat saat muncul kembali. |
| Counter menurun | Anggap reset, delta tidak boleh negatif. |
| Polling job overlap | Gunakan lock per router collector. |
| User reconnect | Buat session baru; jangan gabungkan otomatis dengan session lama. |
| Shared user | Setiap device/session memiliki record sendiri; agregasi dilakukan di level username. |
| Missing poll | Jangan mengestimasi konsumsi tanpa dasar; lanjutkan pada snapshot berikutnya. |
| Data duplicate | Gunakan `collector_run_id` + unique constraint. |
| Browser disconnect | Client melakukan reconnect WebSocket dan meminta fresh snapshot. |
| WebSocket down | Dashboard fallback ke polling HTTP ringan untuk current state. |
| Voucher generated tetapi belum dipakai | Tidak dihitung sebagai penjualan/omzet. |
| Voucher dipakai pertama kali | Buat satu transaksi voucher dan tandai activated/used. |
| User bulanan belum bayar | Status periode = `UNPAID`. |
| User bulanan bayar sebagian | Status periode = `PARTIAL`. |
| Pembayaran dibatalkan | Ubah transaksi menjadi `VOID`, jangan hapus histori. |
| Harga bulanan berubah | Berlaku untuk periode baru; histori pembayaran menyimpan nominal saat transaksi. |

---

## 10. API / Backend Boundary

Laravel menyediakan endpoint internal untuk frontend, minimal:

```text
GET    /api/dashboard/overview
GET    /api/live/users
GET    /api/live/devices
GET    /api/live/traffic
GET    /api/usage/users
GET    /api/usage/users/{user}
GET    /api/usage/sessions/{session}
GET    /api/router/status
POST   /api/router/test-connection
GET    /api/pos/overview
GET    /api/pos/vouchers/usage
GET    /api/pos/monthly-customers
POST   /api/pos/monthly-payments
POST   /api/pos/monthly-payments/{payment}/void
GET    /api/pos/cashier-shifts/current
POST   /api/pos/cashier-shifts/open
POST   /api/pos/cashier-shifts/{shift}/close
```

WebSocket channels minimal:

```text
router.status
live.users
live.devices
live.traffic
pos.voucher.usage
pos.monthly.payment
```

Frontend tidak boleh memanggil RouterOS API secara langsung.

---

## 11. UI Sitemap

```text
Dashboard
├── Live Overview
│   ├── Online Users
│   ├── Online Devices
│   ├── Total Upload / Download Rate
│   ├── Router Resource
│   └── Realtime Traffic Chart
│
├── Users
│   ├── Online Users
│   ├── User Detail
│   │   ├── Active Devices
│   │   ├── Realtime Bandwidth
│   │   ├── Session History
│   │   └── Usage Chart
│   └── Historical Usage
│
├── Devices / Sessions
│   ├── Live Devices
│   ├── Device Detail
│   └── Session History
│
├── Hotspot
│   ├── Users
│   ├── Profiles
│   └── Generate Voucher
│
├── Voucher Printing
│   ├── Thermal 58mm
│   ├── Thermal 80mm
│   └── A4 / F4 Grid
│
├── POS / Kasir
│   ├── Overview
│   ├── Voucher Terpakai
│   ├── User Bulanan
│   │   ├── Daftar User
│   │   ├── Detail Tagihan
│   │   └── Input Pembayaran
│   ├── Transaksi
│   ├── Cashier Shift
│   └── Daily / Monthly Report
│
└── Settings
    ├── Router
    ├── Users & Roles
    ├── Voucher Template
    └── System / Retention
```

### 11.1 Mobile Friendly

UI wajib mobile-first:

- tabel besar berubah menjadi card/list pada layar kecil;
- filter menggunakan drawer/bottom sheet;
- chart dapat horizontal scroll/zoom;
- action utama tersedia sebagai sticky bottom action pada mobile bila sesuai;
- POS harus nyaman digunakan dari layar 5–7 inci;
- input pembayaran menggunakan numeric keypad pada mobile;
- tombol minimal touch target 44px;
- navigation mobile menggunakan bottom navigation atau hamburger menu yang konsisten.

---

## 12. Security

1. Router password wajib dienkripsi menggunakan Laravel application encryption.
2. Password tidak pernah dikirim kembali ke frontend setelah disimpan.
3. Gunakan API-SSL bila tersedia.
4. Terapkan RBAC di route, controller/service, dan action level.
5. Semua perubahan sensitif dicatat pada audit log.
6. Gunakan CSRF protection dan session timeout.
7. Gunakan rate limiting untuk endpoint authentication dan administrative actions.
8. Jangan expose credential RouterOS pada log aplikasi.
9. Perubahan harga bulanan dan void pembayaran wajib membutuhkan permission Admin.

---

## 13. Role Permission Matrix

| Fitur | Admin | Kasir | Teknisi |
|---|---:|---:|---:|
| Dashboard | ✅ | ✅ | ✅ |
| Live User / Device | ✅ | ✅ | ✅ |
| Historical Usage | ✅ | ✅ | ✅ |
| Router Settings | ✅ | ❌ | ✅ |
| Test Connection | ✅ | ❌ | ✅ |
| Generate Voucher | ✅ | ✅ | ❌ |
| Lihat Voucher Terpakai | ✅ | ✅ | ❌ |
| Manage Profile | ✅ | ❌ | ❌ |
| Reset User Counter | ✅ | ❌ | ✅ |
| Delete User | ✅ | ❌ | ❌ |
| User Management | ✅ | ❌ | ❌ |
| User Bulanan & Harga | ✅ | Lihat ✅ | ❌ |
| Input Pembayaran Bulanan | ✅ | ✅ | ❌ |
| Void Pembayaran | ✅ | ❌ | ❌ |
| Audit Log | ✅ | ❌ | ✅ |
| Cashier Shift | ✅ | ✅ | ❌ |

---

## 14. Tech Stack

- **Backend**: Laravel 11.x
- **PHP**: 8.2+
- **Queue**: Redis preferred
- **Realtime**: Laravel Reverb / WebSocket
- **Frontend**: Livewire 3 + Alpine.js atau Inertia.js + Vue 3
- **CSS**: Tailwind CSS
- **Chart**: ApexCharts atau Chart.js
- **Router API**: `evilfreelancer/routeros-api-php`
- **Database**: PostgreSQL preferred / MySQL
- **Web Server**: Nginx / Apache

---

## 15. Performance Target

### Live Data

- Live session refresh target: 2–5 detik.
- WebSocket event delivery target: < 1 detik setelah data collector tersedia.
- Dashboard tidak melakukan full page reload untuk pembaruan realtime.

### Dashboard

- Initial dashboard response target: < 1 detik pada data normal.
- Historical query harus menggunakan index dan server-side aggregation.

### Router Load

Collector harus diuji untuk memastikan polling realtime tidak menyebabkan kenaikan CPU router yang tidak wajar.

Target awal:

- rata-rata tambahan CPU MikroTik < 5% pada workload MVP yang diuji;
- interval polling dapat dinaikkan bila router menunjukkan beban tinggi.

---

## 16. Success Metrics

1. **Realtime Accuracy**: status online/offline device konsisten dengan active session MikroTik dalam toleransi polling.
2. **Usage Accuracy**: accounting Laravel tidak menghasilkan delta negatif atau double counting.
3. **Realtime Latency**: perubahan data yang diterima collector tampil di browser dalam target < 1 detik setelah event diproses.
4. **Historical Query**: filter 30 hari tetap responsif pada database dengan ratusan ribu snapshot.
5. **Router Stability**: collector tidak menyebabkan beban CPU router meningkat secara tidak wajar.
6. **POS Accuracy**: omzet voucher dihitung dari voucher yang benar-benar dipakai/diaktivasi, bukan dari voucher yang hanya digenerate.
7. **Monthly Billing Accuracy**: setiap periode user bulanan memiliki status pembayaran yang jelas dan nominal transaksi mengikuti tarif user pada saat pembayaran.

---

## 17. MVP Must Have

- [x] Laravel backend.
- [x] Single MikroTik router context.
- [x] Router connection/test.
- [x] Realtime online users.
- [x] Realtime devices/sessions.
- [x] Realtime upload/download bandwidth per device dan user.
- [x] Realtime interface traffic chart.
- [x] Historical usage dengan filter waktu.
- [x] User → Device → Session drill-down.
- [x] Session accounting berbasis snapshot + delta.
- [x] Counter reset handling.
- [x] Router reboot/session recovery.
- [x] Queue + locking untuk collector.
- [x] Daily usage summary.
- [x] Voucher generator.
- [x] Voucher printing QR.
- [x] Voucher revenue berdasarkan voucher yang dipakai/diaktivasi.
- [x] User bulanan dengan tarif per user.
- [x] Input pembayaran manual berdasarkan nama user dan periode tagihan.
- [x] Status tagihan UNPAID / PARTIAL / PAID / VOID.
- [x] Manual sales ledger.
- [x] Cashier shift.
- [x] RBAC.
- [x] Audit log.
- [x] Mobile-first POS UI.

### Fase 2

- [ ] Multi-router.
- [ ] Telegram/WhatsApp notification.
- [ ] Automatic FUP.
- [ ] Advanced anomaly detection.
- [ ] Advanced report export.

---

## 18. Acceptance Criteria Utama

### Realtime User

- Ketika user login di MikroTik, user muncul pada dashboard tanpa refresh manual.
- Ketika user logout, user berubah menjadi offline setelah melewati polling/grace period.

### Realtime Device

- Setiap device aktif tampil terpisah.
- MAC address dan IP dapat ditampilkan.
- Reconnect membuat session baru.

### Realtime Bandwidth

- Upload/download rate berubah mengikuti counter MikroTik.
- Tidak ada rate negatif.
- Tidak ada double counting karena job overlap.

### Historical Usage

- Admin dapat memilih range waktu.
- Total upload/download konsisten dengan session yang termasuk dalam range tersebut.
- User dapat melihat detail device/session.

### Router Failure

- Jika router offline, aplikasi tidak crash.
- Status router berubah menjadi `Unreachable`.
- Setelah router kembali online, collector melakukan resync dan membuat session baru bila diperlukan.

### Voucher POS

- Generate 100 voucher menghasilkan 100 inventory voucher, bukan 100 transaksi penjualan.
- Sebelum digunakan, omzet voucher = 0 untuk voucher tersebut.
- Saat sebuah voucher pertama kali dipakai/diaktivasi, tepat satu transaksi pendapatan dibuat.
- Reconnect user setelah voucher aktif tidak membuat transaksi voucher kedua.
- Voucher expired tanpa pernah dipakai tidak masuk omzet voucher.

### User Bulanan

- Admin dapat membuat user dengan tarif bulanan berbeda-beda.
- Operator dapat memilih user berdasarkan nama saat mencatat pembayaran.
- Sistem otomatis menampilkan tarif user tersebut.
- Operator memasukkan tanggal pembayaran secara manual.
- Satu periode tagihan tidak dapat dibayar dua kali kecuali transaksi pertama di-void.
- Histori pembayaran menyimpan nominal yang benar-benar dibayar, tanggal bayar, dan operator.
- Perubahan tarif bulan berikutnya tidak mengubah histori pembayaran bulan sebelumnya.

### Cashier Shift

- Transaksi voucher cash dan pembayaran bulanan cash masuk ke expected cash.
- Pembayaran transfer tidak masuk expected cash.
- Close shift menampilkan discrepancy.

---

## 19. Catatan Implementasi Penting

1. Jangan membuat polling MikroTik dari setiap request HTTP frontend.
2. Jangan menjadikan username + MAC sebagai primary key session.
3. Jangan menganggap `bytes_in/out` dari `/ip/hotspot/active` sebagai histori; data tersebut harus di-snapshot dan diakumulasikan.
4. Jangan mengandalkan browser sebagai sumber kebenaran realtime; Laravel collector adalah source of truth untuk live state.
5. **Generate voucher bukan transaksi penjualan. Voucher baru dihitung sebagai pendapatan ketika benar-benar dipakai/diaktivasi.**
6. **User bulanan adalah domain pembayaran terpisah dari voucher. Tarif bulanan melekat pada customer dan histori transaksi menyimpan nominal pada saat pembayaran.**
7. Semua operasi accounting harus idempotent.
8. Semua query historical harus memiliki index berdasarkan waktu dan identitas user/session.
9. Void harus digunakan untuk membatalkan transaksi; histori tidak boleh dihapus secara fisik.
10. UI POS harus tetap nyaman digunakan pada layar mobile 5–7 inci.
11. Perubahan harga user bulanan sebaiknya hanya berlaku untuk periode tagihan berikutnya kecuali Admin melakukan override secara eksplisit.

---
