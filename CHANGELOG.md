# v0.7.0 — Combate avanzado + Battle Replay + API JWT (rate limited)

## Añadido
- **Combate avanzado**
  - **Targeting configurable**: `proportional`, `focus_low_hp`, `focus_high_hp` (configurable en `application/config/game.php`).
  - **Tipos de daño** por unidad (`physical` / `magical`) y **resistencias** por stack (`resist` JSON).
  - **Round log** en el resultado del motor para facilitar replays.
- **Battle Replay**
  - Ruta `/battle/{id}` con vista básica del log (`application/controllers/Battle.php` + `application/views/battle/view.php`).
- **API REST (JWT)**
  - `POST /api/login` → devuelve **JWT (HS256)**.
  - `GET /api/state` → estado del reino del usuario autenticado.
  - **Rate limiting**: 60 req/min por ruta/usuario (tabla `api_rate`).
  - Librería `Jwt.php` ligera (sin dependencias externas).
- **Simulador CLI**
  - `php public/index.php sim duel <atk> <def> <runs>` para pruebas de balance.

## Cambios
- `application/libraries/Engine.php`:
  - Integra `Formula` para **daño base** y **HP por defecto**.
  - Añade **selector de objetivo** según `game.php`.
  - Calcula **pérdidas por stack** aplicando resistencias.
  - Devuelve `rounds` (round log) junto al `log` plano.

## Migraciones
- **007_advanced_combat_api**
  - `unit_def`: columnas nuevas `type` (VARCHAR), `damage_type` (VARCHAR), `resist` (JSON), `speed` (INT), `morale` (INT).
  - `api_rate`: tabla para contabilizar llamadas por ventana de 60s.
  - **Compatibilidad BD**:
    - MySQL ≥ 5.7 soporta `JSON`.
    - En MariaDB antiguas, cambia `resist JSON` por `TEXT` si es necesario.

## Rutas nuevas
- `battle/(:num)  → Battle::view`
- `api/login      → Api_auth::login`
- `api/state      → Api_game::state`

## Configuración
- `application/config/game.php` → bloque `combat`:
  - `damage_scale`, `min_damage`, `spread`, `targeting`, `hp_per_unit`.
- **JWT**: define `JWT_SECRET` en el entorno del servidor.

## Seguridad
- **JWT** con firma HMAC-SHA256.
- **Rate limiting** básico por usuario/ruta/ventana de 60s.

## Cómo actualizar
1. **Migraciones**:  
   ```bash
   php public/index.php migrate up   # avanza hasta 007
