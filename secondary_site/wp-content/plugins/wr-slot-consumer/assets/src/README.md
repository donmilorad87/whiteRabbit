# WR Slot Consumer -- Frontend Build (Vite + TypeScript)

## Commands

```bash
npm install        # first time
npm run build      # production build
npm run dev        # dev server with HMR
npm test           # run Vitest unit tests
npm run test:watch # watch mode
```

## Structure

```
assets/src/
├── js/
│   ├── admin/                     Admin settings page (TypeScript)
│   │   ├── adminApp.ts            Entry point (tab switching + form init)
│   │   └── classes/
│   │       ├── FetchHandler.ts    AJAX with dialog + Toastify
│   │       ├── SettingsForm.ts    Settings form handler
│   │       └── SyncButton.ts     Manual sync trigger
│   └── blocks/
│       ├── slot-grid/
│       │   ├── index.ts           Block editor (90+ attributes)
│       │   ├── frontend.ts        Frontend entry (load-more + popup)
│       │   ├── SlotCardBuilder.ts Card HTML renderer
│       │   ├── SlotLoadMore.ts    Pagination handler
│       │   └── SlotPopup.ts       Native dialog popup
│       └── slot-detail/
│           └── index.ts           Block editor (dropdown selector + styling panels)
├── scss/
│   ├── admin/admin.scss           Admin styles
│   └── blocks/
│       ├── frontend.scss          Shared block styles entry
│       ├── slot-grid/style.scss   Grid frontend styles
│       ├── slot-grid/editor.scss  Grid editor styles
│       └── slot-detail/style.scss Detail block styles
├── types/
│   └── wordpress.d.ts             WP global + SlotData type declarations
├── __tests__/
│   ├── SlotCardBuilder.test.ts    Card rendering, XSS, stars
│   └── FetchHandler.test.ts       Dialog, toast, callbacks
├── tsconfig.json                  strict: true, target: ES2022
├── vite.config.js                 IIFE output, Terser, ES2022
└── package.json
```

## Build Output

Build output goes to sibling directories under `assets/`:

```
../admin/js/admin.js           ../admin/css/admin.css
../blocks/js/slot-grid.js      ../blocks/css/frontend.css
../blocks/js/slot-grid-frontend.js
../blocks/js/slot-detail.js
```

Additionally, `../blocks/css/slot-grid.css` is generated for grid-specific styles.

## Vendor Files

Toastify is loaded via PHP from `../admin/vendor/toastify.min.js` and `toastify.min.css`. It is NOT bundled by Vite.
