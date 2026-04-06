---
name: react-blocks
description: React patterns within WordPress Gutenberg blocks. Use when working with block editor components, hooks, and WordPress data stores.
---

# React in Gutenberg

## Key Constraint
WordPress Gutenberg **requires** React function components with hooks. Class components are not supported for blocks. This is a framework requirement, not a choice.

## WordPress React API
```typescript
const { createElement: el, useEffect, useRef, useState, useCallback } = wp.element;
const { useSelect, useDispatch } = wp.data;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, TextControl, Button, Disabled } = wp.components;
const { __ } = wp.i18n;
const ServerSideRender = wp.serverSideRender;
```

## Hooks Used
- `useState` — local field state (not stored in block attributes while typing)
- `useCallback` — memoized handlers (save, image select)
- `useEffect` — keyboard shortcuts, focus management, data fetching
- `useRef` — DOM references, stable callback refs
- `useSelect` — read from WordPress data stores
- `useDispatch` — write to WordPress data stores (`editPost`, `savePost`)
- `useBlockProps` — required for all block wrappers

## Pattern: Local State → Save on Button Click
```typescript
const [title, setTitle] = useState(storeTitle);
const fieldsRef = useRef();
fieldsRef.current = { title };

const handleSave = useCallback(async () => {
    editPost({ title: fieldsRef.current.title, meta: { ... } });
    await savePost();
    createSuccessNotice(__('Saved.'), { type: 'snackbar' });
}, [editPost, savePost, createSuccessNotice]);
```

## Pattern: Tab Navigation (escape Gutenberg focus trap)
Gutenberg renders blocks in an iframe. To let Tab escape the block:
1. `stopPropagation` on Tab key inside the block
2. When at the last focusable element, find the next focusable in `document.body` (outside iframe)
3. Use `iframe.contentDocument.activeElement` to get the real focused element

## Pattern: apiFetch for Block Data
```typescript
wp.apiFetch({ path: '/wr-slot-consumer/v1/slot-list' }).then((list) => {
    setSlotOptions(list.map(s => ({ label: s.label, value: String(s.value) })));
});
```
