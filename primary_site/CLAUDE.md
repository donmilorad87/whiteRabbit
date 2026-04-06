# Claude Code Instructions -- Primary Site

## Project Context

This is the **primary site** of a two-site WordPress slot management system. It runs in Docker and manages slot game data via the `wr-slot-manager` plugin. The secondary site (`../secondary_site`) consumes data from this site.

## Infrastructure

- Docker Compose with nginx, WordPress, MySQL, Redis, phpMyAdmin
- Entrypoint syncs `ENVIRONMENT` and `WR_THEME_ENABLED` from `.env` into wp-config on every container start
- Redis has its own Dockerfile with configurable memory/persistence via `.env`
- `WR_THEME_ENABLED=false` disables the frontend (API-only mode)

## Plugin: wr-slot-manager

Located at `wp-content/plugins/wr-slot-manager/`. See its own CLAUDE.md for code conventions.

Key structure:
- `wr-slot-manager.php` -- thin bootstrap (header + constants only)
- `plugin.php` -- autoloader + hooks + i18n init
- `activate.php` / `deactivate.php` -- activation/deactivation handlers
- `includes/` -- all PHP classes (`class-{name}.php`) and traits (`trait-{name}.php`)
- `assets/src/` -- Vite + TypeScript source
- `assets/admin/` -- built admin JS/CSS + vendor (Toastify)
- `assets/editor/` -- built editor JS/CSS
- `templates/admin/settings.php` -- single settings template (3 JS tabs)
- `tests/php/` -- PHPUnit tests

## Key Commands

```bash
# Build plugin assets
cd wp-content/plugins/wr-slot-manager/assets/src && npm run build

# Restart with env changes
docker compose restart wordpress

# Watch logs
docker logs -f primary_site-wordpress-1 2>&1 | grep "wr-slot-manager"

# Fix uploads permissions
docker exec primary_site-wordpress-1 chown -R www-data:www-data /var/www/html/wp-content/uploads
```

## Environment

- `ENVIRONMENT=dev` in `.env` enables info-level logging and disables SSL verify
- `ENVIRONMENT=prod` restricts to error-level logging and enables SSL verify
- `log_error()` always fires regardless of ENVIRONMENT (errors are never silenced)
- Entrypoint handles the wp-config constant -- no rebuild needed, just restart

## Testing

```bash
# PHPUnit -- standalone tests (AuthSigner, WebhookPayload, SlotPostType)
cd wp-content/plugins/wr-slot-manager && vendor/bin/phpunit

# Vitest -- TypeScript tests (FetchHandler)
cd wp-content/plugins/wr-slot-manager/assets/src && npm test
```

## Cross-Site Communication

All requests between sites use 3-layer auth (AuthSigner):
1. `Authorization: Bearer <api_key>`
2. `X-Signature: HMAC-SHA256(base64(api_key:consumer_url), api_key)`
3. `X-Auth-Nonce: time-based nonce (5-min window)`
4. `X-Origin: consumer site URL`

The manager validates `X-Origin` against its Connected Sites list. REST API requests are also rate-limited per IP.
