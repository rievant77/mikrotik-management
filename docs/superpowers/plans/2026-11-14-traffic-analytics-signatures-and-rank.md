# Traffic Analytics Signatures & Application User Rank Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Provide dynamic, customizable Traffic Signatures (ports, domains, TLS hosts) for Traffic & Content Analytics, and introduce an interactive detail pop-up on application cards displaying the real-time ranking of users who access each application the most.

**Architecture:** 
1. Database migration and `TrafficSignature` model to store traffic classification rules dynamically (with default pre-seeded signatures for popular games and platforms).
2. Refactor `RouterOsService::deployTrafficMangleRules()` to deploy rules directly from the database signatures.
3. Add `TrafficAnalyticsService::getPlatformUserRank()` that inspects RouterOS connection tracking (`/ip/firewall/connection`), maps IP addresses to Hotspot users/leases, and calculates user usage ranks.
4. Add API routes and controller endpoints for signature CRUD, default resetting, and platform user ranking.
5. Upgrade `resources/views/traffic/index.blade.php` with:
   - Clickable application rows/cards triggering a clean User Ranking Modal.
   - A "Kelola Signature & Port" modal to customize ports, domains, and toggle active status.

**Tech Stack:** Laravel 11, PHP 8.2, MikroTik RouterOS API (`RouterosAPI`), Alpine.js, Tailwind CSS, Pest/PHPUnit.

---

## Global Constraints

- Anti-slop UI: Accessible, semantic Tailwind colors, clean dark/light mode contrast, touch-friendly tap targets, no excessive emojis.
- All file references must use clickable links format `[file.php](file:///d:/PROJECTS/WEB/mikrotik-management/path/to/file.php)`.
- RouterOS safety: Ensure all mangle rules use non-intrusive `action=passthrough` and `connection-mark` without interrupting user traffic or routing.
- Graceful fallback: When RouterOS is disconnected or in test environment, APIs must return safe fallback structures without throwing unhandled exceptions.

## Review Focus

1. Signature rule syntax: Port ranges like `5000-5200,30000-30200` must be sanitized before passing to RouterOS.
2. Connection tracking volume: In high-traffic routers, querying connection tracking must be efficient (using connection-mark filter or cached DNS mapping).
3. IP to User mapping: Ensure IP-to-user resolution correctly handles Hotspot Active, DHCP Leases, and IP Bindings (bypassed/CCTV/PC devices).
4. Signature reset safety: Resetting to defaults must restore system signatures without breaking existing mangle rule tags.
5. Modal responsiveness: User rank modal and Signature manager modal must display smoothly on both mobile screens and desktops.

---

### Task 1: TrafficSignature Model, Migration, and Default Seeding

**Files:**
- Create: `database/migrations/2026_11_14_000001_create_traffic_signatures_table.php`
- Create: `app/Models/TrafficSignature.php`
- Test: `tests/Feature/TrafficSignatureManagementTest.php`

- [ ] Write failing test for `TrafficSignature` model attributes, active scope, and default seeding in `tests/Feature/TrafficSignatureManagementTest.php`.
- [ ] Create migration `database/migrations/2026_11_14_000001_create_traffic_signatures_table.php` with columns: `name`, `category`, `mark_name`, `address_list_name`, `fqdn_domains` (json), `tls_host`, `tcp_ports`, `udp_ports`, `is_quic`, `is_active`, `is_system`.
- [ ] Create `app/Models/TrafficSignature.php` with casting and helper `defaultSignatures()` method.
- [ ] Run migration and verify test passes.

### Task 2: Refactor RouterOsService and TrafficAnalyticsService to Use Dynamic Signatures

**Files:**
- Modify: `app/Services/RouterOsService.php`
- Modify: `app/Services/TrafficAnalyticsService.php`
- Test: `tests/Feature/TrafficSignatureManagementTest.php`

- [ ] Update `RouterOsService::deployTrafficMangleRules()` to load active signatures from `TrafficSignature::where('is_active', true)->get()`.
- [ ] Add `RouterOsService::getPlatformConnections(string $markName)` or query connections by `connection-mark`.
- [ ] Add `TrafficAnalyticsService::getPlatformUserRank(string $platformNameOrMark)`:
  - Resolves active connections for that platform.
  - Maps source IP addresses to Hotspot users, DHCP hostnames, and user categories.
  - Computes ranked leaderboard with totals, percentages, and user category labels.
- [ ] Add unit/feature tests for `getPlatformUserRank` and `deployTrafficMangleRules`.

### Task 3: API Endpoints & Controller Methods

**Files:**
- Modify: `app/Http/Controllers/TrafficAnalyticsController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/TrafficSignatureManagementTest.php`

- [ ] Add routes in `routes/web.php`:
  - `GET /api/traffic-analytics/signatures`
  - `POST /api/traffic-analytics/signatures`
  - `PUT /api/traffic-analytics/signatures/{id}`
  - `DELETE /api/traffic-analytics/signatures/{id}`
  - `POST /api/traffic-analytics/signatures/reset-defaults`
  - `GET /api/traffic-analytics/platforms/{platformKey}/users`
- [ ] Implement controller methods in `TrafficAnalyticsController.php` with validation and audit logging.
- [ ] Write tests covering all endpoints and verify 200 OK responses with proper JSON schemas.

### Task 4: Frontend UI Enhancement in `resources/views/traffic/index.blade.php`

**Files:**
- Modify: `resources/views/traffic/index.blade.php`
- Test: Browser / Blade rendering test in `tests/Feature/TrafficAnalyticsTest.php`

- [ ] Add "Kelola Port & Signature" button to action bar with modal:
  - Table of active platform signatures.
  - Editable TCP/UDP ports, domains, active toggle.
  - Add new custom game/platform signature form.
  - "Reset ke Default" button.
  - "Deploy ke MikroTik" trigger.
- [ ] Enhance Platform Table / Cards:
  - Add click handler `@click="openPlatformRank(item)"` on table rows.
  - Add visual cue (chevron / "Lihat Rank Pengguna" pill).
- [ ] Add "Detail Pengguna & Rank Aplikasi" Modal:
  - Header: Application title, category badge, total data.
  - Body: Leaderboard table with Rank (#1, #2, #3 badges), User & Hostname, User Category chip, Total Bytes, Percentage progress bar, and Active status.
  - Loading skeleton state when fetching rank data from API.
- [ ] Verify responsive behavior and theme colors (light/dark).

### Task 5: End-to-End Verification and Assets Build

**Files:**
- Run: `php artisan test`
- Run: `npm run build`

- [ ] Run complete test suite (`php artisan test`) and verify 100% green tests.
- [ ] Run `npm run build` to ensure no frontend bundle errors.
- [ ] Verify everything matches user requirements cleanly without regressions.
