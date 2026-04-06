# WR Slot Manager -- Frontend Build (Vite + TypeScript)

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
│   ├── admin/                 Admin pages (TypeScript)
│   │   ├── adminApp.ts        Entry point (tab switching + form init)
│   │   └── classes/
│   │       ├── FetchHandler.ts    AJAX with dialog + Toastify
│   │       ├── ApiKeyForm.ts      API key generation form
│   │       ├── ConnectedSitesForm.ts  Site CRUD forms
│   │       └── SettingsForm.ts    Cache settings form
│   └── editor/
│       └── slotFields.ts      Gutenberg block (slot meta editor)
├── scss/
│   ├── admin/admin.scss       Admin styles (cards, ripple, dialog)
│   └── editor/slotFields.scss Block editor styles
├── types/
│   └── wordpress.d.ts         WP global type declarations
├── __tests__/
│   └── FetchHandler.test.ts   Vitest unit tests
├── tsconfig.json              strict: true, target: ES2022
├── vite.config.js             IIFE output, Terser, ES2022
└── package.json
```

## Build Output

Vite builds to two separate output directories (IIFE format to prevent variable collisions):

```
../admin/js/admin.js       ../admin/css/admin.css
../editor/js/slotFields.js ../editor/css/slotFields.css
```

## Vendor Files

Toastify is loaded as a vendor file via PHP `wp_enqueue_script` from `../admin/vendor/` -- it is not bundled by Vite.
