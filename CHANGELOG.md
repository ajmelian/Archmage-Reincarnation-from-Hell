# v1.16.0 — I18N/L10N completo (multilenguaje + formatos locales)

## Añadido
- **Preferencia de idioma** por usuario (`users.locale`) y **timezone** opcional.
- **Config** `i18n.php`: idiomas soportados (es/en), default, fallback, cookie y autodetección.
- **LanguageService**:
  - Detección (query/session/cookie/usuario/Accept-Language), `set()` persistente.
  - `load()` con **fallback** automático y `line($key, $params)` con **pluralización** simple.
- **Helper** `t()` / `tp()` para traducir en vistas/controladores.
- **Format** (L10N): `dateTime()` con Intl si está disponible; `number()` con locale.
- **Controlador** `Lang::set/<code>` y **ruta** `/lang/set/{code}`.
- **Archivos de idioma** ampliados (`es`, `en`) con claves usadas en UI.

## Integración
- `MY_Controller` carga LanguageService/Format y helper i18n.
- Vistas **Chat**, **Mensajes** y **Auth** actualizadas para usar `t()` y `Format`.
- Añadido **selector de idioma** (botones) en la vista de Chat.

## Notas
- Determinista y sin IA. Puedes seguir migrando cadenas del resto de vistas de forma incremental.
