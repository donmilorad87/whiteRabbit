---
name: wp-plugin-dev
description: WordPress plugin development following VIP standards. Use when creating or modifying WordPress plugins — handles post types, meta, admin pages, AJAX, REST API, caching, webhooks.
---

# WordPress Plugin Development

## Plugin Structure (VIP-aligned)
```
my-plugin/
├── my-plugin.php          # Thin bootstrap: header + constants + require plugin.php
├── plugin.php             # Autoloader + hooks + i18n + init
├── activate.php           # Activation callback
├── deactivate.php         # Deactivation callback
├── uninstall.php          # Cleanup on deletion
├── includes/              # PHP classes (class-{name}.php)
├── assets/
│   ├── src/               # Vite + TypeScript source
│   ├── admin/             # Built admin assets + vendor
│   ├── editor/            # Built editor assets
│   └── blocks/            # Built block assets
├── templates/             # PHP templates
├── languages/             # .pot/.po/.mo/.json
├── tests/php/             # PHPUnit
├── composer.json
└── phpunit.xml
```

## Key Patterns
- Custom Post Types: `register_post_type()` in a dedicated class
- Meta Fields: `register_post_meta()` with `show_in_rest: true`
- Admin Pages: `add_menu_page()` / `add_submenu_page()` with AJAX handlers
- AJAX: `wp_ajax_{action}` hooks, `wp_send_json_success/error()`, nonce + capability checks
- REST API: `register_rest_route()` with `permission_callback`
- Cache: `wp_cache_set/get` (Redis) or `set_transient/get_transient`
- Webhooks: Queue in `wp_options`, send via `wp_remote_post()`
- Templates: `TemplateLoaderTrait::load_template()` with `compact()` for vars

## Admin AJAX Pattern
```php
// PHP: handle_ajax_save()
check_ajax_referer( 'nonce_action', 'nonce' );
if ( ! current_user_can( 'manage_options' ) ) {
    wp_send_json_error( array( 'message' => 'Unauthorized.' ), 403 );
}
// ... save logic
wp_send_json_success( array( 'message' => 'Saved.' ) );

// JS: FetchHandler with dialog loading mask + Toastify
```

## Reference Plugins
- Manager: `primary_site/wp-content/plugins/wr-slot-manager/`
- Consumer: `secondary_site/wp-content/plugins/wr-slot-consumer/`
