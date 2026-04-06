# WiseRabbit Secondary Site

Dockerized WordPress instance that consumes slot data from the primary site and displays it via Gutenberg blocks on the frontend.

## Architecture

```
secondary_site/
├── Docker/
│   ├── nginx/          Reverse proxy (port 81)
│   ├── wordpress/      WordPress + WP-CLI, build.sh, entrypoint.sh
│   ├── mysql/          MySQL 9
│   └── phpmyadmin/     Database UI at /pma/
├── wp-content/
│   ├── plugins/
│   │   └── wr-slot-consumer/  Custom plugin (see plugin README)
│   ├── themes/
│   └── uploads/               Persisted via host volume
├── docker-compose.yml
├── .env                       All configuration
└── notes.md                   Local setup instructions
```

## Services

| Service    | Port | Purpose               |
|------------|------|-----------------------|
| nginx      | 81   | Reverse proxy         |
| wordpress  | -    | WordPress + WP-CLI    |
| mysql      | 3307 | Database              |
| phpmyadmin | -    | DB admin at /pma/     |

## Environment Variables (.env)

| Variable         | Default                      | Purpose                           |
|------------------|------------------------------|-----------------------------------|
| ENVIRONMENT      | dev                          | `dev` = logs + no SSL verify, `prod` = silent + SSL verify |
| WR_THEME_ENABLED | true                         | Frontend theme active             |
| WORDPRESS_URL    | http://sec.wiserabbit.com:81 | Site URL                          |

## How It Works

1. Consumer plugin receives slot data via manual sync or webhook push
2. Data is stored in WordPress transients (configurable expiry via admin)
3. When transient expires, the Slot Grid block auto-syncs from the primary site
4. Webhooks include `total_count` -- if local count mismatches, full resync triggers
5. Two Gutenberg blocks display slots: Slot Grid (list with pagination/load-more/infinite-scroll) and Slot Detail (single slot with dropdown selector)
6. Slot Detail block has a dropdown slot selector sourced from REST API `/wr-slot-consumer/v1/slot-list`
7. 3-layer auth secures all cross-site communication
8. Rate limiter protects the webhook endpoint

## Quick Start

```bash
# Primary site must be running first

docker compose up -d

cd wp-content/plugins/wr-slot-consumer/assets/src
npm install && npm run build
```

Access: http://sec.wiserabbit.com:81/wp-admin/ (admin / admin)

Configure: Slot Consumer > Source URL + API Key > Save > Sync Data

## Admin Page

Single settings page with 4 JavaScript-based tabs and sidebar submenus:
- **Connection** -- Source URL, API Key
- **API Settings** -- Endpoint configuration
- **Cache** -- Cache expiry settings
- **Manual Sync** -- Sync data button

## Testing

```bash
# PHPUnit (standalone, no WP needed)
cd wp-content/plugins/wr-slot-consumer
composer install
vendor/bin/phpunit

# Vitest (TypeScript unit tests)
cd wp-content/plugins/wr-slot-consumer/assets/src
npm test

# Playwright (e2e browser tests)
cd wp-content/plugins/wr-slot-consumer/e2e-tests
npm install && npx playwright install chromium
npm test
```

**E2E test URL:** Edit `e2e-tests/playwright.config.ts` > `baseURL` to point to your consumer site.
Requires `/etc/hosts`: `127.0.0.1 sec.wiserabbit.com`

## Data Flow

```
Primary Site                          Secondary Site
     │                                      │
     ├─ REST API /v1/slots ◄── Manual Sync ─┤
     │                                      │
     ├─ Webhook /v1/webhook ──► WebhookProcessor ──► TransientCache
     │                                      │
     │                          SlotGridBlock checks is_expired()
     │                                │ yes
     │                          Auto-sync (source=block)
     │                                │
     │                          Render slots from cache
```
