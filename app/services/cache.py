import json
from typing import Any

from config.redis import get_redis, redis_available
from config.settings import get_settings


class CacheService:
    @staticmethod
    def _key(namespace: str, key: str) -> str:
        return f"briocampus:{namespace}:{key}"

    @classmethod
    def get(cls, namespace: str, key: str) -> Any | None:
        if not redis_available():
            return None
        try:
            raw = get_redis().get(cls._key(namespace, key))
            if raw is None:
                return None
            return json.loads(raw)
        except Exception:
            return None

    @classmethod
    def set(cls, namespace: str, key: str, value: Any, ttl: int | None = None) -> None:
        if not redis_available():
            return
        settings = get_settings()
        try:
            get_redis().setex(
                cls._key(namespace, key),
                ttl or settings.CACHE_TTL_SECONDS,
                json.dumps(value, default=str),
            )
        except Exception:
            return

    @classmethod
    def delete(cls, namespace: str, key: str) -> None:
        if not redis_available():
            return
        try:
            get_redis().delete(cls._key(namespace, key))
        except Exception:
            return

    @classmethod
    def invalidate_namespace(cls, namespace: str) -> None:
        if not redis_available():
            return
        try:
            client = get_redis()
            pattern = cls._key(namespace, "*")
            for redis_key in client.scan_iter(match=pattern, count=100):
                client.delete(redis_key)
        except Exception:
            return

    @classmethod
    def set_otp(cls, email: str, code: str, purpose: str = "reset") -> None:
        settings = get_settings()
        ttl = settings.OTP_EXPIRE_MINUTES * 60
        cls.set("otp", f"{purpose}:{email.lower()}", {"code": code}, ttl=ttl)

    @classmethod
    def get_otp(cls, email: str, purpose: str = "reset") -> str | None:
        data = cls.get("otp", f"{purpose}:{email.lower()}")
        if not data:
            return None
        return data.get("code")

    @classmethod
    def clear_otp(cls, email: str, purpose: str = "reset") -> None:
        cls.delete("otp", f"{purpose}:{email.lower()}")
