---
name: security-first
description: Security-first development for WordPress. Use when reviewing or writing code to ensure proper input validation, output escaping, authentication, and authorization.
---

# Security First

## Input Validation (Backend)
```php
// Always: wp_unslash + sanitize
$url = esc_url_raw( wp_unslash( trim( $_POST['url'] ) ) );
$text = sanitize_text_field( wp_unslash( $_POST['text'] ) );
$number = absint( wp_unslash( $_POST['number'] ) );
$id = (int) sanitize_text_field( wp_unslash( $_GET['id'] ) );
```

## Output Escaping (Backend)
```php
echo esc_html( $title );                // Text content
echo esc_attr( $value );                // HTML attributes
echo esc_url( $link );                  // URLs in href/src
echo wp_kses_post( $description );      // Rich content (safe HTML)
echo wp_json_encode( $data );           // JSON in data-attributes
```

## Output Escaping (Frontend JS)
```typescript
function esc(str: string): string {
    const el = document.createElement('span');
    el.textContent = str || '';
    return el.innerHTML;
}
// Use esc() before insertAdjacentHTML or template literal interpolation
```

## Authentication
- Nonce verification: `wp_verify_nonce()` / `check_ajax_referer()`
- Capability check: `current_user_can( 'manage_options' )`
- REST API: `permission_callback` with `Authentication::validate_request()`
- 3-layer auth for cross-site: Bearer + HMAC + time-based nonce

## Authorization Order
1. Backend validates nonce
2. Backend checks capability
3. Backend sanitizes input
4. Backend processes
5. Backend escapes output
6. Frontend validates (client-side, for UX only — never trust)

## Common Pitfalls
- Never trust `$_GET`/`$_POST` without sanitization
- Never echo variables without escaping
- Never use `$wpdb->query()` with unescaped user input — use `$wpdb->prepare()`
- Never disable SSL verify in production (`ENVIRONMENT !== 'dev'`)
- Never store secrets in code — use `wp_generate_password()` + `wp_options`
- Rate limit all public endpoints
