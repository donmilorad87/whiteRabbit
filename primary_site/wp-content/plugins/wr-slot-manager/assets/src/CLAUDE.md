# Claude Code -- Vite Build Source (Manager)

## Build

- `npm run build` outputs to `../admin/js/`, `../admin/css/`, `../editor/js/`, `../editor/css/`
- IIFE output format (prevents variable collisions with WP globals like Underscore.js)
- Terser minification with `_` and `$` reserved (Underscore.js protection)
- Two entry points: `js/admin/adminApp` and `js/editor/slotFields`
- Source: TypeScript (`.ts`) -- Vite transpiles natively

## TypeScript

- `tsconfig.json` with `target: ES2022`, `strict: true`, `moduleResolution: bundler`
- Type declarations in `types/wordpress.d.ts` for WP globals + Toastify + `wrSmAdmin`
- Admin JS: ES6 classes with full typed constructors, methods, properties
- Editor JS: WordPress block API (function components + hooks -- required by Gutenberg)

## Testing

```bash
npm test           # run Vitest
npm run test:watch # watch mode
```

- Tests in `__tests__/` directory (`.test.ts` files)
- Environment: jsdom
- Mock `Toastify` and `fetch` as globals
- Coverage: FetchHandler (dialog, toast, callbacks, error states, reload behavior)

## Vendor Files

- Toastify loaded via PHP `wp_enqueue_script` from `../admin/vendor/`
- NOT bundled by Vite -- kept as separate vendor file
