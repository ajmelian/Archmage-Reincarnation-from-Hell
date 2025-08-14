# v1.5.0 — Economía de turno (tick loop) + colas de construcción e investigación + efectos

## Añadido
- **Tick loop** determinista (CLI): `php public/index.php tickcli run [N]`
  - Producción por reino (oro, maná, investigación) usando `EconomyFormula` + bonos de talentos (si existen).
  - **Upkeep** opcional por unidad (si existe tabla `armies`).
  - Limpieza de **efectos activos** expirados.
- **Colas**
  - `building_queue` → aplica a `buildings` cuando `finish_at <= now`.
  - `research_queue` → sube `research_levels` al `level_target` al concluir.
- **Estructuras**
  - `buildings` (qty/level por reino y tipo).
  - `research_levels` por reino y tech.
  - `active_effects` para buffs/debuffs temporales.
  - `tick_state` para control de locking/contador de ticks.
- **Wallet** extendida con **`research`**.

## Configuración
- `application/config/tick.php`: `period_sec`, `batch_size`, `upkeep.*`.

## Notas
- 100% **sin IA**: todo basado en **reglas y datos**.
- Los productores/colas son genéricos; conecta tus UIs de edificios e investigación para **insertar** en `*_queue` y el tick hará el resto.
