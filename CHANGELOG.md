# v1.30.0 — Backoffice de contenidos + Importador ODS/CSV

## Añadido
- **Migración 037**: `colors`, `rarities`, `units`, `spells`, `items`, `heroes`, `resistances`.
- **Config** `content.php`: tipos, colores y rarezas base.
- **Librerías**: `ContentService` (CRUD) e `Importer` (CSV nativo; ODS/XLSX con PhpSpreadsheet si disponible).
- **AdminContent** (controlador + vistas Bootstrap) con **lista/crear/editar/borrar** e **importación** por tabla.
- **Rutas**: `/admin/content`, `/admin/content/import`, etc.


# v1.31.0 — i18n completo (EN/ES) + auditoría de claves

## Añadido
- **MY_Controller**: carga idioma desde sesión (`site_lang`), por defecto `english`.
- **Controlador** `Language::set/{lang}` para cambiar idioma (english|spanish).
- **Helper** `langx()` para fallback seguro.
- **Paquetes de idioma** EN/ES: `common`, `content`, `battle`, `war` (muestras).
- **AdminContent** y sus vistas migradas a `lang()`.
- **CLI** `Langcli::audit` para listar claves faltantes (usa `spanish` como referencia).

## Notas
- Añade más paquetes (market, auctions, alliances, security, backups…) siguiendo el mismo patrón.
- Las vistas deben usar `lang('…')` o `langx('…')` para cobertura completa.


# v1.32.0 — Email & recuperación de cuenta

## Añadido
- **Migración 038**: `password_resets` y `email_verifications`.
- **Config** `email.php` con SMTP/From/Base URL.
- **EmailService**: envío de `sendPasswordReset()` y `sendEmailVerification()`.
- **Auth**:
  - `request_reset` (GET/POST) → solicita token y envía correo.
  - `reset/{token}` (GET) y `reset_submit` (POST) → establece nueva contraseña (BCRYPT).
  - `email/verify/{token}` → marca email verificado.
- **Vistas**: formularios de recuperación y plantillas de email.
- **Rutas** actualizadas.

## Notas
- Si tu tabla `users` usa otra columna de contraseña, se intenta `password_hash` y si no, `password`.
- Configura `application/config/email.php` con tus credenciales SMTP.


# v1.33.0 — Anti-trampas (reglas) + sanciones + hook de sesión

## Añadido
- **Migración 039**: `session_log`, `anticheat_events`, `sanctions`, `transfers_log`.
- **Config** `anticheat.php`: umbrales de multi-cuenta por IP y límites de transferencias por pareja/24h.
- **AntiCheatService**:
  - `logSession()` + `detectMultiAccount()` (evento `multi_ip` cuando supera el umbral).
  - `logTransfer()` + `checkTransferLimits()` (evento `transfer_limit` por exceso).
  - `assertAllowed(user, action)` para bloquear acciones (p. ej., `mute_market`).
  - `imposeSanction()` / `revokeSanction()`.
- **Hook** `AntiCheatHook` en `pre_controller` para registrar IP/UA de usuarios logueados (requiere `config['enable_hooks']=TRUE`).
- **Admin** `AntiCheatAdmin`: panel de eventos y sanciones, con imposición/revocación.
- **Rutas**: `/admin/anticheat/*`.

## Notas
- Integra `assertAllowed()` en puntos sensibles: mercado, subastas, alianzas, chat.
- Llama a `logTransfer()` desde los módulos de comercio para activar límites automáticos.


# v1.34.0 — Observabilidad & Auditoría

## Añadido
- **Migración 040**: `audit_log`, `metrics_counters`, `app_events`.
- **AuditService**: `log(action, userId, realmId, meta)` y `recent()`.
- **MetricsService**: `inc(key, amount)` y series/Top (día).
- **Hook** `MetricsHook` (`post_controller`): cuenta requests por controlador/método (`http.{Controller}.{method}`).
- **Admin** `ObservabilityAdmin` + vista `observability_dashboard` con Top de métricas del día y auditoría reciente.
- **Rutas**: `/admin/observability`, `/admin/observability/metrics_json`.

