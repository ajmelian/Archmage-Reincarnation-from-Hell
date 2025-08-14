# v1.7.0 — Investigación (UI + colas) — determinista

## Añadido
- **Definiciones** `research_def` con `base_cost_*`, `time_sec`, `growth_rate`, `max_level` y **prerrequisitos** (JSON).
- **Servicio** `ResearchService`:
  - `quote()` calcula coste/tiempo desde nivel actual (incluye colas) hasta un `target_level`.
  - `queue()` descuenta **RP** (research), oro y maná, y encola con `finish_at` acumulado.
  - `cancel()` reembolsa según `config/research.php`.
  - `level()` devuelve el nivel efectivo (considera colas).
- **UI** `/research`:
  - Árbol con nivel actual, costes base y growth; formulario para fijar **nivel objetivo**.
  - **Cola** visible con opción **cancelar**.
- **Logs** `research_logs` (queue/cancel/finish).

## Integración
- El **Tick (S14)** ya procesa `research_queue → research_levels` y puede llamar `ResearchService::markFinished()` si se desea log adicional.

## Configuración
- `application/config/research.php`: `queue_cancel_refund` (0..1, por defecto 1.0).

## Notas
- 100% determinista, sin IA. RP proviene del tick (recurso `research` en `wallets`).
