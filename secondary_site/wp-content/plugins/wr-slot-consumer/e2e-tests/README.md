# E2E Tests (Playwright)

End-to-end browser tests for the WR Slot Consumer plugin. Tests run against the consumer site's frontend in a real browser.

## Setup

```bash
npm install
npx playwright install chromium
```

## Run

```bash
npm test              # headless
npm run test:ui       # visual UI mode
```

## Configuring the Test URL

Edit `playwright.config.ts` (in this directory) and set `baseURL` to your consumer site:

```ts
export default defineConfig( {
    use: {
        baseURL: 'http://sec.wiserabbit.com:81',  // change this
    },
} );
```

## Prerequisites

### /etc/hosts

The browser must resolve the WordPress site hostname:

```
127.0.0.1 sec.wiserabbit.com local.wiserabbit.com
```

Without this, server-rendered tests (pagination) pass via `localhost:81`, but JS-dependent tests (popup, load-more) fail because WordPress enqueues scripts/styles using the configured site URL.

### Test Page

The consumer plugin creates `/test-page` on activation (via `activate.php`) with a slot grid block (pagination mode, 3 per page, popup links). If the page is missing:

```bash
docker exec secondary_site-wordpress-1 wp post create \
    --post_type=page --post_title="Test Page" --post_name=test-page \
    --post_status=publish \
    --post_content='<!-- wp:wr-slot-consumer/slot-grid {"paginationMode":"pagination","limit":3,"linkMode":"popup"} /-->' \
    --allow-root
```

### Docker

Both sites must be running:

```bash
cd primary_site && docker compose up -d
cd secondary_site && docker compose up -d
```

Consumer must have synced data. If empty, sync via admin or WP-CLI:

```bash
docker exec secondary_site-wordpress-1 wp eval '$s = new WiseRabbit\SlotConsumer\Sync\SlotSyncManager(); $s->sync();' --allow-root
```

## Test Files

| File | Tests | What |
|------|-------|------|
| `tests/pagination.spec.ts` | 6 | Card rendering, pagination nav, Next/Prev, page numbers, active state |
| `tests/popup.spec.ts` | 4 | Dialog opens, title shown, close button, provider/rtp data |

## Adding New Tests

Create a new `.spec.ts` file in `tests/`. Playwright auto-discovers all `*.spec.ts` files.

```ts
import { test, expect } from '@playwright/test';

test( 'my test', async ( { page } ) => {
    await page.goto( '/test-page/' );
    // ...
} );
```
