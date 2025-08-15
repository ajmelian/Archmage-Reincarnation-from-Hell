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
