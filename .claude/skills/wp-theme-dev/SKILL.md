---
name: wp-theme-dev
description: WordPress theme development following VIP standards. Use when creating themes — handles template hierarchy, block themes, theme.json, patterns, and full site editing.
---

# WordPress Theme Development (VIP)

## Theme Types
- **Block Theme** (preferred): `theme.json` + HTML templates in `templates/` + `parts/`
- **Classic Theme**: `functions.php` + PHP template files

## Block Theme Structure
```
my-theme/
├── style.css              # Theme header (required)
├── theme.json             # Global settings, styles, typography, colors
├── functions.php          # Enqueues, hooks, customizations
├── templates/             # Block templates (index.html, single.html, page.html)
├── parts/                 # Template parts (header.html, footer.html)
├── patterns/              # Block patterns (PHP files returning pattern markup)
├── assets/
│   ├── src/               # Vite + TypeScript/SCSS source
│   ├── css/               # Built CSS
│   └── js/                # Built JS
└── languages/
```

## VIP Standards
- No direct database queries — use WP_Query, get_posts()
- No file writes — use transients or options API
- No `eval()`, `exec()`, `system()`, `passthru()`
- Escape all output: `esc_html()`, `esc_attr()`, `esc_url()`
- Sanitize all input: `sanitize_text_field()`, `absint()`, `esc_url_raw()`
- Prefix everything (functions, classes, hooks, options)
- Use `wp_enqueue_script/style()` — never raw `<script>` or `<link>` tags
- i18n: all strings translatable via `__()`, `_e()`, `esc_html_e()`

## theme.json
```json
{
  "$schema": "https://schemas.wp.org/trunk/theme.json",
  "version": 3,
  "settings": {
    "color": { "palette": [...] },
    "typography": { "fontFamilies": [...], "fontSizes": [...] },
    "spacing": { "units": ["px", "rem", "%"] }
  },
  "styles": {
    "color": { "background": "#fff", "text": "#1a1a1a" }
  }
}
```
