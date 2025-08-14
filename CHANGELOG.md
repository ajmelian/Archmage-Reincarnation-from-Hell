# v1.6.0 — Gestión de Edificios (UI completa) + Colas

## Añadido
- **Definiciones de edificios** (`building_def`) con coste base oro/maná, tiempo y crecimiento geométrico por unidad.
- **Servicio** `BuildingService`: `quote`, `queue`, `cancel` (reembolso configurable), `demolish` (reembolso parcial opcional).
- **UI** `/buildings`:
  - Lista defs con descripción, poseído, costes base y growth.
  - Formularios para **añadir a cola** y **demoler**.
  - **Cola** visible con acción **cancelar** y fecha de finalización.
- **Logs** `building_logs` para auditoría.

## Integración
- El **Tick (S14)** aplica `building_queue → buildings` automáticamente al llegar `finish_at`.
- Costes deducidos de **cartera** (oro/maná) al encolar; cancelación devuelve según `queue_cancel_refund`.

## Configuración
- `application/config/buildings.php`:
  - `demolish_refund_rate` (0..1)
  - `queue_cancel_refund` (0..1)

## Notas
- Determinista y **sin IA**.
- Ajusta `growth_rate` y costes en `building_def` a tu diseño real; el seed incluido es un ejemplo mínimo.
