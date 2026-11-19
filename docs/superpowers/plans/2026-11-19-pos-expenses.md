# POS Expense Tracking Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan modul pengeluaran (harian ad-hoc & bulanan tetap dengan snapshot independen) ke sistem POS MikroTik Manager dan mengintegrasikan biaya operasional ke perhitungan laba bersih dashboard POS.

**Architecture:** Menggunakan 3 tabel database baru (`daily_expenses`, `monthly_expense_templates`, `monthly_expense_entries`) untuk memisahkan pengeluaran harian dan snapshot bulanan tetap. Logika perhitungan dan auto-snapshot diisolasi di `PosManagerService`, di-expose melalui `PosController` dan REST/JSON endpoints, serta disajikan melalui tampilan Blade + Alpine.js (`pos/expenses.blade.php`) dan update KPI card di `pos/index.blade.php`.

**Tech Stack:** Laravel 11, PHP 8.2+, MySQL/MariaDB, Blade, Alpine.js, Tailwind CSS, PHPUnit/Pest.

**Spec:** `docs/superpowers/specs/2026-11-19-pos-expenses-design.md`

## Global Constraints

- Standar moneter Rupiah (`FormatHelper::formatRupiah` di Blade/PHP, `window.formatRupiah` di JS/Alpine).
- Snapshot bulanan tidak boleh menimpa bulan sebelumnya jika template diubah (`amount` disimpan langsung pada `monthly_expense_entries`).
- Pengeluaran harian disimpan dengan tanggal transaksi (`expense_date`), deskripsi, dan nominal > 0.
- Dashboard POS menampilkan Laba Operasional = Laba Kotor - Total Pengeluaran (Harian + Bulanan).
- Mengikuti tema gelap/terang (`dark:`) dan mobile responsive sesuai konvensi UI aplikasi.

## Review Focus

1. **Auto-snapshot idempotency:** Memastikan pemanggilan `generateMonthlyExpenseEntries` berkali-kali untuk bulan yang sama tidak membuat duplicate entries atau me-reset nominal yang telah diedit secara manual.
2. **Snapshot isolation:** Mengubah nominal template master di bulan B tidak boleh mengubah nominal entri bulan A yang sudah ada.
3. **Month boundary filtering:** Memastikan pengeluaran harian hanya teragregasi pada bulan kalender yang tepat (misal tanggal 2026-11-30 tidak masuk ke 2026-12).
4. **Zero/Negative net profit handling:** Memastikan jika pengeluaran melebihi laba kotor, angka laba operasional menjadi minus tanpa crash atau NaN pada kalkulasi margin/chart.
5. **Soft fail & user feedback:** Validasi form input nominal minimal 1 dan deskripsi tidak boleh kosong.

---

### Task 1: Database Migrations & Eloquent Models

**Files:**
- Create: `database/migrations/2026_11_19_000001_create_pos_expenses_tables.php`
- Create: `app/Models/DailyExpense.php`
- Create: `app/Models/MonthlyExpenseTemplate.php`
- Create: `app/Models/MonthlyExpenseEntry.php`
- Test: `tests/Unit/PosExpenseModelTest.php`

**Interfaces:**
- Produces: 
  - `DailyExpense`: `$fillable = ['description', 'amount', 'expense_date', 'created_by']`
  - `MonthlyExpenseTemplate`: `$fillable = ['name', 'amount', 'is_active']`
  - `MonthlyExpenseEntry`: `$fillable = ['template_id', 'name', 'amount', 'billing_month', 'notes']`

- [ ] **Step 1: Write the failing unit test for expense models and relationships**

Create `tests/Unit/PosExpenseModelTest.php`:
```php
<?php

namespace Tests\Unit;

use App\Models\DailyExpense;
use App\Models\MonthlyExpenseEntry;
use App\Models\MonthlyExpenseTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosExpenseModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_daily_expense(): void
    {
        $user = User::factory()->create();
        $expense = DailyExpense::create([
            'description' => 'Beli kabel patchcord',
            'amount' => 50000,
            'expense_date' => '2026-11-19',
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('daily_expenses', [
            'description' => 'Beli kabel patchcord',
            'amount' => 50000,
        ]);
        $this->assertEquals($user->id, $expense->creator->id);
    }

    public function test_template_and_monthly_entry_relationship(): void
    {
        $template = MonthlyExpenseTemplate::create([
            'name' => 'ISP Dedicated',
            'amount' => 1500000,
            'is_active' => true,
        ]);

        $entry = MonthlyExpenseEntry::create([
            'template_id' => $template->id,
            'name' => $template->name,
            'amount' => $template->amount,
            'billing_month' => '2026-11-01',
        ]);

        $this->assertDatabaseHas('monthly_expense_entries', [
            'name' => 'ISP Dedicated',
            'amount' => 1500000,
            'billing_month' => '2026-11-01',
        ]);
        $this->assertEquals($template->id, $entry->template->id);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php -d "xdebug.mode=off" artisan test --filter=PosExpenseModelTest`
