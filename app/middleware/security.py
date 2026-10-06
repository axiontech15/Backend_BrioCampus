import re
from collections.abc import Callable

from starlette.middleware.base import BaseHTTPMiddleware
from starlette.requests import Request
from starlette.responses import JSONResponse, Response

# Patterns often used in XSS / injection probes in query strings and path.
_SUSPICIOUS_PATTERN = re.compile(
    r"(?i)(<\s*script|javascript:|onerror\s*=|onload\s*=|"
    r"union\s+select|drop\s+table|insert\s+into|;\s*--|"
    r"/\*|\*/|xp_cmdshell)",
)


class SecurityHeadersMiddleware(BaseHTTPMiddleware):
    """MIME sniffing, XSS, clickjacking and related response headers."""

    async def dispatch(self, request: Request, call_next: Callable) -> Response:
        if _is_suspicious(request):
            return JSONResponse(
                status_code=400,
                content={"detail": "Request rejected by security filter"},
            )

        response = await call_next(request)
        response.headers["X-Content-Type-Options"] = "nosniff"
        response.headers["X-XSS-Protection"] = "1; mode=block"
        response.headers["X-Frame-Options"] = "DENY"
        response.headers["Referrer-Policy"] = "no-referrer"
        response.headers["Permissions-Policy"] = "geolocation=(), microphone=(), camera=()"
        response.headers["Content-Security-Policy"] = (
            "default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'"
        )
        response.headers["Cross-Origin-Opener-Policy"] = "same-origin"
        response.headers["Cross-Origin-Resource-Policy"] = "same-origin"
        if "server" in response.headers:
            del response.headers["server"]
        return response


def _is_suspicious(request: Request) -> bool:
    target = f"{request.url.path}?{request.url.query}"
    return bool(_SUSPICIOUS_PATTERN.search(target))
