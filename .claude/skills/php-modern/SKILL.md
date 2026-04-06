---
name: php-modern
description: Modern PHP 8.5 coding standards. Use when writing or reviewing PHP code — enforces typed parameters, return types, union types, and modern syntax.
---

# Modern PHP 8.5

## Type System
- Every method MUST have typed parameters and return types
- Use `void` for methods returning nothing
- Use `array`, `string`, `int`, `float`, `bool` for simple returns
- Use `static` for singleton `get_instance()`
- Use union types: `int|\WP_Error`, `true|\WP_Error`, `string|false`
- Use `mixed` for generic returns (e.g., `get_option`)
- Use `?Type` for nullable (not `Type|null`)
- WordPress types: `\WP_REST_Request`, `\WP_REST_Response`, `\WP_Post`, `\WP_Error`
- Constructors have no return type

## Class Design
- Namespaced (`WiseRabbit\SlotManager\`, `WiseRabbit\SlotConsumer\`)
- One class per file
- Traits for shared behavior (LoggerTrait, OptionPrefixTrait, TemplateLoaderTrait)
- Private properties with typed declarations
- Readonly properties where appropriate (PHP 8.1+)

## Patterns
```php
public function get_slots(): array { }
public function sync( string $source = 'manual' ): int|\WP_Error { }
public static function validate_request( \WP_REST_Request $request ): bool { }
private function sanitize_site_url( string $url ): string|false { }
public static function check(): true|\WP_Error { }
```

## Avoid
- No dynamic properties without `#[AllowDynamicProperties]`
- No `${var}` string interpolation (deprecated 8.2)
- No implicit nullable types
- No `utf8_encode`/`utf8_decode` (deprecated 8.2)
