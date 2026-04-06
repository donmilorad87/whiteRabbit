---
name: wp-rest-api
description: WordPress REST API with 3-layer authentication. Use when creating or consuming REST endpoints between WordPress sites.
---

# WordPress REST API

## Endpoints

### Manager (primary)
- `GET /wr-slot-manager/v1/slots` — returns all cached slots
- Uses `?rest_route=` format to bypass permalink issues in Docker

### Consumer (secondary)
- `POST /wr-slot-consumer/v1/webhook` — receives slot create/update/delete
- `GET /wr-slot-consumer/v1/slot-list` — simplified list for editor dropdown (requires `edit_posts` capability)

## 3-Layer Authentication (AuthSigner)

### Headers (outgoing)
```
Authorization: Bearer <api_key>
X-Signature: HMAC-SHA256(base64(api_key:consumer_url), api_key)
X-Auth-Nonce: HMAC-SHA256(base64(api_key:hmac):time_window, api_key)
X-Origin: <consumer_site_url>
```

### Validation (incoming)
1. Extract Bearer token → compare with stored key
2. Read `X-Origin` → validate against allowed URLs list
3. Recompute HMAC using stored key + X-Origin → compare with X-Signature
4. Recompute nonce (current + previous 5-min window) → compare with X-Auth-Nonce

### Permission Callback
```php
register_rest_route( 'namespace/v1', '/endpoint', array(
    'methods'             => \WP_REST_Server::READABLE,
    'callback'            => array( $this, 'handler' ),
    'permission_callback' => array( Authentication::class, 'validate_request' ),
) );
```

## Rate Limiter
- `RateLimiter::check()` in callback (not permission_callback)
- Transient-based per IP: `wr_sm_rl_{md5(ip)}`
- Configurable limit via admin Settings page
- Returns 429 when exceeded

## Webhook Payload
```json
{
    "action": "create|update|delete",
    "timestamp": "ISO-8601",
    "slot": { "id": 42, "title": "...", ... },
    "total_count": 28
}
```
Consumer checks `total_count` — if local count differs, triggers full resync.
