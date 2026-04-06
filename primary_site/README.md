# WiseRabbit Primary Site

Dockerized WordPress instance that serves as the slot management backend and API. No frontend theme -- operates in API/backend-only mode.

## Architecture

```
primary_site/
├── Docker/
│   ├── nginx/          Reverse proxy (port 80)
│   ├── wordpress/      WordPress + WP-CLI, build.sh, entrypoint.sh
│   ├── mysql/          MySQL 9
│   ├── redis/          Redis 7 with configurable memory/persistence
│   └── phpmyadmin/     Database UI at /pma/
├── wp-content/
│   ├── plugins/
│   │   └── wr-slot-manager/   Custom plugin (see plugin README)
│   ├── themes/
│   └── uploads/               Persisted via host volume
├── docker-compose.yml
├── .env                       All configuration
└── notes.md                   Local setup instructions
```

## Services

| Service    | Port | Purpose                       |
|------------|------|-------------------------------|
| nginx      | 80   | Reverse proxy                 |
| wordpress  | -    | WordPress + WP-CLI            |
| mysql      | 3306 | Database                      |
| redis      | 6379 | Object cache (wp-redis)       |
| phpmyadmin | -    | DB admin at /pma/             |

## Environment Variables (.env)

| Variable              | Default              | Purpose                           |
|-----------------------|----------------------|-----------------------------------|
| ENVIRONMENT           | dev                  | `dev` = info logs + no SSL verify, `prod` = error logs only + SSL verify |
| WR_THEME_ENABLED      | false                | `false` = API-only landing page, `true` = theme active |
| REDIS_MAXMEMORY       | 128mb                | Redis memory limit                |
| REDIS_MAXMEMORY_POLICY| allkeys-lru          | Redis eviction policy             |
| WORDPRESS_URL         | http://local.wiserabbit.com | Site URL                   |

## How It Works

1. WordPress manages slot data via the `wr-slot-manager` plugin
2. Slots are stored as a custom post type with meta fields
3. Redis caches all slot data (configurable expiry via admin Settings page)
4. REST API at `/wr-slot-manager/v1/slots` serves data to consumer sites (rate-limited)
5. On slot create/update/delete, webhooks push changes to connected consumer sites
6. 3-layer auth (Bearer + HMAC + time-based nonce) secures all cross-site communication
7. Webhook sender uses bounded loop (MAX_JOBS_PER_RUN = 50) to prevent runaway processing

## Quick Start

```bash
# Add to /etc/hosts:
# 127.0.0.1 local.wiserabbit.com sec.wiserabbit.com

docker compose up -d

# Build plugin assets:
cd wp-content/plugins/wr-slot-manager/assets/src
npm install && npm run build
```

Access: http://local.wiserabbit.com/wp-admin/ (admin / admin)

## Testing

```bash
# PHPUnit (standalone, no WP needed)
cd wp-content/plugins/wr-slot-manager
composer install
vendor/bin/phpunit

# Vitest (TypeScript unit tests)
cd wp-content/plugins/wr-slot-manager/assets/src
npm test
```

## Data Flow

```
Gutenberg Editor
      |
      v
SlotSaveHook --> SlotCache (Redis) --> REST API /v1/slots
      |
      v
WebhookDispatcher --> WebhookSender --> Consumer /v1/webhook
```
