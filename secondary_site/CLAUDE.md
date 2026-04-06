# Claude Code Instructions -- Secondary Site

## Project Context

This is the **secondary site** of a two-site WordPress slot management system. It runs in Docker and displays slot data from the primary site (`../primary_site`) via the `wr-slot-consumer` plugin.

## Infrastructure

- Docker Compose with nginx, WordPress (PHP 8.5), MySQL, phpMyAdmin (no Redis -- uses transients)
- Entrypoint syncs `ENVIRONMENT` and `WR_THEME_ENABLED` from `.env` into wp-config on every container start
- `WR_THEME_ENABLED=true` (frontend active with slot grid/detail blocks)

## Plugin: wr-slot-consumer

Located at `wp-content/plugins/wr-slot-consumer/`. See its own CLAUDE.md for code conventions.

### Plugin File Structure

- `wr-slot-consumer.php` -- thin bootstrap (header + constants only)
- `plugin.php` -- autoloader + hooks + i18n
- `activate.php` -- creates test page for e2e on activation
- `deactivate.php` -- placeholder
- `uninstall.php` -- cleanup on plugin deletion
- `includes/` -- PHP classes (VIP structure: `class-{name}.php`, `trait-{name}.php`)
- `assets/src/` -- Vite + TypeScript source
- `assets/admin/` -- built admin JS/CSS + vendor (Toastify)
- `assets/blocks/` -- built block JS/CSS
- `templates/` -- PHP templates (admin + blocks)
- `tests/php/` -- PHPUnit tests
- `e2e-tests/` -- Playwright e2e tests
- `languages/` -- .pot/.po/.mo + 4 JSON files for block translations

## Key Commands

```bash
# Build plugin assets
cd wp-content/plugins/wr-slot-consumer/assets/src && npm run build

# Restart with env changes
docker compose restart wordpress

# Watch logs
docker logs -f secondary_site-wordpress-1 2>&1 | grep "wr-slot-consumer"

# Fix uploads permissions
docker exec secondary_site-wordpress-1 chown -R www-data:www-data /var/www/html/wp-content/uploads
```

## Environment

- `ENVIRONMENT=dev` in `.env` enables logging and disables SSL verify
- `ENVIRONMENT=prod` silences info logs (errors always fire) and enables SSL verify
- Entrypoint handles the wp-config constant -- no rebuild needed, just restart

## Testing

```bash
# PHPUnit -- standalone tests (AuthSigner)
cd wp-content/plugins/wr-slot-consumer && vendor/bin/phpunit

# Vitest -- TypeScript tests (SlotCardBuilder, FetchHandler)
cd wp-content/plugins/wr-slot-consumer/assets/src && npm test

# Playwright -- e2e browser tests (pagination, popup)
cd wp-content/plugins/wr-slot-consumer/e2e-tests && npm test
# Configure URL: edit playwright.config.ts > baseURL
```

## Cross-Site Communication

All requests between sites use 3-layer auth (AuthSigner):
1. `Authorization: Bearer <api_key>`
2. `X-Signature: HMAC-SHA256(base64(api_key:consumer_url), api_key)`
3. `X-Auth-Nonce: time-based nonce (5-min window)`
4. `X-Origin: consumer site URL (this site's own URL)`

The consumer validates that `X-Origin` matches its own `siteurl`.

Webhook endpoint is protected by a rate limiter (`class-rate-limiter.php`).
