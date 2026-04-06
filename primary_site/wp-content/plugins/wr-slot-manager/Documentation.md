# WR Slot Manager -- User Documentation

## Overview

WR Slot Manager lets you create and manage slot game entries from the WordPress admin. Each slot has a title, description, star rating, image, provider name, RTP percentage, and wager range. Slot data is served via REST API and pushed to connected consumer sites automatically.

## Getting Started

1. Activate the plugin
2. Go to **Slot Manager > Settings** and switch to the **API Settings** tab
3. Click **Generate API Key** and copy the key -- you will need it for consumer sites

## Creating a Slot

1. Go to **Slot Manager > Add New Slot**
2. Fill in the fields:
   - **Title** -- name of the slot game
   - **Description** -- short description
   - **Star Rating** -- 0 to 5 (supports half stars like 3.5)
   - **Slot Image** -- click "Select Image" to pick from media library
   - **Provider Name** -- game provider/studio
   - **RTP (%)** -- return to player percentage (0-100)
   - **Min Wager / Max Wager** -- bet range
3. Click **Save Slot** (or press Ctrl+S / Cmd+S)

All slots are automatically set to "Private" status -- they are only visible via the API, not on the frontend.

## Keyboard Shortcuts

| Shortcut  | Action                                    |
|-----------|-------------------------------------------|
| Tab       | Move between fields                       |
| Shift+Tab | Move backwards through fields             |
| Ctrl+S    | Save the slot                             |

## Settings Page

The plugin has a single **Settings** page under the **Slot Manager** menu with 3 tabs that switch via JavaScript (no page reload):

### Connected Sites Tab

Manage which consumer sites receive webhook data.

- **Add Site** -- enter the full URL of the consumer site (including port if needed, e.g. `http://sec.wiserabbit.com:81`)
- **Update** -- edit an existing site URL inline
- **Remove** -- remove a site from the list (with confirmation)

When you create, update, or delete a slot, a webhook is sent to all connected sites automatically.

### API Settings Tab

Manage the shared API key and rate limiting.

- Click **Generate API Key** to create a new key
- Click **Regenerate API Key** to replace the current key (all consumer sites will need the new key)
- Click **Copy** to copy the key to clipboard
- Configure rate limit settings for the REST API

Share this key with consumer sites -- they need it to authenticate.

### Cache Configuration Tab

Configure the Redis cache behavior.

- **Redis Cache Expiry (minutes)** -- how long slot data stays in Redis before rebuilding from the database
- Default: 60 minutes
- For testing, set to 1-2 minutes

## REST API

The plugin exposes a single endpoint:

```
GET /wp-json/wr-slot-manager/v1/slots
```

Or using query parameter format:
```
GET /?rest_route=/wr-slot-manager/v1/slots
```

Requires authentication headers (handled automatically by consumer sites). Requests are rate-limited per IP.

## Webhooks

When a slot is saved or deleted, the plugin:
1. Updates the Redis cache
2. Builds a webhook payload with the slot data + total slot count
3. Sends it immediately to all connected sites

If delivery fails, the job is re-queued (up to 3 retries). The webhook sender processes a maximum of 50 jobs per run to prevent runaway processing.

## Frontend Disabled Mode

When `WR_THEME_ENABLED=false` in the environment, visiting the site URL shows a terminal-style API status page instead of the WordPress theme. The admin dashboard and REST API continue to work normally.

## Running Tests

### PHPUnit (PHP unit tests)

```bash
cd wp-content/plugins/wr-slot-manager

# Install test dependencies (first time only)
composer install

# Run all tests
vendor/bin/phpunit

# Run a single test file
vendor/bin/phpunit tests/php/test-auth-signer.php
```

Tests run standalone without WordPress. The bootstrap (`tests/php/bootstrap.php`) stubs WP functions (`WP_Error`, `WP_REST_Request`, `hash_equals`, etc.).

**Test files:**
- `test-auth-signer.php` -- 3-layer auth: HMAC generation, nonce, full validate_request with all 10 error branches
- `test-webhook-payload.php` -- payload structure, types, defaults
- `test-slot-post-type.php` -- force_private_status business rule

### Vitest (TypeScript unit tests)

```bash
cd wp-content/plugins/wr-slot-manager/assets/src

# Run all tests
npm test

# Watch mode (re-runs on save)
npm run test:watch
```

**Test files:**
- `__tests__/FetchHandler.test.ts` -- dialog management, fetch calls, Toastify notifications, callbacks, error handling

## Translations

The plugin supports internationalization. Serbian Cyrillic translation is included. To switch language, go to **Settings > General > Site Language** and select "Serbian".
