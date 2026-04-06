# WR Slot Manager

WordPress plugin for managing slot game data. Provides a custom post type with Gutenberg inline editing, Redis-backed cache, REST API with rate limiting, and webhook push to connected consumer sites.

## Features

- Custom post type `slot` with meta fields (description, star rating, image, provider, RTP, wager range)
- Gutenberg block editor with inline fields, image picker, Ctrl+S save shortcut
- Redis object cache with configurable expiry (admin Settings page)
- REST API endpoint `GET /wr-slot-manager/v1/slots` with transient-based per-IP rate limiting
- Webhook dispatch to connected consumer sites on create/update/delete
- Webhook queue with retry logic (max 3 attempts) and bounded processing (MAX_JOBS_PER_RUN = 50)
- 3-layer authentication (Bearer + HMAC + time-based nonce)
- Unified Settings page with 3 JS-based tabs: Connected Sites, API Settings, Cache Configuration
- API Key generation and management
- i18n ready with Serbian Cyrillic translation
- Frontend disabled mode (API-only landing page)

## File Structure

```
wr-slot-manager/
├── wr-slot-manager.php              Thin bootstrap (header + constants)
├── plugin.php                       Autoloader + hooks + i18n
├── activate.php                     Activation: register CPT + flush rules
├── deactivate.php                   Deactivation: flush rules
├── uninstall.php                    Cleanup on plugin deletion
├── composer.json                    PHPUnit deps
├── phpunit.xml                      Test config
├── .gitignore
├── includes/                        PHP classes (VIP: class-{name}.php)
│   ├── class-plugin.php             Singleton orchestrator
│   ├── admin/
│   │   ├── class-admin-assets.php   Script/style enqueuing
│   │   └── class-settings-page.php  Unified settings (Connected Sites + API + Cache tabs)
│   ├── api/
│   │   ├── class-authentication.php 3-layer auth permission callback
│   │   ├── class-auth-signer.php    HMAC + nonce generation/validation
│   │   ├── class-rate-limiter.php   Transient-based per-IP rate limiting
│   │   └── class-slots-endpoint.php GET /v1/slots
│   ├── block/slot-fields/
│   │   ├── block.json
│   │   └── class-slot-fields-block.php
│   ├── cache/
│   │   └── class-slot-cache.php     Redis object cache
│   ├── hooks/
│   │   ├── class-slot-save-hook.php
│   │   └── class-slot-delete-hook.php
│   ├── post-type/
│   │   ├── class-slot-post-type.php
│   │   └── class-slot-meta-fields.php
│   ├── traits/
│   │   ├── trait-logger.php         Info=dev only, Error=always
│   │   ├── trait-nonce-verification.php
│   │   ├── trait-option-prefix.php
│   │   └── trait-template-loader.php
│   └── webhook/
│       ├── class-webhook-dispatcher.php
│       ├── class-webhook-payload.php
│       ├── class-webhook-queue.php
│       └── class-webhook-sender.php Bounded loop (MAX_JOBS_PER_RUN=50)
├── assets/
│   ├── src/                         Vite + TypeScript source
│   │   ├── vite.config.js           IIFE output, Terser, ES2022
│   │   ├── tsconfig.json            strict: true
│   │   ├── package.json
│   │   ├── types/wordpress.d.ts     WP global declarations
│   │   ├── js/
│   │   │   ├── admin/adminApp.ts    Tab switching + form init
│   │   │   ├── admin/classes/       FetchHandler, ApiKeyForm, ConnectedSitesForm, SettingsForm
│   │   │   └── editor/slotFields.ts Gutenberg block
│   │   ├── scss/admin/admin.scss
│   │   ├── scss/editor/slotFields.scss
│   │   └── __tests__/FetchHandler.test.ts
│   ├── admin/                       Built admin assets
│   │   ├── js/admin.js
│   │   ├── css/admin.css
│   │   └── vendor/toastify.*
│   └── editor/                      Built editor assets
│       ├── js/slotFields.js
│       └── css/slotFields.css
├── templates/admin/settings.php     Single page, JS tabs (Connected Sites/API/Cache)
├── languages/                       .pot/.po/.mo/.json
│   ├── wr-slot-manager.pot
│   ├── wr-slot-manager-sr_RS.po
│   ├── wr-slot-manager-sr_RS.mo
│   └── wr-slot-manager-sr_RS-*.json JS translations
└── tests/php/                       PHPUnit (standalone, no WP needed)
    ├── bootstrap.php
    ├── test-auth-signer.php         3-layer auth tests (all error branches)
    ├── test-webhook-payload.php
    └── test-slot-post-type.php
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

**What's tested:** AuthSigner (HMAC generation, nonce, validate_request -- all 10 error branches), WebhookPayload (build, types, defaults), SlotPostType (force_private_status logic).

### Vitest (TypeScript unit tests)

Tests are in `assets/src/__tests__/`. They run in jsdom via Vitest.

```bash
cd assets/src

# Run all TS tests
npm test

# Watch mode (re-runs on file change)
npm run test:watch
```

**What's tested:** FetchHandler (dialog open/close, Toastify calls, callbacks, error handling, reload behavior).

## Admin Pages

- **Slot Manager** (sidebar) > All Slots, Add New Slot
- **Slot Manager** > Settings -- single page with 3 JS-based tabs (no page reload):
  - **Connected Sites** -- manage consumer site URLs (add, edit, remove via AJAX)
  - **API Settings** -- generate/regenerate API key + rate limit configuration
  - **Cache Configuration** -- Redis cache expiry (minutes)
