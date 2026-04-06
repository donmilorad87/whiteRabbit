# Claude Code -- Vite Build Source (Consumer)

## Build

- `npm run build` outputs to `../admin/js/`, `../admin/css/`, `../blocks/js/`, `../blocks/css/`
- IIFE output format with Terser minification (`_` and `$` reserved)
- Four JS entry points: admin, slot-grid (editor), slot-grid-frontend, slot-detail (editor)
- SCSS entries: admin styles + block frontend/editor styles
- WordPress `@wordpress/*` packages are externalized (not bundled)
- Source: TypeScript (`.ts`) -- Vite transpiles natively, target ES2022

## TypeScript

- `tsconfig.json` with `target: ES2022`, `strict: true`, `moduleResolution: bundler`
- Type declarations in `types/wordpress.d.ts` for WP globals + `SlotData` interface + Toastify + `wrScAdmin`
- Admin JS: ES6 classes with typed constructors/methods (`adminApp.ts` handles tab switching + form init)
- Block editor JS: WordPress block API (function components + hooks)
  - `slot-grid/index.ts` -- 90+ attributes, SSR preview
  - `slot-detail/index.ts` -- dropdown slot selector from REST API + styling panels
- Frontend JS: ES6 classes (SlotCardBuilder, SlotLoadMore, SlotPopup) with full types

## Testing

```bash
npm test           # run Vitest
npm run test:watch # watch mode
```

- Tests in `__tests__/` directory (`.test.ts` files)
- Environment: jsdom
- Coverage: SlotCardBuilder (XSS, stars, links, edge cases), FetchHandler (dialog, toast, callbacks)
- All slot data HTML-escaped via `esc()` helper -- tested for XSS prevention

## Vendor Files

- Toastify loaded via PHP from `../admin/vendor/toastify.min.js` and `toastify.min.css`
- NOT bundled by Vite