Expected: FAIL (tables and classes not found).

- [ ] **Step 3: Create migration and models**

Create migration `database/migrations/2026_11_19_000001_create_pos_expenses_tables.php` with:
- `daily_expenses` table (id, description, amount decimal(12,2), expense_date date, created_by foreignId nullOnDelete, timestamps).
- `monthly_expense_templates` table (id, name string(100), amount decimal(12,2), is_active boolean default true, timestamps).
- `monthly_expense_entries` table (id, template_id foreignId nullOnDelete, name string(100), amount decimal(12,2), billing_month date, notes text nullable, timestamps, unique constraint `['template_id', 'billing_month']`).

Create models with relationships and casts:
- `DailyExpense`: belongsTo `User` via `created_by`.
- `MonthlyExpenseTemplate`: hasMany `MonthlyExpenseEntry` via `template_id`.
- `MonthlyExpenseEntry`: belongsTo `MonthlyExpenseTemplate` via `template_id`.

- [ ] **Step 4: Run migration and test to verify it passes**

Run: `php -d "xdebug.mode=off" artisan migrate`
Run: `php -d "xdebug.mode=off" artisan test --filter=PosExpenseModelTest`
Expected: PASS (2 passed).

- [ ] **Step 5: Commit**

```bash
git add database/migrations/*create_pos_expenses* app/Models/DailyExpense.php app/Models/MonthlyExpenseTemplate.php app/Models/MonthlyExpenseEntry.php tests/Unit/PosExpenseModelTest.php
git commit -m "feat(pos): add expenses migrations, models, and unit tests"
```

---

### Task 2: PosManagerService - Expense Logic & Dashboard Analytics Integration

**Files:**
- Modify: `app/Services/PosManagerService.php`
- Test: `tests/Feature/PosExpenseServiceTest.php`

**Interfaces:**
- Produces:
  - `PosManagerService::getExpensesForMonth(string $month): array`
  - `PosManagerService::generateMonthlyExpenseEntries(string $month): array`
  - `PosManagerService::getExpenseTotalsForMonth(string $month): array`
  - `PosManagerService::getDetailedDashboardAnalytics(?string $month = null): array` (updated with `total_expenses`, `daily_expenses_total`, `monthly_expenses_total`, `total_operational_profit`)

- [ ] **Step 1: Write the failing feature test for expense service logic**

Create `tests/Feature/PosExpenseServiceTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\DailyExpense;
use App\Models\MonthlyExpenseEntry;
use App\Models\MonthlyExpenseTemplate;
use App\Services\PosManagerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosExpenseServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_generate_monthly_entries_from_templates(): void
    {
        $service = app(PosManagerService::class);

        MonthlyExpenseTemplate::create(['name' => 'ISP 1', 'amount' => 500000, 'is_active' => true]);
        MonthlyExpenseTemplate::create(['name' => 'Listrik', 'amount' => 300000, 'is_active' => true]);
        MonthlyExpenseTemplate::create(['name' => 'Nonaktif', 'amount' => 100000, 'is_active' => false]);

        $data = $service->getExpensesForMonth('2026-11');

        $this->assertCount(2, $data['monthly_entries']);
        $this->assertEquals(800000, $data['monthly_total']);
    }

    public function test_snapshot_isolation_preserves_past_month_amount(): void
    {
        $service = app(PosManagerService::class);

        $template = MonthlyExpenseTemplate::create(['name' => 'ISP', 'amount' => 500000, 'is_active' => true]);

        // Generate for Month A (November)
        $service->getExpensesForMonth('2026-11');

        // Update template price in Month B (December)
        $template->update(['amount' => 600000]);

        // Generate for Month B
        $service->getExpensesForMonth('2026-12');

        // Verify Month A entry remains 500,000 while Month B is 600,000
        $entryNov = MonthlyExpenseEntry::whereDate('billing_month', '2026-11-01')->first();
        $entryDec = MonthlyExpenseEntry::whereDate('billing_month', '2026-12-01')->first();

        $this->assertEquals(500000, (float) $entryNov->amount);
        $this->assertEquals(600000, (float) $entryDec->amount);
    }

    public function test_dashboard_analytics_includes_expenses_and_operational_profit(): void
    {
        $service = app(PosManagerService::class);

        DailyExpense::create([
            'description' => 'Bensin & ATK',
            'amount' => 50000,
            'expense_date' => '2026-11-10',
        ]);

        MonthlyExpenseTemplate::create(['name' => 'ISP', 'amount' => 200000, 'is_active' => true]);

        $analytics = $service->getDetailedDashboardAnalytics('2026-11');

        $this->assertEquals(50000, $analytics['daily_expenses_total']);
        $this->assertEquals(200000, $analytics['monthly_expenses_total']);
        $this->assertEquals(250000, $analytics['total_expenses']);
        $this->assertEquals($analytics['total_net_profit'] - 250000, $analytics['total_operational_profit']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php -d "xdebug.mode=off" artisan test --filter=PosExpenseServiceTest`
