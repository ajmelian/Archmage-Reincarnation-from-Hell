# v1.22.0 — Registro/Auditoría & Moderación

## Añadido
- **Tablas**:
  - `audit_log` (user/realm, acción, ip/ua, meta JSON, timestamp).
  - `mod_flags` (reportes de usuarios: tipo, target, razón, estado).
  - `mod_actions` (sanciones: mute_chat, suspend_market, ban_arena, warn).
- **Librerías**:
  - `AuditLog`: registro simple de eventos con contexto (user/realm, IP, UA, meta).
  - `ModerationService`: reportes, resolución y **sanciones** (+helpers `isMutedChat`, `canTrade`, `activeSanctions`). 
- **UI Moderación** (`/mod`):
  - Lista reportes pendientes y sanciones recientes; ver reporte y **resolver**; **aplicar sanción** (duración y motivo).
- **CLI** `Modcli`:
  - `flags` (listar pendientes), `sanction <modUserId> <realmId> <action> <minutes> [reason]`, `expire` (comprobación de expiradas).
- **API v1**:
  - `POST /api/v1/report` para que jugadores reporten (tipo, motivo, target opcional).
- **Enforcement**:
  - **Chat mute**: evita enviar mensaje si el reino está muteado.
  - **Mercado/Subastas**: bloqueados si el reino tiene `suspend_market` activo.

## Notas
- Determinista y sin IA. Compatible con CI3. Roles de moderación reutilizan `AdminService::requireAdmin()` (adaptar si defines rol específico).
- Las sanciones expiran por comparación de tiempo en consultas (no requiere job), pero se incluye `Modcli::expire` para auditoría programable.
