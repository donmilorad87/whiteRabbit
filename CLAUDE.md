# WiseRabbit Slot System — Project Root

Two-site WordPress slot management system running in Docker with PHP 8.5.

## Sites

| Site | Path | URL | Purpose |
|------|------|-----|---------|
| Primary | `primary_site/` | http://local.wiserabbit.com | Slot Manager (API, backend, Redis) |
| Secondary | `secondary_site/` | http://sec.wiserabbit.com:81 | Slot Consumer (frontend, blocks) |

## Plugins

| Plugin | Path | Stack |
|--------|------|-------|
| WR Slot Manager | `primary_site/wp-content/plugins/wr-slot-manager/` | PHP 8.5 + TypeScript + Vite |
| WR Slot Consumer | `secondary_site/wp-content/plugins/wr-slot-consumer/` | PHP 8.5 + TypeScript + Vite |

## Architecture

- **PHP**: VIP folder structure (`includes/` with `class-{name}.php`, `trait-{name}.php`), namespaced (`WiseRabbit\SlotManager\`, `WiseRabbit\SlotConsumer\`)
- **Frontend**: Vite + TypeScript (strict) in `assets/src/`, builds to `assets/{admin,editor,blocks}/` with IIFE wrapping
- **Auth**: 3-layer (Bearer + HMAC + time-based nonce) via `AuthSigner` — HMAC uses consumer site URL
- **Cache**: Manager uses Redis object cache, Consumer uses WordPress transients — both configurable expiry via admin
- **Docker**: nginx + WordPress (PHP 8.5) + MySQL 9 + Redis 7 + phpMyAdmin per site
- **Environment**: `ENVIRONMENT=dev|prod` controls logging (`log_info` dev only, `log_error` always) and SSL verify
- **i18n**: Serbian Cyrillic translations, `.po`/`.mo` for PHP, `.json` for JS blocks
- **Admin**: Single settings page per plugin with JS-based tabs (no page reload)
- **Blocks**: Manager has 1 (Slot Fields editor), Consumer has 2 (Slot Grid + Slot Detail with dropdown selector)
- **Rate Limiter**: Transient-based per-IP, configurable via admin settings
- **Testing**: PHPUnit (standalone), Vitest (jsdom), Playwright (e2e browser)

## Commands

```bash
# Docker
cd primary_site && docker compose up -d
cd secondary_site && docker compose up -d

# Build
cd primary_site/wp-content/plugins/wr-slot-manager/assets/src && npm run build
cd secondary_site/wp-content/plugins/wr-slot-consumer/assets/src && npm run build

# PHPUnit
cd primary_site/wp-content/plugins/wr-slot-manager && vendor/bin/phpunit
cd secondary_site/wp-content/plugins/wr-slot-consumer && vendor/bin/phpunit

# Vitest
cd primary_site/wp-content/plugins/wr-slot-manager/assets/src && npm test
cd secondary_site/wp-content/plugins/wr-slot-consumer/assets/src && npm test

# Playwright E2E
cd secondary_site/wp-content/plugins/wr-slot-consumer/e2e-tests && npx playwright test

# Logs
docker logs -f primary_site-wordpress-1 2>&1 | grep "wr-slot-manager"
docker logs -f secondary_site-wordpress-1 2>&1 | grep "wr-slot-consumer"
```

## Custom Agent

A `full-stack-wordpress-developer` agent is available at `.claude/agents/` with 20 skills covering WordPress VIP, PHP 8.5, TypeScript, Vite, Gutenberg blocks, Docker, REST API, security, testing, and headless frontends.
