---
name: scss-modern
description: Modern SCSS with CSS custom properties. Use when writing styles for WordPress blocks and admin pages.
paths: "**/*.scss"
---

# Modern SCSS

## Conventions
- Use `@use` (not `@import`) for module inclusion
- CSS custom properties (`--var-name`) for all configurable values
- BEM-like naming: `.wr-sc-slot-card__title`, `.wr-sc-slot-card--active`
- Plugin prefix: `wr-sm-` (manager), `wr-sc-` (consumer)
- No `!important` — use specificity or CSS custom properties
- Responsive: mobile-first with `@media (min-width: ...)` or `clamp()`

## CSS Custom Properties Pattern
```scss
.wr-sc-slot-page {
    padding: var(--sp-pad, 0);
    background: var(--sp-bg, transparent);
    border-radius: var(--sp-radius, 12px);

    &__title {
        font-size: var(--sp-title-size, clamp(1.6rem, 4vw, 2.4rem));
        font-weight: var(--sp-title-weight, 750);
        color: var(--sp-title-color, #18181b);
    }
}
```

Properties are set as inline styles from PHP `build_css_vars()` method.

## File Organization
```
scss/
├── admin/admin.scss           # Admin page styles (cards, dialog, ripple)
└── blocks/
    ├── frontend.scss          # Entry point (@use all block styles)
    ├── slot-grid/style.scss   # Grid block
    ├── slot-detail/style.scss # Detail block
    └── slot-page/style.scss   # Page block
```

## Dialog + Ripple Loader
```scss
dialog {
    background: transparent;
    border: none;
    box-shadow: none;
    &::backdrop { backdrop-filter: blur(5px); }
}

.wr-ripple {
    // Expanding circle animation for loading states
}
```
