# v1.9.0 — Arena PvP (matchmaking + ladder ELO) — sin torneos ni mapa

## Añadido
- **Temporadas de Arena** (`arena_seasons`) activas con rango de fechas.
- **Ratings por temporada** (`arena_ratings`) con ELO y W/L/D.
- **Cola de emparejamiento** (`arena_queue`) con rango de búsqueda expandible.
- **Partidas** (`arena_matches`) resueltas automáticamente al emparejar, usando `Engine::duel()` y bonos compilados.
- **Servicio** `ArenaService`:
  - `enqueue/dequeue`, `matchmake()` (busca rival por ELO, crea y resuelve match), `leaderboard()`, `history()`.
  - Recompensa opcional al ganador (oro/maná) configurable.
- **UI** `/arena`: rating propio, **historial**, **clasificación** top.
- **CLI** `Arenacli::matchmake [loops] [sleep]` para procesar la cola.

## Configuración
- `application/config/arena.php`: `k_factor`, `search_delta`, `search_expand_sec`, `expand_step`, `reward`.

## Notas
- Sin **torneos** ni **mapa/exploración/raids** (excluidos por decisión).
- Determinista y data-driven; si no existe `armies`/`unit_def`, se usa un stub basado en edificios para el combate.
