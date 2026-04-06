---
name: wp-cli-ops
description: WP-CLI operations within Docker containers. Use for database operations, option management, post creation, plugin management, and debugging.
---

# WP-CLI Operations

## Docker Exec Pattern
```bash
docker exec primary_site-wordpress-1 wp <command> --allow-root
docker exec secondary_site-wordpress-1 wp <command> --allow-root
```

## Common Commands
```bash
# Options
wp option get wr_sm_api_key --allow-root
wp option update wr_sm_rate_limit 10 --allow-root

# Posts
wp post list --post_type=slot --fields=ID,post_title --allow-root
wp post create --post_type=page --post_title="Test" --post_status=publish --allow-root

# Config
wp config set ENVIRONMENT "'dev'" --raw --allow-root
wp config delete WR_THEME_ENABLED --allow-root

# Cache
wp cache flush --allow-root
wp transient delete --all --allow-root

# Cron
wp cron event list --allow-root
wp cron event run wr_sm_process_webhook_queue --allow-root

# Language
wp language core install sr_RS --allow-root
wp site switch-language sr_RS --allow-root

# Eval (run PHP inline)
wp eval '$sync = new WiseRabbit\SlotConsumer\Sync\SlotSyncManager(); echo $sync->sync();' --allow-root
```

## In build.sh
- Used for WordPress core install, plugin install/activate, theme install/activate
- Sets WP_CACHE, WP_REDIS_HOST constants
- Copies Redis object-cache.php drop-in

## In entrypoint.sh
- Syncs `ENVIRONMENT` constant into wp-config on every start
- Syncs `WR_THEME_ENABLED` into wp-config
- Waits for wp-config.php to exist before running
