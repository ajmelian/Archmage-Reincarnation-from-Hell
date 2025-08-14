# v1.21.0 — Alianzas (guilds) & chat de alianza

## Añadido
- **BD**:
  - `alliances` (id, name, tag, description, created_at)
  - `alliance_members` (alliance_id, realm_id, role, joined_at)
  - `alliance_invites` (from/to, status, expiración)
  - `alliance_logs` (auditoría mínima)
  - `realms.alliance_id` (enlace al clan/alianza)
- **AllianceService**: crear, invitar, revocar, aceptar, salir (con **disband** si último), promover/degradar, transferir liderazgo y expulsar. `chatChannelId(aid)` devuelve el canal para el chat de alianza (reusa `chat_messages.channel_id`).
- **UI** (`/alliances`): ver/crear alianza, ver miembros, invitar, aceptar invitaciones, salir; link directo a **Chat de alianza** (via `?channel=ally_{id}`).
- **API v1**: `GET /api/v1/alliance/me` y POSTs para `create`, `invite`, `accept`, `leave`, `promote`, `demote`, `kick` (requieren scope `write` donde corresponde).
- **CLI** `Alliancecli`: `create`, `invite`, `accept` para pruebas rápidas.

## Notas
- Determinista y sin IA. Roles: **leader**, **officer**, **member**. Líder no puede salir sin transferir o disolver.
- El módulo asume que el Chat soporta un `channel` parametrizable. El canal de alianza recomendado es `ally_{alliance_id}`.
