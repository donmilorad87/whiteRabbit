---
name: vite-build
description: Vite build configuration for WordPress plugins. Use when configuring Vite, adding entry points, or troubleshooting build issues. Produces modern JS (ES2022) and CSS.
paths: "**/vite.config.*,**/assets/src/**"
---

# Vite Build System

## Project Structure
```
assets/
├── src/                       # Vite project root
│   ├── vite.config.js         # Build config
│   ├── tsconfig.json          # TypeScript config
│   ├── package.json           # Dependencies (vite, sass, terser, typescript, vitest, jsdom)
│   ├── js/                    # TypeScript source
│   │   ├── admin/             # Admin JS classes
│   │   ├── editor/            # Gutenberg block JS
│   │   └── blocks/            # Frontend block JS
│   ├── scss/                  # SCSS source
│   ├── types/                 # TypeScript declarations (wordpress.d.ts)
│   └── __tests__/             # Vitest tests
├── admin/                     # Built admin assets
│   ├── js/
│   ├── css/
│   └── vendor/                # Toastify, etc.
├── editor/                    # Built editor assets (manager only)
│   ├── js/
│   └── css/
└── blocks/                    # Built block assets (consumer only)
    ├── js/
    └── css/
```

## Vite Config Pattern
```js
import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
  build: {
    outDir: path.resolve(__dirname, '..'),  // outputs to assets/
    emptyOutDir: false,
    minify: 'terser',
    terserOptions: { mangle: { reserved: ['_', '$'] } },  // Underscore.js protection
    rollupOptions: {
      input: {
        'admin/js/admin': path.resolve(__dirname, 'js/admin/adminApp.ts'),
        'blocks/js/slot-grid': path.resolve(__dirname, 'js/blocks/slot-grid/index.ts'),
      },
      output: {
        entryFileNames: '[name].js',
        assetFileNames: (info) => { /* route CSS to correct folder */ },
      },
      external: [/^@wordpress\//],  // WP packages provided by WordPress
    },
  },
  test: {
    environment: 'jsdom',
    include: ['__tests__/**/*.test.ts'],
    globals: true,
  },
});
```

## Commands
```bash
cd assets/src
npm install          # Install deps
npm run build        # Production build
npm run dev          # Dev server with HMR
npm test             # Vitest
npm run test:watch   # Vitest watch mode
```

## Key Rules
- Target: `ES2022` (tsconfig) — modern output
- Terser reserves `_` and `$` to prevent Underscore.js collision
- WordPress `@wordpress/*` packages externalized (not bundled)
- SCSS preprocessed automatically by Vite
- Toastify loaded as vendor file via PHP `wp_enqueue_script`, NOT bundled
