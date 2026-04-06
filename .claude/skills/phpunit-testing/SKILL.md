---
name: phpunit-testing
description: PHPUnit testing for WordPress plugins. Use when writing or running PHP unit tests. Tests run standalone without WordPress.
paths: "**/tests/php/**,**/phpunit.xml"
---

# PHPUnit Testing

## Structure
```
tests/php/
├── bootstrap.php           # Stubs WP functions (WP_Error, WP_REST_Request, etc.)
├── test-auth-signer.php    # AuthSigner unit tests
├── test-webhook-payload.php # WebhookPayload tests
└── test-slot-post-type.php # SlotPostType business logic
```

## Bootstrap
Tests run **standalone** — no WordPress installation needed. The bootstrap:
- Defines constants (`WR_SM_PLUGIN_DIR`, `MINUTE_IN_SECONDS`, `ENVIRONMENT`, etc.)
- Loads Composer autoload (`vendor/autoload.php`)
- Registers the VIP autoloader for plugin classes
- Stubs: `WP_Error`, `WP_REST_Request`, `hash_equals`, `untrailingslashit`, `is_wp_error`

## Test Pattern
```php
use WiseRabbit\SlotManager\Api\AuthSigner;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class Test_AuthSigner extends TestCase {
    public function test_generate_hmac_returns_64_char_hex(): void {
        $hmac = AuthSigner::generate_hmac( 'key', 'url' );
        $this->assertMatchesRegularExpression( '/^[a-f0-9]{64}$/', $hmac );
    }
}
```

## Commands
```bash
composer install              # First time
vendor/bin/phpunit            # Run all tests
vendor/bin/phpunit tests/php/test-auth-signer.php  # Single file
```

## What to Test (standalone-testable)
- `AuthSigner` — HMAC, nonce, validate_request (all error branches)
- `WebhookPayload` — build, types, defaults
- `SlotPostType::force_private_status()` — pure array logic
- Any pure function without WP dependencies
