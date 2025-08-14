# v1.13.0 — API pública v1 (JSON, Bearer)

## Añadido
- **Autenticación** por **Bearer token**:
  - `api_tokens` (hash SHA-256, scopes, expiración, revoke).
  - `ApiAuth` (mint/validate/revoke + scopes).
  - Endpoint `POST /api/auth/token` y **CLI** `Apicli` (mint/list/revoke).
- **Base API** `MY_ApiController`:
  - CORS configurable, JSON helpers y **rate limit** por token (120 req/60s, configurable).
- **Endpoints v1** (solo JSON):
  - `GET /api/v1/me`, `GET /api/v1/wallet`, `GET /api/v1/buildings`.
  - `GET /api/v1/research`, `POST /api/v1/research/queue` (scope `write`).
  - `GET /api/v1/arena/leaderboard`, `GET /api/v1/arena/history`.
  - `POST /api/v1/arena/queue` y `/api/v1/arena/cancel` (scope `arena`).
  - `POST /api/v1/battle/simulate` (usa `Engine::duel`).
- **Docs** simples en `/api/docs` (Bootstrap).

## Notas
- Determinista y **sin IA**.
- Ajusta CORS en `application/config/api.php` para producción.
