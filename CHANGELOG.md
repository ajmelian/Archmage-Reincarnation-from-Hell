# v1.12.0 — Panel Admin "Live Ops"

## Añadido
- **Flag de usuario admin** (`users.is_admin`) y **registro de acciones GM** (`gm_actions`).
- **Servicio** `AdminService` con:
  - Autorización `requireAdmin()`.
  - Gestión de **reports** (listar, resolver).
  - Gestión de **mutes** (listar, crear, eliminar).
  - Ajustes rápidos de **economía** (wallet add/spend) con log.
  - Lectura de **logs** (gm_actions, arena_logs, building_logs, research_logs, chat_messages).
  - Búsqueda de **usuarios** y **concesión/revocación** de admin.
- **Controlador/UI** `Admin` con vistas:
  - **Dashboard**, **Reports**, **Mutes**, **Economía**, **Logs**, **Usuarios** (Bootstrap).
- **CLI** `Admincli` para **grant/revoke admin** por email.

## Rutas
- `/admin` + subrutas para cada sección (reports, mutes, economy, logs, users).

## Notas
- Determinista y sin IA.
- Requiere que la sesión tenga `userId` y que en `users` el usuario tenga `is_admin=1`.
