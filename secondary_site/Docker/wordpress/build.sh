#!/bin/bash
set -e

WP_PATH="/var/www/html"
MAX_RETRIES=60

# --- WAIT FOR DATABASE ---

echo "[build.sh] Waiting for database connection..."
retries=0
until php -r "
\$host = explode(':', getenv('WORDPRESS_DB_HOST') ?: 'mysql:3306');
\$conn = @new mysqli(\$host[0], getenv('WORDPRESS_DB_USER'), getenv('WORDPRESS_DB_PASSWORD'), getenv('WORDPRESS_DB_NAME'), \$host[1] ?? 3306);
if (\$conn->connect_error) exit(1);
\$conn->close();
exit(0);
" 2>/dev/null; do
    retries=$((retries + 1))
    if [ "$retries" -ge "$MAX_RETRIES" ]; then
        echo "[build.sh] Database not reachable after ${MAX_RETRIES} attempts. Exiting."
        exit 1
    fi
    sleep 3
done
echo "[build.sh] Database is reachable."

# --- INSTALL WORDPRESS ---

if ! wp core is-installed --path="$WP_PATH" --allow-root 2>/dev/null; then
    echo "[build.sh] Installing WordPress as '${WORDPRESS_TITLE}'..."
    wp core install \
        --url="${WORDPRESS_URL}" \
        --title="${WORDPRESS_TITLE}" \
        --admin_user="${WORDPRESS_ADMIN_USER}" \
        --admin_password="${WORDPRESS_ADMIN_PASSWORD}" \
        --admin_email="${WORDPRESS_ADMIN_EMAIL}" \
        --skip-email \
        --path="$WP_PATH" \
        --allow-root
    echo "[build.sh] WordPress installed successfully."
else
    echo "[build.sh] WordPress is already installed. Skipping core install."
fi

# --- PLUGINS ---

# Install plugins
if [ -n "$INSTALL_PLUGINS" ]; then
    IFS=',' read -ra PLUGINS <<< "$INSTALL_PLUGINS"
    for plugin in "${PLUGINS[@]}"; do
        plugin=$(echo "$plugin" | xargs)
        if [ -n "$plugin" ]; then
            echo "[build.sh] Installing plugin: $plugin"
            wp plugin install "$plugin" --path="$WP_PATH" --allow-root || \
                echo "[build.sh] WARNING: Failed to install plugin: $plugin"
        fi
    done
fi

# Activate plugins
if [ -n "$ACTIVATE_PLUGINS" ]; then
    IFS=',' read -ra PLUGINS <<< "$ACTIVATE_PLUGINS"
    for plugin in "${PLUGINS[@]}"; do
        plugin=$(echo "$plugin" | xargs)
        if [ -n "$plugin" ]; then
            echo "[build.sh] Activating plugin: $plugin"
            wp plugin activate "$plugin" --path="$WP_PATH" --allow-root || \
                echo "[build.sh] WARNING: Failed to activate plugin: $plugin"
        fi
    done
fi

# --- THEMES ---

# Install themes
if [ -n "$INSTALL_THEMES" ]; then
    IFS=',' read -ra THEMES <<< "$INSTALL_THEMES"
    for theme in "${THEMES[@]}"; do
        theme=$(echo "$theme" | xargs)
        if [ -n "$theme" ]; then
            echo "[build.sh] Installing theme: $theme"
            wp theme install "$theme" --force --path="$WP_PATH" --allow-root || \
                echo "[build.sh] WARNING: Failed to install theme: $theme"
        fi
    done
fi

# Activate theme (only one theme can be active)
if [ -n "$ACTIVATE_THEMES" ]; then
    theme=$(echo "$ACTIVATE_THEMES" | xargs)
    echo "[build.sh] Activating theme: $theme"
    wp theme activate "$theme" --path="$WP_PATH" --allow-root || \
        echo "[build.sh] WARNING: Failed to activate theme: $theme"
fi

# --- UPLOADS PERMISSIONS ---

chown -R www-data:www-data "$WP_PATH/wp-content/uploads" 2>/dev/null || true
echo "[build.sh] Uploads directory permissions set."

echo "[build.sh] Build complete."
