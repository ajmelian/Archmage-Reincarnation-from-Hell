# v1.23.0 — Seguridad avanzada (CSP/HSTS, CSRF, rehash y 2FA)

## Añadido
- **Migration 032**: `users.twofa_secret`, `users.twofa_enabled`, `last_login_at`, `last_login_ip`.
- **Hook** `SecurityHeaders` + `application/config/security.php` con CSP y cabeceras seguras (HSTS, XFO, X-CTO, Referrer-Policy, Permissions-Policy).
- **2FA TOTP**: librería `TwoFA`, controlador `Twofa` y vistas (activar, verificar, desactivar).
- **Rehash** de contraseñas en el siguiente login (`Passwords::verifyAndRehash`). 
- **Config** de **CSRF** para entorno `production` (expira 300s).

## Cambios
- Rutas `/twofa/*` añadidas.
- Parche opcional comentado en `Auth.php` para integrar paso 2FA y rehash.

## Notas
- Ajusta la **CSP** según tus CDNs reales.
- La rotación de sesión se recomienda tras aprobar el 2FA (`session_regenerate_id(true)`).
