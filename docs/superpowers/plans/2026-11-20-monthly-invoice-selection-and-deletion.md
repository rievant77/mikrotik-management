# Monthly Invoice Selection and Deletion Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Enable selective customer invoice generation and single/batch invoice deletion with payment cascading in POS Monthly Billing.

**Architecture:** Extend `PosManagerService` and `PosController` to handle single and batch invoice deletion inside database transactions, cascading deletion to associated `MonthlyPayment` records and recalculating active shift cash if cash payments existed. Upgrade the Blade and Alpine.js UI in `resources/views/pos/monthly.blade.php` to provide customer selection checkboxes in the generation modal and multi-select deletion controls on the invoice table.

**Tech Stack:** Laravel 11 (PHP 8.2+), Eloquent ORM, Alpine.js, Tailwind CSS, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-11-20-monthly-invoice-selection-and-deletion-design.md`

## Global Constraints

- Never break existing POS test suites (`FrontendPagesTest`, `PosAccountingTest`, `PosExpense*`).
- Maintain consistent dark mode and styling standards aligned with Tailwind CSS and Antislop UI rules.
- Wrap all destructive actions (invoice deletion + payment deletion) in database transactions with audit logging.
- Deletion of invoices must cascade-delete all linked `MonthlyPayment` records without leaving orphaned rows.

## Review Focus

- Deleting an invoice that has recorded cash payments must trigger shift cash recalculation so cashier shifts remain accurate.
- Generating invoices for a subset of customers must only create invoices for those specified and skip unselected customers.
- Submitting an empty customer selection during generate must either validate or alert cleanly instead of creating zero invoices unexpectedly.
- Batch deleting empty selection or invalid invoice IDs must handle gracefully without crashing or throwing unhandled 500 errors.
- Frontend multi-select state must clear after batch deletion or table filtering to prevent deleting unselected rows accidentally.

---

### Task 1: Backend Invoice Deletion and Cascading in Service, Controller, and Routes

**Files:**
- Modify: `app/Services/PosManagerService.php`
- Modify: `app/Http/Controllers/PosController.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/MonthlyInvoiceManagementTest.php`

**Interfaces:**
- Consumes: `MonthlyInvoice`, `MonthlyPayment`, `CashierShift`, `AuditLog`
- Produces: 
  - `PosManagerService::deleteMonthlyInvoice(MonthlyInvoice $invoice): bool`
  - `PosManagerService::batchDeleteMonthlyInvoices(array $invoiceIds): int`
  - `PosController::deleteInvoice(MonthlyInvoice $invoice): JsonResponse`
  - `PosController::batchDeleteInvoices(Request $request): JsonResponse`
  - Route `DELETE /pos/monthly/invoices/{invoice}` (`pos.monthly.invoices.delete`)
  - Route `POST /pos/monthly/invoices/batch-delete` (`pos.monthly.invoices.batch-delete`)

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/MonthlyInvoiceManagementTest.php` with tests:
1. `test_selective_customer_invoice_generation`: generate with specific `customer_ids`, asserting only selected customers have invoices.
2. `test_single_invoice_deletion_cascades_payments`: create invoice with payment, delete invoice, assert invoice and payments are deleted.
3. `test_single_invoice_deletion_recalculates_cashier_shift`: create invoice with cash payment in open shift, delete invoice, assert shift expected cash is adjusted.
4. `test_batch_invoice_deletion`: create 3 invoices with payments, batch delete 2 invoices, assert 2 deleted and 1 remaining.

- [ ] **Step 2: Run test to verify it fails**

Run: `php -d "xdebug.mode=off" artisan test --filter=MonthlyInvoiceManagementTest`
Expected: FAIL with route or method not defined.

- [ ] **Step 3: Implement Service methods in `app/Services/PosManagerService.php`**

Implement:
- `deleteMonthlyInvoice(MonthlyInvoice $invoice): bool`
- `batchDeleteMonthlyInvoices(array $invoiceIds): int`
Both methods wrap operations in DB transactions, cascade delete payments, recalculate shift expected cash for affected cash payments, log to `AuditLog`, and delete the invoices.

- [ ] **Step 4: Implement Controller endpoints in `app/Http/Controllers/PosController.php` & Routes in `routes/web.php`**

Add `deleteInvoice` and `batchDeleteInvoices` methods in `PosController.php`.
Register routes in `routes/web.php`:
- `Route::delete('/monthly/invoices/{invoice}', [PosController::class, 'deleteInvoice'])->name('pos.monthly.invoices.delete');`
- `Route::post('/monthly/invoices/batch-delete', [PosController::class, 'batchDeleteInvoices'])->name('pos.monthly.invoices.batch-delete');`

- [ ] **Step 5: Run tests to verify they pass**

Run: `php -d "xdebug.mode=off" artisan test --filter=MonthlyInvoiceManagementTest`
Expected: PASS (4/4 tests passing)

- [ ] **Step 6: Commit**

```bash
git add app/Services/PosManagerService.php app/Http/Controllers/PosController.php routes/web.php tests/Feature/MonthlyInvoiceManagementTest.php
git commit -m "feat(pos): add single and batch invoice deletion with payment cascade"
```

---

### Task 2: Frontend Customer Selection in Generate Invoices Modal

