#!/bin/bash
set -e

BUILD_MARKER="/build_state/.built"
WP_PATH="/var/www/html"

if [ ! -f "$BUILD_MARKER" ]; then
    echo "[entrypoint] First run detected. Running build in background..."
    (
        /usr/local/bin/build.sh && touch "$BUILD_MARKER"
        echo "[entrypoint] Build complete. Marker created."
    ) &
else
    echo "[entrypoint] Build already completed. Starting normally."
fi

# Sync environment config into wp-config on every start.
(
    # Wait for wp-config.php to exist (build may still be running).
    retries=0
    while [ ! -f "$WP_PATH/wp-config.php" ] && [ "$retries" -lt 30 ]; do
        sleep 2
        retries=$((retries + 1))
    done

    if [ -f "$WP_PATH/wp-config.php" ]; then
        # Environment (dev/prod).
        if [ "${ENVIRONMENT}" = "dev" ]; then
            wp config set ENVIRONMENT "'dev'" --raw --path="$WP_PATH" --allow-root 2>/dev/null
            echo "[entrypoint] ENVIRONMENT = dev (logs on, SSL verify off)"
        else
            wp config set ENVIRONMENT "'prod'" --raw --path="$WP_PATH" --allow-root 2>/dev/null
            echo "[entrypoint] ENVIRONMENT = prod (logs off, SSL verify on)"
        fi

        # Theme toggle.
        if [ "${WR_THEME_ENABLED}" = "false" ]; then
            wp config set WR_THEME_ENABLED false --raw --path="$WP_PATH" --allow-root 2>/dev/null
            echo "[entrypoint] WR_THEME_ENABLED = false (API/backend only)"
        else
            wp config delete WR_THEME_ENABLED --path="$WP_PATH" --allow-root 2>/dev/null || true
            echo "[entrypoint] WR_THEME_ENABLED = true (theme active)"
        fi
    fi
) &

# Delegate to the original WordPress entrypoint
exec docker-entrypoint.sh "$@"
