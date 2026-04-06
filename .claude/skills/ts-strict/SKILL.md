---
name: ts-strict
description: TypeScript strict mode enforcement. Use to ensure all TypeScript code has proper types, no any leaks, and follows the project's strict tsconfig.
paths: "**/*.ts"
---

# TypeScript Strict Mode

## tsconfig Enforcement
- `strict: true` — enables all strict checks
- `target: ES2022` — modern output
- `moduleResolution: bundler` — Vite-compatible
- `noEmit: true` — Vite handles transpilation

## Required Types
- All function parameters typed
- All function return types declared
- All class properties declared with types
- Interfaces for configuration objects
- No implicit `any` — use explicit `any` only for WordPress globals with `as any`

## WordPress Global Types (`types/wordpress.d.ts`)
```typescript
declare namespace wp {
    namespace blocks { function registerBlockType(name: string, settings: Record<string, any>): void; }
    namespace element { function createElement(type: any, props?: any, ...children: any[]): any; }
    namespace data { function useSelect<T>(selector: (select: any) => T, deps?: any[]): T; }
    namespace i18n { function __(text: string, domain?: string): string; }
}

declare interface SlotData {
    id: number; title: string; slug: string; description: string;
    star_rating: number; featured_image: string; provider_name: string;
    rtp: number; min_wager: number; max_wager: number;
}

declare function Toastify(options: { text: string; style?: Record<string, string>; duration?: number; }): { showToast(): void };
```

## DOM Types
```typescript
const dialog = document.getElementById('id') as HTMLDialogElement;
const form = document.getElementById('form') as HTMLFormElement | null;
const btn = document.querySelector<HTMLButtonElement>('.btn');
```
