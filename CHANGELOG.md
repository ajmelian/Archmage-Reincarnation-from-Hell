# v1.17.0 — Rendimiento & Caching

## Añadido
- **Índices** en tablas críticas: `chat_messages(channel_id,id)`, `dm_messages(to_realm_id,is_read,id)`, `arena_matches(created_at)`, `research_queue(realm_id,finish_at)`, `rate_counters(action,window_start)`.
- **Caching**:
  - Librería `Caching` (get/set/remember + **tags** simples) y helper `fragment_cache()` para vistas.
  - **Microcaché API** en `V1` (`me`, `wallet`, `buildings`, `research`, `arena_leaderboard`, `arena_history`) con TTLs configurables.
  - **ETag/If-None-Match** y `Cache-Control` (GET) en `MY_ApiController` + `Vary: Authorization`.
- **CLI** `Cachecli`:
  - `clear_tag <tag>` para invalidación por etiqueta.
  - `warm` para precalentar defs, leaderboard y wallets.
- **Panel Ops** `/ops/cache` con estado de driver y TTLs.

## Config
- `cache_ext.php`: driver, prefijo y TTL por defecto.
- `performance.php`: TTLs por endpoint API.

## Notas
- Totalmente determinista, sin IA. Mejora latencias y reduce carga de DB/CPU.
