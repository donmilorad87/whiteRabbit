---
name: gutenberg-blocks
description: Gutenberg block development with server-side rendering and inspector controls. Use when creating or modifying WordPress blocks.
paths: "**/block/**,**/blocks/**,**/block.json"
---

# Gutenberg Block Development

## Block Structure
```
includes/block/slot-grid-block/
├── block.json                      # Block metadata + attributes
└── class-slot-grid-block.php       # Registration + server-side render

assets/src/js/blocks/slot-grid/
├── index.ts                        # Editor UI (InspectorControls + ServerSideRender)
├── frontend.ts                     # Frontend JS (load-more, popup)
├── SlotCardBuilder.ts              # Card HTML builder
├── SlotLoadMore.ts                 # Pagination handler
└── SlotPopup.ts                    # Dialog popup handler
```

## block.json
```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "wr-slot-consumer/slot-grid",
    "title": "Slots Grid",
    "category": "widgets",
    "attributes": {
        "columns": { "type": "number", "default": 3 },
        "moreInfoLabel": { "type": "string", "default": "More Info" }
    },
    "textdomain": "wr-slot-consumer",
    "editorScript": "wr-sc-slot-grid-editor",
    "style": "wr-sc-slot-grid-style"
}
```

## Server-Side Render Pattern
```php
public function register(): void {
    wp_register_script( 'handle', $url, $deps, $ver, true );
    wp_set_script_translations( 'handle', 'textdomain', PLUGIN_DIR . 'languages' );
    register_block_type( __DIR__ . '/block.json', array(
        'render_callback' => array( $this, 'render' ),
    ) );
}

public function render( array $attributes ): string {
    $a = wp_parse_args( $attributes, self::DEFAULTS );
    return $this->render_template( 'templates/blocks/slot-grid.php', compact( ... ) );
}
```

## Editor Pattern (TypeScript)
```typescript
registerBlockType( 'wr-slot-consumer/slot-grid', {
    edit: function EditSlotGrid( props: any ) {
        const { attributes, setAttributes } = props;
        const blockProps = useBlockProps();

        return el( 'div', blockProps,
            el( InspectorControls, {},
                el( PanelBody, { title: __( 'Display', 'wr-slot-consumer' ) },
                    el( RangeControl, { label: __( 'Columns' ), value: attributes.columns, onChange: (v) => setAttributes({ columns: v }) })
                )
            ),
            el( Disabled, {},
                el( ServerSideRender, { block: 'wr-slot-consumer/slot-grid', attributes } )
            )
        );
    },
    save: () => null,  // Dynamic block — rendered on server
});
```

## Current Blocks
- **Slot Grid** (`slot-grid`): grid display with pagination, load-more, popup, 90+ styling attributes
- **Slot Detail** (`slot-detail`): single slot by ID or URL param
- **Slot Page** (`slot-page`): dedicated slot page with dropdown selector + full styling
