FROM redis:7-alpine

COPY redis.conf /usr/local/etc/redis/redis.conf
COPY entrypoint.sh /usr/local/bin/redis-entrypoint.sh
RUN chmod +x /usr/local/bin/redis-entrypoint.sh

ENTRYPOINT ["redis-entrypoint.sh"]
