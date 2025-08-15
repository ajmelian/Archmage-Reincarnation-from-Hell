# Archmage: Reincarnation from Hell

Un mundo roto por antiguas guerras arcanas vuelve a rugir. De los abismos surge una nueva estirpe de magos—**reencarnados desde el mismísimo Infierno**—dispuestos a reclamar reinos, despertar reliquias prohibidas y reescribir las reglas de la magia. **Archmage: Reincarnation from Hell** es un MMORPG estratégico **por turnos** donde cada tick es un latido del mundo: tus conjuros se tejen, tus ejércitos marchan, tus enemigos conspiran.

Forja tu **reino** desde ceniza y hierro. **Investiga escuelas de magia**, invoca criaturas imposibles, **contrata héroes** con talentos únicos y **equípales** con artefactos legendarios. Teje **alianzas**, declara **guerras**, manipula el **maná** y el **oro** como un verdadero Archmage. Cuando la campana del turno suene, el destino de todos quedará sellado.

### Lo que encontrarás aquí

* **Estrategia por turnos** con economía, reclutamiento, investigación y combate táctico.
* **Magia profunda**: investigación y lanzamiento de hechizos, invocaciones, buffs, daño y efectos con duración por tick.
* **Héroes e Ítems**: sinergias, equipo por slots y bonificaciones a recursos y combate.
* **Alianzas y Diplomacia**: coordina ofensivas, pacta treguas y domina el mapa.
* **Temporadas y Torneos**: compite por gloria persistente y premios de temporada.
* **Ranking**: ascenso por **Networth** y registros de batalla con **replay**.
* **API JWT** (rate-limited) para integraciones y herramientas de comunidad.

### Para constructores de mundos

* Backend **PHP + CodeIgniter 3** con **MySQL/MariaDB** y UI **Bootstrap**.
* **Multilenguaje** (es/en) y **CSRF** en toda la superficie.
* **Fórmulas configurables** (daño, caps, stacking, targeting) en `config/game.php`.
* **Ticks automáticos** vía CLI/cron y semilleros (Alice/Bob) para testeo inmediato.

**Reencarna. Reclama. Reina.** El mundo espera tu próxima orden cuando el turno cambie.


---

## Tabla de contenidos

