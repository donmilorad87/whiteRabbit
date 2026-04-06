# WiseRabbit Slot System

Two-site WordPress system for managing and displaying slot game data. A **primary site** manages slots via a custom plugin and serves data through a REST API. A **secondary site** consumes that data and displays it on the frontend via Gutenberg blocks.

Both sites run in Docker with PHP 8.5, MySQL 9, and nginx.

---

## Prerequisites

- Docker & Docker Compose v2+
- Node.js 18+
- Composer

---

## 1. Hosts File

Both sites use custom hostnames. Add these to your hosts file:

**Linux / Mac:** `/etc/hosts`
**Windows:** `C:\Windows\System32\drivers\etc\hosts`

```
127.0.0.1 local.wiserabbit.com
127.0.0.1 sec.wiserabbit.com
```

---

## 2. Environment Configuration

All configuration is in `.env` files. Copy the examples and adjust as needed:

```bash
cp primary_site/.env.example primary_site/.env
cp secondary_site/.env.example secondary_site/.env
```

`.env` files are gitignored — they contain database passwords and site-specific settings. Key variables:

| Variable | Primary Default | Secondary Default | Purpose |
|----------|----------------|-------------------|---------|
| `ENVIRONMENT` | `dev` | `dev` | `dev` = info logs + no SSL verify, `prod` = error logs only + SSL verify |
| `WR_THEME_ENABLED` | `false` | `true` | `false` = API-only landing page, `true` = theme active |
| `WORDPRESS_URL` | `http://local.wiserabbit.com` | `http://sec.wiserabbit.com:81` | Site URL (must match /etc/hosts) |
| `REDIS_MAXMEMORY` | `128mb` | — | Redis memory limit (primary only) |

See `.env.example` in each site for all available variables.

---

## 3. Start Docker

```bash
# Primary site (port 80)
cd primary_site
docker compose up -d

# Secondary site (port 81)
cd ../secondary_site
docker compose up -d
```

Wait about 30 seconds for the first boot — WordPress installs automatically via `build.sh`.

---

## 4. Access

| Site | WordPress Admin | Credentials | phpMyAdmin |
|------|----------------|-------------|------------|
| Primary | http://local.wiserabbit.com/wp-admin/ | admin / admin | http://local.wiserabbit.com/pma/ |
| Secondary | http://sec.wiserabbit.com:81/wp-admin/ | admin / admin | http://sec.wiserabbit.com:81/pma/ |

---

## 5. Plugin Setup

### Primary Site (Slot Manager)

1. Go to **Slot Manager > Settings** in the admin sidebar
2. **API Settings** tab: click **Generate API Key** — copy the key
3. **Connected Sites** tab: add `http://sec.wiserabbit.com:81` and click **Add Site**
4. Create slots via **Slot Manager > Add New Slot**

### Secondary Site (Slot Consumer)

1. Go to **Slot Consumer** in the admin sidebar
2. **Connection** tab: set Source Site URL to `http://local.wiserabbit.com`
3. **API Settings** tab: paste the API key from the primary site
4. Click **Save Settings**
5. **Manual Sync** tab: click **Sync Data** to pull all slots

Slots now display on any page using the **Slot Grid** or **Slot Detail** Gutenberg blocks.

---

## 6. Build Frontend Assets

Both plugins use Vite + TypeScript. Build must run before the frontend works:

```bash
# Primary plugin
cd primary_site/wp-content/plugins/wr-slot-manager/assets/src
npm install
npm run build

# Secondary plugin
cd secondary_site/wp-content/plugins/wr-slot-consumer/assets/src
npm install
npm run build
```

Output goes to `assets/admin/`, `assets/editor/`, `assets/blocks/`.

---

## 7. Run PHPUnit Tests

PHP tests run standalone without WordPress — WP functions are stubbed in the bootstrap.

```bash
# Primary plugin
cd primary_site/wp-content/plugins/wr-slot-manager
composer install
vendor/bin/phpunit

# Secondary plugin
cd secondary_site/wp-content/plugins/wr-slot-consumer
composer install
vendor/bin/phpunit
```

Tests cover: AuthSigner (3-layer auth — all error branches), WebhookPayload, SlotPostType business logic.

---

## 8. Run Vitest (TypeScript Unit Tests)

```bash
# Primary plugin
cd primary_site/wp-content/plugins/wr-slot-manager/assets/src
npm test

# Secondary plugin
cd secondary_site/wp-content/plugins/wr-slot-consumer/assets/src
npm test
```

Tests cover: FetchHandler (dialog, Toastify, callbacks), SlotCardBuilder (HTML rendering, XSS escaping, star ratings).

---

## 9. Run Playwright E2E Tests

Browser tests live inside the consumer plugin:

```bash
cd secondary_site/wp-content/plugins/wr-slot-consumer/e2e-tests

# First time setup
npm install
npx playwright install chromium

# Run tests
npm test
```

Tests cover: pagination (6 tests — card rendering, next/prev, page numbers), popup dialog (4 tests — open, close, data display).

**URL configuration:** Edit `e2e-tests/playwright.config.ts` to change the `baseURL` if your site runs on a different address.

**Prerequisite:** The `/etc/hosts` entries must be set (see step 1) so the browser can load WordPress assets.