**Files:**
- Modify: `resources/views/pos/monthly.blade.php`

**Interfaces:**
- Consumes: `customers` collection from controller, `POST /pos/monthly/invoices/generate`
- Produces: UI checklist with search input and select-all inside `generate-invoices-modal`

- [ ] **Step 1: Update Alpine state and methods in `monthlyBillingManager`**

In `resources/views/pos/monthly.blade.php`:
- Add properties to Alpine component:
  - `selectedCustomerIds: []`
  - `customerSelectSearch: ''`
- Add helper methods/getters:
  - `get selectableCustomers()`: filter `customers` by `customerSelectSearch` (name, contact, or profile name).
  - `toggleSelectAllCustomers(e)`: toggle all selectable customer IDs into `selectedCustomerIds`.
  - `isAllCustomersSelected()`: check if all selectable customers are in `selectedCustomerIds`.
- On opening `generate-invoices-modal`: initialize `selectedCustomerIds` with all active customer IDs (`customers.map(c => c.id)`).
- Update `submitGenerateInvoices()`: send `{ billing_month: this.genForm.month, customer_ids: this.selectedCustomerIds }`. Prevent submission with toast message if `selectedCustomerIds.length === 0`.

- [ ] **Step 2: Update modal markup in `generate-invoices-modal`**

Add:
- Customer search bar input.
- "Pilih Semua" checkbox header with counter: `<span x-text="selectedCustomerIds.length"></span> dari <span x-text="customers.length"></span> pelanggan dipilih`.
- Scrollable list (max-h-60 overflow-y-auto) with individual customer items:
  - Checkbox bound to `selectedCustomerIds`.
  - Customer name, username/phone badge, package name, and monthly price formatted in Rupiah.
- Dynamic estimation footer displaying total monthly fee of selected customers.

- [ ] **Step 3: Verification of modal rendering and tests**

Run: `php -d "xdebug.mode=off" artisan test --filter=FrontendPagesTest`
Expected: PASS

- [ ] **Step 4: Commit**

```bash
git add resources/views/pos/monthly.blade.php
git commit -m "feat(pos): add customer selection checklist in generate monthly invoices modal"
```

---

### Task 3: Frontend Invoices Table Checkbox Multi-Select, Batch Toolbar, and Single Delete Action

**Files:**
- Modify: `resources/views/pos/monthly.blade.php`

**Interfaces:**
- Consumes: `filteredInvoices`, `DELETE /pos/monthly/invoices/{invoice}`, `POST /pos/monthly/invoices/batch-delete`
- Produces: Invoices table with multi-select checkboxes, batch action bar, and single delete button with confirmation

- [ ] **Step 1: Update Alpine state and methods for invoice deletion**

In `resources/views/pos/monthly.blade.php`:
- Add state: `selectedInvoiceIds: []`, `isBatchDeleting: false`.
- Add methods:
  - `toggleSelectAllInvoices(e)`: select/deselect all `filteredInvoices.map(i => i.id)`.
  - `isAllInvoicesSelected()`: returns true if all filtered invoices are in `selectedInvoiceIds`.
  - `confirmDeleteInvoice(inv)`: prompt confirmation via `confirm()`, mentioning invoice number, customer name, and that linked payments will also be deleted. On confirm, send `DELETE /pos/monthly/invoices/{id}`.
  - `confirmBatchDeleteInvoices()`: prompt confirmation with count of selected invoices. On confirm, send `POST /pos/monthly/invoices/batch-delete` with `{ invoice_ids: selectedInvoiceIds }`. On success, clear `selectedInvoiceIds` and reload or filter out deleted invoices.

- [ ] **Step 2: Add Batch Action Bar and Table Column Markup**

In `resources/views/pos/monthly.blade.php`:
- Add Batch Action Bar above the invoices table, visible when `selectedInvoiceIds.length > 0`:
  - Display badge: "X tagihan dipilih".
  - Button "Hapus Tagihan Terpilih" (styled in red/danger with trash icon).
  - Button "Batal Pilih" to clear `selectedInvoiceIds = []`.
- In Table Header (`<thead>`):
  - Add checkbox column for "Pilih Semua".
- In Table Body (`<tbody>`):
  - Add checkbox column for each row bound to `selectedInvoiceIds`.
  - In Aksi column: add "Hapus" button (red/danger styled, trash icon) alongside "Bayar" and "Riwayat".

- [ ] **Step 3: Verification with existing and new feature tests**

Run: `php -d "xdebug.mode=off" artisan test --filter=Monthly`
Run: `php -d "xdebug.mode=off" artisan test --filter=FrontendPagesTest`
Expected: PASS

- [ ] **Step 4: Commit**

```bash
git add resources/views/pos/monthly.blade.php
git commit -m "feat(pos): add single and batch invoice deletion controls on invoices table"
```

---

### Task 4: Full Suite Regression Verification and Build

**Files:**
- Verify: Full codebase test suite & frontend asset build

- [ ] **Step 1: Run complete test suite**

Run: `php -d "xdebug.mode=off" artisan test`
Expected: All tests PASS.

- [ ] **Step 2: Run asset compilation**

Run: `npm run build`
Expected: Build successfully completes with 0 errors.

- [ ] **Step 3: Commit and clean working tree**

```bash
git status
```
