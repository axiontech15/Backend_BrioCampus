from functools import lru_cache

import redis

from config.settings import get_settings


@lru_cache
def get_redis() -> redis.Redis:
    settings = get_settings()
    return redis.Redis.from_url(
        settings.REDIS_URL,
        decode_responses=True,
        socket_connect_timeout=2,
        socket_timeout=2,
    )


def redis_available() -> bool:
    try:
        client = get_redis()
        return bool(client.ping())
    except Exception:
        return False
