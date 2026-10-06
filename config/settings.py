from functools import lru_cache

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(
        env_file=".env",
        env_file_encoding="utf-8",
        extra="ignore",
    )

    APP_NAME: str = "BrioCampus API"
    APP_ENV: str = "development"
    DEBUG: bool = True
    API_PREFIX: str = "/api"

    DATABASE_URL: str = "mysql+pymysql://root:root@localhost:3307/briocampus"

    REDIS_URL: str = "redis://localhost:6379/0"
    CACHE_TTL_SECONDS: int = 300

    JWT_SECRET: str = "change-me-briocampus-jwt-secret-key-32chars"
    JWT_ALGORITHM: str = "HS256"
    ACCESS_TOKEN_EXPIRE_MINUTES: int = 60
    REFRESH_TOKEN_EXPIRE_DAYS: int = 7

    OTP_EXPIRE_MINUTES: int = 10
    OTP_LENGTH: int = 6

    CORS_ORIGINS: str = (
        "http://localhost:5173,http://127.0.0.1:5173,"
        "http://localhost:3000,http://127.0.0.1:3000"
    )
    CORS_ALLOW_CREDENTIALS: bool = True

    LOG_RETENTION_DAYS: int = 90
    LOG_ARCHIVE_AFTER_DAYS: int = 30

    UPLOAD_DIR: str = "uploads/palmares"
    MAX_UPLOAD_SIZE_MB: int = 10
    ALLOWED_UPLOAD_MIME: str = (
        "text/csv,application/vnd.ms-excel,"
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,"
        "text/plain"
    )

    SMTP_HOST: str = ""
    SMTP_PORT: int = 587
    SMTP_USER: str = ""
    SMTP_PASSWORD: str = ""
    SMTP_FROM: str = "noreply@briocampus.local"
    SMTP_TLS: bool = True

    @property
    def cors_origins_list(self) -> list[str]:
        return [o.strip() for o in self.CORS_ORIGINS.split(",") if o.strip()]

    @property
    def allowed_upload_mime_list(self) -> list[str]:
        return [m.strip() for m in self.ALLOWED_UPLOAD_MIME.split(",") if m.strip()]


@lru_cache
def get_settings() -> Settings:
    return Settings()
