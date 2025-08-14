# v1.15.0 — Seguridad ampliada (2FA, sesiones 300s, RBAC)

## Añadido
- **2FA (TOTP)** opcional con secretos Base32, verificación y **URI otpauth**. Gestión en `/account/security`:
  - Activar (generar clave, confirmar con primer código) / Desactivar / **Códigos de respaldo** (8).
  - Login paso 2 (`/auth/second_factor`) si 2FA está activo.
- **Endurecimiento de sesiones** (CI3):
  - `sess_driver=database`, `sess_expiration=300`, `sess_time_to_update=120`, `sess_regenerate_destroy=TRUE`.
  - Regeneración en login y vinculación a User-Agent; headers de seguridad (HSTS, CSP, etc.).
- **Bloqueo y rate limiting de login** con `SecurityService`:
  - Contadores por IP y por usuario (reutiliza `rate_counters`).
  - `login_attempts` + `locked_until` en `users` (desbloqueo automático o vía `Seccli unlock`).
- **RBAC sencillo** (`roles`, `user_roles`) y librería `Acl` (grant/revoke/hasRole).

## Hooks/Config
- `application/config/security.php` con TOTP, login, sesión y headers.
- Hook `SecurityHeaders` para CSP/HSTS/etc (activado en `config/hooks.php`).

## Rutas
- `/auth/login`, `/auth/logout`, `/auth/second_factor`, `/account/security`.

## CLI
- `Seccli unlock <email>` / `Seccli show <email>`.

## Notas
- Determinista y **sin IA**. Ajusta las políticas en `security.php` según tu despliegue.