## Notas
- Usa `AuditService::log(...)` en acciones sensibles (login, comercio, alianzas, batallas).
- Las métricas se guardan por día (`YYYYMMDD`) para rapidez y bajo costo.


# v1.35.0 — Notificaciones in‑app

## Añadido
- **Migración 041**: `notifications` (por usuario, con `read_at`).
- **NotificationService**: enviar/listar/contar/leer notificaciones.
- **Controlador** `Notifications`: centro de notificaciones, JSON de listado y badge de no leídas; marcar leído/todo leído.
- **Vistas**: `notifications/center.php` (Bootstrap).
- **Integración**: si existe `BattleService::finalize`, se inyectan notificaciones a atacante/defensor.
- **Rutas**: `/notifications*`.


# v1.36.0 — DevOps & despliegue (Docker/Compose + seeds + CI)

## Añadido
- **Docker/Compose**: Nginx + PHP-FPM 8.2 + MySQL 8 + Redis, con `docker/docker-compose.yml` y `Dockerfile` PHP.
- **Nginx**: `default.conf` para front controller `index.php`.
- **.env.example** variables básicas.
- **Scripts**: `scripts/seed.sql` (usuarios/realms mínimos), `backup_db.sh`, `wait-for-db.sh`.
- **CI**: workflow GitHub Actions para **lint** y Composer install si existe.


# v1.37.0 — QA: Golden tests & E2E

## Añadido
- **Golden tests (HTTP)**: fixtures JSON y `tests/golden/run_http.php` que llama a `/battle/finalize` y verifica resultados.
- **PHPUnit** (smoke) con `phpunit.xml` y `SampleSmokeTest`.
- **Cypress** scaffold opcional (`cypress.config.js`, `e2e/smoke.cy.js`).
- **Workflow** `tests.yml` ejecuta PHPUnit y golden runner en CI.

## Notas
- Los golden dependen de que `/battle/finalize` esté operativo (motor de combate listo).
- Ajusta fixtures a tu *content pack* real (NP, stacks, resists).


# v1.38.0 — Privacidad/GDPR

## Añadido
- **GdprService**: `exportUserData(userId)` (users, realms, notifications, eventos anti-cheat, sanciones, batallas relacionadas) y `anonymizeUser(userId)`.
- **Privacy controller**: `/privacy`, `/terms`, **exportación JSON** (`/privacy/export`) y **borrado** por anonimización (`/privacy/delete` → `/privacy/delete_confirm`).
- **Vistas**: páginas estáticas de Política de Privacidad y Términos; flujo de eliminación.
- **Rutas** actualizadas.

## Notas
- La anonimización conserva el historial de juego sin PII (desvincula reinos y enmascara email).
- Integra `AuditService::log()` en operaciones sensibles si quieres traza adicional.


# v1.39.0 — Pulido UX/UI

## Añadido
- **Layout** con parciales `header`, `alerts`, `footer` (Bootstrap 5) y **modo oscuro** con `localStorage`.
- **Navbar** con accesos a Contenido, Observabilidad, Notificaciones y Privacidad + **badge** de no leídas.
- **Assets**: `assets/css/app.css`, `assets/js/app.js` (toggle dark y polling del badge).
- **Home**: controlador y vista básicos.
- **Vistas** existentes envueltas con layout (S41/S45/S46/S49), sin tocar lógica de controladores.
- **Ruta** `default_controller = home` si no existía.


# v1.40.0 — i18n completo en vistas

## Añadido
- Paquetes EN/ES: `ui`, `auth`, `notifications`, `observability`, `privacy`, `anticheat`, `home`.
- `MY_Controller` carga `ui` además de `common`.
- Controladores cargan su paquete: Auth, Notifications, Privacy, ObservabilityAdmin, AntiCheatAdmin, Home.
- Vistas actualizadas para usar `lang()` en **todas** las secciones añadidas en S41–S50.
- Emails: sujetos y cuerpos via `auth_lang` (plantillas actualizadas).

## Notas
- Añade claves adicionales según amplíes módulos (market, auctions, alliances). Ejecuta `Langcli::audit` para detectar faltantes.
