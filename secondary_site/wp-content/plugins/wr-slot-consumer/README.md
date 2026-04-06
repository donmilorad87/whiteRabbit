# WR Slot Consumer

WordPress plugin that receives slot data from a primary WR Slot Manager site and displays it via Gutenberg blocks. Supports manual sync, webhook push, and auto-sync when cache expires.

## Features

- Receives slot data via REST API sync and webhook push
- Transient-based cache with configurable expiry (admin Settings page)
- Auto-sync: Slot Grid block triggers sync when transient expires
- Count mismatch detection: webhook includes `total_count`, triggers full resync if local count differs
- Two Gutenberg blocks: Slot Grid (list with pagination/load-more/infinite-scroll) and Slot Detail (dropdown selector + styling)
- Slot Grid: popup mode with native `<dialog>`, card styling with 90+ CSS custom properties
- Slot Detail: dropdown slot selector from REST API `/wr-slot-consumer/v1/slot-list`
- 3-layer authentication (Bearer + HMAC + time-based nonce)
- Rate limiter on webhook endpoint
- AJAX admin with 4 JS-based tabs, dialog loading mask, and Toastify messages
- Admin sidebar submenus for each settings tab
- `log_error()` always fires; `log_info()` only in dev
- i18n ready with Serbian Cyrillic translation (4 JSON files for block translations)
- Test page created on activation at `/test-page`

## File Structure

```
wr-slot-consumer/
├── wr-slot-consumer.php              Thin bootstrap (header + constants)
├── plugin.php                        Autoloader + hooks + i18n
├── activate.php                      Creates test page for e2e
├── deactivate.php                    Placeholder
├── uninstall.php                     Cleanup on plugin deletion
├── composer.json                     PHPUnit deps
├── phpunit.xml
├── .gitignore
├── includes/                         PHP classes (VIP structure)
│   ├── class-plugin.php              Singleton orchestrator
│   ├── admin/
│   │   ├── class-admin-assets.php    Script/style enqueuing
│   │   └── class-settings-page.php   Unified with 4 tabs + sidebar submenus
│   ├── api/
│   │   ├── class-authentication.php  Permission callback (3-layer auth)
│   │   ├── class-auth-signer.php     HMAC + nonce generation/validation
│   │   ├── class-rate-limiter.php    Rate limiting for webhook endpoint
│   │   └── class-webhook-endpoint.php POST /v1/webhook receiver
│   ├── block/
│   │   ├── slot-grid-block/          Grid block (90+ attributes, SSR)
│   │   │   ├── block.json
│   │   │   └── class-slot-grid-block.php
│   │   └── slot-detail-block/        Detail block (dropdown selector + styling)
│   │       ├── block.json
│   │       └── class-slot-detail-block.php
│   ├── cache/
│   │   └── class-slot-transient-cache.php  Transient CRUD + is_expired()
│   ├── sync/
│   │   ├── class-slot-sync-manager.php     Full sync via REST API (AJAX + block)
│   │   └── class-webhook-processor.php     Single-slot updates + count mismatch resync
│   └── traits/
│       ├── trait-logger.php                Info=dev only, Error=always
│       ├── trait-option-prefix.php
│       └── trait-template-loader.php       VIP-compliant template loading
├── assets/
│   ├── src/                          Vite + TypeScript source
│   │   ├── vite.config.js            IIFE output, Terser, ES2022
│   │   ├── tsconfig.json             strict: true
│   │   ├── package.json
│   │   ├── types/wordpress.d.ts      WP globals + SlotData interface
│   │   ├── js/
│   │   │   ├── admin/adminApp.ts     Tab switching + form init
│   │   │   ├── admin/classes/        FetchHandler, SettingsForm, SyncButton
│   │   │   └── blocks/
│   │   │       ├── slot-grid/        index.ts, frontend.ts, SlotCardBuilder.ts, SlotLoadMore.ts, SlotPopup.ts
│   │   │       └── slot-detail/      index.ts (dropdown selector + styling panels)
│   │   ├── scss/
│   │   │   ├── admin/admin.scss
│   │   │   └── blocks/               frontend.scss, slot-grid/, slot-detail/
│   │   └── __tests__/                FetchHandler.test.ts, SlotCardBuilder.test.ts
│   ├── admin/                        Built admin assets
│   │   ├── js/admin.js
│   │   ├── css/admin.css
│   │   └── vendor/toastify.*
│   └── blocks/                       Built block assets
│       ├── js/slot-grid.js
│       ├── js/slot-grid-frontend.js
│       ├── js/slot-detail.js
│       └── css/frontend.css
├── templates/
│   ├── admin/settings.php            Single page, 4 JS tabs
│   └── blocks/
│       ├── slot-grid.php
│       └── slot-detail.php
├── languages/                        .pot/.po/.mo + 4 JSON files for blocks
│   ├── wr-slot-consumer.pot
│   ├── wr-slot-consumer-sr_RS.po
│   ├── wr-slot-consumer-sr_RS.mo
│   └── wr-slot-consumer-sr_RS-*.json (4 JSON files for block translations)
├── tests/php/                        PHPUnit
│   ├── bootstrap.php
│   └── test-auth-signer.php
└── e2e-tests/                        Playwright
    ├── playwright.config.ts
    ├── package.json
    ├── tests/
    │   ├── pagination.spec.ts        6 tests
    │   └── popup.spec.ts             4 tests
    └── README.md
```

