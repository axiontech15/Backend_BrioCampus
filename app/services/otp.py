import secrets
import string

from app.services.cache import CacheService
from app.services.email import EmailService
from config.settings import get_settings


class OtpService:
    @staticmethod
    def generate_code() -> str:
        length = get_settings().OTP_LENGTH
        alphabet = string.digits
        return "".join(secrets.choice(alphabet) for _ in range(length))

    @classmethod
    def issue(cls, email: str, purpose: str = "reset") -> str:
        code = cls.generate_code()
        CacheService.set_otp(email, code, purpose=purpose)
        EmailService.send_otp(email, code, purpose=purpose)
        return code

    @classmethod
    def verify(cls, email: str, code: str, purpose: str = "reset", consume: bool = True) -> bool:
        stored = CacheService.get_otp(email, purpose=purpose)
        if not stored or not secrets.compare_digest(stored, code.strip()):
            return False
        if consume:
            CacheService.clear_otp(email, purpose=purpose)
        return True