Expected: FAIL (methods not implemented).

- [ ] **Step 3: Implement methods in `PosManagerService.php`**

Add methods to `app/Services/PosManagerService.php`:
1. `generateMonthlyExpenseEntries(string $month): array`:
   - Parse `$targetMonth = Carbon::parse($month)->startOfMonth()`.
   - Query all active `MonthlyExpenseTemplate::where('is_active', true)->get()`.
   - For each template, `firstOrCreate(['template_id' => $t->id, 'billing_month' => $targetMonth->toDateString()], ['name' => $t->name, 'amount' => $t->amount])`.
2. `getExpensesForMonth(string $month): array`:
   - Auto-calls `generateMonthlyExpenseEntries($month)`.
   - Fetches `DailyExpense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->orderByDesc('expense_date')->get()`.
   - Fetches `MonthlyExpenseEntry::whereDate('billing_month', $startOfMonth)->get()`.
   - Returns daily list, monthly entries, daily total, monthly total, combined total.
3. `getExpenseTotalsForMonth(string $month): array`:
   - Sums daily expenses and monthly entries for the month.
4. Update `getDetailedDashboardAnalytics(?string $month = null)`:
   - Call `$this->getExpenseTotalsForMonth($targetMonth->format('Y-m'))`.
   - Add keys: `daily_expenses_total`, `monthly_expenses_total`, `total_expenses`, and `total_operational_profit = $totalNetProfit - $totalExpenses`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php -d "xdebug.mode=off" artisan test --filter=PosExpenseServiceTest`
Expected: PASS (3 passed).

- [ ] **Step 5: Commit**

```bash
git add app/Services/PosManagerService.php tests/Feature/PosExpenseServiceTest.php
git commit -m "feat(pos): implement expense service methods and dashboard analytics integration"
```

---

### Task 3: PosController & Routes for Expenses CRUD

**Files:**
- Modify: `app/Http/Controllers/PosController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/PosExpenseControllerTest.php`

**Interfaces:**
- Routes:
  - `GET /pos/expenses` (`pos.expenses`)
  - `POST /pos/expenses/daily`
  - `PUT /pos/expenses/daily/{dailyExpense}`
  - `DELETE /pos/expenses/daily/{dailyExpense}`
  - `POST /pos/expenses/templates`
  - `PUT /pos/expenses/templates/{monthlyExpenseTemplate}`
  - `DELETE /pos/expenses/templates/{monthlyExpenseTemplate}`
  - `PUT /pos/expenses/entries/{monthlyExpenseEntry}`
  - `POST /pos/expenses/entries/sync`

- [ ] **Step 1: Write the failing controller feature test**

Create `tests/Feature/PosExpenseControllerTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\DailyExpense;
use App\Models\MonthlyExpenseEntry;
use App\Models\MonthlyExpenseTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_view_expenses_page(): void
    {
        $response = $this->get(route('pos.expenses'));
        $response->assertStatus(200);
        $response->assertViewIs('pos.expenses');
    }

    public function test_can_crud_daily_expense(): void
    {
        // Store
        $postRes = $this->postJson('/pos/expenses/daily', [
            'description' => 'Servis Mikrotik',
            'amount' => 120000,
            'expense_date' => '2026-11-19',
        ]);
        $postRes->assertStatus(200)->assertJson(['success' => true]);
        $expense = DailyExpense::first();
        $this->assertNotNull($expense);

        // Update
        $putRes = $this->putJson("/pos/expenses/daily/{$expense->id}", [
            'description' => 'Servis Mikrotik Core',
            'amount' => 150000,
            'expense_date' => '2026-11-19',
        ]);
        $putRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('daily_expenses', ['amount' => 150000]);

        // Delete
        $delRes = $this->deleteJson("/pos/expenses/daily/{$expense->id}");
        $delRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('daily_expenses', ['id' => $expense->id]);
    }

    public function test_can_crud_expense_template_and_update_entry(): void
    {
        // Store template
        $tRes = $this->postJson('/pos/expenses/templates', [
            'name' => 'Sewa Tiang',
            'amount' => 250000,
            'is_active' => true,
        ]);
        $tRes->assertStatus(200)->assertJson(['success' => true]);
        $template = MonthlyExpenseTemplate::first();

        // View expenses page to trigger entry creation
        $this->get('/pos/expenses?month=2026-11');
        $entry = MonthlyExpenseEntry::where('template_id', $template->id)->first();
        $this->assertNotNull($entry);

        // Update entry specifically for this month
        $eRes = $this->putJson("/pos/expenses/entries/{$entry->id}", [
            'amount' => 275000,
            'notes' => 'Ada kenaikan sewa bulan ini',
        ]);
        $eRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('monthly_expense_entries', [
            'id' => $entry->id,
            'amount' => 275000,
            'notes' => 'Ada kenaikan sewa bulan ini',
        ]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php -d "xdebug.mode=off" artisan test --filter=PosExpenseControllerTest`
Expected: FAIL (route pos.expenses not found).

- [ ] **Step 3: Implement controller methods and register routes**

In `routes/web.php` under the `pos` group:
```php
Route::get('/expenses', [PosController::class, 'expenses'])->name('pos.expenses');
Route::post('/expenses/daily', [PosController::class, 'storeDailyExpense'])->name('pos.expenses.daily.store');
Route::put('/expenses/daily/{dailyExpense}', [PosController::class, 'updateDailyExpense'])->name('pos.expenses.daily.update');
Route::delete('/expenses/daily/{dailyExpense}', [PosController::class, 'deleteDailyExpense'])->name('pos.expenses.daily.delete');
Route::post('/expenses/templates', [PosController::class, 'storeExpenseTemplate'])->name('pos.expenses.templates.store');
Route::put('/expenses/templates/{template}', [PosController::class, 'updateExpenseTemplate'])->name('pos.expenses.templates.update');
Route::delete('/expenses/templates/{template}', [PosController::class, 'deleteExpenseTemplate'])->name('pos.expenses.templates.delete');
Route::put('/expenses/entries/{entry}', [PosController::class, 'updateMonthlyExpenseEntry'])->name('pos.expenses.entries.update');
```

In `app/Http/Controllers/PosController.php`:
Implement:
- `expenses(Request $request): View`
- `storeDailyExpense(Request $request): JsonResponse`
- `updateDailyExpense(Request $request, DailyExpense $dailyExpense): JsonResponse`
- `deleteDailyExpense(DailyExpense $dailyExpense): JsonResponse`
- `storeExpenseTemplate(Request $request): JsonResponse`
- `updateExpenseTemplate(Request $request, MonthlyExpenseTemplate $template): JsonResponse`
- `deleteExpenseTemplate(MonthlyExpenseTemplate $template): JsonResponse`
- `updateMonthlyExpenseEntry(Request $request, MonthlyExpenseEntry $entry): JsonResponse`

Create temporary minimal view `resources/views/pos/expenses.blade.php` with placeholder if not yet designed.

- [ ] **Step 4: Run test to verify it passes**

Run: `php -d "xdebug.mode=off" artisan test --filter=PosExpenseControllerTest`
Expected: PASS (3 passed).

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/PosController.php routes/web.php tests/Feature/PosExpenseControllerTest.php
git commit -m "feat(pos): add controller methods and routes for expense management"
```

---

### Task 4: UI View `resources/views/pos/expenses.blade.php` and Navigation

**Files:**
- Create: `resources/views/pos/expenses.blade.php`
- Modify: `resources/views/components/layouts/app.blade.php` (sidebar navigation)
- Modify: `resources/views/layouts/app.blade.php` (if standalone layouts exist)
- Modify: `resources/views/pos/index.blade.php` (add top button to Pengeluaran)
- Modify: `resources/views/pos/vouchers.blade.php` (add top button to Pengeluaran)
- Modify: `resources/views/pos/monthly.blade.php` (add top button to Pengeluaran)

**Interfaces:**
- Blade Component with Alpine `expensesManager()`:
  - Tab 1: Pengeluaran Harian (List with Date, Description, Amount, Creator, Actions Edit/Delete, Add Modal)
  - Tab 2: Pengeluaran Bulanan Tetap (List of Monthly Entries for selected month, Edit Amount Modal, and Master Templates CRUD modal)
  - Bottom summary bar: Total Harian, Total Bulanan, Total Seluruh Pengeluaran Periode Ini.
  - Month selector in header synced with URL query parameter `?month=YYYY-MM`.

- [ ] **Step 1: Build `resources/views/pos/expenses.blade.php`**

Implement complete Blade template following the design guidelines:
- High contrast, dark-mode ready (`dark:text-zinc-100`, `dark:bg-zinc-900`, `dark:border-zinc-800`).
- Semantic buttons with standardized heights and clear hover states.
- Clean modals for `daily-expense-modal`, `edit-monthly-entry-modal`, and `template-modal`.
- Interactive Alpine.js state handling optimistic UI updates and toast notifications.

- [ ] **Step 2: Add Navigation Link to Sidebar and Header Toolbars**

In `resources/views/components/layouts/app.blade.php`:
Under "POS & Keuangan" group, add:
```blade
<a href="{{ route('pos.expenses') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('pos.expenses*') ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
    <svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
    Pengeluaran
</a>
```
Also add the button in the header toolbar of `pos/index.blade.php`, `pos/vouchers.blade.php`, and `pos/monthly.blade.php`.

- [ ] **Step 3: Build frontend assets and verify**

Run: `npm run build`
Expected: Success with no build errors.

- [ ] **Step 4: Commit**

```bash
git add resources/views/pos/expenses.blade.php resources/views/components/layouts/app.blade.php resources/views/pos/index.blade.php resources/views/pos/vouchers.blade.php resources/views/pos/monthly.blade.php
git commit -m "feat(pos): add expenses blade view and navigation links"
```

---

### Task 5: POS Dashboard KPI Cards & Expense Breakdown

**Files:**
- Modify: `resources/views/pos/index.blade.php`

**Interfaces:**
- KPI Card #2 updated:
  - Title: "Laba Operasional"
  - Primary metric: `formatRupiah(overview.total_operational_profit)`
  - Subtext breakdown:
    - Laba Kotor: `formatRupiah(overview.total_net_profit)`
    - Pengeluaran: `formatRupiah(overview.total_expenses)` (dengan warna rose/merah)
- Add Expense metric or summary card in POS dashboard overview.

- [ ] **Step 1: Update Card #2 in `resources/views/pos/index.blade.php`**

Adjust lines 64-84:
- Change header to "Laba Operasional".
- Display `overview.total_operational_profit`.
- Show breakdown: "Laba Kotor: +Rp..." and "Pengeluaran: -Rp...".

- [ ] **Step 2: Verify in browser/tests**

Run: `php -d "xdebug.mode=off" artisan test --filter=Pos`
Expected: PASS.

- [ ] **Step 3: Commit**

```bash
git add resources/views/pos/index.blade.php
git commit -m "feat(pos): update dashboard KPI to show operational profit and expense deduction"
```

---

### Task 6: Full Verification & Automated Tests Integration

**Files:**
- Modify: `tests/Feature/FrontendPagesTest.php`
- Modify: `app/Services/MaintenanceService.php` (include daily expenses in data cleanup if needed)

**Interfaces:**
- Ensure `/pos/expenses` is included in authenticated frontend pages test list.
- All test suites (`Pos`, `Monthly`, `FrontendPagesTest`) pass with 0 failures.

- [ ] **Step 1: Add `/pos/expenses` to `FrontendPagesTest.php`**

Add `'/pos/expenses'` to the `$routes` array in `test_all_frontend_routes_render_successfully_when_authenticated()`.

- [ ] **Step 2: Run all test suites**

Run:
1. `php -d "xdebug.mode=off" artisan test --filter=PosExpense`
2. `php -d "xdebug.mode=off" artisan test --filter=Pos`
3. `php -d "xdebug.mode=off" artisan test --filter=Monthly`
4. `php -d "xdebug.mode=off" artisan test --filter=FrontendPagesTest`
5. `npm run build`
Expected: 100% tests pass.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/FrontendPagesTest.php app/Services/MaintenanceService.php
git commit -m "test(pos): add expenses route to frontend page tests and verify maintenance service"
```
