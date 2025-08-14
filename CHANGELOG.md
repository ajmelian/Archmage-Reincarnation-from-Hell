# v1.3.0 — Alianzas 2.0 y Diplomacia (Guerras/NAP/Pactos)

## Añadido
- **Alianzas** con roles `leader|officer|member`, invitaciones, registro de eventos y **banco de alianza** (oro/maná).
- **Diplomacia** entre alianzas: estados `neutral|nap|allied|war`, con términos (JSON), histórico y **war score** por lado.
- **War events** (batallas, raids, ajustes de score) anexados a la relación de diplomacia.
- **Servicios**:
  - `AllianceService`: creación, invitaciones, unión/salida, promoción, banco (deposit/withdraw), y gestión de diplomacia.
- **UI**:
  - `/alliances` (listado), `/alliances/create`, `/alliances/view/{id}` (miembros, banco, diplomacia, invitaciones).
- **Rutas** para declarar guerra, NAP, alianza y neutralidad.

## Migraciones
- **012_alliances_diplomacy**: `alliances`, `alliance_members`, `alliance_invites`, `alliance_bank`, `alliance_logs`, `diplomacy`, `war_events`.

## Notas
- La transferencia real de recursos al **banco** debe integrarse con tu economía (TODO marcado).
- `war_score_a/b` se actualiza con `AllianceService::addWarScore(diploId, side, delta, event)`.
- Considera restringir **frecuencia de cambios diplomáticos** con cooldowns en el servicio si lo requiere el diseño.
