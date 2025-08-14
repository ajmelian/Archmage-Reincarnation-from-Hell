# v1.10.0 — Mensajería & Chat (global/alianza + DMs)

## Añadido
- **Mensajes privados (DM)** 1:1 entre reinos: `dm_messages` con bandeja de **entrada** y **enviados**, lectura y borrado.
- **Chat** en tiempo (casi) real mediante **polling**:
  - Canales **globales** (`global`, `comercio`) y canal de **alianza** automático (si el reino pertenece a una).
  - `chat_channels`, `chat_messages`, `chat_members` (para futuros canales privados).
  - **UI** `/chat` con lista de canales, log y envío (AJAX); endpoint `poll` y `post`.
- **Servicio** `ChatService` con autorización (pertenencia a alianza para canal de alianza).
- **CLI** `Chatcli::cleanup` para eliminar mensajes antiguos según retención.

## Configuración
- `application/config/chat.php`: `max_len`, `poll_batch`, `retention_days`, `ui_poll_ms`.

## Notas
- Determinista y **sin IA**. Moderación avanzada/anti-abuso vendrá en **S22**.
