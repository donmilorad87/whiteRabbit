---
name: playwright-e2e
description: Playwright end-to-end browser tests for the consumer site. Use when writing or running e2e tests against the WordPress frontend.
paths: "**/e2e-tests/**"
---

# Playwright E2E Tests

## Location
`secondary_site/wp-content/plugins/wr-slot-consumer/e2e-tests/`

## Config (`playwright.config.ts`)
```typescript
import { defineConfig } from '@playwright/test';
export default defineConfig({
    testDir: './tests',
    timeout: 30000,
    use: {
        baseURL: 'http://sec.wiserabbit.com:81',  // Change for your environment
        headless: true,
        viewport: { width: 1280, height: 720 },
    },
});
```

## Prerequisites
- `/etc/hosts`: `127.0.0.1 sec.wiserabbit.com local.wiserabbit.com`
- Both Docker sites running
- Consumer has synced data
- `/test-page` exists (auto-created on plugin activation)

## Test Pattern
```typescript
import { test, expect } from '@playwright/test';

test.describe('Slot Grid Pagination', () => {
    test('renders slot cards', async ({ page }) => {
        await page.goto('/test-page/');
        const cards = page.locator('.wr-sc-slot-card');
        await expect(cards.first()).toBeVisible();
        expect(await cards.count()).toBe(3);
    });
});
```

## Commands
```bash
cd e2e-tests
npm install && npx playwright install chromium
npm test              # Headless
npm run test:ui       # Visual UI
```

## Locator Tips
- Use CSS classes (`.wr-sc-slot-card`, `.wr-sc-pagination__link`)
- For translated text, use symbols (`«`, `»`) not English words
- Native `<dialog>` becomes visible when `.showModal()` is called
- Test IDs: `#wr-sc-dialog`, `#wr-sc-sync-btn`
