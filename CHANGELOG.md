# v1.25.0 — Rendimiento & Caching

## Añadido
- **Migración 034**: índices en tablas críticas (`market_*`, `auctions*`, `alliances*`, `audit_log`, `mod_*`, `inventory`, `economy_history`, `users`, `realms`).
- **Config** `cache.php`: driver (file|apcu|redis), ruta/namespace y **TTLs** por módulo.
- **Librerías**:
  - `Caching`: abstracción de caché con **file**, **APCu** o **Redis** y limpieza por prefijo (Redis) / namespace (file).
  - `RateLimiter`: ventanas fijas por IP y scope.
- **Controladores**: Output cache selectivo en `Market::index` (60s), `Auctions::index` (60s), `Alliances::index` (120s).
- **API v1**: en `export()` añadidos **rate-limit** (60 req/min por IP) y cabeceras **ETag** + `Cache-Control: public, max-age=30`.
- **CLI** `Cachecli`: `purge <prefix>`, `warm_all` (placeholders).

## Notas
- Para purgado fino de vistas de salida usa TTLs cortos; para datos, usa `Caching::deleteByPrefix()` (pleno con Redis).
- Requiere permisos de escritura en `application/cache/archmage` (driver file).
- Determinista y sin IA.
