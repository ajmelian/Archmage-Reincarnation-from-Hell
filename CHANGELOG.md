# v1.19.0 — Economía avanzada & balanceo

## Añadido
- **Tablas**:
  - `econ_params` (clave/valor de balanceo con timestamps).
  - `econ_modifiers` (modificadores globales o por reino; multiplicadores/aditivos; expiración).
  - `economy_history` (registro por tick: bruto, upkeep, mods y neto).
- **EconomyService**:
  - `preview(realmId)` calcula producción por tick con **rendimientos decrecientes** (`out = max*(1-e^{-k x})`), **anti-snowball** por percentil (bonus bottom y penalización top), **upkeep** por unidades y límites **cap per tick**.
  - `tick(realmId)` aplica el resultado a la **Wallet** y registra `economy_history`.
  - `tickAll(limit)` procesa en lote.
  - `setParam(key, val)` para ajustes en caliente.
- **CLI** `Econcli`:
  - `show_params`, `set <key> <value>`.
  - `mod_add <realm|global> <key> <value> [minutes] [reason]` / `mod_del <id>`.
  - `simulate <realmId>`, `tick_one <realmId>`, `tick_all [limit]`.
- **Admin UI** `/admin/economy_balance` (lectura/edición de parámetros, listado de modificadores).
- **API v1**:
  - `GET /api/v1/economy/preview` (producción por tick del reino actual).
  - `GET /api/v1/economy/params` (parámetros de economía).

## Notas
- Totalmente determinista y sin IA. Fórmulas y límites en `econ_params` y `application/config/economy.php`.
- Integra con Observability: métrica `economy.tick`.