## Build

```bash
cd assets/src
npm install
npm run build
```

## Testing

### PHPUnit (unit tests)

Tests are in `tests/php/`. They run standalone without WordPress -- WP functions are stubbed in the bootstrap.

```bash
# Install dependencies (first time)
composer install

# Run all PHP tests
vendor/bin/phpunit

# Run a specific test file
vendor/bin/phpunit tests/php/test-auth-signer.php
```

**What's tested:** AuthSigner (HMAC generation, nonce, validate_request -- all error branches, cross-plugin parity).

### Vitest (TypeScript unit tests)

Tests are in `assets/src/__tests__/`. They run in jsdom via Vitest.

```bash
cd assets/src

# Run all JS/TS tests
npm test

# Watch mode
npm run test:watch
```

**What's tested:** SlotCardBuilder (HTML rendering, XSS escaping, star ratings, link modes, empty fields), FetchHandler (dialog, Toastify, callbacks, error states).

### Playwright (e2e tests)

End-to-end tests are in the plugin's `e2e-tests/` directory. They test the consumer site's frontend in a real browser.

```bash
cd e2e-tests

# Install (first time)
npm install
npx playwright install chromium

# Run all e2e tests
npm test
```

**What's tested:** Pagination (renders cards, navigation, page numbers, active state -- 6 tests), Popup (dialog open/close, slot data displayed, provider/rtp -- 4 tests).

**Configuring the test URL:** Edit `playwright.config.ts` and change the `baseURL`:

```ts
use: {
    baseURL: 'http://sec.wiserabbit.com:81',  // change this to your site URL
}
```

**Prerequisite:** Your machine must resolve the hostname. Add to `/etc/hosts`:

```
127.0.0.1 sec.wiserabbit.com local.wiserabbit.com
```

This is required because WordPress enqueues JS/CSS assets using the configured site URL. If the browser cannot resolve it, server-rendered tests (pagination) pass but JS-dependent tests (popup) fail.

**Test page:** The plugin creates a `/test-page` on activation (via `activate.php`) with a slot grid block (pagination mode, 3 per page, popup links). If missing, recreate via WP-CLI:

```bash
docker exec secondary_site-wordpress-1 wp post create --post_type=page --post_title="Test Page" --post_name=test-page --post_status=publish --post_content='<!-- wp:wr-slot-consumer/slot-grid {"paginationMode":"pagination","limit":3,"linkMode":"popup"} /-->' --allow-root
```

## Admin Page

Single settings page with 4 JavaScript-based tabs and sidebar submenus:

| Tab           | Fields / Actions                                      |
|---------------|-------------------------------------------------------|
| Connection    | Source Site URL, API Key                               |
| API Settings  | Endpoint configuration                                 |
| Cache         | Cache expiry (minutes)                                 |
| Manual Sync   | Sync Data button (pulls all slots from primary site)   |

All forms use AJAX via FetchHandler with dialog loading mask and Toastify notifications.
