# Print Preview Modal & WhatsApp Share Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Provide an in-page print preview pop-up modal with direct printing capability and a WhatsApp voucher sharing feature, eliminating the need to leave the page or open extra browser tabs.

**Architecture:** 
1. **Controller Enhancement**: Pass active `RouterSetting` (hotspot name, login URL, contact info) to `vouchers.index`.
2. **Print Preview Modal**: An accessible in-page modal dialog in `vouchers/index.blade.php` containing a format switcher (Grid A4, Thermal 58mm, Thermal 80mm), direct print trigger (`iframe.contentWindow.print()`), and responsive iframe viewport.
3. **WhatsApp Share Modal & Helpers**: An integrated share modal with WhatsApp message generation (including hotspot name, credentials, validity, price, login instructions), customer phone number input, direct `wa.me` launcher, and one-click clipboard copy.
4. **Interactive Action Hooks**: Wire up header print button, floating multi-select bar buttons, and per-voucher row/card actions to open the preview modal and WhatsApp share dialog.

**Tech Stack:** Laravel 11, Blade Components (`<x-modal>`), Alpine.js, Tailwind CSS, Heroicons.

---

## Global Constraints
- Do NOT run any git commands ("Tidak perlu cek git dan fungsi-fungsi lain diluar permintaan saya").
- Indonesian date formatting and numeric thousand separators (`FormatHelper` / `formatNumber`) across all views.
- Adhere to `antislop-ui`: clean UI, proper tap targets, no generic gradients or decorative emoji spam.
- Maintain full backwards compatibility with existing print routes and `FrontendPagesTest`.

---

## Task Decomposition

### Task 1: Controller Data Provision
**Files:**
- Modify: `app/Http/Controllers/VoucherController.php:140-160`
- Test: `tests/Feature/FrontendPagesTest.php`

- [ ] 1.1 Query active `RouterSetting` in `VoucherController@index` and pass it to the view `compact('..., 'routerSetting')`.
- [ ] 1.2 Verify `php "-dxdebug.mode=off" vendor/bin/phpunit tests/Feature/FrontendPagesTest.php --filter test_pages_render_without_intl_with_usage_data` passes.

---

### Task 2: Implement Print Preview Modal & Direct Printing
**Files:**
- Modify: `resources/views/vouchers/index.blade.php`
- Test: `tests/Feature/FrontendPagesTest.php`

- [ ] 2.1 Add `<x-modal name="print-preview-modal" ...>` containing:
  - Header: Modal title, layout switcher tabs (`Grid A4`, `58mm`, `80mm`).
  - Action buttons: "Cetak Langsung" (triggers `printIframe()`), "Bagikan WA", "Tutup".
  - Body: Responsive `<iframe>` with loading indicator and crisp rendering.
- [ ] 2.2 In `vouchersManager()` Alpine component, implement:
  - `previewFormat`: default `'grid'`
  - `previewUrl`: computed URL based on format and selected voucher IDs / active filters
  - `openPrintPreview(format, ids)`
  - `switchPreviewFormat(format)`
  - `printIframe()`: invokes `contentWindow.print()` safely with fallback.
- [ ] 2.3 Wire up "Cetak Grid" header button and Floating Action Bar print buttons to call `openPrintPreview(...)` instead of `window.open(..., '_blank')`.
- [ ] 2.4 Add quick print preview button to each row in Desktop Table and Mobile Card.

---

### Task 3: Implement WhatsApp Share Modal & Template Generator
**Files:**
- Modify: `resources/views/vouchers/index.blade.php`
- Test: `tests/Feature/FrontendPagesTest.php`

- [ ] 3.1 Add `<x-modal name="whatsapp-share-modal" ...>` containing:
  - Target customer phone number input (e.g., `08123456789`) with auto-prefix formatting.
  - Formatted WhatsApp message preview box with line breaks, monospace voucher codes, and clear bold headers.
  - "Buka WhatsApp" button linking to `https://wa.me/{phone}?text={encoded}`.
  - "Salin Pesan" button using clipboard helper.
- [ ] 3.2 In `vouchersManager()` Alpine component, implement:
  - `generateWhatsAppText(voucher)`: builds professional message with router hotspot name, username, password, profile, validity, price, login URL, and thank-you note.
  - `openWhatsAppModal(voucher)`: pre-fills message and target phone.
  - `sendWhatsApp()`: validates phone and opens WhatsApp Web/App.
- [ ] 3.3 Add "Bagikan WA" icon button on each voucher row in desktop table, mobile card, and inside the print preview modal.

---

### Task 4: Asset Build & End-to-End Verification
**Files:**
- Test: `tests/Feature/FrontendPagesTest.php`
- Test: `tests/Feature/VoucherCategoryFilterTest.php`

- [ ] 4.1 Run `npm run build` to ensure assets compile with zero errors.
- [ ] 4.2 Run full `FrontendPagesTest.php` suite to ensure all assertions pass.
- [ ] 4.3 Verify modal behavior, keyboard/backdrop close, and responsive layout.
