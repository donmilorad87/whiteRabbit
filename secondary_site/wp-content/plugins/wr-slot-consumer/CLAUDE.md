# Claude Code Instructions -- WR Slot Consumer Plugin

## Folder Structure (VIP-aligned)

- `includes/` -- PHP classes (`class-{name}.php`) and traits (`trait-{name}.php`)
- `assets/src/` -- Vite + TypeScript source, `__tests__/` for Vitest
- `assets/admin/` -- built admin JS/CSS + vendor (Toastify)
- `assets/blocks/` -- built block JS/CSS
- `templates/` -- PHP templates (admin + blocks)
- `tests/php/` -- PHPUnit tests
- `e2e-tests/` -- Playwright e2e tests
- `languages/` -- .pot/.po/.mo + 4 JSON files for block translations
- `plugin.php` -- autoloader + hooks + i18n (loaded by `wr-slot-consumer.php`)
- `wr-slot-consumer.php` -- thin bootstrap (header + constants only)
- `activate.php` -- creates test page for e2e on activation
- `deactivate.php` -- placeholder
- `uninstall.php` -- cleanup on plugin deletion

## Code Conventions

- PHP: OOP with namespaces (`WiseRabbit\SlotConsumer\`), VIP autoloader (`includes/`)
- WordPress VIP coding standards
- File naming: `class-{name}.php` for classes, `trait-{name}.php` for traits
- Directory naming: lowercase-hyphenated (e.g. `slot-grid-block/`, `slot-detail-block/`)
- Traits for cross-cutting concerns (LoggerTrait, OptionPrefixTrait, TemplateLoaderTrait)
- Templates loaded via `$this->load_template()` / `$this->render_template()` (not raw `include`)
- All `$_GET`/`$_POST` access uses `wp_unslash()` + `sanitize_text_field()`
- All output escaped with `esc_html()`, `esc_attr()`, `esc_url()`
- Logging: `log_error()` always fires, `log_info()` gated by `ENVIRONMENT === 'dev'`
- SSL verify controlled by `ENVIRONMENT` (off in dev, on in prod)

## TypeScript / JavaScript

- Vite build with IIFE output format, Terser minification (`reserved: ['_', '$']`), `target: ES2022`
- `tsconfig.json` with `strict: true`
- Admin: ES6 classes (FetchHandler, SettingsForm, SyncButton) in `assets/src/js/admin/`
- Blocks: WordPress block API + frontend JS classes (SlotCardBuilder, SlotLoadMore, SlotPopup)
- Toastify loaded as vendor file via `wp_enqueue_script` from `assets/admin/vendor/`, not bundled
- Frontend JS enqueued only when load-more or popup mode is active
- Type declarations in `assets/src/types/wordpress.d.ts` for WP globals + `SlotData` interface

## Testing

- **PHPUnit**: `vendor/bin/phpunit` -- tests in `tests/php/`, standalone bootstrap stubs WP functions
- **Vitest**: `cd assets/src && npm test` -- tests in `assets/src/__tests__/`, jsdom environment
- **Playwright**: `cd e2e-tests && npm test` -- e2e browser tests in plugin's `e2e-tests/`
- Tests cover: AuthSigner (validate_request, cross-plugin parity), SlotCardBuilder (XSS, stars, links), FetchHandler, Pagination (6 tests), Popup (4 tests)
- **E2E URL config**: `e2e-tests/playwright.config.ts` > `use.baseURL`
- **Test page**: created on activation via `activate.php` at `/test-page`

## Authentication (AuthSigner)

Identical logic to the manager plugin (different namespace):
- HMAC always uses **consumer site URL** (this site's own URL)
- This site sends its own URL in `X-Origin` header when syncing
- Incoming webhooks are validated: `X-Origin` must match this site's `siteurl`
- Webhook endpoint protected by rate limiter (`class-rate-limiter.php`)

## Key Patterns

- Sync flow: `SlotSyncManager::sync()` calls primary REST API > stores in transient
- Webhook flow: `WebhookEndpoint` > `WebhookProcessor` > `SlotTransientCache` update/remove
- Count mismatch: webhook includes `total_count`, if local differs triggers full resync
- Auto-sync: `SlotGridBlock::render()` checks `SlotTransientCache::is_expired()`, syncs if needed
- Cache expiry: configurable via admin page (stored in `wr_sc_cache_expiry_minutes` option, in minutes)
- Block renders: `render_template()` extracts vars via `compact()` into template scope
- All admin forms use AJAX via FetchHandler + dialog loading mask + Toastify messages
- Admin: single settings page with 4 JS-based tabs (Connection, API Settings, Cache, Manual Sync) + sidebar submenus

## Gutenberg Blocks

### Slot Grid (`wr-slot-consumer/slot-grid`)
- Server-side rendered via `SlotGridBlock::render()`
- 90+ configurable attributes (columns, colors, shadows, hover, typography)
- CSS custom properties for all styling
- Three pagination modes: off, page numbers, load-more (button or infinite scroll)
- Two link modes: detail page or popup (native `<dialog>`)

### Slot Detail (`wr-slot-consumer/slot-detail`)
- Server-side rendered via `SlotDetailBlock::render()`
- Dropdown slot selector sourced from REST API `/wr-slot-consumer/v1/slot-list`
- Styling panels for customization
- Visibility toggles for each section (image, rating, description, provider, rtp, wager)

## Text Domain

`wr-slot-consumer` -- all strings wrapped in `__()` or `esc_html_e()`. Serbian Cyrillic translations in `languages/` with 4 JSON files for block translations.
