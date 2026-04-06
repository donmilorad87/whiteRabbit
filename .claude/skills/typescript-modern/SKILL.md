---
name: typescript-modern
description: Modern TypeScript with strict mode for WordPress plugin frontends. Use when writing or reviewing TypeScript files.
paths: "**/*.ts,**/tsconfig.json"
---

# Modern TypeScript

## tsconfig.json
```json
{
  "compilerOptions": {
    "target": "ES2022",
    "module": "ESNext",
    "moduleResolution": "bundler",
    "strict": true,
    "noEmit": true,
    "esModuleInterop": true,
    "skipLibCheck": true,
    "forceConsistentCasingInFileNames": true,
    "types": ["vitest/globals"]
  },
  "include": ["js/**/*.ts", "__tests__/**/*.ts", "types/**/*.d.ts"]
}
```

## Type Declarations
- WordPress globals in `types/wordpress.d.ts`
- Declare: `wp.blocks`, `wp.element`, `wp.data`, `wp.i18n`, `wp.blockEditor`, `wp.components`
- Declare: `Toastify`, `wrSmAdmin`/`wrScAdmin` globals
- Shared interfaces: `SlotData`, `CardBuilderConfig`, `WrSmAdmin`, `WrScAdmin`

## Patterns
```typescript
// Typed class properties
export class FetchHandler {
    public dialog: HTMLDialogElement;
    constructor( dialog: HTMLDialogElement ) { this.dialog = dialog; }
    fetch( data: FormData, callback: ((data: Record<string, unknown>) => void) | null = null ): void { }
}

// Interface for config
export interface CardBuilderConfig {
    linkMode: string;
    detailUrl: string;
    showBtn: boolean;
}

// DOM type assertions
const form = document.getElementById('my-form') as HTMLFormElement | null;
const dialog = document.getElementById('dialog') as HTMLDialogElement;
```

## Rules
- All source files are `.ts` — Vite transpiles natively
- Import paths use `.ts` extension: `import { FetchHandler } from './FetchHandler.ts'`
- No `any` unless interfacing with WordPress globals (use `as any` sparingly)
- Prefer interfaces over type aliases for object shapes
