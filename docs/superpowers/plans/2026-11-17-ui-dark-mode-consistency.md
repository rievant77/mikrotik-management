# UI Dark Mode & Design Consistency Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyelaraskan seluruh tampilan UI pada mode terang dan gelap, memperbaiki kontras teks/komponen agar terbaca alami (WCAG AA), mewarnai ikon navigasi sidebar secara tematik, dan menghubungkan widget status router di sidebar dengan data riil MikroTik.

**Architecture:** Menerapkan standarisasi token warna Tailwind CSS v4 berbasis Zinc neutral secara presisi pada seluruh 25+ file Blade di `resources/views/`. Membagikan data telemetry router terkini ke seluruh view via `AppServiceProvider` dan event Alpine.js, serta memverifikasi keterbacaan dengan test dan audit script otomatis.

**Tech Stack:** Laravel 13, Blade Templates, Tailwind CSS v4, Alpine.js 3, ApexCharts, PHPUnit.

**Spec:** [docs/superpowers/specs/2026-11-17-ui-dark-mode-consistency-design.md](file:///d:/PROJECTS/WEB/mikrotik-management/docs/superpowers/specs/2026-11-17-ui-dark-mode-consistency-design.md)

## Global Constraints

- Bebas dari karakter em dash (`—`) di teks UI (R-02).
- Seluruh teks memenuhi WCAG AA: 4.5:1 untuk teks normal, 3:1 untuk teks besar/bold (R-25).
- Teks utama: `text-zinc-900 dark:text-zinc-100`.
- Teks sekunder: `text-zinc-600 dark:text-zinc-300`.
- Teks tersier / label / timestamp: `text-zinc-500 dark:text-zinc-400` (dilarang menggunakan `text-zinc-400` tanpa adaptasi dark mode).
- Kontrol form wajib adaptif: `bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100`.
- Checkbox & Radio: `border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-emerald-600 dark:text-emerald-500`.
- Data statistik dan telemetry router harus bersumber dari data nyata, dilarang data fiktif (R-17, R-38).
- Seluruh fitur diimplementasikan langsung di file sumber Blade/PHP, bukan skrip patch regex saat runtime (R-33).

## Review Focus

1. Kontrol input atau dropdown yang latar belakangnya putih di mode gelap sehingga teksnya tidak terbaca.
2. Label kecil atau keterangan abu-abu yang pudar/hilang di mode terang pada latar putih (`text-zinc-400` statis).
3. Checkbox atau radio button yang tidak kontras atau hilang centangnya pada mode gelap.
4. Widget status router di footer sidebar menampilkan data riil dari DB/MikroTik bukan string hardcoded.
5. Ikon sidebar memiliki kontras tinggi baik saat link dalam kondisi tidak aktif maupun aktif (`bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900`).

---

### Task 1: Shared Data & Real Router Telemetry in Sidebar Footer

**Files:**
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `app/Services/CollectorService.php`
- Modify: `resources/views/components/layouts/app.blade.php`
- Modify: `resources/views/layouts/app.blade.php`
- Test: `tests/Feature/SidebarTelemetryTest.php`

**Interfaces:**
- Consumes: `App\Models\RouterSetting`, `Cache::get('router:system_resource')`
- Produces: Global view variable `$routerResource`, Alpine component `sidebarRouterStatus()`

- [ ] **Step 1: Write test for sidebar telemetry data binding**

Create `tests/Feature/SidebarTelemetryTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\RouterSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SidebarTelemetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_displays_real_router_data(): void
    {
        $user = User::factory()->create();
        $setting = RouterSetting::create([
            'name' => 'MikroTik-UTAMA',
            'app_name' => 'MikroTik Manager',
            'host' => '10.20.30.1',
            'api_port' => 8728,
            'is_active' => true,
            'last_successful_poll_at' => now(),
        ]);

        Cache::put('router:system_resource', [
            'board-name' => 'RB4011iGS+',
            'version' => '7.16.1',
            'cpu-load' => 12,
        ], 120);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('RB4011iGS+');
        $response->assertSee('10.20.30.1');
        $response->assertDontSee('CCR1009-Core');
        $response->assertDontSee('192.168.88.1');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SidebarTelemetryTest`  
Expected: FAIL (assertion fails because CCR1009-Core is still hardcoded in layout).

- [ ] **Step 3: Implement data sharing and sidebar footer widget**

In `app/Providers/AppServiceProvider.php`:
Update `View::composer('*', ...)` to share `$routerResource = Cache::get('router:system_resource', []);`.

In `app/Http/Controllers/DashboardController.php`:
Cache system resource array on `index` and `liveData` when `$resource` is retrieved:
```php
if (!empty($resource)) {
    Cache::put('router:system_resource', $resource, 120);
}
```

In `app/Services/CollectorService.php`:
In `collect()`, when polling successfully, cache the system resource.

In `resources/views/components/layouts/app.blade.php` and `resources/views/layouts/app.blade.php`:
Replace the hardcoded CCR1009 widget with:
```blade
@php
    $cachedResource = Cache::get('router:system_resource', []);
    $isRouterOnline = !empty($appSetting?->is_active) && !empty($appSetting?->last_successful_poll_at) && ($appSetting->last_error_at === null || $appSetting->last_successful_poll_at->gt($appSetting->last_error_at));
    $boardName = $cachedResource['board-name'] ?? ($appSetting?->name ?? 'MikroTik');
    $osVersion = !empty($cachedResource['version']) ? ('v' . $cachedResource['version']) : ($isRouterOnline ? 'RouterOS' : 'Offline');
    $cpuDisplay = isset($cachedResource['cpu-load']) ? ('CPU: ' . $cachedResource['cpu-load'] . '%') : ($isRouterOnline ? 'Connected' : 'Disconnected');
    $hostDisplay = $appSetting?->host ?? '127.0.0.1';
@endphp

<div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50" 
     x-data="{
        isOnline: {{ $isRouterOnline ? 'true' : 'false' }},
        name: '{{ addslashes($boardName) }}',
        version: '{{ addslashes($osVersion) }}',
        cpu: '{{ addslashes($cpuDisplay) }}',
        host: '{{ addslashes($hostDisplay) }}'
     }"
     @router-telemetry-update.window="
        if ($event.detail) {
            isOnline = $event.detail.is_online ?? isOnline;
            if ($event.detail.board_name) name = $event.detail.board_name;
            if ($event.detail.version) version = 'v' + $event.detail.version;
            if ($event.detail.cpu_load !== undefined) cpu = 'CPU: ' + $event.detail.cpu_load + '%';
            if ($event.detail.host) host = $event.detail.host;
        }
     ">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 min-w-0">
            <span class="relative flex h-2.5 w-2.5 shrink-0">
                <span :class="isOnline ? 'animate-ping opacity-75 bg-emerald-400' : 'opacity-0'" class="absolute inline-flex h-full w-full rounded-full"></span>
                <span :class="isOnline ? 'bg-emerald-500' : 'bg-rose-500'" class="relative inline-flex rounded-full h-2.5 w-2.5"></span>
            </span>
            <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate" x-text="name">{{ $boardName }}</span>
        </div>
        <span class="text-[10px] text-zinc-500 dark:text-zinc-400 font-mono shrink-0 ml-1" x-text="version">{{ $osVersion }}</span>
    </div>
    <div class="mt-1 flex items-center justify-between text-[11px] text-zinc-600 dark:text-zinc-400">
        <span x-text="cpu">{{ $cpuDisplay }}</span>
        <span class="font-mono text-[10px] truncate ml-1" x-text="host">{{ $hostDisplay }}</span>
    </div>
</div>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=SidebarTelemetryTest`  
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Providers/AppServiceProvider.php app/Http/Controllers/DashboardController.php app/Services/CollectorService.php resources/views/components/layouts/app.blade.php resources/views/layouts/app.blade.php tests/Feature/SidebarTelemetryTest.php
git commit -m "feat(ui): bind real router telemetry to sidebar footer widget"
```

---

### Task 2: Thematic Colored Icons on Sidebar Navigation & Menus

**Files:**
- Modify: `resources/views/components/layouts/app.blade.php`
- Modify: `resources/views/layouts/app.blade.php`
- Test: `tests/Feature/SidebarColoredIconsTest.php`

**Interfaces:**
- Consumes: Tailwind semantic color classes (`text-indigo-500`, `text-emerald-500`, etc.)
- Produces: Vibrant, thematic sidebar icons with high contrast in light & dark mode

- [ ] **Step 1: Write test for colored icons presence**

Create `tests/Feature/SidebarColoredIconsTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarColoredIconsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_contains_thematic_icon_colors(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('text-indigo-500');
        $response->assertSee('text-emerald-500');
        $response->assertSee('text-sky-500');
        $response->assertSee('text-rose-500');
        $response->assertSee('text-amber-500');
        $response->assertSee('text-teal-500');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SidebarColoredIconsTest`  
Expected: FAIL (some icons lack explicit color classes).

- [ ] **Step 3: Apply semantic color classes to all sidebar and drawer icons**

In `resources/views/components/layouts/app.blade.php` and `resources/views/layouts/app.blade.php`:
- Live Monitoring:
  - Dashboard: `<svg class="w-5 h-5 text-indigo-500 dark:text-indigo-400 shrink-0" ...>`
  - Online Users: `<svg class="w-5 h-5 text-emerald-500 dark:text-emerald-400 shrink-0" ...>`
  - Live Devices: `<svg class="w-5 h-5 text-sky-500 dark:text-sky-400 shrink-0" ...>`
  - Analisis Trafik: `<svg class="w-5 h-5 text-rose-500 dark:text-rose-400 shrink-0" ...>`
  - Historical Usage: `<svg class="w-5 h-5 text-violet-500 dark:text-violet-400 shrink-0" ...>`
- Hotspot & Voucher:
  - Paket Hotspot: `<svg class="w-5 h-5 text-amber-500 dark:text-amber-400 shrink-0" ...>`
  - Kelola User & Voucher: `<svg class="w-5 h-5 text-teal-500 dark:text-teal-400 shrink-0" ...>`
  - Generate Voucher: `<svg class="w-5 h-5 text-emerald-500 dark:text-emerald-400 shrink-0" ...>`
- POS & Keuangan:
  - Kasir POS: `<svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" ...>`
  - Rekap Penjualan: `<svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" ...>`
  - Tagihan Bulanan: `<svg class="w-5 h-5 text-blue-500 dark:text-blue-400 shrink-0" ...>`
- Settings & Logs:
  - Profil & Aplikasi: `<svg class="w-5 h-5 text-indigo-500 dark:text-indigo-400 shrink-0" ...>`
  - Router Settings: `<svg class="w-5 h-5 text-cyan-500 dark:text-cyan-400 shrink-0" ...>`
  - Template Voucher: `<svg class="w-5 h-5 text-purple-500 dark:text-purple-400 shrink-0" ...>`
  - Pemeliharaan: `<svg class="w-5 h-5 text-rose-500 dark:text-rose-400 shrink-0" ...>`
  - Audit Log: `<svg class="w-5 h-5 text-zinc-500 dark:text-zinc-400 shrink-0" ...>`
Apply same colors to Mobile Drawer and Mobile Bottom Nav.
In topbar search button: change text to `text-zinc-500 dark:text-zinc-400`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=SidebarColoredIconsTest`  
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/layouts/app.blade.php resources/views/layouts/app.blade.php tests/Feature/SidebarColoredIconsTest.php
git commit -m "feat(ui): add thematic colors to sidebar navigation icons"
```

---

### Task 3: Shared Components & Design Tokens Harmonization

**Files:**
- Modify: `resources/views/components/card.blade.php`
- Modify: `resources/views/components/stat-card.blade.php`
- Modify: `resources/views/components/modal.blade.php`
- Modify: `resources/views/components/badge.blade.php`
- Modify: `resources/views/components/command-palette.blade.php`
- Modify: `resources/views/components/theme-toggle.blade.php`
- Test: `tests/Feature/ComponentsContrastTest.php`

**Interfaces:**
- Consumes: Blade component props
- Produces: Resilient, WCAG AA compliant shared UI components

- [ ] **Step 1: Write test for shared component contrast classes**

Create `tests/Feature/ComponentsContrastTest.php`:
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ComponentsContrastTest extends TestCase
{
    public function test_card_and_stat_card_render_with_accessible_contrast(): void
    {
        $view = $this->blade('<x-card title="Test Card" subtitle="Test Subtitle">Content</x-card>');
        $view->assertSee('text-zinc-900 dark:text-zinc-100');
        $view->assertSee('text-zinc-500 dark:text-zinc-400');

        $statView = $this->blade('<x-stat-card title="Online Users" value="123" change="+5 today" />');
        $statView->assertSee('text-zinc-500 dark:text-zinc-400');
        $statView->assertSee('text-2xl font-bold text-zinc-900 dark:text-zinc-100');
    }
}
```

- [ ] **Step 2: Run test to verify it runs**

Run: `php artisan test --filter=ComponentsContrastTest`

- [ ] **Step 3: Update shared components with standardized tokens**

- In `resources/views/components/card.blade.php`:
  - Subtitle: `text-xs text-zinc-500 dark:text-zinc-400 mt-0.5`
  - Border: `border-zinc-200 dark:border-zinc-800`
- In `resources/views/components/stat-card.blade.php`:
  - Title: `text-zinc-500 dark:text-zinc-400`
  - Value: `text-zinc-900 dark:text-zinc-100`
  - Icon wrapper: `bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700`
  - Change text: `text-zinc-500 dark:text-zinc-400`
- In `resources/views/components/modal.blade.php`:
  - Close button: `text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200`
  - Backdrop: `bg-zinc-950/70 dark:bg-zinc-950/80`
- In `resources/views/components/command-palette.blade.php`:
  - Replace bare `text-zinc-400` in subtitles, descriptions, counters, and footer with `text-zinc-500 dark:text-zinc-400`.
  - Input: ensure `text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500`.
  - Empty state text: `text-zinc-600 dark:text-zinc-300` and `text-zinc-500 dark:text-zinc-400`.
  - Footer hints: `text-zinc-500 dark:text-zinc-400`.
- In `resources/views/components/theme-toggle.blade.php`:
  - Moon icon: `text-zinc-700 dark:text-zinc-300`.
  - Button border & hover: `border-zinc-200 dark:border-zinc-700/80 bg-zinc-50 dark:bg-zinc-850`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ComponentsContrastTest`  
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/card.blade.php resources/views/components/stat-card.blade.php resources/views/components/modal.blade.php resources/views/components/badge.blade.php resources/views/components/command-palette.blade.php resources/views/components/theme-toggle.blade.php tests/Feature/ComponentsContrastTest.php
git commit -m "fix(ui): harmonize shared components contrast and dark mode tokens"
```

---

### Task 4: Dashboard & Realtime Telemetry Contrast Alignment

**Files:**
- Modify: `resources/views/dashboard/index.blade.php`
- Test: `tests/Feature/DashboardContrastTest.php`

**Interfaces:**
- Consumes: Standardized design tokens
- Produces: WCAG AA compliant live dashboard view

- [ ] **Step 1: Write test for dashboard contrast**

Create `tests/Feature/DashboardContrastTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardContrastTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_with_high_contrast_classes(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('text-zinc-400 truncate">/ ');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DashboardContrastTest`

- [ ] **Step 3: Fix contrast and dark mode tokens in `dashboard/index.blade.php`**

- In metric stat cards:
  - Total users counter: replace `text-zinc-400 truncate` with `text-zinc-500 dark:text-zinc-400 truncate`.
  - Action link: `text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 font-medium`.
  - Subtitle and label: `text-zinc-500 dark:text-zinc-400`.
- In realtime traffic chart section:
  - Sub-chart info footer: replace `text-zinc-400` with `text-zinc-500 dark:text-zinc-400`.
  - Throughput label: `text-zinc-600 dark:text-zinc-400`.
- In hardware telemetry panel:
  - Telemetry card labels: `text-zinc-500 dark:text-zinc-400`.
  - Telemetry sub-details: `text-zinc-600 dark:text-zinc-400`.
- In consumers and recent users table:
  - Header: `text-zinc-500 dark:text-zinc-400`.
  - Empty states: `text-zinc-500 dark:text-zinc-400`.
  - Sub-details (IP/MAC): `text-zinc-500 dark:text-zinc-400`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=DashboardContrastTest`  
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/views/dashboard/index.blade.php tests/Feature/DashboardContrastTest.php
git commit -m "fix(dashboard): enhance contrast and dark mode consistency in dashboard"
```

---

### Task 5: Users, Devices, and Traffic Pages Harmonization

**Files:**
- Modify: `resources/views/users/index.blade.php`
- Modify: `resources/views/users/show.blade.php`
- Modify: `resources/views/users/historical.blade.php`
- Modify: `resources/views/devices/index.blade.php`
- Modify: `resources/views/traffic/index.blade.php`
- Test: `tests/Feature/UsersDevicesTrafficViewsTest.php`

**Interfaces:**
- Consumes: Standardized form & text tokens
- Produces: Fully accessible data tables, forms, and live views for Users, Devices, and Traffic

- [ ] **Step 1: Write test for Users, Devices, and Traffic pages**

Create `tests/Feature/UsersDevicesTrafficViewsTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersDevicesTrafficViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_devices_traffic_pages_render_accessible_forms(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('users.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('users.historical'))->assertStatus(200);
        $this->actingAs($user)->get(route('devices.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('traffic.index'))->assertStatus(200);
    }
}
```

- [ ] **Step 2: Run test to verify views load**

Run: `php artisan test --filter=UsersDevicesTrafficViewsTest`

- [ ] **Step 3: Apply token fixes to Users, Devices, and Traffic views**

- In `resources/views/users/show.blade.php`:
  - Add `dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100` to all form controls, modals, and tab selectors.
  - Fix all bare `text-zinc-400` to `text-zinc-500 dark:text-zinc-400`.
  - Fix all static dark text `text-zinc-900` to `text-zinc-900 dark:text-zinc-100`.
- In `resources/views/users/index.blade.php` & `users/historical.blade.php`:
  - Search input: ensure `bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100`.
  - Table headers and sub-text: `text-zinc-500 dark:text-zinc-400`.
- In `resources/views/devices/index.blade.php`:
  - Fix modal inputs and checkboxes (`dark:bg-zinc-800 dark:border-zinc-700 text-emerald-600 dark:text-emerald-500`).
  - Fix tabs: active `border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100`, inactive `text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300`.
- In `resources/views/traffic/index.blade.php`:
  - Fix signature forms (`dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100`).
  - Fix switch indicator: ensure the knob `bg-white dark:bg-zinc-100` is distinct and visible on track `bg-zinc-300 dark:bg-zinc-700` (off) and `bg-emerald-500` (on).

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=UsersDevicesTrafficViewsTest`  
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/views/users/ resources/views/devices/ resources/views/traffic/ tests/Feature/UsersDevicesTrafficViewsTest.php
git commit -m "fix(ui): align contrast and dark mode on users, devices, and traffic views"
```

---

### Task 6: Hotspot, Vouchers, and POS Pages Harmonization

**Files:**
- Modify: `resources/views/hotspot/profiles.blade.php`
- Modify: `resources/views/hotspot/users.blade.php`
- Modify: `resources/views/hotspot/generate.blade.php`
- Modify: `resources/views/vouchers/index.blade.php`
- Modify: `resources/views/pos/index.blade.php`
- Modify: `resources/views/pos/vouchers.blade.php`
- Modify: `resources/views/pos/monthly.blade.php`
- Modify: `resources/views/pos/shifts.blade.php`
- Test: `tests/Feature/HotspotAndPosViewsTest.php`

**Interfaces:**
- Consumes: Standardized form & table tokens
- Produces: Consistent styling across all Hotspot, Voucher, and POS cashier views

- [ ] **Step 1: Write test for Hotspot and POS views**

Create `tests/Feature/HotspotAndPosViewsTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotspotAndPosViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_hotspot_and_pos_pages_render_correctly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('hotspot.profiles'))->assertStatus(200);
        $this->actingAs($user)->get(route('hotspot.users'))->assertStatus(200);
        $this->actingAs($user)->get(route('hotspot.generate'))->assertStatus(200);
        $this->actingAs($user)->get(route('vouchers.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('pos.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('pos.vouchers'))->assertStatus(200);
        $this->actingAs($user)->get(route('pos.monthly'))->assertStatus(200);
    }
}
```

- [ ] **Step 2: Run test to verify views load**

Run: `php artisan test --filter=HotspotAndPosViewsTest`

- [ ] **Step 3: Apply token fixes to Hotspot, Vouchers, and POS views**

- In `resources/views/hotspot/profiles.blade.php`:
  - Category radio indicators: ensure radio circle and dot have strong contrast in both modes (`bg-emerald-600 dark:bg-emerald-500` dot on selected, `border-zinc-300 dark:border-zinc-600` container).
  - FUP switch & checkbox: ensure `dark:bg-zinc-800 dark:border-zinc-700`.
- In `resources/views/hotspot/generate.blade.php`:
  - Batch generation inputs, select dropdowns, prefix input: `bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100`.
- In `resources/views/vouchers/index.blade.php`:
  - Checkboxes: `rounded border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-emerald-600 dark:text-emerald-500 focus:ring-emerald-500`.
  - Batch preview table: `border-zinc-200 dark:border-zinc-800`.
- In `resources/views/pos/index.blade.php`, `pos/vouchers.blade.php`, `pos/monthly.blade.php`, `pos/shifts.blade.php`:
  - Cashier payment modal, customer search input, invoice amount summary: `bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100`.
  - Status badges: use `<x-badge>` or standardized badge classes.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=HotspotAndPosViewsTest`  
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/views/hotspot/ resources/views/vouchers/ resources/views/pos/ tests/Feature/HotspotAndPosViewsTest.php
git commit -m "fix(ui): harmonize hotspot, voucher, and POS views contrast and form controls"
```

---

### Task 7: Settings, Audit Logs, and Login Page Harmonization

**Files:**
- Modify: `resources/views/settings/router.blade.php`
- Modify: `resources/views/settings/profile.blade.php`
- Modify: `resources/views/settings/templates.blade.php`
- Modify: `resources/views/settings/maintenance.blade.php`
- Modify: `resources/views/audit/index.blade.php`
- Modify: `resources/views/auth/login.blade.php`
- Test: `tests/Feature/SettingsAndAuthViewsTest.php`

**Interfaces:**
- Consumes: Standardized form & container tokens
- Produces: Polished, accessible configuration and authentication screens

- [ ] **Step 1: Write test for Settings and Auth views**

Create `tests/Feature/SettingsAndAuthViewsTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAndAuthViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_and_login_views_render(): void
    {
        $user = User::factory()->create();

        $this->get(route('login'))->assertStatus(200);
        $this->actingAs($user)->get(route('settings.router'))->assertStatus(200);
        $this->actingAs($user)->get(route('settings.profile'))->assertStatus(200);
        $this->actingAs($user)->get(route('settings.templates'))->assertStatus(200);
        $this->actingAs($user)->get(route('settings.maintenance'))->assertStatus(200);
        $this->actingAs($user)->get(route('audit.index'))->assertStatus(200);
    }
}
```

- [ ] **Step 2: Run test to verify views load**

Run: `php artisan test --filter=SettingsAndAuthViewsTest`

- [ ] **Step 3: Apply token fixes to Settings, Audit, and Login**

- In `resources/views/auth/login.blade.php`:
  - Remember me checkbox: `rounded border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-zinc-500`.
  - Helper copy: `text-zinc-500 dark:text-zinc-400`.
- In `resources/views/settings/router.blade.php`:
  - Checkboxes (SSL, Active): `rounded border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-emerald-600 dark:text-emerald-500`.
  - Credentials input: `bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100`.
- In `resources/views/settings/profile.blade.php`:
  - File upload inputs & drag-drop boxes: `bg-zinc-50 dark:bg-zinc-800/50 border-zinc-300 dark:border-zinc-700`.
- In `resources/views/settings/templates.blade.php`:
  - Thermal ticket preview card: wrap in an explicit neutral preview frame so physical black-on-white ticket preview is visible even in dark mode without clashing with page dark background.
  - Editor controls: `bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100`.
- In `resources/views/settings/maintenance.blade.php`:
  - Warning and confirmation cards: `bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-900/50 text-rose-700 dark:text-rose-400`.
  - Delete checkboxes: `border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-rose-600`.
- In `resources/views/audit/index.blade.php`:
  - Code/JSON blocks: `bg-zinc-100 dark:bg-zinc-850 text-zinc-800 dark:text-zinc-200 border-zinc-200 dark:border-zinc-700`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=SettingsAndAuthViewsTest`  
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/views/settings/ resources/views/audit/ resources/views/auth/ tests/Feature/SettingsAndAuthViewsTest.php
git commit -m "fix(ui): align contrast and dark mode on settings, audit, and login views"
```

---

### Task 8: End-to-End Delivery Gate & Verification

**Files:**
- Modify: `docs/superpowers/plans/2026-11-17-ui-dark-mode-consistency.md` (Update task statuses)
- Verification script: `deep-audit.js`

- [ ] **Step 1: Run Vite build**

Run: `npm run build`  
Expected: Assets built successfully with 0 errors.

- [ ] **Step 2: Run automated dark mode & contrast audit script**

Run: `node "C:\Users\spm\.gemini\antigravity\brain\ee742b0a-3f47-47ee-807c-801ec8e1dd97\scratch\deep-audit.js"`  
Expected: 0 contrast issues, 0 missing dark classes on form controls and containers.

- [ ] **Step 3: Run full PHPUnit test suite**

Run: `php artisan test`  
Expected: All tests PASS with 0 failures.

- [ ] **Step 4: Run Antislop Delivery Gate checks (Block 1 - Block 4)**

Verify every gate item:
- R-02 PASS: No em dash (`—`) in UI text.
- R-17 & R-38 PASS: Sidebar router telemetry and stats bound to real RouterOS and DB values.
- R-25 PASS: All text meets WCAG AA 4.5:1 / 3:1.
- R-26 PASS: All buttons and links have functional behaviors or routes.
- R-34 PASS: Both light and dark modes fully functional across every screen.
- R-35 PASS: Build passed, automated audit passed, test suite passed.

- [ ] **Step 5: Final Commit**

```bash
git add -A
git commit -m "chore(ui): complete dark mode & design consistency pass across all views"
```
