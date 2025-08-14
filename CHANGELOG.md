# v1.11.0 — Moderación & Anti-abuso (chat/DM) — sin IA

## Añadido
- **Rate limits** (`rate_counters`) por ventana:
  - `chat_post` (p. ej. 5/10s), `dm_send` (10/min) configurables en `config/moderation.php`.
- **Mutes/Silencios** (`moderation_mutes`) por ámbito: `chat_global`, `chat_alliance`, `dm`, `all`.
- **Bloqueos (DM)** (`moderation_blocks`): bloquear/desbloquear otros reinos para evitar DMs.
- **Reportes** (`moderation_reports`) para mensajes de chat y DMs.
- **Filtros de contenido** (lista en DB + config) con modo **rechazo** u **ocultación** (asteriscos).
- **Servicio** `ModerationService`: verificación de mutes, rate limit, filtros, bloqueos, reportes; utilidades GM.

## Integración
- **ChatService** ahora aplica **rate limit**, **mutes** y **filtros** antes de publicar; añade checks de **bloqueos** y filtros a **DMs**.
- **UI**: botón **(reportar)** en cada mensaje del chat; botón **Reportar** en lectura de DM.
- **CLI** `Modcli`:
  - `cleanup` (expira mutes y limpia contadores antiguos)
  - `list_reports [status]`, `mute <realmId> [scope] [minutes] [reason]`, `resolve <reportId> [status] [resolution]`

## Configuración
- `application/config/moderation.php` define límites y comportamiento de filtros.
- Palabras prohibidas se pueden cargar en `moderation_badwords` (seed de ejemplo incluido).

## Notas
- Determinista y **sin IA**. Para una UI GM completa, añade un panel en S23 basado en `ModerationService`.
