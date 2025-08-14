# v1.1.0 — Progresión de héroes (XP/Niveles/Talentos) + Rarezas/Conjuntos de ítems + Tablas de Drop

## Añadido
- **Progresión de Héroes**
  - Tabla `hero_progress`: niveles, XP y **puntos de talento**.
  - Librería `HeroProgress`: ganar XP, subir de nivel y asignar puntos.
  - UI `/heroes`: listado con nivel/XP/puntos y asignación de talentos.
- **Talentos**
  - `talent_def`: talentos con `max_rank` y `effects` (JSON).
  - `hero_talents`: rangos por héroe/reino; asignación controlada (cap y puntos).
  - Librería `TalentTree`: obtención y **agregación de bonos** por talento.
- **Ítems avanzados**
  - `item_def` ahora soporta **`rarity`** (`common|uncommon|rare|epic|legendary`) y `set_id`.
  - `item_set_def`: definición de **sets** con `bonuses` por nº de piezas.
- **Botín**
  - `drop_table_def` / `drop_table_entry`: tablas de drop con pesos y cantidades.
  - `Loot`: ruleta ponderada y generación de botín; `drop_logs` para auditoría.

## Migraciones
- **010_heroes_progression**: crea `talent_def`, `hero_progress`, `hero_talents`, `item_set_def`, `drop_table_*`, `drop_logs` y amplía `item_def`.

## Rutas
- `/heroes` (panel de progreso) y `/heroes/allocate` (asignación de talentos).

## Notas
- Integra los **bonos agregados** de `TalentTree` en producción/combate según convenga (p. ej. en `Turn` o motores específicos).
- Define talentos y sets en `talent_def`/`item_set_def` (vía admin/import S7) para habilitar sinergias.
