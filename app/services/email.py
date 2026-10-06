import logging
import smtplib
from email.message import EmailMessage

from config.settings import get_settings

logger = logging.getLogger("briocampus.email")


class EmailService:
    @staticmethod
    def send(to: str, subject: str, body: str) -> bool:
        settings = get_settings()
        if not settings.SMTP_HOST:
            logger.info("SMTP not configured — email to=%s subject=%s body=%s", to, subject, body)
            return True

        msg = EmailMessage()
        msg["From"] = settings.SMTP_FROM
        msg["To"] = to
        msg["Subject"] = subject
        msg.set_content(body)

        try:
            with smtplib.SMTP(settings.SMTP_HOST, settings.SMTP_PORT, timeout=10) as smtp:
                if settings.SMTP_TLS:
                    smtp.starttls()
                if settings.SMTP_USER:
                    smtp.login(settings.SMTP_USER, settings.SMTP_PASSWORD)
                smtp.send_message(msg)
            return True
        except Exception:
            logger.exception("Failed to send email to %s", to)
            return False

    @classmethod
    def send_otp(cls, to: str, code: str, purpose: str = "password reset") -> bool:
        return cls.send(
            to=to,
            subject=f"BrioCampus — code OTP ({purpose})",
            body=(
                f"Votre code de vérification BrioCampus est : {code}\n"
                f"Il expire dans quelques minutes.\n"
                f"Si vous n'avez pas demandé ce code, ignorez cet e-mail."
            ),
        )
