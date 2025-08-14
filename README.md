# Archmage CI3 Skeleton (PHP 8.3+ / CodeIgniter 3.1.13 / Bootstrap 5)

Motor mínimo y esqueleto del MMORPG estilo Archmage, con soporte multilenguaje (ES/EN),
migraciones, importador de datos desde CSV, y resolución de turnos determinista.

## Requisitos
- PHP 8.1–8.4 con extensiones `mysqli`, `mbstring`, `json`, `intl`
- MySQL/MariaDB
- Composer (opcional para herramientas)
- Servidor web apuntando a `public/index.php` (o usar `php -S localhost:8080 -t public`)

## Instalación
1. Copia este proyecto y **coloca tus CSV** en `data/csv/`.
2. Copia `application/config/database.php.dist` a `application/config/database.php` y ajusta credenciales.
3. En `application/config/config.php.dist`, ajusta `base_url` y cópialo a `config.php`.
4. Activa migraciones y ejecútalas:
   ```bash
   php public/index.php migrate up
   ```
5. (Opcional) Importa datos de unidades/héroes/ítems/hechizos desde `data/csv/`:
   ```bash
   php public/index.php import definitions
   ```
6. Lanza el servidor y entra en `/`:
   ```bash
   php -S localhost:8080 -t public
   ```

## Idiomas
- Idioma por defecto: español (`es`).
- Se detecta por sesión o cabecera `Accept-Language`, y se puede forzar con `?lang=es` o `?lang=en`.
- Los textos viven en `application/language/{es,en}/game_lang.php`.

## Turnos
- Las órdenes se guardan en `orders` y un comando CLI resuelve el turno:
  ```bash
  php public/index.php turn run
  ```

## Estructura relevante
- `application/core/MY_Controller.php` — Base con carga de idioma.
- `application/libraries/Engine.php` — RNG determinista y resolver de combate (esqueleto).
- `application/controllers/Game.php` — Dashboard simple (Bootstrap 5).
- `application/controllers/Orders.php` — Endpoint para enviar órdenes (JSON).
- `application/controllers/Import.php` — CLI para importar definiciones desde CSV.
- `application/controllers/Migrate.php` — CLI para ejecutar migraciones.
- `application/controllers/Turn.php` — CLI para resolver turnos.
- `application/migrations/001_create_core_tables.php` — Tablas base.
- `application/models/*` — Modelos de dominio.
- `application/language/{es,en}/game_lang.php` — Textos del juego.

## Seguridad y buenas prácticas
- Valida siempre entrada (servidor autoritativo). 
- Sesiones en DB (`ci_sessions`) y protección CSRF (actívalo si expones formularios).
- Registra logs de auditoría (`battles`, `orders`).

## Nota
Este es un **esqueleto**: las reglas exactas del motor (fórmulas de daño, buffs/debuffs,
investigación, economía) se integran fácilmente en `Engine.php` y servicios asociados.
