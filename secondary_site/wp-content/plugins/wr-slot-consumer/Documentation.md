# WR Slot Consumer -- User Documentation

## Overview

WR Slot Consumer connects to a primary WR Slot Manager site, pulls slot game data, caches it locally, and displays it on your WordPress pages using Gutenberg blocks. Data stays in sync automatically via webhooks and cache auto-refresh.

## Getting Started

1. Activate the plugin (a test page at `/test-page` is created automatically)
2. Go to **Slot Consumer** in the admin sidebar
3. On the **Connection** tab, enter the **Source Site URL** (e.g. `http://local.wiserabbit.com`)
4. Enter the **API Key** (copy from the primary site's API Key page)
5. Switch to the **Cache** tab, set the **Cache Expiry** (in minutes, default 60)
6. Click **Save Settings**
7. Switch to the **Manual Sync** tab and click **Sync Data** to pull all slots for the first time

## Settings

Go to **Slot Consumer** in the admin sidebar. The settings page has 4 tabs (switched via JavaScript, no page reload) and matching sidebar submenus:

### Connection Tab
| Field           | Description                                           |
|-----------------|-------------------------------------------------------|
| Source Site URL  | Full URL of the primary WR Slot Manager site          |
| API Key          | Must match the API key from the primary site          |

### API Settings Tab
| Field           | Description                                           |
|-----------------|-------------------------------------------------------|
| Endpoint config  | REST API endpoint configuration                      |

### Cache Tab
| Field           | Description                                           |
|-----------------|-------------------------------------------------------|
| Cache Expiry     | How many minutes before cached data expires (triggers re-sync) |

### Manual Sync Tab
| Action          | Description                                           |
|-----------------|-------------------------------------------------------|
| Sync Data        | Manually pulls all slot data from the primary site   |

All forms save via AJAX (no page reload) with a dialog loading mask and Toastify notifications.

## Adding the Slot Grid Block

1. Edit any page in the Gutenberg editor
2. Add a new block, search for **"Slot Grid"**
3. The block appears with a live preview of your slots
4. Use the sidebar Inspector Controls to customize:

### Display Settings
- **Columns** -- 1 to 6 grid columns
- **Items Per Page** -- slots shown per page/load
- **Sort By** -- Recent or Random
- **Pagination** -- Off, Page Numbers, or Load More (button or infinite scroll)

### More Info Action
- **Open Detail Page** -- clicking "More Info" navigates to a detail page (append `?slot_detail=ID`)
- **Show Popup** -- clicking "More Info" opens a native dialog popup with full slot details

### Styling Controls
The block offers extensive styling through the sidebar:
- Block background (color, gradient, padding, border radius)
- Card styling (background, border, shadow, padding, hover effects)
- Title typography (size, weight, style, color, line height)
- Provider text styling
- Star rating (colors for full/half/empty, size, gap, border)
- More Info button (colors, shadow, border radius, hover state)

All styles use CSS custom properties, so they are clean and performant.

## Adding the Slot Detail Block

1. Create a new page (e.g. "Slot Detail")
2. Add the **"Slot Detail"** block
3. The block provides a **dropdown selector** that lists all available slots from the REST API (`/wr-slot-consumer/v1/slot-list`)
4. Select a slot from the dropdown, or leave it to read `?slot_detail=ID` from the URL
5. When visitors click "More Info" on the grid (in page mode), they are sent to this page with `?slot_detail=ID`

### Visibility Toggles
In the sidebar, toggle which sections to show:
- Image
- Star Rating
- Description
- Provider
- RTP
- Wager Range

### Styling Panels
The block includes styling panels for customizing the appearance of the detail display.

## How Data Stays in Sync

### Manual Sync
Click **Sync Data** on the Manual Sync tab to pull all slots from the primary site.

### Webhook Push
When a slot is created, updated, or deleted on the primary site, it pushes the change via webhook. If the local cache has fewer slots than the primary site's total count, a full resync triggers automatically. The webhook endpoint is protected by a rate limiter.

### Auto-Sync on Cache Expiry
When the Slot Grid block renders and the cached data has expired (based on your Cache Expiry setting), it automatically syncs fresh data from the primary site before displaying. No user action needed.

## Popup Mode

When "More Info Action" is set to **Show Popup**:
- Clicking "More Info" on any slot card opens a native `<dialog>` overlay
- Shows the slot image, title, star rating, description, provider, RTP, and wager range
- Click the X button, press Escape, or click the backdrop to close
- Page scroll is locked while the popup is open

## Load More / Infinite Scroll

When pagination is set to **Load More**:
- **Button mode** -- a "Load More" button appears below the grid
- **Infinite Scroll mode** -- new slots load automatically as you scroll down (uses IntersectionObserver)

Both modes load the next batch of slots from pre-loaded data (no additional server requests).

## Translations

The plugin supports internationalization. Serbian Cyrillic translation is included (`.po`, `.mo`, and 4 JSON files for block translations). To switch language, go to **Settings > General > Site Language** and select "Serbian".

## Troubleshooting

### Slots not appearing
1. Check **Slot Consumer** > Connection tab > verify Source URL and API Key are correct
2. Switch to the Manual Sync tab and click **Sync Data** to force a fresh pull
3. Verify the primary site is running and has slots

### Sync fails with HTTP 403
- API key mismatch -- copy the key again from the primary site
- Consumer site URL not in the primary site's Connected Sites list

### Slots disappear after a while
- Cache expired -- this is normal. The block auto-syncs on next page load
- Reduce Cache Expiry to a lower value for faster refresh

### Watch logs (Docker)
```bash
docker logs -f secondary_site-wordpress-1 2>&1 | grep "wr-slot-consumer"
```
Logs appear when `ENVIRONMENT=dev` in the `.env` file. Error logs (`log_error()`) always appear regardless of environment.

## Running Tests

### PHPUnit (PHP unit tests)

```bash
cd wp-content/plugins/wr-slot-consumer

# Install test dependencies (first time only)
composer install

# Run all tests
vendor/bin/phpunit

# Run a single test file
vendor/bin/phpunit tests/php/test-auth-signer.php
```

Tests run standalone without WordPress. The bootstrap stubs WP functions.

**Test files:**
- `test-auth-signer.php` -- 3-layer auth with all error branches + cross-plugin parity check

### Vitest (TypeScript unit tests)

```bash
cd wp-content/plugins/wr-slot-consumer/assets/src

# Run all tests
npm test

# Watch mode
npm run test:watch
```

**Test files:**
- `__tests__/SlotCardBuilder.test.ts` -- HTML rendering, XSS escaping, star ratings, link modes, edge cases
- `__tests__/FetchHandler.test.ts` -- dialog, Toastify, callbacks, error states

### Playwright (e2e browser tests)

End-to-end tests are in the plugin's `e2e-tests/` directory. They test the consumer site in a real browser.

```bash
cd wp-content/plugins/wr-slot-consumer/e2e-tests

# Install (first time)
npm install
npx playwright install chromium

# Run all e2e tests
npm test

# Run with UI (visual debugging)
npm run test:ui
```

**Configuring the site URL to test:**

Open `playwright.config.ts` and change the `baseURL`:

```ts
export default defineConfig( {
    use: {
        baseURL: 'http://sec.wiserabbit.com:81',  // your consumer site URL
    },
} );
```

**Prerequisite -- /etc/hosts:**

The browser must resolve the WordPress site hostname. Add this to `/etc/hosts`:

```
127.0.0.1 sec.wiserabbit.com local.wiserabbit.com
```

Without this, server-rendered tests (pagination) work via `localhost:81`, but JS-dependent tests (popup) fail because WordPress enqueues assets using the configured site URL.

**Test page:**

The plugin creates `/test-page` on activation (via `activate.php`). If missing:

```bash
docker exec secondary_site-wordpress-1 wp post create \
    --post_type=page --post_title="Test Page" --post_name=test-page \
    --post_status=publish \
    --post_content='<!-- wp:wr-slot-consumer/slot-grid {"paginationMode":"pagination","limit":3,"linkMode":"popup"} /-->' \
    --allow-root
```

**Test coverage:**
- `pagination.spec.ts` -- renders 3 cards, pagination nav visible, Next/Prev navigation, page number click, active page highlight (6 tests)
- `popup.spec.ts` -- More Info opens dialog, title displayed, close button, provider/rtp data (4 tests)
