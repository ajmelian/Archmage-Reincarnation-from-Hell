# v1.14.0 — Observabilidad (métricas + /metrics + panel)

## Añadido
- **Tablas de métricas**:
  - `metrics_counter` (contadores por ventana)
  - `metrics_summary` (duraciones: count/sum/min/max por ventana)
- **Librería** `Observability`:
  - `inc(name, labels, delta)` y `observe(name, ms, labels)`
  - `beginRequest(name, labels)` / `endRequest(status)` para instrumentar peticiones
  - Export **Prometheus** en texto con `/metrics`
  - `cleanup()` para purga por retención
- **Instrumentación automática**:
  - `MY_ApiController` (toda la API): **conteo** y **duración** por endpoint y status
  - `MY_Controller` (páginas HTML): igual que API
- **Panel Ops** `/ops/metrics` con Top endpoints HTML/API (última hora)
- **CLI** `Obscli::cleanup` para compactar

## Notas
- Determinista y **sin IA**. Compatible con Prometheus/Graphite vía `/metrics`.
- Ajustes en `application/config/observability.php`: ventana, retención y namespace Prom.
