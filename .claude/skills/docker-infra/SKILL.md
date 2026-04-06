---
name: docker-infra
description: Docker infrastructure for the WiseRabbit WordPress system. Use when modifying Docker configs, compose files, Dockerfiles, entrypoints, or build scripts.
paths: "**/Docker/**,**/docker-compose.yml,**/.env,**/entrypoint.sh,**/build.sh"
---

# Docker Infrastructure

## Services Per Site

### Primary Site (port 80)
- **nginx**: reverse proxy
- **wordpress**: PHP 8.5 (wordpress:beta-php8.5-apache), WP-CLI, custom entrypoint
- **mysql**: MySQL 9
- **redis**: Redis 7 (custom Dockerfile with configurable memory/persistence)
- **phpmyadmin**: at /pma/

### Secondary Site (port 81)
- **nginx**: reverse proxy
- **wordpress**: PHP 8.5, WP-CLI, custom entrypoint
- **mysql**: MySQL 9 (port 3307)
- **phpmyadmin**: at /pma/

## Entrypoint (`Docker/wordpress/entrypoint.sh`)
Runs on every container start (not just first boot):
1. First boot: runs `build.sh` in background, creates marker
2. Every boot: syncs `ENVIRONMENT` (dev/prod) and `WR_THEME_ENABLED` from env into wp-config
3. Delegates to WordPress default entrypoint

## Build Script (`Docker/wordpress/build.sh`)
Runs once on first boot:
1. Waits for database
2. Installs WordPress via WP-CLI
3. Installs/activates plugins and themes
4. Sets up Redis object cache (primary only)
5. Fixes uploads permissions (`chown www-data:www-data`)

## Environment Variables (.env)
- `ENVIRONMENT=dev|prod` — controls logging and SSL verify
- `WR_THEME_ENABLED=true|false` — frontend theme toggle
- `REDIS_MAXMEMORY`, `REDIS_MAXMEMORY_POLICY`, `REDIS_LOGLEVEL`

## Cross-Container Communication
- `extra_hosts: ["sec.wiserabbit.com:host-gateway"]` — primary can reach secondary
- `extra_hosts: ["local.wiserabbit.com:host-gateway"]` — secondary can reach primary