---

## Infrastructure

### Primary Site

```
primary_site/
├── Docker/
│   ├── nginx/          Reverse proxy (port 80)
│   ├── wordpress/      PHP 8.5, WP-CLI, entrypoint, build script
│   ├── mysql/          MySQL 9
│   ├── redis/          Redis 7 (configurable memory, persistence)
│   └── phpmyadmin/     Database admin at /pma/
├── docker-compose.yml
├── .env                All configuration (ENVIRONMENT, WR_THEME_ENABLED, Redis settings)
└── wp-content/plugins/wr-slot-manager/
```

- **Redis** caches slot data (configurable expiry via admin Settings)
- **Frontend disabled** by default (`WR_THEME_ENABLED=false`) — shows API landing page
- `ENVIRONMENT=dev` enables logging and disables SSL verify for local Docker

### Secondary Site

```
secondary_site/
├── Docker/
│   ├── nginx/          Reverse proxy (port 81)
│   ├── wordpress/      PHP 8.5, WP-CLI
│   ├── mysql/          MySQL 9 (port 3307)
│   └── phpmyadmin/     Database admin at /pma/
├── docker-compose.yml
├── .env
└── wp-content/plugins/wr-slot-consumer/
```

- **Transient cache** stores slot data (configurable expiry via admin)
- **Auto-sync**: when cache expires, the Slot Grid block fetches fresh data automatically
- **Webhook push**: primary site pushes changes instantly on slot create/update/delete

---

## Plugin: WR Slot Manager (Primary)

Custom WordPress plugin for managing slot game data.

**PHP** (21 classes in `includes/`):
- Custom post type `slot` with meta fields (star rating, provider, RTP, wager range, image)
- Gutenberg block editor with inline fields, image picker, Ctrl+S save
- Redis-backed object cache with configurable expiry
- REST API endpoint `GET /wr-slot-manager/v1/slots`
- Webhook dispatch to connected consumer sites on create/update/delete
- Webhook queue with retry logic (max 3 attempts)
- Rate limiter (configurable requests/minute per IP)
- 3-layer auth: Bearer token + HMAC-SHA256 + time-based nonce

**TypeScript** (8 files in `assets/src/`):
- Admin: ES6 classes (FetchHandler with dialog + Toastify, ApiKeyForm, ConnectedSitesForm, SettingsForm)
- Editor: Gutenberg block with React hooks, tab navigation, keyboard shortcuts

**Admin tabs**: Connected Sites, API Settings (key + rate limit), Cache Configuration

---

## Plugin: WR Slot Consumer (Secondary)

Custom WordPress plugin that receives slot data and displays it via Gutenberg blocks.

**PHP** (15 classes in `includes/`):
- Receives data via REST API sync and webhook push
- Transient cache with configurable expiry and auto-sync on expiry
- Count mismatch detection — webhook includes `total_count`, triggers full resync if local count differs
- Rate limiter (configurable requests/minute per IP)
- 3-layer auth matching the manager's AuthSigner

**TypeScript** (13 files in `assets/src/`):
- Admin: FetchHandler, SettingsForm, SyncButton
- Blocks: SlotCardBuilder (HTML rendering with XSS escaping), SlotLoadMore (infinite scroll), SlotPopup (native dialog)

**Gutenberg blocks**:
- **Slot Grid**: responsive grid with 90+ styling attributes, pagination (page numbers / load more / infinite scroll), popup or detail page link mode
- **Slot Detail**: dedicated slot page with dropdown selector, full styling controls (background, title, description, stars, meta info), ServerSideRender preview

**Admin tabs**: Connection, API Settings, Cache, Manual Sync

---

## Cross-Site Authentication

All API requests use 3-layer authentication via `AuthSigner`:

```
Authorization: Bearer <api_key>
X-Signature: HMAC-SHA256(base64(api_key:consumer_url), api_key)
X-Auth-Nonce: HMAC-SHA256(base64(api_key:hmac):time_window, api_key)
X-Origin: <consumer_site_url>
```

The manager validates `X-Origin` against its Connected Sites list. The consumer validates that `X-Origin` matches its own URL.

---

## Translations

Both plugins support Serbian Cyrillic (sr_RS). To switch language:

```bash
docker exec secondary_site-wordpress-1 wp site switch-language sr_RS --allow-root
docker exec primary_site-wordpress-1 wp site switch-language sr_RS --allow-root
```

---

## Environment

Control logging and SSL via `.env`:

```bash
ENVIRONMENT=dev    # Logs enabled, SSL verify off (for Docker)
ENVIRONMENT=prod   # Logs silent, SSL verify on
```

Change and restart: `docker compose restart wordpress`

---

## Stopping / Restarting

```bash
docker compose down       # Stop (data persists in volumes)
docker compose up -d      # Restart

docker compose down -v    # Stop and DELETE all data (fresh start)
```

---

## Watch Logs

```bash
# Primary
docker logs -f primary_site-wordpress-1 2>&1 | grep "wr-slot-manager"

# Secondary
docker logs -f secondary_site-wordpress-1 2>&1 | grep "wr-slot-consumer"
```

Logs appear when `ENVIRONMENT=dev`.
