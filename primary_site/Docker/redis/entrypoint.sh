#!/bin/sh
set -e

CONF="/usr/local/etc/redis/redis.conf"

# Override config values from environment variables.
sed -i "s|^port .*|port ${REDIS_PORT:-6379}|" "$CONF"
sed -i "s|^maxmemory .*|maxmemory ${REDIS_MAXMEMORY:-128mb}|" "$CONF"
sed -i "s|^maxmemory-policy .*|maxmemory-policy ${REDIS_MAXMEMORY_POLICY:-allkeys-lru}|" "$CONF"
sed -i "s|^loglevel .*|loglevel ${REDIS_LOGLEVEL:-notice}|" "$CONF"

# Apply password if set.
if [ -n "${REDIS_PASSWORD}" ]; then
    echo "requirepass ${REDIS_PASSWORD}" >> "$CONF"
fi

exec redis-server "$CONF"
