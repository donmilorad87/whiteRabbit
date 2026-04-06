---
name: vitest-testing
description: Vitest testing for TypeScript in WordPress plugins. Use when writing JS/TS unit tests with jsdom environment.
paths: "**/__tests__/**,**/vitest.config.*"
---

# Vitest Testing

## Config (in vite.config.js)
```js
test: {
    environment: 'jsdom',
    include: ['__tests__/**/*.test.ts'],
    globals: true,
}
```

## Test Pattern
```typescript
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { FetchHandler } from '../js/admin/classes/FetchHandler.ts';

// Mock globals
globalThis.Toastify = vi.fn(() => ({ showToast: vi.fn() }));
globalThis.wrSmAdmin = { ajaxUrl: '/wp-admin/admin-ajax.php' };

function makeDialog(): HTMLDialogElement {
    const dialog = document.createElement('dialog') as HTMLDialogElement;
    dialog.showModal = vi.fn();
    dialog.close = vi.fn();
    return dialog;
}

describe('FetchHandler', () => {
    let handler: FetchHandler;
    beforeEach(() => {
        handler = new FetchHandler(makeDialog());
        vi.restoreAllMocks();
        Toastify.mockClear();
    });

    it('opens dialog on fetch', () => { /* ... */ });
});
```

## Commands
```bash
cd assets/src
npm test             # Run all tests
npm run test:watch   # Watch mode
```

## Key Rules
- Tests in `assets/src/__tests__/*.test.ts`
- Mock WordPress globals (`Toastify`, `wrSmAdmin`/`wrScAdmin`, `fetch`)
- Use `vi.fn()` for mocks, `vi.waitFor()` for async assertions
- Clear mocks in `beforeEach` to prevent leaking between tests
- DOM elements created via `document.createElement` in jsdom
