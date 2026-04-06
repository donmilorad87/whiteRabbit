# Claude Code Instructions -- WR Slot Manager Plugin

## Folder Structure (VIP-aligned)

- `wr-slot-manager.php` -- thin bootstrap (header + constants only)
- `plugin.php` -- autoloader + init (loaded by `wr-slot-manager.php`)
- `activate.php` / `deactivate.php` -- activation/deactivation hooks
- `includes/` -- PHP classes (`class-{name}.php`) and traits (`trait-{name}.php`)
- `assets/src/` -- Vite + TypeScript source, `__tests__/` for Vitest, `types/` for declarations
- `assets/admin/` -- built admin JS/CSS + vendor (Toastify)
- `assets/editor/` -- built editor JS/CSS
- `templates/admin/settings.php` -- single unified settings template (3 JS tabs)
- `tests/php/` -- PHPUnit tests
- `languages/` -- .pot/.po/.mo/.json translations

## Code Conventions

- PHP: OOP with namespaces (`WiseRabbit\SlotManager\`), VIP autoloader (`includes/`)
- WordPress VIP coding standards
- File naming: `class-{name}.php` for classes, `trait-{name}.php` for traits
- Directory naming: lowercase-hyphenated (e.g. `post-type/`, `slot-fields/`)
- Traits for cross-cutting concerns (LoggerTrait, OptionPrefixTrait, NonceVerificationTrait, TemplateLoaderTrait)
- Templates loaded via `$this->load_template()` with path validation (not raw `include`)
- All `$_GET`/`$_POST` access uses `wp_unslash()` + `sanitize_text_field()`
- All output escaped with `esc_html()`, `esc_attr()`, `esc_url()`
- `log_info()` gated by `ENVIRONMENT === 'dev'` -- `log_error()` always fires regardless of ENVIRONMENT
- SSL verify controlled by `ENVIRONMENT` (off in dev, on in prod)

## Admin UI

Single unified Settings page with 3 JS-based tabs (no page reload):
- **Connected Sites** -- manage consumer site URLs (add, edit, remove via AJAX)
- **API Settings** -- generate/regenerate API key + rate limit configuration
- **Cache Configuration** -- Redis cache expiry (minutes)

Template: `templates/admin/settings.php`
PHP class: `includes/admin/class-settings-page.php`

## JavaScript / TypeScript

- Vite build with IIFE output format (prevents variable collisions with WP globals)
- Terser minification (`reserved: ['_', '$']` to avoid Underscore.js collision)
- Source: TypeScript (`.ts`) in `assets/src/` -- Vite transpiles natively
- `tsconfig.json` with `target: ES2022`, `strict: true`, `moduleResolution: bundler`
- Type declarations in `assets/src/types/wordpress.d.ts` for WP globals + Toastify + `wrSmAdmin`
- Admin: ES6 classes (FetchHandler, ApiKeyForm, ConnectedSitesForm, SettingsForm)
- Editor: WordPress block API (function components with hooks -- React is required by Gutenberg)
- Toastify loaded as vendor file via `wp_enqueue_script` from `assets/admin/vendor/`, not bundled
- `wp.i18n.__()` for block translations, JSON translation file for Serbian
- Build output: `assets/admin/{js,css}/` and `assets/editor/{js,css}/`

## Testing

- **PHPUnit**: `vendor/bin/phpunit` -- tests in `tests/php/`, standalone bootstrap stubs WP functions
- **Vitest**: `cd assets/src && npm test` -- tests in `assets/src/__tests__/`, jsdom environment
- Tests cover: AuthSigner (all 10 validate_request branches), WebhookPayload, SlotPostType, FetchHandler

## Authentication (AuthSigner)

Both plugins share identical AuthSigner logic (different namespaces):
- HMAC always uses **consumer site URL** (not manager URL)
- Consumer sends its own URL in `X-Origin` header
- Manager validates `X-Origin` against Connected Sites list
- Nonce uses 5-minute time windows, checks current + previous

## REST API

- Endpoint: `GET /wr-slot-manager/v1/slots`
- Authentication: 3-layer via `class-authentication.php`
- Rate limiting: transient-based per-IP via `class-rate-limiter.php`

## Key Patterns

- Slot save flow: `wp_after_insert_post` hook > `SlotCache::update_single_slot()` > `WebhookDispatcher::dispatch()` > `WebhookSender::process()` (immediate, not cron)
- `WebhookSender` uses bounded loop: `MAX_JOBS_PER_RUN = 50` to prevent runaway processing
- Cache reads: `SlotCache::get_all_slots()` falls back to DB rebuild on miss
- Cache expiry: configurable via admin Settings page (stored in `wr_sm_cache_expiry_minutes` option)
- All admin forms use AJAX via FetchHandler + dialog loading mask + Toastify messages
- Frontend disabled via `WR_THEME_ENABLED=false` (terminal-style API landing page)

## Text Domain

`wr-slot-manager` -- all strings wrapped in `__()` or `esc_html_e()`. Serbian Cyrillic translations in `languages/`.
