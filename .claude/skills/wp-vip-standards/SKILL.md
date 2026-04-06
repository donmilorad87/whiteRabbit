---
name: wp-vip-standards
description: WordPress VIP coding standards and conventions. Use when writing PHP for WordPress plugins or themes to ensure VIP compliance.
---

# WordPress VIP Standards

## Folder Structure
- `includes/` for PHP classes, `class-{name}.php` naming, `trait-{name}.php` for traits
- Directories: lowercase-hyphenated (`post-type/`, `slot-fields/`)
- `src/` for JS/SCSS build source only
- `assets/` for build output and vendor files
- `templates/` for PHP templates
- Main plugin file: thin bootstrap (header + constants + `require plugin.php`)
- `plugin.php`: autoloader + activation/deactivation hooks + i18n + init

## Autoloader Pattern
- Custom `spl_autoload_register` in `plugin.php`
- Converts PascalCase namespaces to lowercase-hyphenated paths
- `WiseRabbit\SlotManager\Admin\ApiKeyPage` → `includes/admin/class-api-key-page.php`
- Traits detected by `Traits` namespace segment, file prefix `trait-`

## Code Rules
- No raw `include`/`require` — use `TemplateLoaderTrait::load_template()` with path validation
- All `$_GET`/`$_POST`/`$_REQUEST` access: `wp_unslash()` + `sanitize_text_field()`
- All output: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`
- Nonce verification on every form handler
- Capability checks (`current_user_can()`) on every admin action
- No `extract()` without `EXTR_SKIP`
- `sslverify` controlled by `ENVIRONMENT` constant (off in dev, on in prod)
- Logging gated by `ENVIRONMENT === 'dev'`

## Reference
- Project: `/home/milner/Desktop/catTest/`
- Manager plugin: `primary_site/wp-content/plugins/wr-slot-manager/`
- Consumer plugin: `secondary_site/wp-content/plugins/wr-slot-consumer/`
