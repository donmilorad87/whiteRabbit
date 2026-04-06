---
name: wp-i18n
description: WordPress internationalization and localization. Use when adding translatable strings, creating translation files, or managing languages.
paths: "**/languages/**"
---

# WordPress i18n

## PHP Strings
```php
__( 'Settings', 'wr-slot-manager' )           // Return translated string
esc_html_e( 'Save Settings', 'wr-slot-consumer' )  // Echo escaped
esc_html__( 'Title', 'wr-slot-manager' )      // Return escaped
esc_attr_e( 'placeholder', 'wr-slot-manager' ) // Echo for attributes
sprintf( __( 'Synced %d slots.', 'wr-slot-consumer' ), $count ) // Formatted
```

## JS Strings (Gutenberg blocks)
```typescript
const { __ } = wp.i18n;
__( 'More Info Button', 'wr-slot-consumer' )
```

## Translation Files
```
languages/
├── wr-slot-consumer.pot                              # Template (English)
├── wr-slot-consumer-sr_RS.po                         # Serbian source
├── wr-slot-consumer-sr_RS.mo                         # Serbian compiled (PHP)
├── wr-slot-consumer-sr_RS-{md5hash}.json             # Serbian compiled (JS)
```

## Loading
```php
// In plugin.php
load_plugin_textdomain( 'wr-slot-consumer', false, dirname( plugin_basename( WR_SC_PLUGIN_FILE ) ) . '/languages' );

// For JS blocks — in block registration class
wp_set_script_translations( 'wr-sc-slot-grid-editor', 'wr-slot-consumer', WR_SC_PLUGIN_DIR . 'languages' );
```

## JSON for JS Blocks
Filename: `{domain}-{locale}-{md5 of script src path}.json`
```bash
echo -n "assets/blocks/js/slot-grid.js" | md5sum  # → 17067994e32190dd96d190f5b6498265
```

## Build .mo from .po
```bash
msgfmt -o languages/wr-slot-consumer-sr_RS.mo languages/wr-slot-consumer-sr_RS.po
```

## Current Language: Serbian Cyrillic (sr_RS)
- Set via: `wp site switch-language sr_RS --allow-root`
- All UI strings translated to Cyrillic