* [Visión general](#visión-general)
* [Arquitectura y stack](#arquitectura-y-stack)
* [Módulos implementados](#módulos-implementados)
* [Requisitos](#requisitos)
* [Instalación](#instalación)
* [Configuración clave](#configuración-clave)
* [Migraciones](#migraciones)
* [Arranque rápido](#arranque-rápido)
* [Backups y exportaciones](#backups-y-exportaciones)
* [Seguridad](#seguridad)
* [Rendimiento y caché](#rendimiento-y-caché)
* [Moderación](#moderación)
* [CLI / Tareas útiles](#cli--tareas-útiles)
* [API](#api)
* [Desinstalación](#desinstalación)
* [Hoja de ruta (pendiente)](#hoja-de-ruta-pendiente)
* [Resolución de problemas](#resolución-de-problemas)

---

## Visión general

**Archmage: Reincarnation from Hell** es un MMORPG de estrategia con foco en:

* **Determinismo**: todo el cálculo de combate/economía se rige por fórmulas fijas y testeables.
* **Fair play**: sin IA, reglas claras, auditoría y moderación integradas.
* **Economía viva**: mercado P2P, subastas, límites anti-abuso y métricas.
* **Social**: alianzas con roles, chat dedicado y herramientas de coordinación.
* **Operabilidad**: backups, export de datos, límites de tasa y seguridad avanzada (CSP, CSRF, 2FA).

---

## Arquitectura y stack

* **Framework**: CodeIgniter **3.1.13** (MVC).
* **Lenguaje**: PHP 7.4+ (compatible con 8.x si extensiones lo permiten).
* **Base de datos**: MySQL/MariaDB.
* **Frontend**: Bootstrap (vistas server-side), i18n por ficheros de idioma CI3.
* **Caché**: File/APCu/Redis (configurable).
* **Sesiones**: CI3; expiración **300 s** (autolimpieza).
* **CLI**: Controladores CLI de CI3 para mantenimiento.
* **Determinismo**: fórmulas y motor (Engine) encapsulados en librerías.

---

## Módulos implementados

### Núcleo del juego

* **Autenticación y sesiones** (exp. 300 s), registro, rehash seguro de contraseñas.
* **2FA (TOTP)** opcional.
* **Soporte i18n** (ficheros de idioma CI3).
* **Engine** (fórmulas), investigación, hechizos (lanzar, investigar, invocar), economía base.

### PvP y combate

* **Combate avanzado** (resolución determinista) + **API** de batalla.

### Social

* **Alianzas (guilds)** con roles (líder, oficial, miembro), invitaciones, auditoría y **chat de alianza**.

### Economía

* **Mercado**: listar/comprar con tasas, depósitos, límites de precio vs referencia.
* **Subastas**: pujas, buyout, soft-extend anti-snipe.
* **Rate-limits** y controles anti-abuso.

### Operación

* **Moderación**: reportes de usuarios, sanciones (mute chat, suspender mercado, ban arena), panel de moderación y auditoría.
* **Seguridad avanzada**: CSP/HSTS, CSRF en producción, passwords rehash, 2FA TOTP.
* **Backups**: dumps de BD con retención, checksum y **UI** de gestión.
* **Export de datos**: CSV/JSON por módulos (market, auctions, alliances, audit, economy…).
* **Rendimiento y caché**: índices SQL, output-cache selectivo, caché de datos (file/APCu/Redis), ETag + Cache-Control en API, rate-limit básico.

> **Excluido por diseño** (según requisitos): IA, PvE instanciado, torneos programados, mapa/exploración/raids, eventos globales.

---

## Requisitos

* **PHP** 7.4 o superior (extensiones: `mbstring`, `openssl`, `pdo_mysql`, `json`, `ctype`, `curl`; opcional `apcu`, `redis`).
* **MySQL/MariaDB** 5.7+ / 10.3+.
* **Servidor web**: Apache/Nginx con PHP-FPM o mod\_php.
* Acceso **CLI** para ejecutar migraciones y tareas de mantenimiento.

---

## Instalación

1. **Clonar** el repositorio:

```bash
git clone <url> archmage
cd archmage
```

2. **Permisos** de escritura (usuario del servidor web):

```bash
mkdir -p application/cache/archmage public/backups application/logs
chown -R www-data:www-data application/cache application/logs public/backups
chmod -R 775 application/cache application/logs public/backups
```

3. **Configurar base de datos**
   Edita `application/config/database.php` y ajusta el grupo `default` (host, dbname, user, pass, charset `utf8mb4` recomendado).

4. **Configurar aplicación**

* `application/config/config.php`:

  * `base_url` → URL pública (incluye `/` final).
  * `encryption_key` → genera una clave segura (32+ chars).
  * **CSRF (producción)**: ver `application/config/production/config.php` (activado).
* **Hooks**: `application/config/hooks.php` incluye `SecurityHeaders` (CSP/HSTS).

5. **Migrations** (crear/esquema/índices/tablas nuevas):

```bash
php public/index.php migrate up
```

6. (Opcional) **Redis** para caché:

* Instala Redis y ext. `php-redis`.
* En `application/config/cache.php` → `driver` = `redis`.

7. **VirtualHost / Nginx**
   Apunta el docroot a `public/`. Asegura HTTPS para HSTS.

---

## Configuración clave

* **Sesiones**: expiran a los **300 s** por requisito (auto-eliminación).
* **CSP/HSTS**: ajusta `application/config/security.php` a tus CDNs reales. HSTS solo aplica con HTTPS.
* **Caché**: `application/config/cache.php` (driver, TTLs por prefijo).
* **Backups**: `application/config/backup.php` (ruta, formato, retención).
* **Moderación**: `application/config/moderation.php` (duraciones máx., etc.).

---

## Migraciones

El proyecto usa migraciones CI3. Algunas relevantes:

* `030_alliances_module.php` — alianzas.
* `031_moderation_logging.php` — audit, flags, sanciones.
* `032_security_twofa.php` — 2FA y metadatos de login.
* `033_backups_table.php` — metadatos de backups.
* `034_performance_indexes.php` — Índices de rendimiento.

Ejecuta siempre:

```bash
php public/index.php migrate up
```

---

## Arranque rápido

1. **Crear usuario admin** (si no tienes interfaz para ello, inserta manualmente un usuario con rol admin).
2. **Entrar** y crear tu **reino** inicial.
3. Probar **hechizos**, **mercado** (`/market`), **subastas** (`/auctions`), **alianzas** (`/alliances`).
4. Panel de **moderación**: `/mod` (requiere admin).

---

## Backups y exportaciones

### Backups (UI y CLI)

* UI: `/backup` → crear/descargar/eliminar.
* CLI:

```bash
php public/index.php backupcli create    # crear dump de BD
php public/index.php backupcli list      # listar
php public/index.php backupcli prune     # aplicar retención
```

* Configuración: `application/config/backup.php`

  * Ruta (por defecto `public/backups`), formato (`gzip|zip`), retención (`keep_last`, `max_total_mb`).

### Export de datos (CSV/JSON)

* UI: `/export` → selecciona módulo y filtros (since, realm\_id, etc.).
* API:

```bash
GET /api/v1/export?module=market_trades&format=csv&since=1719792000
Authorization: Bearer <token>   # scope read
```

---

## Seguridad

* **CSRF** activo en entorno `production` (`application/config/production/config.php`).
* **CSP/HSTS y cabeceras** seguras vía hook `SecurityHeaders` (edita `application/config/security.php`).
* **Passwords**: `Passwords::verifyAndRehash` re-hashea en login si cambian parámetros.
* **2FA (TOTP)**:

  * UI: `/twofa` → activar/desactivar.
  * Login: si está activo, paso adicional `/twofa/login_step`.
  * CLI: `Securitycli twofa_setup <userId> | twofa_disable <userId>`.
* **Rate-limit** básico en endpoints críticos (p.ej. export de API).

**Checklist recomendado**

* Servir **siempre** en HTTPS.
* Revisar CSP (CDNs permitidos).
* Rotar `encryption_key` al inicio del proyecto.
* Revisar permisos de `application/logs`, `application/cache/archmage`, `public/backups`.

---

## Rendimiento y caché

* **Índices SQL** en tablas críticas (mercado, subastas, log/auditoría, economía).
* **Output cache** en `/market`, `/auctions`, `/alliances`.
* **Caché de datos** (file/APCu/Redis) con TTLs por prefijo.
* **ETag + Cache-Control** en `GET /api/v1/export`.
* CLI para caché:

```bash
php public/index.php cachecli purge market:
php public/index.php cachecli warm_all
```

---

## Moderación

* **Reportes** de usuarios (`/api/v1/report` o desde UI).
* **Sanciones**: mute de chat, suspender mercado, ban de arena, avisos.
* **Panel**: `/mod` (resolver reportes, aplicar sanciones).
* **Enforcement** automático en Chat/Mercado/Subastas.

---

## CLI / Tareas útiles

* **Backups**: `backupcli create|list|prune`
* **Caché**: `cachecli purge <prefijo> | warm_all`
* **Moderación**: `modcli flags | sanction <modUserId> <realmId> <action> <min> [reason] | expire`
* **Alianzas**: `alliancecli create|invite|accept`
* **Seguridad**: `securitycli twofa_setup|twofa_disable|show_csp`

> Todos los controladores CLI se ejecutan con:
> `php public/index.php <controller> <method> [args...]`

---

## API

API **v1** (JSON/CSV) pensada para integraciones y dashboards:

* **Economía/mercado/subastas**: lectura y escritura (según endpoints).
* **Alianzas**: información y acciones (crear, invitar, aceptar, salir…).
* **Moderación**: enviar reportes.
* **Export**: `GET /api/v1/export` (CSV/JSON).

Revisa `application/controllers/api/V1.php` para el listado actualizado y scopes requeridos.

---

## Desinstalación

1. **Backups** (opcional): exporta y descarga lo necesario:

   * `/backup` → descargar últimos dumps.
   * `/export` → CSV/JSON de módulos clave.
2. **Eliminar tareas y servicios**:

   * Detén crons o supervisores que llamen a CLI del juego.
   * Si usas **Redis**, puedes limpiar claves con prefijo (`archmage:`).
3. **Borrar base de datos**:

   * `DROP DATABASE <tu_bd>;` (o elimina tablas si compartidas).
4. **Eliminar archivos**:

   * Borra el directorio del proyecto, los logs y `public/backups`.
   * Retira el vHost de Nginx/Apache y recarga servicio.

---

## Hoja de ruta (pendiente)

(Respetando tus exclusiones: **sin IA**, **sin PvE instanciado**, **sin torneos programados**, **sin mapa/exploración/raids**, **sin eventos globales**)

* **S37 – Anti-trampas (rule-based)**: límites de transferencia, firmas anti-exploit, alertas.
* **S38 – Temporadas / resets**: Hall of Fame, snapshots, recompensas de fin de temporada.
* **S39 – i18n completo**: cubrir todas las pantallas nuevas (mercado, subastas, alianzas, moderación, seguridad, backups, export).
* **S40 – Notificaciones & correo**: SMTP opcional, avisos de sanción/mercado, recuperación de cuenta.
* **S41 – Backoffice de contenidos**: CRUD de unidades/hechizos/items y **importador** desde tu ODS/CSV.
* **S42 – Observabilidad & dashboards**: métricas, paneles y alertas.
* **S43 – Seguridad extra**: throttling de login, bloqueo por IP/ASN, CAPTCHA opcional, política de contraseñas.
* **S44 – Deploy & DevOps**: Docker/Compose, Nginx+PHP-FPM, CI/CD, seeds y cronjobs.
* **S45 – Pruebas E2E y carga**: E2E (browser) y stress (k6/artillery).
* **S46 – Privacidad/GDPR**: exportar/borrar cuenta, retenciones, cookies si aplica.
* **S47 – UX/UI polishing**: a11y, responsive fino, toasts, dark mode opcional.
* **S48 – Documentación**: OpenAPI de v1, guías de operación y economía.

---

## Resolución de problemas

* **500 / cabeceras CSP**: revisa `application/config/security.php` y añade los CDNs que uses realmente.
* **CSRF inválido**: en producción, los tokens expiran a **300 s**; renueva la página antes de enviar formularios.
* **Sesiones expiran rápido**: por diseño a 300 s. Si necesitas más, ajusta política de sesión (ten en cuenta requisito original).
* **Caché no se purga**: usa Redis para `deleteByPrefix` efectivo; con file-cache depende del TTL.
* **No puedo crear backup**: verifica permisos en `public/backups` y que la DB sea accesible desde PHP.
* **CLI no responde**: ejecuta con `php public/index.php <controller> <method>`, estando en el raíz del proyecto.
